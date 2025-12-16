<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions.php';

// POST isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek metodu'
    ]);
    exit;
}

$phone = safeInput($_POST['phone'] ?? '');

if (empty($phone)) {
    echo json_encode([
        'success' => false,
        'message' => 'Telefon numarası girilmedi'
    ]);
    exit;
}

// Telefon numarasını normalize et
function normalizePhone($phone) {
    // Sadece rakamları al
    $cleaned = preg_replace('/\D/', '', $phone);

    // Eğer 0 ile başlıyorsa ve 11 haneliyse, 0'ı kaldır
    if (strlen($cleaned) === 11 && strpos($cleaned, '0') === 0) {
        $cleaned = substr($cleaned, 1);
    }

    return $cleaned;
}

$normalizedPhone = normalizePhone($phone);

try {
    // MySQL'de rakamları karşılaştır (boşlukları ve 0'ı kaldır)
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE
        TRIM(LEADING '0' FROM REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', '')) = :phone1 OR
        REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', '') = :phone2 OR
        REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', '') = :phone3");

    $stmt->execute([
        'phone1' => $normalizedPhone,
        'phone2' => $normalizedPhone,
        'phone3' => '0' . $normalizedPhone
    ]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        // Eksik zorunlu alanları kontrol et
        $missingFields = [];
        $requiredFields = [
            'identity_number' => 'TC Kimlik Numarası',
            'first_name' => 'Ad',
            'last_name' => 'Soyad',
            'email' => 'E-posta',
            'city' => 'Şehir',
            'district' => 'İlçe',
            'postal_code' => 'Posta Kodu',
            'address' => 'Adres'
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($customer[$field])) {
                $missingFields[] = $label;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'customer_number' => $customer['customer_number'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'identity_number' => $customer['identity_number'],
                'country' => $customer['country'] ?? 'Türkiye',
                'city' => $customer['city'],
                'district' => $customer['district'],
                'postal_code' => $customer['postal_code'],
                'address' => $customer['address'],
                'credit_balance' => (int)$customer['credit_balance']
            ],
            'missing_fields' => $missingFields,
            'has_missing_fields' => count($missingFields) > 0
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Müşteri bulunamadı'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası oluştu'
    ]);
}
