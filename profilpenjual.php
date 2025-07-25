<?php
require_once 'fungsi.php';

// PERBAIKAN: Mulai session dan cek login dengan benar
start_secure_session();

// PERBAIKAN: Fungsi check_login_status yang disederhanakan
function check_login_status() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
        return false;
    }
    
    // Cek session timeout (2 jam - konsisten dengan fungsi.php)
    if (time() - $_SESSION['login_time'] > 7200) {
        return false;
    }
    
    // PERBAIKAN: SELALU update last_activity
    $_SESSION['last_activity'] = time();
    return true;
}

// HAPUS DEBUG INFO - Ini bisa mengganggu session
// debug_session_info(); // COMMENT OUT INI

// Cek login dan role - PERBAIKAN: Sederhanakan
if (!check_login_status()) {
    // Redirect langsung tanpa delay
    header("Location: loginpenjual.php?error=session_expired");
    exit();
}

if ($_SESSION['user_role'] !== 'penjual') {
    header("Location: login.php");
    exit();
}

// Dapatkan data user
$user_data = get_user_by_id($_SESSION['user_id']);

// Jika data user tidak ditemukan
if (!$user_data) {
    logout_user();
    header("Location: loginpenjual.php?error=user_not_found");
    exit();
}

// Update session data jika ada perubahan di database
if ($user_data['store_name'] && $_SESSION['store_name'] !== $user_data['store_name']) {
    $_SESSION['store_name'] = $user_data['store_name'];
}
if (isset($user_data['verification_status']) && $_SESSION['verification_status'] !== $user_data['verification_status']) {
    $_SESSION['verification_status'] = $user_data['verification_status'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            background: #f5f5f5; 
        }
        .header { 
            background: #34A853; 
            color: white; 
            padding: 1rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-links a { 
            color: white; 
            text-decoration: none; 
            margin-left: 1rem; 
            padding: 0.5rem 1rem; 
            border-radius: 5px; 
            transition: background 0.3s; 
        }
        .nav-links a:hover { 
            background: rgba(255,255,255,0.1); 
        }
        .main-content { 
            max-width: 1200px; 
            margin: 2rem auto; 
            padding: 0 2rem; 
        }
        .welcome-card { 
            background: white; 
            padding: 2rem; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            margin-bottom: 2rem; 
        }
        .session-info { 
            background: #e3f2fd; 
            padding: 1.5rem; 
            border-radius: 8px; 
            margin: 1rem 0; 
            font-size: 0.9em;
            border-left: 4px solid #2196f3;
        }
        .verification-status {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-weight: bold;
        }
        .status-0 { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .status-1 { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .status-2 { background: #cce7ff; color: #004085; border-left: 4px solid #007bff; }
        .status-3 { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .btn {
            padding: 0.7rem 1.2rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #1e7e34; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #34A853;
        }
    </style>
</head>
<body>
    <div class="header">
    <div>
        <h2><i class="fas fa-store"></i> Dashboard Penjual</h2>
    </div>
    <div class="nav-links">
        <span>Hai, <?php echo safe_output($_SESSION['user_name']); ?>!</span>
        
        <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
        
        <a href="upload_produk.php"><i class="fas fa-plus"></i> Tambah Produk</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

    <div class="main-content">
        <div class="welcome-card">
            <h3>🏪 Selamat datang di Dashboard Penjual</h3>
            
            <div class="session-info">
                <h4><i class="fas fa-info-circle"></i> Info Session Aktif:</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem;">
                    <div><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></div>
                    <div><strong>Email:</strong> <?php echo safe_output($_SESSION['user_email']); ?></div>
                    <div><strong>Nama Toko:</strong> <?php echo safe_output($_SESSION['store_name'] ?? 'Belum diatur'); ?></div>
                    <div><strong>Login sejak:</strong> <?php echo date('H:i:s d/m/Y', $_SESSION['login_time']); ?></div>
                    <div><strong>Session ID:</strong> <?php echo substr(session_id(), 0, 12) . '...'; ?></div>
                    <div><strong>Durasi:</strong> <?php echo gmdate('H:i:s', time() - $_SESSION['login_time']); ?></div>
                </div>
            </div>
            
            <?php 
            $verification_status = $user_data['verification_status'] ?? 0;
            $status_messages = [
                0 => "⏳ Belum terverifikasi - Lengkapi data diri Anda",
                1 => "✅ Data diri terverifikasi - Lengkapi informasi toko",
                2 => "🏪 Informasi toko terverifikasi - Upload produk pertama",
                3 => "🎉 Akun fully active - Siap berjualan!"
            ];
            ?>
            
            <div class="verification-status status-<?php echo $verification_status; ?>">
                <i class="fas fa-shield-alt"></i> Status Verifikasi: 
                <?php echo $status_messages[$verification_status]; ?>
            </div>

            <?php if($verification_status < 3): ?>
                <div class="action-buttons">
                    <?php if($verification_status == 0): ?>
                        <a href="verifikasi-data-diri.php" class="btn btn-primary">
                            <i class="fas fa-id-card"></i> Verifikasi Data Diri
                        </a>
                    <?php elseif($verification_status == 1): ?>
                        <a href="informasitoko.php" class="btn btn-primary">
                            <i class="fas fa-store"></i> Lengkapi Info Toko
                        </a>
                    <?php elseif($verification_status == 2): ?>
                        <a href="uploadproduk.php" class="btn btn-success">
                            <i class="fas fa-box"></i> Upload Produk Pertama
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div>Total Produk</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div>Pesanan Hari Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">Rp 0</div>
                    <div>Penjualan Bulan Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div>Rating Toko</div>
                </div>
            </div>

            <p style="margin-top: 2rem;">
                <i class="fas fa-lightbulb"></i> 
                <strong>Tips:</strong> Lengkapi profil toko dan upload produk berkualitas untuk meningkatkan penjualan Anda!
            </p>
        </div>
    </div>

    <script>
        // Auto refresh session check setiap 5 menit
        setInterval(function() {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        alert('Session Anda telah berakhir. Silakan login kembali.');
                        window.location.href = 'loginpenjual.php?error=session_expired';
                    }
                })
                .catch(error => {
                    console.error('Error checking session:', error);
                });
        }, 300000); // 5 menit

        // Update waktu login secara real-time
        function updateLoginDuration() {
            const loginTime = <?php echo $_SESSION['login_time']; ?>;
            const currentTime = Math.floor(Date.now() / 1000);
            const duration = currentTime - loginTime;
            
            const hours = Math.floor(duration / 3600);
            const minutes = Math.floor((duration % 3600) / 60);
            const seconds = duration % 60;
            
            const durationElement = document.querySelector('.session-info div:last-child div:last-child');
            if (durationElement) {
                durationElement.innerHTML = '<strong>Durasi:</strong> ' + 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }
        }
        
        // Update setiap detik
        setInterval(updateLoginDuration, 1000);
    </script>
</body>
</html>