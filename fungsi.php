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


// FUNGSI BARU UNTUK ALUR VERIFIKASI

/**
 * Memproses dan menyimpan data verifikasi diri penjual.
 * @return bool - True jika berhasil, false jika gagal.
 */
// Contoh konseptual di dalam fungsi proses_verifikasi_data_diri() di fungsi.php

function proses_verifikasi_data_diri($data, $files) {
    // ... (kode yang sudah ada untuk memproses NIK, nama, dan KTP)

    // 1. Terima data gambar wajah dari form
    $face_image_base64 = $data['face_image_data'];
    
    // 2. Simpan foto wajah ke file (opsional tapi direkomendasikan)
    // Anda perlu mengubah data base64 kembali menjadi file gambar.
    // list($type, $face_image_base64) = explode(';', $face_image_base64);
    // list(, $face_image_base64)      = explode(',', $face_image_base64);
    // $face_image_data_decoded = base64_decode($face_image_base64);
    // $face_image_filename = 'uploads/faces/' . uniqid() . '.jpg';
    // file_put_contents($face_image_filename, $face_image_data_decoded);

    // 3. KIRIM DATA KE API VERIFIKASI
    // Ini adalah bagian di mana Anda berinteraksi dengan API pihak ketiga.
    // $api_response = call_face_verification_api(
    //     'path/to/ktp_image.jpg',  // Path file KTP yang diupload
    //     $face_image_filename     // Path file foto wajah yang baru diambil
    // );

    // 4. Proses respons dari API
    // if ($api_response['is_match'] == true && $api_response['liveness'] == 'passed') {
    //      // Jika cocok dan wajahnya asli, update status verifikasi ke 1
    //      // UPDATE users SET verification_status = 1 WHERE id = ...
    //      return true;
    // } else {
    //      // Jika tidak cocok, kembalikan false
    //      return false;
    // }

    // Untuk sekarang, kita anggap berhasil dan langsung update status.
    // Hapus bagian ini jika Anda sudah mengintegrasikan API asli.
    global $koneksi;
    $user_id = $_SESSION['user_id'];
    mysqli_query($koneksi, "UPDATE users SET verification_status = 1 WHERE id = $user_id");
    return true; // Asumsi berhasil untuk development
}

/**
 * Memproses dan menyimpan informasi toko penjual.
 * @return bool - True jika berhasil, false jika gagal.
 */
