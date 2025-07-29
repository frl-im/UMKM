<?php
require_once 'fungsi.php';
 check_login('pembeli');
 // Mengambil data pesanan untuk setiap status
 $pesanan_pending = ambil_pesanan_by_status('pending');
 $pesanan_processing = ambil_pesanan_by_status('processing'); // Anda mungkin perlu status 'paid' atau 'settlement' juga
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
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; align-items: center; position: sticky; top: 0; z-index: 100; }
        .navbar .back-link { font-size: 1.2rem; color: #333; text-decoration: none; display: flex; align-items: center; }
        .navbar .logo { font-size: 1.5rem; font-weight: bold; color: #2e8b57; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .page-header h2 { margin: 0 0 2rem 0; font-size: 1.8rem; font-weight: 600; }
        .btn { display: inline-block; padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; transition: background-color 0.2s; }
        .btn-primary { background-color: #2e8b57; color: white; }
        .btn-secondary { background-color: #f0f0f0; color: #333; }
        .empty-state { text-align: center; padding: 4rem 2rem; border: 2px dashed #ddd; border-radius: 8px; background-color: #fafafa; }
        .empty-state i { font-size: 3rem; color: #ccc; margin-bottom: 1.5rem; }
        .empty-state p { font-size: 1.1rem; color: #777; margin: 0; }
        .tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 2rem; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .tab-item { padding: 1rem 1.5rem; cursor: pointer; font-weight: 600; color: #777; border-bottom: 3px solid transparent; margin-bottom: -2px; white-space: nowrap; }
        .tab-item.active { color: #2e8b57; border-bottom-color: #2e8b57; }
        .order-item { border: 1px solid #eee; border-radius: 8px; margin-bottom: 1.5rem; background-color: #fff; }
        .order-header { display: flex; justify-content: space-between; padding: 1rem 1.5rem; border-bottom: 1px solid #eee; align-items: center; }
        .order-header .store-name { font-weight: 600; }
        .order-header .status { font-weight: bold; }
        .status-pending { color: #f0ad4e; }
        .status-processing { color: #0275d8; }
        .status-shipped { color: #5cb85c; }
        .status-delivered { color: #2e8b57; }
        .status-cancelled { color: #d9534f; }
        .order-product { display: flex; gap: 1rem; align-items: center; padding: 1.5rem; border-bottom: 1px solid #f5f5f5; }
        .order-product:last-child { border-bottom: none; }
        .order-product img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; }
        .product-info h4 { margin: 0 0 0.25rem 0; font-size: 1rem; }
        .product-info p { margin: 0; color: #777; font-size: 0.9rem; }
        .order-footer { text-align: right; padding: 1rem 1.5rem; background-color: #fafafa; border-top: 1px solid #eee; border-radius: 0 0 8px 8px; }
        .order-footer p { margin: 0 0 1rem 0; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="profilpembeli.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
        <a href="index.php" class="logo">Pesanan Saya</a>
    </nav>
    <div class="container">
        <div class="page-header">
            <h2>Riwayat Transaksi</h2>
        </div>
        <div class="tabs">
            <div class="tab-item active" data-tab="pending">Belum Bayar</div>
            <div class="tab-item" data-tab="processing">Dikemas</div>
            <div class="tab-item" data-tab="shipped">Dikirim</div>
            <div class="tab-item" data-tab="delivered">Selesai</div>
            <div class="tab-item" data-tab="cancelled">Dibatalkan</div>
        </div>

        <div id="pending" class="tab-content active">
            <?php if (empty($pesanan_pending)): ?>
                <div class="empty-state"><i class="fas fa-wallet"></i><p>Tidak ada pesanan yang menunggu pembayaran.</p></div>
            <?php else: foreach ($pesanan_pending as $pesanan): ?>
                <div class="order-item">
                    <?php foreach ($pesanan['items'] as $item): ?>
                    <div class="order-product">
                        <img src="<?php echo safe_output($item['product_image_path'] ?? 'https://via.placeholder.com/150'); ?>" alt="Produk">
                        <div class="product-info">
                            <h4><?php echo safe_output($item['product_name']); ?></h4>
                            <p><?php echo safe_output($item['quantity']); ?> x <?php echo format_price($item['price_per_item']); ?></p>
                            <small>dari <?php echo safe_output($item['store_name']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="order-footer">
                        <p>Total Pesanan: <strong style="font-size: 1.2rem; color: #2e8b57;"><?php echo format_price($pesanan['total_amount']); ?></strong></p>
                        <a href="bayar.php?order_id=<?php echo $pesanan['order_id']; ?>" class="btn btn-primary">Bayar Sekarang</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div id="processing" class="tab-content">
             <?php if (empty($pesanan_processing)): ?>
                <div class="empty-state"><i class="fas fa-box-open"></i><p>Tidak ada pesanan yang sedang dikemas.</p></div>
            <?php else: foreach ($pesanan_processing as $pesanan): ?>
                <div class="order-item">
                    <?php foreach ($pesanan['items'] as $item): ?>
                    <div class="order-product">
                        <img src="<?php echo safe_output($item['product_image_path'] ?? 'https://via.placeholder.com/150'); ?>" alt="Produk">
                        <div class="product-info">
                            <h4><?php echo safe_output($item['product_name']); ?></h4>
                            <p><?php echo safe_output($item['quantity']); ?> x <?php echo format_price($item['price_per_item']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="order-footer">
                        <p>Total Pesanan: <strong><?php echo format_price($pesanan['total_amount']); ?></strong></p>
                        <a href="lacak.php?order_id=<?php echo $pesanan['order_id']; ?>" class="btn btn-secondary">Lacak Pesanan</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div id="shipped" class="tab-content">
              <?php if (empty($pesanan_shipped)): ?>
                <div class="empty-state"><i class="fas fa-truck"></i><p>Tidak ada pesanan yang sedang dikirim.</p></div>
            <?php else: foreach ($pesanan_shipped as $pesanan): ?>
                <div class="order-item">
                    <?php foreach ($pesanan['items'] as $item): ?>
                    <div class="order-product">
                        <img src="<?php echo safe_output($item['product_image_path'] ?? 'https://via.placeholder.com/150'); ?>" alt="Produk">
                        <div class="product-info">
                            <h4><?php echo safe_output($item['product_name']); ?></h4>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="order-footer">
                        <a href="lacak.php?order_id=<?php echo $pesanan['order_id']; ?>" class="btn btn-primary">Lacak Pengiriman</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div id="delivered" class="tab-content">
             <?php if (empty($pesanan_delivered)): ?>
                <div class="empty-state"><i class="fas fa-check-circle"></i><p>Belum ada pesanan yang selesai.</p></div>
            <?php else: foreach ($pesanan_delivered as $pesanan): ?>
                <div class="order-item">
                    <?php foreach ($pesanan['items'] as $item): ?>
                    <div class="order-product">
                        <img src="<?php echo safe_output($item['product_image_path'] ?? 'https://via.placeholder.com/150'); ?>" alt="Produk">
                        <div class="product-info">
                            <h4><?php echo safe_output($item['product_name']); ?></h4>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="order-footer">
                        <a href="beri_penilaian.php?order_id=<?php echo $pesanan['order_id']; ?>" class="btn btn-primary">Beri Penilaian</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div id="cancelled" class="tab-content">
             <?php if (empty($pesanan_cancelled)): ?>
                <div class="empty-state"><i class="fas fa-times-circle"></i><p>Tidak ada pesanan yang dibatalkan.</p></div>
            <?php else: foreach ($pesanan_cancelled as $pesanan): ?>
                <div class="order-item">
                    <?php foreach ($pesanan['items'] as $item): ?>
                    <div class="order-product">
                        <img src="<?php echo safe_output($item['product_image_path'] ?? 'https://via.placeholder.com/150'); ?>" alt="Produk">
                        <div class="product-info"><h4><?php echo safe_output($item['product_name']); ?></h4></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

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