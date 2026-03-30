<?php
/** @var modX $modx */
$corePath = $modx->getOption('msgiftcards_core_path', null, $modx->getOption('core_path') . 'components/msgiftcards/');
/** @var msGiftCards $msGiftCards */
$msGiftCards = $modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
if (!$msGiftCards || !$msGiftCards->config['enabled']) {
    return '';
}

$modx->lexicon->load('msgiftcards:default');
$state = $msGiftCards->getCheckoutState();
$currentCode = !empty($state['code']) ? $state['code'] : '';

$tpl = isset($tpl) && trim((string)$tpl) !== '' ? trim((string)$tpl) : 'msGiftCards.field';
$infoSnippet = isset($infoSnippet) && trim((string)$infoSnippet) !== '' ? trim((string)$infoSnippet) : 'msGiftCardsInfo';
$tplInfo = isset($tplInfo) && trim((string)$tplInfo) !== '' ? trim((string)$tplInfo) : 'msGiftCards.info';

$assetsUrl = $msGiftCards->config['assetsUrl'];
$defaultCssUrl = $assetsUrl . 'css/web/default.css';
$frontendCss = trim((string)$modx->getOption('msgiftcards_frontend_css', null, $defaultCssUrl));
if ($frontendCss !== '') {
    $modx->regClientCSS($frontendCss);
}
$modx->regClientStartupScript($assetsUrl . 'js/web/msgiftcards.js');
$modx->regClientStartupHTMLBlock('<script>window.msGiftCardsConfig=' . json_encode([
    'connectorUrl' => $msGiftCards->config['connectorUrl'],
    'ctx' => $msGiftCards->getContextKey(),
    'messageAppliedTemplate' => $modx->lexicon('msgiftcards_message_applied'),
    'messageRemoved' => $modx->lexicon('msgiftcards_message_removed'),
    'messageErrorGeneric' => $modx->lexicon('msgiftcards_message_error_generic'),
    'messageNetworkError' => $modx->lexicon('msgiftcards_message_network_error'),
]) . ';</script>');

$placeholders = [
    'code' => htmlentities($currentCode, ENT_QUOTES, 'UTF-8'),
    'label' => $modx->lexicon('msgiftcards_label_code'),
    'placeholder' => $modx->lexicon('msgiftcards_placeholder_code'),
    'btn_apply' => $modx->lexicon('msgiftcards_btn_apply'),
    'btn_remove' => $modx->lexicon('msgiftcards_btn_remove'),
    'info' => $msGiftCards->runSnippet($infoSnippet, ['asBody' => 1, 'tpl' => $tplInfo]),
];

$output = $msGiftCards->renderChunk($tpl, $placeholders);
return is_string($output) ? $output : '';