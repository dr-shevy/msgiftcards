<?php

abstract class msGiftCardsMgrProcessor extends modProcessor
{
    /** @var msGiftCards */
    protected $msGiftCards;
    protected $tableCertificates;
    protected $tableRedemptions;

    public function initialize()
    {
        $corePath = $this->modx->getOption('msgiftcards_core_path', null, $this->modx->getOption('core_path') . 'components/msgiftcards/');
        $this->msGiftCards = $this->modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
        if (!$this->msGiftCards || !$this->msGiftCards->config['enabled']) {
            return $this->modx->lexicon('msgiftcards_err_disabled');
        }

        $this->modx->lexicon->load('msgiftcards:default');
        $prefix = $this->modx->getOption('table_prefix');
        $this->tableCertificates = $prefix . 'msgiftcards_certificates';
        $this->tableRedemptions = $prefix . 'msgiftcards_redemptions';

        return parent::initialize();
    }

    protected function hasRedemptions($certificateId)
    {
        $stmt = $this->modx->prepare('SELECT id FROM ' . $this->tableRedemptions . ' WHERE certificate_id = :certificate_id LIMIT 1');
        if (!$stmt) {
            return false;
        }

        $stmt->bindValue(':certificate_id', (int)$certificateId, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return false;
        }

        return (bool)$stmt->fetchColumn();
    }

    protected function mapRow(array $row)
    {
        $row['id'] = (int)$row['id'];
        $row['nominal'] = (float)$row['nominal'];
        $row['balance'] = (float)$row['balance'];
        $row['active'] = (int)$row['active'];
        $row['order_id'] = (int)$row['order_id'];
        $row['order_product_id'] = (int)$row['order_product_id'];
        $row['item_index'] = (int)$row['item_index'];

        return $row;
    }
}

return 'msGiftCardsMgrProcessor';
