<?php
// informasi-toko.php
session_start();
require 'fungsi.php';

// Cek sesi login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penjual') {
    header("Location: loginpenjual.php");
    exit();
}

if (isset($_POST["submit"])) {
    // Panggil fungsi untuk memproses informasi toko
    if (proses_informasi_toko($_POST)) {
        // Jika berhasil, arahkan ke langkah terakhir
        header("Location: uploadproduk.php");
        exit();
    } else {
        // Jika gagal, tampilkan pesan error (opsional)
        $error = "Gagal menyimpan informasi toko.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Toko - KreasiLokal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css"> <style>
        /* Salin CSS dari atas atau gunakan file style.css yang sama */
        :root{--primary-color:#FF4500;--light-grey:#F0F0F0;--border-color:#E5E5E5;--text-color:#333;--background-color:#F9FAFB;}
        *{margin:0;padding:0;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;}
        body{background-color:var(--background-color);color:var(--text-color);}
        .main-container{max-width:800px;margin:0 auto;background-color:#fff;min-height:100vh;}
        .page-header{display:flex;align-items:center;justify-content:space-between;padding:1rem;border-bottom:1px solid var(--border-color);}
        .page-header a{color:var(--primary-color);text-decoration:none;font-weight:500;}
        .page-header .back-link{font-size:1.5rem;color:var(--text-color);}
        .page-header .title{font-size:1.1rem;font-weight:600;}
        .stepper{display:flex;justify-content:space-around;padding:1.5rem 1rem;border-bottom:6px solid var(--light-grey);position:relative;}
        .step{display:flex;flex-direction:column;align-items:center;color:#aaa;flex:1;text-align:center;}
        .step .circle{width:24px;height:24px;border-radius:50%;border:2px solid #aaa;background-color:#fff;margin-bottom:0.5rem;}
        .step .label{font-size:0.9rem;}
        .step.active .circle{border-color:var(--primary-color);}
        .step.active .label{color:var(--text-color);font-weight:500;}
        .step.completed .circle{border-color:var(--primary-color);background-color:var(--primary-color);}
        .stepper::before{content:'';position:absolute;width:66%;height:2px;background-color:var(--border-color);top:40px;left:17%;z-index:-1;}
        .stepper-progress{position:absolute;height:2px;background-color:var(--primary-color);top:40px;left:17%;z-index:-1;}
        .form-content{padding:1.5rem;}
        .form-group{margin-bottom:1.5rem;}
        .form-group label{display:block;margin-bottom:0.5rem;font-weight:500;font-size:0.95rem;}
        .form-group label .required{color:var(--primary-color);}
        .form-group input[type="text"],.form-group textarea{width:100%;padding:0.8rem;border:1px solid var(--border-color);border-radius:5px;font-size:1rem;}
        .form-group textarea{min-height:100px;resize:vertical;}
        .form-footer{display:flex;gap:1rem;padding:1.5rem;border-top:1px solid var(--border-color);background-color:#fff;position:sticky;bottom:0;}
        .btn{flex:1;padding:0.9rem;border-radius:5px;border:1px solid var(--border-color);font-size:1rem;font-weight:500;cursor:pointer;}
        .btn-secondary{background-color:#fff;color:var(--text-color);}
        .btn-primary{background-color:var(--primary-color);color:#fff;border-color:var(--primary-color);}
    </style>
</head>
<body>
    <div class="main-container">
        <header class="page-header">
            <a href="verifikasi-data-diri.php" class="back-link"><i class="fa fa-arrow-left"></i></a>
            <span class="title">Informasi Toko</span>
            <a href="#" onclick="document.getElementById('storeForm').submit();">Simpan</a>
        </header>

        <div class="stepper">
            <div class="stepper-progress" style="width: 33%;"></div>
            <div class="step completed">
                <div class="circle"></div>
                <div class="label">Verifikasi Data Diri</div>
            </div>
            <div class="step active">
                <div class="circle"></div>
                <div class="label">Informasi Toko</div>
            </div>
            <div class="step">
                <div class="circle"></div>
                <div class="label">Upload Produk</div>
            </div>
        </div>

        <form class="form-content" id="storeForm" method="POST">
            <div class="form-group">
                <label for="store_name">Nama Toko <span class="required">*</span></label>
                <input type="text" id="store_name" name="store_name" required placeholder="Contoh: Batik Juwita">
            </div>

            <div class="form-group">
                <label for="store_address">Alamat Toko <span class="required">*</span></label>
                <textarea id="store_address" name="store_address" required placeholder="Masukkan alamat lengkap toko"></textarea>
            </div>

            <div class="form-group">
                <label for="store_description">Deskripsi Toko</label>
                <textarea id="store_description" name="store_description" placeholder="Jelaskan sedikit tentang toko Anda"></textarea>
            </div>
        </form>

        <footer class="form-footer">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='verifikasi-data-diri.php'">Kembali</button>
            <button type="submit" name="submit" class="btn btn-primary" form="storeForm">Lanjut</button>
        </footer>
    </div>
</body>
</html>