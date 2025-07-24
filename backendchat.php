<?php
session_start();
require_once 'config/database.php';

// Response helper function
function jsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Check if user is logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse('error', 'User not authenticated');
    }
}

// Get user info helper
function getUserInfo($user_id, $pdo) {
    $stmt = $pdo->prepare("SELECT id, fullname, email, role, store_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if user is customer service
function isCustomerService($user_id, $pdo) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$user_id]);
    return $stmt->fetch() !== false;
}

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    
    // ==================== ENHANCED CHAT OPERATIONS ====================
    
    case 'send_message':
        checkAuth();
        $receiver_id = (int)$_POST['receiver_id'];
        $message = trim($_POST['message']);
        $message_type = $_POST['message_type'] ?? 'text'; // text, image, file
        $chat_type = $_POST['chat_type'] ?? 'user'; // user, support
        $sender_id = $_SESSION['user_id'];
        
        if (empty($message) && $message_type === 'text') {
            jsonResponse('error', 'Pesan tidak boleh kosong');
        }
        
        // Validate receiver exists
        $receiverInfo = getUserInfo($receiver_id, $pdo);
        if (!$receiverInfo) {
            jsonResponse('error', 'Penerima pesan tidak ditemukan');
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, receiver_id, message, message_type, chat_type, is_read) 
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$sender_id, $receiver_id, $message, $message_type, $chat_type]);
            
            $message_id = $pdo->lastInsertId();
            
            // Get sender info for response
            $senderInfo = getUserInfo($sender_id, $pdo);
            
            jsonResponse('success', 'Pesan berhasil dikirim', [
                'message_id' => $message_id,
                'sender_name' => $senderInfo['fullname'],
                'sender_role' => $senderInfo['role'],
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_messages':
        checkAuth();
        $other_user_id = (int)$_GET['user_id'];
        $current_user_id = $_SESSION['user_id'];
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        
        try {
            // Mark messages as read
            $stmt = $pdo->prepare("
                UPDATE messages SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$other_user_id, $current_user_id]);
            
            // Get messages with user info
            $stmt = $pdo->prepare("
                SELECT m.*, 
                       u.fullname as sender_name, 
                       u.role as sender_role,
                       u.store_name as sender_store
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?) 
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$current_user_id, $other_user_id, $other_user_id, $current_user_id, $limit, $offset]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Reverse array to show oldest first
            $messages = array_reverse($messages);
            
            jsonResponse('success', 'Messages retrieved', $messages);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_conversations':
        checkAuth();
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT 
                    CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id 
                        ELSE m.sender_id 
                    END as other_user_id,
                    u.fullname as other_user_name,
                    u.role as other_user_role,
                    u.store_name as other_user_store,
                    MAX(m.created_at) as last_message_time,
                    (SELECT message FROM messages m2 
                     WHERE (m2.sender_id = ? AND m2.receiver_id = other_user_id) 
                        OR (m2.sender_id = other_user_id AND m2.receiver_id = ?)
                     ORDER BY m2.created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM messages m3 
                     WHERE m3.sender_id = other_user_id AND m3.receiver_id = ? AND m3.is_read = 0) as unread_count
                FROM messages m
                JOIN users u ON u.id = CASE 
                    WHEN m.sender_id = ? THEN m.receiver_id 
                    ELSE m.sender_id 
                END
                WHERE m.sender_id = ? OR m.receiver_id = ?
                GROUP BY other_user_id, u.fullname, u.role, u.store_name
                ORDER BY last_message_time DESC
            ");
            $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            jsonResponse('success', 'Conversations retrieved', $conversations);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_unread_count':
        checkAuth();
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM messages WHERE receiver_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            jsonResponse('success', 'Unread count retrieved', ['count' => $result['total'] ?? 0]);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    // ==================== CUSTOMER SERVICE OPERATIONS ====================
    
    case 'get_customer_service':
        checkAuth();
        
        try {
            // Get available customer service agents (admin role)
            $stmt = $pdo->prepare("
                SELECT id, fullname, email, 'Available' as status
                FROM users 
                WHERE role = 'admin' 
                ORDER BY fullname ASC
            ");
            $stmt->execute();
            $cs_agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cs_agents)) {
                // Create default CS if none exists
                jsonResponse('success', 'Customer service retrieved', [
                    [
                        'id' => 0,
                        'fullname' => 'Customer Service',
                        'email' => 'cs@kreasilokal.id',
                        'status' => 'Available'
                    ]
                ]);
            } else {
                jsonResponse('success', 'Customer service retrieved', $cs_agents);
            }
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'create_support_ticket':
        checkAuth();
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $category = $_POST['category'] ?? 'general'; // general, order, product, technical
        $user_id = $_SESSION['user_id'];
        
        if (empty($subject) || empty($message)) {
            jsonResponse('error', 'Subject dan pesan harus diisi');
        }
        
        try {
            $pdo->beginTransaction();
            
            // Create support ticket
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (user_id, subject, category, status, priority) 
                VALUES (?, ?, ?, 'open', 'medium')
            ");
            $stmt->execute([$user_id, $subject, $category]);
            $ticket_id = $pdo->lastInsertId();
            
            // Get or create CS agent
            $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $cs_agent = $stmt->fetch();
            
            if (!$cs_agent) {
                // Create default CS agent if none exists
                $stmt = $pdo->prepare("
                    INSERT INTO users (fullname, email, password, role) 
                    VALUES ('Customer Service', 'cs@kreasilokal.id', ?, 'admin')
                ");
                $stmt->execute([password_hash('cs123456', PASSWORD_DEFAULT)]);
                $cs_id = $pdo->lastInsertId();
            } else {
                $cs_id = $cs_agent['id'];
            }
            
            // Create initial message
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, receiver_id, message, message_type, chat_type, ticket_id) 
                VALUES (?, ?, ?, 'text', 'support', ?)
            ");
            $stmt->execute([$user_id, $cs_id, $message, $ticket_id]);
            
            $pdo->commit();
            
            jsonResponse('success', 'Tiket support berhasil dibuat', [
                'ticket_id' => $ticket_id,
                'cs_id' => $cs_id,
                'subject' => $subject
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_support_tickets':
        checkAuth();
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("
                SELECT st.*, 
                       (SELECT COUNT(*) FROM messages m 
                        WHERE m.ticket_id = st.id) as message_count,
                       (SELECT message FROM messages m 
                        WHERE m.ticket_id = st.id 
                        ORDER BY m.created_at DESC LIMIT 1) as last_message
                FROM support_tickets st
                WHERE st.user_id = ?
                ORDER BY st.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            jsonResponse('success', 'Support tickets retrieved', $tickets);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    // ==================== SELLER-BUYER COMMUNICATION ====================
    
    case 'contact_seller':
        checkAuth();
        $seller_id = (int)$_POST['seller_id'];
        $product_id = (int)($_POST['product_id'] ?? 0);
        $message = trim($_POST['message']);
        $buyer_id = $_SESSION['user_id'];
        
        if (empty($message)) {
            jsonResponse('error', 'Pesan tidak boleh kosong');
        }
        
        // Validate seller exists
        $sellerInfo = getUserInfo($seller_id, $pdo);
        if (!$sellerInfo || $sellerInfo['role'] !== 'penjual') {
            jsonResponse('error', 'Penjual tidak ditemukan');
        }
        
        try {
            // Add product context if provided
            $finalMessage = $message;
            if ($product_id > 0) {
                $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ? AND seller_id = ?");
                $stmt->execute([$product_id, $seller_id]);
                $product = $stmt->fetch();
                if ($product) {
                    $finalMessage = "[Mengenai produk: " . $product['name'] . "]\n\n" . $message;
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, receiver_id, message, message_type, chat_type, product_id) 
                VALUES (?, ?, ?, 'text', 'user', ?)
            ");
            $stmt->execute([$buyer_id, $seller_id, $finalMessage, $product_id]);
            
            jsonResponse('success', 'Pesan berhasil dikirim ke penjual');
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_sellers':
        checkAuth();
        $search = $_GET['search'] ?? '';
        
        try {
            $sql = "SELECT id, fullname, store_name, email FROM users WHERE role = 'penjual'";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (fullname LIKE ? OR store_name LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $sql .= " ORDER BY store_name ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            jsonResponse('success', 'Sellers retrieved', $sellers);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'delete_message':
        checkAuth();
        $message_id = (int)$_POST['message_id'];
        $user_id = $_SESSION['user_id'];
        
        try {
            // Check if user owns the message
            $stmt = $pdo->prepare("SELECT sender_id FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            $message = $stmt->fetch();
            
            if (!$message || $message['sender_id'] != $user_id) {
                jsonResponse('error', 'Tidak memiliki akses untuk menghapus pesan ini');
            }
            
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            
            jsonResponse('success', 'Pesan berhasil dihapus');
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'mark_as_read':
        checkAuth();
        $sender_id = (int)$_POST['sender_id'];
        $receiver_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("
                UPDATE messages SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmt->execute([$sender_id, $receiver_id]);
            
            jsonResponse('success', 'Pesan ditandai sebagai sudah dibaca');
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    // ==================== REAL-TIME CHAT POLLING ====================
    
    case 'poll_new_messages':
        checkAuth();
        $last_check = $_GET['last_check'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u.fullname as sender_name, u.role as sender_role
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.receiver_id = ? AND m.created_at > ? AND m.is_read = 0
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$user_id, $last_check]);
            $new_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            jsonResponse('success', 'New messages retrieved', [
                'messages' => $new_messages,
                'count' => count($new_messages),
                'last_check' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    default:
        jsonResponse('error', 'Action tidak valid');
}
?>