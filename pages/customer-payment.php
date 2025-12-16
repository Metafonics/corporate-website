<?php
$pageTitle = "Özel Ödeme";
$pageDescription = "Belirlediğiniz tutarı güvenli bir şekilde ödeyebilirsiniz.";
include(__DIR__ . "/../layout/header.php");
?>

<!-- Sayfa Başlığı -->
<section class="page-title">
    <div class="page-title-icon" style="background-image:url(/assets/images/icons/page-title_icon-1.png)"></div>
    <div class="page-title-icon-two" style="background-image:url(/assets/images/icons/page-title_icon-2.png)"></div>
    <div class="page-title-shadow" style="background-image:url(/assets/images/background/page-title-1.png)"></div>
    <div class="page-title-shadow_two" style="background-image:url(/assets/images/background/page-title-2.png)"></div>
    <div class="auto-container">
        <h1>Özel Ödeme</h1>
        <ul class="bread-crumb clearfix">
            <li><a href="/">Anasayfa</a></li>
            <li>Özel Ödeme</li>
        </ul>
    </div>
</section>

<!-- Ödeme Formu -->
<section class="mt-5" style="margin-bottom: 150px;">
    <div class="auto-container default-form">
        <div class="column col-lg-8 col-md-12 col-sm-12 m-auto">

            <!-- Step Radio Inputs -->
            <input type="radio" name="step" id="step1" checked hidden>
            <input type="radio" name="step" id="step2" hidden>
            <input type="radio" name="step" id="step3" hidden>

            <!-- Step Indicators -->
            <div class="steps">
                <div class="step s1" data-step="1"><span>Tutar & Bilgiler</span></div>
                <div class="step s2" data-step="2"><span>Kart Bilgileri</span></div>
                <div class="step s3" data-step="3"><span>Özet</span></div>
            </div>

            <!-- Form Başlangıcı -->
            <form id="custom-payment-form" class="validateForm">
                <input type="hidden" name="payment_type" value="custom">
                <input type="hidden" name="vat_included" id="vat_included" value="1">

                <div class="contents">

                    <!-- Step1: Tutar ve Müşteri Bilgileri -->
                    <div class="step-content content1">
                        <h3 class="text-light mb-4">Ödeme Tutarı</h3>

                        <div class="form-group">
                            <label>Ödenecek Tutar (TL)*</label>
                            <input type="text" name="custom_amount" id="custom_amount" value="" placeholder="Örn: 1500" min="1" step="0.01" data-required="true" data-type="number">
                            <div class="error-msg"></div>
                        </div>

                        <div class="form-group">
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid var(--main-color);">
                                <label style="display: flex; align-items: center; margin: 0; cursor: pointer; font-weight: 600;">
                                    <input type="checkbox" id="include_vat" name="include_vat" checked style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                                    <span>KDV Ekle (%20)</span>
                                </label>
                                <small class="text-muted" style="display: block; margin-top: 8px; margin-left: 30px;">
                                    Varsayılan olarak KDV dahil edilir. İşareti kaldırarak KDV hariç ödeme yapabilirsiniz.
                                </small>
                            </div>
                        </div>

                        <div class="form-group" id="amount-summary" style="display: none;">
                            <div style="background: var(--main-color); padding: 20px; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(var(--main-color-rgb), 0.3);">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span>Tutar:</span>
                                    <span id="base-amount-display" style="font-weight: 600;">0.00 TL</span>
                                </div>
                                <div id="vat-row" style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.3);">
                                    <span>KDV (%20):</span>
                                    <span id="vat-amount-display" style="font-weight: 600;">0.00 TL</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700;">
                                    <span>Ödenecek Toplam:</span>
                                    <span id="total-amount-display">0.00 TL</span>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-light mb-4 mt-5">Müşteri Bilgileri</h3>

                        <!-- Customer Type Tabs -->
                        <div class="customer-type-tabs mb-4">
                            <input type="radio" name="customer_type" id="customer_type_company" value="company" checked hidden>
                            <input type="radio" name="customer_type" id="customer_type_private" value="private" hidden>

                            <div class="tab-buttons">
                                <label for="customer_type_company" class="tab-button active">Kurumsal</label>
                                <label for="customer_type_private" class="tab-button">Bireysel</label>
                            </div>
                        </div>

                        <!-- Company Fields -->
                        <div id="company-fields" class="customer-fields">
                            <div class="form-group">
                                <label>Şirket Adı*</label>
                                <input type="text" name="company_name" placeholder="Şirket Adı" data-customer-type="company">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Yetkili Adı*</label>
                                <input type="text" name="company_authorized_name" placeholder="Yetkili Adı" data-customer-type="company">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Yetkili Soyadı*</label>
                                <input type="text" name="company_authorized_lastname" placeholder="Yetkili Soyadı" data-customer-type="company">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Telefon*</label>
                                <input type="text" name="company_phone" placeholder="Telefon" data-customer-type="company" data-type="phone">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>E-posta*</label>
                                <input type="email" name="company_email" placeholder="E-posta" data-customer-type="company" data-type="email">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Ülke*</label>
                                <input type="text" name="company_country" placeholder="Ülke" value="Türkiye" data-customer-type="company">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Şehir*</label>
                                <input type="text" name="company_city" placeholder="Şehir" data-customer-type="company">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>İlçe*</label>
                                <input type="text" name="company_district" placeholder="İlçe" data-customer-type="company">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Posta Kodu*</label>
                                <input type="text" name="company_postal_code" placeholder="Posta Kodu" data-customer-type="company" data-type="number_5">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Adres*</label>
                                <textarea name="company_address" placeholder="Adres" data-customer-type="company"></textarea>
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Vergi Dairesi*</label>
                                <input type="text" name="company_tax_office" placeholder="Vergi Dairesi" data-customer-type="company">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Vergi Numarası*</label>
                                <input type="text" name="company_tax_number" placeholder="Vergi Numarası" data-customer-type="company" data-type="number_10">
                                <div class="error-msg"></div>
                            </div>
                        </div>

                        <!-- Private Fields -->
                        <div id="private-fields" class="customer-fields" style="display: none;">
                            <div class="form-group">
                                <label>TC Kimlik Numarası*</label>
                                <input type="text" name="private_identity_number" placeholder="TC Kimlik Numarası" data-customer-type="private" data-type="number_11">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Adınız*</label>
                                <input type="text" name="private_first_name" placeholder="Adınız" data-customer-type="private">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Soyadınız*</label>
                                <input type="text" name="private_last_name" placeholder="Soyadınız" data-customer-type="private">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>E-posta*</label>
                                <input type="email" name="private_email" placeholder="E-posta" data-customer-type="private" data-type="email">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Telefon*</label>
                                <input type="text" name="private_phone" placeholder="Telefon" data-customer-type="private" data-type="phone">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Ülke*</label>
                                <input type="text" name="private_country" placeholder="Ülke" value="Türkiye" data-customer-type="private">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Şehir*</label>
                                <input type="text" name="private_city" placeholder="Şehir" data-customer-type="private">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>İlçe*</label>
                                <input type="text" name="private_district" placeholder="İlçe" data-customer-type="private">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Posta Kodu*</label>
                                <input type="text" name="private_postal_code" placeholder="Posta Kodu" data-customer-type="private" data-type="number_5">
                                <div class="error-msg"></div>
                            </div>

                            <div class="form-group">
                                <label>Adres*</label>
                                <textarea name="private_address" placeholder="Adres" data-customer-type="private"></textarea>
                                <div class="error-msg"></div>
                            </div>
                        </div>

                        <!-- Different Billing Address Checkbox -->
                        <div class="form-group mt-5">
                            <label class="checkbox-label">
                                <input type="checkbox" id="different_billing_address" name="different_billing_address" value="1">
                                Farklı Fatura Bilgileri Gir
                            </label>
                        </div>

                        <!-- Billing Information (Hidden by default) -->
                        <div id="billing-section" style="display: none;">
                            <h3 class="text-light mt-4 mb-4">Fatura Bilgileri</h3>

                            <!-- Billing Type Selection -->
                            <div class="billing-type-tabs mb-4">
                                <input type="radio" name="billing_type" id="billing_type_company" value="company" checked hidden>
                                <input type="radio" name="billing_type" id="billing_type_private" value="private" hidden>

                                <div class="billing-tab-buttons">
                                    <label for="billing_type_company" class="billing-tab-button active">Kurumsal Fatura</label>
                                    <label for="billing_type_private" class="billing-tab-button">Bireysel Fatura</label>
                                </div>
                            </div>

                            <!-- Company Invoice Fields -->
                            <div id="invoice-company-fields" class="invoice-fields">
                                <div class="form-group">
                                    <label>Şirket Adı*</label>
                                    <input type="text" name="invoice_company_name" placeholder="Şirket Adı" data-billing-type="company">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Yetkili Adı*</label>
                                    <input type="text" name="invoice_company_authorized_name" placeholder="Yetkili Adı" data-billing-type="company">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Yetkili Soyadı*</label>
                                    <input type="text" name="invoice_company_authorized_lastname" placeholder="Yetkili Soyadı" data-billing-type="company">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Telefon*</label>
                                    <input type="text" name="invoice_company_phone" placeholder="Telefon" data-billing-type="company" data-type="phone">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>E-posta*</label>
                                    <input type="email" name="invoice_company_email" placeholder="E-posta" data-billing-type="company" data-type="email">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Ülke*</label>
                                    <input type="text" name="invoice_company_country" placeholder="Ülke" value="Türkiye" data-billing-type="company">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Şehir*</label>
                                    <input type="text" name="invoice_company_city" placeholder="Şehir" data-billing-type="company">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>İlçe*</label>
                                    <input type="text" name="invoice_company_district" placeholder="İlçe" data-billing-type="company">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Posta Kodu*</label>
                                    <input type="text" name="invoice_company_postal_code" placeholder="Posta Kodu" data-billing-type="company" data-type="number_5">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Adres*</label>
                                    <textarea name="invoice_company_address" placeholder="Adres" data-billing-type="company"></textarea>
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Vergi Dairesi*</label>
                                    <input type="text" name="invoice_company_tax_office" placeholder="Vergi Dairesi" data-billing-type="company">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Vergi Numarası*</label>
                                    <input type="text" name="invoice_company_tax_number" placeholder="Vergi Numarası" data-billing-type="company" data-type="number_10">
                                    <div class="error-msg"></div>
                                </div>
                            </div>

                            <!-- Private Invoice Fields -->
                            <div id="invoice-private-fields" class="invoice-fields" style="display: none;">
                                <div class="form-group">
                                    <label>TC Kimlik Numarası*</label>
                                    <input type="text" name="private_invoice_identity_number" placeholder="TC Kimlik Numarası" data-billing-type="private" data-type="number_11">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Adınız*</label>
                                    <input type="text" name="private_invoice_first_name" placeholder="Adınız" data-billing-type="private">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Soyadınız*</label>
                                    <input type="text" name="private_invoice_last_name" placeholder="Soyadınız" data-billing-type="private">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>E-posta*</label>
                                    <input type="email" name="private_invoice_email" placeholder="E-posta" data-billing-type="private" data-type="email">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Telefon*</label>
                                    <input type="text" name="private_invoice_phone" placeholder="Telefon" data-billing-type="private" data-type="phone">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Ülke*</label>
                                    <input type="text" name="private_invoice_country" placeholder="Ülke" value="Türkiye" data-billing-type="private">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Şehir*</label>
                                    <input type="text" name="private_invoice_city" placeholder="Şehir" data-billing-type="private">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>İlçe*</label>
                                    <input type="text" name="private_invoice_district" placeholder="İlçe" data-billing-type="private">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Posta Kodu*</label>
                                    <input type="text" name="private_invoice_postal_code" placeholder="Posta Kodu" data-billing-type="private" data-type="number_5">
                                    <div class="error-msg"></div>
                                </div>

                                <div class="form-group">
                                    <label>Adres*</label>
                                    <textarea name="private_invoice_address" placeholder="Adres" data-billing-type="private"></textarea>
                                    <div class="error-msg"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="buttons">
                                <button type="button" class="template-btn btn-style-one next-step" data-next="step2">
                                    <span class="btn-wrap"><span class="text-one">İleri</span><span class="text-two">İleri</span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step2: Ödeme Bilgileri -->
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
                                    <input type="password" name="cvc_code" id="cvc_code" placeholder="000" value="" data-required="true" data-type="number_3">
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
                        <h3 class="text-light mb-4">Ödeme Özeti</h3>

                        <div class="summary-item">
                            <span>Ödeme Türü:</span>
                            <span>Özel Ödeme</span>
                        </div>

                        <div class="summary-item">
                            <span>Tutar:</span>
                            <span id="summary-base-amount">0.00 TL</span>
                        </div>

                        <div class="summary-item" id="summary-vat-row">
                            <span>KDV (%20):</span>
                            <span id="summary-vat-amount">0.00 TL</span>
                        </div>

                        <div class="summary-total">
                            <span>Ödenecek Toplam:</span>
                            <span id="summary-total">0.00 TL</span>
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
                                <span class="btn-wrap"><span class="text-one">Ödeme Yap</span><span class="text-two">Ödeme Yap</span></span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</section>

