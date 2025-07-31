<?php
require_once 'fungsi.php';

// Memulai session dan memastikan user sudah login untuk bisa bermain
// Jika Anda ingin semua orang bisa melihat tapi hanya user login yang bisa main,
// kita akan atur logikanya di JavaScript.
start_secure_session();
$is_logged_in = is_logged_in();
$user_name = $_SESSION['user_name'] ?? 'Tamu';

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Games & Hadiah - KreasiLokal.id</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    /* Reset CSS & Font Dasar (Sama seperti index.php) */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
      line-height: 1.6;
    }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

    /* Header & Footer Styles (Copy dari index.php untuk konsistensi) */
    .navbar { background-color: #ffffff; padding: 1rem 0; border-bottom: 1px solid #e0e0e0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .navbar .container { display: flex; align-items: center; justify-content: space-between; }
    .navbar .logo a { font-size: 1.8rem; font-weight: bold; color: #2e8b57; text-decoration: none; display: flex; align-items: center; gap: 8px; }
    .main-search-bar { flex-grow: 1; margin: 0 2rem; display: flex; max-width: 500px; }
    .main-search-bar input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #2e8b57; border-right: none; border-radius: 5px 0 0 5px; font-size: 1rem; outline: none; }
    .main-search-bar button { padding: 0 1.2rem; border: none; background-color: #2e8b57; color: white; border-radius: 0 5px 5px 0; cursor: pointer; font-size: 1.2rem; }
    .nav-icons a { font-size: 1.5rem; color: #555; text-decoration: none; margin-left: 1.5rem; }
    .user-dropdown { position: relative; display: inline-block; }
    .user-dropdown-content { display: none; position: absolute; right: 0; background-color: white; min-width: 200px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); border-radius: 5px; z-index: 1; padding: 10px 0; }
    .user-dropdown:hover .user-dropdown-content { display: block; }
    .user-dropdown-content a { color: #333; padding: 10px 16px; text-decoration: none; display: block; font-size: 0.9rem; }
    .user-dropdown-content a:hover { background-color: #f1f1f1; }
    .user-info { padding: 10px 16px; border-bottom: 1px solid #eee; color: #2e8b57; font-weight: bold; font-size: 0.9rem; }
    .footer { background: linear-gradient(135deg, #2e8b57, #1e6b47); color: white; padding: 40px 0 0 0; margin-top: 50px; }
    .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 30px; }
    .footer-section h3 { margin-bottom: 15px; }
    .footer-section ul { list-style: none; }
    .footer-section ul li a { color: #e8f5e8; text-decoration: none; }
    .footer-bottom { border-top: 1px solid #4caf50; padding: 20px 0; text-align: center; }
    
    /* STYLING KHUSUS HALAMAN GAMES */
    .game-page-header {
      text-align: center;
      margin: 30px 0;
      color: #2e8b57;
    }
    .game-page-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }
    .game-page-header p {
      font-size: 1.1rem;
      color: #555;
    }
    
    .game-container {
      position: relative;
      width: 450px;
      height: 450px;
      margin: 30px auto;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .wheel-pointer {
      position: absolute;
      width: 0;
      height: 0;
      border-left: 25px solid transparent;
      border-right: 25px solid transparent;
      border-top: 50px solid #d9534f; /* Merah sebagai penunjuk */
      top: -20px;
      z-index: 10;
      transform: rotate(180deg);
    }

    #spin-wheel-canvas {
      transition: transform 6s ease-out; /* Animasi putaran */
    }

    #spin-btn {
      position: absolute;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background-color: #ffffff;
      color: #2e8b57;
      border: 8px solid #2e8b57;
      font-size: 1.5rem;
      font-weight: bold;
      cursor: pointer;
      z-index: 5;
      transition: background-color 0.3s, transform 0.2s;
    }
    
    #spin-btn:hover {
      background-color: #f0fff0;
    }

    #spin-btn:active {
        transform: scale(0.95);
    }
    
    #spin-btn:disabled {
      background-color: #e9ecef;
      color: #6c757d;
      border-color: #ced4da;
      cursor: not-allowed;
    }
    
    .result-container {
      text-align: center;
      margin-top: 20px;
      padding: 20px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      min-height: 50px;
    }
    
    #result-text {
      font-size: 1.5rem;
      font-weight: bold;
      color: #ff8c00;
    }
    
    /* Responsive */
    @media(max-width: 500px) {
        .game-container {
            width: 300px;
            height: 300px;
        }
        #spin-btn {
            width: 70px;
            height: 70px;
            font-size: 1.2rem;
            border-width: 6px;
        }
        .wheel-pointer {
            border-left-width: 15px;
            border-right-width: 15px;
            border-top-width: 30px;
            top: -10px;
        }
    }

  </style>
</head>
<body>

  <header class="navbar">
    <div class="container">
      <div class="logo">
        <a href="index.php"><i class="fas fa-leaf"></i> KreasiLokal.id</a>
      </div>
      <form action="pencarian.php" method="GET" class="main-search-bar">
          <input type="text" name="q" placeholder="Cari di KreasiLokal.id...">
          <button type="submit"><i class="fas fa-search"></i></button>
      </form>
      <div class="nav-icons">
        <?php if ($is_logged_in): ?>
          <div class="user-dropdown">
            <a href="#" title="Profil User"><i class="fas fa-user-circle"></i></a>
            <div class="user-dropdown-content">
              <div class="user-info">Hai, <?php echo htmlspecialchars($user_name); ?>!</div>
                <a href="profilpembeli.php"><i class="fas fa-user"></i> Profil Saya</a>
                <a href="pesanan.php"><i class="fas fa-shopping-bag"></i> Pesanan Saya</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.html" title="Login / Daftar"><i class="fas fa-user-circle"></i></a>
        <?php endif; ?>
        <a href="keranjang.php" title="Keranjang Belanja"><i class="fas fa-shopping-cart"></i></a>
      </div>
    </div>
  </header>

  <main>
    <div class="container">
      <div class="game-page-header">
        <h1><i class="fas fa-dice"></i> Roda Keberuntungan</h1>
        <p>Putar Roda dan menangkan hadiah menarik! Kesempatan 1x setiap hari.</p>
      </div>

      <div class="game-container">
        <div class="wheel-pointer"></div>
        <canvas id="spin-wheel-canvas" width="450" height="450"></canvas>
        <button id="spin-btn">PUTAR</button>
      </div>

      <div class="result-container">
        <p id="result-text">Semoga Beruntung!</p>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-content">
         </div>
      <div class="footer-bottom">
        <p>&copy; 2025 KreasiLokal.id. Seluruh hak cipta dilindungi.</p>
      </div>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const canvas = document.getElementById('spin-wheel-canvas');
      const spinBtn = document.getElementById('spin-btn');
      const resultText = document.getElementById('result-text');
      const ctx = canvas.getContext('2d');

      // Daftar hadiah di roda
      const prizes = [
        { text: 'ZONK!', color: '#FADBD8' },
        { text: 'Voucher 10%', color: '#D5F5E3' },
        { text: 'Coba Lagi', color: '#FCF3CF' },
        { text: 'Gratis Ongkir', color: '#D6EAF8' },
        { text: 'Diskon 5rb', color: '#FADBD8' },
        { text: 'Voucher 20%', color: '#D5F5E3' },
        { text: 'ZONK!', color: '#FCF3CF' },
        { text: 'Hadiah Spesial', color: '#D6EAF8' },
      ];

      const numPrizes = prizes.length;
      const sliceAngle = (2 * Math.PI) / numPrizes;
      let currentAngle = 0;
      let isSpinning = false;
      const isLoggedIn = <?php echo json_encode($is_logged_in); ?>;

      // Fungsi untuk menggambar roda
      function drawWheel() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        prizes.forEach((prize, i) => {
          const angle = i * sliceAngle;
          ctx.beginPath();
          ctx.fillStyle = prize.color;
          ctx.moveTo(canvas.width / 2, canvas.height / 2);
          ctx.arc(canvas.width / 2, canvas.height / 2, canvas.width / 2 - 10, angle, angle + sliceAngle);
          ctx.closePath();
          ctx.fill();

          // Gambar teks hadiah
          ctx.save();
          ctx.fillStyle = '#333';
          ctx.font = 'bold 16px Arial';
          ctx.translate(canvas.width / 2, canvas.height / 2);
          ctx.rotate(angle + sliceAngle / 2);
          ctx.textAlign = 'right';
          ctx.fillText(prize.text, canvas.width / 2 - 20, 10);
          ctx.restore();
        });
      }

      // Fungsi untuk cek kesempatan putar harian
      function checkSpinChance() {
        if (!isLoggedIn) {
            spinBtn.disabled = true;
            resultText.textContent = 'Silakan login untuk bermain!';
            return;
        }

        const lastSpinDate = localStorage.getItem('lastSpinDate');
        const today = new Date().toDateString();

        if (lastSpinDate === today) {
          spinBtn.disabled = true;
          resultText.textContent = 'Kesempatan Anda hari ini sudah habis. Coba lagi besok!';
        } else {
          spinBtn.disabled = false;
          resultText.textContent = 'Anda punya 1 kesempatan. Putar sekarang!';
        }
      }

      // Fungsi putar
      function spin() {
        if (isSpinning || !isLoggedIn) return;

        isSpinning = true;
        spinBtn.disabled = true;
        resultText.textContent = 'Berputar...';

        // Simpan tanggal putaran ke localStorage
        // Untuk aplikasi production, ini harus divalidasi di server.
        localStorage.setItem('lastSpinDate', new Date().toDateString());

        const totalRotations = 5; // Jumlah putaran
        const randomExtraAngle = Math.floor(Math.random() * 360);
        const targetAngle = (totalRotations * 360) + randomExtraAngle;

        currentAngle += targetAngle;
        canvas.style.transform = `rotate(${currentAngle}deg)`;
      }

      // Event listener ketika animasi selesai
      canvas.addEventListener('transitionend', () => {
        isSpinning = false;
        
        // Kalkulasi hasil kemenangan
        const finalAngle = currentAngle % 360;
        const pointerAngle = 360 - finalAngle; // Posisi penunjuk relatif terhadap roda
        const winningIndex = Math.floor(pointerAngle / (360 / numPrizes));
        const winningPrize = prizes[winningIndex];

        resultText.textContent = `Selamat! Anda memenangkan: ${winningPrize.text}`;

        // Di sini Anda bisa menambahkan logika untuk memberikan hadiah ke user
        // Contoh: panggil API untuk menambahkan voucher ke akun user
        if (winningPrize.text.includes('Voucher') || winningPrize.text.includes('Diskon')) {
            console.log(`User memenangkan: ${winningPrize.text}. Tambahkan voucher ke database.`);
        }
      });
      
      spinBtn.addEventListener('click', spin);

      // Inisialisasi
      drawWheel();
      checkSpinChance();

    });
  </script>

</body>
</html>