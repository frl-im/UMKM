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

 case 'pay_with_saldo':
        $checkoutData = $_SESSION['checkout_data'] ?? null;
        if (!$checkoutData || empty($checkoutData['items'])) {
            json_response('error', 'Sesi checkout tidak valid atau keranjang kosong.');
        }

        $result = buat_pesanan_dengan_saldo($current_user_id, $checkoutData);

        if ($result['status'] === 'success') {
            unset($_SESSION['checkout_data']); // Hapus data checkout setelah berhasil
            json_response('success', $result['message'], ['external_id' => $result['external_id']]);
        } else {
            json_response('error', $result['message'] ?? 'Gagal memproses pesanan.');
        }
        break;

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
        json_response('error', 'Sesi checkout tidak ditemukan atau telah kedaluwarsa. Silakan ulangi dari keranjang.');
    }

    $autoload_path = '../vendor/autoload.php';
    if (!file_exists($autoload_path)) {
        json_response('error', 'Composer autoload tidak ditemukan. Jalankan: composer install');
    }
    require_once $autoload_path;

    try {
        \Xendit\Configuration::setXenditKey('xnd_development_udQ0g57Psr0X6XRB1EpmryMle34l3opt3f3TXWzXUDFOpZ2E7A3LX7jKOkZQ4YGY');

        $external_id = 'KREASI-' . time() . '-' . $current_user_id;
        $totalAmount = (int) $checkoutData['total_amount'];
        $paymentMethod = $_POST['payment_method'] ?? '';

        if (empty($paymentMethod)) {
            json_response('error', 'Metode pembayaran harus dipilih.');
        }

        $xendit_payment_methods = [];
        switch(strtoupper($paymentMethod)) {
            case 'BCA': $xendit_payment_methods = ['BCA']; break;
            case 'BNI': $xendit_payment_methods = ['BNI']; break;
            case 'BRI': $xendit_payment_methods = ['BRI']; break;
            case 'MANDIRI': $xendit_payment_methods = ['MANDIRI']; break;
            case 'PERMATA': $xendit_payment_methods = ['PERMATA']; break;
            case 'ID_SHOPEEPAY': $xendit_payment_methods = ['SHOPEEPAY']; break;
            case 'ID_DANA': $xendit_payment_methods = ['DANA']; break;
            case 'ID_OVO': $xendit_payment_methods = ['OVO']; break;
            default: json_response('error', 'Metode pembayaran tidak didukung: ' . $paymentMethod);
        }

        mysqli_begin_transaction($koneksi);
        
        try {
            // [DIPERBAIKI] Menyimpan ke kolom yang benar: user_id, external_id, total_amount, status, dll.
            // Catatan: `id` akan terisi otomatis (auto-increment) dan tidak perlu dimasukkan dalam query INSERT.
            $stmt_temp_order = mysqli_prepare($koneksi, 
                "INSERT INTO orders (user_id, external_id, total_amount, status, payment_method, shipping_address, created_at) 
                 VALUES (?, ?, ?, 'pending', ?, ?, NOW())"
            );
            
            if ($stmt_temp_order === false) {
                throw new Exception('Gagal menyiapkan statement SQL untuk tabel orders: ' . mysqli_error($koneksi));
            }

            $shipping_address = $checkoutData['shipping_address'];
            // [DIPERBAIKI] Melakukan bind parameter dengan tipe data yang benar.
            mysqli_stmt_bind_param($stmt_temp_order, "isdss", 
                $current_user_id, $external_id, $totalAmount, $paymentMethod, $shipping_address
            );
            mysqli_stmt_execute($stmt_temp_order);
            $order_id = mysqli_insert_id($koneksi); // Ambil ID dari order yang baru saja dibuat.
            
            // [DIPERBAIKI] Menyimpan ke kolom yang benar: order_id, product_id, seller_id, quantity, price.
            $stmt_temp_item = mysqli_prepare($koneksi, 
                "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) 
                 VALUES (?, ?, ?, ?, ?)"
            );
             if ($stmt_temp_item === false) {
                throw new Exception('Gagal menyiapkan statement SQL untuk tabel order_items: ' . mysqli_error($koneksi));
            }
            
            foreach ($checkoutData['items'] as $item) {
                // [DIPERBAIKI] Bind parameter dengan tipe data yang benar dan menyertakan seller_id.
                mysqli_stmt_bind_param($stmt_temp_item, "iiiid", 
                    $order_id, $item['id'], $item['seller_id'], $item['quantity'], $item['price']
                );
                mysqli_stmt_execute($stmt_temp_item);
            }
            
            mysqli_commit($koneksi);
            
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            throw $e;
        }

        $user_details = get_user_by_id($current_user_id);
        $params = [ 
            'external_id' => $external_id,
            'amount' => $totalAmount,
            'currency' => 'IDR',
            'payment_methods' => $xendit_payment_methods,
            'success_redirect_url' => 'http://localhost:8080/UMKM/statuspesanan.php?status=sukses&external_id=' . $external_id,
            'failure_redirect_url' => 'http://localhost:8080/UMKM/statuspesanan.php?status=gagal&external_id=' . $external_id,
            'customer' => [
                'given_names' => $user_details['fullname'] ?? 'Customer',
                'email' => $user_details['email'] ?? '',
            ],
            'description' => 'Pembelian produk KreasiLokal.id - Order ID: ' . $order_id
        ];

        $apiInstance = new \Xendit\Invoice\InvoiceApi();
        $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest($params);
        $invoice = $apiInstance->createInvoice($createInvoiceRequest);
        
        // [DIPERBAIKI] Update tabel order dengan invoice_id dari Xendit
        // Update order dengan invoice ID
// ... setelah $invoice = $apiInstance->createInvoice(...)

// [DIPERBAIKI] Query UPDATE sekarang menyertakan 3 placeholder (?) untuk 3 kolom
$stmt_update_invoice = mysqli_prepare($koneksi, "UPDATE orders SET xendit_invoice_id = ?, payment_url = ? WHERE id = ?");

if ($stmt_update_invoice === false) {
    throw new Exception('Gagal menyiapkan query update invoice: ' . mysqli_error($koneksi));
}

// [DIPERBAIKI] Menggunakan getter method untuk menghindari "Notice" dan lebih aman
$invoice_id_val = $invoice->getId();
$invoice_url_val = $invoice->getInvoiceUrl();

// [DIPERBAIKI] Mengikat 3 variabel dengan tipe data 'ssi' (string, string, integer)
mysqli_stmt_bind_param($stmt_update_invoice, "ssi", $invoice_id_val, $invoice_url_val, $order_id);
mysqli_stmt_execute($stmt_update_invoice);

$_SESSION['payment_data'] = [
    'external_id' => $external_id, 
    'invoice_id' => $invoice_id_val,
    'amount' => $totalAmount, 
    'payment_method' => $paymentMethod,
    'invoice_url' => $invoice_url_val, 
    'order_id' => $order_id
];

json_response('success', 'Invoice berhasil dibuat.', [
    'invoice_url' => $invoice_url_val, 
    'external_id' => $external_id,
    'invoice_id' => $invoice_id_val, 
    'order_id' => $order_id
]);
       
        $_SESSION['payment_data'] = [
            'external_id' => $external_id, 'invoice_id' => $invoice['id'],
            'amount' => $totalAmount, 'payment_method' => $paymentMethod,
            'invoice_url' => $invoice['invoice_url'], 'order_id' => $order_id
        ];

        json_response('success', 'Invoice berhasil dibuat.', [
            'invoice_url' => $invoice['invoice_url'], 'external_id' => $external_id,
            'invoice_id' => $invoice['id'], 'order_id' => $order_id
        ]);

    } catch (\Xendit\XenditSdkException $e) {
        json_response('error', 'Xendit SDK Error: ' . $e->getMessage(), ['details' => $e->getFullError()]);
    } catch (Exception $e) {
        json_response('error', 'Error: ' . $e->getMessage());
    }
    break;

    case 'pay_with_paylater':
        $checkoutData = $_SESSION['checkout_data'] ?? null;
        if (!$checkoutData || empty($checkoutData['items'])) {
            json_response('error', 'Sesi checkout tidak valid atau keranjang kosong.');
        }

        $result = buat_pesanan_dengan_paylater($current_user_id, $checkoutData);

        if ($result['status'] === 'success') {
            unset($_SESSION['checkout_data']);
            json_response('success', $result['message'], ['external_id' => $result['external_id']]);
        } else {
            json_response('error', $result['message'] ?? 'Gagal memproses pesanan.');
        }
        break;

    case 'check_payment_status':
        $external_id = $_GET['external_id'] ?? '';
        if (empty($external_id)) {
            json_response('error', 'External ID tidak ditemukan.');
        }

        // [DIPERBAIKI] Menggunakan nama kolom yang benar: 'id' dan 'status'
        $stmt = mysqli_prepare($koneksi, 
            "SELECT id, status FROM orders WHERE external_id = ? AND user_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "si", $external_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);

        if ($order) {
            json_response('success', 'Status ditemukan', $order);
        } else {
            json_response('error', 'Pesanan tidak ditemukan.');
        }
        break;
    default: json_response('error', 'Action tidak valid.');
}
?>