<?php
require_once 'fungsi.php';
check_login('pembeli');

// Logika untuk menangani form alamat
if (isset($_POST['tambah_alamat'])) {
    add_address($_SESSION['user_id'], $_POST);
    header("Location: profilpembeli.php#alamat-section");
    exit();
}
if (isset($_GET['set_primary'])) {
    set_primary_address($_SESSION['user_id'], (int)$_GET['set_primary']);
    header("Location: profilpembeli.php#alamat-section");
    exit();
}

// Ambil semua data alamat pengguna
$addresses = get_all_addresses($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #2c3e50;
            line-height: 1.6;
        }

        /* Navigation */
        .top-nav {
            background: linear-gradient(135deg, #34A853 0%, #2d8f47 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(52, 168, 83, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Profile Header */
        .profile-header {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #34A853, #2d8f47, #34A853);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #34A853, #2d8f47);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 2rem;
            box-shadow: 0 8px 25px rgba(52, 168, 83, 0.3);
            position: relative;
        }

        .profile-avatar::after {
            content: '';
            position: absolute;
            inset: -3px;
            background: linear-gradient(45deg, #34A853, transparent, #34A853);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.7;
        }

        .profile-avatar i {
            font-size: 2.5rem;
            color: white;
        }

        .profile-info h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .profile-info p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        /* Menu Sections */
        .menu-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .menu-section:hover {
            transform: translateY(-2px);
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .section-title h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .section-title a {
            color: #34A853;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .section-title a:hover {
            background: rgba(52, 168, 83, 0.1);
            transform: translateX(5px);
        }

        /* Icon Grid */
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
        }

        .icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s ease;
            padding: 1.5rem;
            border-radius: 12px;
            background: #f8f9fa;
            position: relative;
            overflow: hidden;
        }

        .icon-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(52, 168, 83, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .icon-item:hover::before {
            left: 100%;
        }

        .icon-item:hover {
            color: #34A853;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(52, 168, 83, 0.2);
        }

        .icon-item .icon {
            font-size: 2.5rem;
            color: #34A853;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .icon-item:hover .icon {
            transform: scale(1.1);
        }

        .icon-item span {
            font-weight: 500;
            text-align: center;
        }
        /* [BARU] Sub-Navigation Bar */
        .sub-nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .sub-nav a {
            background: #f8f9fa;
            text-decoration: none;
            color: #2c3e50;
            padding: 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .sub-nav a i {
            font-size: 1.5rem;
            color: #34A853;
            width: 30px;
            text-align: center;
        }

        .sub-nav a:hover {
            background: rgba(52, 168, 83, 0.05);
            color: #34A853;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 168, 83, 0.1);
            border-color: #34A853;
        }

        /* Address Section */
        .address-list {
            margin: 2rem 0;
        }

        .address-item {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }

        .address-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .address-item.primary {
            border-left: 4px solid #34A853;
            background: linear-gradient(135deg, rgba(52, 168, 83, 0.05), rgba(52, 168, 83, 0.02));
        }

        .address-item strong {
            color: #2c3e50;
            font-size: 1.1rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .address-item p {
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .address-item a {
            color: #34A853;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border: 1px solid #34A853;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .address-item a:hover {
            background: #34A853;
            color: white;
        }

        .primary-badge {
            color: #34A853;
            font-weight: 600;
            background: rgba(52, 168, 83, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Address Form */
        .address-form {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .address-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .address-form input,
        .address-form textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .address-form input:focus,
        .address-form textarea:focus {
            outline: none;
            border-color: #34A853;
            box-shadow: 0 0 0 3px rgba(52, 168, 83, 0.1);
        }

        .address-form button {
            background: linear-gradient(135deg, #34A853, #2d8f47);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 168, 83, 0.3);
        }

        .address-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.4);
        }

        /* Divider */
        .divider {
            margin: 3rem 0;
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, #dee2e6, transparent);
        }

        /* Section Headers */
        .section-header {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-header i {
            color: #34A853;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-avatar {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .nav-container {
                padding: 0 1rem;
            }

            .nav-links a {
                margin-left: 1rem;
            }

            .icon-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }

            .section-title {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-container">
            <a href="index.php" class="logo">KreasiLokal.id</a>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h2><?php echo safe_output($_SESSION['user_name']); ?></h2>
                <p><?php echo safe_output($_SESSION['user_email']); ?></p>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="menu-section">
            <div class="section-title">
                <h3><i class="fas fa-shopping-bag"></i> Pesanan Saya</h3>
                <a href="pesanan.php">Lihat Riwayat Pesanan <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="icon-grid">
                <a href="pesanan.php" class="icon-item">
                    <i class="fas fa-wallet icon"></i>
                    <span>Belum Bayar</span>
                </a>
                <a href="pesanan.php" class="icon-item">
                    <i class="fas fa-box-open icon"></i>
                    <span>Dikemas</span>
                </a>
                <a href="pesanan.php" class="icon-item">
                    <i class="fas fa-truck icon"></i>
                    <span>Dikirim</span>
                </a>
                <a href="#" class="icon-item">
                    <i class="fas fa-star icon"></i>
                    <span>Beri Penilaian</span>
                </a>
            </div>
        </div>

        <!-- Wallet Section -->
        <div class="menu-section">
            <div class="section-title">
                <h3><i class="fas fa-wallet"></i> Dompet Saya</h3>
            </div>
            <div class="sub-nav">
                <a href="saldo.php">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Saldo Akun</span>
                </a>
                <a href="vouchersaya.php">
                    <i class="fas fa-tags"></i>
                    <span>Voucher Saya</span>
                </a>
                <a href="paylater.php">
                    <i class="fas fa-credit-card"></i>
                    <span>Paylater</span>
                </a>
            </div>
        </div>
        <!-- Account Section -->
        <div id="alamat-section" class="menu-section">
            <div class="section-title">
                <h3><i class="fas fa-user-cog"></i> Akun Saya</h3>
            </div>
             <div class="sub-nav">
                <a href="ubahprofil.php">
                    <i class="fas fa-user-edit"></i>
                    <span>Ubah Profil</span>
                </a>
                <a href="alamat.php">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Alamat Pengiriman</span>
                </a>
                <a href="wishlist.php">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                </a>
                <a href="keamanan.php">
                    <i class="fas fa-shield-alt"></i>
                    <span>Keamanan Akun</span>
                </a>
            </div>
            
            <hr class="divider">
            
            <div class="section-header">
                <i class="fas fa-map-marked-alt"></i>
                Alamat Tersimpan
            </div>
            
            <div class="address-list">
                <?php if (empty($addresses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Anda belum memiliki alamat tersimpan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($addresses as $address): ?>
                        <div class="address-item <?php if($address['is_primary']) echo 'primary'; ?>">
                            <strong><?php echo safe_output($address['label']); ?></strong>
                            <span style="color: #6c757d; font-weight: normal;">- <?php echo safe_output($address['recipient_name']); ?></span>
                            <p>
                                <i class="fas fa-phone" style="color: #34A853; margin-right: 0.5rem;"></i>
                                <?php echo safe_output($address['phone']); ?><br>
                                <i class="fas fa-map-marker-alt" style="color: #34A853; margin-right: 0.5rem;"></i>
                                <?php echo safe_output($address['full_address']); ?>
                            </p>
                            <?php if (!$address['is_primary']): ?>
                                <a href="profilpembeli.php?set_primary=<?php echo $address['id']; ?>#alamat-section">
                                    <i class="fas fa-star"></i> Jadikan Alamat Utama
                                </a>
                            <?php else: ?>
                                <span class="primary-badge">
                                    <i class="fas fa-check-circle"></i>
                                    Alamat Utama
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
            
            <hr class="divider">
            
            
            <div class="address-list">
                <?php if (empty($addresses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Anda belum memiliki alamat tersimpan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($addresses as $address): ?>
                        <div class="address-item <?php if($address['is_primary']) echo 'primary'; ?>">
                            <strong><?php echo safe_output($address['label']); ?></strong>
                            <span style="color: #6c757d; font-weight: normal;">- <?php echo safe_output($address['recipient_name']); ?></span>
                            <p>
                                <i class="fas fa-phone" style="color: #34A853; margin-right: 0.5rem;"></i>
                                <?php echo safe_output($address['phone']); ?><br>
                                <i class="fas fa-map-marker-alt" style="color: #34A853; margin-right: 0.5rem;"></i>
                                <?php echo safe_output($address['full_address']); ?>
                            </p>
                            <?php if (!$address['is_primary']): ?>
                                <a href="profilpembeli.php?set_primary=<?php echo $address['id']; ?>#alamat-section">
                                    <i class="fas fa-star"></i> Jadikan Alamat Utama
                                </a>
                            <?php else: ?>
                                <span class="primary-badge">
                                    <i class="fas fa-check-circle"></i>
                                    Alamat Utama
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Smooth scroll untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animation untuk cards saat scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.menu-section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(section);
        });
    </script>
</body>
</html>