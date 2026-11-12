<?php
session_start();
session_unset();    // Tüm session değişkenlerini temizler
session_destroy();  // Oturumu tamamen yok eder

// Çerezleri (cookie) temizlemek güvenliği artırır
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

header("Location: login.php");
exit;