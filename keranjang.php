<?php
session_start();
require_once 'config/database.php';

$cartItems = [];
$totalPrice = 0;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.price, p.image_url, p.stock, c.quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
            font-size: 1.1rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            background: #667eea;
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
            background: #5a67d8;
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
            height: fit-content;
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
            color: #667eea;
        }
        
        .btn-checkout {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .cart-item {
                grid-template-columns: 60px 1fr;
                gap: 0.5rem;
            }
            
            .quantity-controls {
                grid-column: 1 / -1;
                justify-content: space-between;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <nav style="display: flex; justify-content: space-between; align-items: center; color: white;">
                <h1><i class="fas fa-shopping-cart"></i> KreasiDB</h1>
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
                            <p>Mulai berbelanja dan tambahkan produk ke keranjang Anda</p>
                            <a href="index.php" class="btn-checkout" style="margin-top: 1rem; display: inline-block; width: auto; padding: 0.75rem 1.5rem;">
                                <i class="fas fa-shopping-bag"></i> Mulai Belanja
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <div class="item-price">Rp <?php echo number_format($item['price']); ?></div>
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
                                Rp <span class="item-total-price"><?php echo number_format($item['price'] * $item['quantity']); ?></span>
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
                    <span id="subtotal">Rp <?php echo number_format($totalPrice); ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim</span>
                    <span>Gratis</span>
                </div>
                <div class="summary-row">
                    <span>Total</span>
                    <span id="total">Rp <?php echo number_format($totalPrice); ?></span>
                </div>
                
                <a href="checkout.php" class="btn-checkout" id="checkout-btn">
                    <i class="fas fa-credit-card"></i> Lanjut ke Checkout
                </a>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    ${message}
                </div>
            `;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) return;
            
            const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
            cartItem.classList.add('loading');
            
            const formData = new FormData();
            formData.append('action', 'update_cart');
            formData.append('product_id', productId);
            formData.append('quantity', newQuantity);
            
            fetch('app_logic.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                cartItem.classList.remove('loading');
                
                if (data.status === 'success') {
                    // Update quantity input
                    const quantityInput = cartItem.querySelector('.quantity-input');
                    quantityInput.value = newQuantity;
                    
                    // Update item total
                    const price = parseFloat(cartItem.querySelector('.item-price').textContent.replace(/[^0-9]/g, ''));
                    const itemTotal = cartItem.querySelector('.item-total-price');
                    itemTotal.textContent = (price * newQuantity).toLocaleString('id-ID');
                    
                    // Update buttons state
                    const minusBtn = cartItem.querySelector('.quantity-btn');
                    const plusBtn = cartItem.querySelectorAll('.quantity-btn')[1];
                    const maxQuantity = parseInt(cartItem.querySelector('.quantity-input').getAttribute('max'));
                    
                    minusBtn.disabled = newQuantity <= 1;
                    plusBtn.disabled = newQuantity >= maxQuantity;
                    
                    // Recalculate total
                    calculateTotal();
                    
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                cartItem.classList.remove('loading');
                showAlert('error', 'Terjadi kesalahan jaringan');
                console.error('Error:', error);
            });
        }

        function removeFromCart(productId) {
            if (!confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
                return;
            }
            
            const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
            cartItem.classList.add('loading');
            
            const formData = new FormData();
            formData.append('action', 'remove_from_cart');
            formData.append('product_id', productId);
            
            fetch('app_logic.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    cartItem.remove();
                    calculateTotal();
                    
                    // Check if cart is empty
                    const remainingItems = document.querySelectorAll('.cart-item');
                    if (remainingItems.length === 0) {
                        location.reload(); // Reload to show empty cart message
                    }
                    
                    showAlert('success', data.message);
                } else {
                    cartItem.classList.remove('loading');
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                cartItem.classList.remove('loading');
                showAlert('error', 'Terjadi kesalahan jaringan');
                console.error('Error:', error);
            });
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.item-price').textContent.replace(/[^0-9]/g, ''));
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                total += price * quantity;
            });
            
            document.getElementById('subtotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Auto-save quantity changes after typing
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity-input')) {
                const productId = e.target.closest('.cart-item').getAttribute('data-product-id');
                const newQuantity = parseInt(e.target.value);
                const maxQuantity = parseInt(e.target.getAttribute('max'));
                
                if (newQuantity > 0 && newQuantity <= maxQuantity) {
                    clearTimeout(e.target.saveTimeout);
                    e.target.saveTimeout = setTimeout(() => {
                        updateQuantity(productId, newQuantity);
                    }, 1000);
                }
            }
        });
    </script>
</body>
</html>