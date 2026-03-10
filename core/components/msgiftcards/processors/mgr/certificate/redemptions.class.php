<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsCertificateRedemptionsProcessor extends msGiftCardsMgrProcessor
{
    public function process()
    {
        $certificateId = (int)$this->getProperty('certificate_id', 0);
        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));

        if ($certificateId <= 0) {
            return $this->outputArray([], 0);
        }

        $totalStmt = $this->modx->prepare('SELECT COUNT(*) FROM ' . $this->tableRedemptions . ' WHERE certificate_id = :certificate_id');
        if (!$totalStmt) {
            return $this->failure('Could not prepare count query');
        }
        $totalStmt->bindValue(':certificate_id', $certificateId, PDO::PARAM_INT);
        if (!$totalStmt->execute()) {
            return $this->failure('Could not execute count query');
        }
        $total = (int)$totalStmt->fetchColumn();

        $sql = 'SELECT id, certificate_id, order_id, amount, balance_after, operation, createdon '
            . 'FROM ' . $this->tableRedemptions . ' '
            . 'WHERE certificate_id = :certificate_id ORDER BY id DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $start . ', ' . $limit;
        }

        $stmt = $this->modx->prepare($sql);
        if (!$stmt) {
            return $this->failure('Could not prepare redemptions query');
        }
        $stmt->bindValue(':certificate_id', $certificateId, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return $this->failure('Could not execute redemptions query');
        }

        $list = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['id'] = (int)$row['id'];
            $row['certificate_id'] = (int)$row['certificate_id'];
            $row['order_id'] = (int)$row['order_id'];
            $row['amount'] = (float)$row['amount'];
            $row['balance_after'] = (float)$row['balance_after'];
            $row['operation'] = (string)$row['operation'];
            $list[] = $row;
        }

        return $this->outputArray($list, $total);
    }
}

return 'msGiftCardsCertificateRedemptionsProcessor';
