<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/vizyonpos.php';
require_once __DIR__ . '/../functions.php';

/**
 * Unified Payment Controller
 * Tüm ödeme türleri için merkezi kontrol noktası
 *
 * Desteklenen payment_type değerleri:
 * - package: Paket satın alma
 * - custom: Özel tutar ödeme
 * - credit: Kontor satın alma
 */

// --- API Ayarları (Ortam bazlı: config/.env dosyasından) ---
$config = VizionPosConfig::getInstance();
$apiKey = $config->getApiKey();
$apiSecret = $config->getApiSecret();
$baseUrl = $config->getThreeDSecureUrl();
$callbackUrl = $config->getCallbackUrl();

// Formdan gelen veriler
$post = $_POST;
$paymentType = safeInput($post['payment_type'] ?? null);

// Ödeme türü kontrolü
$allowedTypes = ['package', 'custom', 'credit'];
if (!in_array($paymentType, $allowedTypes)) {
    echo json_encode([
        "success" => false,
        "message" => "Geçersiz ödeme türü. Gelen: " . $paymentType
    ]);
    exit;
}

$errors = [];

// Müşteri tipi kontrolü (company veya private)
$customerType = safeInput($post['customer_type'] ?? 'company');

// Kontor ödemesi için özel validasyon (hidden fields kullanıyor)
if ($paymentType === 'credit') {
    // Kontor ödemeleri için müşteri bilgileri zaten veritabanından geldiği için
    // sadece temel alanları kontrol edelim
    if (empty($post['first_name'])) {
        $errors[] = "Müşteri adı bulunamadı.";
    }
    if (empty($post['last_name'])) {
        $errors[] = "Müşteri soyadı bulunamadı.";
    }
    if (empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi bulunamadı.";
    }
    $phoneNumber = safeInput(str_replace(' ', '', $post['phone'] ?? ''));
    if (empty($phoneNumber) || !preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
        $errors[] = "Geçerli bir telefon numarası bulunamadı.";
    }
} else {
    // Müşteri tipine göre validasyon (package ve custom ödemeleri için)
    if ($customerType === 'company') {
        // Kurumsal müşteri validasyonları
        if (empty($post['company_name'])) {
            $errors[] = "Şirket adını giriniz.";
        }
        if (empty($post['company_authorized_name'])) {
            $errors[] = "Yetkili adını giriniz.";
        }
        if (empty($post['company_authorized_lastname'])) {
            $errors[] = "Yetkili soyadını giriniz.";
        }
        if (empty($post['company_email']) || !filter_var($post['company_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Geçerli bir e-posta adresi giriniz.";
        }
        $phoneNumber = safeInput(str_replace(' ', '', $post['company_phone'] ?? ''));
        if (empty($phoneNumber) || !preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
            $errors[] = "Geçerli bir telefon numarası giriniz.";
        }
        if (empty($post['company_tax_number']) || !preg_match('/^[0-9]{10}$/', $post['company_tax_number'])) {
            $errors[] = "Geçerli bir vergi numarası giriniz (10 haneli).";
        }
        if (empty($post['company_tax_office'])) {
            $errors[] = "Vergi dairesini giriniz.";
        }
    } else {
        // Bireysel müşteri validasyonları
        if (empty($post['private_identity_number']) || !preg_match('/^[0-9]{11}$/', $post['private_identity_number'])) {
            $errors[] = "Geçerli bir TC Kimlik numarası giriniz.";
        }
        if (empty($post['private_first_name'])) {
            $errors[] = "Adınızı giriniz.";
        }
        if (empty($post['private_last_name'])) {
            $errors[] = "Soyadınızı giriniz.";
        }
        if (empty($post['private_email']) || !filter_var($post['private_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Geçerli bir e-posta adresi giriniz.";
        }
        $phoneNumber = safeInput(str_replace(' ', '', $post['private_phone'] ?? ''));
        if (empty($phoneNumber) || !preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
            $errors[] = "Geçerli bir telefon numarası giriniz.";
        }
    }
}

if (empty($post['card_name'])) {
    $errors[] = "Kart üzerindeki adı giriniz.";
}

$cardNumber = preg_replace('/\s+/', '', $post['card_number'] ?? '');
if (!preg_match('/^[0-9]{16}$/', $cardNumber)) {
    $errors[] = "Geçerli bir kart numarası giriniz.";
}

if (empty($post['expire_year']) || !preg_match('/^[0-9]{4}$/', $post['expire_year'])) {
    $errors[] = "Geçerli bir yıl seçiniz.";
}
if (empty($post['expire_month']) || !preg_match('/^(0[1-9]|1[0-2])$/', $post['expire_month'])) {
    $errors[] = "Geçerli bir ay seçiniz.";
}

if (empty($post['cvc_code']) || !preg_match('/^[0-9]{3}$/', $post['cvc_code'])) {
    $errors[] = "Geçerli bir CVC kodu giriniz.";
}

if (empty($post['contract_agreement'])) {
    $errors[] = "Mesafeli Satış Sözleşmesini kabul etmelisiniz.";
}
if (empty($post['privacy_agreement'])) {
    $errors[] = "Kişisel verilerin işlenmesine ilişkin aydınlatma metnini kabul etmelisiniz.";
}

// --- Ödeme Türüne Göre Özel Validasyonlar ve Tutar Hesaplama ---
$amountTL = 0;
$itemName = '';
$itemId = null;

switch ($paymentType) {
    case 'package':
        // Paket satın alma
        $packageId = safeInput($post['packageId'] ?? null);
        if (!$packageId) {
            $errors[] = "Paket seçimi yapılmadı.";
            break;
        }

        $packages_get = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
        $packages_get->execute([$packageId]);
        $package = $packages_get->fetch(PDO::FETCH_ASSOC);

        if (!$package) {
            $errors[] = "Paket bulunamadı.";
            break;
        }

        $priceTL = (float)$package['price'];
        $vat = (float)($package['vat'] ?? 0);
        $amountTL = $priceTL + ($priceTL * $vat / 100);
        $itemName = $package['title'] ?? "Paket #" . $packageId;
        $itemId = $packageId;
        break;

    case 'custom':
        // Özel tutar ödeme
        $customAmount = safeInput($post['custom_amount'] ?? null);
        if (empty($customAmount) || !is_numeric($customAmount) || $customAmount <= 0) {
            $errors[] = "Geçerli bir tutar giriniz.";
            break;
        }

        // KDV dahil mi?
        $vatIncluded = isset($post['vat_included']) && $post['vat_included'] == '1';
        $baseAmount = (float)$customAmount;

        if ($vatIncluded) {
            // KDV dahil: %20 KDV ekle
            $amountTL = $baseAmount * 1.20;
            $itemName = "Özel Ödeme (KDV Dahil)";
        } else {
            // KDV hariç
            $amountTL = $baseAmount;
            $itemName = "Özel Ödeme (KDV Hariç)";
        }

        $itemId = 'custom_' . time();
        break;

    case 'credit':
        // Kontor satın alma
        $creditAmount = safeInput($post['credit_amount'] ?? null);
        $customerNumber = safeInput($post['customer_number'] ?? null);

        if (empty($customerNumber)) {
            $errors[] = "Müşteri numarası giriniz.";
        }

        if (empty($creditAmount)) {
            $errors[] = "Kontor miktarı seçilmedi.";
            break;
        }

        // Kontor paketini veritabanından çek
        $package_get = $pdo->prepare("SELECT * FROM credit_packages WHERE credit_amount = ? AND is_active = 1");
        $package_get->execute([$creditAmount]);
        $creditPackage = $package_get->fetch(PDO::FETCH_ASSOC);

        if (!$creditPackage) {
            $errors[] = "Geçersiz kontor paketi.";
            break;
        }

        // Müşteri bilgilerini veritabanından çek
        $customer_get = $pdo->prepare("SELECT * FROM customers WHERE customer_number = ?");
        $customer_get->execute([$customerNumber]);
        $customerData = $customer_get->fetch(PDO::FETCH_ASSOC);

        if (!$customerData) {
            $errors[] = "Müşteri bulunamadı.";
            break;
        }

        // Müşteri bilgilerini form verilerine ekle (otomatik doldurma)
        $post['first_name'] = $customerData['first_name'] ?? $post['first_name'];
        $post['last_name'] = $customerData['last_name'] ?? $post['last_name'];
        $post['email'] = $customerData['email'] ?? $post['email'];
        $post['phone'] = $customerData['phone'] ?? $post['phone'];
        $post['identity_number'] = $customerData['identity_number'] ?? $post['identity_number'];
        $post['city'] = $customerData['city'] ?? $post['city'];
        $post['district'] = $customerData['district'] ?? $post['district'];
        $post['postal_code'] = $customerData['postal_code'] ?? $post['postal_code'];
        $post['address'] = $customerData['address'] ?? $post['address'];

        // Varsayılan fatura adresini çek
        $billing_get = $pdo->prepare("SELECT * FROM customer_billing_addresses WHERE customer_id = ? AND is_default = 1 LIMIT 1");
        $billing_get->execute([$customerData['id']]);
        $billingData = $billing_get->fetch(PDO::FETCH_ASSOC);

        if ($billingData) {
            // Fatura adresi varsa kullan
            $post['billing_first_name'] = $billingData['first_name'] ?? $post['first_name'];
            $post['billing_last_name'] = $billingData['last_name'] ?? $post['last_name'];
            $post['billing_company_name'] = $billingData['company_name'] ?? '';
            $post['billing_tax_number'] = $billingData['tax_number'] ?? '';
            $post['billing_tax_office'] = $billingData['tax_office'] ?? '';
            $post['billing_address'] = $billingData['address'] ?? $post['address'];
            $post['billing_country'] = $billingData['country'] ?? 'Türkiye';
            $post['billing_city'] = $billingData['city'] ?? $post['city'];
            $post['billing_district'] = $billingData['district'] ?? $post['district'];
            $post['billing_postal_code'] = $billingData['postal_code'] ?? $post['postal_code'];
        } else {
            // Fatura adresi yoksa müşteri bilgilerini kullan (bireysel müşteri)
            $post['billing_first_name'] = $post['first_name'];
            $post['billing_last_name'] = $post['last_name'];
            $post['billing_company_name'] = $post['first_name'] . ' ' . $post['last_name']; // VizionPOS zorunlu alan
            $post['billing_tax_number'] = $post['identity_number'] ?? ''; // TC kimlik no kullan
            $post['billing_tax_office'] = ''; // Bireysel için boş
            $post['billing_address'] = $post['address'];
            $post['billing_country'] = 'Türkiye';
            $post['billing_city'] = $post['city'];
            $post['billing_district'] = $post['district'];
            $post['billing_postal_code'] = $post['postal_code'];
        }

        // KDV dahil fiyat hesapla
        $priceTL = (float)$creditPackage['price'];
        $vat = (float)$creditPackage['vat'];
        $amountTL = $priceTL * (1 + $vat / 100);

        $itemName = $creditPackage['package_name'] ?? ($creditAmount . " Kontor");
        $itemId = 'credit_' . $creditPackage['id'];
        break;
}

if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "message" => implode("<hr>", $errors)
    ]);
    exit;
}

// --- Sipariş ID Oluştur ---
$orderId = 'SIP-' . strtoupper($paymentType) . '-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

// Tutar formatı (VizyonPOS *100 formatı)
$amount = intval($amountTL * 100);

// --- Kart Bilgileri ---
$card = [
    "holderName" => safeInput($post['card_name'] ?? ''),
    "number" => safeInput(str_replace(' ', '', $post['card_number'] ?? '')),
    "expireYear" => safeInput($post['expire_year'] ?? ''),
    "expireMonth" => safeInput($post['expire_month'] ?? ''),
    "cvc" => safeInput($post['cvc_code'] ?? ''),
];

// --- Müşteri Bilgileri (Tip bazlı) ---
if ($paymentType === 'credit') {
    // Kontor ödemeleri için müşteri bilgileri (hidden fields'tan geliyor)
    $customer = [
        "clientIp" => $_SERVER['REMOTE_ADDR'],
        "identity" => safeInput($post['identity_number'] ?? ''),
        "firstName" => safeInput($post['first_name'] ?? ''),
        "lastName" => safeInput($post['last_name'] ?? ''),
        "email" => safeInput($post['email'] ?? ''),
        "phoneNumber" => safeInput(str_replace(' ', '', $post['phone'] ?? '')),
        "country" => safeInput($post['country'] ?? 'Türkiye'),
        "state" => safeInput($post['city'] ?? ''),
        "city" => safeInput($post['district'] ?? ''),
        "zipCode" => safeInput($post['postal_code'] ?? ''),
        "address" => safeInput($post['address'] ?? '')
    ];
} elseif ($customerType === 'company') {
    $customer = [
        "clientIp" => $_SERVER['REMOTE_ADDR'],
        "identity" => safeInput($post['company_tax_number'] ?? ''), // Kurumsal için vergi no
        "firstName" => safeInput($post['company_authorized_name'] ?? ''),
        "lastName" => safeInput($post['company_authorized_lastname'] ?? ''),
        "email" => safeInput($post['company_email'] ?? ''),
        "phoneNumber" => safeInput(str_replace(' ', '', $post['company_phone'] ?? '')),
        "country" => safeInput($post['company_country'] ?? 'Türkiye'),
        "state" => safeInput($post['company_city'] ?? ''),
        "city" => safeInput($post['company_district'] ?? ''),
        "zipCode" => safeInput($post['company_postal_code'] ?? ''),
        "address" => safeInput($post['company_address'] ?? '')
    ];
} else {
    $customer = [
        "clientIp" => $_SERVER['REMOTE_ADDR'],
        "identity" => safeInput($post['private_identity_number'] ?? ''),
        "firstName" => safeInput($post['private_first_name'] ?? ''),
        "lastName" => safeInput($post['private_last_name'] ?? ''),
        "email" => safeInput($post['private_email'] ?? ''),
        "phoneNumber" => safeInput(str_replace(' ', '', $post['private_phone'] ?? '')),
        "country" => safeInput($post['private_country'] ?? 'Türkiye'),
        "state" => safeInput($post['private_city'] ?? ''),
        "city" => safeInput($post['private_district'] ?? ''),
        "zipCode" => safeInput($post['private_postal_code'] ?? ''),
        "address" => safeInput($post['private_address'] ?? '')
    ];
}

// --- Sipariş Bilgileri ---
$orderItems = [
    [
        "id" => $itemId,
        "name" => $itemName,
        "category" => ucfirst($paymentType),
        "unitPrice" => $amount,
        "quantity" => 1,
        "totalPrice" => $amount
    ]
];

// --- Fatura Bilgileri ---
// Farklı fatura adresi var mı?
$hasDifferentBilling = !empty($post['different_billing_address']) && $post['different_billing_address'] == '1';

if ($paymentType === 'credit') {
    // Kontor ödemeleri için fatura bilgileri (veritabanından gelen billing_* alanlarından)
    $companyName = safeInput($post['billing_company_name'] ?? ($post['first_name'] . ' ' . $post['last_name']));
    $billing = [
        "firstName" => safeInput($post['billing_first_name'] ?? $post['first_name']),
        "lastName" => safeInput($post['billing_last_name'] ?? $post['last_name']),
        "company" => $companyName,
        "phoneNumber" => safeInput(str_replace(' ', '', $post['phone'] ?? '')),
        "country" => safeInput($post['billing_country'] ?? 'Türkiye'),
        "state" => safeInput($post['billing_city'] ?? $post['city']),
        "city" => safeInput($post['billing_district'] ?? $post['district']),
        "zipCode" => safeInput($post['billing_postal_code'] ?? $post['postal_code']),
        "address" => safeInput($post['billing_address'] ?? $post['address'])
    ];
} elseif ($hasDifferentBilling) {
    // Farklı fatura bilgileri girilmiş
    $billingType = safeInput($post['billing_type'] ?? 'company');

    if ($billingType === 'company') {
        $companyName = safeInput($post['invoice_company_name'] ?? '');
        $billing = [
            "firstName" => safeInput($post['invoice_company_authorized_name'] ?? ''),
            "lastName" => safeInput($post['invoice_company_authorized_lastname'] ?? ''),
            "company" => $companyName,
            "phoneNumber" => safeInput(str_replace(' ', '', $post['invoice_company_phone'] ?? '')),
            "country" => safeInput($post['invoice_company_country'] ?? 'Türkiye'),
            "state" => safeInput($post['invoice_company_city'] ?? ''),
            "city" => safeInput($post['invoice_company_district'] ?? ''),
            "zipCode" => safeInput($post['invoice_company_postal_code'] ?? ''),
            "address" => safeInput($post['invoice_company_address'] ?? '')
        ];
    } else {
        // Bireysel fatura
        $companyName = safeInput($post['private_invoice_first_name'] ?? '') . ' ' . safeInput($post['private_invoice_last_name'] ?? '');
        $billing = [
            "firstName" => safeInput($post['private_invoice_first_name'] ?? ''),
            "lastName" => safeInput($post['private_invoice_last_name'] ?? ''),
            "company" => $companyName,
            "phoneNumber" => safeInput(str_replace(' ', '', $post['private_invoice_phone'] ?? '')),
            "country" => safeInput($post['private_invoice_country'] ?? 'Türkiye'),
            "state" => safeInput($post['private_invoice_city'] ?? ''),
            "city" => safeInput($post['private_invoice_district'] ?? ''),
            "zipCode" => safeInput($post['private_invoice_postal_code'] ?? ''),
            "address" => safeInput($post['private_invoice_address'] ?? '')
        ];
    }
} else {
    // Müşteri bilgileri ile aynı
    if ($customerType === 'company') {
        $companyName = safeInput($post['company_name'] ?? '');
        $billing = [
            "firstName" => safeInput($post['company_authorized_name'] ?? ''),
            "lastName" => safeInput($post['company_authorized_lastname'] ?? ''),
            "company" => $companyName,
            "phoneNumber" => safeInput(str_replace(' ', '', $post['company_phone'] ?? '')),
            "country" => safeInput($post['company_country'] ?? 'Türkiye'),
            "state" => safeInput($post['company_city'] ?? ''),
            "city" => safeInput($post['company_district'] ?? ''),
            "zipCode" => safeInput($post['company_postal_code'] ?? ''),
            "address" => safeInput($post['company_address'] ?? ''),
            "taxOffice" => safeInput($post['company_tax_office'] ?? ''),
            "taxNumber" => safeInput($post['company_tax_number'] ?? '')
        ];
    } else {
        $companyName = safeInput($post['private_first_name'] ?? '') . ' ' . safeInput($post['private_last_name'] ?? '');
        $billing = [
            "firstName" => safeInput($post['private_first_name'] ?? ''),
            "lastName" => safeInput($post['private_last_name'] ?? ''),
            "company" => $companyName,
            "phoneNumber" => safeInput(str_replace(' ', '', $post['private_phone'] ?? '')),
            "country" => safeInput($post['private_country'] ?? 'Türkiye'),
            "state" => safeInput($post['private_city'] ?? ''),
            "city" => safeInput($post['private_district'] ?? ''),
            "zipCode" => safeInput($post['private_postal_code'] ?? ''),
            "address" => safeInput($post['private_address'] ?? '')
        ];
    }
}

// --- Kargo Bilgileri (Müşteri bilgileri ile aynı) ---
if ($paymentType === 'credit') {
    // Kontor ödemeleri için kargo bilgileri
    $shipping = [
        "firstName" => safeInput($post['first_name'] ?? ''),
        "lastName" => safeInput($post['last_name'] ?? ''),
        "company" => safeInput($post['billing_company_name'] ?? ($post['first_name'] . ' ' . $post['last_name'])),
        "phoneNumber" => safeInput(str_replace(' ', '', $post['phone'] ?? '')),
        "country" => safeInput($post['country'] ?? 'Türkiye'),
        "state" => safeInput($post['city'] ?? ''),
        "city" => safeInput($post['district'] ?? ''),
        "zipCode" => safeInput($post['postal_code'] ?? ''),
        "address" => safeInput($post['address'] ?? '')
    ];
} elseif ($customerType === 'company') {
    $shipping = [
        "firstName" => safeInput($post['company_authorized_name'] ?? ''),
        "lastName" => safeInput($post['company_authorized_lastname'] ?? ''),
        "company" => safeInput($post['company_name'] ?? ''),
        "phoneNumber" => safeInput(str_replace(' ', '', $post['company_phone'] ?? '')),
        "country" => safeInput($post['company_country'] ?? 'Türkiye'),
        "state" => safeInput($post['company_city'] ?? ''),
        "city" => safeInput($post['company_district'] ?? ''),
        "zipCode" => safeInput($post['company_postal_code'] ?? ''),
        "address" => safeInput($post['company_address'] ?? '')
    ];
} else {
    $shipping = [
        "firstName" => safeInput($post['private_first_name'] ?? ''),
        "lastName" => safeInput($post['private_last_name'] ?? ''),
        "company" => safeInput($post['private_first_name'] ?? '') . ' ' . safeInput($post['private_last_name'] ?? ''),
        "phoneNumber" => safeInput(str_replace(' ', '', $post['private_phone'] ?? '')),
        "country" => safeInput($post['private_country'] ?? 'Türkiye'),
        "state" => safeInput($post['private_city'] ?? ''),
        "city" => safeInput($post['private_district'] ?? ''),
        "zipCode" => safeInput($post['private_postal_code'] ?? ''),
        "address" => safeInput($post['private_address'] ?? '')
    ];
}

// --- Client IP ---
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// --- Hash oluştur ---
$random = time(); // Random değerini bir kez oluştur
$dataToEncrypt = $orderId . $amount . $callbackUrl;
$computedHex = hash_hmac('sha256', $dataToEncrypt, $apiSecret);
$authorizationString = "APIKey:{$apiKey}&Random:{$random}&Signature:" . $computedHex;
$hash = base64_encode($authorizationString);

// --- JSON Body ---
$body = [
    "orderId" => $orderId,
    "amount" => $amount,
    "currency" => "TRY",
    "callbackUrl" => $callbackUrl,
    "callbackType" => "Post",
    "returnUrl" => $callbackUrl,
    "installment" => 1,
    "card" => $card,
    "customer" => $customer,
    "billing" => $billing,
    "shipping" => $shipping,
    "orderItems" => $orderItems
];

// --- Header ---
$headers = [
    "Content-Type: application/json",
    "Hash: $hash",
    "ApiKey: $apiKey",
    "Random: {$random}", // Aynı random değerini kullan
    "ClientIpAddress: " . $clientIp
];

// --- cURL ile POST ---
$ch = curl_init($baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

$response = curl_exec($ch);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// cURL hatası kontrolü
if ($curlErrno !== 0) {
    $debugLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'payment_type' => $paymentType,
        'order_id' => $orderId,
        'amount' => $amount,
        'curl_error' => $curlError,
        'curl_errno' => $curlErrno,
        'http_code' => $httpCode,
        'request_body' => $body,
        'headers' => $headers
    ];
    @file_put_contents(__DIR__ . '/../payment/unified_payment_error.log', json_encode($debugLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

    echo json_encode([
        "success" => false,
        "message" => "Ödeme sistemine bağlanılamadı: " . $curlError
    ]);
    exit;
}

$result = json_decode($response, true);

// 3D Secure formu
if (isset($result['succeeded']) && $result['succeeded'] && isset($result['data']['form3d'])) {
    $formHtml = $result['data']['form3d'];

    // Müşteri ve fatura bilgilerini snake_case'e çevir (Bifatura için)
    $customerDataSnake = convertToSnakeCase($customer);
    $billingDataSnake = convertToSnakeCase($billing);

    // Session'a kaydet (snake_case formatında)
    $_SESSION['orderId'] = $orderId;
    $_SESSION['amount'] = $amountTL;
    $_SESSION['payment_type'] = $paymentType;
    $_SESSION['item_id'] = $itemId;
    $_SESSION['form3d'] = $formHtml;
    $_SESSION['customer_data'] = $customerDataSnake;
    $_SESSION['billing_data'] = $billingDataSnake;

    // Order mapping dosyası (snake_case formatında - Bifatura için)
    $orderMapping = [
        'orderId' => $orderId,
        'payment_type' => $paymentType,
        'item_id' => $itemId,
        'amount' => $amountTL,
        'customer_data' => $customerDataSnake,
        'billing_data' => $billingDataSnake,
        'created_at' => date('Y-m-d H:i:s')
    ];
    @file_put_contents(__DIR__ . '/../payment/order_mapping_' . $orderId . '.json', json_encode($orderMapping), LOCK_EX);

    // --- Transactions tablosuna kaydet ---
    try {
        // Müşteri ID'si varsa al (credit payment'te customer_number üzerinden)
        $customerId = null;
        $billingAddressId = null;

        if ($paymentType === 'credit' && isset($customerData)) {
            $customerId = $customerData['id'] ?? null;

            // Varsayılan fatura adresini al
            $billing_addr_stmt = $pdo->prepare("SELECT id FROM customer_billing_addresses WHERE customer_id = ? AND is_default = 1 LIMIT 1");
            $billing_addr_stmt->execute([$customerId]);
            $defaultBillingAddress = $billing_addr_stmt->fetch(PDO::FETCH_ASSOC);
            $billingAddressId = $defaultBillingAddress['id'] ?? null;
        }

        // Fatura tipini belirle
        $finalBillingType = $hasDifferentBilling ? ($billingType ?? $customerType) : null;

        // Transaction kaydı oluştur
        $transaction_stmt = $pdo->prepare("
            INSERT INTO transactions
            (transaction_id, customer_id, billing_address_id, payment_type, package_id,
             first_name, last_name, email, phone, identity_number,
             country, city, district, postal_code, address,
             customer_type, billing_type,
             billing_first_name, billing_last_name, billing_company_name,
             billing_tax_number, billing_tax_office, billing_country,
             billing_city, billing_district, billing_postal_code, billing_address,
             amount, currency, status, vizyonpos_response)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Müşteri bilgilerini customer_type'a göre belirle
        $firstName = $customerType === 'company'
            ? safeInput($post['company_authorized_name'] ?? '')
            : safeInput($post['private_first_name'] ?? '');
        $lastName = $customerType === 'company'
            ? safeInput($post['company_authorized_lastname'] ?? '')
            : safeInput($post['private_last_name'] ?? '');
        $email = $customerType === 'company'
            ? safeInput($post['company_email'] ?? '')
            : safeInput($post['private_email'] ?? '');
        $identityNumber = $customerType === 'company'
            ? safeInput($post['company_tax_number'] ?? '')
            : safeInput($post['private_identity_number'] ?? '');
        $country = $customerType === 'company'
            ? safeInput($post['company_country'] ?? 'Türkiye')
            : safeInput($post['private_country'] ?? 'Türkiye');
        $city = $customerType === 'company'
            ? safeInput($post['company_city'] ?? '')
            : safeInput($post['private_city'] ?? '');
        $district = $customerType === 'company'
            ? safeInput($post['company_district'] ?? '')
            : safeInput($post['private_district'] ?? '');
        $postalCode = $customerType === 'company'
            ? safeInput($post['company_postal_code'] ?? '')
            : safeInput($post['private_postal_code'] ?? '');
        $address = $customerType === 'company'
            ? safeInput($post['company_address'] ?? '')
            : safeInput($post['private_address'] ?? '');

        // Fatura bilgileri
        $billingFirstName = $firstName;
        $billingLastName = $lastName;
        $billingTaxNumber = '';
        $billingTaxOffice = '';
        $billingCountry = $country;
        $billingCity = $city;
        $billingDistrict = $district;
        $billingPostalCode = $postalCode;
        $billingAddress = $address;

        if ($hasDifferentBilling) {
            $actualBillingType = $billingType ?? 'company';
            if ($actualBillingType === 'company') {
                $billingFirstName = safeInput($post['invoice_company_authorized_name'] ?? '');
                $billingLastName = safeInput($post['invoice_company_authorized_lastname'] ?? '');
                $billingTaxNumber = safeInput($post['invoice_company_tax_number'] ?? '');
                $billingTaxOffice = safeInput($post['invoice_company_tax_office'] ?? '');
                $billingCountry = safeInput($post['invoice_company_country'] ?? 'Türkiye');
                $billingCity = safeInput($post['invoice_company_city'] ?? '');
                $billingDistrict = safeInput($post['invoice_company_district'] ?? '');
                $billingPostalCode = safeInput($post['invoice_company_postal_code'] ?? '');
                $billingAddress = safeInput($post['invoice_company_address'] ?? '');
            } else {
                $billingFirstName = safeInput($post['private_invoice_first_name'] ?? '');
                $billingLastName = safeInput($post['private_invoice_last_name'] ?? '');
                $billingCountry = safeInput($post['private_invoice_country'] ?? 'Türkiye');
                $billingCity = safeInput($post['private_invoice_city'] ?? '');
                $billingDistrict = safeInput($post['private_invoice_district'] ?? '');
                $billingPostalCode = safeInput($post['private_invoice_postal_code'] ?? '');
                $billingAddress = safeInput($post['private_invoice_address'] ?? '');
            }
        } else {
            // Farklı fatura yoksa, müşteri tipine göre vergi bilgilerini al
            if ($customerType === 'company') {
                $billingTaxNumber = safeInput($post['company_tax_number'] ?? '');
                $billingTaxOffice = safeInput($post['company_tax_office'] ?? '');
            }
        }

        $transaction_stmt->execute([
            $orderId,
            $customerId,
            $billingAddressId,
            $paymentType,
            ($paymentType === 'package' ? $itemId : null),
            $firstName,
            $lastName,
            $email,
            $phoneNumber,
            $identityNumber,
            $country,
            $city,
            $district,
            $postalCode,
            $address,
            $customerType,  // customer_type
            $finalBillingType,  // billing_type
            $billingFirstName,
            $billingLastName,
            $companyName,
            $billingTaxNumber,
            $billingTaxOffice,
            $billingCountry,
            $billingCity,
            $billingDistrict,
            $billingPostalCode,
            $billingAddress,
            $amountTL,
            'TRY',
            'pending',
            json_encode($result)
        ]);

        $_SESSION['transaction_db_id'] = $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Transaction Insert Error: " . $e->getMessage());
        // Hata olsa bile ödemeye devam et
    }

    echo json_encode([
        "success" => true,
        "redirectUrl" => "/yonlendirme",
        "apiResponse" => $result, // API yanıtını da gönder
        "debug" => [
            "orderId" => $orderId,
            "amount" => $amountTL,
            "paymentId" => $result['data']['paymentId'] ?? null,
            "status" => $result['data']['status'] ?? null,
            "succeeded" => $result['succeeded'] ?? null
        ]
    ]);
    exit;
} else {
    $errorMessage = "Ödeme başlatılamadı";

    // HTTP hata kodlarına göre mesaj
    if ($httpCode == 502 || $httpCode == 503) {
        $errorMessage = "Ödeme sistemimiz geçici olarak hizmet dışı. Lütfen birkaç dakika sonra tekrar deneyin.";
    } elseif ($httpCode == 403) {
        $errorMessage = "Güvenlik doğrulaması başarısız. Lütfen daha sonra tekrar deneyin.";
    } elseif (isset($result['message'])) {
        $errorMessage = $result['message'];
    } elseif (isset($result['errorMessage'])) {
        $errorMessage = $result['errorMessage'];
    } elseif ($httpCode == 0) {
        $errorMessage = "Ödeme sistemine bağlanılamadı. İnternet bağlantınızı kontrol edin.";
    }

    // DEBUG LOG
    $debugLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'payment_type' => $paymentType,
        'order_id' => $orderId,
        'amount' => $amount,
        'http_code' => $httpCode,
        'raw_response' => $response,
        'api_response' => $result,
        'request_body' => $body,
        'headers' => $headers,
        'base_url' => $baseUrl
    ];
    @file_put_contents(__DIR__ . '/../payment/unified_payment_error.log', json_encode($debugLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

    echo json_encode([
        "success" => false,
        "message" => $errorMessage,
        "apiResponse" => $result, // API yanıtını hata durumunda da gönder
        "debug" => [
            "orderId" => $orderId,
            "amount" => $amount,
            "httpCode" => $httpCode,
            "curlError" => $curlError ?? null,
            "rawResponse" => substr($response ?? '', 0, 500)
        ],
        "errorInfo" => $result['errorInfo'] ?? null
    ]);
    exit;
}
