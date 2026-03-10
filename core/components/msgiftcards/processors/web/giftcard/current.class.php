<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsGiftCardCurrentProcessor extends msGiftCardsWebProcessor
{
    public function process()
    {
        $state = $this->msGiftCards->getCheckoutState();
        if (empty($state['code'])) {
            return $this->success('', [
                'applied' => 0,
                'code' => '',
                'nominal' => 0,
                'balance' => 0,
                'writeoff' => 0,
                'balance_after' => 0,
                'currency' => '',
                'info_html' => '',
            ]);
        }

        list($ok, $certificate) = $this->msGiftCards->validateCertificateCode($state['code']);
        if (!$ok) {
            $this->msGiftCards->clearCheckoutState();
            return $this->success('', [
                'applied' => 0,
                'code' => '',
                'nominal' => 0,
                'balance' => 0,
                'writeoff' => 0,
                'balance_after' => 0,
                'currency' => '',
                'info_html' => '',
            ]);
        }

        $balance = max(0.0, (float)$certificate['balance']);
        $nominal = max(0.0, (float)$certificate['nominal']);
        $currency = (string)$certificate['currency'];
        $writeoff = 0.0;

        $ms2 = $this->modx->getService('miniShop2');
        if ($ms2) {
            $ctx = $this->modx->context ? $this->modx->context->get('key') : 'web';
            $ms2->initialize($ctx);
            // Trigger common order-cost pipeline (with all external discounts),
            // then read giftcard writeoff from checkout state set by plugin.
            $ms2->order->getCost(true, true);
            $state = $this->msGiftCards->getCheckoutState();
            $writeoff = isset($state['discount']) ? max(0.0, (float)$state['discount']) : 0.0;
        }

        $remain = max(0.0, $balance - $writeoff);
        $this->msGiftCards->setCheckoutState([
            'code' => $certificate['code'],
            'certificate_id' => (int)$certificate['id'],
            'discount' => $writeoff,
        ]);

        $infoHtml = $this->modx->runSnippet('msGiftCardsInfo', [
            'asBody' => 1,
            'code' => $certificate['code'],
            'currency' => $currency,
            'nominal' => $nominal,
            'balance' => $balance,
            'writeoff' => $writeoff,
            'balance_after' => $remain,
        ]);

        return $this->success('', [
            'applied' => 1,
            'code' => $certificate['code'],
            'nominal' => $nominal,
            'balance' => $balance,
            'writeoff' => $writeoff,
            'balance_after' => $remain,
            'currency' => $currency,
            'info_html' => $infoHtml,
        ]);
    }
}

return 'msGiftCardsGiftCardCurrentProcessor';
