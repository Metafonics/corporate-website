<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /giris');
    exit;
}

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../config/database.php';

$success = '';
$error = '';
$form_data = [];
$billing_data = [];

// Form gönderildi mi?
if ($_POST) {
    // Form verilerini al ve temizle
    $form_data = [
        'customer_number' => safeInput($_POST['customer_number'] ?? ''),
        'first_name' => safeInput($_POST['first_name'] ?? ''),
        'last_name' => safeInput($_POST['last_name'] ?? ''),
        'email' => safeInput($_POST['email'] ?? ''),
        'phone' => safeInput($_POST['phone'] ?? ''),
        'identity_number' => safeInput($_POST['identity_number'] ?? ''),
        'country' => safeInput($_POST['country'] ?? ''),
        'city' => safeInput($_POST['city'] ?? ''),
        'district' => safeInput($_POST['district'] ?? ''),
        'postal_code' => safeInput($_POST['postal_code'] ?? ''),
        'address' => safeInput($_POST['address'] ?? ''),
        'credit_balance' => intval($_POST['credit_balance'] ?? 0)
    ];

    // Fatura adresi verilerini al
    $billing_data = [
        'billing_company_name' => safeInput($_POST['billing_company_name'] ?? ''),
        'billing_tax_number' => safeInput($_POST['billing_tax_number'] ?? ''),
        'billing_tax_office' => safeInput($_POST['billing_tax_office'] ?? ''),
        'billing_country' => safeInput($_POST['billing_country'] ?? ''),
        'billing_city' => safeInput($_POST['billing_city'] ?? ''),
        'billing_district' => safeInput($_POST['billing_district'] ?? ''),
        'billing_postal_code' => safeInput($_POST['billing_postal_code'] ?? ''),
        'billing_address' => safeInput($_POST['billing_address'] ?? ''),
        'is_default' => 1 // İlk fatura adresi varsayılan olarak ayarlanır
    ];

    // Basit validasyon
    $required_fields = ['customer_number', 'first_name', 'last_name', 'email', 'phone', 'identity_number'];
    $required_billing_fields = ['billing_city', 'billing_district', 'billing_postal_code', 'billing_address'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (empty($form_data[$field])) {
            $missing_fields[] = $field;
        }
    }

    foreach ($required_billing_fields as $field) {
        if (empty($billing_data[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        $error = 'Lütfen tüm zorunlu alanları doldurun.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi girin.';
    } elseif (strlen($form_data['identity_number']) !== 11) {
        $error = 'TC Kimlik numarası 11 haneli olmalıdır.';
    } else {
        try {
            // Transaction başlat
            $pdo->beginTransaction();

            // Müşteri numarasının benzersiz olup olmadığını kontrol et
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE customer_number = ?");
            $check_stmt->execute([$form_data['customer_number']]);

            if ($check_stmt->fetchColumn() > 0) {
                $error = 'Bu müşteri numarası zaten kullanılıyor. Lütfen farklı bir numara deneyin.';
                $pdo->rollBack();
            } else {
                // E-posta adresinin benzersiz olup olmadığını kontrol et
                $email_check_stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
                $email_check_stmt->execute([$form_data['email']]);

                if ($email_check_stmt->fetchColumn() > 0) {
                    $error = 'Bu e-posta adresi zaten kayıtlı. Lütfen farklı bir e-posta adresi kullanın.';
                    $pdo->rollBack();
                } else {
                    // Müşteriyi veritabanına kaydet
                    $insert_stmt = $pdo->prepare("
                        INSERT INTO customers (
                            customer_number, first_name, last_name, email, phone,
                            identity_number, country, city, district, postal_code,
                            address, credit_balance, created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                        )
                    ");

                    $insert_result = $insert_stmt->execute([
                        $form_data['customer_number'],
                        $form_data['first_name'],
                        $form_data['last_name'],
                        $form_data['email'],
                        $form_data['phone'],
                        $form_data['identity_number'],
                        $form_data['country'],
                        $form_data['city'],
                        $form_data['district'],
                        $form_data['postal_code'],
                        $form_data['address'],
                        $form_data['credit_balance']
                    ]);

                    if ($insert_result) {
                        $customer_id = $pdo->lastInsertId();

                        // Fatura adresini kaydet
                        $billing_stmt = $pdo->prepare("
                            INSERT INTO customer_billing_addresses (
                                customer_id,
                                billing_company_name, billing_tax_number, billing_tax_office,
                                billing_country, billing_city, billing_district,
                                billing_postal_code, billing_address, is_default, created_at
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                            )
                        ");

                        $billing_result = $billing_stmt->execute([
                            $customer_id,
                            $billing_data['billing_company_name'],
                            $billing_data['billing_tax_number'],
                            $billing_data['billing_tax_office'],
                            $billing_data['billing_country'],
                            $billing_data['billing_city'],
                            $billing_data['billing_district'],
                            $billing_data['billing_postal_code'],
                            $billing_data['billing_address'],
                            $billing_data['is_default']
                        ]);

                        if ($billing_result) {
                            $pdo->commit();
                            $success = 'Müşteri kaydı ve fatura adresi başarıyla oluşturuldu! Müşteri ID: ' . $customer_id;

                            // Form verilerini temizle
                            $form_data = [];
                            $billing_data = [];
                        } else {
                            $pdo->rollBack();
                            $error = 'Fatura adresi kaydı sırasında bir hata oluştu. Lütfen tekrar deneyin.';
                        }
                    } else {
                        $pdo->rollBack();
                        $error = 'Müşteri kaydı sırasında bir hata oluştu. Lütfen tekrar deneyin.';
                    }
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// Otomatik müşteri numarası üret
if (empty($form_data['customer_number'])) {
    $form_data['customer_number'] = 'MST' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Kaydı - Metafonics</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .user-info {
            background-color: #e9ecef;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
        }

        .user-info span {
            font-weight: 500;
            color: #495057;
        }

        .logout-btn {
            float: right;
            background: #dc3545;
            color: white;
            padding: 5px 15px;
            border: none;
            border-radius: 3px;
            text-decoration: none;
            font-size: 14px;
        }

        .form-container {
            padding: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
        }

        .form-group.full-width {
            flex: 100%;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group label.required::after {
            content: ' *';
            color: #dc3545;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            margin-right: 10px;
        }

        .form-actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .container {
                margin: 0 10px;
            }

            .header,
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Müşteri Kaydı</h1>
            <p>Yeni müşteri bilgilerini girin</p>
        </div>

        <div class="user-info">
            <span>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="/cikis" class="logout-btn">Çıkış Yap</a>
            <div style="clear: both;"></div>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_number" class="required">Müşteri Numarası:</label>
                        <input type="text" id="customer_number" name="customer_number" required
                            value="<?php echo htmlspecialchars($form_data['customer_number'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="required">Ad:</label>
                        <input type="text" id="first_name" name="first_name" required
                            value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="required">Soyad:</label>
                        <input type="text" id="last_name" name="last_name" required
                            value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="required">E-posta:</label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone" class="required">Telefon:</label>
                        <input type="tel" id="phone" name="phone" required
                            value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="identity_number" class="required">TC Kimlik No:</label>
                        <input type="text" id="identity_number" name="identity_number" required
                            maxlength="11" pattern="[0-9]{11}"
                            value="<?php echo htmlspecialchars($form_data['identity_number'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="credit_balance">Kredi Bakiyesi:</label>
                        <input type="number" id="credit_balance" name="credit_balance"
                            step="0.01" min="0"
                            value="<?php echo htmlspecialchars($form_data['credit_balance'] ?? '0'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="country">Ülke:</label>
                        <input type="text" id="country" name="country"
                            value="<?php echo htmlspecialchars($form_data['country'] ?? ''); ?>"
                            placeholder="Örn: Türkiye">
                    </div>

                    <div class="form-group">
                        <label for="city">Şehir:</label>
                        <input type="text" id="city" name="city"
                            value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="district">İlçe:</label>
                        <input type="text" id="district" name="district"
                            value="<?php echo htmlspecialchars($form_data['district'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="postal_code">Posta Kodu:</label>
                        <input type="text" id="postal_code" name="postal_code"
                            value="<?php echo htmlspecialchars($form_data['postal_code'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address">Adres:</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Fatura Adresi Bölümü -->
                <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #e1e1e1;">
                    <h3 style="color: #333; margin-bottom: 20px; text-align: center;">📄 Fatura Adresi Bilgileri</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_company_name">Şirket Adı:</label>
                            <input type="text" id="billing_company_name" name="billing_company_name"
                                value="<?php echo htmlspecialchars($billing_data['billing_company_name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="billing_tax_number">Vergi Numarası:</label>
                            <input type="text" id="billing_tax_number" name="billing_tax_number"
                                maxlength="11" pattern="[0-9]{10,11}"
                                value="<?php echo htmlspecialchars($billing_data['billing_tax_number'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_tax_office">Vergi Dairesi:</label>
                            <input type="text" id="billing_tax_office" name="billing_tax_office"
                                value="<?php echo htmlspecialchars($billing_data['billing_tax_office'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="billing_country">Fatura Ülkesi:</label>
                            <input type="text" id="billing_country" name="billing_country"
                                value="<?php echo htmlspecialchars($billing_data['billing_country'] ?? ''); ?>"
                                placeholder="Örn: Türkiye">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_city" class="required">Fatura Şehri:</label>
                            <input type="text" id="billing_city" name="billing_city" required
                                value="<?php echo htmlspecialchars($billing_data['billing_city'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="billing_district" class="required">Fatura İlçesi:</label>
                            <input type="text" id="billing_district" name="billing_district" required
                                value="<?php echo htmlspecialchars($billing_data['billing_district'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_postal_code" class="required">Fatura Posta Kodu:</label>
                            <input type="text" id="billing_postal_code" name="billing_postal_code" required
                                value="<?php echo htmlspecialchars($billing_data['billing_postal_code'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-secondary" onclick="copyFromCustomer()"
                                style="margin-top: 25px; padding: 8px 15px; font-size: 14px;">
                                📋 Müşteri Bilgilerinden Kopyala
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="billing_address" class="required">Fatura Adresi:</label>
                            <textarea id="billing_address" name="billing_address" rows="3" required><?php echo htmlspecialchars($billing_data['billing_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Temizle</button>
                    <button type="submit" class="btn">Müşteri Kaydı ve Fatura Adresi Oluştur</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function resetForm() {
            if (confirm('Formu temizlemek istediğinizden emin misiniz?')) {
                document.querySelector('form').reset();
                // Müşteri numarasını yeniden üret
                document.getElementById('customer_number').value = 'MST' + new Date().getFullYear() + String(Math.floor(Math.random() * 9999) + 1).padStart(4, '0');
            }
        }

        // Müşteri bilgilerini fatura adresine kopyala
        function copyFromCustomer() {
            if (confirm('Müşteri bilgileri fatura adresine kopyalanacak. Devam etmek istiyor musunuz?')) {
                document.getElementById('billing_country').value = document.getElementById('country').value;
                document.getElementById('billing_city').value = document.getElementById('city').value;
                document.getElementById('billing_district').value = document.getElementById('district').value;
                document.getElementById('billing_postal_code').value = document.getElementById('postal_code').value;
                document.getElementById('billing_address').value = document.getElementById('address').value;
            }
        }

        // TC Kimlik numarası sadece rakam girişi
        document.getElementById('identity_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Vergi numarası sadece rakam girişi
        document.getElementById('billing_tax_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Telefon numarası formatı (0xxx xxx xx xx)
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            
            // Eğer 0 ile başlamıyorsa, başına 0 ekle
            if (value.length > 0 && !value.startsWith('0')) {
                value = '0' + value;
            }
            
            // Maksimum 11 haneli olsun
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            // Format: 0xxx xxx xx xx
            if (value.length > 0) {
                if (value.length <= 4) {
                    this.value = value;
                } else if (value.length <= 7) {
                    this.value = value.slice(0, 4) + ' ' + value.slice(4);
                } else if (value.length <= 9) {
                    this.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7);
                } else {
                    this.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 9) + ' ' + value.slice(9);
                }
            }
        });
    </script>
</body>

</html>