<?php
$pageTitle = "Kontor Satın Al";
$pageDescription = "Müşteri numaranızla hızlıca kontor satın alabilirsiniz.";
include(__DIR__ . "/../layout/header.php");
?>

<!-- Sayfa Başlığı -->
<section class="page-title">
    <div class="page-title-icon" style="background-image:url(/assets/images/icons/page-title_icon-1.png)"></div>
    <div class="page-title-icon-two" style="background-image:url(/assets/images/icons/page-title_icon-2.png)"></div>
    <div class="page-title-shadow" style="background-image:url(/assets/images/background/page-title-1.png)"></div>
    <div class="page-title-shadow_two" style="background-image:url(/assets/images/background/page-title-2.png)"></div>
    <div class="auto-container">
        <h1>Kontor Satın Al</h1>
        <ul class="bread-crumb clearfix">
            <li><a href="/">Anasayfa</a></li>
            <li>Kontor Satın Al</li>
        </ul>
    </div>
</section>

<!-- Kontor Satın Alma Formu -->
<section class="mt-5" style="margin-bottom: 150px;">
    <div class="auto-container default-form">
        <div class="column col-lg-8 col-md-12 col-sm-12 m-auto">

            <!-- Step Radio Inputs -->
            <input type="radio" name="step" id="step1" checked hidden>
            <input type="radio" name="step" id="step2" hidden>
            <input type="radio" name="step" id="step3" hidden>

            <!-- Step Indicators -->
            <div class="steps">
                <div class="step s1" data-step="1"><span>Müşteri & Kontor</span></div>
                <div class="step s2" data-step="2"><span>Kart Bilgileri</span></div>
                <div class="step s3" data-step="3"><span>Özet</span></div>
            </div>

            <!-- Form Başlangıcı -->
            <form id="credit-purchase-form" class="validateForm">
                <input type="hidden" name="payment_type" value="credit">

                <div class="contents">

                    <!-- Step1: Müşteri Numarası ve Kontor Seçimi -->
                    <div class="step-content content1">
                        <div class="form-group">
                            <label>Telefon Numaranız*</label>
                            <input type="text" name="phone" id="phone" placeholder="Örn: 05XX XXX XX XX" data-required="true" data-type="phone" maxlength="15">
                            <div class="error-msg"></div>
                            <small class="text-muted">Telefon numaranızı girin, bilgileriniz otomatik dolacaktır.</small>
                        </div>

                        <div id="customer-info" style="display: none; background: var(--main-color); padding: 25px; border-radius: 12px; margin: 20px 0; box-shadow: 0 4px 15px rgba(var(--main-color-rgb), 0.3);">
                            <h4 class="mb-3" style="color: white; font-weight: 600;">
                                <i class="fas fa-user-check" style="margin-right: 8px;"></i>Müşteri Bilgileri
                            </h4>
                            <div style="background: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 8px;">
                                <p style="margin-bottom: 12px; color: #2c3e50;"><strong>Ad Soyad:</strong> <span id="customer-name" style="color: var(--main-color); font-weight: 600;"></span></p>
                                <p style="margin-bottom: 12px; color: #2c3e50;"><strong>Telefon:</strong> <span id="customer-phone" style="color: var(--main-color); font-weight: 600;"></span></p>
                                <p style="margin-bottom: 0; color: #2c3e50;"><strong>E-posta:</strong> <span id="customer-email" style="color: var(--main-color); font-weight: 600;"></span></p>
                            </div>
                        </div>

                        <h3 class="text-light mb-4 mt-5">Kontor Paketi Seçin</h3>

                        <div class="form-group">
                            <div class="credit-packages" id="credit-packages-container">
                                <!-- Paketler dinamik olarak yüklenecek -->
                                <div class="text-center" style="padding: 40px;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Yükleniyor...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="error-msg"></div>
                        </div>

                        <!-- Hidden fields for customer data -->
                        <input type="hidden" name="customer_number" id="hidden_customer_number">
                        <input type="hidden" name="first_name" id="hidden_first_name">
                        <input type="hidden" name="last_name" id="hidden_last_name">
                        <input type="hidden" name="email" id="hidden_email">
                        <input type="hidden" name="identity_number" id="hidden_identity">
                        <input type="hidden" name="country" value="Türkiye">
                        <input type="hidden" name="city" id="hidden_city">
                        <input type="hidden" name="district" id="hidden_district">
                        <input type="hidden" name="postal_code" id="hidden_postal">
                        <input type="hidden" name="address" id="hidden_address">

                        <div class="form-group">
                            <div class="buttons">
                                <button type="button" class="template-btn btn-style-one next-step" data-next="step2" disabled>
                                    <span class="btn-wrap"><span class="text-one">İleri</span><span class="text-two">İleri</span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step2: Kart Bilgileri -->
                    <div class="step-content content2">
                        <h3 class="text-light mb-4">Kart Bilgileri</h3>

                        <div class="form-group">
                            <label>Kart Üzerindeki İsim*</label>
                            <input type="text" name="card_name" placeholder="Kart üzerindeki isim" value="" data-required="true" data-type="text">
                            <div class="error-msg"></div>
                        </div>

                        <div class="form-group">
                            <label>Kart Numarası*</label>
                            <input type="text" name="card_number" placeholder="0000 0000 0000 0000" value="" maxlength="19" data-required="true" data-type="creditcard">
                            <div class="error-msg"></div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Yıl*</label>
                                <select name="expire_year" data-required="true">
                                    <option value="">Yıl Seçiniz</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <option value="2027">2027</option>
                                    <option value="2028">2028</option>
                                    <option value="2029">2029</option>
                                    <option value="2030">2030</option>
                                    <option value="2031">2031</option>
                                    <option value="2032">2032</option>
                                    <option value="2033">2033</option>
                                    <option value="2034">2034</option>
                                    <option value="2035">2035</option>
                                </select>
                                <span class="error-msg"></span>
                            </div>
                            <div class="col-md-4">
                                <label>Ay*</label>
                                <select name="expire_month" data-required="true">
                                    <option value="">Ay Seçiniz</option>
                                    <option value="01">Ocak</option>
                                    <option value="02">Şubat</option>
                                    <option value="03">Mart</option>
                                    <option value="04">Nisan</option>
                                    <option value="05">Mayıs</option>
                                    <option value="06">Haziran</option>
                                    <option value="07">Temmuz</option>
                                    <option value="08">Ağustos</option>
                                    <option value="09">Eylül</option>
                                    <option value="10">Ekim</option>
                                    <option value="11">Kasım</option>
                                    <option value="12">Aralık</option>
                                </select>
                                <span class="error-msg"></span>
                            </div>
                            <div class="col-md-4">
                                <div class="cvc-code-wrap">
                                    <label>CVC Kodu*</label>
                                    <input type="password" name="cvc_code" id="cvc_code_credit" placeholder="000" value="" data-required="true" data-type="number_3">
                                    <i class="fas fa-eye" id="toggleCVC"></i>
                                </div>
                                <div class="error-msg"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="buttons">
                                <button type="button" class="template-btn btn-style-one prev-step" data-prev="step1">
                                    <span class="btn-wrap"><span class="text-one">Geri</span><span class="text-two">Geri</span></span>
                                </button>
                                <button type="button" class="template-btn btn-style-one next-step" data-next="step3">
                                    <span class="btn-wrap"><span class="text-one">İleri</span><span class="text-two">İleri</span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step3: Özet -->
                    <div class="step-content content3">
                        <h3 class="text-light mb-4">Sipariş Özeti</h3>

                        <div class="summary-item">
                            <span>Müşteri No:</span>
                            <span id="summary-customer"></span>
                        </div>

                        <div class="summary-item">
                            <span>Kontor Miktarı:</span>
                            <span id="summary-credit"></span>
                        </div>

                        <div class="summary-item">
                            <span>Paket Fiyatı:</span>
                            <span id="summary-base-price"></span>
                        </div>

                        <div class="summary-item">
                            <span>KDV:</span>
                            <span id="summary-vat"></span>
                        </div>

                        <div class="summary-total">
                            <span>Ödenecek Toplam:</span>
                            <span id="summary-total"></span>
                        </div>

                        <div class="form-group my-5">
                            <label>
                                <input type="checkbox" name="contract_agreement" required>
                                <a href="/mesafeli-satis-eticaret-sozlesmesi" target="_blank" class="legal-link">Mesafeli Satış Sözleşmesini</a> okudum, kabul ediyorum.
                            </label>
                            <label>
                                <input type="checkbox" name="privacy_agreement" required>
                                <a href="/kisisel-verilerin-korunma-politikasi" target="_blank" class="legal-link">Kişisel verilerin işlenmesine ilişkin aydınlatma metnini</a> okudum, kabul ediyorum.
                            </label>
                        </div>

                        <div class="buttons">
                            <button type="button" class="template-btn btn-style-one prev-step" data-prev="step2">
                                <span class="btn-wrap"><span class="text-one">Geri</span><span class="text-two">Geri</span></span>
                            </button>
                            <button type="button" class="template-btn btn-style-one" id="pay-button">
                                <span class="btn-wrap"><span class="text-one">Kontor Satın Al</span><span class="text-two">Kontor Satın Al</span></span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</section>

