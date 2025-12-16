<?php

$pageTitle = "Ürünlerimiz";
$pageDescription = "Metafonics’in yapay zekâ destekli içerik üretimi ve otomasyon çözümleriyle tanışın. İşletmenizin ihtiyaçlarına yönelik inovatif ürünlerimiz, verimliliği artırmak ve dijital süreçleri optimize etmek için tasarlanmıştır.";

include("layout/header.php");
include("data.php");

$assistants = array_filter($all_products, fn($p) => $p['type'] === 'sectoral');
?>

<!-- Page Title -->
<section class="page-title">
    <div class="page-title-icon" style="background-image:url(assets/images/icons/page-title_icon-1.png)"></div>
    <div class="page-title-icon-two" style="background-image:url(assets/images/icons/page-title_icon-2.png)"></div>
    <div class="page-title-shadow" style="background-image:url(assets/images/background/page-title-1.png)"></div>
    <div class="page-title-shadow_two" style="background-image:url(assets/images/background/page-title-2.png)"></div>
    <div class="auto-container">
        <h1>Sektörel Asistanlarımız</h1>
        <ul class="bread-crumb clearfix">
            <li><a href="/">Anasayfa</a></li>
            <li>Sektörel Asistanlarımız</li>
        </ul>
    </div>
</section>
<!-- End Page Title -->

<!-- Services One -->
<section class="services-one style-two">
    <div class="auto-container">
        <div class="row clearfix">
            <?php foreach ($assistants as $id => $assistant) { ?>
                <!-- Service Block One -->
                <div class="service-block_one col-lg-4 col-md-6 col-sm-12">
                    <div class="service-block_one-inner wow fadeInLeft news-block_one-inner" data-wow-delay="0ms" data-wow-duration="1500ms">
                        <div class="news-block_one-image video-hover">
                            <a href="/sektorel-asistanlar/<?php echo $assistant['slug'] ?>">
                                <?php if (!empty($assistant['short-video'])): ?>
                                    <video
                                        class="preview-video"
                                        muted
                                        loop
                                        playsinline
                                        preload="metadata"
                                        poster="<?php echo $assistant['poster'] ?? 'assets/images/video-fallback.jpg'; ?>">
                                        <source src="<?php echo $assistant['short-video'] ?>" type="video/mp4">
                                        Tarayıcınız video etiketini desteklemiyor.
                                    </video>
                                <?php endif; ?>
                            </a>
                        </div>
                        <h5 class="service-block_one-heading">
                            <a href="/sektorel-asistanlar/<?php echo $assistant['slug'] ?>">
                                <?php echo $assistant['title'] ?>
                            </a>
                        </h5>
                        <div class="service-block_one-text"><?php echo $assistant['description'] ?></div>
                        <div class="lower-box d-flex justify-content-between align-items-center flex-wrap">
                            <div class="service-block_one-number"><?php echo $id ?></div>
                            <a class="service-block_one-join" href="/sektorel-asistanlar/<?php echo $assistant['slug'] ?>">
                                İncele <i class="fa-solid fa-plus fa-fw"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
<!-- End Services One -->

<!-- Price Three -->
<?php include("sections/section-price.php"); ?>
<!-- End Price Three -->

<script>
    document.querySelectorAll('.video-hover').forEach(el => {
        const video = el.querySelector('.preview-video');
        if (!video) return;

        el.addEventListener('mouseenter', () => {
            video.play().catch(() => {}); // Safari için hatayı yut
        });
        el.addEventListener('mouseleave', () => {
            video.pause();
            video.currentTime = 0; // tekrar başa al
        });
    });
</script>

<?php include("layout/footer.php"); ?>