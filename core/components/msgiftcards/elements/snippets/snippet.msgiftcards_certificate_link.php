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

$token = $msGiftCards->generateCertificateToken($orderId);
if ($token === '') {
    return '';
}

$params = [];
$format = strtolower(trim((string)$modx->getOption('format', $scriptProperties, 'pdf')));
if (in_array($format, ['pdf', 'html'], true) && $format !== 'pdf') {
    $params['format'] = $format;
}

$tpl = trim((string)$modx->getOption('tpl', $scriptProperties, ''));
if ($tpl !== '') {
    $params['tpl'] = $tpl;
}

$url = $msGiftCards->getCertificateUrl($token, $params);
$toPlaceholder = trim((string)$modx->getOption('toPlaceholder', $scriptProperties, ''));
if ($toPlaceholder !== '') {
    $modx->setPlaceholder($toPlaceholder, $url);
}

return $url;
