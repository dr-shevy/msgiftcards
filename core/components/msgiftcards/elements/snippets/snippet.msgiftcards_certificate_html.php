<?php
/** @var modX $modx */
$corePath = $modx->getOption('msgiftcards_core_path', null, $modx->getOption('core_path') . 'components/msgiftcards/');
/** @var msGiftCards $msGiftCards */
$msGiftCards = $modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
if (!$msGiftCards || !$msGiftCards->config['enabled']) {
    return '';
}

$orderId = (int)$modx->getOption('order_id', $scriptProperties, $modx->getOption('orderId', $scriptProperties, 0));
$token = trim((string)$modx->getOption('token', $scriptProperties, ''));
if ($orderId <= 0 && $token !== '') {
    list($ok, $parsedOrderId) = $msGiftCards->parseCertificateToken($token);
    if ($ok) {
        $orderId = (int)$parsedOrderId;
    }
}
if ($orderId <= 0) {
    return '';
}

$tpl = trim((string)$modx->getOption('tpl', $scriptProperties, 'msGiftCards.certificate'));
if ($tpl === '') {
    $tpl = 'msGiftCards.certificate';
}

$toPlaceholder = trim((string)$modx->getOption('toPlaceholder', $scriptProperties, ''));
$html = $msGiftCards->renderCertificateHtml($orderId, $tpl);
if ($html === '') {
    return '';
}

if ($toPlaceholder !== '') {
    $modx->setPlaceholder($toPlaceholder, $html);
}

return $html;
