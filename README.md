msGiftCards

RU
==================================================

1. Описание
--------------------------------------------------
msGiftCards - дополнение для MODX Revolution + miniShop2 для продажи, применения, учета и управления подарочными сертификатами.

Ключевые возможности:
- Генерация сертификатов по статусу заказа (`msgiftcards_generate_status_id`).
- Применение сертификата на оформлении заказа.
- Частичное списание: списывается `min(сумма заказа, баланс сертификата)`.
- Автоматический пересчет сертификата при изменении корзины.
- Списание по статусу заказа (`msgiftcards_paid_status_id`).
- Возврат суммы на сертификат при отмене заказа (`msgiftcards_cancel_status_id`).
- Управление сертификатами и историей операций в менеджере MODX.
- Генерация защищенной ссылки на PDF сертификата.
- Генерация PDF из HTML-чанка через встроенную библиотеку `dompdf`.

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
3. Настройте системные параметры в namespace `msgiftcards`.

4. Системные настройки
--------------------------------------------------
Основные:
- `msgiftcards_enabled` - глобальное включение дополнения.
- `msgiftcards_nominal_option` - ключ опции товара с номиналом сертификата.
- `msgiftcards_default_currency` - валюта сертификата по умолчанию.
- `msgiftcards_certificate_lifetime_days` - срок действия сертификата в днях (`0` = без срока).
- `msgiftcards_generate_status_id` - статус заказа для генерации сертификата.
- `msgiftcards_paid_status_id` - статус заказа для списания сертификата.
- `msgiftcards_cancel_status_id` - статус заказа для возврата суммы на сертификат.
- `msgiftcards_gift_payment_id` - ID способа оплаты "Подарочный сертификат" для заказов с итогом `0`.

Генерация кода:
- `msgiftcards_code_mask` - маска генерации кода сертификата.

Безопасность:
- `msgiftcards_certificate_token_key` - секретный ключ для шифрования и подписи ссылок на PDF сертификата. Создается автоматически при первой установке и не должен меняться.

PDF:
- `msgiftcards_certificate_pdf_paper` - формат листа PDF сертификата. По умолчанию: `A5`.
- `msgiftcards_certificate_pdf_orientation` - ориентация PDF сертификата. По умолчанию: `landscape`.

5. Сниппеты
--------------------------------------------------
- `msGiftCardsField` - поле применения сертификата на checkout.
- `msGiftCardsInfo` - блок информации о примененном сертификате.
- `msGiftCardsOrderData` - данные сертификатов и операций по `order_id`.
- `msGiftCardsCertificateLink` - генерация защищенной ссылки на HTML/PDF сертификата.
- `msGiftCardsCertificateHtml` - генерация HTML сертификата по чанку.

Параметры `msGiftCardsOrderData`:
- `order_id` - ID заказа.
- `mode` - `all`, `issued`, `redeemed`.
- `format` - `auto`, `json`, `array`.
- `toPlaceholder` - сохранить результат в плейсхолдер.
- `includeIssuedRedemptions` - включать операции в блок `issued`.

Параметры `msGiftCardsCertificateLink`:
- `order_id` - ID заказа.
- `toPlaceholder` - сохранить ссылку в плейсхолдер.
- `format` - `pdf` или `html`.
- `tpl` - альтернативный чанк/@FILE шаблон для `certificate.php`.

Параметры `msGiftCardsCertificateHtml`:
- `order_id` - ID заказа.
- `token` - защищенный токен вместо открытого `order_id`.
- `tpl` - чанк или `@FILE` шаблон сертификата.
- `toPlaceholder` - сохранить HTML в плейсхолдер.

6. Чанки
--------------------------------------------------
- `msGiftCards.field`
- `msGiftCards.info`
- `msGiftCards.certificate`

`msGiftCards.certificate` используется:
- для HTML-preview сертификата;
- для PDF, который отдает `assets/components/msgiftcards/certificate.php`.

Шаблон сертификата по умолчанию:
- рассчитан под `A5 landscape`;
- использует `DejaVu Sans` для корректного PDF с кириллицей;
- правится полностью через чанк.

7. Примеры использования
--------------------------------------------------
Обычный MODX:
- `[[!msGiftCardsField]]`
- `[[!msGiftCardsInfo]]`
- `[[!msGiftCardsOrderData? &order_id=`123` &mode=`all`]]`
- `[[!msGiftCardsCertificateLink? &order_id=`123`]]`
- `[[!msGiftCardsCertificateHtml? &order_id=`123`]]`

Fenom:
```fenom
{set $pdfUrl = '!msGiftCardsCertificateLink' | snippet : ['order_id' => $order.id]}
<a href="{$pdfUrl}">Скачать PDF сертификата</a>
```

```fenom
{'!msGiftCardsCertificateHtml' | snippet : ['order_id' => $order.id]}
```

8. PDF сертификат
--------------------------------------------------
Публичная точка входа:
- `assets/components/msgiftcards/certificate.php`

Как работает:
- принимает только защищенный `token`;
- не использует открытый `order_id` в URL;
- извлекает данные сертификата по заказу;
- рендерит HTML из чанка `msGiftCards.certificate`;
- строит PDF через встроенный `dompdf`.

Короткие правила для PDF-шаблонов:
- Используйте UTF-8.
- Для кириллицы используйте `DejaVu Sans`, `DejaVu Serif` или `DejaVu Sans Mono`.
- Предпочитайте inline-стили.
- Не полагайтесь на сложный modern CSS.
- Не подключайте внешние CSS/JS.
- Избегайте `position: fixed`, сложных flex/grid-комбинаций и нестабильных web-font.
- Для изображений используйте локальные абсолютные пути внутри сайта, если они реально нужны.

