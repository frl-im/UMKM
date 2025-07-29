<?php
require_once 'fungsi.php'; // Mengambil semua fungsi, termasuk koneksi DB

// 1. Mengambil nama kategori dari URL. Contoh: template_kategori.php?kategori=Fashion Lokal
$nama_kategori = $_GET['kategori'] ?? 'Tidak Dikenal';

// 2. Pusat data untuk styling setiap kategori.
//    Menyesuaikan tampilan (warna, ikon, judul) berdasarkan nama kategori.
$kategori_data = [
    'Fashion Lokal' => [
        'judul' => 'Gaya Busana Lokal', 'icon' => 'fas fa-tshirt', 'gradient' => 'linear-gradient(to right, #c0392b, #e74c3c)', 'color' => '#c0392b'
    ],
    'Kerajinan Tangan' => [
        'judul' => 'Keterampilan Tangan Lokal', 'icon' => 'fas fa-hands-helping', 'gradient' => 'linear-gradient(to right, #795548, #8d6e63)', 'color' => '#795548'
    ],
    'Batik & Tenun' => [
        'judul' => 'Warisan Batik & Tenun', 'icon' => 'fas fa-layer-group', 'gradient' => 'linear-gradient(to right, #8e44ad, #9b59b6)', 'color' => '#8e44ad'
    ],
    'Kesehatan' => [
        'judul' => 'Produk Kesehatan & Kebugaran', 'icon' => 'fas fa-heartbeat', 'gradient' => 'linear-gradient(to right, #2e8b57, #3cb371)', 'color' => '#2e8b57'
    ],
    'Buku & Koleksi' => [
        'judul' => 'Buku & Koleksi Pilihan', 'icon' => 'fas fa-book-open', 'gradient' => 'linear-gradient(to right, #2980b9, #3498db)', 'color' => '#2980b9'
    ],
    'Hasil Bumi' => [
        'judul' => 'Hasil Bumi Nusantara', 'icon' => 'fas fa-leaf', 'gradient' => 'linear-gradient(to right, #d35400, #e67e22)', 'color' => '#d35400'
    ],
    'Inovasi Mahasiswa' => [
        'judul' => 'Produk Inovasi Mahasiswa', 'icon' => 'fas fa-user-graduate', 'gradient' => 'linear-gradient(to right, #16a085, #1abc9c)', 'color' => '#16a085'
    ],
    'default' => [
        'judul' => 'Produk Lokal', 'icon' => 'fas fa-box', 'gradient' => 'linear-gradient(to right, #6c757d, #495057)', 'color' => '#6c757d'
    ]
];

// Pilih data yang sesuai, atau gunakan 'default' jika kategori tidak ditemukan
$style = $kategori_data[$nama_kategori] ?? $kategori_data['default'];

// 3. Mengambil produk dari database secara REALTIME
$products = ambil_produk_by_kategori($nama_kategori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori: <?php echo safe_output($nama_kategori); ?> - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS yang sudah disempurnakan dan dibuat responsif */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f4f4f4; margin: 0; color: #333; }
        
        /* [RESPONSIF] Mengganti width menjadi max-width agar bisa mengecil di layar mobile */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

        .card { background-color: #fff; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; }
        .navbar .container { display: flex; align-items: center; justify-content: space-between; }
        .nav-left { display: flex; align-items: center; }
        .nav-left .back-btn { font-size: 1.5rem; color: #333; text-decoration: none; margin-right: 1.5rem; }
        .nav-left .page-title { font-size: 1.2rem; font-weight: 600; }
        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .nav-right a { font-size: 1.5rem; color: #555; text-decoration: none; }
        .hero-banner { background: <?php echo $style['gradient']; ?>; color: white; padding: 3rem 2rem; border-radius: 8px; text-align: center; margin-top: 1.5rem; }
        .hero-banner h1 { margin: 0; font-size: 2.5rem; }
        .hero-banner p { font-size: 1.2rem; opacity: 0.9; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; }
        .product-card { background-color: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; text-decoration: none; color: #333; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-image img { width: 100%; height: 220px; object-fit: cover; display: block; }
        .product-info { padding: 1rem; flex-grow: 1; display: flex; flex-direction: column; }
        .product-info h4 { font-size: 1rem; margin: 0 0 0.5rem 0; height: 40px; overflow: hidden; }
        .product-info .author, .product-info .origin { font-size: 0.8rem; color: #777; margin-bottom: 1rem; }
        .product-price { font-size: 1.2rem; font-weight: bold; color: <?php echo $style['color']; ?>; margin-top: auto; }
        .empty-state { text-align: center; padding: 4rem; background: #fafafa; border-radius: 8px; }
        .empty-state i { font-size: 3rem; color: #ccc; margin-bottom: 1rem; }

        /* [RESPONSIF] Media Query untuk layar lebih kecil (tablet & mobile) */
        @media (max-width: 768px) {
            .hero-banner h1 { font-size: 1.8rem; }
            .hero-banner { padding: 2rem 1rem; }
            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
            .nav-left .page-title { font-size: 1rem; }
            .nav-right { gap: 1.2rem; }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-left">
                <a href="kategori.php" class="back-btn" title="Kembali ke Kategori"><i class="fas fa-arrow-left"></i></a>
                <span class="page-title"><?php echo safe_output($nama_kategori); ?></span>
            </div>
            <div class="nav-right">
                <a href="index.php" title="Beranda"><i class="fas fa-home"></i></a>
                <a href="chat.php" title="Chat"><i class="fas fa-comments"></i></a>
                <a href="keranjang.php" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </header>
    <main class="container">
        <section class="hero-banner">
            <h1><i class="<?php echo $style['icon']; ?>"></i> <?php echo safe_output($style['judul']); ?></h1>
        </section>
        <section class="card">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Belum ada produk di kategori ini.</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <a href="detailproduk.php?id=<?php echo $product['id']; ?>" class="product-card">
                            <div class="product-image">
                                <img src="<?php echo safe_output($product['image_url']); ?>" alt="<?php echo safe_output($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h4><?php echo safe_output($product['name']); ?></h4>
                                <?php if (isset($product['author'])): ?>
                                    <p class="author">oleh <?php echo safe_output($product['author']); ?></p>
                                <?php elseif (isset($product['origin'])): ?>
                                    <p class="origin">Asal: <?php echo safe_output($product['origin']); ?></p>
                                <?php endif; ?>
                                <p class="product-price"><?php echo format_price($product['price']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>