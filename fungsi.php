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

    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    $user_id = (int)$_SESSION['user_id'];
    $status_aman = mysqli_real_escape_string($koneksi, $status);

    // Query ini mengambil semua item dari semua pesanan dengan status tertentu
    $query = "SELECT
                o.id AS order_id,
                o.total_amount,
                o.status AS order_status,
                oi.quantity,
                oi.price AS price_per_item,
                p.id AS product_id,
                p.name AS product_name,
                p.image_url AS product_image_path,
                s.store_name
              FROM orders AS o
              JOIN order_items AS oi ON o.id = oi.order_id
              JOIN products AS p ON oi.product_id = p.id
              JOIN users AS s ON p.seller_id = s.id
              WHERE o.user_id = ? AND o.status = ?";

    $stmt = mysqli_prepare($koneksi, $query);
    if (!$stmt) {
        error_log("Prepare failed in ambil_pesanan_by_status: " . mysqli_error($koneksi));
        return [];
    }

    mysqli_stmt_bind_param($stmt, "is", $user_id, $status_aman);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // [DIPERBAIKI] Logika untuk mengelompokkan item ke dalam pesanan yang sesuai
    $pesanan_terstruktur = [];
    while ($item = mysqli_fetch_assoc($result)) {
        $order_id = $item['order_id'];

        // Jika pesanan ini belum ada di array, buat entri baru untuk pesanan tersebut
        if (!isset($pesanan_terstruktur[$order_id])) {
            $pesanan_terstruktur[$order_id] = [
                'order_id'      => $order_id,
                'total_amount'  => $item['total_amount'],
                'order_status'  => $item['order_status'],
                'items'         => [] // Buat array kosong untuk menampung item-itemnya
            ];
        }

        // Tambahkan item saat ini ke dalam array 'items' dari pesanan yang sesuai
        $pesanan_terstruktur[$order_id]['items'][] = $item;
    }

    // Kembalikan array yang sudah dikelompokkan dengan benar
    return array_values($pesanan_terstruktur);
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

    // Cek dulu apakah produknya ada dan stoknya cukup
    $product = ambil_produk_by_id($pid);
    if (!$product) {
        return ['status' => 'error', 'message' => 'Produk tidak ditemukan.'];
    }

    // Cek apakah produk sudah ada di keranjang user
    $stmt_check = mysqli_prepare($koneksi, "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $pid);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $existing_item = mysqli_fetch_assoc($result_check);

    if ($existing_item) {
        // Jika sudah ada, update quantity
        $new_quantity = $existing_item['quantity'] + $qty;
        if ($new_quantity > $product['stock']) {
            return ['status' => 'error', 'message' => 'Jumlah melebihi stok yang tersedia.'];
        }

        $stmt_update = mysqli_prepare($koneksi, "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt_update, "iii", $new_quantity, $uid, $pid);
        
        // PERBAIKAN: Cek apakah eksekusi berhasil
        if (mysqli_stmt_execute($stmt_update)) {
            return ['status' => 'success', 'message' => 'Jumlah produk di keranjang diperbarui!'];
        } else {
            return ['status' => 'error', 'message' => 'Gagal memperbarui keranjang.'];
        }
    } else {
        // Jika belum ada, insert baru
        if ($qty > $product['stock']) {
            return ['status' => 'error', 'message' => 'Jumlah melebihi stok yang tersedia.'];
        }

        $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "iii", $uid, $pid, $qty);

        // PERBAIKAN: Cek apakah eksekusi berhasil
        if (mysqli_stmt_execute($stmt_insert)) {
            return ['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang!'];
        } else {
            return ['status' => 'error', 'message' => 'Gagal menambahkan ke keranjang. Error: ' . mysqli_stmt_error($stmt_insert)];
        }
    }
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

// TAMBAHKAN FUNGSI INI DI DALAM FILE FUNGSI.PHP
function ambil_isi_keranjang($user_id) {
    global $koneksi;
    $uid = (int)$user_id;
    
    $query = "
        SELECT p.id, p.name, p.price, p.image_url, p.stock, c.quantity, u.store_name, p.seller_id 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        JOIN users u ON p.seller_id = u.id
        WHERE c.user_id = ? AND p.status = 'active'
    ";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// TAMBAHKAN FUNGSI-FUNGSI INI KE DALAM FILE FUNGSI.PHP

function get_primary_address($user_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user_addresses WHERE user_id = ? AND is_primary = 1 LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function apply_voucher($voucher_code) {
    global $koneksi;
    $code = mysqli_real_escape_string($koneksi, $voucher_code);
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM vouchers WHERE voucher_code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
    mysqli_stmt_bind_param($stmt, "s", $code);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// TAMBAHKAN 3 FUNGSI INI KE DALAM FILE fungsi.php

function get_all_addresses($user_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_primary DESC");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

function add_address($user_id, $data) {
    global $koneksi;
    $uid = (int)$user_id;

    // Cek apakah sudah ada alamat lain, jika tidak ada, jadikan ini utama
    $existing_addresses = get_all_addresses($uid);
    $is_primary = empty($existing_addresses) ? 1 : 0;

    $stmt = mysqli_prepare($koneksi, "INSERT INTO user_addresses (user_id, label, recipient_name, phone, full_address, is_primary) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssi", $uid, $data['label'], $data['recipient_name'], $data['phone'], $data['full_address'], $is_primary);
    return mysqli_stmt_execute($stmt);
}

function set_primary_address($user_id, $address_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $aid = (int)$address_id;
    // Reset semua alamat lain menjadi bukan utama
    mysqli_query($koneksi, "UPDATE user_addresses SET is_primary = 0 WHERE user_id = $uid");
    // Set alamat yang dipilih menjadi utama
    $stmt = mysqli_prepare($koneksi, "UPDATE user_addresses SET is_primary = 1 WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $aid, $uid);
    return mysqli_stmt_execute($stmt);
}

// FUNGSI BARU: Mengambil produk berdasarkan kategori
function ambil_produk_by_kategori($kategori) {
    global $koneksi;
    
    // Menggunakan prepared statement untuk keamanan dari SQL Injection
    $stmt = mysqli_prepare($koneksi, 
        "SELECT 
            p.*, 
            u.store_name AS author,
            u.fullname AS seller_name
         FROM products p 
         LEFT JOIN users u ON p.seller_id = u.id 
         WHERE p.category = ? AND p.status = 'active' AND p.stock > 0
         ORDER BY p.created_at DESC"
    );

    // Jika statement gagal disiapkan, ini akan membantu proses debug
    if ($stmt === false) {
        error_log("Prepare statement failed in ambil_produk_by_kategori: " . mysqli_error($koneksi));
        return []; // Kembalikan array kosong jika query gagal
    }

    mysqli_stmt_bind_param($stmt, "s", $kategori);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}
// TAMBAHKAN SEMUA FUNGSI INI KE DALAM FILE fungsi.php

// Fungsi untuk Wishlist
function tambah_ke_wishlist($user_id, $product_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $pid = (int)$product_id;

    // Cek dulu agar tidak duplikat
    $stmt_check = mysqli_prepare($koneksi, "SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $pid);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) {
        return ['status' => 'info', 'message' => 'Produk sudah ada di wishlist.'];
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ii", $uid, $pid);
    if (mysqli_stmt_execute($stmt)) {
        return ['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke wishlist!'];
    }
    return ['status' => 'error', 'message' => 'Gagal menambahkan ke wishlist.'];
}

function ambil_wishlist_by_user($user_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $query = "SELECT p.id, p.name, p.price, p.image_url FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi untuk Toko Pilihan
function ambil_toko_pilihan() {
    global $koneksi;
    // Query ini hanya mengambil kolom yang pasti ada
    $query = "SELECT id, store_name, store_description FROM users WHERE role = 'penjual' AND verification_status = 'verified' LIMIT 10";
    $result = mysqli_query($koneksi, $query);

    // Menambahkan pengecekan error agar tidak terjadi fatal error
    if ($result === false) {
        return []; // Kembalikan array kosong jika query gagal
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi untuk Voucher
function ambil_semua_voucher() {
    global $koneksi;
    $query = "SELECT * FROM vouchers WHERE is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// TAMBAHKAN DUA FUNGSI INI KE DALAM FILE fungsi.php

/**
 * Fungsi untuk mengklaim voucher berdasarkan kode.
 */
function klaim_voucher_by_code($user_id, $voucher_code) {
    global $koneksi;
    $uid = (int)$user_id;
    $code = mysqli_real_escape_string($koneksi, $voucher_code);

    // 1. Cari voucher berdasarkan kodenya
    $stmt_find = mysqli_prepare($koneksi, "SELECT id FROM vouchers WHERE voucher_code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
    mysqli_stmt_bind_param($stmt_find, "s", $code);
    mysqli_stmt_execute($stmt_find);
    $result_find = mysqli_stmt_get_result($stmt_find);
    $voucher = mysqli_fetch_assoc($result_find);

    if (!$voucher) {
        return "Voucher tidak valid atau sudah kedaluwarsa.";
    }
    $voucher_id = $voucher['id'];

    // 2. Cek apakah user sudah pernah klaim voucher ini
    $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $voucher_id);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) {
        return "Anda sudah pernah mengklaim voucher ini.";
    }

    // 3. Tambahkan voucher ke koleksi user
    $stmt_claim = mysqli_prepare($koneksi, "INSERT INTO user_vouchers (user_id, voucher_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_claim, "ii", $uid, $voucher_id);
    if (mysqli_stmt_execute($stmt_claim)) {
        return "Voucher berhasil diklaim!";
    }
    return "Terjadi kesalahan saat mengklaim voucher.";
}

/**
 * Fungsi untuk mengambil semua voucher yang dimiliki user.
 */
// HAPUS FUNGSI klaim_voucher_by_code DAN GANTI DENGAN INI
function klaim_voucher_by_id($user_id, $voucher_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $vid = (int)$voucher_id;

    // Cek apakah user sudah pernah klaim voucher ini
    $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $vid);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) {
        return "Anda sudah pernah mengklaim voucher ini.";
    }

    // Tambahkan voucher ke koleksi user
    $stmt_claim = mysqli_prepare($koneksi, "INSERT INTO user_vouchers (user_id, voucher_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_claim, "ii", $uid, $vid);
    if (mysqli_stmt_execute($stmt_claim)) {
        return "Voucher berhasil diklaim! Cek di 'Voucher Saya'.";
    }
    return "Terjadi kesalahan saat mengklaim voucher.";
}
// Fungsi untuk mengambil pesanan berdasarkan status
function log_payment_activity($external_id, $status, $message = '') {
    global $koneksi;
    
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO payment_logs (external_id, status, message, created_at) VALUES (?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt, "sss", $external_id, $status, $message);
    
    return mysqli_stmt_execute($stmt);
}

// Fungsi untuk mengirim notifikasi (bisa dikembangkan lebih lanjut)
function send_order_notification($user_id, $order_id, $message) {
    global $koneksi;
    
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO notifications (user_id, order_id, message, type, is_read, created_at) 
         VALUES (?, ?, ?, 'order', 0, NOW())"
    );
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $order_id, $message);
    
    return mysqli_stmt_execute($stmt);
}

// Fungsi untuk mengecek apakah order bisa dibatalkan
function can_cancel_order($order_id) {
    global $koneksi;
    
    $stmt = mysqli_prepare($koneksi, 
        "SELECT order_status, payment_status FROM orders WHERE order_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) return false;
    
    // Order bisa dibatalkan jika statusnya pending atau payment_status belum paid
    return in_array($order['order_status'], ['pending', 'confirmed']) && 
           in_array($order['payment_status'], ['pending', 'failed']);
}

// Fungsi untuk validasi pembayaran dari Xendit
function validate_xendit_callback($data, $verification_token = null) {
    // Validasi basic data
    if (!isset($data['external_id']) || !isset($data['status'])) {
        return false;
    }
    
    // Validasi token jika disediakan
    if ($verification_token !== null) {
        $received_token = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '';
        if ($received_token !== $verification_token) {
            return false;
        }
    }
    
    return true;
}

// Fungsi untuk format status order dalam bahasa Indonesia
function format_order_status($status) {
    $status_map = [
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Sudah Dibayar',
        'processing' => 'Sedang Diproses',
        'shipped' => 'Sedang Dikirim',
        'delivered' => 'Sudah Diterima',
        'cancelled' => 'Dibatalkan',
        'failed' => 'Gagal'
    ];
    
    return $status_map[$status] ?? ucfirst($status);
}

// Fungsi untuk format payment status
function format_payment_status($status) {
    $status_map = [
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Sudah Dibayar',
        'settlement' => 'Sudah Dibayar',
        'failed' => 'Pembayaran Gagal',
        'expired' => 'Pembayaran Kadaluarsa'
    ];
    
    return $status_map[$status] ?? ucfirst($status);
}
?>