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
    <title>Profil Pembeli - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            background: #f5f5f5; 
        }
        .header { 
            background: #2e8b57; 
            color: white; 
            padding: 1rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
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
            background: #e8f5e8; 
            padding: 1rem; 
            border-radius: 5px; 
            margin-bottom: 1rem; 
            font-size: 0.9em; 
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h2><i class="fas fa-shopping-bag"></i> Profil Pembeli</h2>
        </div>
        <div class="nav-links">
            <span>Hai, <?php echo safe_output($_SESSION['user_name']); ?>!</span>
            <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="welcome-card">
            <h3>Selamat datang, <?php echo safe_output($_SESSION['user_name']); ?>!</h3>
            <div class="session-info">
                <strong>Info Session:</strong><br>
                User ID: <?php echo $_SESSION['user_id']; ?><br>
                Email: <?php echo safe_output($_SESSION['user_email']); ?><br>
                Role: <?php echo safe_output($_SESSION['user_role']); ?><br>
                Login sejak: <?php echo date('H:i:s', $_SESSION['login_time']); ?><br>
                Session ID: <?php echo substr(session_id(), 0, 10) . '...'; ?>
            </div>
            
            <p>Selamat berbelanja produk tradisional Indonesia!</p>
        </div>
    </div>

    <script>
        // Auto refresh session setiap 5 menit
        setInterval(function() {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        alert('Session Anda telah berakhir. Silakan login kembali.');
                        window.location.href = 'loginpembeli.php';
                    }
                })
                .catch(error => console.error('Error checking session:', error));
        }, 300000); // 5 menit
    </script>
</body>
</html>