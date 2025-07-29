<?php
require_once 'fungsi.php';
// verifikasi-data-diri.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'penjual') {
    header("Location: loginpenjual.php");
    exit();
}

if (isset($_POST["submit"])) {
    // Di sini, selain $_POST dan $_FILES, kita juga akan menerima foto wajah
    // dalam format base64 dari input tersembunyi 'face_image_data'
    if (proses_verifikasi_data_diri($_POST, $_FILES)) {
        header("Location: informasi-toko.php");
        exit();
    } else {
        $error = "Gagal menyimpan data. Pastikan semua kolom terisi dengan benar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langkah 1: Verifikasi Data Diri</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* === DESAIN BARU === */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            width: 100%;
            box-sizing: border-box;
        }
        header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 1.5rem;
        }
        header h1 {
            color: #34A853;
            margin: 0;
            font-size: 1.8rem;
        }
        .form-section {
            margin-bottom: 1.5rem;
        }
        .form-section p, label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
            border-color: #34A853;
            box-shadow: 0 0 0 3px rgba(52, 168, 83, 0.2);
            outline: none;
        }
        .radio-group label {
            display: inline-flex;
            align-items: center;
            margin-right: 1.5rem;
            font-weight: normal;
        }
        input[type="radio"],
        input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        .file-label {
            display: block;
            background-color: #f0f0f0;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s, background-color 0.3s;
        }
        .file-label:hover {
            border-color: #34A853;
            background-color: #e8f5e8;
        }
        input[type="file"] {
            display: none;
        }
        .camera-module {
            border: 1px solid #e0e0e0;
            padding: 1.5rem;
            margin-top: 10px;
            border-radius: 8px;
            background-color: #fafafa;
        }
        #video-container {
            position: relative;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 8px;
            overflow: hidden;
        }
        #video {
            width: 100%;
            height: auto;
            display: block;
        }
        .camera-controls {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        #captured-image {
            margin-top: 1rem;
            max-width: 200px;
            border: 2px solid #34A853;
            border-radius: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .btn {
            background-color: #34A853;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2a8747;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .submit-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            margin-top: 1.5rem;
        }
        .hidden {
            display: none;
        }
        .error-msg {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Langkah 1 dari 3: Verifikasi Data Diri</h1>
        </header>

        <main>
            <?php if (isset($error)): ?>
                <p class="error-msg"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <p><strong>Jenis Usaha *</strong></p>
                    <div class="radio-group">
                        <label><input type="radio" name="business_type" value="perorangan" checked> Perorangan</label>
                        <label><input type="radio" name="business_type" value="perusahaan"> Perusahaan (PT/CV)</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <label for="fullname"><strong>Nama (Sesuai KTP) *</strong></label>
                    <input type="text" id="fullname" name="fullname" placeholder="Masukkan nama lengkap Anda" required>
                </div>
                
                <div class="form-section">
                    <label for="nik"><strong>NIK *</strong></label>
                    <input type="number" id="nik" name="nik" placeholder="Masukkan 16 digit NIK Anda" required>
                </div>
                
                <div class="form-section">
                    <label for="ktp_image" class="file-label">
                        <i class="fas fa-id-card"></i> <strong>Upload Foto KTP *</strong>
                        <span style="display: block; font-weight: normal; font-size: 0.9em;">Klik di sini untuk memilih file</span>
                    </label>
                    <input type="file" id="ktp_image" name="ktp_image" accept="image/*" required>
                </div>
                
                <div class="form-section">
                    <p><strong>Verifikasi Wajah *</strong></p>
                    <div class="camera-module">
                        <p style="text-align:center; font-weight:normal;">Posisikan wajah Anda di dalam bingkai dan ambil foto.</p>
                        <div id="video-container">
                            <video id="video" autoplay playsinline></video>
                        </div>
                        <div class="camera-controls">
                            <button type="button" id="start-camera-btn" class="btn btn-secondary">Buka Kamera</button>
                            <button type="button" id="capture-btn" class="btn hidden">Ambil Foto</button>
                        </div>
                        <img id="captured-image" class="hidden" alt="Hasil Foto Wajah">
                        <input type="hidden" name="face_image_data" id="face_image_data">
                    </div>
                </div>
                
                <div class="form-section">
                    <label><input type="checkbox" name="terms" required> Saya menyetujui Syarat & Ketentuan yang berlaku.</label>
                </div>
                
                <button type="submit" name="submit" class="btn submit-btn">Lanjut ke Informasi Toko</button>
            </form>
        </main>
    </div>

    <script>
        const startCameraBtn = document.getElementById('start-camera-btn');
        const captureBtn = document.getElementById('capture-btn');
        const video = document.getElementById('video');
        const capturedImage = document.getElementById('captured-image');
        const faceImageDataInput = document.getElementById('face_image_data');
        let stream = null;

        startCameraBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = stream;
                
                startCameraBtn.classList.add('hidden');
                captureBtn.classList.remove('hidden');
                video.classList.remove('hidden');
                capturedImage.classList.add('hidden');

            } catch (err) {
                console.error("Error: ", err);
                alert("Tidak dapat mengakses kamera. Pastikan Anda memberikan izin.");
            }
        });

        captureBtn.addEventListener('click', () => {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageDataURL = canvas.toDataURL('image/jpeg');

            capturedImage.src = imageDataURL;
            capturedImage.classList.remove('hidden');
            
            faceImageDataInput.value = imageDataURL;

            stream.getTracks().forEach(track => track.stop());
            video.classList.add('hidden');
            captureBtn.classList.add('hidden');
            startCameraBtn.innerText = "Ulangi Foto";
            startCameraBtn.classList.remove('hidden');
        });
    </script>
</body>
</html>