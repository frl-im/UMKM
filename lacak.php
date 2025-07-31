<?php
require_once 'fungsi.php';
check_login('pembeli');

// Ambil ID pesanan dari URL dan pastikan itu angka
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    die("Error: ID Pesanan tidak valid.");
}

// Ambil detail pesanan dari database menggunakan fungsi baru
$pesanan = ambil_detail_pesanan($order_id, $_SESSION['user_id']);

// Jika pesanan tidak ditemukan (mungkin bukan milik user ini), tampilkan pesan
if (!$pesanan) {
    die("Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.");
}

// Logika untuk menentukan langkah mana yang aktif di timeline pelacakan
$status_pesanan = $pesanan['status'];
$langkah = [
    'processing' => 1, // Dikemas
    'shipped' => 2,    // Dikirim
    'delivered' => 3   // Tiba
];
$langkah_aktif = $langkah[$status_pesanan] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan #<?php echo $pesanan['id']; ?> - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .card { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .card-header { padding: 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h2 { margin: 0; font-size: 1.5rem; }
        .card-body { padding: 1.5rem; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .info-item h4 { color: #777; font-size: 0.9rem; margin-bottom: 0.25rem; }
        .info-item p { margin: 0; font-weight: 500; }
        
        /* Timeline Pelacakan */
        .tracking-timeline { display: flex; justify-content: space-between; position: relative; margin-top: 1rem; }
        .timeline-line { position: absolute; top: 20px; left: 10%; right: 10%; height: 4px; background-color: #ddd; }
        .timeline-line-progress { position: absolute; top: 20px; left: 10%; height: 4px; background-color: #2e8b57; transition: width 0.5s ease; }
        .timeline-step { text-align: center; position: relative; width: 25%; }
        .step-icon { width: 40px; height: 40px; background-color: #ddd; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; color: white; border: 4px solid #f4f7f6; }
        .step-title { margin-top: 0.5rem; font-size: 0.8rem; font-weight: 500; color: #777; }
        .timeline-step.active .step-icon { background-color: #2e8b57; }
        .timeline-step.active .step-title { color: #2e8b57; font-weight: bold; }

        /* Daftar Produk */
        .product-item { display: flex; align-items: center; gap: 1rem; padding: 1rem 0; }
        .product-item:not(:last-child) { border-bottom: 1px solid #f0f0f0; }
        .product-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .product-info h4 { margin: 0; font-size: 1rem; }
        .product-info p { margin: 0; color: #777; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Lacak Pesanan <span style="color: #2e8b57;">#<?php echo safe_output($pesanan['id']); ?></span></h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <h4>Tanggal Pemesanan</h4>
                        <p><?php echo date('d F Y', strtotime($pesanan['created_at'])); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Total Pembayaran</h4>
                        <p><?php echo format_price($pesanan['total_amount']); ?></p>
                    </div>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <h4>Alamat Pengiriman</h4>
                        <p><?php echo safe_output($pesanan['shipping_address']); ?></p>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #eee; margin: 2rem 0;">

                <div class="tracking-timeline">
                    <div class="timeline-line"></div>
                    <div class="timeline-line-progress" style="width: <?php echo ($langkah_aktif * 33.33); ?>%;"></div>

                    <div class="timeline-step active"> <div class="step-icon"><i class="fas fa-box"></i></div>
                        <p class="step-title">Dikemas</p>
                    </div>
                    <div class="timeline-step <?php if ($langkah_aktif >= 2) echo 'active'; ?>">
                        <div class="step-icon"><i class="fas fa-truck"></i></div>
                        <p class="step-title">Dikirim</p>
                    </div>
                    <div class="timeline-step <?php if ($langkah_aktif >= 3) echo 'active'; ?>">
                        <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                        <p class="step-title">Tiba di Tujuan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Detail Produk</h3>
            </div>
            <div class="card-body" style="padding-top: 0;">
                <?php foreach($pesanan['items'] as $item): ?>
                <div class="product-item">
                    <img src="<?php echo safe_output($item['image_url']); ?>" alt="<?php echo safe_output($item['name']); ?>">
                    <div class="product-info">
                        <h4><?php echo safe_output($item['name']); ?></h4>
                        <p><?php echo $item['quantity']; ?> x <?php echo format_price($item['price']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="text-align: center; margin: 2rem 0;">
            <a href="pesanan.php" style="color: #2e8b57; text-decoration: none; font-weight: 500;">&larr; Kembali ke Daftar Pesanan</a>
        </div>
    </div>
</body>
</html>