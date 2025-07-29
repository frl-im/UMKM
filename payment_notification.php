<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/fungsi.php';

// Memuat file .env dari direktori saat ini
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Konfigurasi Midtrans dengan Server Key Anda
\Midtrans\Config::$isProduction = false; // Ganti ke true jika sudah live
\Midtrans\Config::$serverKey = $_ENV['MIDTRANS_SERVER_KEY']; // Ganti dengan Server Key Anda

try {
    // Buat object notifikasi dari inputan Midtrans
    $notif = new \Midtrans\Notification();
} catch (Exception $e) {
    // Tangani error jika input dari Midtrans tidak valid
    error_log('Error creating notification object: ' . $e->getMessage());
    http_response_code(400); // Bad Request
    exit('Invalid notification signature');
}

// Ambil detail notifikasi
$transaction_status = $notif->transaction_status;
$payment_type = $notif->payment_type;
$order_id = $notif->order_id;
$fraud_status = $notif->fraud_status;

// Inisialisasi status yang akan di-update ke database Anda
$status_to_update = '';

// Logika untuk menentukan status pesanan berdasarkan notifikasi
if ($transaction_status == 'capture') {
    // Khusus untuk kartu kredit
    if ($fraud_status == 'challenge') {
        // TODO: Atur status sebagai 'challenge' di database Anda
        $status_to_update = 'challenge';
    } else {
        // Pembayaran berhasil
        $status_to_update = 'paid';
    }
} else if ($transaction_status == 'settlement') {
    // Untuk semua jenis pembayaran lain (VA, QRIS, E-Wallet) yang berhasil
    $status_to_update = 'paid';
} else if ($transaction_status == 'pending') {
    // Menunggu pembayaran
    $status_to_update = 'pending';
} else if ($transaction_status == 'deny') {
    // Pembayaran ditolak
    $status_to_update = 'denied';
} else if ($transaction_status == 'expire') {
    // Waktu pembayaran habis
    $status_to_update = 'expired';
} else if ($transaction_status == 'cancel') {
    // Pembayaran dibatalkan
    $status_to_update = 'cancelled';
}

// Jika ada status yang perlu di-update, lakukan query ke database
if (!empty($status_to_update)) {
    // Ambil koneksi database dari fungsi.php
    global $koneksi; 

    // Amankan input sebelum query
    $safe_order_id = mysqli_real_escape_string($koneksi, $order_id);
    $safe_status = mysqli_real_escape_string($koneksi, $status_to_update);
    $safe_payment_type = mysqli_real_escape_string($koneksi, $payment_type);

    // Siapkan query untuk meng-update status pesanan
    // Siapkan statement yang aman
$stmt = mysqli_prepare($koneksi, "UPDATE orders SET status = ?, payment_method = ? WHERE id = ?");

// Ikat parameter ke statement
mysqli_stmt_bind_param($stmt, "sss", $status_to_update, $payment_type, $order_id);

// Jalankan statement
if(mysqli_stmt_execute($stmt)) {
    // Jika statusnya 'paid', Anda bisa menambahkan aksi lain di sini
    if($status_to_update == 'paid'){
        // Tambahkan logika bisnis Anda di sini
    }
    error_log("Order status for $order_id successfully updated to $status_to_update.");
} else {
    error_log("Failed to update order status for $order_id. Error: " . mysqli_stmt_error($stmt));
    http_response_code(500);
    exit();
} }

// Beri respons 200 OK ke Midtrans untuk menandakan notifikasi sudah diterima
http_response_code(200);
echo "Notification Handled.";
?>