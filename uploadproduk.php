<?php
session_start();
require 'fungsi.php';

// Cek apakah pengguna sudah login sebagai penjual
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penjual') {
    header("Location: loginpenjual.php");
    exit();
}

if (isset($_POST["submit"])) {
    if (upload_produk($_POST, $_FILES) > 0) {
        echo "<script>
                alert('Produk berhasil ditambahkan!');
                window.location.href='dashboard-penjual.php';
              </script>";
    } else {
        echo "<script>alert('Upload produk gagal!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Unggah Produk Baru</title>
    <!-- ... CSS dan Font Awesome Anda ... -->
</head>
<body>
    <div class="upload-container">
        <h1>Unggah Produk Baru</h1>
        <form class="upload-form" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product-name">Nama Produk</label>
                <input type="text" id="product-name" name="product-name" required>
            </div>
            <div class="form-group">
                <label for="product-description">Deskripsi Produk</label>
                <textarea id="product-description" name="product-description" required></textarea>
            </div>
            <div class="form-group">
                <label for="product-price">Harga</label>
                <input type="number" id="product-price" name="product-price" required>
            </div>
            <div class="form-group">
                <label for="product-category">Kategori</label>
                <select id="product-category" name="product-category" required>
                    <option value="kerajinan">Kerajinan</option>
                    <option value="makanan">Makanan</option>
                    <option value="fashion">Fashion</option>
                </select>
            </div>
            <div class="form-group">
                <label for="product-stock">Jumlah Stok</label>
                <input type="number" id="product-stock" name="product-stock" required>
            </div>
            <div class="form-group">
                <label>Gambar Produk</label>
                <input type="file" name="product-image" required>
            </div>
            <button type="submit" name="submit" class="submit-btn">Tambahkan Produk</button>
        </form>
    </div>
</body>
</html>
