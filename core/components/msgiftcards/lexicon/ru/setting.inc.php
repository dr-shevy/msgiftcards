<?php
$_lang['area_msgiftcards_main'] = 'Основные';
$_lang['area_msgiftcards_code'] = 'Генерация кода';

$_lang['setting_msgiftcards_enabled'] = 'Включить msGiftCards';
$_lang['setting_msgiftcards_enabled_desc'] = 'Глобально включает или отключает логику подарочных сертификатов.';

$_lang['setting_msgiftcards_nominal_option'] = 'Ключ опции номинала';
$_lang['setting_msgiftcards_nominal_option_desc'] = 'Ключ опции позиции заказа, в которой хранится номинал сертификата.';

$_lang['setting_msgiftcards_default_currency'] = 'Валюта по умолчанию';
$_lang['setting_msgiftcards_default_currency_desc'] = 'Валюта сертификата по умолчанию для формы создания в менеджере и автоматически сгенерированных сертификатов.';

$_lang['setting_msgiftcards_code_mask'] = 'Маска кода сертификата';
$_lang['setting_msgiftcards_code_mask_desc'] = 'Маска для автоматической генерации кода сертификата, например [a-zA-Z0-9]{12}.';

$_lang['setting_msgiftcards_certificate_lifetime_days'] = 'Срок действия сертификата (дней)';
$_lang['setting_msgiftcards_certificate_lifetime_days_desc'] = 'Количество дней до истечения срока сертификата. Укажите 0, чтобы отключить срок действия.';

$_lang['setting_msgiftcards_generate_status_id'] = 'ID статуса генерации';
$_lang['setting_msgiftcards_generate_status_id_desc'] = 'ID статуса заказа miniShop2, при котором генерируется код сертификата.';

$_lang['setting_msgiftcards_paid_status_id'] = 'ID статуса списания';
$_lang['setting_msgiftcards_paid_status_id_desc'] = 'ID статуса заказа miniShop2, при котором выполняется списание сертификата.';

$_lang['setting_msgiftcards_cancel_status_id'] = 'ID статуса отмены';
$_lang['setting_msgiftcards_cancel_status_id_desc'] = 'ID статуса заказа miniShop2, при котором выполняется возврат списанных средств на баланс сертификата и его включение. Укажите 0, чтобы отключить возврат при отмене.';

$_lang['setting_msgiftcards_gift_payment_id'] = 'ID оплаты Подарочный сертификат';
$_lang['setting_msgiftcards_gift_payment_id_desc'] = 'ID способа оплаты miniShop2, который автоматически выбирается, если подарочный сертификат полностью покрывает сумму заказа (итог = 0). При установке/обновлении дополнения способ оплаты "Подарочный сертификат" создается автоматически.';