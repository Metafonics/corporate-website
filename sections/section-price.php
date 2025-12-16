<?php
// Aktif kategorileri çek (sort_order'a göre sıralı)
$categories_query = $pdo->prepare("
    SELECT * FROM package_categories
    WHERE status = 1
    ORDER BY sort_order ASC
");
$categories_query->execute();
$categories = $categories_query->fetchAll(PDO::FETCH_ASSOC);

// Her kategori için paketleri çek
$packagesByCategory = [];
foreach ($categories as $category) {
    $packages_query = $pdo->prepare("
        SELECT * FROM packages
        WHERE category_id = ? AND status = 1
        ORDER BY sort_order ASC
    ");
    $packages_query->execute([$category['id']]);
    $packages = $packages_query->fetchAll(PDO::FETCH_ASSOC);

    // Her paket için özelliklerini çek
    foreach ($packages as &$package) {
        $features_query = $pdo->prepare("
            SELECT feature FROM package_features
            WHERE package_id = ?
            ORDER BY id ASC
        ");
        $features_query->execute([$package['id']]);
        $package['features'] = $features_query->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($package); // Referans temizleme - önemli!

    // Sadece paketi olan kategorileri dahil et
    if (!empty($packages)) {
        $packagesByCategory[$category['id']] = [
            'category' => $category,
            'packages' => $packages
        ];
    }
}
?>

<!-- Price One -->
<section class="price-three">
    <div class="price-one_bg" style="background-image:url(assets/images/background/price-bg.png)"></div>
    <div class="auto-container">
        <div class="inner-container">
            <!-- Sec Title -->
            <div class="sec-title title-anim centered">
                <div class="sec-title_title">Fiyatlandırma</div>
                <h2 class="sec-title_heading">Bugün ücretsiz katılın</h2>
            </div>
            <div class="pricing-tabs tabs-box">
                <!--Tab Btns-->
                <div class="buttons-outer">
                    <ul class="tab-buttons clearfix">
                        <?php
                        $isFirst = true;
                        foreach ($packagesByCategory as $categoryId => $data) {
                            $category = $data['category'];
                            $activeClass = $isFirst ? 'active-btn' : '';
                            $tabId = 'prod-' . generateSlug($category['name']);
                        ?>
                            <li data-tab="#<?php echo $tabId; ?>" class="tab-btn <?php echo $activeClass; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </li>
                        <?php
                            $isFirst = false;
                        }
                        ?>
                    </ul>
                </div>
                <!--Tabs Container-->
                <div class="tabs-content">
                    <?php
                    $isFirstTab = true;
                    foreach ($packagesByCategory as $categoryId => $data) {
                        $category = $data['category'];
                        $packages = $data['packages'];
                        $tabId = 'prod-' . generateSlug($category['name']);
                        $activeTabClass = $isFirstTab ? 'active-tab' : '';
                    ?>
                        <!-- <?php echo htmlspecialchars($category['name']); ?> Tab -->
                        <div class="tab <?php echo $activeTabClass; ?>" id="<?php echo $tabId; ?>">
                            <div class="content">
                                <div class="price-carousel swiper-container py-5" style="opacity: 0; transition: opacity 0.3s ease;">
                                    <div class="swiper-wrapper">
                                        <?php
                                        foreach ($packages as $index => $package) {
                                        ?>
                                            <div class="swiper-slide">
                                                <div class="price-block_one">
                                                    <div class="price-block_one-inner">
                                                        <div class="price-block_one-title"><?php echo $package['title']; ?></div>
                                                        <div class="price-block_one-subtitle"><?php echo $package['sub_title']; ?></div>
                                                        <div class="price-block_one-content">
                                                            <div class="d-flex justify-content-between align-items-end flex-wrap">
                                                                <?php
                                                                if ($package['price'] > 0) {
                                                                    // İndirim yüzdesini hesapla
                                                                    $discount_percent = 0;
                                                                    if (!empty($package['old_price']) && $package['old_price'] > $package['price']) {
                                                                        $discount_percent = round((($package['old_price'] - $package['price']) / $package['old_price']) * 100);
                                                                    }
                                                                ?>
                                                                    <div class="price-block_one-price-wrapper">
                                                                        <?php if ($discount_percent > 0) { ?>
                                                                            <div class="price-discount-badge">%<?php echo $discount_percent; ?> İNDİRİM</div>
                                                                            <div class="price-block_one-old-price">
                                                                                <sup><?php echo $package['currency']; ?></sup><?php echo number_format($package['old_price'], 2); ?>
                                                                            </div>
                                                                        <?php } ?>
                                                                        <div class="price-block_one-price">
                                                                            <sup><?php echo $package['currency']; ?></sup><?php echo number_format($package['price'], 2); ?>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                                ?>
                                                                <div class="price-block_one-text">*<?php echo $package['text']; ?></div>
                                                            </div>
                                                            <div class="price-block_one-description">
                                                                <?php if (!empty($package['features'])) { ?>
                                                                    <ul class="price-block_one-list">
                                                                        <?php foreach ($package['features'] as $feature) { ?>
                                                                            <li><i class="fa-solid fa-check fa-fw"></i>
                                                                                <div><?php echo $feature; ?></div>
                                                                            </li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="price-block_one-buttons">
                                                                <?php
                                                                $package_slug = generateSlug($package['title']);
                                                                ?>
                                                                <a class="template-btn price-one_button detail-button" href="/paket/<?php echo $package_slug; ?>">
                                                                    Detayları Gör
                                                                </a>

                                                                <?php
                                                                if ($package['price'] > 0) {
                                                                ?>
                                                                    <a class="template-btn price-one_button" href="/odeme/<?php echo urlencode($package['id']) ?>">
                                                                        <?php echo $package['buy_button_text'] ?>
                                                                    </a>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <a class="template-btn price-one_button" href="/iletisim">
                                                                        <?php echo $package['action_button_text'] ?>
                                                                    </a>
                                                                <?php
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>

                                    <!-- Navigation arrows -->
                                    <div class="price-carousel-button-prev">
                                        <i class="fa-solid fa-angle-left"></i>
                                    </div>
                                    <div class="price-carousel-button-next">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                        $isFirstTab = false;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Price One -->