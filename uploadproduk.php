<?php
require_once 'fungsi.php';

// PERBAIKAN: Gunakan fungsi check_login yang sudah ada untuk konsistensi
check_login('penjual');

$error_message = '';
$success_message = '';

// Cek apakah penjual sudah menyelesaikan verifikasi data diri & info toko
if ($_SESSION['verification_status'] < 2) {
    // Arahkan kembali ke dashboard jika belum siap upload produk
    header("Location: profilpenjual.php?error=not_fully_verified");
    exit();
}

if (isset($_POST["submit_produk"])) {
    // Panggil fungsi upload_produk dari fungsi.php
    $hasil = upload_produk($_POST, $_FILES);
    
    if ($hasil['status'] === true) {
        $success_message = $hasil['message'];
    } else {
        $error_message = $hasil['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Anda sudah bagus, tidak perlu diubah */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding-top: 2rem; }
        .container { background: #fff; padding: 2.5rem; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); width: 100%; max-width: 700px; }
        .header-section { text-align: center; margin-bottom: 2rem; }
        .header-section i { color: #34A853; font-size: 3rem; margin-bottom: 0.5rem; }
        .header-section h2 { margin: 0; font-size: 1.8rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.8rem 1rem; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 1rem; }
        textarea { resize: vertical; min-height: 120px; }
        .submit-btn { width: 100%; padding: 1rem; background: #34A853; color: white; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer; }
        .message { padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; text-align: center; }
        .error { background: #f8d7da; color: #721c24; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <i class="fas fa-box"></i>
            <h2>Tambah Produk Baru</h2>
        </div>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">Nama Produk</label>
                <input type="text" id="product_name" name="product_name" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi Produk</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Harga (Rp)</label>
                <input type="number" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            <div class="form-group">
                <label for="category">Kategori</label>
                <select id="category" name="category" required>
                    <option value="kerajinan">Kerajinan</option>
                    <option value="makanan">Makanan</option>
                    <option value="fashion">Fashion</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="product_image">Foto Produk</label>
                <input type="file" id="product_image" name="product_image" accept="image/*" required>
            </div>
            <button type="submit" name="submit_produk" class="submit-btn">Simpan Produk</button>
        </form>
         <p style="text-align:center; margin-top:1.5rem;"><a href="profilpenjual.php">Kembali ke Dashboard</a></p>
    </div>
</body>
</html>