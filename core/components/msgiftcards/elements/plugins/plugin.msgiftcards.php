<?php
/** @var modX $modx */
$corePath = $modx->getOption('msgiftcards_core_path', null, $modx->getOption('core_path') . 'components/msgiftcards/');
/** @var msGiftCards $msGiftCards */
$msGiftCards = $modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
if (!$msGiftCards || !$msGiftCards->config['enabled']) {
    return;
}

$recalculateCheckoutState = function ($cartObj = null) use ($modx, $msGiftCards) {
    $state = $msGiftCards->getCheckoutState();
    if (empty($state['code'])) {
        return;
    }

    list($ok, $certificate) = $msGiftCards->validateCertificateCode($state['code']);
    if (!$ok) {
        $msGiftCards->clearCheckoutState();
        return;
    }

    $ms2 = $modx->getService('miniShop2');
    if ($ms2) {
        $ctx = $modx->context ? $modx->context->get('key') : 'web';
        $ms2->initialize($ctx);
    }

    $msGiftCards->setCheckoutState([
        'code' => $certificate['code'],
        'certificate_id' => (int)$certificate['id'],
        'discount' => 0,
    ]);

    if ($ms2 && isset($ms2->order)) {
        $ms2->order->getCost(true, true);
    }
};

switch ($modx->event->name) {
    case 'msOnSubmitOrder':
        if (!isset($data) || !is_array($data)) {
            break;
        }

        $inputCode = isset($data['gift_code']) ? $data['gift_code'] : '';
        $normalizedCode = $msGiftCards->normalizeCode($inputCode);
        if ($normalizedCode === '') {
            $msGiftCards->clearCheckoutState();
            break;
        }

        list($ok, $payload) = $msGiftCards->validateCertificateCode($normalizedCode);
        if (!$ok) {
            $modx->event->output($payload);
            break;
        }

        $msGiftCards->setCheckoutState([
            'code' => $payload['code'],
            'certificate_id' => (int)$payload['id'],
            'discount' => 0,
        ]);
        $modx->event->returnedValues['data'] = $data;
        break;

    case 'msOnGetOrderCost':
        if (isset($with_cart) && !$with_cart) {
            // miniShop2 calls getCost(false, true) to calculate delivery part.
            // Gift certificate discount must not be applied in this branch.
            break;
        }

        $state = $msGiftCards->getCheckoutState();
        if (empty($state['code'])) {
            break;
        }

        list($ok, $certificate) = $msGiftCards->validateCertificateCode($state['code']);
        if (!$ok) {
            $msGiftCards->clearCheckoutState();
            $modx->log(modX::LOG_LEVEL_INFO, '[msGiftCards] ' . (string)$certificate);
            break;
        }

        $balance = max(0.0, (float)$certificate['balance']);
        $currentCost = max(0.0, (float)$cost);
        $msBonus2Writeoff = $msGiftCards->getMsBonus2Writeoff();

        // msBonus2 1.3.x does not change miniShop2 order cost on msOnGetOrderCost.
        // It stores writeoff in session and subtracts it on the frontend only.
        // Apply the certificate to the actual remaining payable amount.
        if ($msBonus2Writeoff > 0) {
            $currentCost = max(0.0, $currentCost - $msBonus2Writeoff);
        }

        // IMPORTANT: apply certificate to already calculated order cost.
        // This makes it compatible with other discounts (e.g. msBonus2).
        $orderBaseCost = $currentCost;
        $discount = min($balance, $orderBaseCost);
        $newCost = max(0.0, $orderBaseCost - $discount);

        $msGiftCards->setCheckoutState([
            'code' => (string)$certificate['code'],
            'certificate_id' => (int)$certificate['id'],
            'discount' => $discount,
            'order_discount_base' => $orderBaseCost,
            'msbonus2_writeoff' => $msBonus2Writeoff,
        ]);

        $modx->event->returnedValues['cost'] = $newCost;
        if (isset($delivery_cost)) {
            $modx->event->returnedValues['delivery_cost'] = $delivery_cost;
        }
        $modx->event->returnedValues['msbonus2_writeoff'] = $msBonus2Writeoff;
        $modx->event->returnedValues['msgiftcards_discount'] = $discount;
        $modx->event->returnedValues['msgiftcards_code'] = (string)$certificate['code'];
        break;

    case 'msOnGetStatusCart':
        if (!isset($status) || !is_array($status)) {
            break;
        }

        $originalCartCost = $msGiftCards->getCartBaseTotalCost($status);
        $applied = $msGiftCards->applyDiscountToCost($originalCartCost);
        if (!empty($applied['message'])) {
            $modx->log(modX::LOG_LEVEL_INFO, '[msGiftCards] ' . $applied['message']);
        }

        // Do not override cart total_cost here.
        // This keeps mini-cart `.ms2_total_cost` unchanged by gift certificates.
        // Final writeoff is applied on order totals in msOnGetOrderCost.
        $status['msgiftcards_original_total_cost'] = $originalCartCost;
        $status['msgiftcards_discount'] = $applied['discount'];
        $status['msgiftcards_code'] = !empty($applied['certificate']['code']) ? $applied['certificate']['code'] : '';
        $modx->event->returnedValues['status'] = $status;
        break;

    case 'msOnBeforeCreateOrder':
        $state = $msGiftCards->getCheckoutState();
        if (empty($state['code'])) {
            break;
        }

        list($ok, $certificate) = $msGiftCards->validateCertificateCode($state['code']);
        if (!$ok) {
            $msGiftCards->clearCheckoutState();
            break;
        }

        $balance = max(0.0, (float)$certificate['balance']);
        if (isset($state['order_discount_base'])) {
            $amount = min($balance, max(0.0, (float)$state['order_discount_base']));
        } else {
            // Fallback for flows where msOnGetOrderCost has not populated state yet.
            $recalculateCheckoutState();
            $state = $msGiftCards->getCheckoutState();
            $amount = max(0.0, (float)($state['discount'] ?? 0));
        }
        if ($amount <= 0) {
            break;
        }

        $props = $msGiftCards->normalizeProperties($msOrder->get('properties'));
        $props['msgiftcards'] = [
            'code' => $certificate['code'],
            'certificate_id' => (int)$certificate['id'],
            'amount' => $amount,
            'redeemed' => 0,
        ];
        $msOrder->set('properties', $props);

        $giftPaymentId = (int)$modx->getOption('msgiftcards_gift_payment_id', null, 0);
        if ($giftPaymentId > 0) {
            $orderBaseCost = isset($state['order_discount_base'])
                ? max(0.0, (float)$state['order_discount_base'])
                : max(0.0, (float)$msOrder->get('cart_cost') + (float)$msOrder->get('delivery_cost'));

            $finalOrderCost = max(0.0, (float)$msOrder->get('cost'));
            $isFullyCovered = ($finalOrderCost <= 0.00001) || ($amount >= ($orderBaseCost - 0.00001));
            if ($isFullyCovered) {
                // Use configured gift payment even if it is disabled in miniShop2 settings.
                $payment = $modx->getObject('msPayment', ['id' => $giftPaymentId]);
                if ($payment) {
                    $msOrder->set('payment', $giftPaymentId);
                }
            }
        }
        break;

    case 'msOnAddToCart':
    case 'msOnRemoveFromCart':
    case 'msOnChangeInCart':
        $recalculateCheckoutState(isset($cart) ? $cart : null);
        break;

    case 'msOnChangeOrderStatus':
        if (empty($order) || !($order instanceof msOrder)) {
            break;
        }

        $currentStatus = 0;
        if (isset($status)) {
            if (is_array($status)) {
                if (isset($status['id'])) {
                    $currentStatus = (int)$status['id'];
                } elseif (isset($status['status'])) {
                    $currentStatus = (int)$status['status'];
                }
            } else {
                $currentStatus = (int)$status;
            }
        }
        if ($currentStatus <= 0) {
            $currentStatus = (int)$order->get('status');
        }
        if ($currentStatus <= 0 && isset($statusOld)) {
            $currentStatus = (int)$statusOld;
        }

        $generateStatus = (int)$msGiftCards->ensureGenerateStatusId();
        $redeemStatus = (int)$msGiftCards->ensureRedeemStatusId();
        $cancelStatus = (int)$msGiftCards->ensureCancelStatusId();

        if (!empty($generateStatus) && $currentStatus === $generateStatus) {
            $created = $msGiftCards->generateCertificatesForPaidOrder($order);
            if (!empty($created)) {
                $modx->log(modX::LOG_LEVEL_INFO, '[msGiftCards] Generated certificates for order #' . $order->get('id') . ': ' . implode(', ', $created));
            }
        }

        if (!empty($redeemStatus) && $currentStatus === $redeemStatus) {
            $msGiftCards->redeemOrderCertificate($order);

            // Customer has completed order; clear active code from session.
            $msGiftCards->clearCheckoutState();
        }

        if (!empty($cancelStatus) && $currentStatus === $cancelStatus) {
            $msGiftCards->refundOrderCertificate($order);
        }
        break;

    case 'msOnEmptyCart':
        $msGiftCards->clearCheckoutState();
        break;
}

