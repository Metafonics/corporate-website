<?php

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$baseUrl = "$protocol://$domain";

include(__DIR__ . "/../config/database.php");

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <!-- Charset & Compatibility -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="robots" content="noindex, nofollow">

    <base href="/">

    <!-- SEO Meta -->
    <title><?php echo $pageTitle ? "$pageTitle | Metafonics" : "Metafonics | Yapay Zeka Destekli Otomasyon ve AI Agent Çözümleri"; ?></title>
    <meta name="description" content="<?php echo $pageDescription ? $pageDescription : "Metafonics, yapay zeka destekli otomasyon sistemleri ve AI agent çözümleriyle işletmelerin verimliliğini artırır. İş süreçlerinizi akıllandırın, geleceğe bugünden adım atın."; ?>">

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.png" type="image/x-icon">
    <link rel="icon" href="assets/images/favicon.png" type="image/x-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/meanmenu.min.css" rel="stylesheet">
    <link href="assets/css/responsive.css" rel="stylesheet">
</head>


<body>

    <div class="page-wrapper">

        <?php include("components/component-cursor.php"); ?>

        <?php
        // include("components/component-preloader.php");
        ?>

        <?php include("sections/section-navbar.php"); ?>