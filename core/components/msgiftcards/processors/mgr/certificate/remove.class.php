<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsCertificateRemoveProcessor extends msGiftCardsMgrProcessor
{
    public function process()
    {
        $id = (int)$this->getProperty('id', 0);
        if ($id <= 0) {
            return $this->failure('Certificate id is required');
        }

        if ($this->hasRedemptions($id)) {
            return $this->failure($this->modx->lexicon('msgiftcards_mgr_err_has_redemptions'));
        }

        $stmt = $this->modx->prepare('DELETE FROM ' . $this->tableCertificates . ' WHERE id = :id');
        if (!$stmt) {
            return $this->failure('Could not prepare delete query');
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return $this->failure('Could not delete certificate');
        }

        return $this->success();
    }
}

return 'msGiftCardsCertificateRemoveProcessor';
