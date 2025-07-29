<?php
require_once 'fungsi.php';
check_login('penjual');

// Ambil status verifikasi terbaru dari session
$verification_status = $_SESSION['verification_status'] ?? 0;

$status_messages = [
    0 => "Akun Anda belum terverifikasi. Silakan lengkapi data diri Anda untuk melanjutkan.",
    1 => "Verifikasi data diri berhasil! Lanjutkan dengan melengkapi informasi toko Anda.",
    2 => "Informasi toko berhasil disimpan! Upload produk pertama Anda untuk mengaktifkan toko.",
    3 => "Selamat! Toko Anda sudah aktif sepenuhnya dan siap untuk berjualan."
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Menggunakan CSS yang sama persis dengan profilpembeli.php untuk konsistensi */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        .profile-header {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .profile-avatar {
            font-size: 3rem;
            color: #fff;
            background-color: #34A853;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
        }
        .profile-info h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .profile-info p {
            margin: 0.25rem 0 0 0;
            color: #6c757d;
        }
        .menu-section {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .section-title {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        .section-title h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        .top-nav {
            background-color: #34A853;
            padding: 0.8rem 1rem;
            text-align: right;
        }
        .top-nav a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            font-size: 0.95rem;
        }
        .action-button {
            display: inline-block;
            background-color: #34A853;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        .action-button:hover {
            background-color: #2a8747;
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <div style="max-width: 960px; margin: 0 auto; padding: 0 1.5rem;">
             <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
             <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-store"></i>
            </div>
            <div class="profile-info">
                <h2><?php echo safe_output($_SESSION['store_name'] ?? 'Nama Toko'); ?></h2>
                <p>Pemilik: <?php echo safe_output($_SESSION['user_name']); ?></p>
            </div>
        </div>

        <div class="menu-section">
            <div class="section-title">
                <h3>Status Verifikasi Akun</h3>
            </div>
            <p><?php echo $status_messages[$verification_status]; ?></p>
            
            <?php if ($verification_status == 0): ?>
                <a href="verifikasi-data-diri.php" class="action-button">
                    <i class="fas fa-id-card"></i> Lakukan Verifikasi Data Diri
                </a>
            <?php elseif ($verification_status == 1): ?>
                <a href="informasi_toko.php" class="action-button">
                    <i class="fas fa-info-circle"></i> Lengkapi Informasi Toko
                </a>
            <?php elseif ($verification_status >= 2): ?>
                <a href="upload_produk.php" class="action-button">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
                </a>
            <?php endif; ?>
        </div>
        
        <div class="menu-section">
            <div class="section-title">
                <h3>Manajemen Toko</h3>
            </div>
            <p>Menu untuk melihat produk, pesanan masuk, dan lainnya akan muncul di sini setelah toko Anda aktif.</p>
        </div>
    </div>

</body>
</html>