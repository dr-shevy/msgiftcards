<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsCertificateCreateProcessor extends msGiftCardsMgrProcessor
{
    public function process()
    {
        $code = trim((string)$this->getProperty('code', ''));
        if ($code === '') {
            $code = $this->msGiftCards->generateUniqueCode();
        }
        $code = trim((string)$code);
        if ($code === '') {
            return $this->failure($this->modx->lexicon('msgiftcards_err_code_empty'));
        }

        $nominal = max(0, (float)$this->getProperty('nominal', 0));
        if ($nominal <= 0) {
            return $this->failure($this->modx->lexicon('msgiftcards_mgr_err_nominal_required'));
        }

        $balanceRaw = $this->getProperty('balance', null);
        $balance = $balanceRaw === null || $balanceRaw === '' ? $nominal : max(0, (float)$balanceRaw);
        $defaultCurrency = trim((string)$this->modx->getOption('msgiftcards_default_currency', null, $this->modx->getOption('ms2_frontend_currency', null, 'RUB')));
        if ($defaultCurrency === '') {
            $defaultCurrency = 'RUB';
        }
        $currency = trim((string)$this->getProperty('currency', $defaultCurrency));
        if ($currency === '') {
            $currency = $defaultCurrency;
        }

        $active = (int)$this->getProperty('active', 1) ? 1 : 0;
        $orderId = max(0, (int)$this->getProperty('order_id', 0));
        $orderProductId = max(0, (int)$this->getProperty('order_product_id', 0));
        // Keep unique pair (order_product_id,item_index) for manually created certificates.
        $itemIndex = (int)$this->getProperty('item_index', 0);
        if ($itemIndex <= 0) {
            $itemIndex = (int)(microtime(true) * 1000000) % 2147483647;
            if ($itemIndex <= 0) {
                $itemIndex = mt_rand(100000, 999999);
            }
        }
        $expiresOn = trim((string)$this->getProperty('expireson', ''));
        if ($expiresOn === '') {
            $expiresOn = null;
        }

        if ($this->msGiftCards->getCertificateByCode($code)) {
            return $this->failure($this->modx->lexicon('msgiftcards_mgr_err_duplicate_code'));
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->modx->prepare(
            'INSERT INTO ' . $this->tableCertificates . ' '
            . '(code, nominal, balance, currency, active, order_id, order_product_id, item_index, createdon, updatedon, expireson) '
            . 'VALUES (:code, :nominal, :balance, :currency, :active, :order_id, :order_product_id, :item_index, :createdon, :updatedon, :expireson)'
        );

        if (!$stmt) {
            return $this->failure('Could not prepare insert query');
        }

        $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        $stmt->bindValue(':nominal', $nominal);
        $stmt->bindValue(':balance', $balance);
        $stmt->bindValue(':currency', $currency, PDO::PARAM_STR);
        $stmt->bindValue(':active', $active, PDO::PARAM_INT);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':order_product_id', $orderProductId, PDO::PARAM_INT);
        $stmt->bindValue(':item_index', $itemIndex, PDO::PARAM_INT);
        $stmt->bindValue(':createdon', $now, PDO::PARAM_STR);
        $stmt->bindValue(':updatedon', $now, PDO::PARAM_STR);
        if ($expiresOn === null) {
            $stmt->bindValue(':expireson', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':expireson', $expiresOn, PDO::PARAM_STR);
        }

        if (!$stmt->execute()) {
            return $this->failure('Could not create certificate');
        }

        return $this->success();
    }
}

return 'msGiftCardsCertificateCreateProcessor';
