<?php
// profilpenjual.php - DIPERBAIKI
// Konfigurasi session yang benar (sama dengan authlogic.php)
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.name', 'KREASI_SESSION');

// Mulai session dengan aman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'fungsi.php';

// Fungsi untuk debug session (hapus setelah selesai)
function debug_session_info() {
    echo "<div style='background:#fff3cd;border:1px solid #ffeaa7;padding:10px;margin:10px 0;border-radius:5px;'>";
    echo "<h3>🔍 DEBUG SESSION INFO:</h3>";
    echo "<strong>Session Status:</strong> " . session_status();
    
    $status_text = [
        PHP_SESSION_DISABLED => ' (DISABLED)',
        PHP_SESSION_NONE => ' (NONE)', 
        PHP_SESSION_ACTIVE => ' (ACTIVE)'
    ];
    echo $status_text[session_status()] ?? ' (UNKNOWN)';
    echo "<br>";
    
    echo "<strong>Session ID:</strong> " . (session_id() ?: 'NOT SET') . "<br>";
    echo "<strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
    echo "<strong>User Role:</strong> " . ($_SESSION['user_role'] ?? 'NOT SET') . "<br>";
    echo "<strong>User Name:</strong> " . ($_SESSION['user_name'] ?? 'NOT SET') . "<br>";
    echo "<strong>Email:</strong> " . ($_SESSION['user_email'] ?? 'NOT SET') . "<br>";
    echo "<strong>Store Name:</strong> " . ($_SESSION['store_name'] ?? 'NOT SET') . "<br>";
    echo "<strong>Login Time:</strong> " . (isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'NOT SET') . "<br>";
    echo "<strong>Last Activity:</strong> " . (isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'NOT SET') . "<br>";
    echo "<strong>Current Time:</strong> " . time() . " (" . date('Y-m-d H:i:s') . ")<br>";
    
    if (isset($_SESSION['login_time'])) {
        $time_diff = time() - $_SESSION['login_time'];
        echo "<strong>Session Duration:</strong> " . gmdate('H:i:s', $time_diff) . " (" . $time_diff . " seconds)<br>";
    }
    
    echo "</div>";
}

// Tampilkan debug info
debug_session_info();

// Cek apakah user sudah login
function check_login_status() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
        return false;
    }
    
    // Cek session timeout (2 jam)
    if (time() - $_SESSION['login_time'] > 7200) {
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

// Cek login dan role
if (!check_login_status()) {
    echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border-radius:5px;'>";
    echo "❌ Session tidak valid atau sudah timeout. Redirecting...";
    echo "</div>";
    
    // Redirect setelah 3 detik
    echo "<script>setTimeout(function(){ window.location.href = 'loginpenjual.php?error=session_expired'; }, 3000);</script>";
    exit();
}

if ($_SESSION['user_role'] !== 'penjual') {
    echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border-radius:5px;'>";
    echo "❌ Akses ditolak. Hanya penjual yang dapat mengakses halaman ini.";
    echo "</div>";
    
    echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 3000);</script>";
    exit();
}

// Dapatkan data user
$user_data = get_user_by_id($_SESSION['user_id']);

// Jika data user tidak ditemukan
if (!$user_data) {
    echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px 0;border-radius:5px;'>";
    echo "❌ Data user tidak ditemukan. Redirecting...";
    echo "</div>";
    
    logout_user();
    echo "<script>setTimeout(function(){ window.location.href = 'loginpenjual.php?error=user_not_found'; }, 3000);</script>";
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
                        <a href="verifikasi_data.php" class="btn btn-primary">
                            <i class="fas fa-id-card"></i> Verifikasi Data Diri
                        </a>
                    <?php elseif($verification_status == 1): ?>
                        <a href="informasi_toko.php" class="btn btn-primary">
                            <i class="fas fa-store"></i> Lengkapi Info Toko
                        </a>
                    <?php elseif($verification_status == 2): ?>
                        <a href="upload_produk.php" class="btn btn-success">
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