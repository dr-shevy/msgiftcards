<?php
require dirname(__DIR__) . '/config.core.php';
require MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
$ns = $modx->getObject('modNamespace', ['name' => 'msgiftcards']);
if(!$ns){echo "ns:not_found\n"; exit;}
echo "name=".$ns->get('name')."\n";
echo "path=".$ns->get('path')."\n";
echo "assets_path=".$ns->get('assets_path')."\n";
echo "resolved_path=".$ns->getCorePath()."\n";
