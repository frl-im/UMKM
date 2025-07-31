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

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    }
}

function login_user($data) {
    global $koneksi;
    
    if (empty($data['email']) || empty($data['password'])) {
        return false;
    }

    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $password = $data['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            start_secure_session();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            if ($user['role'] == 'penjual') {
                $_SESSION['store_name'] = $user['store_name'];
                $_SESSION['verification_status'] = $user['verification_status'];
            }
            
            return true;
        }
    }
    return false;
}

function check_login($required_role = null) {
    start_secure_session();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
        if ($required_role !== null) {
            redirect_to_login($required_role);
        }
        return false;
    }
    
    if (time() - $_SESSION['login_time'] > 7200) {
        session_unset();
        session_destroy();
        if ($required_role !== null) {
            redirect_to_login($required_role);
        }
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    
    if ($required_role && $_SESSION['user_role'] !== $required_role) {
        redirect_to_login($required_role);
        return false;
    }
    
    return true;
}

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

function ambil_pesanan_by_status($status) {
    global $koneksi;

    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    $user_id = (int)$_SESSION['user_id'];
    $status_aman = mysqli_real_escape_string($koneksi, $status);

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

    $pesanan_terstruktur = [];
    while ($item = mysqli_fetch_assoc($result)) {
        $order_id = $item['order_id'];

        if (!isset($pesanan_terstruktur[$order_id])) {
            $pesanan_terstruktur[$order_id] = [
                'order_id'      => $order_id,
                'total_amount'  => $item['total_amount'],
                'order_status'  => $item['order_status'],
                'items'         => []
            ];
        }
        $pesanan_terstruktur[$order_id]['items'][] = $item;
    }
    return array_values($pesanan_terstruktur);
}

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

