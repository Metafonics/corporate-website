<?php
/**
 * Bifatura API - Fatura Link Güncelleme Endpoint
 * POST /api/bifatura/invoice-link-update
 *
 * Bifatura'nın fatura linkini ve bilgilerini güncellemek için kullandığı endpoint
 * Fatura kesildikten sonra Bifatura bu endpoint'e fatura bilgilerini gönderir
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/bifatura.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/BifaturaService.php';

// Token doğrulama
$config = BifaturaConfig::getInstance();

// Header'dan token al
$headers = getallheaders();
$token = $headers['token'] ?? $headers['Token'] ?? null;

if (!$config->validateToken($token)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized',
        'message' => 'Geçersiz API token',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST body'den parametreleri al
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Callback'i logla
@file_put_contents(
    __DIR__ . '/../../payment/bifatura_invoice_callback.txt',
    date('Y-m-d H:i:s') . " - Callback received:\n" . print_r($data, true) . "\n\n",
    FILE_APPEND
);

$orderId = $data['orderId'] ?? null; // Transaction ID (order_id kolonu)
$faturaUrl = $data['faturaUrl'] ?? null;
$faturaNo = $data['faturaNo'] ?? null;
$faturaTarihi = $data['faturaTarihi'] ?? null; // "01.09.2023 14:27:09"

if (!$orderId || !$faturaUrl || !$faturaNo) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Bad Request',
        'message' => 'orderId, faturaUrl ve faturaNo gerekli',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $bifaturaService = new BifaturaService();

    // Fatura bilgilerini güncelle
    $result = $bifaturaService->updateInvoiceLink(
        $orderId,
        $faturaUrl,
        $faturaNo,
        $faturaTarihi
    );

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Fatura bilgileri başarıyla güncellendi',
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
