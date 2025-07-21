<!-- loginpembeli.php -->
<?php
require 'fungsi.php';

if (isset($_POST["login"])) {
    if (login_user($_POST) === false) {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Login Pembeli - KreasiLokal.id</title>
    <!-- ... CSS dan Font Awesome Anda ... -->
</head>
<body>
    <div class="login-box">
        <h2>Masuk ke Akun Pembeli</h2>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'reg_success'): ?>
            <p style="color: green;">Registrasi berhasil! Silakan login.</p>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <p style="color: red; font-style: italic;">Email atau password salah!</p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login">Masuk</button>
        </form>
        <p>Belum punya akun? <a href="registerpembeli.php">Daftar sekarang</a></p>
    </div>
</body>
</html>

---

<!-- loginpenjual.php -->
<?php
require 'fungsi.php';

if (isset($_POST["login"])) {
    if (login_user($_POST) === false) {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Login Penjual - KreasiLokal.id</title>
    <!-- ... CSS dan Font Awesome Anda ... -->
</head>
<body>
    <div class="login-box">
        <h2>Masuk ke Dashboard Penjual</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'reg_success'): ?>
            <p style="color: green;">Registrasi berhasil! Silakan login.</p>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p style="color: red; font-style: italic;">Email atau password salah!</p>
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
