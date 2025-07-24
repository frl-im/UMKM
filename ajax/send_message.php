<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$receiver_id = (int)$_POST['receiver_id'];
$message = trim($_POST['message']);
$sender_id = $_SESSION['user_id'];

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong']);
    exit;
}

if ($receiver_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Penerima tidak valid']);
    exit;
}

try {
    // Check if receiver exists
    $stmt = $pdo->prepare("SELECT id, fullname FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch();
    
    if (!$receiver) {
        echo json_encode(['success' => false, 'message' => 'Penerima tidak ditemukan']);
        exit;
    }
    
    // Insert message
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, message, message_type, chat_type, is_read) 
        VALUES (?, ?, ?, 'text', 'user', 0)
    ");
    $stmt->execute([$sender_id, $receiver_id, $message]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pesan berhasil dikirim',
        'data' => [
            'message_id' => $pdo->lastInsertId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>