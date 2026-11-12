<?php
if (session_status() === PHP_SESSION_NONE) {
    // Session çerezlerini sadece HTTP üzerinden erişilebilir yap (JS ile çalınamaz)
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// 1. Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Oturum Sabitleme Koruması (Session Fixation)
// Her 5 dakikada bir session ID'sini yeniler
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
?>