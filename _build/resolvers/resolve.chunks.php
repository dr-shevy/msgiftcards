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

$corePath = $xpdo->getOption('msgiftcards_core_path', null, $xpdo->getOption('core_path') . 'components/msgiftcards/');
$chunkDir = $corePath . 'elements/chunks/';

$map = [
    [
        'name' => 'msGiftCards.field',
        'file' => 'chunk.msgiftcards_field.tpl',
        'option' => 'msgiftcards_overwrite_chunk_field',
    ],
    [
        'name' => 'msGiftCards.info',
        'file' => 'chunk.msgiftcards_info.tpl',
        'option' => 'msgiftcards_overwrite_chunk_info',
    ],
    [
        'name' => 'msGiftCards.certificate',
        'file' => 'chunk.msgiftcards_certificate.tpl',
        'option' => 'msgiftcards_overwrite_chunk_certificate',
    ],
];

$toBool = function ($value) {
    if (is_bool($value)) {
        return $value;
    }
    if (is_numeric($value)) {
        return ((int)$value) === 1;
    }
    $value = strtolower(trim((string)$value));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
};

$category = $xpdo->getObject('modCategory', ['category' => 'msGiftCards']);
$categoryId = $category ? (int)$category->get('id') : 0;

foreach ($map as $item) {
    $chunkName = $item['name'];
    $filePath = $chunkDir . $item['file'];
    if (!is_file($filePath)) {
        continue;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        continue;
    }
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    /** @var modChunk|null $chunk */
    $chunk = $xpdo->getObject('modChunk', ['name' => $chunkName]);
    $exists = $chunk instanceof modChunk;

    if (!$exists) {
        $chunk = $xpdo->newObject('modChunk');
        $chunk->fromArray([
            'name' => $chunkName,
            'description' => '',
            'category' => $categoryId,
        ], '', true, true);
        $chunk->setContent($content);
        $chunk->save();
        continue;
    }

    $overwrite = $toBool(isset($options[$item['option']]) ? $options[$item['option']] : 0);
    if ($overwrite) {
        $chunk->setContent($content);
        $chunk->save();
    }
}

return true;