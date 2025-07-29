<?php
require_once 'fungsi.php';
// Gunakan check_login untuk melindungi halaman dan memulai sesi
check_login('pembeli');

// Fungsi ambil_isi_keranjang() harus sudah ada di fungsi.php
$cartItems = ambil_isi_keranjang($_SESSION['user_id']);
$totalPrice = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - KreasiLokal.id</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .navbar {
            background: #34A853; /* Warna hijau khas Anda */
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            align-items: flex-start;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 80px 1fr auto auto auto;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .item-info h4 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .item-price {
            font-weight: 600;
            color: #34A853;
            font-size: 1.1rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            background: #34A853;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        
        .quantity-btn:hover {
            background: #2a8747;
        }
        
        .quantity-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.25rem;
        }
        
        .remove-btn {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .remove-btn:hover {
            background: #c53030;
        }
        
        .checkout-summary {
            position: sticky;
            top: 2rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.2rem;
            color: #34A853;
        }
        
        .btn-checkout {
            display: block;
            width: 100%;
            background: #34A853;
            color: white;
            text-decoration: none;
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        .loading {
            opacity: 0.5;
            pointer-events: none;
        }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
            .cart-item { grid-template-columns: 60px 1fr auto; grid-template-areas: "img info remove" "qty qty qty"; }
            .cart-item img { grid-area: img; }
            .item-info { grid-area: info; }
            .remove-btn { grid-area: remove; }
            .quantity-controls { grid-area: qty; justify-content: flex-start; margin-top: 1rem; }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div style="max-width: 1200px; margin: 0 auto; width: 100%; padding: 0 1rem;">
            <nav style="display: flex; justify-content: space-between; align-items: center; color: white;">
                <h1><a href="index.php" style="color:white; text-decoration:none;"><i class="fas fa-leaf"></i> KreasiLokal.id</a></h1>
                <div>
                    <a href="index.php" style="color: white; text-decoration: none; margin-right: 1rem;">
                        <i class="fas fa-home"></i> Beranda
                    </a>
                    <a href="chat.php" style="color: white; text-decoration: none;">
                        <i class="fas fa-comments"></i> Chat
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <div>
            <div class="card">
                <div id="alert-container"></div>
                
                <h2 style="margin-bottom: 1.5rem;">
                    <i class="fas fa-shopping-cart"></i> Keranjang Belanja
                </h2>
                
                <div id="cart-items">
                    <?php if (empty($cartItems)): ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Keranjang Anda kosong</h3>
                            <p>Mulai berbelanja dan tambahkan produk ke keranjang Anda.</p>
                            <a href="index.php" class="btn-checkout" style="margin-top: 1rem; display: inline-block; width: auto; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-shopping-bag"></i> Mulai Belanja
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                            <img src="<?php echo safe_output($item['image_url']); ?>" alt="<?php echo safe_output($item['name']); ?>">
                            
                            <div class="item-info">
                                <h4><?php echo safe_output($item['name']); ?></h4>
                                <div class="item-price"><?php echo format_price($item['price']); ?></div>
                                <small style="color: #666;">Stok: <?php echo $item['stock']; ?></small>
                            </div>
                            
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)" 
                                        <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['stock']; ?>"
                                       onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                        <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            
                            <div class="item-total">
                                <span class="item-total-price"><?php echo format_price($item['price'] * $item['quantity']); ?></span>
                            </div>
                            
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <?php $totalPrice += $item['price'] * $item['quantity']; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($cartItems)): ?>
        <div class="checkout-summary">
            <div class="card">
                <h3 style="margin-bottom: 1rem;">
                    <i class="fas fa-receipt"></i> Ringkasan Belanja
                </h3>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal"><?php echo format_price($totalPrice); ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim</span>
                    <span>Gratis</span>
                </div>
                <div class="summary-row">
                    <span>Total</span>
                    <span id="total"><?php echo format_price($totalPrice); ?></span>
                </div>
                
                <a href="checkout.php" class="btn-checkout" id="checkout-btn">
                    <i class="fas fa-credit-card"></i> Lanjut ke Checkout
                </a>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        // ... (Kode JavaScript lengkap Anda untuk update, remove, dan kalkulasi total) ...
        // PASTIKAN SEMUA FETCH MENGARAH KE 'ajax/ajax_handler.php'
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) return;
            
            fetch('ajax/ajax_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_cart&product_id=${productId}&quantity=${newQuantity}`
            })
            .then(res => res.json()).then(data => {
                if(data.status === 'success') {
                    location.reload(); // Reload halaman untuk update total
                } else {
                    alert(data.message);
                }
            });
        }

        function removeFromCart(productId) {
            if (!confirm('Hapus item ini dari keranjang?')) return;
            
            fetch('ajax/ajax_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove_from_cart&product_id=${productId}`
            })
            .then(res => res.json()).then(data => {
                if(data.status === 'success') {
                    location.reload(); // Reload halaman
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html>