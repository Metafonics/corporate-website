<?php

require_once __DIR__ . '/../config/database.php';
include("data.php");

// Path bilgisini al, query string hariç
$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");

// Anasayfa
if ($request === "" || $request === "index.php") {
    include("pages/home.php");
    exit;
}

// Dinamik asistan detay sayfası
if (preg_match('#^sektorel-asistanlar/([\w-]+)$#', $request, $matches)) {
    $slug = $matches[1];

    // Slug'a göre id bul
    $product_id = null;
    foreach ($all_products as $id => $p) {
        if ($p['slug'] === $slug) {
            $product_id = $id;
            break;
        }
    }

    if ($product_id) {
        include("pages/assistant-detail.php");
        exit;
    } else {
        http_response_code(404);
        include("404.php");
        exit;
    }
}

// Grid sayfası
if ($request == "sektorel-asistanlar") {
    include("pages/sectoral-assistants.php");
    exit;
}

// Fonksiyonel ürünler (type => "functional") - Direkt slug ile erişim
foreach ($all_products as $id => $p) {
    if (isset($p['type']) && $p['type'] === 'functional' && $request === $p['slug']) {
        $product_id = $id;
        include("pages/assistant-detail.php");
        exit;
    }
}

// SEO friendly ödeme URL: /odeme/{id}
if (preg_match('#^odeme/([0-9]+)$#', $request, $matches)) {
    $_GET['id'] = $matches[1]; // payment.php için paket ID
    include("payment.php");
    exit;
}

// Paket detay sayfası: /paket/{slug}
if (preg_match('#^paket/([\w-]+)$#', $request, $matches)) {
    $slug = $matches[1];

    // Tüm aktif paketleri çek ve slug ile eşleştir
    $package_query = $pdo->prepare("SELECT id, title FROM packages WHERE status = 1");
    $package_query->execute();
    $packages = $package_query->fetchAll(PDO::FETCH_ASSOC);

    $package_id = null;
    foreach ($packages as $pkg) {
        if (generateSlug($pkg['title']) === $slug) {
            $package_id = $pkg['id'];
            break;
        }
    }

    if ($package_id) {
        include("pages/package-detail.php");
        exit;
    }
}

// Diğer statik sayfalar
switch ($request) {
    case "giris":
        include("login.php");
        break;

    case "musteri-kayit":
        include("customer-register.php");
        break;

    case "cikis":
        include("logout.php");
        break;

    case "hakkimizda":
        include("about.php");
        break;

    case "referanslarimiz":
        include("references.php");
        break;

    case "odeme":
        $packageId = $_GET['id'] ?? null;
        include("payment.php");
        break;

    case "basarili":
        include("payment/payment_success.php");
        break;

    case "yonlendirme":
        include("payment/payment_redirect.php");
        break;

    case "basarisiz":
        include("payment/payment_fail.php");
        break;

    case "kisisel-verilerin-korunma-politikasi":
        include("personal-data-protection-law-policy.php");
        break;

    case "cerez-politikasi":
        include("cookie-policy.php");
        break;

    case "iptal-ve-iade-politikasi":
        include("return-and-cancellation-policy.php");
        break;

    case "mesafeli-satis-eticaret-sozlesmesi":
        include("distance-selling-ecommerce-agreement.php");
        break;

    case "odeme-ve-guvenlik-politikasi":
        include("payment-and-security-policy.php");
        break;

    case "kullanim-kosullari-hizmet-sozlesmesi":
        include("terms-of-use-service-agreement.php");
        break;

    case "kullanici-verilerinin-silinmesi-sozlesmesi":
        include("deletion-of-user-data.php");
        break;

    case "iletisim":
        include("contact.php");
        break;

    case "musteri-odeme":
        include("customer-payment.php");
        break;

    case "kontor-al":
        include("credit-buy.php");
        break;

    default:
        http_response_code(404);
        include("404.php");
        break;
}
