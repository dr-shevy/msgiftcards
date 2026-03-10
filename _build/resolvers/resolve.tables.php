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

$prefix = $xpdo->getOption('table_prefix');
$certificates = $prefix . 'msgiftcards_certificates';
$redemptions = $prefix . 'msgiftcards_redemptions';

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $obsoleteSettings = ['msgiftcards_code_prefix', 'msgiftcards_code_length', 'msgiftcards_redeem_status_id'];
        foreach ($obsoleteSettings as $settingKey) {
            if ($setting = $xpdo->getObject('modSystemSetting', ['key' => $settingKey])) {
                $setting->remove();
            }
        }

        $sql = [];
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$certificates}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(64) NOT NULL,
            `nominal` decimal(12,2) NOT NULL DEFAULT '0.00',
            `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
            `currency` varchar(16) NOT NULL DEFAULT 'RUB',
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `order_id` int(10) unsigned NOT NULL DEFAULT '0',
            `order_product_id` int(10) unsigned NOT NULL DEFAULT '0',
            `item_index` int(10) unsigned NOT NULL DEFAULT '1',
            `createdon` datetime DEFAULT NULL,
            `updatedon` datetime DEFAULT NULL,
            `expireson` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`),
            UNIQUE KEY `order_product_item` (`order_product_id`,`item_index`),
            KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $sql[] = "CREATE TABLE IF NOT EXISTS `{$redemptions}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `certificate_id` int(10) unsigned NOT NULL,
            `order_id` int(10) unsigned NOT NULL,
            `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `balance_after` decimal(12,2) NOT NULL DEFAULT '0.00',
            `operation` varchar(16) NOT NULL DEFAULT 'debit',
            `createdon` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `certificate_id` (`certificate_id`),
            KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        foreach ($sql as $query) {
            $xpdo->exec($query);
        }

        // Upgrade path for existing installs where expireson column is absent.
        $hasExpires = false;
        if ($stmt = $xpdo->prepare("SHOW COLUMNS FROM `{$certificates}` LIKE 'expireson'")) {
            if ($stmt->execute() && $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasExpires = true;
            }
        }
        if (!$hasExpires) {
            $xpdo->exec("ALTER TABLE `{$certificates}` ADD COLUMN `expireson` datetime DEFAULT NULL AFTER `updatedon`");
        }

        $hasBalanceAfter = false;
        if ($stmt = $xpdo->prepare("SHOW COLUMNS FROM `{$redemptions}` LIKE 'balance_after'")) {
            if ($stmt->execute() && $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasBalanceAfter = true;
            }
        }
        if (!$hasBalanceAfter) {
            $xpdo->exec("ALTER TABLE `{$redemptions}` ADD COLUMN `balance_after` decimal(12,2) NOT NULL DEFAULT '0.00' AFTER `amount`");
        }

        $hasOperation = false;
        if ($stmt = $xpdo->prepare("SHOW COLUMNS FROM `{$redemptions}` LIKE 'operation'")) {
            if ($stmt->execute() && $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasOperation = true;
            }
        }
        if (!$hasOperation) {
            $xpdo->exec("ALTER TABLE `{$redemptions}` ADD COLUMN `operation` varchar(16) NOT NULL DEFAULT 'debit' AFTER `balance_after`");
        }
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        $xpdo->exec("DROP TABLE IF EXISTS `{$redemptions}`");
        $xpdo->exec("DROP TABLE IF EXISTS `{$certificates}`");
        break;
}

return true;
