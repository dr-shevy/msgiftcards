<?php
/** @var modX $modx */
$corePath = $modx->getOption('msgiftcards_core_path', null, $modx->getOption('core_path') . 'components/msgiftcards/');
/** @var msGiftCards $msGiftCards */
$msGiftCards = $modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
if (!$msGiftCards || !$msGiftCards->config['enabled']) {
    return '';
}

$orderId = (int)$modx->getOption('order_id', $scriptProperties, $modx->getOption('orderId', $scriptProperties, 0));
if ($orderId <= 0) {
    return '';
}

$isFenomContext = function () {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 24);
    foreach ($trace as $frame) {
        if (!empty($frame['class']) && stripos((string)$frame['class'], 'Fenom') !== false) {
            return true;
        }
        if (!empty($frame['file']) && stripos((string)$frame['file'], 'fenom') !== false) {
            return true;
        }
    }

    return false;
};

$mode = strtolower(trim((string)$modx->getOption('mode', $scriptProperties, 'all')));
if (!in_array($mode, ['all', 'issued', 'redeemed'], true)) {
    $mode = 'all';
}

$includeIssuedRedemptions = (bool)$modx->getOption('includeIssuedRedemptions', $scriptProperties, true);
$format = strtolower(trim((string)$modx->getOption('format', $scriptProperties, 'auto')));
if (!in_array($format, ['auto', 'json', 'array'], true)) {
    $format = 'auto';
}
$toPlaceholder = trim((string)$modx->getOption('toPlaceholder', $scriptProperties, ''));

$tableCertificates = $msGiftCards->table('msgiftcards_certificates');
$tableRedemptions = $msGiftCards->table('msgiftcards_redemptions');

$issued = [];
if ($mode === 'all' || $mode === 'issued') {
    $sql = 'SELECT id, code, nominal, balance, currency, active, order_id, order_product_id, item_index, createdon, updatedon, expireson '
        . 'FROM ' . $tableCertificates . ' WHERE order_id = :order_id ORDER BY id ASC';
    $stmt = $modx->prepare($sql);
    if ($stmt) {
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $issued = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    if ($includeIssuedRedemptions && !empty($issued)) {
        foreach ($issued as &$certificateRow) {
            $certificateId = (int)$certificateRow['id'];
            $redemptionRows = [];

            $rs = $modx->prepare(
                'SELECT id, certificate_id, order_id, amount, balance_after, operation, createdon '
                . 'FROM ' . $tableRedemptions . ' WHERE certificate_id = :certificate_id ORDER BY id ASC'
            );
            if ($rs) {
                $rs->bindValue(':certificate_id', $certificateId, PDO::PARAM_INT);
                if ($rs->execute()) {
                    $redemptionRows = $rs->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            foreach ($redemptionRows as &$redemptionRow) {
                $redemptionRow['id'] = (int)$redemptionRow['id'];
                $redemptionRow['certificate_id'] = (int)$redemptionRow['certificate_id'];
                $redemptionRow['order_id'] = (int)$redemptionRow['order_id'];
                $redemptionRow['amount'] = number_format((float)$redemptionRow['amount'], 2, '.', '');
                $redemptionRow['balance_after'] = number_format((float)$redemptionRow['balance_after'], 2, '.', '');
                $redemptionRow['operation'] = (string)$redemptionRow['operation'];
            }
            unset($redemptionRow);

            $certificateRow['redemptions'] = $redemptionRows;
            $certificateRow['redemptions_count'] = count($redemptionRows);

            $certificateRow['id'] = (int)$certificateRow['id'];
            $certificateRow['nominal'] = number_format((float)$certificateRow['nominal'], 2, '.', '');
            $certificateRow['balance'] = number_format((float)$certificateRow['balance'], 2, '.', '');
            $certificateRow['active'] = (int)$certificateRow['active'];
            $certificateRow['order_id'] = (int)$certificateRow['order_id'];
            $certificateRow['order_product_id'] = (int)$certificateRow['order_product_id'];
            $certificateRow['item_index'] = (int)$certificateRow['item_index'];
        }
        unset($certificateRow);
    } elseif (!empty($issued)) {
        foreach ($issued as &$certificateRow) {
            $certificateRow['id'] = (int)$certificateRow['id'];
            $certificateRow['nominal'] = number_format((float)$certificateRow['nominal'], 2, '.', '');
            $certificateRow['balance'] = number_format((float)$certificateRow['balance'], 2, '.', '');
            $certificateRow['active'] = (int)$certificateRow['active'];
            $certificateRow['order_id'] = (int)$certificateRow['order_id'];
            $certificateRow['order_product_id'] = (int)$certificateRow['order_product_id'];
            $certificateRow['item_index'] = (int)$certificateRow['item_index'];
        }
        unset($certificateRow);
    }
}

$redeemed = [];
if ($mode === 'all' || $mode === 'redeemed') {
    $sql = 'SELECT r.id, r.certificate_id, r.order_id, r.amount, r.balance_after, r.operation, r.createdon, '
        . 'c.code, c.nominal, c.balance, c.currency, c.active, c.expireson '
        . 'FROM ' . $tableRedemptions . ' r '
        . 'LEFT JOIN ' . $tableCertificates . ' c ON c.id = r.certificate_id '
        . 'WHERE r.order_id = :order_id ORDER BY r.id ASC';
    $stmt = $modx->prepare($sql);
    if ($stmt) {
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $redeemed = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    foreach ($redeemed as &$row) {
        $row['id'] = (int)$row['id'];
        $row['certificate_id'] = (int)$row['certificate_id'];
        $row['order_id'] = (int)$row['order_id'];
        $row['amount'] = number_format((float)$row['amount'], 2, '.', '');
        $row['balance_after'] = number_format((float)$row['balance_after'], 2, '.', '');
        $row['operation'] = (string)$row['operation'];
        $row['nominal'] = number_format((float)$row['nominal'], 2, '.', '');
        $row['balance'] = number_format((float)$row['balance'], 2, '.', '');
        $row['active'] = (int)$row['active'];
    }
    unset($row);
}

$result = [
    'order_id' => $orderId,
    'mode' => $mode,
    'issued' => $issued,
    'issued_count' => count($issued),
    'redeemed' => $redeemed,
    'redeemed_count' => count($redeemed),
    'has_issued' => !empty($issued) ? 1 : 0,
    'has_redeemed' => !empty($redeemed) ? 1 : 0,
];

if ($toPlaceholder !== '') {
    $modx->setPlaceholder($toPlaceholder, $result);
}

if ($format === 'array') {
    return $result;
}

if ($format === 'auto' && $isFenomContext()) {
    return $result;
}

return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
