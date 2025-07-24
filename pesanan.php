<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Umum */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { font-size: 1.5rem; font-weight: bold; color: #2e8b57; text-decoration: none; }
        .container { max-width: 900px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 2rem; display: flex; align-items: center; }
        .page-header i { color: #2e8b57; font-size: 1.5rem; margin-right: 1rem; }
        .page-header h2 { margin: 0; font-size: 1.8rem; font-weight: 600; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background-color: #2e8b57; color: white; }
        .empty-state { text-align: center; padding: 4rem 2rem; border: 2px dashed #ddd; border-radius: 8px; background-color: #fafafa; }
        .empty-state i { font-size: 4rem; color: #ccc; margin-bottom: 1.5rem; }
        .empty-state p { font-size: 1.2rem; color: #777; margin: 0; }
        footer { text-align: center; margin-top: 3rem; padding: 1rem; color: #888; }
        /* CSS Khusus Halaman Pesanan */
        .tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 2rem; }
        .tab-item { padding: 1rem 1.5rem; cursor: pointer; font-weight: 600; color: #777; border-bottom: 3px solid transparent; margin-bottom: -2px; }
        .tab-item.active { color: #2e8b57; border-bottom-color: #2e8b57; }
        .order-item { border: 1px solid #eee; border-radius: 8px; margin-bottom: 1.5rem; padding: 1.5rem; }
        .order-header { display: flex; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid #eee; margin-bottom: 1rem; }
        .order-header p, .order-header h4 { margin: 0; }
        .order-product { display: flex; gap: 1rem; align-items: center; }
        .order-product img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .order-footer { text-align: right; margin-top: 1rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="profilpembeli.php" class="logo">KreasiLokal.id</a>
    </nav>
    <div class="container">
        <div class="page-header">
            <i class="fas fa-box-open"></i>
            <h2>Pesanan Saya</h2>
        </div>
        <div class="tabs">
            <div class="tab-item active">Belum Bayar</div>
            <div class="tab-item">Dikemas</div>
            <div class="tab-item">Dikirim</div>
            <div class="tab-item">Beri Penilaian</div>
            <div class="tab-item">Selesai</div>
        </div>
        <div class="tab-content">
            <div class="order-item">
                <div class="order-header">
                    <h4>Toko Batik Trusmi</h4>
                    <p style="color: #d9534f; font-weight: bold;">Menunggu Pembayaran</p>
                </div>
                <div class="order-product">
                    <img src="https://via.placeholder.com/150" alt="Produk">
                    <div>
                        <h4>Kemeja Batik Mega Mendung</h4>
                        <p>1 x Rp150.000</p>
                    </div>
                </div>
                <div class="order-footer">
                    <p>Total Pesanan: <strong style="font-size: 1.2rem; color: #2e8b57;">Rp155.000</strong></p>
                    <a href="#" class="btn btn-primary">Bayar Sekarang</a>
                </div>
            </div>
            </div>
    </div>
    <footer>
        <p>&copy; 2025 KreasiLokal.id</p>
    </footer>
</body>
</html>