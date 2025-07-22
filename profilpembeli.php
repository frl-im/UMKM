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
    <!-- Style Anda -->
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="logo"><a href="index.php">KreasiLokal.id</a></div>
            <!-- Tautan Logout -->
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="profile-layout">
            <aside class="profile-nav">
                <!-- Navigasi profil Anda -->
            </aside>
            <main class="profile-content">
                <div class="card">
                    <h3>Profil Saya</h3>
                    <form>
                        <div class="form-group">
                            <label for="fullname">Nama Lengkap</label>
                            <!-- Menampilkan nama pengguna dari database -->
                            <input type="text" id="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <!-- Menampilkan email pengguna dari database -->
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        <button type="submit" class="btn-save">Simpan Perubahan</button>
                    </form>
                </div>
                <!-- Bagian riwayat pesanan, dll. -->
            </main>
        </div>
    </div>
</body>
</html>