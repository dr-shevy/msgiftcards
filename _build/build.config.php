<?php

define('PKG_NAME', 'msGiftCards');
define('PKG_NAME_LOWER', 'msgiftcards');
define('PKG_VERSION', '1.2.2');
define('PKG_RELEASE', 'pl');

define('MODX_BASE_PATH', dirname(__DIR__) . '/');
define('MODX_BASE_URL', '/');
define('MODX_CORE_PATH', MODX_BASE_PATH . 'core/');
define('MODX_CONFIG_KEY', 'config');

$sources = [
    'root' => dirname(dirname(__DIR__)) . '/',
    'build' => dirname(__FILE__) . '/',
    'data' => dirname(__FILE__) . '/data/',
    'resolvers' => dirname(__FILE__) . '/resolvers/',
    'source_core' => dirname(__DIR__) . '/core/components/' . PKG_NAME_LOWER . '/',
    'source_assets' => dirname(__DIR__) . '/assets/components/' . PKG_NAME_LOWER . '/',
    'docs' => dirname(__DIR__) . '/core/components/' . PKG_NAME_LOWER . '/docs/',
    'packages' => dirname(__DIR__) . '/core/packages/',
];

$modx = null;
$builder = null;



