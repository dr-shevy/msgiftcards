<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsCertificateRedemptionsAllProcessor extends msGiftCardsMgrProcessor
{
    public function process()
    {
        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $sort = trim((string)$this->getProperty('sort', 'createdon'));
        $dir = strtoupper(trim((string)$this->getProperty('dir', 'DESC')));
        $query = trim((string)$this->getProperty('query', ''));
        $orderId = (int)$this->getProperty('order_id', 0);
        $code = trim((string)$this->getProperty('code', ''));
        $dateFrom = trim((string)$this->getProperty('date_from', ''));
        $dateTo = trim((string)$this->getProperty('date_to', ''));

        $allowedSort = ['order_id', 'code', 'nominal', 'amount', 'balance_after', 'operation', 'createdon'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'createdon';
        }
        if ($dir !== 'ASC') {
            $dir = 'DESC';
        }

        $sortMap = [
            'order_id' => 'r.order_id',
            'code' => 'c.code',
            'nominal' => 'c.nominal',
            'amount' => 'r.amount',
            'balance_after' => 'r.balance_after',
            'operation' => 'r.operation',
            'createdon' => 'r.createdon',
        ];
        $sortSql = isset($sortMap[$sort]) ? $sortMap[$sort] : 'r.createdon';

        $conditions = [];
        $params = [];
        if ($orderId > 0) {
            $conditions[] = 'r.order_id = :order_id';
            $params[':order_id'] = $orderId;
        }
        if ($code !== '') {
            $conditions[] = 'c.code LIKE :code';
            $params[':code'] = '%' . $code . '%';
        }
        if ($query !== '') {
            $qParts = ['c.code LIKE :code_query'];
            $params[':code_query'] = '%' . $query . '%';
            if (preg_match('/^\d+$/', $query)) {
                $qParts[] = 'r.order_id = :order_id_query';
                $params[':order_id_query'] = (int)$query;
            }
            $conditions[] = '(' . implode(' OR ', $qParts) . ')';
        }

        if ($dateFrom !== '') {
            $fromTime = strtotime($dateFrom);
            if ($fromTime !== false) {
                $conditions[] = 'r.createdon >= :date_from';
                $params[':date_from'] = date('Y-m-d 00:00:00', $fromTime);
            }
        }
        if ($dateTo !== '') {
            $toTime = strtotime($dateTo);
            if ($toTime !== false) {
                $conditions[] = 'r.createdon <= :date_to';
                $params[':date_to'] = date('Y-m-d 23:59:59', $toTime);
            }
        }
        $where = !empty($conditions) ? (' WHERE ' . implode(' AND ', $conditions)) : '';

        $totalSql = 'SELECT COUNT(*) '
            . 'FROM ' . $this->tableRedemptions . ' r '
            . 'LEFT JOIN ' . $this->tableCertificates . ' c ON c.id = r.certificate_id'
            . $where;
        $totalStmt = $this->modx->prepare($totalSql);
        if (!$totalStmt) {
            return $this->failure('Could not prepare count query');
        }
        foreach ($params as $key => $value) {
            $paramType = in_array($key, [':order_id', ':order_id_query'], true) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $totalStmt->bindValue($key, $value, $paramType);
        }
        if (!$totalStmt->execute()) {
            return $this->failure('Could not execute count query');
        }
        $total = (int)$totalStmt->fetchColumn();

        $sql = 'SELECT r.order_id, r.amount, r.balance_after, r.operation, r.createdon, c.code, c.nominal '
            . 'FROM ' . $this->tableRedemptions . ' r '
            . 'LEFT JOIN ' . $this->tableCertificates . ' c ON c.id = r.certificate_id'
            . $where
            . ' ORDER BY ' . $sortSql . ' ' . $dir;
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $start . ', ' . $limit;
        }

        $stmt = $this->modx->prepare($sql);
        if (!$stmt) {
            return $this->failure('Could not prepare list query');
        }
        foreach ($params as $key => $value) {
            $paramType = in_array($key, [':order_id', ':order_id_query'], true) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $paramType);
        }
        if (!$stmt->execute()) {
            return $this->failure('Could not execute list query');
        }

        $list = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = [
                'order_id' => (int)$row['order_id'],
                'code' => (string)$row['code'],
                'nominal' => (float)$row['nominal'],
                'amount' => (float)$row['amount'],
                'balance_after' => (float)$row['balance_after'],
                'operation' => (string)$row['operation'],
                'createdon' => (string)$row['createdon'],
            ];
        }

        return $this->outputArray($list, $total);
    }
}

return 'msGiftCardsCertificateRedemptionsAllProcessor';
