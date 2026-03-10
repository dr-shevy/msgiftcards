<?php
require dirname(__DIR__) . '/config.core.php';
require MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');

$menu = $modx->getObject('modMenu', ['namespace' => 'msgiftcards']);
if (!$menu) {
  echo "menu:not_found\n";
} else {
  echo "menu:id=" . $menu->get('id') . ", text=" . $menu->get('text') . ", action=" . $menu->get('action') . ", parent=" . $menu->get('parent') . "\n";
}

$q = $modx->newQuery('modAction');
$q->where(['namespace' => 'msgiftcards']);
$acts = $modx->getCollection('modAction', $q);
if (!$acts) {
  echo "actions:none\n";
} else {
  foreach ($acts as $a) {
    echo "action:id=" . $a->get('id') . ", controller=" . $a->get('controller') . ", namespace=" . $a->get('namespace') . ", haslayout=" . $a->get('haslayout') . "\n";
  }
}

$target = $modx->getObject('modAction', ['namespace' => 'msgiftcards', 'controller' => 'home']);
if ($target) {
  echo "target_home_action_id=" . $target->get('id') . "\n";
}
