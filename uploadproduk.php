<?php
session_start();
require 'fungsi.php';

// Cek apakah pengguna sudah login sebagai penjual
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penjual') { //
    header("Location: loginpenjual.php"); //
    exit(); //
}

$message = '';
$message_type = '';

if (isset($_POST["submit"])) { //
    $result = upload_produk($_POST, $_FILES); //
    
    if ($result['status']) { //
        $message = $result['message']; //
        $message_type = 'success'; //
    } else {
        $message = $result['message']; //
        $message_type = 'error'; //
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Produk - KreasiLokal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF4500; /* Warna oranye/merah seperti di screenshot */
            --light-grey: #F0F0F0;
            --border-color: #E5E5E5;
            --text-color: #333;
            --background-color: #F9FAFB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .main-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .page-header a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .page-header .back-link {
            font-size: 1.5rem;
            color: var(--text-color);
        }
        .page-header .title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .stepper {
            display: flex;
            justify-content: space-around;
            padding: 1.5rem 1rem;
            border-bottom: 6px solid var(--light-grey);
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #aaa;
            position: relative;
            flex: 1;
        }
        .step .circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #aaa;
            background-color: #fff;
            margin-bottom: 0.5rem;
        }
        .step .label { font-size: 0.9rem; }
        .step.completed .circle { border-color: var(--primary-color); background-color: var(--primary-color); }
        .step.active .circle { border-color: var(--primary-color); background-color: #fff; }
        .step.active .label { color: var(--text-color); font-weight: 500; }
        .stepper::before {
            content: '';
            position: absolute;
            width: 66%; /* Lebar garis untuk 3 step */
            height: 2px;
            background-color: var(--border-color);
            top: 100px; /* Sesuaikan posisi vertikal */
            left: 17%;
        }
         .stepper::after {
            content: '';
            position: absolute;
            width: 66%; /* Progress bar */
            height: 2px;
            background-color: var(--primary-color);
            top: 100px;
            left: 17%;
        }

        .form-content { padding: 1.5rem; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .form-group label .required { color: var(--primary-color); }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .form-group textarea { min-height: 100px; resize: vertical; }

        .image-upload-box {
            width: 120px;
            height: 120px;
            border: 2px dashed var(--border-color);
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        .image-upload-box:hover { border-color: var(--primary-color); }
        .image-upload-box i { font-size: 2rem; color: #ccc; }
        .image-upload-box input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .image-preview { margin-top: 1rem; }
        .image-preview img {
            max-width: 120px;
            max-height: 120px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
        }

        .form-footer {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            background-color: #fff;
            position: sticky;
            bottom: 0;
        }
        .btn {
            flex: 1;
            padding: 0.9rem;
            border-radius: 5px;
            border: 1px solid var(--border-color);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-secondary { background-color: #fff; color: var(--text-color); }
        .btn-primary { background-color: var(--primary-color); color: #fff; border-color: var(--primary-color); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .message { padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }

    </style>
</head>
<body>
    <div class="main-container">
        <header class="page-header">
            <a href="profilpenjual.php" class="back-link"><i class="fa fa-arrow-left"></i></a> <span class="title">Upload Produk</span>
            <a href="#" onclick="document.getElementById('productForm').submit();">Simpan</a>
        </header>

       <div class="stepper">
    <div class="stepper-progress" style="width: 66%;"></div>
    <div class="step completed">
        <div class="circle"></div>
        <div class="label">Verifikasi Data Diri</div>
    </div>
    <div class="step completed">
        <div class="circle"></div>
        <div class="label">Informasi Toko</div>
    </div>
    <div class="step active">
        <div class="circle"></div>
        <div class="label">Upload Produk</div>
    </div>
</div>

        <form class="form-content" action="" method="POST" enctype="multipart/form-data" id="productForm">
            <?php if (!empty($message)): ?>
                <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div> <?php endif; ?>

            <div class="form-group">
                <label for="product-name">Nama Produk <span class="required">*</span></label>
                <input type="text" id="product-name" name="product-name" required placeholder="Contoh: Kemeja Batik Pria"> </div>

            <div class="form-group">
                <label for="product-description">Deskripsi Produk <span class="required">*</span></label> <textarea id="product-description" name="product-description" required placeholder="Jelaskan detail produk Anda"></textarea> </div>

            <div class="form-group">
                <label for="product-price">Harga <span class="required">*</span></label>
                <input type="number" id="product-price" name="product-price" required placeholder="Masukkan harga (Rp)"> </div>

            <div class="form-group">
                <label for="product-stock">Stok <span class="required">*</span></label>
                <input type="number" id="product-stock" name="product-stock" required placeholder="Jumlah stok yang tersedia"> </div>

            <div class="form-group">
                <label for="product-category">Kategori <span class="required">*</span></label>
                <select id="product-category" name="product-category" required> <option value="">Pilih Kategori</option>
                    <option value="kerajinan">Kerajinan Tangan</option> <option value="makanan">Makanan & Minuman</option> <option value="fashion">Fashion & Aksesoris</option> <option value="lainnya">Lainnya</option> </select>
            </div>

             <div class="form-group">
                <label for="product-image">Foto Produk <span class="required">*</span></label>
                <div class="image-upload-box">
                    <i class="fa fa-plus"></i>
                    <input type="file" name="product-image" id="product-image" accept="image/*" required> </div>
                <div class="image-preview" id="image-preview" style="display: none;">
                    <img id="preview-image" src="#" alt="Pratinjau Gambar"/>
                </div>
            </div>

            </form>
         <footer class="form-footer">
    <button type="button" class="btn btn-secondary" onclick="window.location.href='informasi-toko.php'">Kembali</button>
    <button type="submit" name="submit" class="btn btn-primary" id="submit-btn" form="productForm">Selesai</button>
</footer>
    </div>
    
    <script>
        const fileInput = document.getElementById('product-image');
        const previewContainer = document.getElementById('image-preview');
        const previewImage = document.getElementById('preview-image');
        const uploadBox = document.querySelector('.image-upload-box');
        const form = document.getElementById('productForm');
        const submitBtn = document.getElementById('submit-btn');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewContainer.style.display = 'block';
                    uploadBox.style.display = 'none'; // Sembunyikan box upload jika gambar sudah dipilih
                }
                reader.readAsDataURL(file);
            }
        });

        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
        });

        // Mencegah resubmission form saat refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>