<?php
require_once 'fungsi.php';
 check_login('pembeli');
 // Menggunakan status yang benar sesuai database Anda
 $pesanan_pending = ambil_pesanan_by_status('pending');
 $pesanan_processing = ambil_pesanan_by_status('processing');
 $pesanan_shipped = ambil_pesanan_by_status('shipped');
 $pesanan_delivered = ambil_pesanan_by_status('delivered');
 $pesanan_cancelled = ambil_pesanan_by_status('cancelled');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Anda tidak perlu diubah */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; align-items: center; }
        .navbar .back-link { font-size: 1.2rem; color: #333; text-decoration: none; display: flex; align-items: center; }
        .navbar .logo { font-size: 1.5rem; font-weight: bold; color: #2e8b57; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 900px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header h2 { margin: 0; font-size: 1.8rem; font-weight: 600; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background-color: #2e8b57; color: white; }
        .empty-state { text-align: center; padding: 4rem 2rem; border: 2px dashed #ddd; border-radius: 8px; background-color: #fafafa; }
        .empty-state i { font-size: 4rem; color: #ccc; margin-bottom: 1.5rem; }
        .empty-state p { font-size: 1.2rem; color: #777; margin: 0; }
        footer { text-align: center; margin-top: 3rem; padding: 1rem; color: #888; }
        .tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 2rem; overflow-x: auto; }
        .tab-item { padding: 1rem 1.5rem; cursor: pointer; font-weight: 600; color: #777; border-bottom: 3px solid transparent; margin-bottom: -2px; white-space: nowrap; }
        .tab-item.active { color: #2e8b57; border-bottom-color: #2e8b57; }
        .order-item { border: 1px solid #eee; border-radius: 8px; margin-bottom: 1.5rem; padding: 1.5rem; }
        .order-header { display: flex; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid #eee; margin-bottom: 1rem; align-items: center; }
        .order-header p, .order-header h4 { margin: 0; }
        .order-product { display: flex; gap: 1rem; align-items: center; }
        .order-product img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .order-footer { text-align: right; margin-top: 1rem; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="profilpembeli.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
        <a href="index.php" class="logo">KreasiLokal.id</a>
    </nav>
    <div class="container">
        <div class="page-header">
            <h2>Pesanan Saya</h2>
        </div>
        <div class="tabs">
<<<<<<< HEAD
            <div class="tab-item active" data-tab="belum-bayar">Belum Bayar</div>
            <div class="tab-item" data-tab="dikemas">Dikemas</div>
            <div class="tab-item" data-tab="dikirim">Dikirim</div>
            <div class="tab-item" data-tab="selesai">Selesai</div>
            <div class="tab-item" data-tab="dibatalkan">Dibatalkan</div>
        </div>

        <div id="belum-bayar" class="tab-content active">
            <?php if (empty($pesanan_pending)): ?>
                <div class="empty-state"><i class="fas fa-wallet"></i><p>Tidak ada pesanan yang menunggu pembayaran.</p></div>
            <?php else: ?>
                <?php foreach ($pesanan_pending as $pesanan): ?>
                    <div class="order-item">
                        <div class="order-header">
                            <h4><?php echo safe_output($pesanan['store_name']); ?></h4>
                            <p style="color: #d9534f; font-weight: bold;">Menunggu Pembayaran</p>
                        </div>
                        <div class="order-product">
                            <img src="<?php echo safe_output($pesanan['product_image_path'] ?? 'https://via.placeholder.com/150'); ?>" alt="Produk">
                            <div>
                                <h4><?php echo safe_output($pesanan['product_name']); ?></h4>
                                <p><?php echo safe_output($pesanan['quantity']); ?> x <?php echo format_price($pesanan['price_per_item']); ?></p>
                            </div>
                        </div>
                        <div class="order-footer">
                             <p>Total Pesanan: <strong style="font-size: 1.2rem; color: #2e8b57;"><?php echo format_price($pesanan['total_amount']); ?></strong></p>
                            <a href="bayar.php?order_id=<?php echo $pesanan['order_id']; ?>" class="btn btn-primary">Bayar Sekarang</a>
                        </div>
=======
    <a href="pesanan.php" class="tab-item active">Belum Bayar</a>
    <a href="dikemas.php" class="tab-item">Dikemas</a>
    <a href="dikirim.php" class="tab-item">Dikirim</a>
    <a href="beri_penilaian.php" class="tab-item">Beri Penilaian</a>
    <a href="selesai.php" class="tab-item">Selesai</a>
</div>
        <div class="tab-content">
            <div class="order-item">
                <div class="order-header">
                    <h4>Toko Batik Trusmi</h4>
                    <p style="color: #d9534f; font-weight: bold;">Menunggu Pembayaran</p>
                </div>
                <div class="order-product">
                    <img src="https://dynamic.zacdn.com/CAqpEXN0152sEQHTIO3s3RVPYCE=/filters:quality(70):format(webp)/https://static-id.zacdn.com/p/arjuna-weda-6040-2520944-3.jpg" alt="Produk">
                    <div>
                        <h4>Kemeja Batik Mega Mendung</h4>
                        <p>1 x Rp150.000</p>
>>>>>>> ceacfe38b8eb1b032b23cc81903b93c9dc0bfb65
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="dikemas" class="tab-content">
             <?php if (empty($pesanan_processing)): ?>
                <div class="empty-state"><i class="fas fa-box-open"></i><p>Tidak ada pesanan yang sedang dikemas.</p></div>
            <?php else: ?>
                <?php foreach ($pesanan_processing as $pesanan): ?>
                    <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="dikirim" class="tab-content">
              <?php if (empty($pesanan_shipped)): ?>
                <div class="empty-state"><i class="fas fa-truck"></i><p>Tidak ada pesanan yang sedang dikirim.</p></div>
            <?php else: ?>
                <?php foreach ($pesanan_shipped as $pesanan): ?>
                    <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="selesai" class="tab-content">
             <?php if (empty($pesanan_delivered)): ?>
                <div class="empty-state"><i class="fas fa-check-circle"></i><p>Belum ada pesanan yang selesai.</p></div>
            <?php else: ?>
                <?php foreach ($pesanan_delivered as $pesanan): ?>
                    <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="dibatalkan" class="tab-content">
             <?php if (empty($pesanan_cancelled)): ?>
                <div class="empty-state"><i class="fas fa-times-circle"></i><p>Tidak ada pesanan yang dibatalkan.</p></div>
            <?php else: ?>
                <?php foreach ($pesanan_cancelled as $pesanan): ?>
                    <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 KreasiLokal.id</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-item');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(item => item.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    tab.classList.add('active');
                    const targetContent = document.getElementById(tab.dataset.tab);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>
 