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
// GANTI FUNGSI LAMA DENGAN VERSI DEBUG INI
function ambil_pesanan_by_status($status) {
    global $koneksi;
    
    // ================== ALAT DETEKSI DIMULAI ==================
    echo "<div style='background: #111; color: #00ff00; padding: 15px; font-family: monospace; z-index: 9999; position: relative;'>";
    echo "<h3>-- DEBUG MODE AKTIF --</h3>";

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pembeli') {
        echo "<strong>ERROR:</strong> Tidak ada sesi login pembeli yang valid.<br>";
        echo "</div>";
        return [];
    }
    
    $user_id = (int)$_SESSION['user_id'];
    $status_aman = mysqli_real_escape_string($koneksi, $status);

    // Menampilkan user ID yang sedang login
    echo "<strong>INFO:</strong> Mencari pesanan untuk User ID: <strong>$user_id</strong> dengan status '<strong>$status_aman</strong>'.<br>";

    $query = "SELECT 
                o.id AS order_id, o.total_amount, o.status,
                oi.quantity, oi.price AS price_per_item,
                p.name AS product_name, p.image_url AS product_image_path,
                s.store_name
              FROM orders AS o
              JOIN order_items AS oi ON o.id = oi.order_id
              JOIN products AS p ON oi.product_id = p.id
              JOIN users AS s ON oi.seller_id = s.id
              WHERE o.user_id = $user_id AND o.status = '$status_aman'";
    
    echo "<hr><strong>QUERY SQL:</strong><br><pre>$query</pre>";
              
    $hasil = mysqli_query($koneksi, $query);
    
    // Menampilkan jika ada error dari MySQL
    if (!$hasil) {
        echo "<strong>SQL ERROR:</strong> <span style='color: #ff0000;'>" . mysqli_error($koneksi) . "</span><br>";
        echo "</div>";
        return [];
    }
    
    // Menampilkan jumlah data yang ditemukan
    $jumlah_baris = mysqli_num_rows($hasil);
    echo "<strong>HASIL:</strong> Ditemukan <strong>$jumlah_baris</strong> baris data.<br>";
    echo "</div>";
    // =================== ALAT DETEKSI SELESAI ===================
    
    $pesanan = [];
    while ($baris = mysqli_fetch_assoc($hasil)) {
        $pesanan[] = $baris;
    }
    
    return $pesanan;
}

// FUNGSI BARU: Mengambil semua produk untuk ditampilkan di beranda
function ambil_semua_produk() {
    global $koneksi;
    $query = "SELECT * FROM products WHERE status = 'active' AND stock > 0 ORDER BY created_at DESC";
    $result = mysqli_query($koneksi, $query);
    $products = [];
    while($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    return $products;
}

// FUNGSI BARU: Mengambil satu produk berdasarkan ID untuk halaman detail
function ambil_produk_by_id($product_id) {
    global $koneksi;
    $id = (int)$product_id;
    
    $stmt = mysqli_prepare($koneksi, 
        "SELECT p.*, u.store_name 
         FROM products p 
         JOIN users u ON p.seller_id = u.id 
         WHERE p.id = ? AND p.status = 'active'");
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

// FUNGSI BARU: Menambah produk ke keranjang
function tambah_ke_keranjang($user_id, $product_id, $quantity = 1) {
    global $koneksi;
    $uid = (int)$user_id;
    $pid = (int)$product_id;
    $qty = (int)$quantity;

    // Cek apakah produk sudah ada di keranjang user
    $stmt_check = mysqli_prepare($koneksi, "SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $pid);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        // Jika sudah ada, update quantity
        $stmt_update = mysqli_prepare($koneksi, "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt_update, "iii", $qty, $uid, $pid);
        mysqli_stmt_execute($stmt_update);
    } else {
        // Jika belum ada, insert baru
        $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "iii", $uid, $pid);
        mysqli_stmt_execute($stmt_insert);
    }

    return ['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang!'];
}

// FUNGSI BARU: Membuat pesanan dari keranjang
function buat_pesanan_dari_keranjang($user_id, $payment_method, $shipping_address) {
    global $koneksi;
    $uid = (int)$user_id;

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Ambil semua item dari keranjang user
        $cart_query = mysqli_prepare($koneksi, "SELECT c.*, p.price, p.seller_id FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        mysqli_stmt_bind_param($cart_query, "i", $uid);
        mysqli_stmt_execute($cart_query);
        $cart_items = mysqli_stmt_get_result($cart_query)->fetch_all(MYSQLI_ASSOC);

        if (empty($cart_items)) {
            throw new Exception("Keranjang kosong.");
        }

        // 2. Hitung total harga
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        // 3. Buat pesanan di tabel 'orders'
        $order_stmt = mysqli_prepare($koneksi, "INSERT INTO orders (user_id, total_amount, status, payment_method, shipping_address) VALUES (?, ?, 'pending', ?, ?)");
        mysqli_stmt_bind_param($order_stmt, "idss", $uid, $total_amount, $payment_method, $shipping_address);
        mysqli_stmt_execute($order_stmt);
        $order_id = mysqli_insert_id($koneksi);

        // 4. Pindahkan item dari keranjang ke 'order_items'
        $order_item_stmt = mysqli_prepare($koneksi, "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            mysqli_stmt_bind_param($order_item_stmt, "iiiid", $order_id, $item['product_id'], $item['seller_id'], $item['quantity'], $item['price']);
            mysqli_stmt_execute($order_item_stmt);
            
            // 5. Kurangi stok produk
            $stock_stmt = mysqli_prepare($koneksi, "UPDATE products SET stock = stock - ? WHERE id = ?");
            mysqli_stmt_bind_param($stock_stmt, "ii", $item['quantity'], $item['product_id']);
            mysqli_stmt_execute($stock_stmt);
        }
        
        // 6. Kosongkan keranjang
        $clear_cart_stmt = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($clear_cart_stmt, "i", $uid);
        mysqli_stmt_execute($clear_cart_stmt);

        // Jika semua berhasil, commit transaksi
        mysqli_commit($koneksi);
        return ['status' => 'success', 'order_id' => $order_id];

    } catch (Exception $e) {
        // Jika ada error, batalkan semua perubahan
        mysqli_rollback($koneksi);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// TAMBAHKAN FUNGSI BARU INI DI FILE fungsi.php
function ambil_daftar_percakapan($user_id, $user_role) {
    global $koneksi;
    $uid = (int)$user_id;

    $query = "";
    if ($user_role === 'pembeli') {
        // Jika pembeli, tampilkan semua penjual dan admin (CS)
        $query = "SELECT id, fullname, role, store_name FROM users WHERE role = 'penjual' OR role = 'admin'";
    } elseif ($user_role === 'penjual') {
        // Jika penjual, tampilkan hanya pembeli yang pernah mengirim pesan kepadanya
        $query = "
            SELECT DISTINCT u.id, u.fullname, u.role, u.store_name
            FROM users u
            JOIN messages m ON u.id = m.sender_id
            WHERE m.receiver_id = $uid AND u.role = 'pembeli'
        ";
    }

    if (empty($query)) {
        return [];
    }
    
    $result = mysqli_query($koneksi, $query);
    if (!$result) return [];

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>