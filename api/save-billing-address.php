<?php
/**
 * Müşteri Fatura Adresi Kaydetme API
 *
 * POST /api/save-billing-address.php
 *
 * Kullanım:
 * - Yeni fatura adresi ekleme
 * - Mevcut fatura adresi güncelleme
 * - Varsayılan adres seçimi
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

// Gerekli alanları kontrol et
$requiredFields = [
    'customer_id',
    'billing_first_name',
    'billing_last_name',
    'billing_city',
    'billing_district',
    'billing_postal_code',
    'billing_address'
];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Zorunlu alan eksik: {$field}"
        ]);
        exit;
    }
}

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // Eğer bu adres varsayılan olarak işaretlendiyse, diğer adreslerin varsayılan işaretini kaldır
    if (!empty($data['is_default']) && $data['is_default'] == 1) {
        $stmt = $pdo->prepare("UPDATE customer_billing_addresses SET is_default = 0 WHERE customer_id = ?");
        $stmt->execute([$data['customer_id']]);
    }

    // ID varsa güncelleme, yoksa yeni kayıt
    if (!empty($data['id'])) {
        // Güncelleme
        $stmt = $pdo->prepare("
            UPDATE customer_billing_addresses SET
                billing_first_name = ?,
                billing_last_name = ?,
                billing_company_name = ?,
                billing_tax_number = ?,
                billing_tax_office = ?,
                billing_country = ?,
                billing_city = ?,
                billing_district = ?,
                billing_postal_code = ?,
                billing_address = ?,
                is_default = ?,
                updated_at = NOW()
            WHERE id = ? AND customer_id = ?
        ");

        $result = $stmt->execute([
            $data['billing_first_name'],
            $data['billing_last_name'],
            $data['billing_company_name'] ?? null,
            $data['billing_tax_number'] ?? null,
            $data['billing_tax_office'] ?? null,
            $data['billing_country'] ?? 'Türkiye',
            $data['billing_city'],
            $data['billing_district'],
            $data['billing_postal_code'],
            $data['billing_address'],
            $data['is_default'] ?? 0,
            $data['id'],
            $data['customer_id']
        ]);

        $billingAddressId = $data['id'];
        $message = 'Fatura adresi başarıyla güncellendi';

    } else {
        // Yeni kayıt
        $stmt = $pdo->prepare("
            INSERT INTO customer_billing_addresses
            (customer_id, billing_first_name, billing_last_name, billing_company_name,
             billing_tax_number, billing_tax_office, billing_country, billing_city,
             billing_district, billing_postal_code, billing_address, is_default)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['customer_id'],
            $data['billing_first_name'],
            $data['billing_last_name'],
            $data['billing_company_name'] ?? null,
            $data['billing_tax_number'] ?? null,
            $data['billing_tax_office'] ?? null,
            $data['billing_country'] ?? 'Türkiye',
            $data['billing_city'],
            $data['billing_district'],
            $data['billing_postal_code'],
            $data['billing_address'],
            $data['is_default'] ?? 0
        ]);

        $billingAddressId = $pdo->lastInsertId();
        $message = 'Fatura adresi başarıyla kaydedildi';
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'billing_address_id' => $billingAddressId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Billing Address Save Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Fatura adresi kaydedilirken bir hata oluştu: ' . $e->getMessage()
    ]);
}