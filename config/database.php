<?php
require_once 'env.php';
EnvLoader::load();

try {
    $pdo = new PDO("mysql:host=" . EnvLoader::get('DB_HOST') . ";dbname=" . EnvLoader::get('DB_NAME') . ";charset=utf8mb4", EnvLoader::get('DB_USERNAME'), EnvLoader::get('DB_PASSWORD'));

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
    $pdo->exec("SET CHARACTER SET utf8mb4");
} catch (PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
}
