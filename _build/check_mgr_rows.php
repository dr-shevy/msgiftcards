<?php
require dirname(__DIR__) . '/config.core.php';
require MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');

$prefix = $modx->getOption('table_prefix');

$stmt = $modx->prepare('SELECT id, text, parent, action, namespace FROM ' . $prefix . 'menus WHERE namespace = :ns');
$stmt->bindValue(':ns','msgiftcards');
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "menus:\n";
foreach($menus as $m){ echo json_encode($m, JSON_UNESCAPED_UNICODE) . "\n"; }

$stmt = $modx->prepare('SELECT id, namespace, controller, haslayout, lang_topics FROM ' . $prefix . 'actions WHERE namespace = :ns');
$stmt->bindValue(':ns','msgiftcards');
$stmt->execute();
$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "actions:\n";
foreach($actions as $a){ echo json_encode($a, JSON_UNESCAPED_UNICODE) . "\n"; }

$stmt = $modx->prepare('SELECT id, namespace, controller, haslayout, lang_topics FROM ' . $prefix . 'actions WHERE id = 1');
$stmt->execute();
$a1 = $stmt->fetch(PDO::FETCH_ASSOC);
echo "action#1:\n" . json_encode($a1, JSON_UNESCAPED_UNICODE) . "\n";
