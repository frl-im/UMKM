<?php
session_start();
header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php'; // Sesuaikan path

$user_id = $_SESSION['user_id'];
$response_data = [];

try {
    // 1. Ambil data user (fullname, email)
    $stmt_user = $pdo->prepare("SELECT fullname, email FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $response_data['user'] = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // 2. Ambil jumlah pesanan per status
    // (Asumsi Anda punya tabel 'orders' dengan kolom 'status' dan 'user_id')
    $statuses = ['belum_bayar', 'dikemas', 'dikirim', 'penilaian'];
    $order_counts = [];
    foreach ($statuses as $status) {
        $stmt_orders = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = ?");
        $stmt_orders->execute([$user_id, $status]);
        $result = $stmt_orders->fetch(PDO::FETCH_ASSOC);
        $order_counts[$status] = $result['count'];
    }
    $response_data['order_counts'] = $order_counts;
    
    // (Opsional) 3. Ambil data lain seperti saldo, dll.
    // $stmt_saldo = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    // ...
    
    echo json_encode(['status' => 'success', 'data' => $response_data]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database query failed.']);
}
?>