<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background-color: #f9fafb; 
            margin: 0; 
            color: #333; 
        }
        .dashboard-layout { 
            display: flex; 
        }
        
        /* Sidebar Navigasi */
        .dashboard-sidebar {
            width: 260px;
            background-color: #111827; /* Dark Gray */
            color: #d1d5db;
            min-height: 100vh;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        .sidebar-header { 
            text-align: center; 
            margin-bottom: 2.5rem; 
        }
        .sidebar-header .logo { 
            font-size: 1.5rem; 
            color: white; 
            font-weight: bold; 
            text-decoration: none;
        }
        .sidebar-nav .nav-item { 
            display: flex;
            align-items: center;
            padding: 0.9rem 1rem; 
            border-radius: 5px; 
            text-decoration: none; 
            color: #d1d5db; 
            margin-bottom: 0.5rem; 
            transition: all 0.2s ease-in-out;
        }
        .sidebar-nav .nav-item i { 
            margin-right: 12px; 
            width: 20px; 
            text-align: center; 
            font-size: 1.1rem;
        }
        .sidebar-nav .nav-item.active, .sidebar-nav .nav-item:hover { 
            background-color: #374151; /* Lighter Gray */
            color: white; 
        }

        /* Konten Utama */
        .dashboard-main { 
            flex-grow: 1; 
            padding: 2rem 2.5rem; 
        }
        .main-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 2rem; 
        }
        .main-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .btn-primary {
            padding: 0.7rem 1.2rem;
            border: none;
            background: #2e8b57;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 1.5rem; 
            margin-bottom: 2rem; 
        }
        .stat-card { 
            background: #fff; 
            padding: 1.5rem; 
            border-radius: 8px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .stat-card .stat-icon { 
            font-size: 1.5rem; 
            color: #2e8b57; 
            margin-bottom: 1rem; 
            background-color: #e9f5ee;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-card .stat-value { 
            font-size: 2rem; 
            font-weight: bold; 
        }
        .stat-card .stat-label { 
            color: #6b7280; 
            font-size: 0.9rem;
        }
        
        .card { 
            background-color: #fff; 
            padding: 1.5rem; 
            border-radius: 8px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .card h3 { 
            border-bottom: 1px solid #eee; 
            padding-bottom: 1rem; 
            margin-top: 0; 
            font-size: 1.2rem;
        }
        .order-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .order-table th, .order-table td { 
            padding: 1rem; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar Navigasi -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-header">
                <a href="index.html" class="logo"><i class="fas fa-leaf"></i> KreasiLokal</a>
            </div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="uploadproduk.html" class="nav-item"><i class="fas fa-box-open"></i> Produk Saya</a>
                <a href="#pesanan" class="nav-item"><i class="fas fa-inbox"></i> Pesanan Masuk</a>
                <a href="#keuangan" class="nav-item"><i class="fas fa-wallet"></i> Keuangan</a>
                <a href="#pengaturan" class="nav-item"><i class="fas fa-store"></i> Pengaturan Toko</a>
                <a href="index.html" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Konten Utama -->
        <main class="dashboard-main">
            <header class="main-header">
                <h2>Selamat Datang, Toko Batik Juwita!</h2>
                <a href="uploadproduk.php" class="btn-primary"><i class="fas fa-plus"></i> Tambah Produk Baru</a>
            </header>

            <!-- Ringkasan Statistik -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-value">Rp 12.5 jt</div>
                    <div class="stat-label">Pendapatan (Bulan Ini)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-value">15</div>
                    <div class="stat-label">Pesanan Baru</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value">250</div>
                    <div class="stat-label">Produk Terjual</div>
                </div>
                 <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-eye"></i></div>
                    <div class="stat-value">1,2 rb</div>
                    <div class="stat-label">Kunjungan Toko</div>
                </div>
            </section>

            <!-- Pesanan Terbaru -->
            <section class="card">
                <h3>Pesanan Terbaru</h3>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-12345</td>
                            <td>Budi Santoso</td>
                            <td>Rp 723.500</td>
                            <td>Perlu Dikirim</td>
                        </tr>
                         <tr>
                            <td>#ORD-12343</td>
                            <td>Ani Suryani</td>
                            <td>Rp 150.000</td>
                            <td>Perlu Dikirim</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
