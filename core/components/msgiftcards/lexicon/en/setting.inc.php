<?php
$_lang['area_msgiftcards_main'] = 'Main';
$_lang['area_msgiftcards_code'] = 'Code generation';
$_lang['area_msgiftcards_security'] = 'Security';
$_lang['area_msgiftcards_pdf'] = 'PDF';

$_lang['setting_msgiftcards_enabled'] = 'Enable msGiftCards';
$_lang['setting_msgiftcards_enabled_desc'] = 'Globally enables or disables gift certificates logic.';

$_lang['setting_msgiftcards_nominal_option'] = 'Nominal option key';
$_lang['setting_msgiftcards_nominal_option_desc'] = 'Order product option key that stores gift certificate nominal.';

$_lang['setting_msgiftcards_default_currency'] = 'Default currency';
$_lang['setting_msgiftcards_default_currency_desc'] = 'Default certificate currency for manager create form and auto-generated certificates.';

$_lang['setting_msgiftcards_code_mask'] = 'Certificate code mask';
$_lang['setting_msgiftcards_code_mask_desc'] = 'Mask used for automatic certificate code generation, for example [a-zA-Z0-9]{12}.';

$_lang['setting_msgiftcards_certificate_lifetime_days'] = 'Certificate lifetime (days)';
$_lang['setting_msgiftcards_certificate_lifetime_days_desc'] = 'Number of days before generated certificates expire. Set 0 to disable expiration.';

$_lang['setting_msgiftcards_generate_status_id'] = 'Generate status ID';
$_lang['setting_msgiftcards_generate_status_id_desc'] = 'miniShop2 order status ID that triggers certificate generation.';

$_lang['setting_msgiftcards_paid_status_id'] = 'Redeem status ID';
$_lang['setting_msgiftcards_paid_status_id_desc'] = 'miniShop2 order status ID that triggers certificate redemption (write-off).';

$_lang['setting_msgiftcards_cancel_status_id'] = 'Cancel status ID';
$_lang['setting_msgiftcards_cancel_status_id_desc'] = 'miniShop2 order status ID that triggers gift certificate refund (return amount to certificate balance and enable it). Set 0 to disable refunds on cancel.';

$_lang['setting_msgiftcards_gift_payment_id'] = 'Gift certificate payment ID';
$_lang['setting_msgiftcards_gift_payment_id_desc'] = 'miniShop2 payment method ID used automatically when gift certificate fully covers order total (final cost = 0). The payment method is auto-created on install/upgrade with name "Подарочный сертификат".';
$_lang['setting_msgiftcards_certificate_token_key'] = 'Certificate token secret';
$_lang['setting_msgiftcards_certificate_token_key_desc'] = 'Secret key used to encrypt and sign protected links to the gift certificate PDF page. It is generated automatically on first install and must stay unchanged on updates.';
$_lang['setting_msgiftcards_certificate_pdf_paper'] = 'Certificate PDF paper size';
$_lang['setting_msgiftcards_certificate_pdf_paper_desc'] = 'Paper size for generated certificate PDF. Recommended values: A4, A5, Letter.';
$_lang['setting_msgiftcards_certificate_pdf_orientation'] = 'Certificate PDF orientation';
$_lang['setting_msgiftcards_certificate_pdf_orientation_desc'] = 'Page orientation for generated certificate PDF. Allowed values: portrait or landscape.';

$_lang['setting_msgiftcards_frontend_css'] = 'Frontend CSS path';
$_lang['setting_msgiftcards_frontend_css_desc'] = 'URL or path to CSS for the gift certificate apply field and message states (success/error). Default: {assets_url}components/msgiftcards/css/web/default.css.';
