<?php
require 'fungsi.php';

// Cek apakah tombol register sudah ditekan
if (isset($_POST["register"])) {
    if (registerpembeli($_POST) > 0) {
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
      max-width: 400px;
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
      color: #34A853;
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
