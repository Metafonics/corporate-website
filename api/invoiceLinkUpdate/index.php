<?php
/**
 * Bifatura API - Fatura Link Güncelleme Endpoint
 * POST /api/invoiceLinkUpdate/
 *
 * Bifatura bu endpoint'e fatura oluşturulduktan sonra bilgi gönderir
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/BifaturaService.php';

// POST body'den parametreleri al
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debug: Bifatura'dan gelen callback'i logla
@file_put_contents(__DIR__ . '/../../payment/bifatura_invoice_callback.txt',
    date('Y-m-d H:i:s') . " - Bifatura'dan fatura callback geldi\n" .
    "POST Body: " . $input . "\n" .
    "Parsed Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n",
    FILE_APPEND
);

try {
    // Gerekli parametreleri al
    $orderId = $data['OrderId'] ?? $data['orderId'] ?? null;
    $invoiceUrl = $data['InvoiceUrl'] ?? $data['invoiceUrl'] ?? null;
    $invoiceNumber = $data['InvoiceNumber'] ?? $data['invoiceNumber'] ?? null;
    $invoiceDate = $data['InvoiceDate'] ?? $data['invoiceDate'] ?? null;
    $ettn = $data['ETTN'] ?? $data['ettn'] ?? null;

    if (!$orderId) {
        throw new Exception('OrderId parametresi eksik');
    }

    // BifaturaService ile fatura linkini güncelle
    $bifaturaService = new BifaturaService();
    $result = $bifaturaService->updateInvoiceLink($orderId, $invoiceUrl, $invoiceNumber, $invoiceDate);

    // ETTN varsa ayrıca kaydet
    if ($ettn && $result['success']) {
        $stmt = $pdo->prepare("
            UPDATE invoices i
            INNER JOIN orders o ON i.order_id = o.id
            SET i.ettn = ?
            WHERE o.order_id = ?
        ");
        $stmt->execute([$ettn, $orderId]);
    }

    if ($result['success']) {
        @file_put_contents(__DIR__ . '/../../payment/bifatura_invoice_callback.txt',
            date('Y-m-d H:i:s') . " - Fatura başarıyla güncellendi. OrderID: $orderId\n\n",
            FILE_APPEND
        );

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Fatura bilgileri başarıyla güncellendi'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception($result['message']);
    }

} catch (Exception $e) {
    @file_put_contents(__DIR__ . '/../../payment/bifatura_invoice_callback.txt',
        date('Y-m-d H:i:s') . " - HATA: " . $e->getMessage() . "\n\n",
        FILE_APPEND
    );

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
