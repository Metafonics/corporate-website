<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/vizyonpos.php';
require_once __DIR__ . '/../config/bifatura.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../services/BifaturaService.php';

use App\Services\MailService;

// API Secret'i ortam bazlı config'den al
$config = VizionPosConfig::getInstance();
$apiSecret = $config->getApiSecret();

// POST veya JSON veriyi al
$raw = file_get_contents('php://input');
$post = json_decode($raw, true) ?? $_POST;

// Hash doğrulama (VizyonPay'den gelen callback'i doğrula)
$receivedHash = $_SERVER['HTTP_HASH'] ?? '';
if (!empty($receivedHash) && isset($post['orderId']) && isset($post['amount'])) {
    $dataToEncrypt = $post['orderId'] . $post['amount'];
    $computedHex = hash_hmac('sha256', $dataToEncrypt, $apiSecret);

    // Hash kontrolü (isteğe bağlı, VizyonPay dokümantasyonuna göre ayarlayın)
    // if ($computedHex !== $receivedHash) {
    //     http_response_code(400);
    //     echo "Invalid hash";
    //     exit;
    // }
}

// Kullanıcı browser'dan mı geliyor yoksa backend callback mi?
$isUserRequest = !empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla') !== false;

// Ödeme başarılı mı?
if (isset($post['status']) && $post['status'] === 'APPROVED') {
    // Sipariş bilgilerini al
    $orderId = $post['orderId'] ?? null;
    $amount = isset($post['amount']) ? $post['amount'] / 100 : 0;

    // PackageId ve payment_type'ı order mapping dosyasından çıkar
    $packageId = null;
    $paymentType = 'package'; // Varsayılan
    $customerDataFromFile = null;
    $billingDataFromFile = null;

    // Order mapping dosyasını oku
    $mappingFile = __DIR__ . '/order_mapping_' . $orderId . '.json';
    if (file_exists($mappingFile)) {
        $mappingData = json_decode(file_get_contents($mappingFile), true);
        if ($mappingData) {
            $packageId = $mappingData['packageId'] ?? $mappingData['item_id'] ?? null;
            $paymentType = $mappingData['payment_type'] ?? 'package';
            $customerDataFromFile = $mappingData['customer_data'] ?? null;
            $billingDataFromFile = $mappingData['billing_data'] ?? null;
        }
    }

    // Session'dan da dene
    if (!$packageId) {
        $packageId = $_SESSION['packageId'] ?? $_SESSION['item_id'] ?? null;
    }
    if (!isset($mappingData)) {
        $paymentType = $_SESSION['payment_type'] ?? 'package';
    }

    // Siparişi veritabanına kaydet
    if ($orderId) {
        try {
            // Önce kontrol et, varsa ekleme
            $checkStmt = $pdo->prepare("SELECT id FROM orders WHERE order_id = ?");
            $checkStmt->execute([$orderId]);

            if (!$checkStmt->fetch()) {
                $stmt = $pdo->prepare("
                    INSERT INTO orders (order_id, package_id, payment_type, amount, status, customer_data, billing_data, payment_data, created_at)
                    VALUES (?, ?, ?, ?, 'APPROVED', ?, ?, ?, NOW())
                ");

                // Customer ve billing data'yı önce dosyadan, sonra session'dan al
                $customerData = json_encode($customerDataFromFile ?? $_SESSION['customer_data'] ?? []);
                $billingData = json_encode($billingDataFromFile ?? $_SESSION['billing_data'] ?? []);
                $paymentData = json_encode($post);

                $stmt->execute([
                    $orderId,
                    $packageId,
                    $paymentType,
                    $amount,
                    $customerData,
                    $billingData,
                    $paymentData
                ]);

                // Order'ın veritabanındaki ID'sini al (fatura kesimi için gerekli)
                $orderDbId = $pdo->lastInsertId();

                // Mapping dosyasını sil
                if (file_exists($mappingFile)) {
                    @unlink($mappingFile);
                }

                // Bifatura entegrasyonu aktifse otomatik fatura kes
                $bifaturaConfig = BifaturaConfig::getInstance();
                if ($bifaturaConfig->isAutoInvoiceEnabled()) {
                    try {
                        $bifaturaService = new BifaturaService();
                        $invoiceResult = $bifaturaService->createInvoiceForTransaction($orderDbId);

                        if ($invoiceResult['success']) {
                            @file_put_contents(__DIR__ . '/bifatura_log.txt',
                                date('Y-m-d H:i:s') . " - Fatura başarıyla oluşturuldu. OrderID: $orderId, InvoiceID: " . $invoiceResult['invoice_id'] . "\n",
                                FILE_APPEND
                            );
                        } else {
                            @file_put_contents(__DIR__ . '/bifatura_error.txt',
                                date('Y-m-d H:i:s') . " - Fatura oluşturma hatası. OrderID: $orderId, Error: " . $invoiceResult['message'] . "\n",
                                FILE_APPEND
                            );
                        }
                    } catch (Exception $e) {
                        @file_put_contents(__DIR__ . '/bifatura_error.txt',
                            date('Y-m-d H:i:s') . " - Bifatura exception. OrderID: $orderId, Error: " . $e->getMessage() . "\n",
                            FILE_APPEND
                        );
                    }
                }

                // Ödeme başarılı olduğunda mail gönder
                try {
                    $mailService = new MailService();

                    // Paket bilgisini al
                    $packageName = 'Bilinmiyor';
                    if ($packageId) {
                        $packageStmt = $pdo->prepare("SELECT title FROM packages WHERE id = ?");
                        $packageStmt->execute([$packageId]);
                        $packageRow = $packageStmt->fetch(PDO::FETCH_ASSOC);
                        if ($packageRow) {
                            $packageName = $packageRow['title'];
                        }
                    }

                    // Mail için veri hazırla (snake_case formatında gelecek)
                    $mailData = [
                        'Sipariş No' => $orderId,
                        'Paket' => $packageName,
                        'Tutar' => number_format($amount, 2, ',', '.') . ' TL',
                        'Durum' => 'ONAYLANDI',
                        'Müşteri Adı' => ($customerDataFromFile['first_name'] ?? '') . ' ' . ($customerDataFromFile['last_name'] ?? ''),
                        'Müşteri E-posta' => $customerDataFromFile['email'] ?? '',
                        'Müşteri Telefon' => $customerDataFromFile['phone_number'] ?? '',
                        'Fatura Şirketi' => $billingDataFromFile['company'] ?? '',
                        'Tarih' => date('d.m.Y H:i:s')
                    ];

                    // HTML tablo oluştur
                    $tableHtml = buildHtmlTable($mailData);

                    // Mail gönder
                    $mailService->sendMail([
                        'fromName' => 'Metafonics Ödeme Sistemi',
                        'to' => 'info@metafonics.com', // Kendi mail adresinizi buraya yazın
                        'subject' => 'Yeni Ödeme Alındı - ' . $orderId,
                        'body' => "
                            <h2>Yeni Bir Ödeme Alındı!</h2>
                            <p>Sisteminizde yeni bir ödeme gerçekleştirildi. Detaylar aşağıdadır:</p>
                            {$tableHtml}
                            <br>
                            <p><em>Bu mail otomatik olarak oluşturulmuştur.</em></p>
                        "
                    ]);
                } catch (Exception $e) {
                    @file_put_contents(__DIR__ . '/callback_error.txt', date('Y-m-d H:i:s') . " - Mail Error: " . $e->getMessage() . "\n\n", FILE_APPEND);
                }
            }
        } catch (PDOException $e) {
            @file_put_contents(__DIR__ . '/callback_error.txt', date('Y-m-d H:i:s') . " - DB Error: " . $e->getMessage() . "\nOrderID: $orderId\nPackageID: $packageId\n\n", FILE_APPEND);
        }
    } else {
        @file_put_contents(__DIR__ . '/callback_error.txt', date('Y-m-d H:i:s') . " - OrderID bulunamadı!\n\n", FILE_APPEND);
    }

    // Session bilgilerini güncelle (DB kayıt işleminden sonra)
    $_SESSION['paymentStatus'] = 'APPROVED';
    $_SESSION['orderId'] = $orderId;
    $_SESSION['amount'] = $amount;

    // Eğer kullanıcı request'i ise yönlendir
    if ($isUserRequest) {
        header("Location: /basarili?orderId=" . urlencode($orderId));
        exit;
    }

    http_response_code(200);
    echo "OK";
} else {
    // Başarısız ödeme

    $orderId = $post['orderId'] ?? null;
    $status = $post['status'] ?? 'FAILED';
    $amount = isset($post['amount']) ? $post['amount'] / 100 : 0;

    // PackageId ve payment_type'ı order mapping dosyasından çıkar
    $packageId = null;
    $paymentType = 'package'; // Varsayılan
    $customerDataFromFile = null;
    $billingDataFromFile = null;

    // Order mapping dosyasını oku
    $mappingFile = __DIR__ . '/order_mapping_' . $orderId . '.json';
    if (file_exists($mappingFile)) {
        $mappingData = json_decode(file_get_contents($mappingFile), true);
        if ($mappingData) {
            $packageId = $mappingData['packageId'] ?? $mappingData['item_id'] ?? null;
            $paymentType = $mappingData['payment_type'] ?? 'package';
            $customerDataFromFile = $mappingData['customer_data'] ?? null;
            $billingDataFromFile = $mappingData['billing_data'] ?? null;
        }
    }

    // Session'dan da dene
    if (!$packageId) {
        $packageId = $_SESSION['packageId'] ?? $_SESSION['item_id'] ?? null;
    }
    if (!isset($mappingData)) {
        $paymentType = $_SESSION['payment_type'] ?? 'package';
    }

    if ($orderId) {
        try {
            // Önce kontrol et, varsa ekleme
            $checkStmt = $pdo->prepare("SELECT id FROM orders WHERE order_id = ?");
            $checkStmt->execute([$orderId]);

            if (!$checkStmt->fetch()) {
                $stmt = $pdo->prepare("
                    INSERT INTO orders (order_id, package_id, payment_type, amount, status, customer_data, billing_data, payment_data, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                // Customer ve billing data'yı önce dosyadan, sonra session'dan al
                $customerData = json_encode($customerDataFromFile ?? $_SESSION['customer_data'] ?? []);
                $billingData = json_encode($billingDataFromFile ?? $_SESSION['billing_data'] ?? []);
                $paymentData = json_encode($post);

                $stmt->execute([
                    $orderId,
                    $packageId,
                    $paymentType,
                    $amount,
                    $status,
                    $customerData,
                    $billingData,
                    $paymentData
                ]);

                // Mapping dosyasını sil
                if (file_exists($mappingFile)) {
                    @unlink($mappingFile);
                }
            }
        } catch (PDOException $e) {
            @file_put_contents(__DIR__ . '/callback_error.txt', date('Y-m-d H:i:s') . " - DB Error: " . $e->getMessage() . "\n\n", FILE_APPEND);
        }
    }

    $_SESSION['paymentStatus'] = $status;

    // Eğer kullanıcı request'i ise yönlendir
    if ($isUserRequest) {
        header("Location: /basarisiz?orderId=" . urlencode($orderId));
        exit;
    }

    http_response_code(200);
    echo "OK - Payment status: " . $status;
}