function tambah_ke_keranjang($user_id, $product_id, $quantity = 1) {
    global $koneksi;
    $uid = (int)$user_id;
    $pid = (int)$product_id;
    $qty = (int)$quantity;

    $product = ambil_produk_by_id($pid);
    if (!$product) {
        return ['status' => 'error', 'message' => 'Produk tidak ditemukan.'];
    }

    $stmt_check = mysqli_prepare($koneksi, "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $pid);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $existing_item = mysqli_fetch_assoc($result_check);

    if ($existing_item) {
        $new_quantity = $existing_item['quantity'] + $qty;
        if ($new_quantity > $product['stock']) {
            return ['status' => 'error', 'message' => 'Jumlah melebihi stok yang tersedia.'];
        }

        $stmt_update = mysqli_prepare($koneksi, "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt_update, "iii", $new_quantity, $uid, $pid);
        
        if (mysqli_stmt_execute($stmt_update)) {
            return ['status' => 'success', 'message' => 'Jumlah produk di keranjang diperbarui!'];
        } else {
            return ['status' => 'error', 'message' => 'Gagal memperbarui keranjang.'];
        }
    } else {
        if ($qty > $product['stock']) {
            return ['status' => 'error', 'message' => 'Jumlah melebihi stok yang tersedia.'];
        }

        $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "iii", $uid, $pid, $qty);

        if (mysqli_stmt_execute($stmt_insert)) {
            return ['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang!'];
        } else {
            return ['status' => 'error', 'message' => 'Gagal menambahkan ke keranjang. Error: ' . mysqli_stmt_error($stmt_insert)];
        }
    }
}

function buat_pesanan_dari_keranjang($user_id, $payment_method, $shipping_address) {
    global $koneksi;
    $uid = (int)$user_id;

    mysqli_begin_transaction($koneksi);

    try {
        $cart_query = mysqli_prepare($koneksi, "SELECT c.*, p.price, p.seller_id FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        mysqli_stmt_bind_param($cart_query, "i", $uid);
        mysqli_stmt_execute($cart_query);
        $cart_items = mysqli_stmt_get_result($cart_query)->fetch_all(MYSQLI_ASSOC);

        if (empty($cart_items)) {
            throw new Exception("Keranjang kosong.");
        }

        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        $order_stmt = mysqli_prepare($koneksi, "INSERT INTO orders (user_id, total_amount, status, payment_method, shipping_address) VALUES (?, ?, 'pending', ?, ?)");
        mysqli_stmt_bind_param($order_stmt, "idss", $uid, $total_amount, $payment_method, $shipping_address);
        mysqli_stmt_execute($order_stmt);
        $order_id = mysqli_insert_id($koneksi);

        $order_item_stmt = mysqli_prepare($koneksi, "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            mysqli_stmt_bind_param($order_item_stmt, "iiiid", $order_id, $item['product_id'], $item['seller_id'], $item['quantity'], $item['price']);
            mysqli_stmt_execute($order_item_stmt);
            
            $stock_stmt = mysqli_prepare($koneksi, "UPDATE products SET stock = stock - ? WHERE id = ?");
            mysqli_stmt_bind_param($stock_stmt, "ii", $item['quantity'], $item['product_id']);
            mysqli_stmt_execute($stock_stmt);
        }
        
        $clear_cart_stmt = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($clear_cart_stmt, "i", $uid);
        mysqli_stmt_execute($clear_cart_stmt);

        mysqli_commit($koneksi);
        return ['status' => 'success', 'order_id' => $order_id];

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function ambil_daftar_percakapan($user_id, $user_role) {
    global $koneksi;
    $uid = (int)$user_id;

    $query = "";
    if ($user_role === 'pembeli') {
        $query = "SELECT id, fullname, role, store_name FROM users WHERE role = 'penjual' OR role = 'admin'";
    } elseif ($user_role === 'penjual') {
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
    mysqli_query($koneksi, "UPDATE user_addresses SET is_primary = 0 WHERE user_id = $uid");
    $stmt = mysqli_prepare($koneksi, "UPDATE user_addresses SET is_primary = 1 WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $aid, $uid);
    return mysqli_stmt_execute($stmt);
}

function ambil_produk_by_kategori($kategori) {
    global $koneksi;
    
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

    if ($stmt === false) {
        error_log("Prepare statement failed in ambil_produk_by_kategori: " . mysqli_error($koneksi));
        return [];
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

function tambah_ke_wishlist($user_id, $product_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $pid = (int)$product_id;

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

function ambil_toko_pilihan() {
    global $koneksi;
    $query = "SELECT id, store_name, store_description FROM users WHERE role = 'penjual' AND verification_status = 'verified' LIMIT 10";
    $result = mysqli_query($koneksi, $query);

    if ($result === false) {
        return [];
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function ambil_semua_voucher() {
    global $koneksi;
    $query = "SELECT * FROM vouchers WHERE is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function klaim_voucher_by_code($user_id, $voucher_code) {
    global $koneksi;
    $uid = (int)$user_id;
    $code = mysqli_real_escape_string($koneksi, $voucher_code);

    $stmt_find = mysqli_prepare($koneksi, "SELECT id FROM vouchers WHERE voucher_code = ? AND is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
    mysqli_stmt_bind_param($stmt_find, "s", $code);
    mysqli_stmt_execute($stmt_find);
    $result_find = mysqli_stmt_get_result($stmt_find);
    $voucher = mysqli_fetch_assoc($result_find);

    if (!$voucher) {
        return "Voucher tidak valid atau sudah kedaluwarsa.";
    }
    $voucher_id = $voucher['id'];

    $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $voucher_id);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) {
        return "Anda sudah pernah mengklaim voucher ini.";
    }

    $stmt_claim = mysqli_prepare($koneksi, "INSERT INTO user_vouchers (user_id, voucher_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_claim, "ii", $uid, $voucher_id);
    if (mysqli_stmt_execute($stmt_claim)) {
        return "Voucher berhasil diklaim!";
    }
    return "Terjadi kesalahan saat mengklaim voucher.";
}

function klaim_voucher_by_id($user_id, $voucher_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $vid = (int)$voucher_id;

    $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $uid, $vid);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) {
        return "Anda sudah pernah mengklaim voucher ini.";
    }

    $stmt_claim = mysqli_prepare($koneksi, "INSERT INTO user_vouchers (user_id, voucher_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_claim, "ii", $uid, $vid);
    if (mysqli_stmt_execute($stmt_claim)) {
        return "Voucher berhasil diklaim! Cek di 'Voucher Saya'.";
    }
    return "Terjadi kesalahan saat mengklaim voucher.";
}

function log_payment_activity($external_id, $status, $message = '') {
    global $koneksi;
    
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO payment_logs (external_id, status, message, created_at) VALUES (?, ?, ?, NOW())"
    );
    mysqli_stmt_bind_param($stmt, "sss", $external_id, $status, $message);
    
    return mysqli_stmt_execute($stmt);
}

function send_order_notification($user_id, $order_id, $message) {
    global $koneksi;
    
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO notifications (user_id, order_id, message, type, is_read, created_at) 
         VALUES (?, ?, ?, 'order', 0, NOW())"
    );
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $order_id, $message);
    
    return mysqli_stmt_execute($stmt);
}

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
    
    return in_array($order['order_status'], ['pending', 'confirmed']) && 
           in_array($order['payment_status'], ['pending', 'failed']);
}

function validate_xendit_callback($data, $verification_token = null) {
    if (!isset($data['external_id']) || !isset($data['status'])) {
        return false;
    }
    
    if ($verification_token !== null) {
        $received_token = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '';
        if ($received_token !== $verification_token) {
            return false;
        }
    }
    
    return true;
}

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

function ambil_produk_flash_sale() {
    global $koneksi;
    $query = "SELECT * FROM products
              WHERE status = 'active'
                AND stock > 0
                AND discount_price IS NOT NULL
                AND NOW() BETWEEN flash_sale_start_date AND flash_sale_end_date
              ORDER BY flash_sale_end_date ASC";

    $result = mysqli_query($koneksi, $query);
    $products = [];
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
    return $products;
}

function get_user_wallet_info($user_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $stmt = mysqli_prepare($koneksi, "SELECT saldo, paylater_status, paylater_limit, paylater_balance FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function activate_dev_paylater($user_id) {
    global $koneksi;
    $uid = (int)$user_id;
    $stmt = mysqli_prepare($koneksi, "UPDATE users SET paylater_status = 'active', paylater_limit = 5000000, paylater_balance = 5000000 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    return mysqli_stmt_execute($stmt);
}

function buat_pesanan_dengan_saldo($user_id, $checkout_data) {
    global $koneksi;
    $uid = (int)$user_id;
    $total_belanja = (float)$checkout_data['total_amount'];

    mysqli_begin_transaction($koneksi);

    try {
        $stmt_user = mysqli_prepare($koneksi, "SELECT saldo FROM users WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt_user, "i", $uid);
        mysqli_stmt_execute($stmt_user);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

        if (!$user || $user['saldo'] < $total_belanja) {
            throw new Exception("Saldo tidak mencukupi.");
        }

        $new_saldo = $user['saldo'] - $total_belanja;
        $stmt_update_saldo = mysqli_prepare($koneksi, "UPDATE users SET saldo = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update_saldo, "di", $new_saldo, $uid);
        mysqli_stmt_execute($stmt_update_saldo);

        $external_id = 'SALDO-' . time() . '-' . $uid;
        $order_id_text = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5($external_id), 0, 8));
        
        $stmt_order = mysqli_prepare($koneksi,
            "INSERT INTO orders (user_id, external_id, total_amount, payment_method, status, shipping_address, created_at)
             VALUES (?, ?, ?, 'saldo', 'processing', ?, NOW())"
        );
        mysqli_stmt_bind_param($stmt_order, "isds", $uid, $external_id, $total_belanja, $checkout_data['shipping_address']);
        mysqli_stmt_execute($stmt_order);
        $order_id_db = mysqli_insert_id($koneksi);

        $stmt_items = mysqli_prepare($koneksi, "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($checkout_data['items'] as $item) {
            mysqli_stmt_bind_param($stmt_items, "iiiid", $order_id_db, $item['id'], $item['seller_id'], $item['quantity'], $item['price']);
            mysqli_stmt_execute($stmt_items);

            $stmt_stock = mysqli_prepare($koneksi, "UPDATE products SET stock = stock - ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_stock, "ii", $item['quantity'], $item['id']);
            mysqli_stmt_execute($stmt_stock);
        }

        $stmt_clear_cart = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt_clear_cart, "i", $uid);
        mysqli_stmt_execute($stmt_clear_cart);
        
        mysqli_commit($koneksi);
        return ['status' => 'success', 'message' => 'Pembayaran dengan saldo berhasil!', 'external_id' => $external_id];

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function buat_pesanan_dengan_paylater($user_id, $checkout_data) {
    global $koneksi;
    $uid = (int)$user_id;
    $total_belanja = (float)$checkout_data['total_amount'];

    mysqli_begin_transaction($koneksi);

    try {
        $stmt_user = mysqli_prepare($koneksi, "SELECT paylater_status, paylater_balance FROM users WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt_user, "i", $uid);
        mysqli_stmt_execute($stmt_user);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

        if (!$user || $user['paylater_status'] !== 'active') {
            throw new Exception("Paylater Anda belum aktif.");
        }
        if ($user['paylater_balance'] < $total_belanja) {
            throw new Exception("Limit Paylater tidak mencukupi.");
        }

        $new_balance = $user['paylater_balance'] - $total_belanja;
        $stmt_update_paylater = mysqli_prepare($koneksi, "UPDATE users SET paylater_balance = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update_paylater, "di", $new_balance, $uid);
        mysqli_stmt_execute($stmt_update_paylater);

        $external_id = 'PAYLATER-' . time() . '-' . $uid;
        $stmt_order = mysqli_prepare($koneksi, "INSERT INTO orders (user_id, external_id, total_amount, payment_method, status, shipping_address, created_at) VALUES (?, ?, ?, 'paylater', 'processing', ?, NOW())");
        mysqli_stmt_bind_param($stmt_order, "isds", $uid, $external_id, $total_belanja, $checkout_data['shipping_address']);
        mysqli_stmt_execute($stmt_order);
        $order_id_db = mysqli_insert_id($koneksi);

        $stmt_items = mysqli_prepare($koneksi, "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        $stmt_stock = mysqli_prepare($koneksi, "UPDATE products SET stock = stock - ? WHERE id = ?");
        foreach ($checkout_data['items'] as $item) {
            mysqli_stmt_bind_param($stmt_items, "iiiid", $order_id_db, $item['id'], $item['seller_id'], $item['quantity'], $item['price']);
            mysqli_stmt_execute($stmt_items);
            mysqli_stmt_bind_param($stmt_stock, "ii", $item['quantity'], $item['id']);
            mysqli_stmt_execute($stmt_stock);
        }

        $stmt_clear_cart = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt_clear_cart, "i", $uid);
        mysqli_stmt_execute($stmt_clear_cart);
        
        mysqli_commit($koneksi);
        return ['status' => 'success', 'message' => 'Pembayaran dengan Paylater berhasil!', 'external_id' => $external_id];

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function ambil_detail_pesanan($order_id, $user_id) {
    global $koneksi;
    $oid = (int)$order_id;
    $uid = (int)$user_id;

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM orders WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $oid, $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($result);

    if (!$pesanan) {
        return null;
    }

    $stmt_items = mysqli_prepare($koneksi,
        "SELECT oi.quantity, oi.price, p.name, p.image_url
         FROM order_items oi
         JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?");
    mysqli_stmt_bind_param($stmt_items, "i", $oid);
    mysqli_stmt_execute($stmt_items);
    $result_items = mysqli_stmt_get_result($stmt_items);
    
    $pesanan['items'] = mysqli_fetch_all($result_items, MYSQLI_ASSOC);
    
    return $pesanan;
}
function ambil_voucher_user($user_id) {
    global $koneksi;
    $uid = (int)$user_id;

    // Query ini menggabungkan tabel user_vouchers dan vouchers
    // untuk mendapatkan detail dari setiap voucher yang sudah diklaim oleh user.
    $query = "SELECT
                v.voucher_code,
                v.discount_type,
                v.discount_value,
                v.expiry_date,
                uv.is_used,
                uv.claimed_at
              FROM user_vouchers uv
              JOIN vouchers v ON uv.voucher_id = v.id
              WHERE uv.user_id = ?
              ORDER BY uv.claimed_at DESC";

    $stmt = mysqli_prepare($koneksi, $query);
    if (!$stmt) {
        error_log("Gagal menyiapkan query di ambil_voucher_user: " . mysqli_error($koneksi));
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>