<style>
    .credit-packages {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 25px;
        margin: 20px 0;
    }

    .credit-package-item {
        cursor: pointer;
        position: relative;
    }

    .credit-package-item input[type="radio"] {
        display: none;
    }

    .package-card {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        border: 3px solid #e0e0e0;
        border-radius: 16px;
        padding: 30px 20px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    /* Seçili bir paket varsa diğerlerini soluklaştır */
    .credit-packages:has(input[type="radio"]:checked) .credit-package-item:not(:has(input[type="radio"]:checked)) .package-card {
        opacity: 0.5;
        transform: scale(0.95);
    }

    .credit-packages:has(input[type="radio"]:checked) .credit-package-item:not(:has(input[type="radio"]:checked)) .package-card:hover {
        opacity: 0.8;
        transform: scale(0.98);
    }

    .package-card:hover {
        border-color: #667eea;
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
    }

    .credit-package-item input[type="radio"]:checked+.package-card {
        border-color: #667eea;
        border-width: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: translateY(-10px) scale(1.08);
        box-shadow: 0 25px 50px rgba(102, 126, 234, 0.6);
        position: relative;
        z-index: 10;
    }

    .credit-package-item input[type="radio"]:checked+.package-card::before {
        content: '✓';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 45px;
        height: 45px;
        background: #ffffff;
        color: #667eea;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 900;
        animation: checkmark 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        z-index: 20;
    }

    .credit-package-item input[type="radio"]:checked+.package-card::after {
        content: '';
        position: absolute;
        top: -8px;
        left: -8px;
        right: -8px;
        bottom: -8px;
        border: 3px solid #667eea;
        border-radius: 20px;
        animation: pulse 1.5s infinite;
        pointer-events: none;
    }

    @keyframes checkmark {
        0% {
            transform: scale(0) rotate(-180deg);
            opacity: 0;
        }

        50% {
            transform: scale(1.3) rotate(0deg);
        }

        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.6;
            transform: scale(1.02);
        }
    }

    .package-card.popular {
        border-color: #ffd700;
        box-shadow: 0 4px 20px rgba(255, 215, 0, 0.3);
    }

    .package-card.popular:hover {
        box-shadow: 0 15px 40px rgba(255, 215, 0, 0.4);
    }

    .package-badge {
        position: absolute;
        top: -12px;
        right: 15px;
        background: #ffd700;
        color: #333;
        padding: 6px 18px;
        border-radius: 25px;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.4);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .credit-package-item input[type="radio"]:checked+.package-card .package-badge {
        background: white;
        color: #667eea;
    }

    .package-credit {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 12px;
        color: #2c3e50;
    }

    .credit-package-item input[type="radio"]:checked+.package-card .package-credit {
        color: white;
    }

    .package-price {
        font-size: 28px;
        font-weight: 900;
        margin: 18px 0;
        color: #667eea;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .credit-package-item input[type="radio"]:checked+.package-card .package-price {
        color: white;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .package-unit {
        font-size: 14px;
        opacity: 0.7;
        font-weight: 500;
        margin-top: 8px;
    }

    .credit-package-item input[type="radio"]:checked+.package-card .package-unit {
        opacity: 0.9;
    }

    .package-save {
        margin-top: 12px;
        background: #28a745;
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .credit-package-item input[type="radio"]:checked+.package-card .package-save {
        background: white;
        color: #28a745;
    }

    /* Mobil için responsive */
    @media (max-width: 768px) {
        .credit-packages {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .package-card {
            min-height: 180px;
        }
    }

    /* Loading input animasyonu */
    .loading-input {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }
</style>

<?php include(__DIR__ . "/../layout/footer.php"); ?>

<script>
    // Toast fonksiyonu (script.js yüklenmediyse fallback)
    if (typeof showCenterToast === 'undefined') {
        window.showCenterToast = function(message, type = 'error', duration = 2500) {
            const toast = $('<div class="center-toast ' + type + '">' + message + '</div>');
            $('body').append(toast);
            setTimeout(function() {
                toast.addClass('show');
            }, 10);
            setTimeout(function() {
                toast.removeClass('show');
                setTimeout(function() {
                    toast.remove();
                }, 300);
            }, duration);
        };

        // Toast CSS ekle
        if (!$('#toast-styles').length) {
            $('<style id="toast-styles">' +
                '.center-toast {position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.7);' +
                'background:#fff;color:#333;padding:20px 30px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);' +
                'z-index:999999;opacity:0;transition:all 0.3s ease;min-width:300px;text-align:center;font-size:16px;}' +
                '.center-toast.show {opacity:1;transform:translate(-50%,-50%) scale(1);}' +
                '.center-toast.success {background:#28a745;color:#fff;}' +
                '.center-toast.error {background:#dc3545;color:#fff;}' +
                '.center-toast.warning {background:#ffc107;color:#333;}' +
                '</style>').appendTo('head');
        }
    }

    $(document).ready(function() {
        var customerData = null;
        var creditPrices = {};
        var creditPackages = [];

        // Kontor paketlerini yükle
        function loadCreditPackages() {
            $.ajax({
                url: '/api/get-credit-packages.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        creditPackages = response.data;
                        var packagesHtml = '';

                        response.data.forEach(function(pkg) {
                            // Fiyatları creditPrices objesine ekle (KDV dahil)
                            creditPrices[pkg.credit_amount] = {
                                price: pkg.price,
                                vat: pkg.vat,
                                vat_amount: pkg.vat_amount,
                                price_with_vat: pkg.price_with_vat
                            };

                            // Popüler class
                            var cardClass = pkg.is_popular ? 'popular' : '';

                            // Paket HTML'i oluştur
                            packagesHtml += '<label class="credit-package-item">';
                            packagesHtml += '    <input type="radio" name="credit_amount" value="' + pkg.credit_amount + '" data-price="' + pkg.price + '" data-price-with-vat="' + pkg.price_with_vat + '" data-vat="' + pkg.vat + '" data-required="true">';
                            packagesHtml += '    <div class="package-card ' + cardClass + '">';

                            // Badge varsa ekle
                            if (pkg.badge_text) {
                                packagesHtml += '        <div class="package-badge" style="background: ' + (pkg.badge_color || '#ffd700') + '">' + pkg.badge_text + '</div>';
                            }

                            packagesHtml += '        <div class="package-credit">' + pkg.credit_amount.toLocaleString('tr-TR') + ' Kontor</div>';
                            packagesHtml += '        <div class="package-price">' + pkg.price.toLocaleString('tr-TR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' TL</div>';
                            packagesHtml += '        <div class="package-unit">' + pkg.unit_price.toFixed(2) + ' TL / Kontor</div>';
                            packagesHtml += '    </div>';
                            packagesHtml += '</label>';
                        });

                        $('#credit-packages-container').html(packagesHtml);

                        // Paket seçimi değiştiğinde kontrol et
                        $('input[name="credit_amount"]').on('change', checkStep1);
                    } else {
                        $('#credit-packages-container').html('<p class="text-danger text-center">Paket yüklenirken hata oluştu.</p>');
                    }
                },
                error: function() {
                    $('#credit-packages-container').html('<p class="text-danger text-center">Paketler yüklenemedi.</p>');
                }
            });
        }

        // Sayfa yüklendiğinde paketleri getir
        loadCreditPackages();

        // Telefon numarasını normalize et (sadece rakamları al)
        function normalizePhone(phone) {
            // Sadece rakamları al
            var cleaned = phone.replace(/\D/g, '');

            // Eğer 0 ile başlıyorsa ve 11 haneliyse, 0'ı kaldır
            if (cleaned.startsWith('0') && cleaned.length === 11) {
                cleaned = cleaned.substring(1);
            }

            return cleaned;
        }

        // Otomatik Müşteri Sorgulama
        var searchTimer;
        var lastSearchedPhone = '';

        $('#phone').on('input', function() {
            var phone = $(this).val().trim();
            var normalizedPhone = normalizePhone(phone);

            // Önceki timer'ı iptal et
            clearTimeout(searchTimer);

            // Eğer telefon 10 haneli ise sorgula (0 olmadan)
            if (normalizedPhone.length === 10) {
                // Aynı numarayı tekrar sorgulamayı önle
                if (normalizedPhone === lastSearchedPhone) {
                    return;
                }

                lastSearchedPhone = normalizedPhone;

                // 500ms sonra sorgula (kullanıcı yazmayı bitirsin diye)
                searchTimer = setTimeout(function() {
                    // Loading göstergesi ekle
                    $('#phone').addClass('loading-input');

                    $.ajax({
                        url: '/api/get-customer.php',
                        method: 'POST',
                        data: {
                            phone: normalizedPhone
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                customerData = response.data;

                                // Müşteri bilgilerini göster
                                $('#customer-name').text(customerData.first_name + ' ' + customerData.last_name);
                                $('#customer-email').text(customerData.email);
                                $('#customer-phone').text(customerData.phone);
                                $('#customer-info').slideDown(300);

                                // Hidden field'ları doldur
                                $('#hidden_customer_number').val(customerData.customer_number);
                                $('#hidden_first_name').val(customerData.first_name);
                                $('#hidden_last_name').val(customerData.last_name);
                                $('#hidden_email').val(customerData.email);
                                $('#hidden_identity').val(customerData.identity_number || '');
                                $('#hidden_city').val(customerData.city || '');
                                $('#hidden_district').val(customerData.district || '');
                                $('#hidden_postal').val(customerData.postal_code || '');
                                $('#hidden_address').val(customerData.address || '');

                                // Eksik alan kontrolü
                                if (response.has_missing_fields && response.missing_fields.length > 0) {
                                    var missingFieldsText = response.missing_fields.join(', ');
                                    showCenterToast('Uyarı: Müşteri kaydında eksik bilgiler var (' + missingFieldsText + '). Ödeme sırasında sorun yaşanabilir. Lütfen müşteri bilgilerini güncelleyin.', 'warning');
                                } else {
                                    showCenterToast('Müşteri bilgileri yüklendi.', 'success');
                                }

                                // Kontor seçimi yapıldıysa ileri butonunu aktif et
                                checkStep1();
                            } else {
                                customerData = null;
                                $('#customer-info').slideUp(200);
                                showCenterToast('Bu telefon numarasına kayıtlı müşteri bulunamadı.', 'error');
                                checkStep1();
                            }
                        },
                        error: function() {
                            customerData = null;
                            $('#customer-info').slideUp(200);
                            showCenterToast('Müşteri sorgulanırken hata oluştu.', 'error');
                            checkStep1();
                        },
                        complete: function() {
                            $('#phone').removeClass('loading-input');
                        }
                    });
                }, 500);
            } else {
                // 10 hane değilse müşteri bilgilerini gizle
                if (normalizedPhone.length < 10 && customerData !== null) {
                    customerData = null;
                    lastSearchedPhone = '';
                    $('#customer-info').slideUp(200);
                    checkStep1();
                }
            }
        });

        // Step 1 kontrol
        function checkStep1() {
            var creditSelected = $('input[name="credit_amount"]:checked').length > 0;
            var customerLoaded = customerData !== null;

            $('.content1 .next-step').prop('disabled', !(creditSelected && customerLoaded));
        }

        // ========== Adım Geçiş Scripti ========== //
        var $steps = $('input[name="step"]');
        var $contents = $('.step-content');

        function showStep(stepId) {
            $steps.prop('checked', false);
            $('#' + stepId).prop('checked', true);
            $contents.hide();
            $('.content' + stepId.slice(-1)).show();

            var $formContainer = $('.column');
            if ($formContainer.length > 0 && $formContainer.offset()) {
                var yOffset = -150;
                var y = $formContainer.offset().top + yOffset;
                $('html, body').stop().animate({
                    scrollTop: y
                }, 10);
            }
        }

        function checkStepInputs(stepNumber) {
            var $currentContent = $('.content' + stepNumber);
            var allFilled = true;

            $currentContent.find('input[data-required], textarea[data-required], select[data-required]').each(function() {
                if ($(this).attr('type') === 'radio') {
                    var name = $(this).attr('name');
                    if ($('input[name="' + name + '"]:checked').length === 0) {
                        allFilled = false;
                        return false;
                    }
                } else if ($(this).val().trim() === '') {
                    allFilled = false;
                    return false;
                }
            });

            $currentContent.find('.next-step').prop('disabled', !allFilled);
        }

        checkStepInputs(2);

        $('input[data-required], textarea[data-required], select[data-required]').on('input change', function() {
            var $stepContent = $(this).closest('.step-content');
            var stepClass = $stepContent.attr('class').match(/content(\d+)/);
            if (stepClass) {
                checkStepInputs(stepClass[1]);
            }
        });

        $('.next-step').on('click', function() {
            var nextStep = $(this).data('next');

            // Step 3'e geçerken özeti güncelle
            if (nextStep === 'step3') {
                var creditAmount = $('input[name="credit_amount"]:checked').val();
                var priceData = creditPrices[creditAmount];

                $('#summary-customer').text($('#hidden_customer_number').val());
                $('#summary-credit').text(creditAmount + ' Kontor');
                $('#summary-base-price').text(priceData.price.toLocaleString('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' TL');
                $('#summary-vat').text(priceData.vat_amount.toLocaleString('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' TL (%' + priceData.vat.toFixed(0) + ')');
                $('#summary-total').text(priceData.price_with_vat.toLocaleString('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' TL');
            }

            showStep(nextStep);
        });

        $('.prev-step').on('click', function() {
            showStep($(this).data('prev'));
        });

        showStep('step1');

        // CVC görünürlük toggle
        var $cvcInput = $('#cvc_code_credit');
        var $toggleCVC = $('#toggleCVC');

        $toggleCVC.on('mousedown', function() {
            $cvcInput.attr('type', 'text');
        }).on('mouseup mouseleave', function() {
            $cvcInput.attr('type', 'password');
        });

        // ========== Ödeme Scripti ========== //
        $("#pay-button").on("click", function(e) {
            e.preventDefault();

            if (!customerData) {
                showCenterToast('Lütfen önce müşteri sorgulaması yapın.', 'error');
                return;
            }

            if (!$('input[name="contract_agreement"]').is(':checked')) {
                showCenterToast('Lütfen sözleşmeleri kabul edin.', 'error');
                return;
            }
            if (!$('input[name="privacy_agreement"]').is(':checked')) {
                showCenterToast('Lütfen gizlilik politikasını kabul edin.', 'error');
                return;
            }

            var $btn = $(this);
            var formData = new FormData($("#credit-purchase-form")[0]);

            $btn.prop("disabled", true).addClass("loading");

            $.ajax({
                url: "/controllers/UnifiedPaymentController.php",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        showCenterToast("3D Secure ekranına yönlendiriliyorsunuz...", "success");
                        window.location.href = response.redirectUrl;
                    } else {
                        showCenterToast(response.message, "error");
                    }
                },
                error: function(xhr, status, error) {
                    showCenterToast("Ödeme sırasında bir hata oluştu.", "error");
                },
                complete: function() {
                    $btn.prop("disabled", false).removeClass("loading");
                }
            });
        });
    });
</script>