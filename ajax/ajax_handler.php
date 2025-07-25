<?php
require_once '../fungsi.php'; // Menggunakan fungsi.php dengan koneksi mysqli
header('Content-Type: application/json');

// Fungsi bantuan untuk response JSON
function json_response($status, $message, $data = null) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

start_secure_session();

if (!isset($_SESSION['user_id'])) {
    json_response('error', 'login_required', ['message' => 'Anda harus login untuk melakukan aksi ini.']);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$current_user_id = $_SESSION['user_id'];
global $koneksi; // Membuat koneksi mysqli tersedia

switch ($action) {
    // ==================== FUNGSI CHAT ====================
    case 'send_message':
        $receiver_id = (int)($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if (empty($message) || $receiver_id <= 0) {
            json_response('error', 'Pesan dan penerima tidak boleh kosong.');
        }

        // Query INSERT yang benar sesuai dengan struktur tabel Anda
        $stmt = mysqli_prepare($koneksi, "INSERT INTO messages (sender_id, receiver_id, message, message_type, chat_type, is_read) VALUES (?, ?, ?, 'text', 'user', 0)");
        
        if ($stmt === false) {
            json_response('error', 'Database error: Gagal menyiapkan statement.');
        }

        mysqli_stmt_bind_param($stmt, "iis", $current_user_id, $receiver_id, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            json_response('success', 'Pesan terkirim.');
        } else {
            // Memberikan pesan error yang lebih spesifik dari database
            json_response('error', 'Gagal mengirim pesan: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        break;

    case 'get_messages':
        $other_user_id = (int)($_GET['user_id'] ?? 0);
        if ($other_user_id <= 0) {
            json_response('error', 'User tidak valid.');
        }

        // Tandai pesan sebagai sudah dibaca
        $stmt_read = mysqli_prepare($koneksi, "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
        mysqli_stmt_bind_param($stmt_read, "ii", $other_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_read);

        // Ambil percakapan
        $stmt_msg = mysqli_prepare($koneksi, "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
        mysqli_stmt_bind_param($stmt_msg, "iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_msg);
        $result = mysqli_stmt_get_result($stmt_msg);
        $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        json_response('success', 'Pesan diambil.', $messages);
        break;

    // ==================== FUNGSI KERANJANG ====================
    case 'add_to_cart':
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if ($product_id > 0) {
            $result = tambah_ke_keranjang($current_user_id, $product_id);
            json_response($result['status'], $result['message']);
        } else {
            json_response('error', 'ID Produk tidak valid.');
        }
        break;

    case 'update_cart':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if($quantity <= 0) {
            $stmt = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $current_user_id, $product_id);
            mysqli_stmt_execute($stmt);
            json_response('success', 'Item dihapus dari keranjang.');
        } else {
            // Cek stok
            $product = ambil_produk_by_id($product_id);
            if ($quantity > $product['stock']) {
                json_response('error', 'Jumlah melebihi stok tersedia.');
            }
            $stmt = mysqli_prepare($koneksi, "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            mysqli_stmt_bind_param($stmt, "iii", $quantity, $current_user_id, $product_id);
            mysqli_stmt_execute($stmt);
            json_response('success', 'Keranjang berhasil diperbarui.');
        }
        break;

    case 'remove_from_cart':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $stmt = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $current_user_id, $product_id);
        mysqli_stmt_execute($stmt);
        json_response('success', 'Item berhasil dihapus.');
        break;
        
    // ==================== FUNGSI PESANAN ====================
    case 'create_order':
        $payment_method = $_POST['payment_method'] ?? '';
        $shipping_address = $_POST['shipping_address'] ?? '';

        if (empty($payment_method) || empty($shipping_address)) {
            json_response('error', 'Metode pembayaran dan alamat pengiriman harus diisi.');
        }

        $result = buat_pesanan_dari_keranjang($current_user_id, $payment_method, $shipping_address);

        if ($result['status'] === 'success') {
            json_response('success', 'Pesanan berhasil dibuat', ['order_id' => $result['order_id']]);
        } else {
            json_response('error', $result['message'] ?? 'Gagal membuat pesanan.');
        }
        break;

    default:
        json_response('error', 'Aksi tidak valid.');
        break;
}
?>