<?php include(__DIR__ . "/../layout/footer.php"); ?>

<style>
    /* Customer Type Tabs Styling */
    .customer-type-tabs .tab-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .customer-type-tabs .tab-button {
        flex: 1;
        padding: 12px 20px;
        text-align: center;
        background: var(--bg-theme-color1);
        border: 2px solid var(--main-color);
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--color-six);
        font-weight: 500;
    }

    .customer-type-tabs .tab-button:hover {
        background: var(--main-color);
        color: #fff;
    }

    .customer-type-tabs .tab-button.active,
    #customer_type_company:checked ~ .tab-buttons label[for="customer_type_company"],
    #customer_type_private:checked ~ .tab-buttons label[for="customer_type_private"] {
        background: var(--main-color);
        color: #fff;
    }

    /* Billing Type Tabs Styling */
    .billing-type-tabs .billing-tab-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .billing-type-tabs .billing-tab-button {
        flex: 1;
        padding: 12px 20px;
        text-align: center;
        background: var(--bg-theme-color1);
        border: 2px solid var(--main-color);
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--color-six);
        font-weight: 500;
    }

    .billing-type-tabs .billing-tab-button:hover {
        background: var(--main-color);
        color: #fff;
    }

    .billing-type-tabs .billing-tab-button.active,
    #billing_type_company:checked ~ .billing-tab-buttons label[for="billing_type_company"],
    #billing_type_private:checked ~ .billing-tab-buttons label[for="billing_type_private"] {
        background: var(--main-color);
        color: #fff;
    }

    /* Checkbox and Radio Styling */
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 15px;
        color: var(--text-color);
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
</style>

