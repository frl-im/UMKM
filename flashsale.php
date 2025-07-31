<?php
// [DINAMIS] Memuat fungsi dan mengambil data produk flash sale dari database
require_once 'fungsi.php';
$flash_sale_products = ambil_produk_flash_sale();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flash Sale - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            color: #333;
        }
        .container {
            /* [RESPONSIF] Menggunakan max-width agar layout bisa menyesuaikan di layar kecil */
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        a { text-decoration: none; color: inherit; }

        /* Header */
        .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; position: sticky; top: 0; z-index: 100; }
        .navbar .container { display: flex; align-items: center; justify-content: space-between; }
        .nav-left { display: flex; align-items: center; }
        .nav-left .back-btn { font-size: 1.5rem; color: #333; margin-right: 1.5rem; }
        .nav-left .page-title { font-size: 1.2rem; font-weight: 600; }
        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .nav-right a { font-size: 1.5rem; color: #555; }

        /* Flash Sale Header */
        .flash-sale-banner { padding: 2rem; background: linear-gradient(to right, #e74c3c, #c0392b); color: white; margin-top: 1.5rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .flash-sale-banner h1 { font-size: 2.2rem; margin: 0; }
        .countdown-timer { text-align: right; }
        .countdown-timer span:first-child { display: block; font-size: 0.9rem; }
        .countdown-timer span:last-child { font-size: 1.8rem; font-weight: bold; letter-spacing: 2px; }

        /* Product Grid */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; padding: 1.5rem 0; }
        .product-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: all 0.2s ease-in-out; display: flex; flex-direction: column; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-card .product-image { position: relative; }
        .product-card .product-image img { width: 100%; height: 220px; object-fit: cover; }
        .product-badge { position: absolute; top: 0; right: 0; background: #e74c3c; color: white; padding: 5px 10px; font-size: 0.8rem; font-weight: bold; border-radius: 0 8px 0 8px; }
        .product-info { padding: 1rem; display: flex; flex-direction: column; flex-grow: 1; }
        .product-info h3 { font-size: 1rem; margin: 0 0 0.5rem 0; height: 40px; }
        .price-info { display: flex; align-items: baseline; gap: 0.5rem; margin: 0.5rem 0; }
        .price-info .current-price { font-size: 1.3rem; font-weight: bold; color: #e74c3c; }
        .price-info .original-price { font-size: 0.9rem; text-decoration: line-through; color: #777; }
        .stock-bar { margin-top: auto; }
        .stock-info { font-size: 0.8rem; color: #333; margin-bottom: 5px; }
        .progress-bar { background-color: #eee; height: 10px; border-radius: 5px; overflow: hidden; }
        .progress { background: linear-gradient(to right, #f39c12, #e67e22); height: 100%; }
        .empty-state { text-align: center; padding: 4rem; background: #fff; border-radius: 8px; }
        .empty-state i { font-size: 3rem; color: #ccc; margin-bottom: 1rem; }

        /* [RESPONSIF] Media Queries */
        @media (max-width: 768px) {
            .container { padding: 0 10px; }
            .flash-sale-banner { flex-direction: column; text-align: center; gap: 1rem; }
            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
            .nav-left .page-title { display: none; }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-left">
                <a href="javascript:history.back()" class="back-btn" title="Kembali"><i class="fas fa-arrow-left"></i></a>
                <span class="page-title">Flash Sale</span>
            </div>
            <div class="nav-right">
                <a href="index.php" title="Beranda"><i class="fas fa-home"></i></a>
                <a href="keranjang.php" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="flash-sale-banner">
            <h1><i class="fas fa-bolt"></i> Flash Sale</h1>
            <div class="countdown-timer">
                <span>Berakhir Dalam</span>
                <span id="countdown">02:29:41</span>
            </div>
        </section>

        <section class="product-grid">
            <?php if (empty($flash_sale_products)): ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-store-slash"></i>
                    <p>Saat ini tidak ada produk Flash Sale yang tersedia.</p>
                </div>
            <?php else: ?>
                <?php foreach ($flash_sale_products as $product): ?>
                    <?php
                        // Menghitung persen diskon
                        $original_price = $product['price'];
                        $discount_price = $product['discount_price'];
                        $discount_percentage = 0;
                        if ($original_price > 0 && $discount_price > 0) {
                            $discount_percentage = round((($original_price - $discount_price) / $original_price) * 100);
                        }
                    ?>
                    <article class="product-card">
                        <a href="detailproduk.php?id=<?php echo $product['id']; ?>">
                            <div class="product-image">
                                <img src="<?php echo safe_output($product['image_url']); ?>" alt="<?php echo safe_output($product['name']); ?>">
                                <?php if ($discount_percentage > 0): ?>
                                    <div class="product-badge">-<?php echo $discount_percentage; ?>%</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo safe_output($product['name']); ?></h3>
                                <div class="price-info">
                                    <span class="current-price"><?php echo format_price($discount_price); ?></span>
                                    <span class="original-price"><?php echo format_price($original_price); ?></span>
                                </div>
                                <div class="stock-bar">
                                    <div class="stock-info">Sisa Stok: <?php echo $product['stock']; ?></div>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>