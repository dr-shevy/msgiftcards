<?php
/** @var modX $modx */
define('MODX_API_MODE', true);

$index = dirname(__DIR__, 3) . '/index.php';
if (!file_exists($index)) {
    die('index.php not found');
}

require_once $index;

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

$ctx = !empty($_REQUEST['ctx']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_REQUEST['ctx']) : 'mgr';
$modx->switchContext($ctx);

$corePath = $modx->getOption('msgiftcards_core_path', null, $modx->getOption('core_path') . 'components/msgiftcards/');
$msGiftCards = $modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
if (!$msGiftCards) {
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode([
        'success' => false,
        'message' => 'Could not load msGiftCards service.',
        'data' => [],
    ]));
}

$path = $msGiftCards->config['processorsPath'] . 'mgr/';
$processor = !empty($_REQUEST['action']) ? (string)$_REQUEST['action'] : '';

$response = $modx->runProcessor($processor, $_REQUEST, [
    'processors_path' => $path,
]);

if (!$response) {
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode([
        'success' => false,
        'message' => 'Processor response is empty.',
        'data' => [],
    ]));
}

$result = $response->getResponse();
if (is_array($result)) {
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($result));
}

if (is_string($result)) {
    $trimmed = ltrim($result);
    if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
        header('Content-Type: application/json; charset=utf-8');
        exit($result);
    }
}

if ($response instanceof modProcessorResponse) {
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode([
        'success' => !$response->isError(),
        'message' => $response->getMessage(),
        'data' => $response->getObject(),
    ]));
}

header('Content-Type: application/json; charset=utf-8');
exit(json_encode([
    'success' => false,
    'message' => 'Unexpected processor response format.',
    'data' => [],
]));
