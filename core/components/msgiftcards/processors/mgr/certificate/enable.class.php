<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsCertificateEnableProcessor extends msGiftCardsMgrProcessor
{
    public function process()
    {
        $id = (int)$this->getProperty('id', 0);
        if ($id <= 0) {
            return $this->failure('Certificate id is required');
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->modx->prepare('UPDATE ' . $this->tableCertificates . ' SET active = 1, updatedon = :updatedon WHERE id = :id');
        if (!$stmt) {
            return $this->failure('Could not prepare enable query');
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':updatedon', $now, PDO::PARAM_STR);
        if (!$stmt->execute()) {
            return $this->failure('Could not enable certificate');
        }

        return $this->success();
    }
}

return 'msGiftCardsCertificateEnableProcessor';
