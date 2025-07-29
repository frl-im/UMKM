<?php
require_once 'fungsi.php';
check_login('pembeli');

$checkoutData = $_SESSION['checkout_data'] ?? null;
if (!$checkoutData) {
    header('Location: keranjang.php');
    exit;
}
$totalPayment = $checkoutData['total_amount'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/davidshimjs-qrcodejs/qrcode.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        
        /* DESAIN NAVBAR BARU */
        .navbar-post-payment {
            background: #34A853;
            color: white;
            padding: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-container {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 2px solid white;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .nav-link:hover {
            background-color: white;
            color: #34A853;
        }
        /* AKHIR DESAIN NAVBAR */

        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 2rem; }
        .payment-method { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 1rem; overflow: hidden; }
        .payment-header { padding: 1rem; background: #f9f9f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.2s; }
        .payment-header:hover { background: #f0f0f0; }
        .payment-header.active .fa-chevron-down { transform: rotate(180deg); }
        .payment-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, padding 0.3s ease-out; padding: 0 1.5rem; }
        .payment-content.active { max-height: 500px; padding: 1.5rem; border-top: 1px solid #ddd; }
        .payment-options button { width: 100%; display: flex; align-items: center; text-align: left; padding: 1rem; background: #fff; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 0.75rem; cursor: pointer; font-size: 1rem; transition: background-color 0.2s, border-color 0.2s; }
        .payment-options button:hover { background-color: #f8f9fa; border-color: #34A853; }
        .logo { height: 24px; width: 80px; margin-right: 1rem; object-fit: contain; }
        #payment-instructions { margin-top: 2rem; padding: 1.5rem; background: #eef7ff; border: 1px solid #bde0ff; border-radius: 8px; text-align: center; display: none; }
        #qr-code-container { margin: 1rem auto; width: 250px; height: 250px; background: white; padding: 10px; border-radius: 8px; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #34A853; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <header id="post-payment-nav" class="navbar-post-payment" style="display: none;">
        <div class="nav-container">
            <h1 class="nav-title">Selesaikan Pembayaran Anda</h1>
            <a href="index.php" class="nav-link">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        </div>
    </header>

    <div class="container">
        <div id="payment-selection">
            <div class="page-header">
                <h2>Pilih Metode Pembayaran</h2>
                <p>Total Tagihan: <strong><?php echo format_price($totalPayment); ?></strong></p>
            </div>

            <div id="payment-accordion">
                <div class="payment-method"><div class="payment-header" onclick="selectPayment('qris')"><strong><i class="fas fa-qrcode"></i> QRIS (Semua E-Wallet & M-Banking)</strong></div></div>
                <div class="payment-method">
                    <div class="payment-header" data-accordion-trigger><strong><i class="fas fa-wallet"></i> E-Wallet</strong><i class="fas fa-chevron-down"></i></div>
                    <div class="payment-content"><div class="payment-options">
                        <button onclick="selectPayment('gopay')"><img src="https://assets.midtrans.com/assets/images/logo/payment-methods/gopay.svg" alt="GoPay" class="logo"> GoPay</button>
                        <button onclick="selectPayment('shopeepay')"><img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/ShopeePay_logo.svg" alt="ShopeePay" class="logo"> ShopeePay</button>
                    </div></div>
                </div>
                <div class="payment-method">
                    <div class="payment-header" data-accordion-trigger><strong><i class="fas fa-university"></i> Transfer Bank (Virtual Account)</strong><i class="fas fa-chevron-down"></i></div>
                    <div class="payment-content"><div class="payment-options">
                        <button onclick="selectPayment('bca_va')"><img src="https://assets.midtrans.com/assets/images/logo/payment-methods/bca.svg" alt="BCA" class="logo"> BCA Virtual Account</button>
                        <button onclick="selectPayment('bni_va')"><img src="https://assets.midtrans.com/assets/images/logo/payment-methods/bni.svg" alt="BNI" class="logo"> BNI Virtual Account</button>
                        <button onclick="selectPayment('bri_va')"><img src="https://assets.midtrans.com/assets/images/logo/payment-methods/bri.svg" alt="BRI" class="logo"> BRI Virtual Account</button>
                        <button onclick="selectPayment('mandiri_va')"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/1280px-Bank_Mandiri_logo_2016.svg.png" alt="Mandiri" class="logo"> Mandiri Bill Payment</button>
                        <button onclick="selectPayment('cimb_va')"><img src="https://assets.midtrans.com/assets/images/logo/payment-methods/cimb.svg" alt="CIMB" class="logo"> CIMB Virtual Account</button>
                        <button onclick="selectPayment('permata_va')"><img src="https://assets.midtrans.com/assets/images/logo/payment-methods/permata.svg" alt="Permata" class="logo"> Permata & Bank Lainnya</button>
                    </div></div>
                </div>
            </div>
        </div>

        <div id="payment-instructions"></div>
    </div>

<script>
    document.querySelectorAll('[data-accordion-trigger]').forEach(header => {
        header.addEventListener('click', () => {
            header.classList.toggle('active');
            header.nextElementSibling.classList.toggle('active');
        });
    });

    const instructionsContainer = document.getElementById('payment-instructions');
    const paymentSelectionContainer = document.getElementById('payment-selection');
    const postPaymentNav = document.getElementById('post-payment-nav');

    async function selectPayment(method) {
        instructionsContainer.style.display = 'block';
        instructionsContainer.innerHTML = '<div class="loader"></div><p>Membuat tagihan pembayaran...</p>';
        const formData = new FormData();
        formData.append('action', 'create_payment');
        formData.append('payment_type', method);

        try {
            const response = await fetch('ajax/ajax_handler.php', { method: 'POST', body: formData });
            if (!response.ok) { throw new Error(`Server error (${response.status})`); }
            const result = await response.json();
            if (result.status === 'success') { displayPaymentInfo(result.data); } 
            else { throw new Error(result.message || 'Gagal memproses permintaan.'); }
        } catch (error) {
            instructionsContainer.innerHTML = `<p style="color:red;"><b>Terjadi kesalahan.</b><br><small>${error.message}</small><br>Silakan coba lagi.</p>`;
        }
    }

    function displayPaymentInfo(data) {
        // PERUBAHAN UTAMA: TAMPILKAN NAVBAR DAN SEMBUNYIKAN PILIHAN
        postPaymentNav.style.display = 'block';
        paymentSelectionContainer.style.display = 'none';

        let content = `<h3>${data.title}</h3>`;
        if (data.type === 'qris') {
            content += `<p>Pindai kode QR di bawah ini.</p><div id="qr-code-container" style="background: white; padding: 10px; border-radius: 8px; display: inline-block;"></div><p>Kedaluwarsa: <strong>${data.expiry_time}</strong></p>`;
            instructionsContainer.innerHTML = content;
            new QRCode(document.getElementById("qr-code-container"), { text: data.qr_string, width: 250, height: 250 });
        } else if (data.type === 'va') {
            content += `<p>Selesaikan pembayaran ke:</p><h2 style="letter-spacing: 2px; margin: 1rem 0;">${data.va_number}</h2><p>Total: <strong>${data.total}</strong></p><p>Kedaluwarsa: <strong>${data.expiry_time}</strong></p>`;
            instructionsContainer.innerHTML = content;
        } else if (data.type === 'ewallet') {
            content += `<p>Klik untuk membayar dengan ${data.name}.</p><a href="${data.deeplink_url}" style="text-decoration:none; display:inline-block; margin-top:1rem; padding: 1rem 2rem; background: #007bff; color: white; border-radius: 8px;">Lanjutkan ke ${data.name}</a>`;
            instructionsContainer.innerHTML = content;
        }
    }
</script>
</body>
</html>