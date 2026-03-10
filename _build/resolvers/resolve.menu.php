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

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $action = $xpdo->getObject('modAction', [
            'namespace' => 'msgiftcards',
            'controller' => 'index',
        ]);
        if (!$action) {
            $oldAction = $xpdo->getObject('modAction', [
                'namespace' => 'msgiftcards',
                'controller' => 'certificates',
            ]);
            if ($oldAction) {
                $oldAction->set('controller', 'index');
                $oldAction->save();
                $action = $oldAction;
            }
        }
        if (!$action) {
            $oldAction = $xpdo->getObject('modAction', [
                'namespace' => 'msgiftcards',
                'controller' => 'home',
            ]);
            if ($oldAction) {
                $oldAction->set('controller', 'index');
                $oldAction->save();
                $action = $oldAction;
            }
        }
        if (!$action) {
            $action = $xpdo->newObject('modAction');
            $action->fromArray([
                'namespace' => 'msgiftcards',
                'controller' => 'index',
                'haslayout' => 1,
                'lang_topics' => 'msgiftcards:default',
                'assets' => '',
            ], '', true, true);
            $action->save();
        }

        $menu = $xpdo->getObject('modMenu', ['namespace' => 'msgiftcards']);
        if (!$menu) {
            $menu = $xpdo->newObject('modMenu');
        }
        $menu->fromArray([
            'text' => 'msgiftcards',
            'parent' => 'components',
            'description' => 'msgiftcards',
            'action' => $action ? $action->get('id') : 0,
            'namespace' => 'msgiftcards',
            'menuindex' => 0,
            'params' => '',
            'handler' => '',
        ], '', true, true);
        $menu->save();
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        if ($menu = $xpdo->getObject('modMenu', ['namespace' => 'msgiftcards'])) {
            $menu->remove();
        }
        if ($action = $xpdo->getObject('modAction', ['namespace' => 'msgiftcards', 'controller' => 'index'])) {
            $action->remove();
        }
        if ($action = $xpdo->getObject('modAction', ['namespace' => 'msgiftcards', 'controller' => 'home'])) {
            $action->remove();
        }
        if ($action = $xpdo->getObject('modAction', ['namespace' => 'msgiftcards', 'controller' => 'certificates'])) {
            $action->remove();
        }
        break;
}

return true;
