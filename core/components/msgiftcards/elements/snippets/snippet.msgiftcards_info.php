<?php
/** @var modX $modx */
$corePath = $modx->getOption('msgiftcards_core_path', null, $modx->getOption('core_path') . 'components/msgiftcards/');
/** @var msGiftCards $msGiftCards */
$msGiftCards = $modx->getService('msgiftcards', 'msGiftCards', $corePath . 'model/msgiftcards/');
if (!$msGiftCards || !$msGiftCards->config['enabled']) {
    return '';
}

$modx->lexicon->load('msgiftcards:default');

$providedCode = isset($code) ? trim((string)$code) : '';
$providedCurrency = isset($currency) ? trim((string)$currency) : '';
$providedNominal = isset($nominal) ? (float)$nominal : null;
$providedBalance = isset($balance) ? (float)$balance : null;
$providedWriteoff = isset($writeoff) ? (float)$writeoff : null;
$providedRemain = isset($balance_after) ? (float)$balance_after : null;

$tpl = isset($tpl) && trim((string)$tpl) !== '' ? trim((string)$tpl) : 'msGiftCards.info';
$asBody = !empty($asBody);

$formatAmount = function ($value) {
    $formatted = number_format((float)$value, 2, '.', '');
    $formatted = rtrim($formatted, '0');
    $formatted = rtrim($formatted, '.');

    return $formatted === '' ? '0' : $formatted;
};

if ($providedCode !== '') {
    $defaultCurrency = trim((string)$modx->getOption('msgiftcards_default_currency', null, $modx->getOption('ms2_frontend_currency', null, '')));
    $currencyValue = $providedCurrency !== '' ? $providedCurrency : $defaultCurrency;
    $nominalValue = $providedNominal !== null ? $providedNominal : 0.0;
    $balanceValue = $providedBalance !== null ? $providedBalance : 0.0;
    $writeoffValue = $providedWriteoff !== null ? max(0.0, $providedWriteoff) : 0.0;
    $remainValue = $providedRemain !== null ? max(0.0, $providedRemain) : max(0.0, $balanceValue - $writeoffValue);
} else {
    $certificate = $msGiftCards->getCheckoutCertificate();
    if (!$certificate) {
        return '';
    }
    $state = $msGiftCards->getCheckoutState();
    $currencyValue = (string)$certificate['currency'];
    $nominalValue = (float)$certificate['nominal'];
    $balanceValue = (float)$certificate['balance'];
    $writeoffValue = isset($state['discount']) ? max(0.0, (float)$state['discount']) : 0.0;
    $remainValue = max(0.0, $balanceValue - $writeoffValue);
    $providedCode = (string)$certificate['code'];
}

$placeholders = [
    'code' => htmlentities((string)$providedCode, ENT_QUOTES, 'UTF-8'),
    'balance' => $formatAmount($balanceValue),
    'nominal' => $formatAmount($nominalValue),
    'writeoff' => $formatAmount($writeoffValue),
    'balance_after' => $formatAmount($remainValue),
    'currency' => htmlentities((string)$currencyValue, ENT_QUOTES, 'UTF-8'),
];

$output = $msGiftCards->renderChunk($tpl, $placeholders);
if (is_string($output) && $output !== '') {
    return $asBody
        ? $output
        : '<div data-ms2giftcards-info-block>' . $output . '</div>';
}

return '';
