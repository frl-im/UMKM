<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'fungsi.php';

// Mulai session dengan aman
start_secure_session();

// Fungsi untuk memeriksa apakah user sudah login
function require_login($role = null) {
    if (!check_login($role)) {
        exit(); // check_login sudah handle redirect
    }
}

// Fungsi untuk mendapatkan info user yang sedang login (nama diubah untuk menghindari konflik)
function get_logged_user() {
    if (!is_logged_in()) {
        return false;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'store_name' => $_SESSION['store_name'] ?? null,
        'verification_status' => $_SESSION['verification_status'] ?? null
    ];
}

// Fungsi alternative dengan nama yang berbeda
function get_session_user_data() {
    return get_logged_user();
}

// Auto logout jika session expired - TANPA REDIRECT OTOMATIS
if (is_logged_in()) {
    // Jika lebih dari 1 jam tidak ada aktivitas
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 3600) {
        // Log untuk debugging
        error_log("Auto logout due to inactivity");
        logout_user();
        // JANGAN redirect otomatis, biarkan halaman menentukan
    } else {
        $_SESSION['last_activity'] = time();
    }
}

// Fungsi untuk debugging session (hapus di production)
function debug_session_info() {
    if (!is_logged_in()) {
        echo "<div style='background: #ffebee; padding: 10px; border: 1px solid #f44336; color: #c62828;'>";
        echo "<strong>DEBUG:</strong> User tidak login atau session tidak valid";
        echo "</div>";
        return;
    }
    
    echo "<div style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50; color: #2e7d32; font-size: 12px;'>";
    echo "<strong>DEBUG SESSION INFO:</strong><br>";
    echo "Session Status: " . session_status() . " (2 = aktif)<br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
    echo "User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "<br>";
    echo "Login Time: " . (isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'Not set') . "<br>";
    echo "Last Activity: " . (isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'Not set') . "<br>";
    echo "</div>";
}
?>