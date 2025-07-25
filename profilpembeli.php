<?php
// profilpembeli.php
require_once 'fungsi.php';
// Pastikan hanya pembeli yang bisa akses
check_login('pembeli');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
        /* Profile Header */
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
        /* Menu Section */
        .menu-section {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        .section-title h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        .section-title a {
            font-size: 0.9rem;
            color: #34A853;
            text-decoration: none;
            font-weight: 500;
        }
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1.5rem;
            text-align: center;
        }
        .icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #333;
            transition: color 0.3s;
        }
        .icon-item:hover {
            color: #34A853;
        }
        .icon-item .icon {
            font-size: 1.8rem;
            color: #34A853;
            margin-bottom: 0.75rem;
        }
        .icon-item span {
            font-size: 0.95rem;
        }

        /* Top Navigation (Simple) */
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
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h2><?php echo safe_output($_SESSION['user_name']); ?></h2>
                <p><?php echo safe_output($_SESSION['user_email']); ?></p>
            </div>
        </div>

        <div class="menu-section">
            <div class="section-title">
                <h3>Pesanan Saya</h3>
                <a href="riwayat-pesanan.php">Lihat Riwayat Pesanan <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="icon-grid">
                <a href="pesanan.php" class="icon-item">
                    <i class="fas fa-wallet icon"></i>
                    <span>Belum Bayar</span>
                </a>
                <a href="pesanan.php" class="icon-item">
                    <i class="fas fa-box-open icon"></i>
                    <span>Dikemas</span>
                </a>
                <a href="pesanan.php" class="icon-item">
                    <i class="fas fa-truck icon"></i>
                    <span>Dikirim</span>
                </a>
                <a href="#" class="icon-item">
                    <i class="fas fa-star icon"></i>
                    <span>Beri Penilaian</span>
                </a>
            </div>
        </div>

        <div class="menu-section">
            <div class="section-title">
                <h3>Dompet Saya</h3>
            </div>
            <div class="icon-grid">
                <a href="saldo.php" class="icon-item">
                    <i class="fas fa-money-bill-wave icon"></i>
                    <span>Saldo Akun</span>
                </a>
                <a href="#" class="icon-item">
                    <i class="fas fa-tags icon"></i>
                    <span>Voucher Saya</span>
                </a>
                <a href="metodepembayaran.php" class="icon-item">
                    <i class="fas fa-credit-card icon"></i>
                    <span>Metode Pembayaran</span>
                </a>
            </div>
        </div>

        <div class="menu-section">
            <div class="section-title">
                <h3>Akun Saya</h3>
            </div>
            <div class="icon-grid">
                <a href="ubahprofil.php" class="icon-item">
                    <i class="fas fa-user-edit icon"></i>
                    <span>Ubah Profil</span>
                </a>
                <a href="alamat.php" class="icon-item">
                    <i class="fas fa-map-marker-alt icon"></i>
                    <span>Alamat Pengiriman</span>
                </a>
                <a href="wishlist.php" class="icon-item">
                    <i class="fas fa-heart icon"></i>
                    <span>Wishlist</span>
                </a>
                <a href="keamanan.php" class="icon-item">
                    <i class="fas fa-shield-alt icon"></i>
                    <span>Keamanan Akun</span>
                </a>
            </div>
        </div>
    </div>

</body>
</html>