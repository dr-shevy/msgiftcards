-- msgiftcards full cleanup (MODX)
-- If your table prefix is not `modx_`, replace it below.

SET FOREIGN_KEY_CHECKS = 0;

-- Remove installed package records
DELETE tpv
FROM modx_transport_package_vehicles tpv
INNER JOIN modx_transport_packages tp ON tp.id = tpv.package
WHERE tp.package_name = 'msgiftcards';

DELETE FROM modx_transport_packages
WHERE package_name = 'msgiftcards';

-- Remove manager/action/namespace links
DELETE FROM modx_actions WHERE namespace = 'msgiftcards';
DELETE FROM modx_menu WHERE namespace = 'msgiftcards';
DELETE FROM modx_namespaces WHERE name = 'msgiftcards';

-- Remove system settings
DELETE FROM modx_system_settings
WHERE namespace = 'msgiftcards' OR `key` LIKE 'msgiftcards_%';

-- Remove elements created by package
DELETE spe
FROM modx_site_plugin_events spe
INNER JOIN modx_site_plugins sp ON sp.id = spe.pluginid
WHERE sp.name = 'msGiftCards';

DELETE FROM modx_site_plugins
WHERE name = 'msGiftCards';

DELETE FROM modx_site_snippets
WHERE name IN ('msGiftCardsField','msGiftCardsInfo','msGiftCardsOrderData');

DELETE FROM modx_site_chunks
WHERE name IN ('msGiftCards.field','msGiftCards.info');

DELETE FROM modx_categories
WHERE category = 'msGiftCards';

-- Drop addon tables (this removes all certificate/redemption data)
DROP TABLE IF EXISTS modx_msgiftcards_redemptions;
DROP TABLE IF EXISTS modx_msgiftcards_certificates;

SET FOREIGN_KEY_CHECKS = 1;
