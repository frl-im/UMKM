<?php
require_once 'fungsi.php';
check_login('pembeli');
$user_id = $_SESSION['user_id'];
$message = '';

// Logika untuk menangani klaim voucher
if (isset($_POST['klaim_voucher'])) {
    $voucher_code = $_POST['voucher_code'] ?? '';
    if (!empty($voucher_code)) {
        $message = klaim_voucher_by_code($user_id, $voucher_code);
    } else {
        $message = "Silakan masukkan kode voucher.";
    }
}

// Ambil semua voucher yang dimiliki pengguna
$vouchers = ambil_voucher_user($user_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Saya - KreasiLokal.id</title>
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
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #555; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #2e8b57; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background-color: #2e8b57; color: white; }
        .item-list { border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        .list-item { display: flex; align-items: center; padding: 1.5rem; border-bottom: 1px solid #eee; }
        .list-item:last-child { border-bottom: none; }
        .list-item.used { opacity: 0.5; background-color: #f9f9f9; }
        .list-item .icon { font-size: 1.8rem; color: #2e8b57; margin-right: 1.5rem; }
        .list-item .content { flex-grow: 1; }
        .list-item .content h4 { margin: 0 0 0.25rem 0; }
        .list-item .content p { margin: 0; color: #777; font-size: 0.9rem; }
        .list-item .actions a { color: #2e8b57; text-decoration: none; font-weight: 600; }
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 8px; text-align: center; font-weight: 600; }
        .message.success { background: #d4edda; color: #155724; }
        .message.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <nav class="navbar">
    <a href="profilpembeli.php" class="logo">KreasiLokal.id</a>
    <a href="voucher.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">
        <i class="fas fa-search"></i> Cari Voucher
    </a>
</nav>
    <div class="container">
        <div class="page-header">
            <i class="fas fa-tags"></i>
            <h2>Voucher Saya</h2>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo (strpos($message, 'berhasil') !== false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="voucher_code">Masukkan Kode Voucher</label>
                <div style="display: flex; gap: 1rem;">
                    <input type="text" id="voucher_code" name="voucher_code" placeholder="Contoh: LOKALJUARA" style="flex-grow: 1;">
                    <button type="submit" name="klaim_voucher" class="btn btn-primary">Klaim</button>
                </div>
            </div>
        </form>

        <div class="item-list">
            <?php if(empty($vouchers)): ?>
                <div style="text-align:center; padding: 2rem;">
                    <p>Anda belum memiliki voucher.</p>
                </div>
            <?php else: ?>
                <?php foreach($vouchers as $voucher): ?>
                    <div class="list-item <?php if($voucher['is_used']) echo 'used'; ?>">
                        <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                        <div class="content">
                            <h4><?echo isset($voucher['title'])? $voucher['title'] : 'Judul tidak tersedia';></h4>
                            <p><?php echo safe_output($voucher['voucher_code']); ?></p>
                            <?php if($voucher['is_used']): ?>
                                <p style="color: #777;">Sudah digunakan</p>
                            <?php else: ?>
                                <p style="color: #d9534f;">Berakhir pada <?php echo date('d M Y', strtotime($voucher['expiry_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if(!$voucher['is_used']): ?>
                        <div class="actions"><a href="index.php">Pakai</a></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>