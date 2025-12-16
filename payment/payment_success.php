<?php
session_start();

$pageTitle       = "Ödeme Başarılı";
$pageDescription = "Ödemeniz başarıyla tamamlandı.";
include(__DIR__ . "/../layout/header.php");

require_once __DIR__ . '/../config/database.php';

// Önce URL parametresinden orderId'yi al
$orderId = $_GET['orderId'] ?? $_SESSION['orderId'] ?? null;
$amount = 0;
$paymentStatus = null;

// OrderId varsa DB'den çek
if ($orderId) {
    try {
        $stmt = $pdo->prepare("
            SELECT order_id, amount, status
            FROM orders
            WHERE order_id = ? AND status = 'APPROVED'
            LIMIT 1
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $orderId = $order['order_id'];
            $amount = $order['amount'];
            $paymentStatus = $order['status'];
        }
    } catch (PDOException $e) {
        // Hata olursa devam et
    }
}

// Eğer hala bulunamadıysa session'dan dene
if (!$paymentStatus) {
    $orderId = $_SESSION['orderId'] ?? null;
    $amount = $_SESSION['amount'] ?? 0;
    $paymentStatus = $_SESSION['paymentStatus'] ?? null;
}

// Hala bilgi yoksa hata göster
if (!$orderId || $paymentStatus !== 'APPROVED') {
    echo '<div class="auto-container" style="padding: 100px 0;">
            <h2 class="text-center">Geçersiz sipariş veya ödeme onaylanmadı.</h2>
            <div class="text-center mt-4">
                <a href="/" class="template-btn btn-style-one">
                    <span class="btn-wrap"><span class="text-one">Ana Sayfaya Dön</span><span class="text-two">Ana Sayfaya Dön</span></span>
                </a>
            </div>
          </div>';
    include(__DIR__ . "/../layout/footer.php");
    exit;
}
?>

<!-- Başarı Mesajı -->
<section class="contact-section" style="margin: 100px 0;">
    <div class="auto-container">
        <div class="row">
            <div class="col-lg-8 col-md-12 m-auto">
                <div class="contact-form-one" style="text-align: center; padding: 60px 40px;">
                    <div style="font-size: 80px; color: #28a745; margin-bottom: 30px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 style="margin-bottom: 20px;">Ödemeniz Başarıyla Tamamlandı!</h2>
                    <p class="order-number">Sipariş numaranız: <strong><?php echo htmlspecialchars($orderId); ?></strong></p>
                    <p class="total-amount"">Toplam ödeme: <strong><?php echo number_format($amount, 2, ',', '.'); ?> TL</strong></p>
                    <p class="order-description">
                        Siparişinizle ilgili detaylı bilgi e-posta adresinize gönderilecektir.<br>
                        En kısa sürede sizinle iletişime geçeceğiz.
                    </p>
                    <div class="text-center">
                        <a href="/" class="template-btn btn-style-one">
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