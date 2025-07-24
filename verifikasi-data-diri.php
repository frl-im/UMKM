<?php
// verifikasi-data-diri.php
session_start();
require 'fungsi.php';

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
    <title>Langkah 1: Verifikasi Data Diri</title>
    <style>
        .camera-module { border: 1px solid #ccc; padding: 15px; margin-top: 10px; border-radius: 5px; }
        #video-container { position: relative; max-width: 400px; }
        #video { width: 100%; height: auto; border: 1px solid #ddd; }
        .camera-controls { margin-top: 10px; }
        #captured-image { margin-top: 10px; max-width: 200px; border: 1px solid #ddd; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <header>
        <h1>Langkah 1 dari 3: Verifikasi Data Diri</h1>
    </header>

    <main>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div>
                <p><strong>Jenis Usaha *</strong></p>
                <label><input type="radio" name="business_type" value="perorangan" checked> Perorangan</label>
                <label><input type="radio" name="business_type" value="perusahaan"> Perusahaan (PT/CV)</label>
            </div>
            <hr>
            <div>
                <label for="fullname"><strong>Nama (Sesuai KTP) *</strong></label><br>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            <hr>
            <div>
                <label for="nik"><strong>NIK *</strong></label><br>
                <input type="number" id="nik" name="nik" required>
            </div>
            <hr>
            <div>
                <label for="ktp_image"><strong>Foto KTP *</strong></label><br>
                <input type="file" id="ktp_image" name="ktp_image" accept="image/*" required>
            </div>
            <hr>
            
            <div>
                <p><strong>Verifikasi Wajah *</strong></p>
                <div class="camera-module">
                    <p>Posisikan wajah Anda di dalam bingkai dan ambil foto.</p>
                    <div id="video-container">
                        <video id="video" autoplay playsinline></video>
                    </div>
                    <div class="camera-controls">
                        <button type="button" id="start-camera-btn">Buka Kamera</button>
                        <button type="button" id="capture-btn" class="hidden">Ambil Foto</button>
                    </div>
                    <img id="captured-image" class="hidden" alt="Hasil Foto Wajah">
                    <input type="hidden" name="face_image_data" id="face_image_data">
                </div>
            </div>
            <hr>
            <div>
                <label><input type="checkbox" name="terms" required> Saya menyetujui Syarat & Ketentuan.</label>
            </div>
            <hr>
            <button type="submit" name="submit">Lanjut ke Informasi Toko</button>
        </form>
    </main>

    <script>
        const startCameraBtn = document.getElementById('start-camera-btn');
        const captureBtn = document.getElementById('capture-btn');
        const video = document.getElementById('video');
        const capturedImage = document.getElementById('captured-image');
        const faceImageDataInput = document.getElementById('face_image_data');
        let stream = null;

        startCameraBtn.addEventListener('click', async () => {
            try {
                // Minta izin akses kamera
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = stream;
                
                // Tampilkan dan sembunyikan tombol yang sesuai
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
            // Buat canvas untuk mengambil gambar dari video
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Konversi gambar di canvas ke format data URL (base64)
            const imageDataURL = canvas.toDataURL('image/jpeg');

            // Tampilkan gambar yang diambil
            capturedImage.src = imageDataURL;
            capturedImage.classList.remove('hidden');
            
            // Simpan data base64 ke input tersembunyi
            faceImageDataInput.value = imageDataURL;

            // Matikan kamera
            stream.getTracks().forEach(track => track.stop());
            video.classList.add('hidden');
            captureBtn.classList.add('hidden');
            startCameraBtn.innerText = "Ulangi Foto";
            startCameraBtn.classList.remove('hidden');
        });
    </script>
</body>
</html>