<?php
// loginpenjual.php
require 'fungsi.php';

if (isset($_POST["login"])) {
    if (login_user($_POST) === true) {
        // Redirect dilakukan di dalam fungsi login_user
        header("Location: profilpenjual.php"); // Ganti ke PHP file
        exit();
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Penjual - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #34A853; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .login-box { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-box h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background: #34A853; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #34A853; }
        .error { color: red; font-style: italic; text-align: center; margin-bottom: 1rem; }
        .success { color: green; text-align: center; margin-bottom: 1rem; }
        p { text-align: center; margin-top: 1rem; }
        a { color: #34A853; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Masuk ke Dashboard Penjual</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'reg_success'): ?>
            <p class="success">Registrasi berhasil! Silakan login.</p>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p class="error">Email atau password salah!</p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email Toko</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login">Masuk ke Dashboard</button>
        </form>
        <p>Belum punya toko? <a href="registerpenjual.php">Daftar sebagai Penjual</a></p>
    </div>
</body>
</html>