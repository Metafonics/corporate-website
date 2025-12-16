<?php
/**
 * Bifatura API - Sipariş Kargo Güncelleme Endpoint
 * POST /api/bifatura/order-cargo-update
 *
 * Bifatura'nın kargo takip kodunu ve sipariş durumunu güncellemek için kullandığı endpoint
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

$orderId = $data['orderId'] ?? null; // Transaction ID
$orderStatusId = $data['orderStatusId'] ?? null;
$cargoTrackingCode = $data['cargoTrackingCode'] ?? null;
$cargoTrackingCodeUrl = $data['cargoTrackingCodeUrl'] ?? null;
$cargoCompany = $data['cargoCompany'] ?? null;

if (!$orderId) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Bad Request',
        'message' => 'orderId gerekli',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $bifaturaService = new BifaturaService();

    // Kargo bilgilerini güncelle
    $result = $bifaturaService->updateCargoInfo(
        $orderId,
        $cargoTrackingCode,
        $cargoCompany,
        $cargoTrackingCodeUrl,
        $orderStatusId
    );

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Kargo bilgileri başarıyla güncellendi',
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