function proses_informasi_toko($data) {
    global $koneksi;
    $user_id = $_SESSION['user_id'];

    // Sanitasi data
    $store_name = mysqli_real_escape_string($koneksi, $data['store_name']);
    $store_address = mysqli_real_escape_string($koneksi, $data['store_address']);
    
    // Update data di database
    $stmt = mysqli_prepare($koneksi, "UPDATE users SET store_name = ?, store_address = ?, verification_status = 2 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $store_name, $store_address, $user_id);
    
    return mysqli_stmt_execute($stmt);
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
        return ['status' => false, 'message' => 'Akses ditolak. Hanya penjual yang dapat mengunggah produk.'];
    }
    
    $seller_id = $_SESSION['user_id'];

    // Validasi input
    if (empty($data['product-name']) || empty($data['product-description']) || 
        empty($data['product-price']) || empty($data['product-category']) || 
        empty($data['product-stock'])) {
        return ['status' => false, 'message' => 'Semua field harus diisi!'];
    }

    // Sanitasi dan validasi data
    $name = mysqli_real_escape_string($koneksi, trim($data['product-name']));
    $description = mysqli_real_escape_string($koneksi, trim($data['product-description']));
    $price = filter_var($data['product-price'], FILTER_VALIDATE_FLOAT);
    $category = mysqli_real_escape_string($koneksi, $data['product-category']);
    $stock = filter_var($data['product-stock'], FILTER_VALIDATE_INT);

    // Validasi harga dan stok
    if ($price === false || $price <= 0) {
        return ['status' => false, 'message' => 'Harga harus berupa angka positif!'];
    }
    
    if ($stock === false || $stock < 0) {
        return ['status' => false, 'message' => 'Stok harus berupa angka non-negatif!'];
    }

    // Validasi kategori
    $allowed_categories = ['kerajinan', 'makanan', 'fashion', 'lainnya'];
    if (!in_array($category, $allowed_categories)) {
        return ['status' => false, 'message' => 'Kategori tidak valid!'];
    }

    // Validasi file upload
    if (!isset($files['product-image']) || $files['product-image']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE).',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
        ];
        
        $error_code = $files['product-image']['error'] ?? UPLOAD_ERR_NO_FILE;
        $message = $error_messages[$error_code] ?? 'Terjadi kesalahan saat upload file.';
        
        return ['status' => false, 'message' => $message];
    }

    // Validasi tipe file menggunakan finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $files['product-image']['tmp_name']);
    finfo_close($finfo);
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime_type, $allowed_types)) {
        return ['status' => false, 'message' => 'Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WebP.'];
    }

    // Validasi ukuran file (maksimal 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($files['product-image']['size'] > $max_size) {
        return ['status' => false, 'message' => 'Ukuran file maksimal 5MB.'];
    }

    // Logika upload gambar
    $namaFile = $files['product-image']['name'];
    $tmpName = $files['product-image']['tmp_name'];
    $extension = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
    $namaFileBaru = uniqid() . '_' . time() . '.' . $extension;
    $folder = 'uploads/products/';

    // Buat folder jika belum ada
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }

    // Cek apakah nama produk sudah ada untuk seller ini
    $check_name = "SELECT name FROM products WHERE seller_id = '$seller_id' AND name = '$name' AND status = 'active'";
    $result = mysqli_query($koneksi, $check_name);
    if (mysqli_num_rows($result) > 0) {
        return ['status' => false, 'message' => 'Nama produk sudah ada di toko Anda!'];
    }

    if (move_uploaded_file($tmpName, $folder . $namaFileBaru)) {
        $imageUrl = $folder . $namaFileBaru;
        
        // Gunakan prepared statement untuk keamanan
        $stmt = mysqli_prepare($koneksi, "INSERT INTO products (seller_id, name, description, price, category, stock, image_url, created_at, updated_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 'active')");
        mysqli_stmt_bind_param($stmt, "issdsis", $seller_id, $name, $description, $price, $category, $stock, $imageUrl);
        
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
    // Update status penjual menjadi sepenuhnya aktif setelah upload produk pertama
    mysqli_query($koneksi, "UPDATE users SET verification_status = 3 WHERE id = $seller_id AND verification_status < 3");
    
    mysqli_stmt_close($stmt);
    return ['status' => true, 'message' => 'Produk berhasil ditambahkan!', 'product_id' => mysqli_insert_id($koneksi)];
}
        if ($result) {
            mysqli_stmt_close($stmt);
            return ['status' => true, 'message' => 'Produk berhasil ditambahkan!', 'product_id' => mysqli_insert_id($koneksi)];
        } else {
            mysqli_stmt_close($stmt);
            // Hapus file jika query gagal
            unlink($folder . $namaFileBaru);
            return ['status' => false, 'message' => 'Gagal menyimpan data produk ke database.'];
        }
    } else {
        return ['status' => false, 'message' => 'Gagal mengunggah file gambar.'];
    }
}

// Fungsi untuk mendapatkan data user berdasarkan ID
function get_user_by_id($user_id) {
    global $koneksi;
    
    $user_id = (int)$user_id;
    // Query diperbaiki dengan menghapus "AND status = 'active'"
    $query = "SELECT * FROM users WHERE id = $user_id";
    
    $result = mysqli_query($koneksi, $query);
    
    // --- PERBAIKAN UTAMA ---
    // Cek apakah query gagal. Jika ya, kembalikan false agar tidak error.
    if ($result === false) {
        // Baris ini akan mencegah fatal error di mysqli_num_rows()
        return false; 
    }
    
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

// Fungsi untuk sanitasi output (mencegah XSS)
function safe_output($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk format harga
function format_price($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

?>