<?php

   $koneksi = mysqli_connect("localhost:3306","root","","Kreasidb");

   if(!$koneksi)
   {
        die("Koneksi Gagal!".mysqli_connect_error());
   }
   else
   {
        echo "Koneksi Berhasil!!!";
   }

   require_once 'fungsi.php';

// Cek apakah user sudah login tanpa redirect paksa
$is_logged_in = is_logged_in();

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KreasiLokal.id - Tradisi Bertemu Inovasi</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    /* Reset CSS & Font Dasar */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
      line-height: 1.6;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }

    /* Header */
    .navbar {
      background-color: #ffffff;
      padding: 1rem 0;
      border-bottom: 1px solid #e0e0e0;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .navbar .container {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .navbar .logo a {
      font-size: 1.8rem;
      font-weight: bold;
      color: #2e8b57;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .main-search-bar {
      flex-grow: 1;
      margin: 0 2rem;
      display: flex;
      max-width: 500px;
    }
    
    .main-search-bar input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 2px solid #2e8b57;
      border-right: none;
      border-radius: 5px 0 0 5px;
      font-size: 1rem;
      outline: none;
    }
    
    .main-search-bar input:focus {
      border-color: #1e6b47;
    }
    
    .main-search-bar button {
      padding: 0 1.2rem;
      border: none;
      background-color: #2e8b57;
      color: white;
      border-radius: 0 5px 5px 0;
      cursor: pointer;
      font-size: 1.2rem;
      transition: background-color 0.3s;
    }
    
    .main-search-bar button:hover {
      background-color: #1e6b47;
    }
    
    .nav-icons a {
      font-size: 1.5rem;
      color: #555;
      text-decoration: none;
      margin-left: 1.5rem;
      transition: color 0.3s;
    }
    
    .nav-icons a:hover {
      color: #2e8b57;
    }
    
    /* Banner & Navigation */
    .promo-banner-container { 
      padding: 20px 0; 
      background-color: white; 
    }
    
    .promo-banner-container img {
      width: 100%; 
      border-radius: 5px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .quick-nav-icons { 
      padding: 20px 0; 
      background-color: white; 
      border-bottom: 10px solid #f4f4f4; 
    }
    
    .quick-nav-icons .container {
      display: flex;
      justify-content: space-around;
      text-align: center;
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .quick-nav-item { 
      text-decoration: none; 
      color: #333;
      transition: transform 0.3s;
    }
    
    .quick-nav-item:hover {
      transform: translateY(-5px);
    }
    
    .quick-nav-item i { 
      font-size: 2rem; 
      color: #2e8b57; 
      display: block; 
      margin-bottom: 8px; 
    }

    /* Flash Sale */
    .flash-sale { 
      padding: 20px 0; 
      background-color: white; 
      margin-bottom: 10px; 
    }
    
    .flash-sale-header {
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .flash-sale-header h3 { 
      font-size: 1.5rem; 
      color: #ff8c00; 
      margin: 0; 
    }
    
    .countdown-timer { 
      margin-left: 1rem; 
      font-weight: bold;
      background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
      color: white;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.9rem;
    }
    
    .see-all { 
      margin-left: auto; 
      text-decoration: none; 
      color: #007bff;
      font-weight: 500;
    }
    
    /* Product Grid */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 15px;
    }
    
    .product-card {
      background-color: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      overflow: hidden;
      text-decoration: none;
      color: #333;
      display: flex;
      flex-direction: column;
      position: relative;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .product-image { 
      position: relative; 
      overflow: hidden;
    }
    
    .product-image img { 
      width: 100%; 
      height: 200px;
      object-fit: cover;
      display: block; 
      transition: transform 0.3s;
    }
    
    .product-card:hover .product-image img {
      transform: scale(1.05);
    }
    
    .product-badge {
      position: absolute;
      top: 10px;
      right: -1px;
      background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
      color: white;
      padding: 5px 8px;
      font-size: 0.8rem;
      font-weight: bold;
      border-radius: 3px 0 0 3px;
    }
    
    .product-info { 
      padding: 15px; 
      flex-grow: 1; 
      display: flex; 
      flex-direction: column; 
    }
    
    .product-info h4 { 
      font-size: 0.95rem; 
      margin: 0 0 10px 0; 
      height: 40px;
      overflow: hidden;
      line-height: 1.3;
    }
    
    .product-price { 
      display: flex; 
      align-items: center; 
      gap: 8px; 
      margin: 10px 0; 
    }
    
    .current-price { 
      color: #ff8c00; 
      font-weight: bold; 
      font-size: 1.1rem; 
    }
    
    .original-price { 
      text-decoration: line-through; 
      color: #aaa; 
      font-size: 0.85rem; 
    }
    
    .product-origin { 
      font-size: 0.8rem; 
      color: #777;
      margin-bottom: 10px;
    }
    
    .stock-bar {
      height: 20px;
      background: linear-gradient(135deg, #ffc107, #ffca28);
      border-radius: 10px;
      text-align: center;
      font-size: 0.7rem;
      color: white;
      font-weight: bold;
      line-height: 20px;
      margin-top: auto;
      overflow: hidden;
      position: relative;
    }
    
    /* Section Headers */
    .section-header { 
      padding: 20px 0 10px 0; 
      font-size: 1.3rem; 
      color: #2e8b57;
      border-bottom: 3px solid #2e8b57; 
      margin-bottom: 20px; 
      text-transform: uppercase;
      font-weight: bold;
    }

    /* Footer Styles */
    .footer {
      background: linear-gradient(135deg, #2e8b57, #1e6b47);
      color: white;
      padding: 40px 0 0 0;
      margin-top: 50px;
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      margin-bottom: 30px;
    }

    .footer-section h3 {
      font-size: 1.2rem;
      margin-bottom: 15px;
      color: #ffffff;
      border-bottom: 2px solid #4caf50;
      padding-bottom: 5px;
      display: inline-block;
    }

    .footer-section p, .footer-section li {
      line-height: 1.6;
      color: #e8f5e8;
      margin-bottom: 8px;
    }

    .footer-section ul {
      list-style: none;
    }

    .footer-section ul li {
      margin-bottom: 8px;
    }

    .footer-section ul li a {
      color: #e8f5e8;
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-section ul li a:hover {
      color: #4caf50;
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 15px;
    }

    .social-links a {
      color: #e8f5e8;
      font-size: 1.5rem;
      transition: color 0.3s, transform 0.3s;
    }

    .social-links a:hover {
      color: #4caf50;
      transform: translateY(-3px);
    }

    .footer-bottom {
      border-top: 1px solid #4caf50;
      padding: 20px 0;
      text-align: center;
      background-color: rgba(0,0,0,0.2);
    }

    .footer-bottom p {
      margin: 0;
      color: #e8f5e8;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .navbar .container {
        flex-direction: column;
        gap: 15px;
      }
      
      .main-search-bar {
        margin: 0;
        max-width: 100%;
      }
      
      .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
      }
      
      .quick-nav-icons .container {
        justify-content: space-between;
      }
      
      .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
      }
    }
  </style>
</head>
<body>

  <header class="navbar">
    <div class="container">
      <div class="logo">
        <a href="index.html"><i class="fas fa-leaf"></i> KreasiLokal.id</a>
      </div>
      <div class="main-search-bar">
        <input type="text" placeholder="Cari batik, rendang, atau ukiran...">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="nav-icons">
        <a href="login.php" title="Login / Daftar"><i class="fas fa-user-circle"></i></a>
        <a href="keranjang.php" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
      </div>
    </div>
  </header>

  <main>
    <section class="promo-banner-container">
      <div class="container">
        <img src="https://via.placeholder.com/1170x350/2e8b57/ffffff?text=Promo+Spesial+Produk+Lokal" alt="Promo Utama">
      </div>
    </section>

    <section class="quick-nav-icons">
      <div class="container">
        <a href="voucher.html" class="quick-nav-item">
          <i class="fas fa-tags"></i>
          <span>Voucher</span>
        </a>
        <a href="flashsale.html" class="quick-nav-item">
          <i class="fas fa-bolt"></i>
          <span>Flash Sale</span>
        </a>
        <a href="tokopilihan.html" class="quick-nav-item">
          <i class="fas fa-store"></i>
          <span>Toko Pilihan</span>
        </a>
        <a href="mahasiswa.html" class="quick-nav-item">
          <i class="fas fa-user-graduate"></i>
          <span>Mahasiswa</span>
        </a>
        <a href="makanan.html" class="quick-nav-item">
          <i class="fas fa-cookie-bite"></i>
          <span>Kuliner</span>
        </a>
        <a href="kategori.html" class="quick-nav-item">
          <i class="fas fa-th-large"></i>
          <span>Kategori</span>
        </a>
      </div>
    </section>

    <section class="flash-sale" id="flash-sale">
      <div class="container">
        <div class="flash-sale-header">
          <h3><i class="fas fa-bolt"></i> FLASH SALE</h3>
          <div class="countdown-timer">
            <i class="fas fa-clock"></i> <span id="countdown">02:28:45</span>
          </div>
          <a href="flashsale.html" class="see-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="product-grid">
          <a href="detailproduk.html" class="product-card">
            <div class="product-badge">-53%</div>
            <div class="product-image">
              <img src="https://via.placeholder.com/300x300/654321/ffffff?text=Lampu+Anyaman" alt="Lampu Anyaman" />
            </div>
            <div class="product-info">
              <h4>Lampu Gantung Anyaman Bambu Handmade</h4>
              <div class="product-price">
                <span class="current-price">Rp 129.250</span>
                <span class="original-price">Rp 275.000</span>
              </div>
              <div class="product-origin">Yogyakarta</div>
              <div class="stock-bar">TERJUAL 50%</div>
            </div>
          </a>
        </div>
      </div>
    </section>

    <section class="recommendations">
      <div class="container">
        <div class="section-header"><i class="fas fa-star"></i> Rekomendasi</div>
        <div class="product-grid">
          <a href="detailproduk.html?id=1" class="product-card">
            <div class="product-badge">-18%</div>
            <div class="product-image">
              <img src="https://via.placeholder.com/300x300/2e8b57/ffffff?text=Batik+Modern" alt="Batik Modern Dress" />
            </div>
            <div class="product-info">
              <h4>Dress Batik Modern Kawung Premium</h4>
              <div class="product-price">
                <span class="current-price">Rp 450.000</span>
                <span class="original-price">Rp 550.000</span>
              </div>
              <p class="product-origin">Yogyakarta</p>
            </div>
          </a>
          <a href="detailproduk.html" class="product-card">
            <div class="product-image">
              <img src="https://via.placeholder.com/300x300/ff8c00/ffffff?text=Rendang+Instant" alt="Rendang" />
            </div>
            <div class="product-info">
              <h4>Rendang Instant Premium Asli Padang</h4>
              <div class="product-price">
                <span class="current-price">Rp 85.000</span>
              </div>
              <p class="product-origin">Sumatera Barat</p>
            </div>
          </a>
          <a href="detailproduk.html" class="product-card">
            <div class="product-image">
              <img src="https://via.placeholder.com/300x300/8b4513/ffffff?text=Ukiran+Kayu" alt="Ukiran Kayu" />
            </div>
            <div class="product-info">
              <h4>Ukiran Kayu Jati Motif Garuda</h4>
              <div class="product-price">
                <span class="current-price">Rp 1.250.000</span>
              </div>
              <p class="product-origin">Jepara</p>
            </div>
          </a>
          <a href="detailproduk.html" class="product-card">
            <div class="product-badge">-25%</div>
            <div class="product-image">
              <img src="https://via.placeholder.com/300x300/dc143c/ffffff?text=Kerajinan+Tangan" alt="Kerajinan" />
            </div>
            <div class="product-info">
              <h4>Tas Tenun Tradisional Flores</h4>
              <div class="product-price">
                <span class="current-price">Rp 225.000</span>
                <span class="original-price">Rp 300.000</span>
              </div>
              <p class="product-origin">Nusa Tenggara Timur</p>
            </div>
          </a>
        </div>
      </div>
    </section>
  </main>
  
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3><i class="fas fa-leaf"></i> Tentang KreasiLokal.id</h3>
          <p>KreasiLokal.id adalah platform e-commerce yang menghadirkan produk-produk lokal Indonesia berkualitas tinggi. Kami berkomitmen untuk melestarikan budaya dan tradisi Indonesia melalui produk kerajinan tangan, makanan khas, dan karya seni lokal.</p>
          <p>Bergabunglah dengan misi kami untuk mendukung UMKM Indonesia dan menjaga warisan budaya nusantara.</p>
        </div>
        
        <div class="footer-section">
          <h3><i class="fas fa-link"></i> Tautan Cepat</h3>
          <ul>
            <li><a href="tentang.html">Tentang Kami</a></li>
            <li><a href="kontak.html">Hubungi Kami</a></li>
            <li><a href="bantuan.html">Pusat Bantuan</a></li>
            <li><a href="syarat.html">Syarat & Ketentuan</a></li>
            <li><a href="privasi.html">Kebijakan Privasi</a></li>
            <li><a href="karir.html">Karir</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h3><i class="fas fa-shopping-bag"></i> Kategori Produk</h3>
          <ul>
            <li><a href="batik.html">Batik & Tekstil</a></li>
            <li><a href="kerajinan.html">Kerajinan Tangan</a></li>
            <li><a href="kuliner.html">Kuliner Nusantara</a></li>
            <li><a href="furniture.html">Furniture Kayu</a></li>
            <li><a href="aksesoris.html">Aksesoris Tradisional</a></li>
            <li><a href="seni.html">Seni & Lukisan</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h3><i class="fas fa-envelope"></i> Kontak & Dukungan</h3>
          <p><i class="fas fa-phone"></i> +62 812-3456-7890</p>
          <p><i class="fas fa-envelope"></i> info@kreasilokal.id</p>
          <p><i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia</p>
          
          <div class="social-links">
            <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <div class="container">
          <p>&copy; 2024 KreasiLokal.id. Seluruh hak cipta dilindungi undang-undang. | Dibuat dengan <i class="fas fa-heart" style="color: #ff6b6b;"></i> untuk Indonesia</p>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Countdown Timer
    var countdownElement = document.getElementById('countdown');
    
    if (countdownElement) {
      var timeInSeconds = (2 * 3600) + (28 * 60) + 45;

      setInterval(function() {
        if (timeInSeconds <= 0) {
          countdownElement.textContent = "00:00:00";
          return;
        }
        
        timeInSeconds--;
        
        var hours = Math.floor(timeInSeconds / 3600);
        var minutes = Math.floor((timeInSeconds % 3600) / 60);
        var seconds = timeInSeconds % 60;
        
        countdownElement.textContent = 
          String(hours).padStart(2, '0') + ':' + 
          String(minutes).padStart(2, '0') + ':' + 
          String(seconds).padStart(2, '0');
      }, 1000);
    }

    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth'
          });
        }
      });
    });
  </script>

</body>
</html>