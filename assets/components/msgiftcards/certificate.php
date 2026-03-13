<?php

$root = dirname(dirname(dirname(__DIR__)));
if (!file_exists($root . '/config.core.php')) {
    http_response_code(500);
    exit('config.core.php not found');
}

require_once $root . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var modX $modx */
$modx->initialize('web');

$corePath = $modx->getOption('msgiftcards_core_path', null, $modx->getOption('core_path') . 'components/msgiftcards/');
/** @var msGiftCards $msGiftCards */
$msGiftCards = $modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
if (!$msGiftCards || !$msGiftCards->config['enabled']) {
    http_response_code(404);
    exit('Component is disabled');
}

$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
list($ok, $orderId) = $msGiftCards->parseCertificateToken($token);
if (!$ok || (int)$orderId <= 0) {
    http_response_code(403);
    exit('Invalid token');
}

$certificate = $msGiftCards->getCertificateDataByOrderId((int)$orderId);
if (!$certificate) {
    http_response_code(404);
    exit('Certificate not found');
}

$format = strtolower(trim((string)(isset($_GET['format']) ? $_GET['format'] : 'pdf')));
$tpl = isset($_GET['tpl']) ? trim((string)$_GET['tpl']) : 'msGiftCards.certificate';
if ($tpl === '') {
    $tpl = 'msGiftCards.certificate';
}
if ($format === 'html') {
    $html = $msGiftCards->renderCertificateHtml((int)$orderId, $tpl);
    if ($html === '') {
        http_response_code(404);
        exit('Certificate template not found');
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
    exit;
}

$pdf = $msGiftCards->generateCertificatePdf((int)$orderId, $tpl);
if ($pdf === '') {
    http_response_code(500);
    exit('Could not generate PDF');
}

$filename = 'gift-certificate-' . preg_replace('/[^A-Za-z0-9\-_]+/', '-', (string)$certificate['code']) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdf));
header('Content-Disposition: inline; filename="' . $filename . '"');
echo $pdf;
exit;
