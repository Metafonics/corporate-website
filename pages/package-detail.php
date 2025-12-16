<?php
// $package_id router tarafından gönderiliyor
if (!isset($package_id)) {
    http_response_code(404);
    include("404.php");
    exit;
}

// Paket bilgilerini kategori ile birlikte çek
$package_query = $pdo->prepare("
	SELECT p.*, pc.name as category_name
	FROM packages p
	LEFT JOIN package_categories pc ON p.category_id = pc.id
	WHERE p.id = ? AND p.status = 1
");
$package_query->execute([$package_id]);
$package = $package_query->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    http_response_code(404);
    include("404.php");
    exit;
}

// Paket özelliklerini çek (feature ve feature_description ile)
$features_query = $pdo->prepare("
	SELECT feature, feature_description
	FROM package_features
	WHERE package_id = ?
	ORDER BY id ASC
");
$features_query->execute([$package_id]);
$features = $features_query->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $package['title'];
$pageDescription = $package['sub_title'];

include("layout/header.php");
?>

<!-- Page Title -->
<section class="page-title">
    <div class="page-title-icon" style="background-image:url(assets/images/icons/page-title_icon-1.png)"></div>
    <div class="page-title-icon-two" style="background-image:url(assets/images/icons/page-title_icon-2.png)"></div>
    <div class="page-title-shadow" style="background-image:url(assets/images/background/page-title-1.png)"></div>
    <div class="page-title-shadow_two" style="background-image:url(assets/images/background/page-title-2.png)"></div>
    <div class="auto-container">
        <h1><?php echo htmlspecialchars($package['title']); ?></h1>
        <ul class="bread-crumb clearfix">
            <li><a href="/">Anasayfa</a></li>
            <li><?php echo htmlspecialchars($package['title']); ?></li>
        </ul>
    </div>
</section>
<!-- End Page Title -->

<!-- Package Detail Section -->
<section class="package-detail-section">
    <div class="auto-container">
        <div class="row">
            <!-- Left Column - Package Info & Features -->
            <div class="col-lg-8 col-md-12">
                <div class="package-left-wrapper">
                    <div class="package-left-card">
                        <!-- Package Info Header -->
                        <div class="package-info-header">
                            <div class="package-info-content">
                                <h1><?php echo htmlspecialchars($package['title']); ?></h1>
                                <p class="subtitle"><?php echo htmlspecialchars($package['sub_title']); ?></p>
                            </div>
                            <?php if (!empty($package['category_name'])): ?>
                                <div class="package-category-badge">
                                    <?php echo htmlspecialchars($package['category_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Features Section -->
                        <div class="features-section">
                            <?php if (!empty($features)): ?>
                                <div class="features-grid">
                                    <?php foreach ($features as $feature): ?>
                                        <div class="feature-item">
                                            <div class="feature-check-icon">
                                                <i class="fa-solid fa-check"></i>
                                            </div>
                                            <div class="feature-name">
                                                <?php echo htmlspecialchars($feature['feature']); ?>
                                            </div>
                                            <div class="feature-description">
                                                <?php echo htmlspecialchars($feature['feature_description']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-features">
                                    Bu paket için henüz özellik tanımlanmamış.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Price & Purchase -->
            <div class="col-lg-4 col-md-12">
                <div class="price-card-wrapper">
                    <div class="sticky-price-card">
                        <!-- Price Display -->
                        <div class="price-display">
                            <div class="label">Fiyat</div>
                            <?php if ($package['price'] > 0): ?>
                                <?php
                                // İndirim yüzdesini hesapla
                                $discount_percent = 0;
                                if (!empty($package['old_price']) && $package['old_price'] > $package['price']) {
                                    $discount_percent = round((($package['old_price'] - $package['price']) / $package['old_price']) * 100);
                                }
                                ?>
                                <?php if ($discount_percent > 0): ?>
                                    <div class="discount-badge">%<?php echo $discount_percent; ?> İNDİRİM</div>
                                    <div class="old-price"><?php echo number_format($package['old_price'], 0, ',', '.'); ?>₺</div>
                                <?php endif; ?>
                                <div class="amount"><?php echo number_format($package['price'], 0, ',', '.'); ?>₺</div>
                                <div class="period"><?php echo htmlspecialchars($package['price_period'] ?? 'Tek Seferlik'); ?></div>
                            <?php else: ?>
                                <div class="amount">Ücretsiz</div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="price-actions">
                            <!-- Purchase Button -->
                            <a href="/odeme/<?php echo $package['id']; ?>" class="purchase-button">
                                <span>Hemen Satın Al</span>
                            </a>

                            <!-- Contact CTA -->
                            <div class="contact-cta">
                                <div class="icon">
                                    <i class="fa-regular fa-comments"></i>
                                </div>
                                <div class="title">Sorularınız mı var?</div>
                                <div class="description">
                                    Detaylı bilgi almak için bizimle iletişime geçin.
                                </div>
                                <a href="/iletisim" class="contact-button">
                                    İletişime Geç
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Package Detail Section -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const priceCard = document.querySelector('.sticky-price-card');
        const priceWrapper = document.querySelector('.price-card-wrapper');
        const leftCard = document.querySelector('.package-left-wrapper');

        if (!priceCard || !priceWrapper || !leftCard) return;

        // Mobil ekran kontrolü
        function isMobile() {
            return window.innerWidth <= 991;
        }

        function handleSticky() {
            if (isMobile()) {
                priceCard.classList.remove('is-fixed', 'is-bottom');
                priceCard.style.width = '';
                return;
            }

            const wrapperRect = priceWrapper.getBoundingClientRect();
            const leftCardRect = leftCard.getBoundingClientRect();
            const cardHeight = priceCard.offsetHeight;
            const topOffset = 120;

            // Sol kartın alt pozisyonu
            const leftCardBottom = leftCardRect.bottom;

            // Wrapper'ın genişliğini al
            const wrapperWidth = wrapperRect.width;

            if (wrapperRect.top <= topOffset) {
                // Sol kart hala görünüyorsa sabit kal
                if (leftCardBottom > topOffset + cardHeight + 20) {
                    priceCard.classList.add('is-fixed');
                    priceCard.classList.remove('is-bottom');
                    priceCard.style.width = wrapperWidth + 'px';
                } else {
                    // Sol kart bitmek üzereyse alta yapış
                    priceCard.classList.remove('is-fixed');
                    priceCard.classList.add('is-bottom');
                    priceCard.style.width = '';
                }
            } else {
                // Henüz sticky olması gerekmiyorsa normal pozisyon
                priceCard.classList.remove('is-fixed', 'is-bottom');
                priceCard.style.width = '';
            }
        }

        // Scroll ve resize olaylarını dinle
        window.addEventListener('scroll', handleSticky);
        window.addEventListener('resize', handleSticky);

        // İlk yüklemede kontrol et
        handleSticky();
    });
</script>

<?php include("layout/footer.php"); ?>