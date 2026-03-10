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

if (
    $options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_INSTALL
    && $options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_UPGRADE
) {
    return true;
}

$corePath = $xpdo->getOption('msgiftcards_core_path', null, $xpdo->getOption('core_path') . 'components/msgiftcards/');
$assetsPath = $xpdo->getOption('msgiftcards_assets_path', null, $xpdo->getOption('assets_path') . 'components/msgiftcards/');

$trash = [
    $corePath . 'msgiftcards',
    $corePath . 'msgiftcards-core',
    $assetsPath . 'msgiftcards',
    $assetsPath . 'msgiftcards-assets',
];

foreach ($trash as $path) {
    if (is_dir($path)) {
        $xpdo->cacheManager->deleteTree($path, ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]);
    }
}

return true;
