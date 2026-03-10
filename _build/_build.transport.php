<?php
require_once dirname(__FILE__) . '/build.config.php';

require_once dirname(__DIR__) . '/core/model/modx/modx.class.php';
require_once dirname(__DIR__) . '/core/model/modx/transport/modpackagebuilder.class.php';

if (!function_exists('msGiftCardsBuildPrepareCode')) {
    function msGiftCardsBuildPrepareCode($path)
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return '';
        }

        // Remove UTF-8 BOM and optional PHP wrappers for MODX elements stored in DB.
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = preg_replace('/^\s*<\?php\s*/i', '', $content);
        $content = preg_replace('/\s*\?>\s*$/', '', $content);

        return trim($content);
    }
}

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');
$builder->setPackageAttributes([
    'license' => file_exists($sources['docs'] . 'license.txt') ? file_get_contents($sources['docs'] . 'license.txt') : '',
    'readme' => file_exists($sources['docs'] . 'readme.txt') ? file_get_contents($sources['docs'] . 'readme.txt') : '',
    'changelog' => file_exists($sources['docs'] . 'changelog.txt') ? file_get_contents($sources['docs'] . 'changelog.txt') : '',
]);

$category = $modx->newObject('modCategory');
$category->fromArray([
    'id' => 1,
    'category' => PKG_NAME,
], '', true, true);

$plugin = $modx->newObject('modPlugin');
$plugin->fromArray([
    'id' => 1,
    'name' => 'msGiftCards',
    'description' => 'Gift certificates for miniShop2 checkout and order lifecycle.',
    'plugincode' => msGiftCardsBuildPrepareCode($sources['source_core'] . 'elements/plugins/plugin.msgiftcards.php'),
    'disabled' => 0,
], '', true, true);

$events = [
    'msOnAddToCart' => 1000,
    'msOnRemoveFromCart' => 1000,
    'msOnChangeInCart' => 1000,
    'msOnGetStatusCart' => 1000,
    'msOnSubmitOrder' => 0,
    'msOnGetOrderCost' => 1000,
    'msOnBeforeCreateOrder' => 1000,
    'msOnChangeOrderStatus' => 1000,
    'msOnEmptyCart' => 1000,
];
$pluginEvents = [];
foreach ($events as $eventName => $priority) {
    $pluginEvent = $modx->newObject('modPluginEvent');
    $pluginEvent->fromArray([
        'event' => $eventName,
        'priority' => $priority,
        'propertyset' => 0,
    ], '', true, true);
    $pluginEvents[] = $pluginEvent;
}
if (!empty($pluginEvents)) {
    $plugin->addMany($pluginEvents);
}

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray([
    'id' => 1,
    'name' => 'msGiftCardsField',
    'description' => 'Checkout field for entering gift certificate code.',
    'snippet' => msGiftCardsBuildPrepareCode($sources['source_core'] . 'elements/snippets/snippet.msgiftcards_field.php'),
], '', true, true);
$snippet->setProperties([
    'tpl' => [
        'name' => 'tpl',
        'desc' => 'msgiftcards_prop_field_tpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'msGiftCards.field',
        'lexicon' => 'msgiftcards:properties',
    ],
    'tplInfo' => [
        'name' => 'tplInfo',
        'desc' => 'msgiftcards_prop_field_tplinfo_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'msGiftCards.info',
        'lexicon' => 'msgiftcards:properties',
    ],
    'infoSnippet' => [
        'name' => 'infoSnippet',
        'desc' => 'msgiftcards_prop_field_infosnippet_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'msGiftCardsInfo',
        'lexicon' => 'msgiftcards:properties',
    ],
]);

$snippetInfo = $modx->newObject('modSnippet');
$snippetInfo->fromArray([
    'id' => 2,
    'name' => 'msGiftCardsInfo',
    'description' => 'Displays currently applied gift certificate balance.',
    'snippet' => msGiftCardsBuildPrepareCode($sources['source_core'] . 'elements/snippets/snippet.msgiftcards_info.php'),
], '', true, true);
$snippetInfo->setProperties([
    'tpl' => [
        'name' => 'tpl',
        'desc' => 'msgiftcards_prop_info_tpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'msGiftCards.info',
        'lexicon' => 'msgiftcards:properties',
    ],
    'asBody' => [
        'name' => 'asBody',
        'desc' => 'msgiftcards_prop_info_asbody_desc',
        'type' => 'numberfield',
        'options' => '',
        'value' => 0,
        'lexicon' => 'msgiftcards:properties',
    ],
]);

