<?php
session_start();

// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginpembeli.php");
    exit();
}

require_once 'config/database.php';

// Ambil data pengguna dari database
$stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>

        .grid-item .icon-wrapper {
    position: relative;
    display: inline-block;
}
.notification-badge {
    position: absolute;
    top: -5px;
    right: -10px;
    background-color: #d9534f; /* Warna merah notifikasi */
    color: white;
    border-radius: 50%;
    padding: 2px 8px;
    font-size: 0.8rem;
    font-weight: bold;
    border: 2px solid white;
    display: none; /* Sembunyi secara default */
}
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            background-color: #f4f7f6; 
            margin: 0; 
            color: #333; 
        }
        .navbar { 
            background-color: #fff; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            padding: 1rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .navbar .logo { 
            font-size: 1.5rem; 
            font-weight: bold; 
            color: #2e8b57; /* Warna utama dari logo */
            text-decoration: none; 
        }
        .navbar .nav-links a { 
            margin-left: 1.5rem; 
            text-decoration: none; 
            color: #555; 
            font-weight: 500; 
        }
        .navbar .nav-links a.logout { 
            color: #d9534f; 
        }
        
        .container { 
            max-width: 900px; 
            margin: 2rem auto; 
            padding: 2rem; 
            background-color: #fff; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        .profile-header { 
            display: flex; 
            align-items: center; 
            margin-bottom: 2.5rem; 
        }
        .profile-header .avatar { 
            width: 70px; 
            height: 70px; 
            border-radius: 50%; 
            background-color: #2e8b57; /* Warna utama */
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 2rem; 
            margin-right: 1.5rem; 
        }
        .profile-header .info h2 { 
            margin: 0; 
            font-size: 1.6rem; 
            font-weight: 600;
        }
        .profile-header .info p { 
            margin: 0.25rem 0 0; 
            color: #777; 
        }

        .profile-section {
            margin-bottom: 2.5rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.8rem;
            margin-bottom: 1.5rem;
        }
        .section-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .section-header a {
            text-decoration: none;
            color: #555;
            font-size: 0.9rem;
        }
        .section-header a:hover {
            color: #2e8b57;
        }

        .section-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 kolom untuk menu utama */
            gap: 1.5rem;
        }
        .grid-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-decoration: none;
            color: #333;
            padding: 1rem 0.5rem;
            border-radius: 8px;
            transition: background-color 0.2s, color 0.2s;
        }
        .grid-item:hover {
            background-color: #eaf3ef; /* Latar hover warna hijau muda */
        }
        .grid-item i {
            font-size: 1.8rem;
            margin-bottom: 0.75rem;
            color: #2e8b57; /* Warna utama */
        }
        .grid-item p {
            margin: 0;
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Penyesuaian untuk seksi dengan 3 kolom */
        .grid-3-cols {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
        
        footer { 
            text-align: center; 
            margin-top: 3rem; 
            padding: 1rem; 
            color: #888; 
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">KreasiLokal.id</a>
        <div class="nav-links">
            <a href="index.php">Beranda</a>
            <a href="#">Produk</a>
            <a href="keranjang.php"><i class="fas fa-shopping-cart"></i> Keranjang</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-header">
            <div class="avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="info">
                <h2><?= htmlspecialchars($user['fullname']); ?></h2>
                <p><?= htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <div class="profile-section">
            <div class="section-header">
                <h3>Pesanan Saya</h3>
                <a href="pesanan.php">Lihat Riwayat Pesanan <i class="fas fa-chevron-right"></i></a>
            </div>
           <div class="section-grid">
    <a href="pesanan.php" class="grid-item">
        <div class="icon-wrapper">
            <i class="fas fa-wallet"></i>
            <span class="notification-badge" id="badge-belum-bayar"></span> </div>
        <p>Belum Bayar</p>
    </a>
    <a href="pesanan.php" class="grid-item">
         <div class="icon-wrapper">
            <i class="fas fa-box-open"></i>
            <span class="notification-badge" id="badge-dikemas"></span> </div>
        <p>Dikemas</p>
    </a>
    <a href="pesanan.php" class="grid-item">
        <div class="icon-wrapper">
            <i class="fas fa-truck"></i>
            <span class="notification-badge" id="badge-dikirim"></span> </div>
        <p>Dikirim</p>
    </a>
    <a href="pesanan.php" class="grid-item">
        <div class="icon-wrapper">
            <i class="fas fa-star"></i>
            <span class="notification-badge" id="badge-penilaian"></span> </div>
        <p>Beri Penilaian</p>
    </a>
</div>

        <div class="profile-section">
            <div class="section-header">
                <h3>Dompet Saya</h3>
            </div>
            <div class="section-grid grid-3-cols">
                <a href="saldo.php" class="grid-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <p>Saldo Akun</p>
                </a>
                <a href="vouchersaya.php" class="grid-item">
                    <i class="fas fa-tags"></i>
                    <p>Voucher Saya</p>
                </a>
                <a href="metodepembayaran.php" class="grid-item">
                    <i class="fas fa-credit-card"></i>
                    <p>Metode Pembayaran</p>
                </a>
            </div>
        </div>

        <div class="profile-section">
            <div class="section-header">
                <h3>Akun Saya</h3>
            </div>
            <div class="section-grid">
                <a href="ubahprofil.php" class="grid-item">
                    <i class="fas fa-user-edit"></i>
                    <p>Ubah Profil</p>
                </a>
                <a href="alamat.php" class="grid-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>Alamat Pengiriman</p>
                </a>
                <a href="wishlist.html" class="grid-item">
                    <i class="fas fa-heart"></i>
                    <p>Wishlist</p>
                </a>
                 <a href="keamanan.php" class="grid-item">
                    <i class="fas fa-shield-alt"></i>
                    <p>Keamanan Akun</p>
                </a>
            </div>
        </div>

        <div class="profile-header">
    <div class="avatar">
        <i class="fas fa-user"></i>
    </div>
    <div class="info">
        <h2 id="user-fullname">Memuat...</h2> <p id="user-email">Memuat...</p>      </div>
</div>

    </div>

    <footer>
        <p>&copy; 2025 KreasiLokal.id</p>
    </footer>
        <script>
// Fungsi ini akan berjalan setelah seluruh konten halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    
    // Panggil fungsi untuk mengambil data dari server
    fetchDashboardData();

});

function fetchDashboardData() {
    fetch('api/get_dashboard_data.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const data = result.data;

                // 1. Update nama dan email pengguna
                document.getElementById('user-fullname').textContent = data.user.fullname;
                document.getElementById('user-email').textContent = data.user.email;

                // 2. Update badge notifikasi pesanan
                updateBadge('badge-belum-bayar', data.order_counts.belum_bayar);
                updateBadge('badge-dikemas', data.order_counts.dikemas);
                updateBadge('badge-dikirim', data.order_counts.dikirim);
                updateBadge('badge-penilaian', data.order_counts.penilaian);
                
                // 3. (Opsional) Update data lainnya
                // document.getElementById('saldo-akun').textContent = data.saldo;
            } else {
                console.error('Gagal mengambil data dashboard:', result.message);
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
        });
}

function updateBadge(elementId, count) {
    const badge = document.getElementById(elementId);
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block'; // Tampilkan badge jika ada notif
    } else {
        badge.style.display = 'none'; // Sembunyikan jika tidak ada notif
    }
}
</script>
</body>
</html>