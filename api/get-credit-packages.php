<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM credit_packages WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($packages) {
        // Fiyatları formatlayalım ve KDV'li fiyatı hesaplayalım
        foreach ($packages as &$package) {
            $package['price'] = (float)$package['price'];
            $package['unit_price'] = (float)$package['unit_price'];
            $package['vat'] = (float)$package['vat'];
            $package['credit_amount'] = (int)$package['credit_amount'];
            $package['is_popular'] = (bool)$package['is_popular'];

            // KDV'li fiyatı hesapla
            $package['price_with_vat'] = $package['price'] * (1 + $package['vat'] / 100);
            $package['vat_amount'] = $package['price'] * ($package['vat'] / 100);
        }

        echo json_encode([
            'success' => true,
            'data' => $packages
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aktif paket bulunamadı'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası oluştu'
    ]);
}
