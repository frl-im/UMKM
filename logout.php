<?php
// Memuat file fungsi utama, terutama untuk start_secure_session()
require_once 'fungsi.php';

// Memulai sesi dengan aman agar bisa diakses dan dihancurkan
start_secure_session();

// 1. Kosongkan semua variabel sesi
$_SESSION = array();

// 2. Hancurkan cookie sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan sesi secara total
session_destroy();

// 4. Arahkan pengguna ke halaman beranda (index.php) setelah logout
header("Location: index.php?status=logout_success");
exit();
?>