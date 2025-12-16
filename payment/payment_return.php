<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/bifatura.php';
require_once __DIR__ . '/../services/BifaturaService.php';

// 3D Secure'den dönen verileri al
$post = $_POST;

// Ödeme durumunu kontrol et
$orderId = $post['orderId'] ?? $_SESSION['orderId'] ?? null;
$status = $post['status'] ?? null;

if (!$orderId) {
    header("Location: /basarisiz");
    exit;
}

// Ödeme bilgilerini session'a kaydet
$_SESSION['orderId'] = $orderId;

if ($status === 'APPROVED' || $status === 'SUCCESS') {
    // Başarılı ödeme
    $amount = isset($post['amount']) ? $post['amount'] / 100 : ($_SESSION['amount'] ?? 0);
    $packageId = $_SESSION['packageId'] ?? $_SESSION['item_id'] ?? null;
    $paymentType = $_SESSION['payment_type'] ?? 'package';

    // Veritabanına kaydet
    if ($packageId) {
        try {
            // Önce bu order_id'nin olup olmadığını kontrol et
            $checkStmt = $pdo->prepare("SELECT id FROM orders WHERE order_id = ?");
            $checkStmt->execute([$orderId]);

            $existingOrder = $checkStmt->fetch();

            if (!$existingOrder) {
                // Yok ise ekle
                $stmt = $pdo->prepare("
                    INSERT INTO orders (order_id, package_id, payment_type, amount, status, customer_data, billing_data, payment_data, created_at)
                    VALUES (?, ?, ?, ?, 'APPROVED', ?, ?, ?, NOW())
                ");

                $customerData = json_encode($_SESSION['customer_data'] ?? []);
                $billingData = json_encode($_SESSION['billing_data'] ?? []);
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

                $orderDbId = $pdo->lastInsertId();
            } else {
                $orderDbId = $existingOrder['id'];
            }

            // Otomatik e-fatura kesme (Bifatura entegrasyonu aktifse)
            $bifaturaConfig = BifaturaConfig::getInstance();
            if ($bifaturaConfig->isAutoInvoiceEnabled() && $orderDbId) {
                try {
                    $bifaturaService = new BifaturaService();
                    $invoiceResult = $bifaturaService->createInvoiceForTransaction($orderDbId);

                    // Log fatura sonucunu
                    @file_put_contents(
                        __DIR__ . '/bifatura_invoice_log.txt',
                        date('Y-m-d H:i:s') . " - Order ID: {$orderId} - " .
                        ($invoiceResult['success'] ? 'SUCCESS' : 'FAILED') .
                        " - " . $invoiceResult['message'] . "\n\n",
                        FILE_APPEND
                    );
                } catch (Exception $e) {
                    // Fatura hatası ödeme akışını kesmesin
                    @file_put_contents(
                        __DIR__ . '/bifatura_error.txt',
                        date('Y-m-d H:i:s') . " - Order ID: {$orderId} - Error: " .
                        $e->getMessage() . "\n\n",
                        FILE_APPEND
                    );
                }
            }
        } catch (PDOException $e) {
            @file_put_contents(__DIR__ . '/return_error.txt', date('Y-m-d H:i:s') . " - DB Error: " . $e->getMessage() . "\n\n", FILE_APPEND);
        }
    }

    $_SESSION['paymentStatus'] = 'APPROVED';
    $_SESSION['amount'] = $amount;

    header("Location: /basarili");
    exit;
} else {
    // Başarısız ödeme
    $_SESSION['paymentStatus'] = 'FAILED';

    header("Location: /basarisiz");
    exit;
}
