<?php
session_start();
require 'fungsi.php';

// Cek apakah pengguna sudah login sebagai penjual
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penjual') {
    header("Location: loginpenjual.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data pengguna terbaru dari database, termasuk status verifikasi
$user_data = get_user_by_id($user_id); // Gunakan fungsi get_user_by_id dari fungsi.php

if (!$user_data) {
    // Jika data tidak ditemukan, logout dan redirect ke login
    logout_user();
    header("Location: loginpenjual.php");
    exit();
}

$status = $user_data['verification_status'];

// --- ROUTER LOGIC ---
// Arahkan pengguna berdasarkan status verifikasinya
switch ($status) {
    case 0:
        header('Location: verifikasi-data-diri.php');
        exit();
    case 1:
        header('Location: informasi-toko.php');
        exit();
    case 2:
        header('Location: uploadproduk.php');
        exit();
    case 3:
        // Status 3 berarti pengguna sudah terverifikasi sepenuhnya dan bisa melihat dashboard.
        // Biarkan script lanjut untuk menampilkan konten HTML di bawah ini.
        break;
    default:
        // Jika status tidak terdefinisi, arahkan ke langkah awal
        header('Location: verifikasi-data-diri.php');
        exit();
}

// Data untuk ditampilkan di dashboard (jika status = 3)
$store_name = htmlspecialchars($user_data['store_name']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - <?= $store_name ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Salin CSS dari file profilpenjual.php asli Anda ke sini */
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background-color:#f9fafb;margin:0;color:#333;}
        .dashboard-layout{display:flex;}
        .dashboard-sidebar{width:260px;background-color:#111827;color:#d1d5db;min-height:100vh;padding:1.5rem;box-sizing:border-box;}
        .sidebar-header{text-align:center;margin-bottom:2.5rem;}
        .sidebar-header .logo{font-size:1.5rem;color:white;font-weight:bold;text-decoration:none;}
        .sidebar-nav .nav-item{display:flex;align-items:center;padding:0.9rem 1rem;border-radius:5px;text-decoration:none;color:#d1d5db;margin-bottom:0.5rem;transition:all 0.2s ease-in-out;}
        .sidebar-nav .nav-item.active,.sidebar-nav .nav-item:hover{background-color:#374151;color:white;}
        .dashboard-main{flex-grow:1;padding:2rem 2.5rem;}
        .main-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;}
        .btn-primary{padding:0.7rem 1.2rem;border:none;background:#2e8b57;color:white;border-radius:5px;cursor:pointer;text-decoration:none;}
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="dashboard-sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="logo"><i class="fas fa-leaf"></i> KreasiLokal</a>
            </div>
            <nav class="sidebar-nav">
                <a href="profilpenjual.php" class="nav-item active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="uploadproduk.php" class="nav-item"><i class="fas fa-box-open"></i> Produk Saya</a>
                <a href="#" class="nav-item"><i class="fas fa-inbox"></i> Pesanan Masuk</a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <header class="main-header">
                <h2>Selamat Datang, <?= $store_name ?>!</h2>
                <a href="uploadproduk.php" class="btn-primary"><i class="fas fa-plus"></i> Tambah Produk Baru</a>
            </header>

            <div class="card">
                <h3>Dashboard Anda</h3>
                <p>Anda sudah terverifikasi dan siap untuk berjualan. Kelola produk dan pesanan Anda melalui menu di samping.</p>
            </div>
        </main>
    </div>
</body>
</html>