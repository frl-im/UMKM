<?php
require_once '../fungsi.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1); 
header('Content-Type: application/json');

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

    case 'create_payment':
        $checkoutData = $_SESSION['checkout_data'] ?? null;
        if (!$checkoutData) {
            json_response('error', 'Sesi checkout tidak ditemukan.');
        }

        // Load Composer autoload
        $autoload_path = '../vendor/autoload.php';
        
        if (!file_exists($autoload_path)) {
            json_response('error', 'Composer autoload tidak ditemukan. Jalankan: composer install');
        }
        
        require_once $autoload_path;

        try {
            // Set Xendit API Key untuk Xendit SDK versi 7.0
            \Xendit\Configuration::setXenditKey('xnd_development_udQ0g57Psr0X6XRB1EpmryMle34l3opt3f3TXWzXUDFOpZ2E7A3LX7jKOkZQ4YGY');

            $external_id = 'KREASI-' . time() . '-' . $current_user_id;
            $totalAmount = (int) $checkoutData['total_amount'];
            $paymentMethod = $_POST['payment_method'] ?? '';

            if (empty($paymentMethod)) {
                json_response('error', 'Metode pembayaran harus dipilih.');
            }

            // Convert payment method to Xendit format
            $xendit_payment_methods = [];
            
            switch(strtoupper($paymentMethod)) {
                case 'BCA':
                    $xendit_payment_methods = ['BCA'];
                    break;
                case 'BNI':
                    $xendit_payment_methods = ['BNI'];
                    break;
                case 'BRI':
                    $xendit_payment_methods = ['BRI'];
                    break;
                case 'MANDIRI':
                    $xendit_payment_methods = ['MANDIRI'];
                    break;
                case 'PERMATA':
                    $xendit_payment_methods = ['PERMATA'];
                    break;
                case 'ID_SHOPEEPAY':
                    $xendit_payment_methods = ['SHOPEEPAY'];
                    break;
                case 'ID_DANA':
                    $xendit_payment_methods = ['DANA'];
                    break;
                case 'ID_OVO':
                    $xendit_payment_methods = ['OVO'];
                    break;
                default:
                    json_response('error', 'Metode pembayaran tidak didukung: ' . $paymentMethod);
            }

            // Create invoice using Xendit SDK 7.0
            $params = [ 
                'external_id' => $external_id,
                'amount' => $totalAmount,
                'currency' => 'IDR',
                'payment_methods' => $xendit_payment_methods,
                'success_redirect_url' => 'http://localhost:8080/UMKM/pesanan.php?status=sukses&external_id=' . $external_id,
                'failure_redirect_url' => 'http://localhost:8080/UMKM/pesanan.php?status=gagal&external_id=' . $external_id,
                'description' => 'Pembelian produk KreasiLokal.id'
            ];

            // Use Xendit Invoice API for version 7.0
            $apiInstance = new \Xendit\Invoice\InvoiceApi();
            $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest($params);
            
            $invoice = $apiInstance->createInvoice($createInvoiceRequest);
            
            // Save payment data for tracking
            $_SESSION['payment_data'] = [
                'external_id' => $external_id,
                'invoice_id' => $invoice['id'],
                'amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'invoice_url' => $invoice['invoice_url']
            ];

            json_response('success', 'Invoice berhasil dibuat.', [
                'invoice_url' => $invoice['invoice_url'],
                'external_id' => $external_id,
                'invoice_id' => $invoice['id']
            ]);

        } catch (\Xendit\XenditSdkException $e) {
            json_response('error', 'Xendit SDK Error: ' . $e->getMessage());
        } catch (Exception $e) {
            json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    default:
        json_response('error', 'Action tidak valid.');
}
?>