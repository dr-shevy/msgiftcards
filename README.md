msGiftCards

RU
==================================================

1. Описание
--------------------------------------------------
msGiftCards - дополнение для MODX Revolution + miniShop2 для продажи, применения и учета подарочных сертификатов.

Ключевые возможности:
- Генерация сертификата при смене статуса заказа (`msgiftcards_generate_status_id`).
- Применение сертификата на этапе оформления заказа.
- Частичное списание: списывается `min(сумма заказа, баланс сертификата)`.
- Моментальный пересчет применения сертификата при изменениях корзины (добавление, удаление, изменение количества, очистка).
- Фиксация списания по статусу оплаты (`msgiftcards_paid_status_id`).
- Возврат суммы на сертификат при отмене заказа (`msgiftcards_cancel_status_id`):
  - баланс восстанавливается,
  - отключенный сертификат автоматически включается,
  - в истории операций создается начисление.
- Типы операций: `debit` (списание) и `credit` (начисление).

2. Совместимость
--------------------------------------------------
- MODX Revolution 2.x
- miniShop2
- PHP 7.4+
- Совместимо с msPromoCode2 и msBonus2

3. Установка
--------------------------------------------------
1. Установите пакет `msgiftcards-*.transport.zip` через установщик MODX.
2. Очистите кэш MODX.
3. Настройте системные параметры `msgiftcards`.

4. Системные настройки
--------------------------------------------------
Основные:
- `msgiftcards_enabled` (по умолчанию: `1`) - включение/выключение дополнения.
- `msgiftcards_nominal_option` (по умолчанию: `gift_nominal`) - ключ опции товара с номиналом сертификата.
- `msgiftcards_default_currency` (по умолчанию: `₽`) - валюта сертификата по умолчанию.
- `msgiftcards_certificate_lifetime_days` (по умолчанию: `365`) - срок действия сертификата в днях (`0` = бессрочно).
- `msgiftcards_generate_status_id` (по умолчанию: `0`) - статус заказа для генерации сертификата (`0` = отключено).
- `msgiftcards_paid_status_id` (по умолчанию: `0`) - статус заказа для списания по сертификату (`0` = отключено).
- `msgiftcards_cancel_status_id` (по умолчанию: `0`) - статус заказа для возврата суммы на сертификат (`0` = отключено).

Генерация кода:
- `msgiftcards_code_mask` (по умолчанию: `[a-zA-Z0-9]{12}`) - маска генерации кода.

5. Фронтенд
--------------------------------------------------
Сниппеты:
- `msGiftCardsField` - форма применения сертификата.
- `msGiftCardsInfo` - информационный блок по примененному сертификату.
- `msGiftCardsOrderData` - данные сертификатов/операций по `order_id`.

Параметры `msGiftCardsOrderData`:
- `order_id` - ID заказа.
- `mode` - режим выборки: `all`, `issued`, `redeemed`.
- `format` - формат ответа: `json` или `array`.
- `toPlaceholder` - запись результата в плейсхолдер.
- `includeIssuedRedemptions` - включать ли операции в блоке `issued`.

Чанки:
- `msGiftCards.field`
- `msGiftCards.info`

Примеры:
- `[[!msGiftCardsField]]`
- `[[!msGiftCardsInfo]]`
- `[[!msGiftCardsOrderData? &order_id=`123` &mode=`all`]]`

6. Менеджер (CMP)
--------------------------------------------------
Страница `Подарочные сертификаты` содержит:
- вкладку сертификатов (создание, редактирование, удаление, включение/отключение, копирование кода, поиск),
- вкладку истории операций (списания/начисления по всем сертификатам),
- фильтры истории: ID заказа, код сертификата, дата от/до, очистка фильтров.

Правила редактирования кода сертификата:
- если операций нет, пустой код при сохранении генерируется автоматически по маске,
- если операции уже есть, код сертификата недоступен для изменения.

