<?php
require_once 'fungsi.php';
check_login('pembeli');
$user_id = $_SESSION['user_id'];
$message = '';

// Logika untuk akses developer
if (isset($_POST['dev_activate'])) {
    if (activate_dev_paylater($user_id)) {
        $message = "Akses Developer Paylater berhasil diaktifkan!";
    }
}

// Ambil info paylater terbaru
$wallet_info = get_user_wallet_info($user_id);
$paylater_status = $wallet_info['paylater_status'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KreasiLokal Paylater</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 2rem; }
        .container { max-width: 800px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header { text-align: center; margin-bottom: 2rem; }
        .page-header h2 { font-size: 2rem; color: #2e8b57; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background-color: #2e8b57; color: white; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .status-box { padding: 2rem; text-align: center; border-radius: 8px; }
        .status-box.active { background: #e9f5ee; color: #2e8b57; }
        .status-box.pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <nav class="navbar"><a href="profilpembeli.php" style="font-size: 1.5rem; font-weight: bold; color: #2e8b57; text-decoration: none;">KreasiLokal.id</a></nav>
    <div class="container">
        <div class="page-header">
            <i class="fas fa-credit-card fa-2x" style="color: #2e8b57; margin-bottom: 1rem;"></i>
            <h2>KreasiLokal Paylater</h2>
            <p>Belanja sekarang, bayar nanti. Mudah dan cepat.</p>
        </div>

        <?php if ($message): ?>
            <p style="text-align:center; background: #d4edda; padding: 1rem; border-radius: 8px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($paylater_status === 'active'): ?>
            <div class="status-box active">
                <h3>Paylater Anda Aktif!</h3>
                <p>Limit Tersedia</p>
                <h2 style="font-size: 2.5rem; margin: 1rem 0;"><?php echo format_price($wallet_info['paylater_balance']); ?></h2>
                <p>dari total limit <?php echo format_price($wallet_info['paylater_limit']); ?></p>
            </div>
        <?php elseif ($paylater_status === 'pending'): ?>
            <div class="status-box pending">
                <h3>Pengajuan Sedang Ditinjau</h3>
                <p>Tim kami sedang memverifikasi data Anda. Mohon tunggu kabar selanjutnya.</p>
            </div>
        <?php else: // inactive atau rejected ?>
            <form method="POST" enctype="multipart/form-data">
                <p>Untuk mengaktifkan Paylater, kami memerlukan verifikasi data diri Anda. Silakan unggah dokumen yang diperlukan.</p>
                <div class="form-group">
                    <label for="ktp">Upload Foto KTP</label>
                    <input type="file" id="ktp" name="ktp" required>
                </div>
                <div class="form-group">
                    <label for="selfie">Upload Selfie dengan KTP</label>
                    <input type="file" id="selfie" name="selfie" required>
                </div>
                <button type="submit" name="apply_paylater" class="btn btn-primary" style="width: 100%;">Ajukan Sekarang</button>
            </form>

            <form method="POST" style="margin-top: 2rem; text-align: center;">
                <button type="submit" name="dev_activate" class="btn" style="background: #d9534f; color: white;">
                    <i class="fas fa-code"></i> Aktifkan Akses Developer Paylater
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>