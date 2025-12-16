<?php
session_start();

if (!isset($_SESSION['form3d'])) {
    header("Location: /");
    exit;
}

$formHtml = $_SESSION['form3d'];

// 3D Secure formu sayfasının action'ını değiştirme gerekmiyor
// Form bankaya gider, banka callback ve returnUrl'e yönlendirir
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Secure Doğrulama</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            text-align: center;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            color: #333;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="loader"></div>
        <h2>3D Secure Doğrulama</h2>
        <p>Ödemenizi güvenli bir şekilde tamamlamak için bankınızın sayfasına yönlendiriliyorsunuz...</p>
    </div>

    <div style="display: none;" id="payment-form">
        <?php echo $formHtml; ?>
    </div>

    <script>
        console.log("%c========== 3D SECURE YÖNLENDIRME SAYFASI ==========", "color: #9C27B0; font-weight: bold; font-size: 16px;");
        console.log("%c[1] payment_redirect.php yüklendi", "color: #2196F3; font-weight: bold;");

        // Formu otomatik gönder
        window.onload = function() {
            console.log("%c[2] Sayfa Yükleme Tamamlandı (window.onload)", "color: #FF9800; font-weight: bold;");

            var form = document.querySelector('#payment-form form');
            console.log("%c[3] 3D Secure Formu Aranıyor...", "color: #00BCD4;");

            if (form) {
                console.log("%c[4] ✓ 3D Secure Formu Bulundu!", "color: #4CAF50; font-weight: bold;");
                console.log("   → Form Action: " + form.action);
                console.log("   → Form Method: " + form.method);

                // Form elemanlarını logla (hassas bilgileri gösterme)
                console.groupCollapsed("%c[5] Form Elemanları", "color: #607D8B;");
                var formElements = form.elements;
                for (var i = 0; i < formElements.length; i++) {
                    var element = formElements[i];
                    if (element.name) {
                        console.log(element.name + ": [" + element.type + "]");
                    }
                }
                console.groupEnd();

                console.log("%c[6] Form Banka Sunucusuna Gönderiliyor...", "color: #009688; font-weight: bold;");
                console.log("   → Banka 3D Secure sayfasına yönlendirileceksiniz");

                form.submit();

                console.log("%c[7] Form Submit Edildi", "color: #4CAF50; font-weight: bold;");
                console.log("%c[8] Banka sayfasına yönlendirme bekleniyor...", "color: #9E9E9E;");
            } else {
                console.error("%c[4] ✗ 3D Secure Formu Bulunamadı!", "color: #F44336; font-weight: bold;");
                console.error("   → #payment-form içinde form elementi yok");
                console.log("   → Anasayfaya yönlendirileceksiniz");

                alert('Ödeme formu bulunamadı. Lütfen tekrar deneyin.');
                window.location.href = '/';
            }
        };

        // Sayfa yüklenirken
        console.log("%c[INFO] Bu sayfa bankaya yönlendirme için kullanılıyor", "color: #03A9F4;");
        console.log("   → Session'dan 3D form alındı");
        console.log("   → Form otomatik olarak bankaya gönderilecek");
    </script>
</body>
</html>
<?php
// Form gönderildikten sonra session'dan kaldırma
unset($_SESSION['form3d']);
