<?php
require_once dirname(__FILE__) . '/_base.class.php';

class msGiftCardsCertificateGetListProcessor extends msGiftCardsMgrProcessor
{
    public function process()
    {
        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $sort = trim((string)$this->getProperty('sort', 'id'));
        $dir = strtoupper(trim((string)$this->getProperty('dir', 'DESC')));
        $query = trim((string)$this->getProperty('query', ''));

        $allowedSort = ['id', 'code', 'nominal', 'balance', 'currency', 'active', 'createdon', 'expireson'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'id';
        }
        if ($dir !== 'ASC') {
            $dir = 'DESC';
        }

        $where = '';
        $params = [];
        if ($query !== '') {
            $where = ' WHERE code LIKE :query';
            $params[':query'] = '%' . $query . '%';
        }

        $totalStmt = $this->modx->prepare('SELECT COUNT(*) FROM ' . $this->tableCertificates . $where);
        if (!$totalStmt) {
            return $this->failure('Could not prepare count query');
        }
        foreach ($params as $k => $v) {
            $totalStmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        if (!$totalStmt->execute()) {
            return $this->failure('Could not execute count query');
        }
        $total = (int)$totalStmt->fetchColumn();

        $sql = 'SELECT * FROM ' . $this->tableCertificates . $where . ' ORDER BY ' . $sort . ' ' . $dir;
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $start . ', ' . $limit;
        }

        $stmt = $this->modx->prepare($sql);
        if (!$stmt) {
            return $this->failure('Could not prepare list query');
        }
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        if (!$stmt->execute()) {
            return $this->failure('Could not execute list query');
        }

        $list = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list[] = $this->mapRow($row);
        }

        return $this->outputArray($list, $total);
    }
}

return 'msGiftCardsCertificateGetListProcessor';
