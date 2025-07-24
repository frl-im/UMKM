<?php
session_start();
require_once 'config/database.php';
require_once 'fungsi.php';


// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$cartItems = [];
$totalPrice = 0;
$userId = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.image_url, p.seller_id, c.quantity,
           u.fullname as seller_name
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    JOIN users u ON p.seller_id = u.id
    WHERE c.user_id = ? AND p.status = 'active'
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: keranjang.php');
    exit;
}

// Calculate total
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KreasiDB</title>
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
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .payment-method {
            position: relative;
        }
        
        .payment-method input[type="radio"] {
            display: none;
        }
        
        .payment-method label {
            display: block;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        
        .payment-method input[type="radio"]:checked + label {
            border-color: #667eea;
            background: #f7fafc;
            color: #667eea;
        }
        
        .payment-method i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .item-seller {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .item-price {
            color: #667eea;
            font-weight: 600;
        }
        
        .item-quantity {
            font-size: 0.9rem;
            color: #666;
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
            border-top: 2px solid #667eea;
            margin-top: 0.5rem;
            padding-top: 1rem;
        }
        
        .btn-order {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 1rem;
        }
        
        .btn-order:hover {
            transform: translateY(-2px);
        }
        
        .btn-order:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .crypto-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
        }
        
        .crypto-info.show {
            display: block;
        }
        
        .wallet-address {
            background: #fff;
            padding: 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9rem;
            border: 1px solid #ddd;
            margin-top: 0.5rem;
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
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
            <nav style="display: flex; justify-content: space-between; align-items: center; color: white;">
                <h1><i class="fas fa-credit-card"></i> Checkout</h1>
                <div>
                    <a href="keranjang.php" style="color: white; text-decoration: none; margin-right: 1rem;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <div>
            <form id="checkout-form">
                <div class="card">
                    <h3><i class="fas fa-user"></i> Informasi Pengiriman</h3>
                    
                    <div class="form-group">
                        <label for="fullname">Nama Lengkap</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" 
                               value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shipping_address">Alamat Lengkap</label>
                        <textarea id="shipping_address" name="shipping_address" class="form-control" 
                                  rows="4" placeholder="Masukkan alamat lengkap untuk pengiriman..." required></textarea>
                    </div>
                </div>
                
                <div class="card">
                    <h3><i class="fas fa-credit-card"></i> Metode Pembayaran</h3>
                    
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="bank" name="payment_method" value="bank_transfer" required>
                            <label for="bank">
                                <i class="fas fa-university"></i>
                                Transfer Bank
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" id="ewallet" name="payment_method" value="e_wallet" required>
                            <label for="ewallet">
                                <i class="fas fa-wallet"></i>
                                E-Wallet
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" id="qris" name="payment_method" value="qris" required>
                            <label for="qris">
                                <i class="fas fa-qrcode"></i>
                                QRIS
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" id="crypto" name="payment_method" value="cryptocurrency" required>
                            <label for="crypto">
                                <i class="fab fa-bitcoin"></i>
                                Crypto
                            </label>
                        </div>
                    </div>
                    
                    <div id="crypto-info" class="crypto-info">
                        <h4><i class="fab fa-bitcoin"></i> Pembayaran Cryptocurrency</h4>
                        <p>Kirim pembayaran ke alamat wallet berikut:</p>
                        <div class="wallet-address">
                            <strong>Bitcoin (BTC):</strong><br>
                            1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa
                        </div>
                        <div class="wallet-address">
                            <strong>Ethereum (ETH):</strong><br>
                            0x742d35Cc6336C2a70b8d2e0c7ad8a5F7A7bBA2C3
                        </div>
                        <p><small><i class="fas fa-info-circle"></i> Screenshot bukti transfer akan diminta setelah order dibuat.</small></p>
                    </div>
                </div>
            </form>
        </div>
        
        <div>
            <div class="card">
                <h3><i class="fas fa-receipt"></i> Ringkasan Pesanan</h3>
                
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-seller">Penjual: <?php echo htmlspecialchars($item['seller_name']); ?></div>
                            <div class="item-price">Rp <?php echo number_format($item['price']); ?></div>
                        </div>
                        <div class="item-quantity">
                            x<?php echo $item['quantity']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal (<?php echo count($cartItems); ?> item)</span>
                        <span>Rp <?php echo number_format($totalPrice); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span>Gratis</span>
                    </div>
                    <div class="summary-row">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($totalPrice); ?></span>
                    </div>
                </div>
                
                <button type="submit" form="checkout-form" class="btn-order" id="order-btn">
                    <i class="fas fa-shopping-cart"></i> Buat Pesanan
                </button>
            </div>
        </div>
    </main>

    <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>

    <script>
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            alertContainer.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Show/hide crypto info
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const cryptoInfo = document.getElementById('crypto-info');
                if (this.value === 'cryptocurrency') {
                    cryptoInfo.classList.add('show');
                } else {
                    cryptoInfo.classList.remove('show');
                }
            });
        });

        // Handle form submission
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_order');
            
            const orderBtn = document.getElementById('order-btn');
            orderBtn.disabled = true;
            orderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            fetch('app_logic.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showAlert('success', 'Pesanan berhasil dibuat! Mengarahkan ke halaman pembayaran...');
                    
                    setTimeout(() => {
                        window.location.href = `order_detail.php?id=${data.data.order_id}`;
                    }, 2000);
                } else {
                    showAlert('error', data.message);
                    orderBtn.disabled = false;
                    orderBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Buat Pesanan';
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan jaringan');
                orderBtn.disabled = false;
                orderBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Buat Pesanan';
                console.error('Error:', error);
            });
        });
        
        // Auto-format phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.startsWith('0')) {
                value = '+62' + value.substring(1);
            } else if (!value.startsWith('+62')) {
                value = '+62' + value;
            }
            e.target.value = value;
        });
    </script>
</body>
</html>