<?php
require_once 'fungsi.php';
check_login('pembeli');
// Mengambil info saldo dari database
$wallet_info = get_user_wallet_info($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo Akun - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Lengkap untuk Halaman Saldo */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { font-size: 1.5rem; font-weight: bold; color: #2e8b57; text-decoration: none; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .card { padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 2rem; display: flex; align-items: center; }
        .page-header i { color: #2e8b57; font-size: 1.5rem; margin-right: 1rem; }
        .page-header h2 { margin: 0; font-size: 1.8rem; font-weight: 600; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-secondary { background-color: #f0f0f0; color: #333; border: 1px solid #ddd; }
        .balance-box { background-color: #2e8b57; color: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; }
        .balance-box p { margin:0; font-size: 1rem; }
        .balance-box h3 { margin: 0.5rem 0; font-size: 2.5rem; }
        .item-list { border-top: 1px solid #eee; margin-top: 2rem; padding-top: 1rem; }
        .list-item { display: flex; align-items: center; padding: 1.5rem 0; border-bottom: 1px solid #eee; }
        .list-item:last-child { border-bottom: none; }
        .list-item .icon { font-size: 1.8rem; margin-right: 1.5rem; width: 40px; text-align: center; }
        .list-item .content { flex-grow: 1; }
        .list-item .content h4 { margin: 0 0 0.25rem 0; }
        .list-item .content p { margin: 0; color: #777; font-size: 0.9rem; }
        .list-item .actions { font-weight: bold; }
        footer { text-align: center; margin-top: 3rem; padding: 1rem; color: #888; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="profilpembeli.php" class="logo">KreasiLokal.id</a>
        <a href="javascript:history.back()" style="color: #555;">Kembali</a>
    </nav>
    <div class="container">
        <div class="card">
            <div class="page-header">
                <i class="fas fa-money-bill-wave"></i>
                <h2>Saldo Akun</h2>
            </div>
            <div class="balance-box">
                <p>Saldo Anda Saat Ini</p>
                <h3><?php echo format_price($wallet_info['saldo'] ?? 0); ?></h3>
                <a href="#" class="btn btn-secondary" style="margin-top: 1rem;"><i class="fas fa-plus-circle"></i> Top Up Saldo</a>
            </div>
            <h4>Riwayat Transaksi</h4>
            <div class="item-list">
                 <div style="text-align:center; padding: 2rem;">
                    <p>Belum ada riwayat transaksi.</p>
                </div>
            </div>
        </div>
    </div>
    <footer >
        <p>&copy; 2025 KreasiLokal.id</p>
    </footer>
</body>
</html>