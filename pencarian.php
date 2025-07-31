<?php
require_once 'fungsi.php';

// 1. Ambil kata kunci dari URL (?q=...)
$keyword = $_GET['q'] ?? '';

// 2. Siapkan query untuk mencari produk berdasarkan nama atau deskripsi
// Menggunakan prepared statement untuk keamanan
global $koneksi;
$searchTerm = "%" . $keyword . "%";
$stmt = mysqli_prepare($koneksi,
    "SELECT * FROM products
     WHERE (name LIKE ? OR description LIKE ?)
     AND status = 'active'"
);

mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian untuk "<?php echo safe_output($keyword); ?>"</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f4f4f4; margin: 0; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; }
        .navbar .container { display: flex; align-items: center; justify-content: space-between; }
        .nav-left { display: flex; align-items: center; }
        .nav-left .back-btn { font-size: 1.5rem; color: #333; text-decoration: none; margin-right: 1.5rem; }
        .page-title { font-size: 1.2rem; font-weight: 600; }
        .page-title span { color: #2e8b57; }
        .nav-right a { font-size: 1.5rem; color: #555; text-decoration: none; margin-left:1rem; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; margin-top: 2rem;}
        .product-card { background-color: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; text-decoration: none; color: #333; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-image img { width: 100%; height: 220px; object-fit: cover; }
        .product-info { padding: 1rem; flex-grow: 1; }
        .product-info h4 { font-size: 1rem; margin: 0 0 0.5rem 0; height: 40px; }
        .product-price { font-size: 1.2rem; font-weight: bold; color: #c0392b; }
        .empty-state { text-align: center; padding: 4rem; background: #fafafa; border-radius: 8px; }
        .empty-state i { font-size: 3rem; color: #ccc; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-left">
                <a href="index.php" class="back-btn" title="Kembali ke Beranda"><i class="fas fa-arrow-left"></i></a>
                <h1 class="page-title">Hasil untuk: "<span><?php echo safe_output($keyword); ?></span>"</h1>
            </div>
             <div class="nav-right">
                <a href="keranjang.php" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </header>
    <main class="container">
        <section class="results">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>Produk tidak ditemukan untuk "<?php echo safe_output($keyword); ?>".</p>
                    <p>Coba gunakan kata kunci lain.</p>
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