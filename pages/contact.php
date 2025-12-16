<?php

$pageTitle = "İletişim";
$pageDescription = "Metafonics ile iletişime geçin. Yapay zekâ destekli içerik üretimi ve otomasyon çözümlerimiz hakkında bilgi alın, projeleriniz için danışmanlık talep edin ve iş birliği fırsatlarını keşfedin.";

include("layout/header.php");

?>

<!-- Page Title -->
<section class="page-title">
    <div class="page-title-icon" style="background-image:url(assets/images/icons/page-title_icon-1.png)"></div>
    <div class="page-title-icon-two" style="background-image:url(assets/images/icons/page-title_icon-2.png)"></div>
    <div class="page-title-shadow" style="background-image:url(assets/images/background/page-title-1.png)"></div>
    <div class="page-title-shadow_two" style="background-image:url(assets/images/background/page-title-2.png)"></div>
    <div class="auto-container">
        <h1>İletişim</h1>
        <ul class="bread-crumb clearfix">
            <li><a href="index.html">Anasayfa</a></li>
            <li>İletişim</li>
        </ul>
    </div>
</section>
<!-- End Page Title -->

<!-- Contact Info -->
<section class="contact-info">
    <div class="auto-container">
        <div class="row clearfix">

            <!-- Info Block One -->
            <div class="info-block_one col-lg-4 col-md-6 col-sm-12">
                <div class="info-block_one-inner">
                    <div class="info-block_one-icon">
                        <i class="icon-phone"></i>
                    </div>
                    <h4>Bizi arayın</h4>
                    <a href="tel:+9002164465050"> <strong>Yapay Zekâ</strong> +90 0216 446 50 50</a> <br><br>
                </div>
            </div>

            <!-- Info Block One -->
            <div class="info-block_one active col-lg-4 col-md-6 col-sm-12">
                <div class="info-block_one-inner">
                    <div class="info-block_one-icon">
                        <i class="icon-envelope"></i>
                    </div>
                    <h4>Bize e-posta gönderin</h4>
                    <a href="mailto:info@metafonics.com">info@metafonics.com</a> <br><br>
                </div>
            </div>

            <!-- Info Block One -->
            <div class="info-block_one col-lg-4 col-md-6 col-sm-12">
                <div class="info-block_one-inner">
                    <div class="info-block_one-icon">
                        <i class="icon-map"></i>
                    </div>
                    <h4>Konumumuz</h4>
                    <a href="https://maps.app.goo.gl/Tk7Xp1eUnN3YyxrT8" target="_blank">Sur Yapı Exen İstanbul, Tantavi, Estergon Caddesi, Ümraniye/İstanbul</a>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- End Faq One -->

<!-- Team Detail Form -->
<?php include("sections/section-contact-form.php"); ?>
<!-- End Team Detail Form -->

<!-- Map One -->
<section class="map-one">
    <div class="auto-container">
        <div class="map-one_map">
            <iframe width="820" height="560" id="gmap_canvas" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3009.6661747860458!2d29.087870176425945!3d41.03255881792898!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cac85657409dbf%3A0xc753bbc2314b7419!2sSur%20Yap%C4%B1%20Exen%20%C4%B0stanbul!5e0!3m2!1str!2str!4v1756157224483!5m2!1str!2str"></iframe>

        </div>
    </div>
</section>
<!-- End Map One -->

<?php include("layout/footer.php"); ?>