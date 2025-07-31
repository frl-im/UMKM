<?php
$log_file = __DIR__ . '/webhook_log.txt';
$request_body = file_get_contents('php://input');
$timestamp = date('Y-m-d H:i:s');

$log_message = "--- Log Diterima: {$timestamp} ---\n";
$log_message .= "Request Body:\n" . $request_body . "\n\n";

// Simpan pesan log ke file
file_put_contents($log_file, $log_message, FILE_APPEND);
// webhook_xendit.php - Handler untuk callback dari Xendit
require_once 'fungsi.php';

// Log untuk debugging
error_log("Xendit webhook called at " . date('Y-m-d H:i:s'));

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Ambil raw input dari Xendit
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log data yang diterima untuk debugging
error_log("Xendit webhook data: " . json_encode($data));

// Verifikasi webhook token (opsional tapi direkomendasikan)
$webhook_token = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '';
$expected_token = 'your_webhook_verification_token_here'; // Ganti dengan token yang sebenarnya

// Uncomment jika ingin verifikasi token
// if ($webhook_token !== $expected_token) {
//     http_response_code(401);
//     exit('Unauthorized');
// }

// Pastikan data yang diperlukan ada
if (!isset($data['external_id']) || !isset($data['status'])) {
    http_response_code(400);
    exit('Invalid data');
}

$external_id = $data['external_id'];
$status = $data['status'];
$payment_method = $data['payment_method'] ?? '';
$paid_amount = $data['paid_amount'] ?? 0;

global $koneksi;

try {
    // Mulai transaction
    mysqli_begin_transaction($koneksi);
    
    switch (strtolower($status)) {
        case 'paid':
        case 'settlement':
            // Pembayaran berhasil
            
            // 1. Buat order baru dari data checkout yang tersimpan
            $stmt_check = mysqli_prepare($koneksi, "SELECT * FROM orders WHERE external_id = ?");
            mysqli_stmt_bind_param($stmt_check, "s", $external_id);
            mysqli_stmt_execute($stmt_check);
            $existing_order = mysqli_stmt_get_result($stmt_check);
            
            if (mysqli_num_rows($existing_order) == 0) {
                // Order belum ada, buat order baru
                
                // Ambil data dari session atau database temporary jika ada
                // Untuk sementara, kita ambil dari external_id pattern
                preg_match('/KREASI-\d+-(\d+)/', $external_id, $matches);
                $user_id = $matches[1] ?? null;
                
                if (!$user_id) {
                    throw new Exception("Cannot extract user_id from external_id");
                }
                
                // Ambil data keranjang user
                $cart_items = ambil_isi_keranjang($user_id);
                
                if (empty($cart_items)) {
                    throw new Exception("Cart is empty for user_id: $user_id");
                }
                
                // Hitung total
                $total_amount = 0;
                foreach ($cart_items as $item) {
                    $total_amount += $item['price'] * $item['quantity'];
                }
                
                // Insert order
                $order_id = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5($external_id), 0, 8));
                $stmt_order = mysqli_prepare($koneksi, 
                    "INSERT INTO orders (order_id, user_id, external_id, total_amount, payment_method, payment_status, order_status, created_at) 
                     VALUES (?, ?, ?, ?, ?, 'paid', 'processing', NOW())"
                );
                mysqli_stmt_bind_param($stmt_order, "sisds", $order_id, $user_id, $external_id, $total_amount, $payment_method);
                mysqli_stmt_execute($stmt_order);
                
                // Insert order items
                $stmt_item = mysqli_prepare($koneksi, 
                    "INSERT INTO order_items (order_id, product_id, quantity, price_per_item, total_price) 
                     VALUES (?, ?, ?, ?, ?)"
                );
                
                foreach ($cart_items as $item) {
                    $total_price = $item['price'] * $item['quantity'];
                    mysqli_stmt_bind_param($stmt_item, "siids", 
                        $order_id, $item['product_id'], $item['quantity'], $item['price'], $total_price
                    );
                    mysqli_stmt_execute($stmt_item);
                    
                    // Update stock
                    $stmt_stock = mysqli_prepare($koneksi, "UPDATE products SET stock = stock - ? WHERE product_id = ?");
                    mysqli_stmt_bind_param($stmt_stock, "ii", $item['quantity'], $item['product_id']);
                    mysqli_stmt_execute($stmt_stock);
                }
                
                // Clear cart
                $stmt_clear = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ?");
                mysqli_stmt_bind_param($stmt_clear, "i", $user_id);
                mysqli_stmt_execute($stmt_clear);
                
                // Log successful payment
                error_log("Order created successfully: $order_id for external_id: $external_id");
                
            } else {
    // Order sudah ada, update statusnya
    // [DIPERBAIKI] Query diubah untuk meng-update satu kolom 'status' menjadi 'processing'
    $stmt_update = mysqli_prepare($koneksi,
        "UPDATE orders SET status = 'processing', updated_at = NOW()
         WHERE external_id = ?"
    );
    
    if($stmt_update) { // Pengecekan tambahan untuk memastikan query berhasil disiapkan
        mysqli_stmt_bind_param($stmt_update, "s", $external_id);
        mysqli_stmt_execute($stmt_update);
        error_log("Status pesanan diubah menjadi 'processing' untuk external_id: $external_id");
    } else {
        error_log("Gagal menyiapkan query update di webhook untuk external_id: $external_id");
    }
}

            break;
            
        case 'expired':
        case 'failed':
            // Pembayaran gagal atau expired
            $stmt_failed = mysqli_prepare($koneksi, 
                "UPDATE orders SET payment_status = 'failed', order_status = 'cancelled', updated_at = NOW() 
                 WHERE external_id = ?"
            );
            mysqli_stmt_bind_param($stmt_failed, "s", $external_id);
            mysqli_stmt_execute($stmt_failed);
            
            error_log("Payment failed/expired for external_id: $external_id");
            break;
    }
    
    // Commit transaction
    mysqli_commit($koneksi);
    
    // Response sukses ke Xendit
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Webhook processed']);
    
} catch (Exception $e) {
    // Rollback jika error
    mysqli_rollback($koneksi);
    
    error_log("Webhook error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>