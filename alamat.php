<?php
// profilpembeli.php
require_once 'fungsi.php';
// Pastikan hanya pembeli yang bisa akses
check_login('pembeli');
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Pengiriman - KreasiLokal.id</title>
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
        .btn-danger { background-color: #d9534f; color: white; }
        .item-list { border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
        .list-item { display: flex; align-items: center; padding: 1.5rem; border-bottom: 1px solid #eee; gap: 1rem; }
        .list-item:last-child { border-bottom: none; }
        .list-item .content { flex-grow: 1; }
        .list-item .content h4 { margin: 0 0 0.25rem 0; }
        .list-item .content p { margin: 0; color: #777; font-size: 0.9rem; }
        .list-item .actions { display: flex; gap: 0.5rem; }
        .actions a { color: #2e8b57; text-decoration: none; font-weight: 600; padding: 0.5rem; }
        footer { text-align: center; margin-top: 3rem; padding: 1rem; color: #888; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="profilpembeli.php" class="logo">KreasiLokal.id</a>
    </nav>
    <div class="container">
        <div class="page-header">
            <i class="fas fa-map-marker-alt"></i>
            <h2>Alamat Pengiriman</h2>
        </div>
        <div style="margin-bottom: 2rem; text-align: right;">
             <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Alamat Baru</a>
        </div>
        <div class="item-list">
            <div class="list-item">
                <div class="content">
                    <h4>Budiyono Joko <span style="background-color: #eaf3ef; color: #2e8b57; padding: 3px 8px; font-size: 0.8rem; border-radius: 5px; margin-left: 10px;">Utama</span></h4>
                    <p>(+62) 812-3456-7890</p>
                    <p>Jl. Pahlawan No. 123, Mugassari, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50243</p>
                </div>
                <div class="actions">
                    <a href="#">Ubah</a>
                    <a href="#" class="btn btn-danger" style="padding: 8px 12px;">Hapus</a>
                </div>
            </div>
            <div class="list-item">
                <div class="content">
                    <h4>Alamat Kantor</h4>
                    <p>(+62) 898-7654-3210</p>
                    <p>Menara Suara Merdeka, Jl. Pandanaran No. 30, Pekunden, Kota Semarang, Jawa Tengah 50241</p>
                </div>
                 <div class="actions">
                    <a href="#">Ubah</a>
                    <a href="#" class="btn btn-danger" style="padding: 8px 12px;">Hapus</a>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 KreasiLokal.id</p>
    </footer>
</body>
</html>