$snippetOrderData = $modx->newObject('modSnippet');
$snippetOrderData->fromArray([
    'id' => 3,
    'name' => 'msGiftCardsOrderData',
    'description' => 'Returns gift certificate data by order id (issued/redeemed/all).',
    'snippet' => msGiftCardsBuildPrepareCode($sources['source_core'] . 'elements/snippets/snippet.msgiftcards_order_data.php'),
], '', true, true);
$snippetOrderData->setProperties([
    'order_id' => [
        'name' => 'order_id',
        'desc' => 'msgiftcards_prop_orderdata_order_id_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'msgiftcards:properties',
    ],
    'mode' => [
        'name' => 'mode',
        'desc' => 'msgiftcards_prop_orderdata_mode_desc',
        'type' => 'list',
        'options' => [
            ['text' => 'all', 'value' => 'all'],
            ['text' => 'issued', 'value' => 'issued'],
            ['text' => 'redeemed', 'value' => 'redeemed'],
        ],
        'value' => 'all',
        'lexicon' => 'msgiftcards:properties',
    ],
    'format' => [
        'name' => 'format',
        'desc' => 'msgiftcards_prop_orderdata_format_desc',
        'type' => 'list',
        'options' => [
            ['text' => 'auto', 'value' => 'auto'],
            ['text' => 'json', 'value' => 'json'],
            ['text' => 'array', 'value' => 'array'],
        ],
        'value' => 'auto',
        'lexicon' => 'msgiftcards:properties',
    ],
    'toPlaceholder' => [
        'name' => 'toPlaceholder',
        'desc' => 'msgiftcards_prop_orderdata_to_placeholder_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'msgiftcards:properties',
    ],
    'includeIssuedRedemptions' => [
        'name' => 'includeIssuedRedemptions',
        'desc' => 'msgiftcards_prop_orderdata_include_issued_redemptions_desc',
        'type' => 'combo-boolean',
        'options' => '',
        'value' => 1,
        'lexicon' => 'msgiftcards:properties',
    ],
]);

$chunkField = $modx->newObject('modChunk');
$chunkField->fromArray([
    'id' => 1,
    'name' => 'msGiftCards.field',
    'description' => 'Field template for certificate input.',
    'snippet' => file_get_contents($sources['source_core'] . 'elements/chunks/chunk.msgiftcards_field.tpl'),
], '', true, true);

$chunkInfo = $modx->newObject('modChunk');
$chunkInfo->fromArray([
    'id' => 2,
    'name' => 'msGiftCards.info',
    'description' => 'Info template with certificate code and remaining balance.',
    'snippet' => file_get_contents($sources['source_core'] . 'elements/chunks/chunk.msgiftcards_info.tpl'),
], '', true, true);

$elements = array($plugin, $snippet, $snippetInfo, $snippetOrderData, $chunkField, $chunkInfo);
$category->addMany($elements);

$attr = [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Plugins' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'],
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                ],
            ],
        ],
        'Snippets' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
        ],
        'Chunks' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
        ],
    ],
];

$vehicle = $builder->createVehicle($category, $attr);
$vehicle->resolve('php', [
    'source' => $sources['resolvers'] . 'resolve.tables.php',
]);
$vehicle->resolve('php', [
    'source' => $sources['resolvers'] . 'resolve.menu.php',
]);
$vehicle->resolve('php', [
    'source' => $sources['resolvers'] . 'resolve.cleanup.php',
]);
$vehicle->resolve('php', [
    'source' => $sources['resolvers'] . 'resolve.events.php',
]);
$vehicle->resolve('php', [
    'source' => $sources['resolvers'] . 'resolve.payment.php',
]);
$builder->putVehicle($vehicle);

$settings = [
    'msgiftcards_enabled' => [
        'value' => 1,
        'xtype' => 'combo-boolean',
        'area' => 'main',
    ],
    'msgiftcards_nominal_option' => [
        'value' => 'gift_nominal',
        'xtype' => 'textfield',
        'area' => 'main',
    ],
    'msgiftcards_default_currency' => [
        'value' => '₽',
        'xtype' => 'textfield',
        'area' => 'main',
    ],
    'msgiftcards_code_mask' => [
        'value' => '[a-zA-Z0-9]{12}',
        'xtype' => 'textfield',
        'area' => 'code',
    ],
    'msgiftcards_certificate_lifetime_days' => [
        'value' => 365,
        'xtype' => 'numberfield',
        'area' => 'main',
    ],
    'msgiftcards_generate_status_id' => [
        'value' => 0,
        'xtype' => 'numberfield',
        'area' => 'main',
    ],
    'msgiftcards_paid_status_id' => [
        'value' => 0,
        'xtype' => 'numberfield',
        'area' => 'main',
    ],
    'msgiftcards_cancel_status_id' => [
        'value' => 0,
        'xtype' => 'numberfield',
        'area' => 'main',
    ],
    'msgiftcards_gift_payment_id' => [
        'value' => 0,
        'xtype' => 'numberfield',
        'area' => 'main',
    ],
];

foreach ($settings as $key => $meta) {
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray([
        'key' => $key,
        'value' => $meta['value'],
        'xtype' => $meta['xtype'],
        'namespace' => PKG_NAME_LOWER,
        'area' => $meta['area'],
    ], '', true, true);

    $vehicle = $builder->createVehicle($setting, [
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    ]);
    $builder->putVehicle($vehicle);
}

$fileVehicleAttr = [
    'vehicle_class' => 'xPDOFileVehicle',
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => false,
];

$vehicle = $builder->createVehicle([
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
], $fileVehicleAttr);
$builder->putVehicle($vehicle);

$vehicle = $builder->createVehicle([
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
], $fileVehicleAttr);
$builder->putVehicle($vehicle);

$builder->pack();

$modx->log(modX::LOG_LEVEL_INFO, 'Package built: ' . PKG_NAME_LOWER . '-' . PKG_VERSION . '-' . PKG_RELEASE . '.transport.zip');




