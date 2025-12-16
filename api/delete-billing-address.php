<?php
/**
 * Müşteri Fatura Adresi Silme API
 *
 * POST /api/delete-billing-address.php
 *
 * Request: { "id": 123, "customer_id": 456 }
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

if (empty($data['id']) || empty($data['customer_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID ve müşteri ID zorunludur'
    ]);
    exit;
}

try {
    $pdo = getDbConnection();

    // Adresin varsayılan olup olmadığını kontrol et
    $stmt = $pdo->prepare("SELECT is_default FROM customer_billing_addresses WHERE id = ? AND customer_id = ?");
    $stmt->execute([$data['id'], $data['customer_id']]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$address) {
        echo json_encode([
            'success' => false,
            'message' => 'Fatura adresi bulunamadı'
        ]);
        exit;
    }

    // Varsayılan adresi silmeye çalışıyorsa uyar
    if ($address['is_default'] == 1) {
        // Başka adres var mı kontrol et
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM customer_billing_addresses WHERE customer_id = ?");
        $stmt->execute([$data['customer_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Varsayılan adresi silmeden önce başka bir adresi varsayılan yapmalısınız'
            ]);
            exit;
        }
    }

    // Adresi sil
    $stmt = $pdo->prepare("DELETE FROM customer_billing_addresses WHERE id = ? AND customer_id = ?");
    $result = $stmt->execute([$data['id'], $data['customer_id']]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Fatura adresi başarıyla silindi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Fatura adresi silinemedi'
        ]);
    }

} catch (Exception $e) {
    error_log("Delete Billing Address Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Fatura adresi silinirken bir hata oluştu: ' . $e->getMessage()
    ]);
}