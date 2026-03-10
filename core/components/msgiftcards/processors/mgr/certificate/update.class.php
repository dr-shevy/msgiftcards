<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsCertificateUpdateProcessor extends msGiftCardsMgrProcessor
{
    public function process()
    {
        $id = (int)$this->getProperty('id', 0);
        if ($id <= 0) {
            return $this->failure('Certificate id is required');
        }

        $stmt = $this->modx->prepare('SELECT * FROM ' . $this->tableCertificates . ' WHERE id = :id LIMIT 1');
        if (!$stmt) {
            return $this->failure('Could not prepare find query');
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return $this->failure('Could not execute find query');
        }
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$current) {
            return $this->failure('Certificate not found');
        }

        $hasRedemptions = $this->hasRedemptions($id);
        $currentCode = trim((string)$current['code']);
        $inputCode = trim((string)$this->getProperty('code', ''));

        if ($hasRedemptions) {
            if ($inputCode !== '' && strcasecmp($inputCode, $currentCode) !== 0) {
                return $this->failure($this->modx->lexicon('msgiftcards_mgr_err_code_locked'));
            }
            $code = $currentCode;
        } else {
            $code = $inputCode;
            if ($code === '') {
                $code = $this->msGiftCards->generateUniqueCode();
            }
            $code = trim((string)$code);
            if ($code === '') {
                return $this->failure($this->modx->lexicon('msgiftcards_err_code_empty'));
            }
        }

        $duplicateStmt = $this->modx->prepare('SELECT id FROM ' . $this->tableCertificates . ' WHERE UPPER(code) = UPPER(:code) AND id <> :id LIMIT 1');
        if ($duplicateStmt) {
            $duplicateStmt->bindValue(':code', $code, PDO::PARAM_STR);
            $duplicateStmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($duplicateStmt->execute() && $duplicateStmt->fetchColumn()) {
                return $this->failure($this->modx->lexicon('msgiftcards_mgr_err_duplicate_code'));
            }
        }

        $nominal = max(0, (float)$this->getProperty('nominal', 0));
        $balance = max(0, (float)$this->getProperty('balance', 0));
        $defaultCurrency = trim((string)$this->modx->getOption('msgiftcards_default_currency', null, $this->modx->getOption('ms2_frontend_currency', null, 'RUB')));
        if ($defaultCurrency === '') {
            $defaultCurrency = 'RUB';
        }
        $currency = trim((string)$this->getProperty('currency', $defaultCurrency));
        if ($currency === '') {
            $currency = $defaultCurrency;
        }

        $active = (int)$this->getProperty('active', 1) ? 1 : 0;
        // Keep technical order linkage unchanged in manager update form.
        $orderId = max(0, (int)$current['order_id']);
        $orderProductId = max(0, (int)$current['order_product_id']);
        $itemIndex = max(1, (int)$current['item_index']);
        $expiresOn = trim((string)$this->getProperty('expireson', ''));
        if ($expiresOn === '') {
            $expiresOn = null;
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->modx->prepare(
            'UPDATE ' . $this->tableCertificates . ' SET '
            . 'code = :code, nominal = :nominal, balance = :balance, currency = :currency, active = :active, '
            . 'order_id = :order_id, order_product_id = :order_product_id, item_index = :item_index, expireson = :expireson, updatedon = :updatedon '
            . 'WHERE id = :id'
        );
        if (!$stmt) {
            return $this->failure('Could not prepare update query');
        }

        $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        $stmt->bindValue(':nominal', $nominal);
        $stmt->bindValue(':balance', $balance);
        $stmt->bindValue(':currency', $currency, PDO::PARAM_STR);
        $stmt->bindValue(':active', $active, PDO::PARAM_INT);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':order_product_id', $orderProductId, PDO::PARAM_INT);
        $stmt->bindValue(':item_index', $itemIndex, PDO::PARAM_INT);
        if ($expiresOn === null) {
            $stmt->bindValue(':expireson', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':expireson', $expiresOn, PDO::PARAM_STR);
        }
        $stmt->bindValue(':updatedon', $now, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return $this->failure('Could not update certificate');
        }

        return $this->success();
    }
}

return 'msGiftCardsCertificateUpdateProcessor';
