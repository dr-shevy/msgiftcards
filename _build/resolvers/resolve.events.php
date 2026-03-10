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

/** @var modPlugin $plugin */
$plugin = $xpdo->getObject('modPlugin', ['name' => 'msGiftCards']);
if (!$plugin) {
    return true;
}

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

foreach ($events as $eventName => $priority) {
    $criteria = [
        'pluginid' => (int)$plugin->get('id'),
        'event' => $eventName,
    ];

    /** @var modPluginEvent $pluginEvent */
    $pluginEvent = $xpdo->getObject('modPluginEvent', $criteria);
    if (!$pluginEvent) {
        $pluginEvent = $xpdo->newObject('modPluginEvent');
        $pluginEvent->fromArray($criteria, '', true, true);
    }

    $pluginEvent->set('priority', (int)$priority);
    if ((int)$pluginEvent->get('propertyset') !== 0) {
        $pluginEvent->set('propertyset', 0);
    }
    $pluginEvent->save();
}

return true;
