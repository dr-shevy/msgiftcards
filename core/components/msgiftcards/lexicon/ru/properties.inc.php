<?php
$_lang['msgiftcards_prop_field_infosnippet_desc'] = 'Имя сниппета для вывода блока информации о сертификате.';
$_lang['msgiftcards_prop_field_tpl_desc'] = 'Имя чанка разметки для msGiftCardsField.';
$_lang['msgiftcards_prop_field_tplinfo_desc'] = 'Имя чанка, передаваемого в msGiftCardsInfo как `tpl`.';

$_lang['msgiftcards_prop_info_asbody_desc'] = 'Возвращать только тело чанка без внешней обертки (`1` или `0`).';
$_lang['msgiftcards_prop_info_balance_desc'] = 'Переопределение текущего баланса сертификата.';
$_lang['msgiftcards_prop_info_balance_after_desc'] = 'Переопределение остатка после списания.';
$_lang['msgiftcards_prop_info_code_desc'] = 'Переопределение кода сертификата.';
$_lang['msgiftcards_prop_info_currency_desc'] = 'Переопределение валюты (например: RUB, USD).';
$_lang['msgiftcards_prop_info_nominal_desc'] = 'Переопределение номинала сертификата.';
$_lang['msgiftcards_prop_info_tpl_desc'] = 'Имя чанка разметки для msGiftCardsInfo.';
$_lang['msgiftcards_prop_info_writeoff_desc'] = 'Переопределение суммы списания.';

$_lang['msgiftcards_prop_orderdata_order_id_desc'] = 'ID заказа для получения данных по сертификатам.';
$_lang['msgiftcards_prop_orderdata_mode_desc'] = 'Режим выборки: `all`, `issued` или `redeemed`.';
$_lang['msgiftcards_prop_orderdata_format_desc'] = 'Формат результата: `json` или `array`.';
$_lang['msgiftcards_prop_orderdata_to_placeholder_desc'] = 'Если задано, результат сохраняется в этот MODX-плейсхолдер.';
$_lang['msgiftcards_prop_orderdata_include_issued_redemptions_desc'] = 'Включать список списаний у выпущенных сертификатов (`1`/`0`).';
$_lang['msgiftcards_prop_certificate_link_order_id_desc'] = 'ID заказа, по которому формируется защищенная ссылка на сертификат.';
$_lang['msgiftcards_prop_certificate_link_to_placeholder_desc'] = 'Если задано, сохраняет сгенерированную ссылку в указанный MODX-плейсхолдер.';
$_lang['msgiftcards_prop_certificate_link_format_desc'] = 'Формат ссылки: `pdf` или `html`.';
$_lang['msgiftcards_prop_certificate_link_tpl_desc'] = 'Необязательное имя чанка/@FILE-шаблона, передаваемое в certificate.php.';
$_lang['msgiftcards_prop_certificate_html_order_id_desc'] = 'ID заказа, по которому был выпущен сертификат.';
$_lang['msgiftcards_prop_certificate_html_token_desc'] = 'Защищенный токен, сформированный сниппетом msGiftCardsCertificateLink. Используется вместо открытого order_id.';
$_lang['msgiftcards_prop_certificate_html_tpl_desc'] = 'Имя чанка или @FILE-шаблон для HTML-разметки сертификата.';
$_lang['msgiftcards_prop_certificate_html_to_placeholder_desc'] = 'Если задано, сохраняет сгенерированный HTML сертификата в указанный MODX-плейсхолдер.';
