<?php
require_once 'fungsi.php';
$toko_pilihan = ambil_toko_pilihan();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Pilihan - KreasiLokal.id</title>
    <!-- Tautan ke Font Awesome untuk ikon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f4f4f4; 
            margin: 0; 
            color: #333; 
        }
        .container { 
            width: 1200px; 
            margin: 0 auto; 
            padding: 0 15px; 
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        
        /* Header */
        .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; }
        .navbar .container { display: flex; align-items: center; justify-content: space-between; }
        .nav-left { display: flex; align-items: center; }
        .nav-left .back-btn { font-size: 1.5rem; color: #333; text-decoration: none; margin-right: 1.5rem; }
        .nav-left .page-title { font-size: 1.2rem; font-weight: 600; }
        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .nav-right a { font-size: 1.5rem; color: #555; text-decoration: none; }

        /* Stores Header */
        .stores-header {
            text-align: center;
            padding: 2rem 0;
        }
        .stores-header h1 {
            font-size: 2.5rem;
            color: #2e8b57;
            margin: 0;
        }
        .stores-header p {
            font-size: 1.1rem;
            color: #555;
        }

        /* Store Grid */
        .store-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .store-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .store-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        .store-card > a > img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .store-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
        }
        .store-info img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: -45px; /* Pull the logo up over the banner */
        }
        .store-info h3 {
            margin: 0 0 0.25rem 0;
        }
        .store-info p {
            margin: 0;
            font-size: 0.9rem;
            color: #777;
        }

    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-left">
                <a href="javascript:history.back()" class="back-btn" title="Kembali"><i class="fas fa-arrow-left"></i></a>
                <span class="page-title">Toko Pilihan</span>
            </div>
            <div class="nav-right">
                <a href="index.html" title="Beranda"><i class="fas fa-home"></i></a>
                <a href="chat.html" title="Chat"><i class="fas fa-comments"></i></a>
                <a href="keranjang.html" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="stores-header">
            <h1>Toko Pilihan</h1>
            <p>Temukan produk terbaik dari para pengrajin dan penjual lokal terkurasi.</p>
        </section>

        <section class="store-grid">
    <?php if (empty($toko_pilihan)): ?>
        <p>Belum ada toko pilihan yang tersedia.</p>
    <?php else: ?>
        <?php foreach ($toko_pilihan as $toko): ?>
            <article class="store-card">
                <a href="toko.php?id=<?php echo $toko['id']; ?>">
                    <img src="<?php echo safe_output($toko['store_banner'] ?? 'https://placehold.co/300x150/2e8b57/ffffff?text=Banner'); ?>" alt="Banner <?php echo safe_output($toko['store_name']); ?>">
                    <div class="store-info">
                        <img src="<?php echo safe_output($toko['profile_picture'] ?? 'https://placehold.co/80x80/ffffff/000000?text=L'); ?>" alt="Logo <?php echo safe_output($toko['store_name']); ?>">
                        <div>
                            <h3><?php echo safe_output($toko['store_name']); ?></h3>
                            <p><?php echo safe_output($toko['store_description'] ?? 'Deskripsi toko belum tersedia.'); ?></p>
                        </div>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
    </main>

    <footer style="text-align: center; padding: 2rem; margin-top: 2rem;">
        <p>&copy; 2025 KreasiLokal.id</p>
    </footer>

</body>
</html>
