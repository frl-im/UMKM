<?php
// authlogic.php - DIPERBAIKI
// Konfigurasi session yang benar
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 7200); // 2 jam
ini_set('session.name', 'KREASI_SESSION');

// Mulai session dengan aman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

$action = $_POST['action'] ?? '';

// Fungsi helper untuk set session login
function set_login_session($user) {
    // Regenerate session ID untuk keamanan
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['fullname'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['last_regeneration'] = time();
    
    // Untuk penjual, simpan juga nama toko
    if ($user['role'] == 'penjual') {
        $_SESSION['store_name'] = $user['store_name'];
        $_SESSION['verification_status'] = $user['verification_status'] ?? 0;
    }
}

// --- LOGIKA UNTUK REGISTRASI PEMBELI ---
if ($action == 'register_pembeli') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($fullname) || empty($email) || empty($password)) {
        header("Location: registerpembeli.php?status=empty_fields");
        exit();
    }
    
    // Cek apakah email sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: registerpembeli.php?status=email_exists");
        exit();
    }
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password_hash, role, created_at) VALUES (?, ?, ?, 'pembeli', NOW())");
        $stmt->execute([$fullname, $email, $password_hash]);
        header("Location: loginpembeli.php?status=reg_success");
    } catch (PDOException $e) {
        header("Location: registerpembeli.php?status=reg_failed");
    }
    exit();
}

// --- LOGIKA UNTUK REGISTRASI PENJUAL ---
if ($action == 'register_penjual') {
    $owner_name = trim($_POST['owner-name']);
    $store_name = trim($_POST['store-name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    
    // Validasi input
    if (empty($owner_name) || empty($store_name) || empty($email) || empty($password) || empty($phone)) {
        header("Location: registerpenjual.php?status=empty_fields");
        exit();
    }
    
    // Cek apakah email sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: registerpenjual.php?status=email_exists");
        exit();
    }
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password_hash, role, store_name, phone, verification_status, created_at) VALUES (?, ?, ?, 'penjual', ?, ?, 0, NOW())");
        $stmt->execute([$owner_name, $email, $password_hash, $store_name, $phone]);
        header("Location: loginpenjual.php?status=reg_success");
    } catch (PDOException $e) {
        header("Location: registerpenjual.php?status=reg_failed");
    }
    exit();
}

// --- LOGIKA UNTUK LOGIN (PEMBELI & PENJUAL) - DIPERBAIKI ---
if ($action == 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $from_page = $_POST['from_page'] ?? 'login.php';
    
    // Validasi input
    if (empty($email) || empty($password)) {
        header("Location: $from_page?status=empty_fields");
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session login
            set_login_session($user);
            
            // Redirect berdasarkan role
            if ($user['role'] == 'pembeli') {
                header("Location: profil-pembeli.php");
            } else {
                header("Location: profilpenjual.php");
            }
            exit();
        } else {
            header("Location: $from_page?status=login_failed");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: $from_page?status=system_error");
        exit();
    }
}

// --- LOGIKA UNTUK LOGOUT ---
if ($action == 'logout') {
    // Hapus semua data session
    $_SESSION = array();
    
    // Hapus cookie session jika ada
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    header("Location: login.php?status=logout_success");
    exit();
}

// Jika tidak ada action yang cocok
header("Location: login.php");
exit();
?>