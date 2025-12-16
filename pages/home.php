<?php

$pageTitle = "Metafonics | Yapay Zeka Destekli Otomasyon ve AI Agent Çözümleri";
$pageDescription = "Yapay zekâ destekli içerik üretimi ve otomasyon çözümleriyle işletmenizi geleceğe taşıyoruz. Blog yazılarından sosyal medya içeriklerine kadar her ihtiyacınız için hızlı, akıllı ve etkili çözümler sunuyoruz.";

include("layout/header.php");

?>

<!-- Google Sitelinks -->

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Metafonics",
        "url": "https://www.metafonics.com",
        "logo": "https://www.metafonics.com/assets/images/logo.svg",
        "description": "Yapay zekâ destekli içerik üretimi ve otomasyon çözümleriyle işletmenizi geleceğe taşıyoruz.",
        "sameAs": [
            "https://www.instagram.com/metafonics.ai",
            "https://www.youtube.com/@Metafonics",
            "https://www.facebook.com/people/Metafonics-Yapay-Zeka-Teknolojileri/61581675249770/",
            "https://www.linkedin.com/in/metafonics-ai/?originalSubdomain=tr",
            "https://x.com/metafonics"
        ],
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Sur Yapı Exen İstanbul, Tantavi, Estergon Caddesi",
            "addressLocality": "Ümraniye",
            "addressRegion": "İstanbul",
            "addressCountry": "TR"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "41.0325588",
            "longitude": "29.0878702"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+90 0216 446 50 50",
            "contactType": "customer service",
            "availableLanguage": "Turkish"
        }
    }
</script>
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Metafonics",
        "url": "https://www.metafonics.com",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://www.metafonics.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
</script>

<?php include("sections/section-slider.php"); ?>

<?php include("sections/section-products.php"); ?>

<?php include("sections/section-about.php"); ?>

<?php include("sections/section-choose.php"); ?>

<?php include("sections/section-answer.php"); ?>

<?php include("sections/section-testimonial.php"); ?>

<?php
// include("sections/section-steps.php"); 
?>

<?php include("sections/section-price.php"); ?>

<?php include("sections/section-faq.php"); ?>

<?php include("layout/footer.php"); ?>