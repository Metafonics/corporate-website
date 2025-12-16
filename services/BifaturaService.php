<?php

require_once __DIR__ . '/../config/bifatura.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Bifatura E-Fatura Servisi
 *
 * Ödeme işlemleri sonrası otomatik fatura kesme işlemlerini yönetir
 * NOT: orders tablosu ile çalışır
 */
class BifaturaService
{
    private $config;
    private $pdo;

    public function __construct()
    {
        $this->config = BifaturaConfig::getInstance();
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Order için fatura oluştur
     *
     * @param int $orderId orders tablosundaki id
     * @return array Sonuç
     */
    public function createInvoiceForTransaction($orderId)
    {
        try {
            // Order bilgilerini çek
            $order = $this->getOrderDetails($orderId);

            if (!$order) {
                throw new Exception("Sipariş bulunamadı: {$orderId}");
            }

            // Zaten fatura kesilmiş mi kontrol et
            $existingInvoice = $this->checkExistingInvoice($orderId);
            if ($existingInvoice) {
                return [
                    'success' => false,
                    'message' => 'Bu sipariş için zaten fatura kesilmiş',
                    'invoice_id' => $existingInvoice['id'],
                ];
            }

            // Fatura kaydı oluştur
            $invoiceId = $this->createInvoiceRecord($order);

            // Fatura kalemlerini ekle
            $this->createInvoiceItems($invoiceId, $order);

            // Order'a invoice_id'yi kaydet (eğer orders tablosunda invoice_id kolonu varsa)
            $this->linkInvoiceToOrder($orderId, $invoiceId);

            // Bifatura durumunu 'sent' olarak işaretle
            $this->updateInvoiceStatus($invoiceId, 'sent', 'Bifatura\'ya sipariş gönderilmeye hazır');

            return [
                'success' => true,
                'message' => 'Fatura kaydı başarıyla oluşturuldu',
                'invoice_id' => $invoiceId,
                'order_id' => $orderId,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Fatura oluşturma hatası: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Order detaylarını getir (orders tablosundan)
     */
    private function getOrderDetails($orderId)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                o.*,
                COALESCE(p.title, cp.package_name) as package_name,
                cp.credit_amount,
                COALESCE(p.price, cp.price) as price
            FROM orders o
            LEFT JOIN packages p ON o.package_id = p.id
            LEFT JOIN credit_packages cp ON o.package_id = cp.id
            WHERE o.id = ? AND o.status = 'APPROVED'
            LIMIT 1
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // JSON formatındaki customer_data ve billing_data'yı decode et
        $order['customer_data'] = json_decode($order['customer_data'] ?? '{}', true);
        $order['billing_data'] = json_decode($order['billing_data'] ?? '{}', true);

        return $order;
    }

    /**
     * Mevcut fatura kontrolü
     */
    private function checkExistingInvoice($orderId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id FROM invoices WHERE order_id = ? LIMIT 1
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fatura kaydı oluştur
     */
    private function createInvoiceRecord($order)
    {
        // Müşteri bilgilerini al
        $customerData = $order['customer_data'];
        $billingData = $order['billing_data'];

        // Müşteri adı - HEM camelCase HEM snake_case destekli
        $firstName = $billingData['firstName'] ?? $billingData['first_name'] ?? $customerData['firstName'] ?? $customerData['first_name'] ?? '';
        $lastName = $billingData['lastName'] ?? $billingData['last_name'] ?? $customerData['lastName'] ?? $customerData['last_name'] ?? '';
        $companyName = $billingData['company'] ?? $billingData['company_name'] ?? '';

        $customerName = trim($companyName ?: ($firstName . ' ' . $lastName));

        // KDV hesaplama
        $vatRate = $this->config->getSetting('default_vat_rate', 20.00);
        $totalTaxIncluding = $order['amount'];
        $totalTaxExcluding = round($totalTaxIncluding / (1 + ($vatRate / 100)), 2);
        $totalVat = round($totalTaxIncluding - $totalTaxExcluding, 2);

        $stmt = $this->pdo->prepare("
            INSERT INTO invoices (
                order_id,
                customer_name,
                customer_email,
                customer_phone,
                customer_identity_number,
                customer_tax_number,
                customer_tax_office,
                billing_address,
                billing_city,
                billing_district,
                billing_postal_code,
                billing_country,
                currency,
                total_amount_tax_excluding,
                total_amount_tax_including,
                total_vat_amount,
                bifatura_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        // İl ve İlçe mantığı: camelCase (state/city) vs snake_case (city/district)
        if (isset($billingData['state']) || isset($customerData['state'])) {
            // camelCase formatı (gerçek ödeme): state=İl, city=İlçe
            $billingCity = $billingData['state'] ?? $customerData['state'] ?? '';
            $billingDistrict = $billingData['city'] ?? $customerData['city'] ?? '';
        } else {
            // snake_case formatı (quick_test): city=İl, district=İlçe
            $billingCity = $billingData['city'] ?? $customerData['city'] ?? '';
            $billingDistrict = $billingData['district'] ?? $customerData['district'] ?? '';
        }

        $stmt->execute([
            $order['id'],
            $customerName,
            $customerData['email'] ?? $billingData['email'] ?? '',
            $customerData['phoneNumber'] ?? $customerData['phone'] ?? $billingData['phoneNumber'] ?? $billingData['phone'] ?? '',
            $customerData['identity'] ?? $customerData['identity_number'] ?? $billingData['identity'] ?? $billingData['identity_number'] ?? null,
            $billingData['tax_number'] ?? null,
            $billingData['tax_office'] ?? null,
            $billingData['address'] ?? $customerData['address'] ?? '',
            $billingCity,
            $billingDistrict,
            $billingData['zipCode'] ?? $billingData['postal_code'] ?? $customerData['zipCode'] ?? $customerData['postal_code'] ?? '',
            $billingData['country'] ?? $customerData['country'] ?? 'Türkiye',
            'TRY',
            $totalTaxExcluding,
            $totalTaxIncluding,
            $totalVat,
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Fatura kalemlerini oluştur
     */
    private function createInvoiceItems($invoiceId, $order)
    {
        $vatRate = $this->config->getSetting('default_vat_rate', 20.00);

        // Payment type'a göre ürün adı ve stok kodu belirle
        $paymentType = $order['payment_type'] ?? 'package';
        $productName = '';
        $productCode = '';

        switch ($paymentType) {
            case 'package':
                // Normal paket satın alma
                if ($order['package_id'] && $order['package_name']) {
                    $productName = $order['package_name'];
                    $productCode = 'PKG-' . $order['package_id'];
                } else {
                    $productName = 'Paket Satın Alma';
                    $productCode = 'PKG-UNKNOWN';
                }
                break;

            case 'credit':
                // Kontor satın alma
                if ($order['package_id'] && $order['package_name']) {
                    $productName = $order['package_name'];
                    $productCode = 'KONTOR-' . $order['package_id'];
                } else {
                    $productName = 'Kontor Paketi';
                    $productCode = 'KONTOR';
                }
                break;

            case 'custom':
                // Özel ödeme
                $productName = 'Özel Ödeme';
                $productCode = 'CUSTOM';
                break;

            default:
                // Fallback (eski kayıtlar için)
                $productName = 'Ürün/Hizmet';
                $productCode = 'ITEM';
                break;
        }

        // Fiyat hesaplama
        $totalTaxIncluding = $order['amount'];
        $totalTaxExcluding = round($totalTaxIncluding / (1 + ($vatRate / 100)), 2);
        $vatAmount = round($totalTaxIncluding - $totalTaxExcluding, 2);

        $stmt = $this->pdo->prepare("
            INSERT INTO invoice_items (
                invoice_id,
                product_id,
                product_code,
                product_name,
                quantity,
                quantity_type,
                unit_price_tax_excluding,
                unit_price_tax_including,
                vat_rate,
                total_tax_excluding,
                total_tax_including,
                vat_amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $invoiceId,
            $order['package_id'],
            $productCode,
            $productName,
            1, // Quantity
            $this->config->getSetting('quantity_type', 'Adet'),
            $totalTaxExcluding,
            $totalTaxIncluding,
            $vatRate,
            $totalTaxExcluding,
            $totalTaxIncluding,
            $vatAmount,
        ]);
    }

    /**
     * Order'a invoice_id'yi bağla
     */
    private function linkInvoiceToOrder($orderId, $invoiceId)
    {
        // orders tablosunda invoice_id kolonu var mı kontrol et
        try {
            $stmt = $this->pdo->prepare("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'orders'
                AND COLUMN_NAME = 'invoice_id'
            ");
            $stmt->execute();
            $hasColumn = $stmt->fetch();

            if ($hasColumn) {
                // Kolon varsa güncelle
                $stmt = $this->pdo->prepare("UPDATE orders SET invoice_id = ? WHERE id = ?");
                $stmt->execute([$invoiceId, $orderId]);
            }
        } catch (Exception $exception) {
            // Hata olursa devam et (kritik değil)
            // orders tablosunda invoice_id kolonu yoksa sorun değil
        }
    }

    /**
     * Fatura durumunu güncelle
     */
    public function updateInvoiceStatus($invoiceId, $status, $message = null, $responseData = null)
    {
        $bifaturaSentAt = null;
        if ($status === 'sent') {
            $bifaturaSentAt = date('Y-m-d H:i:s');
        }

        $stmt = $this->pdo->prepare("
            UPDATE invoices
            SET bifatura_status = ?,
                bifatura_sent_at = COALESCE(?, bifatura_sent_at),
                bifatura_response = COALESCE(?, bifatura_response),
                error_message = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $bifaturaSentAt,
            $responseData ? json_encode($responseData, JSON_UNESCAPED_UNICODE) : null,
            $message,
            $invoiceId,
        ]);
    }

    /**
     * Fatura linkini ve bilgilerini güncelle (Bifatura'dan gelecek)
     */
    public function updateInvoiceLink($orderId, $invoiceUrl, $invoiceNumber, $invoiceDate)
    {
        try {
            // order_id'den (transaction_id) invoice_id bul
            $stmt = $this->pdo->prepare("
                SELECT i.id
                FROM invoices i
                INNER JOIN orders o ON i.order_id = o.id
                WHERE o.order_id = ?
                LIMIT 1
            ");
            $stmt->execute([$orderId]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$invoice) {
                throw new Exception("Fatura bulunamadı");
            }

            // Fatura bilgilerini güncelle
            $stmt = $this->pdo->prepare("
                UPDATE invoices
                SET invoice_url = ?,
                    invoice_number = ?,
                    invoice_date = ?,
                    bifatura_status = 'invoiced'
                WHERE id = ?
            ");

            $stmt->execute([
                $invoiceUrl,
                $invoiceNumber,
                date('Y-m-d H:i:s', strtotime($invoiceDate)),
                $invoice['id'],
            ]);

            return [
                'success' => true,
                'message' => 'Fatura bilgileri güncellendi',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Fatura güncelleme hatası: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Kargo bilgilerini güncelle (Bifatura'dan gelecek)
     */
    public function updateCargoInfo($orderId, $cargoTrackingCode, $cargoCompany, $cargoTrackingUrl, $orderStatusId)
    {
        try {
            // orders tablosunda bu kolonlar var mı kontrol et
            $stmt = $this->pdo->prepare("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'orders'
                AND COLUMN_NAME IN ('cargo_tracking_code', 'cargo_company', 'cargo_tracking_url', 'order_status_id')
            ");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($columns) > 0) {
                // Kolonlar varsa güncelle
                $updateFields = [];
                $params = [];

                if (in_array('cargo_tracking_code', $columns)) {
                    $updateFields[] = "cargo_tracking_code = ?";
                    $params[] = $cargoTrackingCode;
                }
                if (in_array('cargo_company', $columns)) {
                    $updateFields[] = "cargo_company = ?";
                    $params[] = $cargoCompany;
                }
                if (in_array('cargo_tracking_url', $columns)) {
                    $updateFields[] = "cargo_tracking_url = ?";
                    $params[] = $cargoTrackingUrl;
                }
                if (in_array('order_status_id', $columns)) {
                    $updateFields[] = "order_status_id = ?";
                    $params[] = $orderStatusId;
                }

                if (count($updateFields) > 0) {
                    $params[] = $orderId;
                    $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE order_id = ?";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($params);
                }
            }

            return [
                'success' => true,
                'message' => 'Kargo bilgileri güncellendi',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Kargo güncelleme hatası: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fatura detaylarını getir
     */
    public function getInvoiceDetails($invoiceId)
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*,
                   o.order_id,
                   o.created_at as order_date
            FROM invoices i
            LEFT JOIN orders o ON i.order_id = o.id
            WHERE i.id = ?
        ");
        $stmt->execute([$invoiceId]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            return null;
        }

        // Fatura kalemlerini getir
        $stmt = $this->pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $stmt->execute([$invoiceId]);
        $invoice['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $invoice;
    }
}
