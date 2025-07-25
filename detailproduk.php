<?php
require_once 'fungsi.php';
$is_logged_in = is_logged_in(); // Cek status login

// Ambil ID produk dari URL
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    die("Error: ID Produk tidak ditemukan.");
}

// Ambil data produk spesifik dari database
$product = ambil_produk_by_id($product_id);

// Jika produk tidak ditemukan, tampilkan pesan
if (!$product) {
    die("Produk tidak ditemukan atau tidak aktif.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_output($product['name']); ?> - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Menggunakan kembali CSS lengkap dari desain awal Anda */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f4f4f4; margin: 0; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1.5rem 15px; }
        .card { background-color: #fff; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; }
        .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; }
        .navbar .container { display: flex; align-items: center; justify-content: space-between; }
        .navbar .logo a { font-size: 1.8rem; font-weight: bold; color: #2e8b57; text-decoration: none; }
        .navbar .nav-icons { display: flex; align-items: center; gap: 1.5rem; }
        .navbar .nav-icons a { font-size: 1.5rem; color: #555; text-decoration: none; }
        .product-overview { display: grid; grid-template-columns: 450px 1fr; gap: 2rem; }
        .image-gallery .main-image img { width: 100%; border-radius: 5px; border: 1px solid #eee; }
        .product-info h1 { font-size: 1.5rem; margin: 0 0 10px 0; }
        .product-stats { display: flex; align-items: center; gap: 1.5rem; color: #555; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
        .product-stats .rating { color: #ffc107; }
        .product-price-section { background-color: #fafafa; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .product-price-section .current-price { font-size: 2rem; color: #2e8b57; font-weight: bold; }
        .seller-info { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid #eee; border-radius: 8px; }
        .seller-info .seller-details { flex-grow: 1; }
        .seller-info .seller-details strong { display: block; font-size: 1.1rem; }
        .btn-outline { padding: 0.6rem 1rem; border: 1px solid #2e8b57; color: #2e8b57; background: transparent; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .product-specs table { width: 100%; border-collapse: collapse; }
        .product-specs td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .product-specs td:first-child { width: 200px; color: #777; }
        .bottom-action-bar { position: fixed; bottom: 0; left: 0; width: 100%; background: #fff; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); display: flex; z-index: 1000; }
        .bottom-action-bar .action-group { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.5rem 1rem; border-right: 1px solid #eee; min-width:80px; cursor: pointer;}
        .bottom-action-bar .main-actions { flex-grow: 1; display: flex; }
        .bottom-action-bar .btn-action { flex: 1; padding: 1rem; border: none; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .btn-add-to-cart { background-color: #e9f5ee; color: #2e8b57; }
        .btn-buy-now { background-color: #2e8b57; color: white; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
          <div class="logo">
            <a href="index.php"><i class="fas fa-leaf"></i> KreasiLokal.id</a>
          </div>
          <div class="nav-icons">
            <a href="javascript:history.back()" title="Kembali"><i class="fas fa-arrow-left"></i></a>
            <a href="index.php" title="Beranda"><i class="fas fa-home"></i></a>
            <a href="keranjang.php" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
          </div>
        </div>
    </header>

    <div class="container" id="product-detail-container">
        <div class="card product-overview">
            <div class="image-gallery">
                <div class="main-image">
                    <img id="mainProductImage" src="<?php echo safe_output($product['image_url']); ?>" alt="<?php echo safe_output($product['name']); ?>">
                </div>
            </div>
            <div class="product-info">
                <h1><?php echo safe_output($product['name']); ?></h1>
                <div class="product-stats">
                    <span class="rating"><i class="fas fa-star"></i> N/A</span>
                    <span>|</span>
                    <span>Terjual: N/A</span>
                </div>
                <div class="product-price-section">
                    <span class="current-price"><?php echo format_price($product['price']); ?></span>
                </div>
                </div>
        </div>

        <div class="card seller-info">
            <img src="https://placehold.co/100x100/2e8b57/ffffff?text=<?php echo strtoupper(substr($product['store_name'], 0, 1)); ?>" alt="Logo Toko">
            <div class="seller-details">
                <strong><?php echo safe_output($product['store_name']); ?></strong>
                <span>Aktif 5 menit lalu</span>
            </div>
            <button class="btn-outline">Kunjungi Toko</button>
        </div>
        
        <div class="card">
            <h3>Spesifikasi Produk</h3>
            <div class="product-specs">
                <table>
                    <tr><td>Kategori</td><td><?php echo safe_output($product['category']); ?></td></tr>
                    <tr><td>Stok</td><td><?php echo safe_output($product['stock']); ?></td></tr>
                    <tr><td>Asal Produk</td><td>Lokal</td></tr>
                    </table>
            </div>
            <br>
            <h3>Deskripsi Produk</h3>
            <p><?php echo nl2br(safe_output($product['description'])); ?></p>
        </div>

        </div>

    <div style="height: 80px;"></div>
    
    <div class="bottom-action-bar">
        <div class="action-group" onclick="location.href='chat.php?user_id=<?php echo $product['seller_id']; ?>'">
            <i class="fas fa-comments"></i>
            <span>Chat</span>
        </div>
        <div class="main-actions">
            <button class="btn-action btn-add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                <i class="fas fa-cart-plus"></i> Masukkan Keranjang
            </button>
            <button class="btn-action btn-buy-now" onclick="buyNow(<?php echo $product['id']; ?>)">Beli Sekarang</button>
        </div>
    </div>

    <script>
    function addToCart(productId) {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', productId);

        fetch('ajax/ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'login_required') {
                window.location.href = 'login.html';
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function buyNow(productId) {
        // Fungsi ini pertama menambahkan ke keranjang, lalu langsung ke halaman checkout
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', productId);

        fetch('ajax/ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'checkout.php';
            } else if (data.status === 'login_required') {
                window.location.href = 'login.html';
            } else {
                alert(data.message);
            }
        });
    }
    </script>
</body>
</html>