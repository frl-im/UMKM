<?php
require_once 'fungsi.php';

if (isset($_POST["register"])) {
    // Panggil fungsi register_penjual dari fungsi.php
    $result = register_penjual($_POST);
    
    // Perbaiki logika untuk menangani nilai return -1 (email duplikat)
    if ($result > 0) {
        echo "<script>
                alert('Registrasi toko berhasil! Silakan login.');
                window.location.href='loginpenjual.php';
              </script>";
    } else if ($result === -1) {
        echo "<script>alert('Registrasi gagal! Email ini sudah terdaftar.');</script>";
    } else {
        echo "<script>alert('Registrasi gagal! Terjadi kesalahan.');</script>";
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
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #34A853;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }

    .form-container {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      max-width: 420px;
      width: 100%;
      animation: fadeIn 0.6s ease-in-out;
    }

    .form-container h2 {
      text-align: center;
      color: #34A853;
      margin-bottom: 0.5rem;
    }

    .form-container p {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #555;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      border-color: #34A853;
      outline: none;
    }

    .submit-btn {
      width: 100%;
      padding: 0.75rem;
      background: #34A853;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      transition: background 0.3s, transform 0.2s;
    }

    .submit-btn:hover {
      background: #2a8747;
      transform: translateY(-2px);
    }

    .form-footer {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.95rem;
    }

    .form-footer a {
      color: #1A0DAB;
      text-decoration: none;
      font-weight: 500;
    }

    .form-footer a:hover {
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
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
