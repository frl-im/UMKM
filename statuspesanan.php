<?php
// payment_status.php - Handler untuk redirect sukses/gagal dari Xendit
require_once 'fungsi.php';
check_login('pembeli');

$status = $_GET['status'] ?? '';
$external_id = $_GET['external_id'] ?? '';

// Inisialisasi variabel
$payment_status = 'unknown';
$order_data = null;
$message = '';

if (!empty($external_id)) {
    global $koneksi;
    
    // Ambil data order berdasarkan external_id
   // Ambil data order berdasarkan external_id
$stmt = mysqli_prepare($koneksi, 
    "SELECT o.*, GROUP_CONCAT(oi.product_id) as product_ids 
     FROM orders o 
     LEFT JOIN order_items oi ON o.id = oi.order_id -- DIUBAH: Menggunakan o.id untuk join
     WHERE o.external_id = ? AND o.user_id = ?
     GROUP BY o.id" );
     
    mysqli_stmt_bind_param($stmt, "si", $external_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order_data = mysqli_fetch_assoc($result);
    
    if ($order_data) {
    // [DIPERBAIKI] Baca kolom 'status' yang benar dari database
    $order_status = $order_data['status'];
    $payment_status = 'unknown'; // Inisialisasi variabel untuk tampilan
    
    // [DIPERBAIKI] Logika disesuaikan dengan nilai dari kolom 'status'
    switch ($order_status) {
        case 'processing': // Status 'processing' dianggap sudah terbayar
        case 'shipped':
        case 'delivered':
            $payment_status = 'paid';
            $message = 'Pembayaran berhasil! Pesanan Anda sedang diproses.';
            break;
        case 'pending':
            $payment_status = 'pending';
            $message = 'Pembayaran sedang diproses atau menunggu. Silakan tunggu konfirmasi.';
            break;
        case 'cancelled':
            $payment_status = 'failed';
            $message = 'Pesanan Anda telah dibatalkan.';
            break;
        default:
            $payment_status = 'unknown';
            $message = 'Status pembayaran tidak diketahui.';
    }
} else {
        // Jika belum ada di database, mungkin webhook belum sampai
        // Cek status dari URL parameter
        if ($status === 'sukses') {
            $payment_status = 'processing';
            $message = 'Pembayaran berhasil! Pesanan Anda sedang diproses.';
        } else {
            $payment_status = 'failed';
            $message = 'Pembayaran tidak berhasil. Silakan coba lagi.';
        }
    }
}

// Clear checkout session
unset($_SESSION['checkout_data']);
unset($_SESSION['payment_data']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f4f7f6; 
            margin: 0; 
            padding: 2rem 1rem; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { 
            max-width: 500px; 
            width: 100%;
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            padding: 3rem 2rem;
            text-align: center;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem auto;
            font-size: 2.5rem;
        }
        .status-success { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-failed { background-color: #f8d7da; color: #721c24; }
        .status-processing { background-color: #d1ecf1; color: #0c5460; }
        
        h2 { 
            margin: 0 0 1rem 0; 
            font-size: 1.5rem; 
            font-weight: 600; 
        }
        p { 
            color: #666; 
            font-size: 1rem; 
            margin-bottom: 2rem; 
            line-height: 1.5;
        }
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 600; 
            margin: 0.5rem; 
            transition: all 0.2s;
        }
        .btn-primary { 
            background-color: #2e8b57; 
            color: white; 
        }
        .btn-primary:hover { 
            background-color: #236b43; 
        }
        .btn-secondary { 
            background-color: #f8f9fa; 
            color: #333; 
            border: 1px solid #ddd; 
        }
        .btn-secondary:hover { 
            background-color: #e9ecef; 
        }
        .order-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }
        .order-info h4 {
            margin: 0 0 1rem 0;
            color: #333;
        }
        .order-info p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($payment_status === 'paid' || $payment_status === 'processing'): ?>
            <div class="status-icon status-success">
                <i class="fas fa-check"></i>
            </div>
            <h2>Pembayaran Berhasil!</h2>
            <p><?php echo $message; ?></p>
            
            <?php if ($order_data): ?>
            <div class="order-info">
                <h4>Detail Pesanan</h4>
                <p><strong>Order ID:</strong> <?php echo safe_output($order_data['id']); ?></p>
                <p><strong>Total:</strong> <?php echo format_price($order_data['total_amount']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order_data['status']); ?></p>
                <p><strong>Tanggal:</strong> <?php echo date('d M Y H:i', strtotime($order_data['created_at'])); ?></p>
            </div>
            <?php endif; ?>
            
            <a href="pesanan.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Lihat Pesanan Saya
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
            
        <?php elseif ($payment_status === 'pending'): ?>
            <div class="status-icon status-pending">
                <i class="fas fa-clock"></i>
            </div>
            <h2>Pembayaran Sedang Diproses</h2>
            <p><?php echo $message; ?></p>
            
            <a href="pesanan.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Cek Status Pesanan
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
            
        <?php else: ?>
            <div class="status-icon status-failed">
                <i class="fas fa-times"></i>
            </div>
            <h2>Pembayaran Gagal</h2>
            <p><?php echo $message; ?></p>
            
            <a href="keranjang.php" class="btn btn-primary">
                <i class="fas fa-redo"></i> Coba Lagi
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        <?php endif; ?>
    </div>

    <?php if ($payment_status === 'paid' || $payment_status === 'processing'): ?>
    <script>
        // Auto-konfirmasi setelah beberapa detik
        setTimeout(() => {
            if (confirm('Pembayaran berhasil! Apakah Anda ingin melihat pesanan Anda sekarang?')) {
                window.location.href = 'pesanan.php';
            }
        }, 3000);
    </script>
    <?php endif; ?>
</body>
</html>