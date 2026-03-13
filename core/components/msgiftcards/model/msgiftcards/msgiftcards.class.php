<?php

require_once dirname(__FILE__) . '/msgiftcardspdf.class.php';

class msGiftCards
{
    /** @var modX */
    public $modx;

    /** @var array */
    public $config = [];

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;

        $corePath = $modx->getOption('msgiftcards_core_path', $config, $modx->getOption('core_path') . 'components/msgiftcards/');
        $assetsUrl = $modx->getOption('msgiftcards_assets_url', $config, $modx->getOption('assets_url') . 'components/msgiftcards/');

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'elementsPath' => $corePath . 'elements/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'action.php',
            'nominalOptionKey' => trim((string)$modx->getOption('msgiftcards_nominal_option', null, 'gift_nominal')),
            'codeMask' => trim((string)$modx->getOption('msgiftcards_code_mask', null, '[a-zA-Z0-9]{12}')),
            'defaultCurrency' => trim((string)$modx->getOption('msgiftcards_default_currency', null, '')),
            'certificateLifetimeDays' => max(0, (int)$modx->getOption('msgiftcards_certificate_lifetime_days', null, 365)),
            'enabled' => (bool)$modx->getOption('msgiftcards_enabled', null, true),
            'generateStatusId' => (int)$modx->getOption('msgiftcards_generate_status_id', null, 0),
            'paidStatusId' => (int)$modx->getOption('msgiftcards_paid_status_id', null, 0),
            'cancelStatusId' => (int)$modx->getOption('msgiftcards_cancel_status_id', null, 0),
            'giftPaymentId' => (int)$modx->getOption('msgiftcards_gift_payment_id', null, 0),
            'certificateTokenKey' => trim((string)$modx->getOption('msgiftcards_certificate_token_key', null, '')),
            'certificatePdfPaper' => trim((string)$modx->getOption('msgiftcards_certificate_pdf_paper', null, 'A4')),
            'certificatePdfOrientation' => trim((string)$modx->getOption('msgiftcards_certificate_pdf_orientation', null, 'portrait')),
            'sessionKey' => 'msgiftcards',
        ], $config);

        if ($this->config['defaultCurrency'] === '') {
            $this->config['defaultCurrency'] = trim((string)$modx->getOption('ms2_frontend_currency', null, 'RUB'));
        }
        if ($this->config['defaultCurrency'] === '') {
            $this->config['defaultCurrency'] = 'RUB';
        }

        $this->modx->lexicon->load('msgiftcards:default');
    }

    public function getCheckoutState()
    {
        if (empty($_SESSION[$this->config['sessionKey']]['checkout']) || !is_array($_SESSION[$this->config['sessionKey']]['checkout'])) {
            return [];
        }

        return $_SESSION[$this->config['sessionKey']]['checkout'];
    }

    public function getCheckoutCertificate()
    {
        $state = $this->getCheckoutState();
        if (empty($state['code'])) {
            return null;
        }

        list($ok, $payload) = $this->validateCertificateCode($state['code']);
        if (!$ok) {
            return null;
        }

        return $payload;
    }

    public function setCheckoutState(array $state)
    {
        $_SESSION[$this->config['sessionKey']]['checkout'] = $state;
    }

    public function clearCheckoutState()
    {
        unset($_SESSION[$this->config['sessionKey']]['checkout']);
    }

    public function getContextKey()
    {
        if ($this->modx->context && $this->modx->context instanceof modContext) {
            return (string)$this->modx->context->get('key');
        }

        return 'web';
    }

    public function getMsBonus2Writeoff($ctx = '')
    {
        $ctx = trim((string)$ctx);
        if ($ctx === '') {
            $ctx = $this->getContextKey();
        }

        if (empty($_SESSION['msBonus2']['writeoff']) || !is_array($_SESSION['msBonus2']['writeoff'])) {
            return 0.0;
        }

        if (!array_key_exists($ctx, $_SESSION['msBonus2']['writeoff'])) {
            return 0.0;
        }

        return max(0.0, (float)$_SESSION['msBonus2']['writeoff'][$ctx]);
    }

    public function getPdoTools()
    {
        if (!class_exists('pdoTools')) {
            return null;
        }

        return $this->modx->getService('pdoTools');
    }

    public function getCertificateUrl($token, array $params = [])
    {
        $query = array_merge([
            'token' => (string)$token,
        ], $params);

        $url = $this->config['assetsUrl'] . 'certificate.php?' . http_build_query($query);
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $siteUrl = rtrim((string)$this->modx->getOption('site_url', null, ''), '/');
        return $siteUrl . '/' . ltrim($url, '/');
    }

    public function getCertificateTokenKey()
    {
        $key = trim((string)$this->config['certificateTokenKey']);
        if ($key !== '') {
            return $key;
        }

        $siteId = (string)$this->modx->getOption('site_id', null, '');
        return hash('sha256', 'msgiftcards|' . MODX_CORE_PATH . '|' . $siteId);
    }

    public function runSnippet($name, array $properties = [])
    {
        $name = trim((string)$name);
        if ($name === '') {
            return '';
        }

        $pdoTools = $this->getPdoTools();
        if ($pdoTools && method_exists($pdoTools, 'runSnippet')) {
            $output = $pdoTools->runSnippet($name, $properties);
            return is_string($output) ? $output : $output;
        }

        $output = $this->modx->runSnippet($name, $properties);
        return is_string($output) ? $output : $output;
    }

    public function renderChunk($tpl, array $placeholders = [], $fastMode = false)
    {
        $tpl = trim((string)$tpl);
        if ($tpl === '') {
            return '';
        }

        $pdoTools = $this->getPdoTools();
        if ($pdoTools && method_exists($pdoTools, 'getChunk')) {
            $output = $pdoTools->getChunk($tpl, $placeholders, (bool)$fastMode);
            return is_string($output) ? $output : '';
        }

        if (stripos($tpl, '@FILE') === 0) {
            $file = trim(substr($tpl, 5));
            $file = ltrim($file, ' :');
            if ($file === '') {
                return '';
            }

            if (strpos($file, MODX_BASE_PATH) !== 0 && strpos($file, MODX_CORE_PATH) !== 0) {
                $file = MODX_BASE_PATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file), DIRECTORY_SEPARATOR);
            }

            if (!is_file($file)) {
                return '';
            }

            $content = file_get_contents($file);
            if ($content === false || $content === '') {
                return '';
            }

            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($content);
            $chunk->_cacheable = false;
            $chunk->_processed = false;

            return (string)$chunk->process($placeholders, $content);
        }

        $output = $this->modx->getChunk($tpl, $placeholders);
        return is_string($output) ? $output : '';
    }

    public function normalizeCode($code)
    {
        $code = trim((string)$code);
        $code = preg_replace('/\s+/', '', $code);

        return strtoupper((string)$code);
    }

    public function getCertificateByCode($code)
    {
        $code = $this->normalizeCode($code);
        if ($code === '') {
            return null;
        }

        $sql = 'SELECT * FROM ' . $this->table('msgiftcards_certificates') . ' WHERE UPPER(code) = UPPER(:code) LIMIT 1';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        if (!$stmt->execute()) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $row;
    }

    public function getIssuedCertificatesByOrderId($orderId)
    {
        $orderId = (int)$orderId;
        if ($orderId <= 0) {
            return [];
        }

        $sql = 'SELECT * FROM ' . $this->table('msgiftcards_certificates') . ' WHERE order_id = :order_id ORDER BY id ASC';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return [];
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    public function getPrimaryIssuedCertificateByOrderId($orderId)
    {
        $rows = $this->getIssuedCertificatesByOrderId($orderId);
        if (empty($rows)) {
            return null;
        }

        return $rows[0];
    }

    public function formatAmount($value)
    {
        $formatted = number_format((float)$value, 2, '.', '');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, '.');

        return $formatted === '' ? '0' : $formatted;
    }

    public function formatCertificateDate($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('d.m.Y H:i', $timestamp);
    }

    public function getCertificateDataByOrderId($orderId)
    {
        $certificate = $this->getPrimaryIssuedCertificateByOrderId($orderId);
        if (!$certificate) {
            return null;
        }

        $currency = trim((string)(isset($certificate['currency']) ? $certificate['currency'] : ''));
        if ($currency === '') {
            $currency = $this->config['defaultCurrency'];
        }

        return [
            'id' => (int)$certificate['id'],
            'order_id' => (int)$certificate['order_id'],
            'code' => (string)$certificate['code'],
            'nominal' => (float)$certificate['nominal'],
            'nominal_formatted' => $this->formatAmount($certificate['nominal']) . ' ' . $currency,
            'currency' => $currency,
            'balance' => (float)$certificate['balance'],
            'balance_formatted' => $this->formatAmount($certificate['balance']) . ' ' . $currency,
            'expireson' => (string)$certificate['expireson'],
            'expireson_formatted' => $this->formatCertificateDate(isset($certificate['expireson']) ? $certificate['expireson'] : ''),
            'active' => (int)$certificate['active'],
            'createdon' => (string)$certificate['createdon'],
            'updatedon' => (string)$certificate['updatedon'],
        ];
    }

    public function renderCertificateHtml($orderId, $tpl = 'msGiftCards.certificate', array $extra = [])
    {
        $certificate = $this->getCertificateDataByOrderId($orderId);
        if (!$certificate) {
            return '';
        }

        $placeholders = array_merge($certificate, $extra);
        return $this->renderChunk($tpl, $placeholders);
    }

    public function getCertificatePdfHtml($orderId, $tpl = 'msGiftCards.certificate')
    {
        $html = $this->renderCertificateHtml($orderId, $tpl);
        if ($html === '') {
            return '';
        }

        return '<!doctype html><html><head><meta charset="UTF-8"><style>'
            . '@page{margin:0;}'
            . 'html,body{margin:0;padding:0;background:#ffffff;}'
            . 'body{font-family:DejaVu Sans,Arial,sans-serif;color:#222;}'
            . '*{box-sizing:border-box;}'
            . '</style></head><body>' . $html . '</body></html>';
    }

    public function generateCertificatePdf($orderId, $tpl = 'msGiftCards.certificate')
    {
        $certificate = $this->getCertificateDataByOrderId($orderId);
        if (!$certificate) {
            return '';
        }

        $autoload = $this->config['corePath'] . 'vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }

        if (class_exists('Dompdf\\Dompdf')) {
            $html = $this->getCertificatePdfHtml($orderId, $tpl);
            if ($html !== '') {
                $optionsClass = 'Dompdf\\Options';
                $dompdfClass = 'Dompdf\\Dompdf';

                $options = new $optionsClass();
                if (method_exists($options, 'set')) {
                    $options->set('isRemoteEnabled', false);
                    $options->set('isHtml5ParserEnabled', true);
                    $options->set('isPhpEnabled', false);
                    $options->set('defaultFont', 'DejaVu Sans');
                    $options->set('tempDir', MODX_CORE_PATH . 'cache/');
                    $options->set('chroot', MODX_BASE_PATH);
                }

                $dompdf = new $dompdfClass($options);
                $dompdf->loadHtml($html, 'UTF-8');
                $paper = strtoupper(trim((string)$this->config['certificatePdfPaper']));
                if (!in_array($paper, ['A4', 'A5', 'LETTER'], true)) {
                    $paper = 'A4';
                }

                $orientation = strtolower(trim((string)$this->config['certificatePdfOrientation']));
                if (!in_array($orientation, ['portrait', 'landscape'], true)) {
                    $orientation = 'portrait';
                }

                $dompdf->setPaper($paper, $orientation);
                $dompdf->render();

                return $dompdf->output();
            }
        }

        return msGiftCardsPdf::build($certificate);
    }

    public function generateCertificateToken($orderId)
    {
        $orderId = (int)$orderId;
        if ($orderId <= 0) {
            return '';
        }

        $key = hash('sha256', $this->getCertificateTokenKey(), true);
        $iv = random_bytes(16);
        $payload = json_encode([
            'order_id' => $orderId,
            'iat' => time(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return '';
        }

        if (!function_exists('openssl_encrypt')) {
            $signature = hash_hmac('sha256', $payload, $key, true);
            return $this->base64UrlEncode($signature . $payload);
        }

        $ciphertext = openssl_encrypt($payload, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            return '';
        }

        $signature = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        return $this->base64UrlEncode($iv . $signature . $ciphertext);
    }

    public function parseCertificateToken($token)
    {
        $token = trim((string)$token);
        if ($token === '') {
            return [false, null];
        }

        $binary = $this->base64UrlDecode($token);
        if ($binary === '') {
            return [false, null];
        }

        $key = hash('sha256', $this->getCertificateTokenKey(), true);
        $payload = '';

        if (function_exists('openssl_decrypt') && strlen($binary) > 48) {
            $iv = substr($binary, 0, 16);
            $signature = substr($binary, 16, 32);
            $ciphertext = substr($binary, 48);
            $expected = hash_hmac('sha256', $iv . $ciphertext, $key, true);
            if (!hash_equals($expected, $signature)) {
                return [false, null];
            }

            $payload = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            if ($payload === false) {
                return [false, null];
            }
        } elseif (strlen($binary) > 32) {
            $signature = substr($binary, 0, 32);
            $payload = substr($binary, 32);
            $expected = hash_hmac('sha256', $payload, $key, true);
            if (!hash_equals($expected, $signature)) {
                return [false, null];
            }
        } else {
            return [false, null];
        }

        $data = json_decode($payload, true);
        if (!is_array($data) || empty($data['order_id'])) {
            return [false, null];
        }

        return [true, (int)$data['order_id']];
    }

    public function validateCertificateCode($code)
    {
        $code = $this->normalizeCode($code);
        if ($code === '') {
            return [false, $this->modx->lexicon('msgiftcards_err_code_empty')];
        }

        $certificate = $this->getCertificateByCode($code);
        if (!$certificate) {
            return [false, $this->modx->lexicon('msgiftcards_err_code_not_found')];
        }

        if ((int)$certificate['active'] !== 1) {
            return [false, $this->modx->lexicon('msgiftcards_err_code_inactive')];
        }

        if (!empty($certificate['expireson'])) {
            $expiresAt = strtotime((string)$certificate['expireson']);
            if ($expiresAt !== false && $expiresAt < time()) {
                return [false, $this->modx->lexicon('msgiftcards_err_code_expired')];
            }
        }

        if ((float)$certificate['balance'] <= 0) {
            return [false, $this->modx->lexicon('msgiftcards_err_code_empty_balance')];
        }

        return [true, $certificate];
    }

    public function calculateDiscountForCost($cost, array $certificate)
    {
        $cost = max(0.0, (float)$cost);
        $balance = max(0.0, (float)$certificate['balance']);

        return min($cost, $balance);
    }

    public function getCartBaseTotalCost($cartStatus)
    {
        if (!is_array($cartStatus)) {
            return 0.0;
        }

        if (isset($cartStatus['msgiftcards_original_total_cost'])) {
            return max(0.0, (float)$cartStatus['msgiftcards_original_total_cost']);
        }

        $total = isset($cartStatus['total_cost']) ? (float)$cartStatus['total_cost'] : 0.0;
        $discount = isset($cartStatus['msgiftcards_discount']) ? (float)$cartStatus['msgiftcards_discount'] : 0.0;

        return max(0.0, $total + max(0.0, $discount));
    }

    public function getCheckoutDeliveryCost($ms2 = null, $cartBaseCost = null, $allowRecalculate = false)
    {
        $delivery = 0.0;
        $cartBase = $cartBaseCost !== null ? max(0.0, (float)$cartBaseCost) : 0.0;

        if (!empty($_SESSION['minishop2']['order']) && is_array($_SESSION['minishop2']['order'])) {
            $orderState = $_SESSION['minishop2']['order'];

            if (isset($orderState['delivery_cost'])) {
                $delivery = max(0.0, (float)$orderState['delivery_cost']);
            } elseif (isset($orderState['delivery_price'])) {
                $delivery = max(0.0, (float)$orderState['delivery_price']);
            } elseif (isset($orderState['cost'], $orderState['cart_cost'])) {
                $delivery = max(0.0, (float)$orderState['cost'] - (float)$orderState['cart_cost']);
            } elseif (isset($orderState['cost']) && $cartBase > 0) {
                $delivery = max(0.0, (float)$orderState['cost'] - $cartBase);
            }
        }

        if ($delivery <= 0 && $ms2 && isset($ms2->order) && method_exists($ms2->order, 'get')) {
            $orderData = $ms2->order->get();
            if (is_array($orderData)) {
                if (isset($orderData['delivery_cost'])) {
                    $delivery = max(0.0, (float)$orderData['delivery_cost']);
                } elseif (isset($orderData['delivery_price'])) {
                    $delivery = max(0.0, (float)$orderData['delivery_price']);
                } elseif (isset($orderData['cost'], $orderData['cart_cost'])) {
                    $delivery = max(0.0, (float)$orderData['cost'] - (float)$orderData['cart_cost']);
                } elseif (isset($orderData['cost']) && $cartBase > 0) {
                    $delivery = max(0.0, (float)$orderData['cost'] - $cartBase);
                }
            }
        }

        if ($delivery <= 0 && $allowRecalculate && $ms2 && isset($ms2->order) && method_exists($ms2->order, 'getcost')) {
            $orderCostData = $ms2->order->getcost();
            if (is_array($orderCostData)) {
                if (isset($orderCostData['delivery_cost'])) {
                    $delivery = max(0.0, (float)$orderCostData['delivery_cost']);
                } elseif (isset($orderCostData['delivery_price'])) {
                    $delivery = max(0.0, (float)$orderCostData['delivery_price']);
                } elseif (isset($orderCostData['cost'], $orderCostData['cart_cost'])) {
                    $delivery = max(0.0, (float)$orderCostData['cost'] - (float)$orderCostData['cart_cost']);
                } elseif (isset($orderCostData['cost']) && $cartBase > 0) {
                    $delivery = max(0.0, (float)$orderCostData['cost'] - $cartBase);
                }
            } elseif ($cartBase > 0) {
                $delivery = max(0.0, (float)$orderCostData - $cartBase);
            }
        }

        return $delivery;
    }

    public function applyDiscountToCost($cost)
    {
        $state = $this->getCheckoutState();
        if (empty($state['code'])) {
            return [
                'cost' => (float)$cost,
                'discount' => 0.0,
                'certificate' => null,
                'message' => '',
            ];
        }

        list($ok, $payload) = $this->validateCertificateCode($state['code']);
        if (!$ok) {
            $this->clearCheckoutState();
            return [
                'cost' => (float)$cost,
                'discount' => 0.0,
                'certificate' => null,
                'message' => (string)$payload,
            ];
        }

        $discount = $this->calculateDiscountForCost($cost, $payload);
        $newCost = max(0.0, (float)$cost - $discount);

        $this->setCheckoutState([
            'code' => $payload['code'],
            'certificate_id' => (int)$payload['id'],
            'discount' => $discount,
        ]);

        return [
            'cost' => $newCost,
            'discount' => $discount,
            'certificate' => $payload,
            'balance_after' => max(0.0, (float)$payload['balance'] - $discount),
            'message' => '',
        ];
    }
    public function ensureGenerateStatusId()
    {
        if (!empty($this->config['generateStatusId'])) {
            return (int)$this->config['generateStatusId'];
        }

        return (int)$this->modx->getOption('ms2_status_paid', null, 0);
    }

    public function ensureRedeemStatusId()
    {
        if (!empty($this->config['paidStatusId'])) {
            return (int)$this->config['paidStatusId'];
        }

        return (int)$this->modx->getOption('ms2_status_paid', null, 0);
    }

    public function ensureCancelStatusId()
    {
        if (!empty($this->config['cancelStatusId'])) {
            return (int)$this->config['cancelStatusId'];
        }

        return 0;
    }

    public function generateCertificatesForPaidOrder(msOrder $order)
    {
        $createdCodes = [];
        $nominalCandidates = 0;
        $checkedProducts = 0;

        $q = $this->modx->newQuery('msOrderProduct');
        $q->where(['order_id' => (int)$order->get('id')]);
        $q->sortby('id', 'ASC');

        /** @var msOrderProduct[] $products */
        $products = $this->modx->getCollection('msOrderProduct', $q);
        if (empty($products)) {
            return $createdCodes;
        }

        foreach ($products as $product) {
            $checkedProducts++;
            $nominal = $this->extractNominalFromOrderProduct($product);
            if ($nominal <= 0) {
                continue;
            }
            $nominalCandidates++;

            $count = max(1, (int)$product->get('count'));
            for ($i = 1; $i <= $count; $i++) {
                if ($this->certificateExistsForOrderProduct((int)$product->get('id'), $i)) {
                    continue;
                }

                $code = $this->generateUniqueCode();
                if ($code === '') {
                    continue;
                }

                $now = date('Y-m-d H:i:s');
                $expiresOn = null;
                if (!empty($this->config['certificateLifetimeDays'])) {
                    $expiresOn = date('Y-m-d H:i:s', strtotime('+' . (int)$this->config['certificateLifetimeDays'] . ' days'));
                }
                $sql = 'INSERT INTO ' . $this->table('msgiftcards_certificates') . ' '
                    . '(code, nominal, balance, currency, active, order_id, order_product_id, item_index, createdon, updatedon, expireson) '
                    . 'VALUES (:code, :nominal, :balance, :currency, 1, :order_id, :order_product_id, :item_index, :createdon, :updatedon, :expireson)';

                $stmt = $this->modx->prepare($sql);
                if (!$stmt) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, '[msGiftCards] Could not prepare certificate insert query for order #' . (int)$order->get('id'));
                    continue;
                }

                $stmt->bindValue(':code', $code, PDO::PARAM_STR);
                $stmt->bindValue(':nominal', $nominal);
                $stmt->bindValue(':balance', $nominal);
                $stmt->bindValue(':currency', $this->config['defaultCurrency'], PDO::PARAM_STR);
                $stmt->bindValue(':order_id', (int)$order->get('id'), PDO::PARAM_INT);
                $stmt->bindValue(':order_product_id', (int)$product->get('id'), PDO::PARAM_INT);
                $stmt->bindValue(':item_index', $i, PDO::PARAM_INT);
                $stmt->bindValue(':createdon', $now, PDO::PARAM_STR);
                $stmt->bindValue(':updatedon', $now, PDO::PARAM_STR);
                if ($expiresOn === null) {
                    $stmt->bindValue(':expireson', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':expireson', $expiresOn, PDO::PARAM_STR);
                }

                if ($stmt->execute()) {
                    $createdCodes[] = $code;
                } else {
                    $err = $stmt->errorInfo();
                    $this->modx->log(modX::LOG_LEVEL_ERROR, '[msGiftCards] Could not insert certificate for order #' . (int)$order->get('id') . '. SQL error: ' . print_r($err, true));
                }
            }
        }

        if (empty($createdCodes)) {
            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[msGiftCards] No certificates generated for order #' . (int)$order->get('id')
                . '; checked products=' . $checkedProducts
                . '; nominal candidates=' . $nominalCandidates
                . '; nominal option key=' . $this->config['nominalOptionKey']
            );
        }

        return $createdCodes;
    }

    public function redeemOrderCertificate(msOrder $order)
    {
        $properties = $this->normalizeProperties($order->get('properties'));
        if (empty($properties['msgiftcards']) || !is_array($properties['msgiftcards'])) {
            return true;
        }

        $gc = $properties['msgiftcards'];
        if (!empty($gc['redeemed'])) {
            return true;
        }

        $code = $this->normalizeCode($gc['code'] ?? '');
        $amount = max(0.0, (float)($gc['amount'] ?? 0));
        if ($code === '' || $amount <= 0) {
            return true;
        }

        list($ok, $certificate) = $this->validateCertificateCode($code);
        if (!$ok) {
            return false;
        }

        $balance = max(0.0, (float)$certificate['balance']);
        $charge = min($amount, $balance);
        if ($charge <= 0) {
            return true;
        }

        $newBalance = $balance - $charge;
        $active = $newBalance > 0 ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        $stmt = $this->modx->prepare(
            'UPDATE ' . $this->table('msgiftcards_certificates') . ' SET balance = :balance, active = :active, updatedon = :updatedon WHERE id = :id'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bindValue(':balance', $newBalance);
        $stmt->bindValue(':active', $active, PDO::PARAM_INT);
        $stmt->bindValue(':updatedon', $now, PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$certificate['id'], PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return false;
        }

        $stmt = $this->modx->prepare(
            'INSERT INTO ' . $this->table('msgiftcards_redemptions')
            . ' (certificate_id, order_id, amount, balance_after, operation, createdon) VALUES (:certificate_id, :order_id, :amount, :balance_after, :operation, :createdon)'
        );
        if ($stmt) {
            $stmt->bindValue(':certificate_id', (int)$certificate['id'], PDO::PARAM_INT);
            $stmt->bindValue(':order_id', (int)$order->get('id'), PDO::PARAM_INT);
            $stmt->bindValue(':amount', $charge);
            $stmt->bindValue(':balance_after', $newBalance);
            $stmt->bindValue(':operation', 'debit', PDO::PARAM_STR);
            $stmt->bindValue(':createdon', $now, PDO::PARAM_STR);
            $stmt->execute();
        }

        $gc['redeemed'] = 1;
        $gc['redeemed_on'] = $now;
        $properties['msgiftcards'] = $gc;
        $order->set('properties', $properties);
        $order->save();

        return true;
    }

    public function refundOrderCertificate(msOrder $order)
    {
        $properties = $this->normalizeProperties($order->get('properties'));
        if (empty($properties['msgiftcards']) || !is_array($properties['msgiftcards'])) {
            return true;
        }

        $gc = $properties['msgiftcards'];
        if (empty($gc['redeemed'])) {
            return true;
        }
        if (!empty($gc['refund_processed'])) {
            return true;
        }

        $certificateId = (int)($gc['certificate_id'] ?? 0);
        if ($certificateId <= 0) {
            $code = $this->normalizeCode($gc['code'] ?? '');
            if ($code !== '') {
                $certificate = $this->getCertificateByCode($code);
                if ($certificate) {
                    $certificateId = (int)$certificate['id'];
                }
            }
        }
        if ($certificateId <= 0) {
            return false;
        }

        $netRedeemed = $this->getOrderCertificateNetRedeemedAmount((int)$order->get('id'), $certificateId);
        if ($netRedeemed <= 0) {
            $gc['refund_processed'] = 1;
            $gc['refund_amount'] = 0;
            $gc['refund_on'] = date('Y-m-d H:i:s');
            $properties['msgiftcards'] = $gc;
            $order->set('properties', $properties);
            $order->save();
            return true;
        }

        $stmt = $this->modx->prepare(
            'SELECT id, balance FROM ' . $this->table('msgiftcards_certificates') . ' WHERE id = :id LIMIT 1'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bindValue(':id', $certificateId, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return false;
        }
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$certificate) {
            return false;
        }

        $currentBalance = max(0.0, (float)$certificate['balance']);
        $newBalance = $currentBalance + $netRedeemed;
        $now = date('Y-m-d H:i:s');

        $updateStmt = $this->modx->prepare(
            'UPDATE ' . $this->table('msgiftcards_certificates') . ' SET balance = :balance, active = 1, updatedon = :updatedon WHERE id = :id'
        );
        if (!$updateStmt) {
            return false;
        }
        $updateStmt->bindValue(':balance', $newBalance);
        $updateStmt->bindValue(':updatedon', $now, PDO::PARAM_STR);
        $updateStmt->bindValue(':id', $certificateId, PDO::PARAM_INT);
        if (!$updateStmt->execute()) {
            return false;
        }

        $insertStmt = $this->modx->prepare(
            'INSERT INTO ' . $this->table('msgiftcards_redemptions')
            . ' (certificate_id, order_id, amount, balance_after, operation, createdon) VALUES (:certificate_id, :order_id, :amount, :balance_after, :operation, :createdon)'
        );
        if ($insertStmt) {
            $insertStmt->bindValue(':certificate_id', $certificateId, PDO::PARAM_INT);
            $insertStmt->bindValue(':order_id', (int)$order->get('id'), PDO::PARAM_INT);
            $insertStmt->bindValue(':amount', $netRedeemed);
            $insertStmt->bindValue(':balance_after', $newBalance);
            $insertStmt->bindValue(':operation', 'credit', PDO::PARAM_STR);
            $insertStmt->bindValue(':createdon', $now, PDO::PARAM_STR);
            $insertStmt->execute();
        }

        $gc['refund_processed'] = 1;
        $gc['refund_amount'] = $netRedeemed;
        $gc['refund_on'] = $now;
        $properties['msgiftcards'] = $gc;
        $order->set('properties', $properties);
        $order->save();

        return true;
    }

    public function getOrderCertificateNetRedeemedAmount($orderId, $certificateId)
    {
        $stmt = $this->modx->prepare(
            'SELECT operation, amount FROM ' . $this->table('msgiftcards_redemptions')
            . ' WHERE order_id = :order_id AND certificate_id = :certificate_id'
        );
        if (!$stmt) {
            return 0.0;
        }
        $stmt->bindValue(':order_id', (int)$orderId, PDO::PARAM_INT);
        $stmt->bindValue(':certificate_id', (int)$certificateId, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return 0.0;
        }

        $net = 0.0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $amount = max(0.0, (float)$row['amount']);
            $operation = isset($row['operation']) ? (string)$row['operation'] : 'debit';
            if ($operation === 'credit') {
                $net -= $amount;
            } else {
                $net += $amount;
            }
        }

        return max(0.0, $net);
    }

    public function normalizeProperties($properties)
    {
        if (is_array($properties)) {
            return $properties;
        }

        if (is_string($properties) && $properties !== '') {
            $decoded = json_decode($properties, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    public function extractNominalFromOrderProduct(msOrderProduct $product)
    {
        $optionKey = $this->config['nominalOptionKey'];
        if ($optionKey === '') {
            return 0.0;
        }

        $options = $product->get('options');
        if (!is_array($options)) {
            if (is_string($options) && $options !== '') {
                $tmp = json_decode($options, true);
                $options = is_array($tmp) ? $tmp : [];
            } else {
                $options = [];
            }
        }

        if (!array_key_exists($optionKey, $options)) {
            $nested = $this->findOptionValueRecursive($options, $optionKey);
            if ($nested === null) {
                return 0.0;
            }

            return max(0.0, (float)$nested);
        }

        return max(0.0, (float)$options[$optionKey]);
    }

    public function findOptionValueRecursive($value, $key)
    {
        if (!is_array($value)) {
            return null;
        }

        if (array_key_exists($key, $value)) {
            return $value[$key];
        }

        foreach ($value as $item) {
            if (is_array($item)) {
                $found = $this->findOptionValueRecursive($item, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    public function certificateExistsForOrderProduct($orderProductId, $itemIndex)
    {
        $stmt = $this->modx->prepare(
            'SELECT id FROM ' . $this->table('msgiftcards_certificates')
            . ' WHERE order_product_id = :order_product_id AND item_index = :item_index LIMIT 1'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bindValue(':order_product_id', (int)$orderProductId, PDO::PARAM_INT);
        $stmt->bindValue(':item_index', (int)$itemIndex, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return false;
        }

        return (bool)$stmt->fetchColumn();
    }

    public function generateUniqueCode()
    {
        $tries = 20;
        while ($tries-- > 0) {
            $code = $this->generateCodeByMask($this->config['codeMask']);
            if ($code === '') {
                return '';
            }

            $exists = $this->getCertificateByCode($code);
            if (!$exists) {
                return $code;
            }
        }

        return '';
    }

    public function table($name)
    {
        return $this->modx->getOption('table_prefix') . $name;
    }

    public function generateCodeByMask($mask)
    {
        $mask = trim((string)$mask);
        if ($mask === '') {
            return '';
        }

        $offset = 0;
        $length = strlen($mask);
        $result = '';

        while ($offset < $length) {
            if (preg_match('/\G\[([^\]]+)\]\{(\d+)\}/A', $mask, $m, 0, $offset)) {
                $charset = $this->expandMaskCharset($m[1]);
                $count = (int)$m[2];
                if ($count < 1 || $charset === '') {
                    return '';
                }
                $result .= $this->randomStringFromCharset($charset, $count);
                $offset += strlen($m[0]);
                continue;
            }

            if (preg_match('/\G\\\\(.)/A', $mask, $m, 0, $offset)) {
                $result .= $m[1];
                $offset += strlen($m[0]);
                continue;
            }

            $result .= $mask[$offset];
            $offset++;
        }

        return $result;
    }

    public function expandMaskCharset($expr)
    {
        $expr = (string)$expr;
        $len = strlen($expr);
        $chars = '';

        for ($i = 0; $i < $len; $i++) {
            $ch = $expr[$i];
            if ($ch === '\\' && $i + 1 < $len) {
                $chars .= $expr[$i + 1];
                $i++;
                continue;
            }

            if ($i + 2 < $len && $expr[$i + 1] === '-') {
                $from = ord($expr[$i]);
                $to = ord($expr[$i + 2]);
                if ($from <= $to) {
                    for ($c = $from; $c <= $to; $c++) {
                        $chars .= chr($c);
                    }
                    $i += 2;
                    continue;
                }
            }

            $chars .= $ch;
        }

        $unique = [];
        $out = '';
        $charsLen = strlen($chars);
        for ($i = 0; $i < $charsLen; $i++) {
            if (!isset($unique[$chars[$i]])) {
                $unique[$chars[$i]] = true;
                $out .= $chars[$i];
            }
        }

        return $out;
    }

    public function randomStringFromCharset($charset, $length)
    {
        $charset = (string)$charset;
        $length = (int)$length;
        $count = strlen($charset);
        if ($count < 1 || $length < 1) {
            return '';
        }

        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[random_int(0, $count - 1)];
        }

        return $result;
    }

    protected function base64UrlEncode($value)
    {
        return rtrim(strtr(base64_encode((string)$value), '+/', '-_'), '=');
    }

    protected function base64UrlDecode($value)
    {
        $value = strtr((string)$value, '-_', '+/');
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($value, true);
        return $decoded === false ? '' : $decoded;
    }
}

