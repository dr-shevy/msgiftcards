msGiftCards

RU
==================================================

1. Описание
--------------------------------------------------
msGiftCards — дополнение для MODX Revolution + miniShop2 для продажи, применения и учета подарочных сертификатов.

Ключевые возможности:
- Генерация сертификатов по статусу заказа (`msgiftcards_generate_status_id`).
- Применение сертификата на оформлении заказа.
- Частичное списание: списывается `min(сумма заказа, баланс сертификата)`.
- Автопересчет данных сертификата при изменениях корзины (добавление/удаление/изменение количества).
- Списание по статусу заказа (`msgiftcards_paid_status_id`).
- Возврат средств на сертификат при отмене заказа (`msgiftcards_cancel_status_id`):
  - баланс возвращается,
  - сертификат включается, если был отключен,
  - в истории операций пишется начисление.
- Типы операций: списание (`debit`) и начисление (`credit`).

2. Совместимость
--------------------------------------------------
- MODX Revolution 2.x
- miniShop2
- PHP 7.4+
- Совместимо с msPromoCode2 и msBonus2 (без использования их namespace/классов/префиксов).

3. Установка
--------------------------------------------------
1. Установите пакет `msgiftcards-*.transport.zip` через установщик MODX.
2. Очистите кэш MODX.
3. Настройте системные параметры в namespace `msgiftcards`.

4. Системные настройки
--------------------------------------------------
Основные:
- `msgiftcards_enabled` — включить/выключить дополнение.
- `msgiftcards_nominal_option` — ключ опции товара с номиналом сертификата (по умолчанию `gift_nominal`).
- `msgiftcards_default_currency` — валюта сертификата по умолчанию.
- `msgiftcards_certificate_lifetime_days` — срок действия сертификата в днях (`0` = бессрочно).
- `msgiftcards_generate_status_id` — статус заказа, при котором генерируется сертификат.
- `msgiftcards_paid_status_id` — статус заказа, при котором выполняется списание сертификата.
- `msgiftcards_cancel_status_id` — статус заказа, при котором выполняется возврат суммы на сертификат (`0` = отключено).

Генерация кода:
- `msgiftcards_code_mask` — маска генерации кода (по умолчанию `[a-zA-Z0-9]{12}`).

5. Фронтенд
--------------------------------------------------
Сниппеты:
- `msGiftCardsField` — поле ввода/применения сертификата.
- `msGiftCardsInfo` — блок информации о примененном сертификате.
- `msGiftCardsOrderData` — данные по сертификатам для конкретного `order_id`.

Чанки:
- `msGiftCards.field`
- `msGiftCards.info`

Примеры:
- `[[!msGiftCardsField]]`
- `[[!msGiftCardsInfo]]`
- `[[!msGiftCardsOrderData? &order_id=`123` &mode=`all`]]`

6. Менеджер (CMP)
--------------------------------------------------
Вкладка «Подарочные сертификаты»:
- список сертификатов,
- создать/редактировать/удалить,
- включить/отключить,
- копирование кода,
- поиск.

Вкладка «История операций»:
- операции списаний/начислений по всем сертификатам,
- фильтры:
  - ID заказа,
  - код сертификата,
  - период даты (от/до),
  - локальная очистка + общий сброс фильтров.

Правило редактирования кода:
- если операций нет — пустой код в редактировании генерируется автоматически по маске;
- если операции есть — код менять нельзя.

7. Таблицы БД
--------------------------------------------------
- `{prefix}msgiftcards_certificates`
- `{prefix}msgiftcards_redemptions`
  - хранит `operation` (`debit`/`credit`) и `balance_after`.

8. Сборка пакета
--------------------------------------------------
Команда:
`php C:\OSPanel\home\msgiftcards\_build\_build.transport.php`

Пакет создается в:
`C:\OSPanel\home\msgiftcards\public\core\packages\`

EN
==================================================

1. Overview
--------------------------------------------------
msGiftCards is a MODX Revolution + miniShop2 add-on for selling, applying and accounting gift certificates.

Key features:
- Certificate generation by order status (`msgiftcards_generate_status_id`).
- Certificate apply at checkout.
- Partial writeoff: `min(order total, certificate balance)`.
- Live recalculation when cart changes.
- Redemption by paid status (`msgiftcards_paid_status_id`).
- Refund to certificate on order cancel (`msgiftcards_cancel_status_id`):
  - balance is returned,
  - certificate is enabled if disabled,
  - accrual operation is stored in history.
- Operation types: writeoff (`debit`) and accrual (`credit`).

2. Compatibility
--------------------------------------------------
- MODX Revolution 2.x
- miniShop2
- PHP 7.4+
- Compatible with msPromoCode2 and msBonus2 (isolated namespace/tables/processors).

3. Installation
--------------------------------------------------
1. Install `msgiftcards-*.transport.zip` via MODX Package Manager.
2. Clear MODX cache.
3. Configure system settings in `msgiftcards` namespace.

4. System settings
--------------------------------------------------
Main:
- `msgiftcards_enabled`
- `msgiftcards_nominal_option`
- `msgiftcards_default_currency`
- `msgiftcards_certificate_lifetime_days`
- `msgiftcards_generate_status_id`
- `msgiftcards_paid_status_id`
- `msgiftcards_cancel_status_id` (`0` = disabled)

Code generation:
- `msgiftcards_code_mask`

5. Frontend
--------------------------------------------------
Snippets:
- `msGiftCardsField`
- `msGiftCardsInfo`
- `msGiftCardsOrderData`

Chunks:
- `msGiftCards.field`
- `msGiftCards.info`

Examples:
- `[[!msGiftCardsField]]`
- `[[!msGiftCardsInfo]]`
- `[[!msGiftCardsOrderData? &order_id=`123` &mode=`all`]]`

6. Manager (CMP)
--------------------------------------------------
Certificates tab:
- list/create/update/remove,
- enable/disable,
- copy code,
- search.

Operation history tab:
- writeoff/accrual records across all certificates,
- filters: order ID, certificate code, date range (from/to), per-field clear + reset all.

Code update rule:
- if no operations exist — empty code in update is auto-generated by mask;
- if operations exist — code is locked.

7. Database tables
--------------------------------------------------
- `{prefix}msgiftcards_certificates`
- `{prefix}msgiftcards_redemptions` (`operation`, `balance_after`)

8. Build package
--------------------------------------------------
Command:
`php C:\OSPanel\home\msgiftcards\_build\_build.transport.php`

Package output:
`C:\OSPanel\home\msgiftcards\public\core\packages\`
