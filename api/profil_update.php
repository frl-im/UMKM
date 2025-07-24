<?php
session_start();
header('Content-Type: application/json'); // Wajib: memberitahu browser bahwa responsnya adalah JSON

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

require_once '../config/database.php'; // Sesuaikan path ke file database Anda

// Validasi data yang masuk
$fullname = $_POST['fullname'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($fullname) || empty($phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama lengkap dan nomor telepon tidak boleh kosong.']);
    exit();
}

try {
    // Update data di database
    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
    $stmt->execute([$fullname, $phone, $_SESSION['user_id']]);

    // Kirim respons sukses
    echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui!']);

} catch (PDOException $e) {
    // Kirim respons error jika query database gagal
    // Sebaiknya jangan tampilkan $e->getMessage() di production
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil di database.']);
}