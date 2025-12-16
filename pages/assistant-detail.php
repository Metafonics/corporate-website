	<?php

	$product = $all_products[$product_id];

	$pageTitle = $product['title'];
	$pageDescription = $product['meta-description'];

	include("layout/header.php");
	include("data.php");

	// $product_id router tarafından gönderiliyor
	if (!isset($product_id) || !isset($all_products[$product_id])) {
		http_response_code(404);
		include("../404.php");
		exit;
	}

	?>

	<style>
		.page-title.assistant-detail:before {
			background-image:
				linear-gradient(to right, var(--main-color), var(--color-five)),
				url('<?php echo $product['details']['background_image'] ?? 'assets/images/background/page-title-default.jpg'; ?>') !important;
			background-blend-mode: overlay;
			background-size: cover;
			background-position: center;
			opacity: 0.7;
		}
	</style>

	<!-- Page Title -->
	<section class="page-title assistant-detail">
		<div class="page-title-icon" style="background-image:url(assets/images/icons/page-title_icon-1.png)"></div>
		<div class="page-title-icon-two" style="background-image:url(assets/images/icons/page-title_icon-2.png)">
		</div>
		<div class="page-title-shadow" style="background-image:url(assets/images/background/page-title-1.png)">
		</div>
		<div class="page-title-shadow_two" style="background-image:url(assets/images/background/page-title-2.png)">
		</div>
		<div class="auto-container">
			<h1><?php echo $product['title'] ?></h1>
			<p class="text-light mt-3"><?php echo $product['short-description'] ?></p>
			<!-- <ul class="bread-crumb clearfix">
				<li><a href="/">Anasayfa</a></li>
				<li><a href="/sektorel-asistanlar">Sektörel Asistanlar</a></li>
				<li><?php echo $product['title'] ?></li>
			</ul> -->
		</div>
	</section>
	<!-- End Page Title -->

	<!-- Video One -->
	<?php
	if ($product["details"]["video"]) {
	?>
		<section class="video-one">
			<div class="auto-container mt-5">
				<div class="video-one_image">
					<iframe width="100%" height="500" src="<?php echo $product["details"]["video"]; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
			</div>
		</section>
	<?php
	}
	?>

	<!-- Services Detail -->
	<section class="services-detail mb-5">
		<div class="auto-container">
			<div class="sec-title style-four">
				<div class="col-lg-12">
					<div class="sec-title_title"><?php echo $product['title'] ?></div>
					<h2 class="sec-title_heading">Neden <?php echo $product['title'] ?>?</h2>
				</div>
				<div class="col-lg-12">
					<p><?php echo $product['details']['why_needed'] ?? ''; ?></p>
				</div>
			</div>

			<hr>

			<div class="sec-title style-four">
				<div class="col-lg-6">
					<div class="col-lg-12">
						<h2 class="sec-title_heading">Asistan Ne Yapar?</h2>
					</div>
					<div class="col-lg-12 mt-5">
						<ul class="solution-one_list">
							<?php foreach ($product['details']['features'] ?? [] as $feature): ?>
								<li><i class="fa-solid fa-check fa-fw"></i><?php echo $feature; ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<hr>

			<div class="sec-title style-four mt-5">
				<div class="col-lg-12">
					<?php if ($product_id == 15 || $product['slug'] == 'insan-kaynaklari-asistani'): ?>
						<h2 class="sec-title_heading">Aday İçin Faydaları</h2>
					<?php else: ?>
						<h2 class="sec-title_heading">Müşteri İçin Faydaları</h2>
					<?php endif; ?>
				</div>
				<div class="col-lg-12 mt-5">
					<ul class="solution-one_list">
						<?php foreach ($product['details']['customer_benefits'] ?? [] as $benefit): ?>
							<li><i class="fa-solid fa-check fa-fw"></i><?php echo $benefit; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<hr>

			<div class="sec-title style-four mt-5">
				<div class="col-lg-12">
					<h2 class="sec-title_heading">İşletme İçin Faydaları</h2>
				</div>
				<div class="col-lg-12 mt-5">
					<ul class="solution-one_list">
						<?php foreach ($product['details']['business_benefits'] ?? [] as $benefit): ?>
							<li><i class="fa-solid fa-check fa-fw"></i><?php echo $benefit; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<hr>

			<p class="text-light"><strong>Sonuç:</strong> <?php echo $product['details']['result'] ?></p>
		</div>
	</section>
	<!-- End Services One -->

	<?php include("layout/footer.php"); ?>