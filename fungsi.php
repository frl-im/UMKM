<?php
// --- KONEKSI KE DATABASE ---
// Pastikan nama database adalah 'kreasidb' atau sesuaikan dengan nama database Anda
$koneksi = mysqli_connect("localhost", "root", "", "kreasidb");

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}


// --- FUNGSI-FUNGSI UNTUK MENGELOLA DATA PENGGUNA ---

// Fungsi untuk Registrasi Pembeli
function registerpembeli($data) {
    global $koneksi;

    $fullname = mysqli_real_escape_string($koneksi, $data['fullname']);
    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    $query = "INSERT INTO users (fullname, email, password_hash, role) VALUES ('$fullname', '$email', '$password', 'pembeli')";
    
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

// Fungsi untuk Registrasi Penjual
function register_penjual($data) {
    global $koneksi;

    $owner_name = mysqli_real_escape_string($koneksi, $data['owner-name']);
    $store_name = mysqli_real_escape_string($koneksi, $data['store-name']);
    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);
    $phone = mysqli_real_escape_string($koneksi, $data['phone']);

    $query = "INSERT INTO users (fullname, email, password_hash, role, store_name, phone) VALUES ('$owner_name', '$email', '$password', 'penjual', '$store_name', '$phone')";

    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

// Fungsi untuk Login (Pembeli & Penjual)
function login_user($data) {
    global $koneksi;
    session_start();

    $email = mysqli_real_escape_string($koneksi, $data['email']);
    $password = $data['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            // Jika login berhasil, simpan info pengguna di session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['fullname'];
            
            // Arahkan ke halaman yang sesuai
            if ($user['role'] == 'pembeli') {
                header("Location: profil-pembeli.php");
            } else {
                header("Location: dashboard-penjual.php");
            }
            exit();
        }
    }
    // Jika gagal, kembalikan false
    return false;
}

// Fungsi untuk Upload Produk
function upload_produk($data, $files) {
    global $koneksi;
    
    // Pastikan seller_id ada di session
    if (!isset($_SESSION['user_id'])) return false;
    $seller_id = $_SESSION['user_id'];

    $name = mysqli_real_escape_string($koneksi, $data['product-name']);
    $description = mysqli_real_escape_string($koneksi, $data['product-description']);
    $price = (int)$data['product-price'];
    $category = mysqli_real_escape_string($koneksi, $data['product-category']);
    $stock = (int)$data['product-stock'];

    // Logika upload gambar
    $namaFile = $files['product-image']['name'];
    $tmpName = $files['product-image']['tmp_name'];
    $namaFileBaru = uniqid() . '_' . $namaFile;
    $folder = 'uploads/';

    if (move_uploaded_file($tmpName, $folder . $namaFileBaru)) {
        $imageUrl = $folder . $namaFileBaru;
        $query = "INSERT INTO products (seller_id, name, description, price, category, stock, image_url) VALUES ('$seller_id', '$name', '$description', '$price', '$category', '$stock', '$imageUrl')";
        
        mysqli_query($koneksi, $query);
        return mysqli_affected_rows($koneksi);
    } else {
        return false;
    }
}

?>
