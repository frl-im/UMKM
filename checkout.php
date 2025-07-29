<?php
require_once 'fungsi.php';
check_login('pembeli');
$cartItems = ambil_isi_keranjang($_SESSION['user_id']);
if (empty($cartItems)) {
    header('Location: keranjang.php');
    exit;
}

$totalPrice = 0;
$totalWeight = 0; // untuk perhitungan ongkir
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
    $totalWeight += ($item['weight'] ?? 100) * $item['quantity']; // default 100g per item jika tidak ada data berat
}

$user = get_user_by_id($_SESSION['user_id']);
$primaryAddress = get_primary_address($_SESSION['user_id']);

// Estimasi biaya kirim berdasarkan berat (dalam gram)
function calculateShippingCost($weight, $service) {
    $weightKg = ceil($weight / 1000); // pembulatan ke atas ke kg
    
    switch($service) {
        case 'jne_reg':
            return max(15000, $weightKg * 9000); // minimal 15rb, 9rb per kg
        case 'jne_oke':
            return max(12000, $weightKg * 7000); // minimal 12rb, 7rb per kg
        case 'sicepat_reg':
            return max(14000, $weightKg * 8500); // minimal 14rb, 8.5rb per kg
        case 'sicepat_halu':
            return max(8000, $weightKg * 5000); // minimal 8rb, 5rb per kg
        case 'pos_reg':
            return max(13000, $weightKg * 8000); // minimal 13rb, 8rb per kg
        default:
            return 15000;
    }
}

