<?php
require 'fungsi.php';

if (isset($_POST["register"])) {
    if (register_penjual($_POST) > 0) {
        echo "<script>
                alert('Registrasi toko berhasil! Silakan login.');
                window.location.href='loginpenjual.php';
              </script>";
    } else {
        echo "<script>alert('Registrasi gagal! Email mungkin sudah terdaftar.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daftar Penjual - KreasiLokal.id</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- Link ke file CSS Anda -->
  <!-- <link rel="stylesheet" href="style.css"> -->
</head>
<body>
  <div class="form-container">
    <h2>Daftar Akun Penjual</h2>
    <p>Lengkapi data diri dan toko Anda untuk mulai berjualan.</p>
    
    <form action="" method="POST">
      <div class="form-group">
        <label for="owner-name">Nama Pemilik</label>
        <input type="text" id="owner-name" name="owner-name" placeholder="Nama lengkap sesuai KTP" required>
      </div>
       <div class="form-group">
        <label for="store-name">Nama Toko</label>
        <input type="text" id="store-name" name="store-name" placeholder="Contoh: Galeri Batik Cirebon" required>
      </div>
      <div class="form-group">
        <label for="email">Email Toko</label>
        <input type="email" id="email" name="email" placeholder="Email aktif untuk komunikasi" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
      </div>
      <div class="form-group">
        <label for="phone">Nomor Telepon</label>
        <input type="tel" id="phone" name="phone" placeholder="Nomor WhatsApp aktif" required>
      </div>
      <button type="submit" name="register" class="submit-btn">Daftarkan Toko Saya</button>
    </form>

    <div class="form-footer">
      <p>Sudah punya akun penjual? <a href="loginpenjual.php">Masuk</a></p>
    </div>
  </div>
</body>
</html>
