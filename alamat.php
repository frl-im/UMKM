<?php
require_once 'fungsi.php';
check_login('pembeli');

// Logika untuk menangani form
if (isset($_POST['tambah_alamat'])) {
    add_address($_SESSION['user_id'], $_POST);
    header("Location: alamat.php?status=add_success");
    exit();
}
if (isset($_GET['set_primary'])) {
    set_primary_address($_SESSION['user_id'], (int)$_GET['set_primary']);
    header("Location: alamat.php?status=primary_success");
    exit();
}
if (isset($_GET['delete'])) {
    // Anda perlu membuat fungsi delete_address di fungsi.php
    // delete_address($_SESSION['user_id'], (int)$_GET['delete']);
    // header("Location: alamat.php?status=delete_success");
    // exit();
}

// Ambil semua alamat pengguna
$addresses = get_all_addresses($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Pengiriman - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* (CSS dari file alamat.php Anda, dengan sedikit tambahan) */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; align-items: center; }
        .navbar .back-link { font-size: 1.2rem; color: #333; text-decoration: none; }
        .navbar .logo { font-size: 1.5rem; font-weight: bold; color: #2e8b57; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 900px; margin: 2rem auto; }
        .card { padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 2rem; display: flex; align-items: center; }
        .page-header i { color: #2e8b57; font-size: 1.5rem; margin-right: 1rem; }
        .page-header h2 { margin: 0; font-size: 1.8rem; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background-color: #2e8b57; color: white; }
        .list-item { display: flex; align-items: flex-start; padding: 1.5rem 0; border-bottom: 1px solid #eee; gap: 1rem; }
        .list-item:last-child { border-bottom: none; }
        .content { flex-grow: 1; }
        .content h4 { margin: 0 0 0.5rem 0; }
        .actions a { color: #2e8b57; text-decoration: none; margin-right: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="profilpembeli.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
        <a href="index.php" class="logo">KreasiLokal.id</a>
    </nav>

    <div class="container">
        <div class="card">
            <div class="page-header">
                <i class="fas fa-map-marker-alt"></i>
                <h2>Alamat Pengiriman</h2>
            </div>

            <div class="item-list">
                <?php if(empty($addresses)): ?>
                    <p>Anda belum menambahkan alamat.</p>
                <?php else: ?>
                    <?php foreach($addresses as $address): ?>
                        <div class="list-item">
                            <div class="content">
                                <h4>
                                    <?php echo safe_output($address['label']); ?>
                                    <?php if($address['is_primary']): ?>
                                        <span style="background-color: #eaf3ef; color: #2e8b57; padding: 3px 8px; font-size: 0.8rem; border-radius: 5px; margin-left: 10px;">Utama</span>
                                    <?php endif; ?>
                                </h4>
                                <p><strong><?php echo safe_output($address['recipient_name']); ?></strong></p>
                                <p><?php echo safe_output($address['phone']); ?></p>
                                <p><?php echo safe_output($address['full_address']); ?></p>
                            </div>
                            <div class="actions">
                                <?php if(!$address['is_primary']): ?>
                                    <a href="alamat.php?set_primary=<?php echo $address['id']; ?>">Jadikan Utama</a>
                                <?php endif; ?>
                                </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <hr style="margin: 2rem 0;">
            <h3>Tambah Alamat Baru</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Label Alamat (Contoh: Rumah, Kantor)</label>
                    <input type="text" name="label" required>
                </div>
                <div class="form-group">
                    <label>Nama Penerima</label>
                    <input type="text" name="recipient_name" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="full_address" rows="4" required></textarea>
                </div>
                <button type="submit" name="tambah_alamat" class="btn btn-primary">Simpan Alamat</button>
            </form>
        </div>
    </div>
</body>
</html>