<?php
// PERBAIKAN UTAMA - Ganti konfigurasi session yang konflik
ini_set('session.cookie_lifetime', 7200); // 2 jam (konsisten)
ini_set('session.gc_maxlifetime', 7200);  // 2 jam (konsisten)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// --- KONEKSI KE DATABASE ---
$koneksi = mysqli_connect("localhost", "root", "", "Kreasidb");

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

function query($query) {
    global $koneksi;
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        die("Query error: " . mysqli_error($koneksi));
    }
    
    $rows = [];
    while($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// PERBAIKAN FUNGSI SESSION - Hilangkan regeneration yang bermasalah
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // HAPUS REGENERATION OTOMATIS - Ini yang menyebabkan masalah
    // Hanya set last_regeneration jika belum ada
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    }
}

// PERBAIKAN FUNGSI LOGIN - Pastikan last_activity di-set
function login_user($data) {
    global $koneksi;
    
    // Validasi input
    if (empty($data['email']) || empty($data['password'])) {
        return false;
    }

    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $password = $data['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            // Mulai session dengan aman
            start_secure_session();
            
            // PERBAIKAN: Pastikan semua session variable di-set dengan benar
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time(); // PERBAIKAN: Set last_activity
            
            // Untuk penjual, simpan juga nama toko
            if ($user['role'] == 'penjual') {
                $_SESSION['store_name'] = $user['store_name'];
                $_SESSION['verification_status'] = $user['verification_status'];
            }
            
            return true;
        }
    }
    return false;
}

// PERBAIKAN FUNGSI CHECK_LOGIN - Konsistensi timeout
function check_login($required_role = null) {
    start_secure_session();
    
    // Cek session validity
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
        if ($required_role !== null) {
            redirect_to_login($required_role);
        }
        return false;
    }
    
    // PERBAIKAN: Konsisten pakai 7200 detik (2 jam)
    if (time() - $_SESSION['login_time'] > 7200) {
        session_unset();
        session_destroy();
        if ($required_role !== null) {
            redirect_to_login($required_role);
        }
        return false;
    }
    
    // PERBAIKAN: SELALU update last_activity
    $_SESSION['last_activity'] = time();
    
    // Role check
    if ($required_role && $_SESSION['user_role'] !== $required_role) {
        redirect_to_login($required_role);
        return false;
    }
    
    return true;
}

// SISANYA TETAP SAMA... (copy paste fungsi lainnya dari kode asli)
function registerpembeli($data) {
    global $koneksi;

    if (empty($data['fullname']) || empty($data['email']) || empty($data['password'])) {
        return false;
    }

    $fullname = mysqli_real_escape_string($koneksi, $data['fullname']);
    $email = mysqli_real_escape_string($koneksi, $data['email']);
    
    $check_email = "SELECT email FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $check_email);
    if (mysqli_num_rows($result) > 0) {
        return -1;
    }
    
    $password = password_hash($data['password'], PASSWORD_BCRYPT);
    $query = "INSERT INTO users (fullname, email, password_hash, role) VALUES ('$fullname', '$email', '$password', 'pembeli')";
    
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

function register_penjual($data) {
    global $koneksi;

    if (empty($data['owner-name']) || empty($data['store-name']) || empty($data['email']) || 
        empty($data['password']) || empty($data['phone'])) {
        return false;
    }

    $owner_name = mysqli_real_escape_string($koneksi, $data['owner-name']);
    $store_name = mysqli_real_escape_string($koneksi, $data['store-name']);
    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $phone = mysqli_real_escape_string($koneksi, $data['phone']);
    
    $check_email = "SELECT email FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $check_email);
    if (mysqli_num_rows($result) > 0) {
        return -1;
    }
    
    $password = password_hash($data['password'], PASSWORD_BCRYPT);
    $query = "INSERT INTO users (fullname, email, password_hash, role, store_name, phone) VALUES ('$owner_name', '$email', '$password', 'penjual', '$store_name', '$phone')";

    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

function get_user_by_id($user_id) {
    global $koneksi;
    
    $user_id = (int)$user_id;
    $query = "SELECT * FROM users WHERE id = $user_id";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result === false) {
        return false; 
    }
    
    if (mysqli_num_rows($result) === 1) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

function logout_user() {
    start_secure_session();
    
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    return true;
}

function safe_output($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function format_price($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function redirect_to_login($required_role = null) {
    if ($required_role === 'penjual') {
        header("Location: loginpenjual.php");
    } else {
        header("Location: loginpembeli.php");
    }
    exit();
}

function is_logged_in() {
    start_secure_session();
    return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
}

function debug_session_status() {
    error_log("=== SESSION DEBUG ===");
    error_log("Session Status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    error_log("Login Time: " . ($_SESSION['login_time'] ?? 'NOT SET'));
    error_log("Last Activity: " . ($_SESSION['last_activity'] ?? 'NOT SET'));
    error_log("Current Time: " . time());
    if (isset($_SESSION['login_time'])) {
        error_log("Time Diff: " . (time() - $_SESSION['login_time']) . " seconds");
    }
    error_log("===================");
}
?>