9. Менеджер MODX
--------------------------------------------------
Страница `Подарочные сертификаты` содержит:
- вкладку сертификатов;
- вкладку истории операций;
- создание, редактирование, удаление, включение/отключение;
- поиск и фильтры;
- защиту от изменения/удаления сертификатов, у которых уже есть операции.

10. Таблицы БД
--------------------------------------------------
- `{prefix}msgiftcards_certificates`
- `{prefix}msgiftcards_redemptions`

11. Сборка пакета
--------------------------------------------------
Git-репозиторий ведется из папки `public`.

Команда сборки:
`php _build/_build.transport.php`

Готовый пакет:
`core/packages/msgiftcards-<version>-pl.transport.zip`


EN
==================================================

1. Overview
--------------------------------------------------
msGiftCards is a MODX Revolution + miniShop2 add-on for selling, applying, accounting and managing gift certificates.

Key features:
- Certificate generation by order status (`msgiftcards_generate_status_id`).
- Certificate apply on checkout.
- Partial writeoff: `min(order total, certificate balance)`.
- Automatic recalculation when cart changes.
- Redemption by order status (`msgiftcards_paid_status_id`).
- Refund back to certificate on cancel status (`msgiftcards_cancel_status_id`).
- Gift certificate manager page and operation history in MODX.
- Protected PDF certificate link generation.
- PDF generation from HTML chunk via bundled `dompdf`.

2. Compatibility
--------------------------------------------------
- MODX Revolution 2.x
- miniShop2
- PHP 7.4+
- Compatible with msPromoCode2 and msBonus2

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
- `msgiftcards_cancel_status_id`
- `msgiftcards_gift_payment_id`

Code generation:
- `msgiftcards_code_mask`

Security:
- `msgiftcards_certificate_token_key` - secret used to encrypt and sign certificate PDF links. Auto-generated on first install and must stay unchanged.

PDF:
- `msgiftcards_certificate_pdf_paper` - certificate PDF paper size. Default: `A5`.
- `msgiftcards_certificate_pdf_orientation` - certificate PDF page orientation. Default: `landscape`.

5. Snippets
--------------------------------------------------
- `msGiftCardsField`
- `msGiftCardsInfo`
- `msGiftCardsOrderData`
- `msGiftCardsCertificateLink`
- `msGiftCardsCertificateHtml`

`msGiftCardsOrderData` params:
- `order_id`
- `mode`: `all`, `issued`, `redeemed`
- `format`: `auto`, `json`, `array`
- `toPlaceholder`
- `includeIssuedRedemptions`

`msGiftCardsCertificateLink` params:
- `order_id`
- `toPlaceholder`
- `format`: `pdf` or `html`
- `tpl`

`msGiftCardsCertificateHtml` params:
- `order_id`
- `token`
- `tpl`
- `toPlaceholder`

6. Chunks
--------------------------------------------------
- `msGiftCards.field`
- `msGiftCards.info`
- `msGiftCards.certificate`

`msGiftCards.certificate` is used for:
- HTML certificate preview;
- PDF rendering in `assets/components/msgiftcards/certificate.php`.

Default certificate template:
- tuned for `A5 landscape`;
- uses `DejaVu Sans` for reliable Cyrillic PDF rendering;
- fully customizable via chunk.

7. Usage examples
--------------------------------------------------
Standard MODX:
- `[[!msGiftCardsField]]`
- `[[!msGiftCardsInfo]]`
- `[[!msGiftCardsOrderData? &order_id=`123` &mode=`all`]]`
- `[[!msGiftCardsCertificateLink? &order_id=`123`]]`
- `[[!msGiftCardsCertificateHtml? &order_id=`123`]]`

Fenom:
```fenom
{set $pdfUrl = '!msGiftCardsCertificateLink' | snippet : ['order_id' => $order.id]}
<a href="{$pdfUrl}">Download certificate PDF</a>
```

```fenom
{'!msGiftCardsCertificateHtml' | snippet : ['order_id' => $order.id]}
```

8. PDF certificate
--------------------------------------------------
Public endpoint:
- `assets/components/msgiftcards/certificate.php`

How it works:
- accepts only protected `token`;
- does not expose plain `order_id` in URL;
- loads issued certificate data by order;
- renders HTML from `msGiftCards.certificate`;
- converts HTML to PDF via bundled `dompdf`.

Short rules for PDF templates:
- Use UTF-8.
- For Cyrillic use `DejaVu Sans`, `DejaVu Serif` or `DejaVu Sans Mono`.
- Prefer inline styles.
- Do not rely on complex modern CSS.
- Do not load external CSS/JS.
- Avoid `position: fixed`, complex flex/grid layouts and unstable web-fonts.
- Use local assets only if really needed.

9. MODX manager
--------------------------------------------------
`Gift Certificates` page includes:
- certificates tab;
- operation history tab;
- create/update/remove/enable/disable actions;
- search and filters;
- protection against editing/removing certificates that already have operations.

10. Database tables
--------------------------------------------------
- `{prefix}msgiftcards_certificates`
- `{prefix}msgiftcards_redemptions`

11. Package build
--------------------------------------------------
Git repository root is `public`.

Build command:
`php _build/_build.transport.php`

Package output:
`core/packages/msgiftcards-<version>-pl.transport.zip`