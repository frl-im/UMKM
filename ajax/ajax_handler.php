<?php
// Memuat autoloader dari Composer dan library phpdotenv
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ... (sisa kode Anda selanjutnya)
// Baris ini untuk menampilkan error PHP secara langsung, PENTING untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan path ini benar, keluar satu folder untuk menemukan fungsi.php
require_once '../fungsi.php'; 
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
global $koneksi;

switch ($action) {
    // ==================== FUNGSI CHAT (KODE LAMA ANDA) ====================
    case 'send_message':
        $receiver_id = (int)($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if (empty($message) || $receiver_id <= 0) {
            json_response('error', 'Pesan dan penerima tidak boleh kosong.');
        }

        $stmt = mysqli_prepare($koneksi, "INSERT INTO messages (sender_id, receiver_id, message, message_type, chat_type, is_read) VALUES (?, ?, ?, 'text', 'user', 0)");
        
        if ($stmt === false) {
            json_response('error', 'Database error: Gagal menyiapkan statement.');
        }

        mysqli_stmt_bind_param($stmt, "iis", $current_user_id, $receiver_id, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            json_response('success', 'Pesan terkirim.');
        } else {
            json_response('error', 'Gagal mengirim pesan: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        break;

    case 'get_messages':
        $other_user_id = (int)($_GET['user_id'] ?? 0);
        if ($other_user_id <= 0) {
            json_response('error', 'User tidak valid.');
        }

        $stmt_read = mysqli_prepare($koneksi, "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
        mysqli_stmt_bind_param($stmt_read, "ii", $other_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_read);

        $stmt_msg = mysqli_prepare($koneksi, "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
        mysqli_stmt_bind_param($stmt_msg, "iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
        mysqli_stmt_execute($stmt_msg);
        $result = mysqli_stmt_get_result($stmt_msg);
        $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        json_response('success', 'Pesan diambil.', $messages);
        break;

    // ==================== FUNGSI KERANJANG (KODE LAMA ANDA) ====================
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
        
    // ==================== FUNGSI PESANAN & CHECKOUT (KODE LAMA ANDA + KODE BARU) ====================
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

    case 'apply_voucher':
        $voucher_code = $_POST['voucher_code'] ?? '';
        if(empty($voucher_code)) {
            json_response('error', 'Kode voucher tidak boleh kosong.');
        }

        $voucher = apply_voucher($voucher_code);
        if ($voucher) {
            $_SESSION['applied_voucher'] = $voucher;
            json_response('success', 'Voucher berhasil diterapkan!', $voucher);
        } else {
            unset($_SESSION['applied_voucher']);
            json_response('error', 'Voucher tidak valid atau sudah kedaluwarsa.');
        }
        break;

    case 'prepare_checkout':
        $_SESSION['checkout_data'] = [
            'user_id'           => $current_user_id,
            'total_amount'      => (float)($_POST['total_amount'] ?? 0),
            'shipping_cost'     => (float)($_POST['shipping_cost'] ?? 0),
            'discount_amount'   => (float)($_POST['discount_amount'] ?? 0),
            'shipping_address'  => $_POST['shipping_address'] ?? '',
            'recipient_name'    => $_POST['recipient_name'] ?? '',
            'items'             => ambil_isi_keranjang($current_user_id) 
        ];

        if (empty($_SESSION['checkout_data']['items'])) {
            json_response('error', 'Keranjang Anda kosong.');
        }
        json_response('success', 'Data checkout siap.');
        break;

    // ==================== FUNGSI PEMBAYARAN MIDTRANS (KODE BARU YANG SUDAH DIPERBAIKI) ====================
    case 'create_payment':

        $checkoutData = $_SESSION['checkout_data'] ?? null;
        if (!$checkoutData) {
            json_response('error', 'Sesi checkout tidak ditemukan. Ulangi dari keranjang.');
        }

        // =======================================================================
        // PERHATIAN: PASTIKAN ANDA MENGGANTI SERVER KEY DI BAWAH INI!
        // INI ADALAH PENYEBAB PALING UMUM DARI ERROR "NOT VALID JSON"
        // =======================================================================
        \Midtrans\Config::$serverKey = $_ENV['MIDTRANS_SERVER_KEY'];
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $paymentType = $_POST['payment_type'];
        $orderId = 'KREASI-' . time();
        $totalAmount = (int) $checkoutData['total_amount'];

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $_SESSION['user_name'] ?? 'Guest',
                'email' => $_SESSION['user_email'] ?? 'guest@example.com',
            ]
        ];
        
        // Logika untuk VA Bank
        if (strpos($paymentType, '_va') !== false) {
             if ($paymentType === 'mandiri_va') {
                 $params['payment_type'] = 'echannel';
                 $params['echannel'] = ['bill_info1' => 'Pembayaran untuk:', 'bill_info2' => 'Order ' . $orderId];
             } else {
                 $params['payment_type'] = 'bank_transfer';
                 $params['bank_transfer'] = ['bank' => str_replace('_va', '', $paymentType)];
             }
        } else {
             $params['payment_type'] = $paymentType;
        }
        
        if ($paymentType === 'shopeepay') {
            $params['shopeepay'] = ['callback_url' => 'https://example.com/status?order_id=' . $orderId];
        }
        
        try {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO orders (id, user_id, total_amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            mysqli_stmt_bind_param($stmt, "sid", $orderId, $current_user_id, $totalAmount);
            mysqli_stmt_execute($stmt);

            $chargeResponse = \Midtrans\CoreApi::charge($params);

            $frontendData = [];
            if ($chargeResponse->payment_type == 'qris') {
                $frontendData = ['type' => 'qris', 'title' => 'Pembayaran via QRIS', 'qr_string' => $chargeResponse->qr_string, 'expiry_time' => date('d M Y H:i:s', strtotime($chargeResponse->expiry_time))];
            } elseif (isset($chargeResponse->va_numbers)) {
                $frontendData = ['type' => 'va', 'title' => 'Pembayaran ' . strtoupper($chargeResponse->va_numbers[0]->bank) . ' VA', 'va_number' => $chargeResponse->va_numbers[0]->va_number, 'total' => format_price($chargeResponse->gross_amount), 'expiry_time' => date('d M Y H:i:s', strtotime($chargeResponse->expiry_time))];
            } elseif ($chargeResponse->payment_type == 'echannel') {
                $frontendData = ['type' => 'va', 'title' => 'Pembayaran Mandiri Bill', 'va_number' => $chargeResponse->biller_code . ' ' . $chargeResponse->bill_key, 'total' => format_price($chargeResponse->gross_amount), 'expiry_time' => date('d M Y H:i:s', strtotime($chargeResponse->expiry_time))];
            } elseif ($chargeResponse->payment_type == 'gopay' || $chargeResponse->payment_type == 'shopeepay') {
                 $ewalletName = ($chargeResponse->payment_type == 'gopay') ? 'GoPay' : 'ShopeePay';
                 $frontendData = ['type' => 'ewallet', 'name' => $ewalletName, 'title' => 'Pembayaran via ' . $ewalletName, 'deeplink_url' => $chargeResponse->actions[0]->url];
            }
            json_response('success', 'Detail pembayaran berhasil dibuat.', $frontendData);
        } catch (Exception $e) {
            json_response('error', 'Gagal memproses pembayaran: ' . $e->getMessage(), ['raw_error' => $e->__toString()]);
        }
        break;

    default:
        json_response('error', 'Aksi tidak valid.');
        break;
}
?>