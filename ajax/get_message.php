<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$other_user_id = (int)$_GET['user_id'];
$current_user_id = $_SESSION['user_id'];

if ($other_user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'User ID tidak valid']);
    exit;
}

try {
    // Mark messages as read
    $stmt = $pdo->prepare("
        UPDATE messages SET is_read = 1 
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$other_user_id, $current_user_id]);
    
    // Get messages
    $stmt = $pdo->prepare("
        SELECT m.*, u.fullname as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?) 
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$current_user_id, $other_user_id, $other_user_id, $current_user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate HTML
    $html = '';
    
    if (empty($messages)) {
        $stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
        $stmt->execute([$other_user_id]);
        $other_user = $stmt->fetch();
        
        $html = '
        <div class="empty-state">
            <i class="fas fa-comment-dots"></i>
            <h3>Mulai percakapan</h3>
            <p>Kirim pesan pertama Anda ke ' . htmlspecialchars($other_user['fullname'] ?? 'User') . '</p>
        </div>';
    } else {
        foreach ($messages as $message) {
            $isSent = ($message['sender_id'] == $current_user_id);
            $messageClass = $isSent ? 'sent' : 'received';
            $senderInitial = strtoupper(substr($message['sender_name'], 0, 1));
            
            $time = new DateTime($message['created_at']);
            $timeStr = $time->format('H:i');
            
            $html .= '
            <div class="message ' . $messageClass . '">
                <div class="message-avatar">' . $senderInitial . '</div>
                <div class="message-content">
                    <div class="message-text">' . nl2br(htmlspecialchars($message['message'])) . '</div>
                    <div class="message-time">' . $timeStr . '</div>
                </div>
            </div>';
        }
    }
    
    echo json_encode([
        'success' => true, 
        'html' => $html,
        'count' => count($messages)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>