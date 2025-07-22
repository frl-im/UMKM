<?php
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

// --- FUNGSI-FUNGSI UNTUK MENGELOLA DATA PENGGUNA ---

// Fungsi untuk Registrasi Pembeli
function registerpembeli($data) {
    global $koneksi;

    // Validasi input
    if (empty($data['fullname']) || empty($data['email']) || empty($data['password'])) {
        return false;
    }

    $fullname = mysqli_real_escape_string($koneksi, $data['fullname']);
    $email = mysqli_real_escape_string($koneksi, $data['email']);
    
    // Cek apakah email sudah terdaftar
    $check_email = "SELECT email FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $check_email);
    if (mysqli_num_rows($result) > 0) {
        return -1; // Email sudah terdaftar
    }
    
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    // Perbaikan: Tambahkan kolom id (auto_increment) dan sesuaikan dengan struktur tabel
    $query = "INSERT INTO users (fullname, email, password_hash, role) VALUES ('$fullname', '$email', '$password', 'pembeli')";
    
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

// Fungsi untuk Registrasi Penjual
function register_penjual($data) {
    global $koneksi;

    // Validasi input
    if (empty($data['owner-name']) || empty($data['store-name']) || empty($data['email']) || 
        empty($data['password']) || empty($data['phone'])) {
        return false;
    }

    $owner_name = mysqli_real_escape_string($koneksi, $data['owner-name']);
    $store_name = mysqli_real_escape_string($koneksi, $data['store-name']);
    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $phone = mysqli_real_escape_string($koneksi, $data['phone']);
    
    // Cek apakah email sudah terdaftar
    $check_email = "SELECT email FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $check_email);
    if (mysqli_num_rows($result) > 0) {
        return -1; // Email sudah terdaftar
    }
    
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    // Perbaikan: Sesuaikan dengan struktur tabel yang benar
    $query = "INSERT INTO users (fullname, email, password_hash, role, store_name, phone) VALUES ('$owner_name', '$email', '$password', 'penjual', '$store_name', '$phone')";

    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

// Fungsi untuk Login (Pembeli & Penjual) - DIPERBAIKI
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
            // Mulai session jika belum dimulai
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Simpan info pengguna di session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['fullname'];
            
            // Untuk penjual, simpan juga nama toko
            if ($user['role'] == 'penjual') {
                $_SESSION['store_name'] = $user['store_name'];
            }
            
            return true; // Login berhasil
        }
    }
    return false; // Login gagal
}

// Fungsi untuk Upload Produk - DIPERBAIKI
function upload_produk($data, $files) {
    global $koneksi;
    
    // Mulai session jika belum dimulai
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Pastikan seller_id ada di session dan user adalah penjual
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penjual') {
        return false;
    }
    
    $seller_id = $_SESSION['user_id'];

    // Validasi input
    if (empty($data['product-name']) || empty($data['product-description']) || 
        empty($data['product-price']) || empty($data['product-category']) || 
        empty($data['product-stock'])) {
        return false;
    }

    $name = mysqli_real_escape_string($koneksi, $data['product-name']);
    $description = mysqli_real_escape_string($koneksi, $data['product-description']);
    $price = (float)$data['product-price']; // Gunakan float untuk harga
    $category = mysqli_real_escape_string($koneksi, $data['product-category']);
    $stock = (int)$data['product-stock'];

    // Validasi file upload
    if (!isset($files['product-image']) || $files['product-image']['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = $files['product-image']['type'];
    if (!in_array($file_type, $allowed_types)) {
        return false;
    }

    // Validasi ukuran file (maksimal 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($files['product-image']['size'] > $max_size) {
        return false;
    }

    // Logika upload gambar
    $namaFile = $files['product-image']['name'];
    $tmpName = $files['product-image']['tmp_name'];
    $extension = pathinfo($namaFile, PATHINFO_EXTENSION);
    $namaFileBaru = uniqid() . '_' . time() . '.' . $extension;
    $folder = 'uploads/';

    // Buat folder jika belum ada
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    if (move_uploaded_file($tmpName, $folder . $namaFileBaru)) {
        $imageUrl = $folder . $namaFileBaru;
        
        // Perbaikan: Tambahkan created_at dan status produk
        $query = "INSERT INTO products (seller_id, name, description, price, category, stock, image_url, created_at, status) 
                  VALUES ('$seller_id', '$name', '$description', '$price', '$category', '$stock', '$imageUrl', NOW(), 'active')";
        
        $result = mysqli_query($koneksi, $query);
        if ($result) {
            return mysqli_affected_rows($koneksi);
        } else {
            // Hapus file jika query gagal
            unlink($folder . $namaFileBaru);
            return false;
        }
    } else {
        return false;
    }
}

// Fungsi untuk mendapatkan data user berdasarkan ID
function get_user_by_id($user_id) {
    global $koneksi;
    
    $user_id = (int)$user_id;
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) === 1) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

// Fungsi untuk mendapatkan produk berdasarkan seller_id
function get_products_by_seller($seller_id) {
    global $koneksi;
    
    $seller_id = (int)$seller_id;
    $query = "SELECT * FROM products WHERE seller_id = $seller_id ORDER BY created_at DESC";
    return query($query);
}

// Fungsi untuk cek session dan redirect jika tidak login
function check_login($required_role = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        if ($required_role === 'penjual') {
            header("Location: loginpenjual.php");
        } else {
            header("Location: loginpembeli.php");
        }
        exit();
    }
    
    // Jika role tertentu diperlukan
    if ($required_role && $_SESSION['user_role'] !== $required_role) {
        if ($required_role === 'penjual') {
            header("Location: loginpenjual.php");
        } else {
            header("Location: loginpembeli.php");
        }
        exit();
    }
    
    return true;
}

// Fungsi untuk logout
function logout_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_unset();
    session_destroy();
    return true;
}

?>