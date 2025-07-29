<?php
require_once 'fungsi.php';

// Data untuk setiap kategori yang akan ditampilkan
// Anda bisa menambah atau mengubah daftar ini sesuai kebutuhan.
$kategori_list = [
    [
        'nama'      => 'Fashion',
        'deskripsi' => 'Temukan gaya busana terkini dari desainer dan brand lokal.',
        'icon'      => 'fas fa-tshirt',
        'link'      => 'halaman.php?kategori=fashion' // Sesuaikan dengan nama file Anda
    ],
    [
        'nama'      => 'Kerajinan Tangan',
        'deskripsi' => 'Karya unik hasil keterampilan para pengrajin nusantara.',
        'icon'      => 'fas fa-hands-helping',
        'link'      => 'halaman.php?kategori=kerajinan' // Sesuaikan dengan nama file Anda
    ],
    [
        'nama'      => 'Batik & Tenun',
        'deskripsi' => 'Keindahan corak dan motif tradisional dalam kain berkualitas.',
        'icon'      => 'fas fa-layer-group',
        'link'      => 'halaman.php?kategori=batik' // Sesuaikan dengan nama file Anda
    ],
    [
        'nama'      => 'Kesehatan',
        'deskripsi' => 'Solusi herbal dan alami untuk menjaga kebugaran tubuh.',
        'icon'      => 'fas fa-heartbeat',
        'link'      => 'halaman.php?' // Sesuaikan dengan nama file Anda
    ],
    [
        'nama'      => 'Buku & Koleksi',
        'deskripsi' => 'Jelajahi karya penulis lokal dan barang koleksi langka.',
        'icon'      => 'fas fa-book-open',
        'link'      => 'halaman.php' // Sesuaikan dengan nama file Anda
    ],
    [
        'nama'      => 'Hasil Bumi',
        'deskripsi' => 'Kekayaan alam segar langsung dari petani dan perkebunan.',
        'icon'      => 'fas fa-leaf',
        'link'      => 'halaman.php' // Sesuaikan dengan nama file Anda
    ],
    [
        'nama'      => 'Inovasi Mahasiswa',
        'deskripsi' => 'Temukan karya brilian dari para mahasiswa kreatif Indonesia.',
        'icon'      => 'fas fa-user-graduate',
        'link'      => 'halaman.php' // Sesuaikan dengan nama file Anda
    ]
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kategori - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f4f7f6; 
            margin: 0; 
            color: #333; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 1.5rem 15px; 
        }
        .navbar { 
            background-color: #ffffff; 
            padding: 1rem 0; 
            border-bottom: 1px solid #e0e0e0; 
            position: sticky; 
            top: 0; 
            z-index: 100;
        }
        .navbar .container { 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            padding-top: 0;
            padding-bottom: 0;
        }
        .nav-left .page-title { font-size: 1.2rem; font-weight: 600; }
        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .nav-right a { font-size: 1.5rem; color: #555; text-decoration: none; }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-size: 2.5rem;
            color: #2c3e50;
        }
        .page-header p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .category-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 2rem;
            text-decoration: none;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(46, 139, 87, 0.15);
        }
        .category-card .category-icon {
            font-size: 3rem;
            color: #2e8b57; /* Sea Green */
            margin-bottom: 1rem;
        }
        .category-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.3rem;
        }
        .category-card p {
            margin: 0;
            font-size: 0.95rem;
            color: #555;
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-left">
                <span class="page-title">KreasiLokal</span>
            </div>
            <div class="nav-right">
                <a href="index.php" title="Beranda"><i class="fas fa-home"></i></a>
                <a href="keranjang.php" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h1>Jelajahi Kategori</h1>
            <p>Temukan produk-produk unik dari seluruh nusantara berdasarkan kategorinya.</p>
        </div>

        <section class="category-grid">
            <?php foreach ($kategori_list as $kategori): ?>
                <a href="<?php echo htmlspecialchars($kategori['link']); ?>" class="category-card">
                    <div class="category-icon">
                        <i class="<?php echo htmlspecialchars($kategori['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($kategori['nama']); ?></h3>
                    <p><?php echo htmlspecialchars($kategori['deskripsi']); ?></p>
                </a>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>