<?php
require_once 'fungsi.php';

// Ambil ID toko dari URL
$toko_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($toko_id <= 0) {
    die("Toko tidak valid.");
}

// Ambil detail toko dari database
$toko = get_user_by_id($toko_id);

if (!$toko || $toko['role'] !== 'penjual' || empty($toko['latitude']) || empty($toko['longitude'])) {
    die("Lokasi toko tidak ditemukan atau tidak valid.");
}

$nama_toko = safe_output($toko['store_name']);
$latitude = $toko['latitude'];
$longitude = $toko['longitude'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi Toko: <?php echo $nama_toko; ?></title>
    <style>
        /* Membuat peta memenuhi seluruh layar */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }
        #map {
            height: 100%;
            width: 100%;
        }
        /* Styling untuk info box di atas peta */
        .info-box {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="info-box">
        <h3><?php echo $nama_toko; ?></h3>
        <p>Membuka lokasi di Google Maps...</p>
    </div>

    <div id="map"></div>

    <script>
        // Fungsi ini akan dipanggil setelah Google Maps API selesai dimuat
        function initMap() {
            // Tentukan lokasi toko
            const lokasiToko = { lat: <?php echo $latitude; ?>, lng: <?php echo $longitude; ?> };
            
            // Buat peta yang berpusat di lokasi toko
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: lokasiToko,
            });

            // Buat penanda (marker) di lokasi toko
            const marker = new google.maps.Marker({
                position: lokasiToko,
                map: map,
                title: "<?php echo $nama_toko; ?>"
            });

            // Otomatis buka Google Maps di tab baru
            window.open(`https://www.google.com/maps/search/?api=1&query=<?php echo $latitude; ?>,<?php echo $longitude; ?>`, '_blank');
        }
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>
    
    </body>
</html>