$shippingOptions = [
    'jne_reg' => [
        'name' => 'JNE Reguler',
        'duration' => '2-3 hari',
        'cost' => calculateShippingCost($totalWeight, 'jne_reg')
    ],
    'jne_oke' => [
        'name' => 'JNE OKE',
        'duration' => '3-4 hari', 
        'cost' => calculateShippingCost($totalWeight, 'jne_oke')
    ],
    'sicepat_reg' => [
        'name' => 'SiCepat REG',
        'duration' => '2-3 hari',
        'cost' => calculateShippingCost($totalWeight, 'sicepat_reg')
    ],
    'sicepat_halu' => [
        'name' => 'SiCepat HALU',
        'duration' => '4-7 hari',
        'cost' => calculateShippingCost($totalWeight, 'sicepat_halu')
    ],
    'pos_reg' => [
        'name' => 'Pos Indonesia Reguler',
        'duration' => '3-5 hari',
        'cost' => calculateShippingCost($totalWeight, 'pos_reg')
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KreasiLokal.id</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa; 
            color: #333; 
            margin: 0; 
            line-height: 1.6;
        }
        
        .navbar { 
            background: #34A853; 
            padding: 1rem 0; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 1rem; 
            width: 100%;
        }
        
        .nav-content {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            color: white;
        }
        
        .nav-title {
            font-size: 1.5rem;
            margin: 0;
        }
        
        .nav-back {
            color: white; 
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        
        .nav-back:hover {
            background-color: rgba(255,255,255,0.1);
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
            margin-bottom: 1.5rem; 
        }
        
        .card h3 { 
            margin: 0 0 1.5rem 0; 
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-info {
            background: #f8f9ff; 
            padding: 1.5rem; 
            border-radius: 12px; 
            border: 2px solid #e3f2fd; 
            margin-bottom: 1rem;
        }
        
        .user-info h4 {
            margin: 0 0 1rem 0;
            color: #1976d2;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-detail {
            margin: 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-detail i {
            width: 20px;
            color: #666;
        }
        
        .address-display { 
            background: #f0f8f0; 
            padding: 1.5rem; 
            border-radius: 12px; 
            border: 2px solid #c8e6c9; 
            position: relative;
        }
        
        .address-display h4 {
            margin: 0 0 1rem 0;
            color: #2e7d32;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .address-display p { 
            margin: 0.5rem 0; 
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .address-display p i {
            width: 20px;
            color: #666;
        }
        
        .change-address-btn { 
            font-size: 0.9rem; 
            color: #34A853; 
            text-decoration: none; 
            font-weight: 600; 
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            border: 2px solid #34A853;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .change-address-btn:hover {
            background: #34A853;
            color: white;
        }
        
        .shipping-option { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 1.25rem; 
            border: 2px solid #eee; 
            border-radius: 12px; 
            margin-bottom: 1rem; 
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .shipping-option:hover {
            border-color: #34A853;
            background: #f8fff8;
        }
        
        .shipping-option.selected {
            border-color: #34A853;
            background: #f0f8f0;
        }
        
        .shipping-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .shipping-info {
            flex: 1;
        }
        
        .shipping-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .shipping-duration {
            color: #666;
            font-size: 0.9rem;
        }
        
        .shipping-cost {
            font-weight: 700;
            color: #34A853;
            font-size: 1.1rem;
        }
        
        .voucher-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #eee;
        }
        
        .voucher-input { 
            display: flex; 
            gap: 0.75rem; 
            margin-top: 0.75rem;
        }
        
        .voucher-input input { 
            flex: 1; 
            padding: 0.875rem; 
            border: 2px solid #ddd; 
            border-radius: 8px; 
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .voucher-input input:focus {
            outline: none;
            border-color: #34A853;
        }
        
        .btn-apply-voucher { 
            padding: 0.875rem 1.5rem; 
            background: #555; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .btn-apply-voucher:hover {
            background: #333;
        }
        
        .order-items {
            margin-bottom: 1.5rem;
        }
        
        .order-item {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .item-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: 700;
            color: #2c3e50;
        }
        
        .summary-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 0.875rem 0; 
            border-bottom: 1px solid #eee; 
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .discount-row { 
            color: #d9534f; 
            font-weight: 600; 
        }
        
        .total-row { 
            border-top: 2px solid #333; 
            font-weight: bold; 
            font-size: 1.3rem; 
            color: #34A853; 
            padding-top: 1rem; 
            margin-top: 0.5rem;
        }
        
        .btn-order { 
            width: 100%; 
            background: #34A853; 
            color: white; 
            border: none; 
            padding: 1.25rem; 
            border-radius: 12px; 
            font-size: 1.1rem; 
            font-weight: 600; 
            cursor: pointer; 
            margin-top: 1.5rem; 
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-order:hover:not(:disabled) {
            background: #2e7d32;
        }
        
        .btn-order:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .weight-info {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #ffeaa7;
        }
        
        .weight-info i {
            color: #856404;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .nav-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .nav-title {
                font-size: 1.25rem;
            }
            
            .card {
                padding: 1rem;
            }
            
            .shipping-option {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .voucher-input {
                flex-direction: column;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .summary-row {
                font-size: 0.9rem;
            }
            
            .total-row {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }
            
            .card {
                padding: 0.75rem;
            }
            
            .nav-container {
                padding: 0 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <nav class="nav-content">
                <h1 class="nav-title"><i class="fas fa-credit-card"></i> Checkout</h1>
                <a href="keranjang.php" class="nav-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                </a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div>
            <form id="checkout-form" method="POST">
                <div class="card">
                    <h3><i class="fas fa-user"></i> Informasi Pembeli</h3>
                    <div class="user-info">
                        <h4><i class="fas fa-id-card"></i> Data Diri</h4>
                        <div class="user-detail">
                            <i class="fas fa-user"></i>
                            <span><strong><?php echo safe_output($user['name'] ?? $user['fullname']); ?></strong></span>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo safe_output($user['email']); ?></span>
                        </div>
                        <?php if (!empty($user['phone'])): ?>
                        <div class="user-detail">
                            <i class="fas fa-phone"></i>
                            <span><?php echo safe_output($user['phone']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="user-detail">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Bergabung sejak <?php echo date('d M Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</h3>
                    <div id="address-info" class="address-display">
                        <?php if ($primaryAddress): ?>
                            <h4><i class="fas fa-home"></i> <?php echo safe_output($primaryAddress['label']); ?></h4>
                            <p><i class="fas fa-user"></i> <strong><?php echo safe_output($primaryAddress['recipient_name']); ?></strong></p>
                            <p><i class="fas fa-phone"></i> <?php echo safe_output($primaryAddress['phone']); ?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo safe_output($primaryAddress['full_address']); ?></p>
                            <input type="hidden" name="shipping_address" value="<?php echo safe_output($primaryAddress['full_address']); ?>">
                            <input type="hidden" name="recipient_name" value="<?php echo safe_output($primaryAddress['recipient_name']); ?>">
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: #d9534f;">
                                <h4>Alamat Pengiriman Belum Diatur</h4>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="alamat.php" class="change-address-btn">
                        <i class="fas fa-edit"></i> Ubah atau Pilih Alamat Lain
                    </a>
                </div>

                <div class="card">
                    <h3><i class="fas fa-truck"></i> Opsi Pengiriman</h3>
                    <div id="shipping-options">
                        <?php foreach ($shippingOptions as $key => $option): ?>
                        <label class="shipping-option" for="shipping_<?php echo $key; ?>">
                            <input type="radio" name="shipping_method" value="<?php echo $key; ?>" 
                                   id="shipping_<?php echo $key; ?>" 
                                   data-cost="<?php echo $option['cost']; ?>"
                                   <?php echo $key === 'jne_reg' ? 'checked' : ''; ?>>
                            <div class="shipping-info">
                                <div class="shipping-name"><?php echo $option['name']; ?></div>
                                <div class="shipping-duration">Estimasi: <?php echo $option['duration']; ?></div>
                            </div>
                            <div class="shipping-cost"><?php echo format_price($option['cost']); ?></div>
                        </label>
                        <?php endforeach; ?>
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
                            <div class="item-info">
                                <div class="item-name"><?php echo safe_output($item['name']); ?></div>
                                <div class="item-details">
                                    Qty: <?php echo $item['quantity']; ?> × <?php echo format_price($item['price']); ?>
                                </div>
                            </div>
                            <div class="item-price"><?php echo format_price($item['price'] * $item['quantity']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="voucher-section">
                    <label for="voucher_code"><i class="fas fa-ticket-alt"></i> <strong>Kode Voucher</strong></label>
                    <div class="voucher-input">
                        <input type="text" id="voucher_code" name="voucher_code" placeholder="Masukkan kode voucher">
                        <button type="button" class="btn-apply-voucher" onclick="applyVoucher()">Terapkan</button>
                    </div>
                    <small id="voucher-message" style="margin-top:0.75rem; display:block;"></small>
                </div>
                
                <div class="order-summary" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #eee;">
                    <div class="summary-row">
                        <span>Subtotal Produk</span>
                        <span id="subtotal"><?php echo format_price($totalPrice); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span id="shipping-cost"><?php echo format_price($shippingOptions['jne_reg']['cost']); ?></span>
                    </div>
                    <div class="summary-row discount-row" id="discount-row" style="display:none;">
                        <span>Diskon Voucher</span>
                        <span id="discount-amount">- Rp 0</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Pembayaran</span>
                        <span id="total-payment"><?php echo format_price($totalPrice + $shippingOptions['jne_reg']['cost']); ?></span>
                    </div>
                </div>
                <button type="submit" form="checkout-form" class="btn-order" id="order-btn" <?php if (!$primaryAddress) echo 'disabled'; ?>>
                    <i class="fas fa-shield-alt"></i> Bayar Sekarang
                </button>
            </div>
        </div>
    </main>

    <script>
        // Variabel global untuk data ringkasan
        let subtotal = <?php echo $totalPrice; ?>;
        let shippingCost = <?php echo $shippingOptions['jne_reg']['cost']; ?>;
        let discount = 0;
        let appliedVoucher = null;

        // Fungsi pembantu untuk memformat harga
        function formatPrice(price) {
            return 'Rp ' + parseInt(price).toLocaleString('id-ID');
        }

        // Fungsi untuk mengupdate total pembayaran
        function updateTotal() {
            const total = subtotal + shippingCost - discount;
            document.getElementById('total-payment').textContent = formatPrice(total);
        }

        // Event listener untuk pilihan pengiriman
        document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.shipping-option').forEach(option => option.classList.remove('selected'));
                this.closest('.shipping-option').classList.add('selected');
                
                shippingCost = parseInt(this.dataset.cost);
                document.getElementById('shipping-cost').textContent = formatPrice(shippingCost);
                updateTotal();
            });
        });

        // Atur pilihan awal yang 'checked'
        document.querySelector('input[name="shipping_method"]:checked').closest('.shipping-option').classList.add('selected');

        // Fungsi untuk menerapkan voucher
        function applyVoucher() {
            const voucherCode = document.getElementById('voucher_code').value.trim();
            const voucherMessage = document.getElementById('voucher-message');
            const applyBtn = document.querySelector('.btn-apply-voucher');

            if (!voucherCode) { return; }

            applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            applyBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'apply_voucher');
            formData.append('voucher_code', voucherCode);

            fetch('ajax/ajax_handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                voucherMessage.style.color = data.status === 'success' ? 'green' : 'red';
                voucherMessage.innerHTML = data.message;
                
                if (data.status === 'success') {
                    const voucher = data.data;
                    appliedVoucher = voucher;
                    
                    if (voucher.discount_type === 'fixed') {
                        discount = parseFloat(voucher.discount_value);
                    } else if (voucher.discount_type === 'percentage') {
                        discount = Math.min(subtotal * (parseFloat(voucher.discount_value) / 100), parseFloat(voucher.max_discount || discount));
                    }
                    
                    document.getElementById('discount-row').style.display = 'flex';
                    document.getElementById('discount-amount').textContent = '- ' + formatPrice(discount);
                    
                    document.getElementById('voucher_code').disabled = true;
                    applyBtn.innerHTML = 'Hapus';
                    applyBtn.onclick = removeVoucher;
                } else {
                    discount = 0;
                    appliedVoucher = null;
                    document.getElementById('discount-row').style.display = 'none';
                }
                updateTotal();
            })
            .finally(() => {
                if (!appliedVoucher) {
                    applyBtn.innerHTML = 'Terapkan';
                    applyBtn.disabled = false;
                }
            });
        }
        
        // Fungsi untuk menghapus voucher
        function removeVoucher() {
            discount = 0;
            appliedVoucher = null;
            document.getElementById('discount-row').style.display = 'none';
            document.getElementById('voucher_code').value = '';
            document.getElementById('voucher_code').disabled = false;
            document.getElementById('voucher-message').textContent = '';
            
            const applyBtn = document.querySelector('.btn-apply-voucher');
            applyBtn.innerHTML = 'Terapkan';
            applyBtn.onclick = applyVoucher;
            
            updateTotal();
        }

        // ===================================================================================
        // INI ADALAH BAGIAN UTAMA YANG DIPERBAIKI UNTUK MENGATASI MASALAH REDIRECT
        // ===================================================================================
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah form submit secara normal

            const orderBtn = document.getElementById('order-btn');
            orderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            orderBtn.disabled = true;

            const formData = new FormData(this);
            formData.append('action', 'prepare_checkout');
            
            // Tambahkan data total yang sudah dihitung oleh JavaScript
            formData.append('total_amount', subtotal + shippingCost - discount);
            formData.append('shipping_cost', shippingCost);
            formData.append('discount_amount', discount);
            if (appliedVoucher) {
                formData.append('voucher_code_applied', appliedVoucher.code);
            }
            formData.append('total_weight', <?php echo $totalWeight; ?>);

            // Kirim data ke ajax_handler untuk disimpan di session
            fetch('ajax/ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Jika BERHASIL disimpan, baru arahkan ke halaman pembayaran
                    window.location.href = 'metodepembayaran.php';
                } else {
                    // Jika GAGAL, tampilkan pesan error
                    alert('Terjadi kesalahan: ' + data.message);
                    orderBtn.innerHTML = '<i class="fas fa-shield-alt"></i> Bayar Sekarang';
                    orderBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Tidak dapat terhubung ke server. Silakan coba lagi.');
                orderBtn.innerHTML = '<i class="fas fa-shield-alt"></i> Bayar Sekarang';
                orderBtn.disabled = false;
            });
        });
    </script>
</body>
</html>