<?php
session_start();
require_once 'config/database.php';

$cartItems = [];
$totalPrice = 0;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT p.name, p.price, p.image_url, c.quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keranjang Belanja</title>
    <!-- ... CSS dan Font Awesome ... -->
</head>
<body>
    <header class="navbar">
        <!-- ... Navigasi Anda ... -->
    </header>
    <main class="container">
        <div>
            <div class="card">
                <?php if (empty($cartItems)): ?>
                    <p>Keranjang Anda kosong.</p>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Produk">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <span>Rp <?php echo number_format($item['price']); ?></span>
                        <span>Jumlah: <?php echo htmlspecialchars($item['quantity']); ?></span>
                    </div>
                    <?php $totalPrice += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="checkout-summary">
            <div class="card">
                <h3>Ringkasan Belanja</h3>
                <div class="summary-row">
                    <span>Total</span>
                    <span>Rp <?php echo number_format($totalPrice); ?></span>
                </div>
                <a href="checkout.php" class="btn-checkout">Lanjut ke Checkout</a>
            </div>
        </div>
    </main>
</body>
</html>