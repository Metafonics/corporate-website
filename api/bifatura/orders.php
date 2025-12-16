<?php

/**
 * Bifatura API - Siparişler Endpoint
 * POST /api/bifatura/orders
 *
 * Bifatura'nın siparişleri çekmek için kullandığı endpoint
 * Tarih aralığına ve sipariş durumuna göre siparişleri döner
 * NOT: orders tablosu ile çalışır
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/bifatura.php';
require_once __DIR__ . '/../../config/database.php';

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

$orderStatusId = $data['orderStatusId'] ?? null;
$startDateTime = $data['startDateTime'] ?? null; // "01.11.2019 00:00:00"
$endDateTime = $data['endDateTime'] ?? null;     // "01.12.2019 23:59:59"

// Tarih formatını dönüştür (dd.mm.yyyy HH:ii:ss -> Y-m-d H:i:s)
function convertDateFormat($dateStr)
{
    if (!$dateStr) return null;
    $date = DateTime::createFromFormat('d.m.Y H:i:s', $dateStr);
    return $date ? $date->format('Y-m-d H:i:s') : null;
}

$startDate = convertDateFormat($startDateTime);
$endDate = convertDateFormat($endDateTime);

try {
    // Siparişleri çek (orders tablosundan)
    $query = "
        SELECT
            o.id as OrderId,
            o.order_id as OrderCode,
            DATE_FORMAT(o.created_at, '%d.%m.%Y %H:%i:%s') as OrderDate,
            o.customer_data,
            o.billing_data,
            o.amount,
            o.package_id,

            -- Fatura Bilgileri
            i.total_amount_tax_excluding as TotalPaidTaxExcluding,
            i.total_amount_tax_including as TotalPaidTaxIncluding,
            i.invoice_type_id as InvoiceTypeId,
            DATE_FORMAT(i.invoice_date, '%d.%m.%Y %H:%i:%s') as InvoiceDate,
            i.e_invoice_profile_id as EInvoiceProfileId,
            i.ettn as ETTN

        FROM orders o
        LEFT JOIN invoices i ON o.id = i.order_id
        WHERE o.status = 'APPROVED'
    ";

    $params = [];

    // Tarih filtresi
    if ($startDate && $endDate) {
        $query .= " AND o.created_at BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
    }

    // Sipariş durumu filtresi (eğer orders tablosunda order_status_id varsa)
    if ($orderStatusId) {
        $query .= " AND o.order_status_id = ?";
        $params[] = $orderStatusId;
    }

    $query .= " ORDER BY o.created_at DESC LIMIT 100";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orderRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Her sipariş için detayları ekle
    $orders = [];
    foreach ($orderRows as $row) {
        // JSON formatındaki verileri decode et
        $customerData = json_decode($row['customer_data'] ?? '{}', true);
        $billingData = json_decode($row['billing_data'] ?? '{}', true);

        // Müşteri bilgileri - HEM camelCase HEM snake_case destekli
        $firstName = $customerData['firstName'] ?? $customerData['first_name'] ?? '';
        $lastName = $customerData['lastName'] ?? $customerData['last_name'] ?? '';
        $email = $customerData['email'] ?? '';
        $phone = $customerData['phoneNumber'] ?? $customerData['phone'] ?? '';
        $identityNumber = $customerData['identity'] ?? $customerData['identity_number'] ?? '';

        // Fatura bilgileri - HEM camelCase HEM snake_case destekli
        $billingFirstName = $billingData['firstName'] ?? $billingData['first_name'] ?? $firstName;
        $billingLastName = $billingData['lastName'] ?? $billingData['last_name'] ?? $lastName;
        $billingCompanyName = $billingData['company'] ?? $billingData['company_name'] ?? '';
        $billingName = $billingCompanyName ?: trim($billingFirstName . ' ' . $billingLastName);
        $billingAddress = $billingData['address'] ?? $customerData['address'] ?? '';

        // İl ve İlçe mantığı: camelCase (state/city) vs snake_case (city/district)
        // camelCase: state=İl, city=İlçe
        // snake_case: city=İl, district=İlçe
        if (isset($billingData['state']) || isset($customerData['state'])) {
            // camelCase formatı (gerçek ödeme)
            $billingCity = $billingData['state'] ?? $customerData['state'] ?? '';
            $billingDistrict = $billingData['city'] ?? $customerData['city'] ?? '';
        } else {
            // snake_case formatı (quick_test)
            $billingCity = $billingData['city'] ?? $customerData['city'] ?? '';
            $billingDistrict = $billingData['district'] ?? $customerData['district'] ?? '';
        }

        $billingCountry = $billingData['country'] ?? $customerData['country'] ?? 'Türkiye';
        $billingPostalCode = $billingData['zipCode'] ?? $billingData['postal_code'] ?? $customerData['zipCode'] ?? $customerData['postal_code'] ?? '';
        $taxOffice = $billingData['tax_office'] ?? '';
        $taxNumber = $billingData['tax_number'] ?? '';

        // Sipariş kalemlerini çek (invoice_items'dan)
        $orderDetails = [];
        if ($row['OrderId']) {
            $itemStmt = $pdo->prepare("
                SELECT
                    ii.product_id as ProductId,
                    ii.product_code as ProductCode,
                    ii.barcode as Barcode,
                    ii.product_name as ProductName,
                    ii.product_description as ProductNote,
                    ii.quantity_type as ProductQuantityType,
                    ii.quantity as ProductQuantity,
                    ii.vat_rate as VatRate,
                    ii.unit_price_tax_excluding as ProductUnitPriceTaxExcluding,
                    ii.unit_price_tax_including as ProductUnitPriceTaxIncluding,
                    ii.discount_amount_tax_excluding as DiscountUnitTaxExcluding,
                    ii.discount_amount_tax_including as DiscountUnitTaxIncluding
                FROM invoice_items ii
                INNER JOIN invoices i ON ii.invoice_id = i.id
                WHERE i.order_id = ?
            ");
            $itemStmt->execute([$row['OrderId']]);
            $orderDetails = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Tutar hesaplama (eğer fatura yoksa amount'tan hesapla)
        $totalTaxIncluding = $row['TotalPaidTaxIncluding'] ?? $row['amount'];
        $totalTaxExcluding = $row['TotalPaidTaxExcluding'] ?? round($row['amount'] / 1.20, 2);

        // Sipariş nesnesini oluştur
        $order = [
            'OrderId' => (int)$row['OrderId'],
            'OrderCode' => $row['OrderCode'],
            'OrderDate' => $row['OrderDate'],
            'CustomerId' => 0, // orders tablosunda customer_id yok

            // Fatura Bilgileri
            'BillingName' => $billingName,
            'BillingAddress' => $billingAddress,
            'BillingTown' => $billingDistrict,
            'BillingCity' => $billingCity,
            'BillingMobilePhone' => $phone,
            'BillingPhone' => $phone,
            'Email' => $email,

            // Teslimat Bilgileri (Aynı adres - dijital ürün)
            'ShippingId' => (int)$row['OrderId'],
            'ShippingName' => trim($firstName . ' ' . $lastName),
            'ShippingAddress' => $billingAddress,
            'ShippingTown' => $billingDistrict,
            'ShippingCity' => $billingCity,
            'ShippingCountry' => $billingCountry,
            'ShippingZipCode' => $billingPostalCode,
            'ShippingPhone' => $phone,
            'ShipCompany' => '', // Dijital ürün - kargo yok

            // Ödeme Bilgileri
            'PaymentTypeId' => 1, // Varsayılan: Kredi Kartı
            'PaymentType' => 'Kredi Kartı',
            'Currency' => 'TRY',
            'CurrencyRate' => 1.0,

            // Tutar Bilgileri
            'TotalPaidTaxExcluding' => (float)$totalTaxExcluding,
            'TotalPaidTaxIncluding' => (float)$totalTaxIncluding,
            'ProductsTotalTaxExcluding' => (float)$totalTaxExcluding,
            'ProductsTotalTaxIncluding' => (float)$totalTaxIncluding,

            // Sipariş Kalemleri
            'OrderDetails' => $orderDetails,
        ];

        // Fatura türüne göre kimlik numarası ekle
        if ($taxNumber) {
            // Kurumsal fatura - Vergi numarası
            $order['TaxOffice'] = $taxOffice;
            $order['TaxNo'] = $taxNumber;
        } else {
            // Bireysel fatura - TC Kimlik numarası
            $order['SSNTCNo'] = $identityNumber;
        }

        if ($row['InvoiceTypeId']) {
            $order['InvoiceTypeId'] = (int)$row['InvoiceTypeId'];
        }

        if ($row['InvoiceDate']) {
            $order['InvoiceDate'] = $row['InvoiceDate'];
        }

        if (!empty($row['EInvoiceProfileId'])) {
            $order['EInvoiceProfileId'] = (int)$row['EInvoiceProfileId'];
        }

        if (!empty($row['ETTN'])) {
            $order['ETTN'] = $row['ETTN'];
        }

        $orders[] = $order;
    }

    // Response
    $response = [
        'Orders' => $orders,
    ];

    http_response_code(200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
