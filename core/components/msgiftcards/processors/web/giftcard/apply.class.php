<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsGiftCardApplyProcessor extends msGiftCardsWebProcessor
{
    public function process()
    {
        $code = $this->getProperty('code', '');
        $code = $this->msGiftCards->normalizeCode($code);
        if ($code === '') {
            return $this->failure($this->modx->lexicon('msgiftcards_err_code_empty'));
        }

        list($ok, $payload) = $this->msGiftCards->validateCertificateCode($code);
        if (!$ok) {
            return $this->failure((string)$payload);
        }

        $this->msGiftCards->setCheckoutState([
            'code' => $payload['code'],
            'certificate_id' => (int)$payload['id'],
            'discount' => 0,
        ]);

        $currency = (string)$payload['currency'];
        $balance = max(0.0, (float)$payload['balance']);
        $nominal = max(0.0, (float)$payload['nominal']);
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
            'code' => $payload['code'],
            'certificate_id' => (int)$payload['id'],
            'discount' => $writeoff,
        ]);

        $infoHtml = $this->modx->runSnippet('msGiftCardsInfo', [
            'code' => $payload['code'],
            'currency' => $currency,
            'nominal' => $nominal,
            'balance' => $balance,
            'writeoff' => $writeoff,
            'balance_after' => $remain,
        ]);

        return $this->success('', [
            'code' => $payload['code'],
            'balance' => $balance,
            'nominal' => $nominal,
            'currency' => $currency,
            'writeoff' => $writeoff,
            'balance_after' => $remain,
            'info_html' => $infoHtml,
        ]);
    }
}

return 'msGiftCardsGiftCardApplyProcessor';
