<?php
session_start();

// Oturumu sonlandır
session_destroy();

// Çerezleri temizle
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Giriş sayfasına yönlendir
header('Location: /giris');
exit;
?>