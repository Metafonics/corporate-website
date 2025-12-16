<?php
session_start();

$pageTitle       = "Ödeme Başarısız";
$pageDescription = "Ödeme işlemi başarısız oldu.";
include(__DIR__ . "/../layout/header.php");

// Önce URL parametresinden orderId'yi al
$orderId = $_GET['orderId'] ?? $_SESSION['orderId'] ?? null;
?>

<!-- Sayfa Başlığı -->
<section class="page-title">
    <div class="page-title-icon" style="background-image:url(/assets/images/icons/page-title_icon-1.png)"></div>
    <div class="page-title-icon-two" style="background-image:url(/assets/images/icons/page-title_icon-2.png)"></div>
    <div class="page-title-shadow" style="background-image:url(/assets/images/background/page-title-1.png)"></div>
    <div class="page-title-shadow_two" style="background-image:url(/assets/images/background/page-title-2.png)"></div>
    <div class="auto-container">
        <h1>Ödeme Başarısız</h1>
        <ul class="bread-crumb clearfix">
            <li><a href="/">Anasayfa</a></li>
            <li>Ödeme Başarısız</li>
        </ul>
    </div>
</section>

<!-- Hata Mesajı -->
<section class="contact-section" style="margin: 100px 0;">
    <div class="auto-container">
        <div class="row">
            <div class="col-lg-8 col-md-12 m-auto">
                <div class="contact-form-one" style="text-align: center; padding: 60px 40px;">
                    <div style="font-size: 80px; color: #dc3545; margin-bottom: 30px;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h2 style="margin-bottom: 20px;">Ödeme İşlemi Başarısız!</h2>
                    <?php if ($orderId): ?>
                        <p class="order-number">Sipariş numarası: <strong><?php echo htmlspecialchars($orderId); ?></strong></p>
                    <?php endif; ?>
                    <p class="order-description">
                        Ödemeniz tamamlanamadı. Lütfen kart bilgilerinizi kontrol ederek tekrar deneyin.<br>
                        Sorun devam ederse lütfen bankanızla iletişime geçin veya farklı bir kart deneyin.
                    </p>
                    <div class="text-center">
                        <a href="javascript:history.back()" class="template-btn btn-style-one me-3">
                            <span class="btn-wrap"><span class="text-one">Tekrar Dene</span><span class="text-two">Tekrar Dene</span></span>
                        </a>
                        <a href="/" class="template-btn btn-style-two">
                            <span class="btn-wrap"><span class="text-one">Ana Sayfaya Dön</span><span class="text-two">Ana Sayfaya Dön</span></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Session'ı temizle
unset($_SESSION['orderId']);
unset($_SESSION['amount']);
unset($_SESSION['paymentStatus']);
unset($_SESSION['packageId']);
unset($_SESSION['customer_data']);
unset($_SESSION['billing_data']);
unset($_SESSION['form3d']);

include(__DIR__ . "/../layout/footer.php");
?>