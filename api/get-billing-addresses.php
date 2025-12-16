<?php
/**
 * Müşteri Fatura Adreslerini Getirme API
 *
 * POST /api/get-billing-addresses.php
 *
 * Request: { "customer_id": 123 }
 * Response: { "success": true, "addresses": [...] }
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

// Sadece POST isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST istekleri kabul edilir']);
    exit;
}

// POST verilerini al
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['customer_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Müşteri ID zorunludur'
    ]);
    exit;
}

try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare("
        SELECT
            id,
            billing_first_name,
            billing_last_name,
            billing_company_name,
            billing_tax_number,
            billing_tax_office,
            billing_country,
            billing_city,
            billing_district,
            billing_postal_code,
            billing_address,
            is_default,
            created_at,
            updated_at
        FROM customer_billing_addresses
        WHERE customer_id = ?
        ORDER BY is_default DESC, created_at DESC
    ");

    $stmt->execute([$data['customer_id']]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'addresses' => $addresses,
        'count' => count($addresses)
    ]);

} catch (Exception $e) {
    error_log("Get Billing Addresses Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Fatura adresleri alınırken bir hata oluştu: ' . $e->getMessage()
    ]);
}