<script>
    $(document).ready(function() {
        // ========== Müşteri Tipi Tab Geçişleri ========== //
        $('input[name="customer_type"]').on('change', function() {
            var selectedType = $(this).val();

            // Tab button'ların görünümünü güncelle
            $('.customer-type-tabs .tab-button').removeClass('active');
            $('label[for="customer_type_' + selectedType + '"]').addClass('active');

            // Alanları göster/gizle
            if (selectedType === 'company') {
                $('#company-fields').show();
                $('#private-fields').hide();

                // Company alanlarını zorunlu yap, private'ı kaldır
                $('#company-fields').find('input, textarea, select').attr('data-required', 'true');
                $('#private-fields').find('input, textarea, select').removeAttr('data-required');

                // Fatura tipini de company yap (eğer fatura bölümü açıksa)
                if ($('#different_billing_address').is(':checked')) {
                    $('#billing_type_company').prop('checked', true).trigger('change');
                } else {
                    // Farklı fatura adresi seçili değilse, billing_type'ı company olarak ayarla
                    $('#billing_type_company').prop('checked', true);
                }
            } else {
                $('#company-fields').hide();
                $('#private-fields').show();

                // Private alanlarını zorunlu yap, company'yi kaldır
                $('#private-fields').find('input, textarea, select').attr('data-required', 'true');
                $('#company-fields').find('input, textarea, select').removeAttr('data-required');

                // Fatura tipini de private yap (eğer fatura bölümü açıksa)
                if ($('#different_billing_address').is(':checked')) {
                    $('#billing_type_private').prop('checked', true).trigger('change');
                } else {
                    // Farklı fatura adresi seçili değilse, billing_type'ı private olarak ayarla
                    $('#billing_type_private').prop('checked', true);
                }
            }

            // Form kontrolünü yenile
            checkStepInputs(1);
        });

        // ========== Farklı Fatura Bilgileri Checkbox ========== //
        $('#different_billing_address').on('change', function() {
            if ($(this).is(':checked')) {
                $('#billing-section').slideDown();

                // Müşteri tipine göre fatura tipini otomatik seç
                var customerType = $('input[name="customer_type"]:checked').val();
                if (customerType === 'company') {
                    $('#billing_type_company').prop('checked', true).trigger('change');
                } else {
                    $('#billing_type_private').prop('checked', true).trigger('change');
                }
            } else {
                $('#billing-section').slideUp();
                // Tüm fatura alanlarını zorunluluktan çıkar
                $('#invoice-company-fields, #invoice-private-fields').find('input, textarea, select').removeAttr('data-required');
            }
            checkStepInputs(1);
        });

        // ========== Fatura Tipi Değişimi ========== //
        $('input[name="billing_type"]').on('change', function() {
            var billingType = $(this).val();

            // Tab button'ların görünümünü güncelle
            $('.billing-type-tabs .billing-tab-button').removeClass('active');
            $('label[for="billing_type_' + billingType + '"]').addClass('active');

            if (billingType === 'company') {
                $('#invoice-company-fields').show();
                $('#invoice-private-fields').hide();

                // Company fatura alanlarını zorunlu yap
                $('#invoice-company-fields').find('input, textarea, select').attr('data-required', 'true');
                $('#invoice-private-fields').find('input, textarea, select').removeAttr('data-required');
            } else {
                $('#invoice-company-fields').hide();
                $('#invoice-private-fields').show();

                // Private fatura alanlarını zorunlu yap
                $('#invoice-private-fields').find('input, textarea, select').attr('data-required', 'true');
                $('#invoice-company-fields').find('input, textarea, select').removeAttr('data-required');
            }

            checkStepInputs(1);
        });

        // ========== KDV Hesaplama ========== //
        var vatRate = 0.20; // %20 KDV

        function calculateAmount() {
            var baseAmount = parseFloat($('#custom_amount').val()) || 0;
            var includeVat = $('#include_vat').is(':checked');

            if (baseAmount > 0) {
                $('#amount-summary').slideDown(300);

                var vatAmount = includeVat ? (baseAmount * vatRate) : 0;
                var totalAmount = baseAmount + vatAmount;

                // Tutar özeti güncelle
                $('#base-amount-display').text(baseAmount.toFixed(2) + ' TL');
                $('#vat-amount-display').text(vatAmount.toFixed(2) + ' TL');
                $('#total-amount-display').text(totalAmount.toFixed(2) + ' TL');

                // KDV satırını göster/gizle
                if (includeVat) {
                    $('#vat-row').show();
                } else {
                    $('#vat-row').hide();
                }
            } else {
                $('#amount-summary').slideUp(300);
            }
        }

        // Tutar değiştiğinde hesapla
        $('#custom_amount').on('input', function() {
            calculateAmount();
        });

        // KDV checkbox değiştiğinde hesapla
        $('#include_vat').on('change', function() {
            // Hidden field'ı güncelle
            $('#vat_included').val($(this).is(':checked') ? '1' : '0');
            calculateAmount();
        });

        // ========== Adım Geçiş Scripti ========== //
        var $steps = $('input[name="step"]');
        var $contents = $('.step-content');

        function showStep(stepId) {
            $steps.prop('checked', false);
            $('#' + stepId).prop('checked', true);
            $contents.hide();
            $('.content' + stepId.slice(-1)).show();

            var $formContainer = $('.column');
            var yOffset = -150;
            var y = $formContainer.offset().top + yOffset;
            $('html, body').stop().animate({
                scrollTop: y
            }, 10);
        }

        // Boş alan kontrolü
        function checkStepInputs(stepNumber) {
            var $currentContent = $('.content' + stepNumber);
            var allFilled = true;

            // Sadece görünür ve data-required olan alanları kontrol et
            $currentContent.find('input[data-required]:visible, textarea[data-required]:visible, select[data-required]:visible').each(function() {
                var $field = $(this);
                // Parent container gizli değilse kontrol et
                if ($field.closest('.customer-fields, .invoice-fields').is(':visible') || !$field.closest('.customer-fields, .invoice-fields').length) {
                    if ($field.val().trim() === '') {
                        allFilled = false;
                        return false;
                    }
                }
            });

            $currentContent.find('.next-step').prop('disabled', !allFilled);
        }

        // Başlangıçta company seçili olsun
        $('#company-fields').find('input, textarea, select').attr('data-required', 'true');

        checkStepInputs(1);
        checkStepInputs(2);

        $(document).on('input change', 'input, textarea, select', function() {
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
                var baseAmount = parseFloat($('#custom_amount').val()) || 0;
                var includeVat = $('#include_vat').is(':checked');
                var vatAmount = includeVat ? (baseAmount * vatRate) : 0;
                var totalAmount = baseAmount + vatAmount;

                // Özet bölümünü güncelle
                $('#summary-base-amount').text(baseAmount.toFixed(2) + ' TL');
                $('#summary-vat-amount').text(vatAmount.toFixed(2) + ' TL');
                $('#summary-total').text(totalAmount.toFixed(2) + ' TL');

                // KDV satırını göster/gizle
                if (includeVat) {
                    $('#summary-vat-row').show();
                } else {
                    $('#summary-vat-row').hide();
                }
            }

            showStep(nextStep);
        });

        $('.prev-step').on('click', function() {
            showStep($(this).data('prev'));
        });

        showStep('step1');

        // CVC görünürlük toggle
        var $cvcInput = $('#cvc_code');
        var $toggleCVC = $('#toggleCVC');

        $toggleCVC.on('mousedown', function() {
            $cvcInput.attr('type', 'text');
        }).on('mouseup mouseleave', function() {
            $cvcInput.attr('type', 'password');
        });

        // ========== Ödeme Scripti ========== //
        $("#pay-button").on("click", function(e) {
            e.preventDefault();

            var $btn = $(this);
            var formData = new FormData($("#custom-payment-form")[0]);

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
                        setTimeout(function() {
                            window.location.href = response.redirectUrl;
                        }, 500);
                    } else {
                        showCenterToast(response.message, "error");
                    }
                },
                error: function(xhr, status, error) {
                    showCenterToast("Ödeme sırasında bir hata oluştu. Lütfen tekrar deneyin.", "error");
                },
                complete: function() {
                    $btn.prop("disabled", false).removeClass("loading");
                }
            });
        });
    });
</script>