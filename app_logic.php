<?php
session_start();
require_once 'config/database.php';

// Response helper function
function jsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if user is logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse('error', 'User not authenticated');
    }
}

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    
    // ==================== CART OPERATIONS ====================
    case 'add_to_cart':
        checkAuth();
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'] ?? 1;
        $user_id = $_SESSION['user_id'];
        
        try {
            // Check if product exists and has stock
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                jsonResponse('error', 'Produk tidak ditemukan');
            }
            
            // Check if item already in cart
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $existingItem = $stmt->fetch();
            
            if ($existingItem) {
                $newQuantity = $existingItem['quantity'] + $quantity;
                if ($newQuantity > $product['stock']) {
                    jsonResponse('error', 'Jumlah melebihi stok tersedia');
                }
                
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existingItem['id']]);
            } else {
                if ($quantity > $product['stock']) {
                    jsonResponse('error', 'Jumlah melebihi stok tersedia');
                }
                
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $product_id, $quantity]);
            }
            
            jsonResponse('success', 'Produk berhasil ditambahkan ke keranjang');
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'update_cart':
        checkAuth();
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        $user_id = $_SESSION['user_id'];
        
        try {
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                jsonResponse('success', 'Item dihapus dari keranjang');
            } else {
                // Check stock
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($quantity > $product['stock']) {
                    jsonResponse('error', 'Jumlah melebihi stok tersedia');
                }
                
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $user_id, $product_id]);
                jsonResponse('success', 'Keranjang berhasil diperbarui');
            }
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'remove_from_cart':
        checkAuth();
        $product_id = (int)$_POST['product_id'];
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            jsonResponse('success', 'Item berhasil dihapus dari keranjang');
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_cart_count':
        checkAuth();
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            jsonResponse('success', 'Cart count retrieved', ['count' => $result['total'] ?? 0]);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    // ==================== CHAT OPERATIONS ====================
    case 'send_message':
        checkAuth();
        $receiver_id = (int)$_POST['receiver_id'];
        $message = trim($_POST['message']);
        $sender_id = $_SESSION['user_id'];
        
        if (empty($message)) {
            jsonResponse('error', 'Pesan tidak boleh kosong');
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, $receiver_id, $message]);
            
            jsonResponse('success', 'Pesan berhasil dikirim');
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_messages':
        checkAuth();
        $other_user_id = (int)$_GET['user_id'];
        $current_user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u.fullname as sender_name 
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?) 
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$current_user_id, $other_user_id, $other_user_id, $current_user_id]);
            $messages = $stmt->fetchAll();
            
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
                    MAX(m.created_at) as last_message_time,
                    (SELECT message FROM messages m2 
                     WHERE (m2.sender_id = ? AND m2.receiver_id = other_user_id) 
                        OR (m2.sender_id = other_user_id AND m2.receiver_id = ?)
                     ORDER BY m2.created_at DESC LIMIT 1) as last_message
                FROM messages m
                JOIN users u ON u.id = CASE 
                    WHEN m.sender_id = ? THEN m.receiver_id 
                    ELSE m.sender_id 
                END
                WHERE m.sender_id = ? OR m.receiver_id = ?
                GROUP BY other_user_id, u.fullname, u.role
                ORDER BY last_message_time DESC
            ");
            $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
            $conversations = $stmt->fetchAll();
            
            jsonResponse('success', 'Conversations retrieved', $conversations);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    // ==================== ORDER OPERATIONS ====================
    case 'create_order':
        checkAuth();
        $payment_method = $_POST['payment_method'] ?? '';
        $shipping_address = $_POST['shipping_address'] ?? '';
        $user_id = $_SESSION['user_id'];
        
        if (empty($payment_method) || empty($shipping_address)) {
            jsonResponse('error', 'Metode pembayaran dan alamat pengiriman harus diisi');
        }
        
        try {
            $pdo->beginTransaction();
            
            // Get cart items
            $stmt = $pdo->prepare("
                SELECT c.*, p.name, p.price, p.stock, p.seller_id 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? AND p.status = 'active'
            ");
            $stmt->execute([$user_id]);
            $cartItems = $stmt->fetchAll();
            
            if (empty($cartItems)) {
                jsonResponse('error', 'Keranjang kosong');
            }
            
            // Calculate total and check stock
            $total_amount = 0;
            foreach ($cartItems as $item) {
                if ($item['quantity'] > $item['stock']) {
                    throw new Exception("Stok tidak mencukupi untuk produk: " . $item['name']);
                }
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, status, payment_method, shipping_address) 
                VALUES (?, ?, 'pending', ?, ?)
            ");
            $stmt->execute([$user_id, $total_amount, $payment_method, $shipping_address]);
            $order_id = $pdo->lastInsertId();
            
            // Create order items and update stock
            foreach ($cartItems as $item) {
                // Insert order item (assuming you have order_items table)
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price, seller_id) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $order_id, 
                    $item['product_id'], 
                    $item['quantity'], 
                    $item['price'],
                    $item['seller_id']
                ]);
                
                // Update product stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $pdo->commit();
            
            jsonResponse('success', 'Pesanan berhasil dibuat', [
                'order_id' => $order_id,
                'total_amount' => $total_amount,
                'payment_method' => $payment_method
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'update_order_status':
        checkAuth();
        $order_id = (int)$_POST['order_id'];
        $status = $_POST['status'];
        $user_id = $_SESSION['user_id'];
        
        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            jsonResponse('error', 'Status tidak valid');
        }
        
        try {
            // Check if user owns the order or is admin/seller
            $stmt = $pdo->prepare("
                SELECT user_id FROM orders WHERE id = ?
            ");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
            
            if (!$order || ($order['user_id'] != $user_id && $_SESSION['role'] != 'admin')) {
                jsonResponse('error', 'Tidak memiliki akses untuk mengubah pesanan ini');
            }
            
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            
            jsonResponse('success', 'Status pesanan berhasil diperbarui');
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_orders':
        checkAuth();
        $user_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("
                SELECT o.*, COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $orders = $stmt->fetchAll();
            
            jsonResponse('success', 'Orders retrieved', $orders);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    case 'get_order_details':
        checkAuth();
        $order_id = (int)$_GET['order_id'];
        $user_id = $_SESSION['user_id'];
        
        try {
            // Get order info
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$order_id, $user_id]);
            $order = $stmt->fetch();
            
            if (!$order) {
                jsonResponse('error', 'Pesanan tidak ditemukan');
            }
            
            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
            
            $order['items'] = $order_items;
            
            jsonResponse('success', 'Order details retrieved', $order);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    // ==================== PRODUCT SEARCH ====================
    case 'search_products':
        $query = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        $min_price = $_GET['min_price'] ?? 0;
        $max_price = $_GET['max_price'] ?? 999999999;
        
        try {
            $sql = "SELECT * FROM products WHERE status = 'active'";
            $params = [];
            
            if (!empty($query)) {
                $sql .= " AND name LIKE ?";
                $params[] = "%{$query}%";
            }
            
            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            $sql .= " AND price BETWEEN ? AND ?";
            $params[] = $min_price;
            $params[] = $max_price;
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
            
            jsonResponse('success', 'Products retrieved', $products);
            
        } catch (Exception $e) {
            jsonResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        break;
    
    default:
        jsonResponse('error', 'Action tidak valid');
}
?>