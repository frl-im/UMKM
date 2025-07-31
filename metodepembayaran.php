<?php
require_once 'fungsi.php';
check_login('pembeli');
$checkoutData = $_SESSION['checkout_data'] ?? null;
if (!$checkoutData) {
    header('Location: keranjang.php');
    exit;
}
$totalPayment = $checkoutData['total_amount'];

// [BARU] Ambil info saldo dan paylater
$wallet_info = get_user_wallet_info($_SESSION['user_id']);
$saldo_cukup = ($wallet_info && $wallet_info['saldo'] >= $totalPayment);
$checkoutData = $_SESSION['checkout_data'] ?? null;
if (!$checkoutData) {
    header('Location: keranjang.php');
    exit;
}
$totalPayment = $checkoutData['total_amount'];
$paylater_bisa_digunakan = ($wallet_info && $wallet_info['paylater_status'] === 'active' && $wallet_info['paylater_balance'] >= $totalPayment);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 2rem; }
        .payment-method { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 1rem; overflow: hidden; }
        .payment-header { padding: 1rem; background: #f9f9f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .payment-header.active .fa-chevron-down { transform: rotate(180deg); }
        .payment-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; padding: 0 1.5rem; }
        .payment-content.active { max-height: 500px; padding: 1.5rem; border-top: 1px solid #ddd; }
        .payment-options button { width: 100%; display: flex; align-items: center; text-align: left; padding: 1rem; background: #fff; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 0.75rem; cursor: pointer; font-size: 1rem; }
        .logo { height: 24px; width: 80px; margin-right: 1rem; object-fit: contain; }
        #payment-status { margin-top: 2rem; padding: 1rem; text-align: center; font-weight: 600; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #34A853; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2>Pilih Metode Pembayaran</h2>
            <p>Total Tagihan: <strong><?php echo format_price($totalPayment); ?></strong></p>
        </div>

         <div class="payment-method">
            <div class="payment-header" style="background: #e9f5ee;">
                <strong><i class="fas fa-wallet"></i> Saldo Akun KreasiLokal</strong>
                <span><?php echo format_price($wallet_info['saldo']); ?></span>
            </div>
            <div class="payment-content active"> <div class="payment-options">
                    <?php if ($saldo_cukup): ?>
                        <button onclick="payWithSaldo()">
                            <i class="fas fa-check-circle" style="color: #2e8b57; margin-right:1rem;"></i> Bayar dengan Saldo
                        </button>
                        <p style="font-size: 0.9rem; text-align: center;">Saldo Anda cukup untuk transaksi ini.</p>
                    <?php else: ?>
                        <button disabled style="cursor: not-allowed; opacity: 0.6;">
                            <i class="fas fa-times-circle" style="color: #d9534f; margin-right:1rem;"></i> Bayar dengan Saldo
                        </button>
                        <p style="font-size: 0.9rem; text-align: center; color: #d9534f;">Saldo Anda tidak mencukupi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="payment-method">
            <div class="payment-header" style="background: #e9f5ee;">
                <strong><i class="fas fa-credit-card"></i> KreasiLokal Paylater</strong>
                <span>Limit: <?php echo format_price($wallet_info['paylater_balance'] ?? 0); ?></span>
            </div>
            <div class="payment-content active">
                <div class="payment-options">
                    <?php if ($paylater_bisa_digunakan): ?>
                        <button onclick="payWithPaylater()">
                            <i class="fas fa-check-circle" style="color: #2e8b57; margin-right:1rem;"></i> Bayar dengan Paylater
                        </button>
                        <p style="font-size: 0.9rem; text-align: center;">Limit Anda cukup untuk transaksi ini.</p>
                    <?php else: ?>
                        <button disabled style="cursor: not-allowed; opacity: 0.6;">
                            <i class="fas fa-times-circle" style="color: #d9534f; margin-right:1rem;"></i> Bayar dengan Paylater
                        </button>
                        <p style="font-size: 0.9rem; text-align: center; color: #d9534f;">
                            <?php echo ($wallet_info['paylater_status'] !== 'active') ? 'Paylater Anda belum aktif.' : 'Limit Paylater tidak mencukupi.'; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div id="payment-accordion">

       <div id="payment-accordion">
    <div class="payment-method">
        <div class="payment-header" data-accordion-trigger><strong><i class="fas fa-university"></i> Transfer Bank (Virtual Account)</strong><i class="fas fa-chevron-down"></i></div>
        <div class="payment-content">
            <div class="payment-options">
                <button onclick="createPayment('BCA')"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia_logo.svg/2560px-Bank_Central_Asia_logo.svg.png" alt="BCA" class="logo"> BCA Virtual Account</button>
                <button onclick="createPayment('BNI')"><img src="https://upload.wikimedia.org/wikipedia/id/thumb/5/55/BNI_logo.svg/1280px-BNI_logo.svg.png" alt="BNI" class="logo"> BNI Virtual Account</button>
                <button onclick="createPayment('BRI')"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/BRI_2020.svg/1280px-BRI_2020.svg.png" alt="BRI" class="logo"> BRI Virtual Account</button>
                <button onclick="createPayment('MANDIRI')"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/1280px-Bank_Mandiri_logo_2016.svg.png" alt="Mandiri" class="logo"> Mandiri Virtual Account</button>
                <button onclick="createPayment('PERMATA')"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/PermataBank_logo.svg/2560px-PermataBank_logo.svg.png" alt="Permata" class="logo"> Permata Virtual Account</button>
            </div>
        </div>
    </div>
    <div class="payment-method">
        <div class="payment-header" data-accordion-trigger><strong><i class="fas fa-wallet"></i> E-Wallet</strong><i class="fas fa-chevron-down"></i></div>
        <div class="payment-content">
            <div class="payment-options">
                <button onclick="createPayment('ID_SHOPEEPAY')"><img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/ShopeePay_logo.svg" alt="ShopeePay" class="logo"> ShopeePay</button>
                <button onclick="createPayment('ID_DANA')"><img src="https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg" alt="DANA" class="logo"> DANA</button>
                <button onclick="createPayment('ID_OVO')"><img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg" alt="OVO" class="logo"> OVO</button>
            </div>
        </div>
    </div>
</div>

        <div id="payment-status"></div>
    </div>

<script>
    // [DIPERBAIKI] Menambahkan ');' yang hilang untuk menutup forEach
    document.querySelectorAll('[data-accordion-trigger]').forEach(header => {
        header.addEventListener('click', () => {
            // Tutup semua konten lain yang terbuka
            document.querySelectorAll('.payment-content.active').forEach(content => {
                if (content !== header.nextElementSibling) {
                    content.classList.remove('active');
                    content.previousElementSibling.classList.remove('active');
                }
            });

            // Buka/tutup yang diklik
            header.classList.toggle('active');
            header.nextElementSibling.classList.toggle('active');
        });
    });

    async function payWithSaldo() {
        if (!confirm('Anda yakin ingin membayar pesanan ini dengan Saldo Akun?')) {
            return;
        }

        const statusContainer = document.getElementById('payment-status');
        statusContainer.innerHTML = '<div class="loader"></div><p>Memproses pembayaran, mohon tunggu...</p>';

        const formData = new FormData();
        formData.append('action', 'pay_with_saldo');

        try {
            const response = await fetch('ajax/ajax_handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = `statuspesanan.php?status=sukses&external_id=${result.data.external_id}`;
            } else {
                statusContainer.innerHTML = `<p style="color:red;"><b>Error:</b> ${result.message}</p>`;
            }
        } catch (error) {
            statusContainer.innerHTML = `<p style="color:red;">Terjadi kesalahan koneksi.</p>`;
        }
    }

    async function payWithPaylater() {
        if (!confirm('Anda yakin ingin membayar pesanan ini dengan Paylater?')) {
            return;
        }

        const statusContainer = document.getElementById('payment-status');
        statusContainer.innerHTML = '<div class="loader"></div><p>Memproses pembayaran, mohon tunggu...</p>';

        const formData = new FormData();
        formData.append('action', 'pay_with_paylater');

        try {
            const response = await fetch('ajax/ajax_handler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = `statuspesanan.php?status=sukses&external_id=${result.data.external_id}`;
            } else {
                statusContainer.innerHTML = `<p style="color:red;"><b>Error:</b> ${result.message}</p>`;
            }
        } catch (error) {
            statusContainer.innerHTML = `<p style="color:red;">Terjadi kesalahan koneksi.</p>`;
        }
    }

    async function createPayment(method) {
        const statusContainer = document.getElementById('payment-status');
        statusContainer.innerHTML = '<div class="loader"></div><p>Membuat tagihan, mohon tunggu...</p>';

        const formData = new FormData();
        formData.append('action', 'create_payment');
        formData.append('payment_method', method);

        try {
            const response = await fetch('ajax/ajax_handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();

            if (result.status === 'success') {
                statusContainer.innerHTML = '<p style="color:green;">Mengalihkan ke halaman pembayaran...</p>';
                window.location.href = result.data.invoice_url;
            } else {
                statusContainer.innerHTML = `<p style="color:red;"><b>Error:</b> ${result.message}</p>`;
            }
        } catch (error) {
            statusContainer.innerHTML = `<p style="color:red;">Terjadi kesalahan koneksi.</p>`;
        }
    }
</script>
</body>
</html>