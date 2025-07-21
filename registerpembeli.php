<?php
require 'fungsi.php';

// Cek apakah tombol register sudah ditekan
if (isset($_POST["register"])) {
    if (register_pembeli($_POST) > 0) {
        echo "<script>
                alert('Registrasi berhasil! Silakan login.');
                window.location.href='loginpembeli.php';
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
  <title>Daftar Pembeli - KreasiLokal.id</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- Link ke file CSS Anda -->
  <!-- <link rel="stylesheet" href="style.css"> -->
</head>
<body>
  <div class="form-container">
    <h2>Daftar Akun Pembeli</h2>
    <p>Hanya perlu beberapa langkah untuk mulai berbelanja.</p>
    
    <form action="" method="POST">
      <div class="form-group">
        <label for="fullname">Nama Lengkap</label>
        <input type="text" id="fullname" name="fullname" placeholder="Masukkan nama lengkap Anda" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="contoh: email@anda.com" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Buat password yang kuat" required>
      </div>
      <button type="submit" name="register" class="submit-btn">Buat Akun</button>
    </form>

    <div class="form-footer">
      <p>Sudah punya akun? <a href="loginpembeli.php">Masuk</a></p>
    </div>
  </div>
</body>
</html>
