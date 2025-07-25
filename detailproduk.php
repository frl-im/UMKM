<?php
require_once 'fungsi.php';
$is_logged_in = is_logged_in(); // Cek status login

// Ambil ID produk dari URL, contoh: detailproduk.php?id=1
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    die("Error: ID Produk tidak ditemukan.");
}

// Ambil data produk spesifik dari database menggunakan fungsi baru
$product = ambil_produk_by_id($product_id);

// Jika produk tidak ditemukan di database, tampilkan pesan
if (!$product) {
    die("Produk tidak ditemukan atau tidak aktif.");
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f4f4f4; 
            margin: 0; 
            color: #333; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 1.5rem 15px; 
        }
        .card { 
            background-color: #fff; 
            padding: 1.5rem; 
            border-radius: 8px; 
            margin-bottom: 1rem;
        }
        
        /* Header */
        .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; }
        .navbar .container { display: flex; align-items: center; justify-content: space-between; padding: 0 15px; margin: 0 auto; }
        .navbar .logo a { font-size: 1.8rem; font-weight: bold; color: #2e8b57; text-decoration: none; }
        .navbar .nav-icons { display: flex; align-items: center; gap: 1.5rem; }
        .navbar .nav-icons a { font-size: 1.5rem; color: #555; text-decoration: none; }


        /* Product Overview */
        .product-overview { display: grid; grid-template-columns: 450px 1fr; gap: 2rem; }
        .image-gallery .main-image img { width: 100%; border-radius: 5px; border: 1px solid #eee; }
        .thumbnail-images { display: flex; gap: 10px; margin-top: 10px; }
        .thumbnail-images img { width: 80px; height: 80px; border-radius: 5px; cursor: pointer; border: 2px solid #eee; object-fit: cover; }
        .thumbnail-images img.active { border-color: #2e8b57; }

        .product-info h1 { font-size: 1.5rem; margin: 0 0 10px 0; }
        .product-stats { display: flex; align-items: center; gap: 1.5rem; color: #555; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
        .product-stats .rating { color: #ffc107; }
        .product-price-section { background-color: #fafafa; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .product-price-section .current-price { font-size: 2rem; color: #2e8b57; font-weight: bold; }
        .product-price-section .original-price { text-decoration: line-through; color: #aaa; margin-left: 10px; }
        
        .info-list .info-item { display: flex; align-items: center; gap: 10px; margin-bottom: 0.8rem; }
        .info-list .info-item i { color: #2e8b57; }
        
        .variant-group { margin-top: 1.5rem; }
        .variant-group label { font-weight: 600; display: block; margin-bottom: 8px; }
        .variant-buttons button { padding: 8px 15px; border: 1px solid #ccc; background: #fff; border-radius: 5px; cursor: pointer; margin-right: 8px; }
        .variant-buttons button.active { border-color: #2e8b57; background-color: #e9f5ee; }

        /* Seller Info */
        .seller-info { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid #eee; border-radius: 8px; }
        .seller-info img { width: 60px; height: 60px; border-radius: 50%; }
        .seller-info .seller-details { flex-grow: 1; }
        .seller-info .seller-details strong { display: block; font-size: 1.1rem; }
        .btn-outline { padding: 0.6rem 1rem; border: 1px solid #2e8b57; color: #2e8b57; background: transparent; border-radius: 5px; cursor: pointer; font-weight: 600; }

        /* Reviews */
        .review-summary { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .review-card { border-top: 1px solid #eee; padding-top: 1.5rem; margin-top: 1.5rem; }
        .review-card .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem; }
        .review-card .user-info img { width: 40px; height: 40px; border-radius: 50%; }
        .review-card .review-images { display: flex; gap: 10px; margin-top: 1rem; }
        .review-card .review-images img { width: 70px; height: 70px; border-radius: 5px; object-fit: cover; }

        /* Horizontal Scroll Sections */
        .horizontal-scroll-container { display: flex; overflow-x: auto; gap: 1rem; padding-bottom: 1rem; }
        .horizontal-scroll-container::-webkit-scrollbar { height: 5px; }
        .horizontal-scroll-container::-webkit-scrollbar-thumb { background: #ccc; border-radius: 5px; }
        .product-card-small { flex: 0 0 180px; border: 1px solid #eee; border-radius: 5px; overflow: hidden; }
        .product-card-small img { width: 100%; height: 180px; object-fit: cover; }
        .product-card-small .info { padding: 10px; }
        .product-card-small .info h4 { font-size: 0.9rem; margin: 0 0 5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-card-small .info p { font-size: 1rem; font-weight: bold; color: #2e8b57; margin: 0; }

        /* Specs & Description */
        .product-specs table { width: 100%; border-collapse: collapse; }
        .product-specs td { padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .product-specs td:first-child { width: 200px; color: #777; }

        /* Bottom Action Bar */
        .bottom-action-bar { position: fixed; bottom: 0; left: 0; width: 100%; background: #fff; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); display: flex; z-index: 1000; }
        .bottom-action-bar .action-group { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.5rem 1rem; border-right: 1px solid #eee; }
        .bottom-action-bar .main-actions { flex-grow: 1; display: flex; }
        .bottom-action-bar .btn-action { flex: 1; padding: 1rem; border: none; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .btn-add-to-cart { background-color: #e9f5ee; color: #2e8b57; }
        .btn-buy-now { background-color: #2e8b57; color: white; }
    </style>
</head>
<body>
    <header class="navbar">
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
                
                <div class="product-price-section">
                    <span class="current-price"><?php echo format_price($product['price']); ?></span>
                    </div>
                
                <div class="info-list">
                    <div class="info-item"><i class="fas fa-check-circle"></i> <span>Garansi Tiba Tepat Waktu</span></div>
                    <div class="info-item"><i class="fas fa-undo"></i> <span>15 Hari Pengembalian</span></div>
                    <div class="info-item"><i class="fas fa-gem"></i> <span>100% Produk Lokal Asli</span></div>
                </div>
            </div>
        </div>

        <div class="card seller-info">
            <img src="https://placehold.co/100x100/2e8b57/ffffff?text=GJ" alt="Logo Toko">
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

    <div style="height: 80px;"></div> <div class="bottom-action-bar">
        <div class="action-group">
            <i class="fas fa-comments"></i>
            <span>Chat</span>
        </div>
        <div class="main-actions">
            <button class="btn-action btn-add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                <i class="fas fa-cart-plus"></i> Masukkan Keranjang
            </button>
            <button class="btn-action btn-buy-now">Beli Sekarang</button>
        </div>
    </div>
    <script>
function addToCart(productId) {
    // Buat objek FormData untuk mengirim data
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_id', productId);

    // Kirim request ke ajax_handler.php
    fetch('ajax_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Tampilkan pesan dari server
        alert(data.message);
        
        // Jika user belum login, arahkan ke halaman login
        if (data.status === 'login_required') {
            window.location.href = 'login.php';
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
</body>
</html>