7. Таблицы БД
--------------------------------------------------
- `{prefix}msgiftcards_certificates`
- `{prefix}msgiftcards_redemptions`

Таблица операций хранит:
- `operation` (`debit`/`credit`),
- `amount`,
- `balance_after`,
- `createdon`.

8. Сборка пакета
--------------------------------------------------
Репозиторий ведется из папки `public` (она является корнем Git).

Команда сборки (из `public`):
`php _build/_build.transport.php`

Готовый пакет:
`core/packages/msgiftcards-<version>-pl.transport.zip`


EN
==================================================

1. Overview
--------------------------------------------------
msGiftCards is a MODX Revolution + miniShop2 add-on for selling, applying and accounting gift certificates.

Key features:
- Certificate generation on order status change (`msgiftcards_generate_status_id`).
- Applying certificate on checkout.
- Partial writeoff: `min(order total, certificate balance)`.
- Live recalculation when cart changes (add/remove/change quantity/empty cart).
- Redemption processing by paid status (`msgiftcards_paid_status_id`).
- Refund back to certificate on cancel status (`msgiftcards_cancel_status_id`):
  - balance is restored,
  - disabled certificate is auto-enabled,
  - a `credit` operation is written to history.
- Operation types: `debit` (writeoff) and `credit` (accrual/refund).

2. Compatibility
--------------------------------------------------
- MODX Revolution 2.x
- miniShop2
- PHP 7.4+
- Compatible with msPromoCode2 and msBonus2 (isolated namespace/tables/prefixes).

3. Installation
--------------------------------------------------
1. Install `msgiftcards-*.transport.zip` via MODX Package Manager.
2. Clear MODX cache.
3. Configure system settings in `msgiftcards` namespace.

4. System settings
--------------------------------------------------
Main:
- `msgiftcards_enabled` (default: `1`)
- `msgiftcards_nominal_option` (default: `gift_nominal`)
- `msgiftcards_default_currency` (default: `₽`)
- `msgiftcards_certificate_lifetime_days` (default: `365`, `0` = no expiration)
- `msgiftcards_generate_status_id` (default: `0`, disabled)
- `msgiftcards_paid_status_id` (default: `0`, disabled)
- `msgiftcards_cancel_status_id` (default: `0`, disabled)

Code generation:
- `msgiftcards_code_mask` (default: `[a-zA-Z0-9]{12}`)

5. Frontend
--------------------------------------------------
Snippets:
- `msGiftCardsField`
- `msGiftCardsInfo`
- `msGiftCardsOrderData`

`msGiftCardsOrderData` params:
- `order_id`
- `mode`: `all`, `issued`, `redeemed`
- `format`: `json` or `array`
- `toPlaceholder`
- `includeIssuedRedemptions`

Chunks:
- `msGiftCards.field`
- `msGiftCards.info`

Examples:
- `[[!msGiftCardsField]]`
- `[[!msGiftCardsInfo]]`
- `[[!msGiftCardsOrderData? &order_id=`123` &mode=`all`]]`

6. Manager (CMP)
--------------------------------------------------
`Gift Certificates` page includes:
- certificates tab (create/update/remove, enable/disable, copy code, search),
- operation history tab (debit/credit records),
- history filters: order ID, certificate code, date from/to, filter reset.

Certificate code update rules:
- if no operations exist, empty code is auto-generated by mask,
- if operations exist, certificate code is locked.

7. Database tables
--------------------------------------------------
- `{prefix}msgiftcards_certificates`
- `{prefix}msgiftcards_redemptions`

Operations table fields include:
- `operation` (`debit`/`credit`)
- `amount`
- `balance_after`
- `createdon`

8. Package build
--------------------------------------------------
Repository is maintained with `public` as Git root.

Build command (run from `public`):
`php _build/_build.transport.php`

Package output:
`core/packages/msgiftcards-<version>-pl.transport.zip`