<?php
/** @var array $options */

$xpdo = null;
if (isset($modx) && $modx instanceof modX) {
    $xpdo = $modx;
} elseif (isset($object) && $object instanceof xPDOObject) {
    $xpdo = $object->xpdo;
} elseif (isset($transport) && isset($transport->xpdo)) {
    $xpdo = $transport->xpdo;
}

if (!$xpdo) {
    return false;
}

$action = isset($options[xPDOTransport::PACKAGE_ACTION]) ? $options[xPDOTransport::PACKAGE_ACTION] : null;
if ($action !== xPDOTransport::ACTION_INSTALL && $action !== xPDOTransport::ACTION_UPGRADE) {
    return true;
}

$setting = $xpdo->getObject('modSystemSetting', ['key' => 'msgiftcards_certificate_token_key']);
if (!$setting) {
    $setting = $xpdo->newObject('modSystemSetting');
    if (!$setting) {
        return false;
    }

    $setting->fromArray([
        'key' => 'msgiftcards_certificate_token_key',
        'namespace' => 'msgiftcards',
        'area' => 'security',
        'xtype' => 'textfield',
        'value' => '',
    ], '', true, true);
}

$value = trim((string)$setting->get('value'));
if ($value !== '') {
    return true;
}

try {
    $value = bin2hex(random_bytes(32));
} catch (Exception $e) {
    $value = sha1(uniqid('msgiftcards', true) . microtime(true));
}

$setting->set('value', $value);
$setting->save();

return true;
