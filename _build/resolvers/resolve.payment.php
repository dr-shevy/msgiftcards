<?php
/** @var array $options */

$xpdo = null;
if (isset($modx) && $modx instanceof modX) {
    $xpdo = $modx;
} elseif (isset($object) && $object instanceof xPDOObject) {
    $xpdo = $object->xpdo;
} elseif (isset($transport) && isset($transport->xpdo)) {
    $xpdo = $transport->xpdo;
}

if (!$xpdo) {
    return false;
}

$action = isset($options[xPDOTransport::PACKAGE_ACTION]) ? $options[xPDOTransport::PACKAGE_ACTION] : null;

$ms2CorePath = $xpdo->getOption('minishop2_core_path', null, $xpdo->getOption('core_path') . 'components/minishop2/');
$ms2 = $xpdo->getService('minishop2', 'miniShop2', $ms2CorePath . 'model/minishop2/');
if (!$ms2) {
    return true;
}

$serviceName = 'msgiftcards_payment';
$serviceController = '{core_path}components/msgiftcards/custom/payment/msgiftcardspaymenthandler.class.php';

if ($action === xPDOTransport::ACTION_UNINSTALL) {
    $ms2->removeService('payment', $serviceName);
    return true;
}

if ($action !== xPDOTransport::ACTION_INSTALL && $action !== xPDOTransport::ACTION_UPGRADE) {
    return true;
}

$ms2->addService('payment', $serviceName, $serviceController);

$paymentName = 'Подарочный сертификат';
$paymentClass = 'msGiftCardsPaymentHandler';
$payment = null;

$setting = $xpdo->getObject('modSystemSetting', ['key' => 'msgiftcards_gift_payment_id']);
$settingPaymentId = $setting ? (int)$setting->get('value') : 0;
if ($settingPaymentId > 0) {
    $payment = $xpdo->getObject('msPayment', ['id' => $settingPaymentId]);
}

if (!$payment) {
    $payment = $xpdo->getObject('msPayment', ['name' => $paymentName]);
}

if (!$payment) {
    $payment = $xpdo->newObject('msPayment');
    $payment->fromArray([
        'name' => $paymentName,
        'description' => 'Автоматически создано msGiftCards',
        'price' => 0,
        'logo' => '',
        'rank' => 999,
        'active' => 1,
        'class' => $paymentClass,
        'properties' => ['msgiftcards_auto_created' => 1],
    ], '', true, true);
    $payment->save();
} else {
    $payment->set('class', $paymentClass);
    if ((int)$payment->get('active') !== 1) {
        $payment->set('active', 1);
    }
    $payment->save();
}

if ($payment) {
    if ($setting) {
        $setting->set('value', (int)$payment->get('id'));
        $setting->save();
    }

    $deliveryMembers = $xpdo->getCollection('msDeliveryMember', ['payment_id' => (int)$payment->get('id')]);
    if (empty($deliveryMembers)) {
        $deliveries = $xpdo->getCollection('msDelivery', ['active' => 1]);
        foreach ($deliveries as $delivery) {
            /** @var msDelivery $delivery */
            $member = $xpdo->newObject('msDeliveryMember');
            $member->fromArray([
                'delivery_id' => (int)$delivery->get('id'),
                'payment_id' => (int)$payment->get('id'),
                'rank' => 0,
            ], '', true, true);
            $member->save();
        }
    }
}

return true;

