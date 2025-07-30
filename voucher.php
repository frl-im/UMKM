<?php
require_once 'fungsi.php';
// check_login('pembeli'); // Opsional: aktifkan jika hanya user login yang bisa lihat
$vouchers = ambil_semua_voucher();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher & Promo - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f4f4f4; 
            margin: 0; 
            color: #333; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 15px; 
        }
        .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; }
        .navbar .container { display: flex; align-items: center; justify-content: space-between; }
        .nav-left { display: flex; align-items: center; }
        .nav-left .back-btn { font-size: 1.5rem; color: #333; text-decoration: none; margin-right: 1.5rem; }
        .nav-left .page-title { font-size: 1.2rem; font-weight: 600; }
        .voucher-header { text-align: center; padding: 2rem 0; }
        .voucher-header h1 { font-size: 2.5rem; color: #2e8b57; margin: 0; }
        .voucher-header p { font-size: 1.1rem; color: #555; }
        .voucher-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .voucher-card {
            display: flex;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.2s;
        }
        .voucher-card:hover { transform: translateY(-5px); }
        .voucher-logo {
            flex: 0 0 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9f5ee;
        }
        .voucher-logo i { font-size: 3rem; color: #2e8b57; }
        .voucher-details { padding: 1rem 1.5rem; flex-grow: 1; border-left: 2px dashed #ddd; position: relative; }
        .voucher-details::before, .voucher-details::after { content: ''; position: absolute; left: -11px; width: 20px; height: 20px; background: #f4f4f4; border-radius: 50%; }
        .voucher-details::before { top: -10px; }
        .voucher-details::after { bottom: -10px; }
        .voucher-details h3 { margin: 0 0 0.5rem 0; color: #2e8b57; }
        .voucher-details p { margin: 0 0 0.5rem 0; font-size: 0.9rem; }
        .voucher-details small { color: #777; }
        .voucher-action { display: flex; align-items: center; justify-content: center; padding: 0 1.5rem; }
        .voucher-action button { padding: 0.6rem 1.2rem; border: none; background-color: #ff8c00; color: white; font-weight: 600; border-radius: 5px; cursor: pointer; }
        .empty-state { text-align: center; padding: 3rem; background-color: #fff; border-radius: 8px; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-left">
                <a href="index.php" class="back-btn" title="Kembali"><i class="fas fa-arrow-left"></i></a>
                <span class="page-title">Voucher & Promo</span>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="voucher-header">
            <h1>Voucher & Promo</h1>
            <p>Klaim dan gunakan voucher untuk mendapatkan potongan harga!</p>
        </section>

        <section class="voucher-list">
            <?php if (empty($vouchers)): ?>
                <div class="empty-state">
                    <p>Belum ada voucher yang tersedia saat ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($vouchers as $voucher): ?>
                    <article class="voucher-card">
                        <div class="voucher-logo">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="voucher-details">
                            <h3><?php echo safe_output($voucher['title']); ?></h3>
                            <p>Kode: <strong><?php echo safe_output($voucher['voucher_code']); ?></strong></p>
                            <small>Berlaku hingga: <?php echo date('d M Y', strtotime($voucher['expiry_date'])); ?></small>
                        </div>
                        <div class="voucher-action">
                            <form action="vouchersaya.php" method="POST">
                                <input type="hidden" name="voucher_code" value="<?php echo safe_output($voucher['voucher_code']); ?>">
                                <button type="submit" name="klaim_voucher">Claim</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>