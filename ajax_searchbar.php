<?php
require_once 'fungsi.php'; // Memuat fungsi dan koneksi database

// Set header ke JSON karena kita akan mengembalikan data dalam format ini
header('Content-Type: application/json');

// Ambil kata kunci dari parameter URL (?q=...)
$keyword = $_GET['q'] ?? '';

// Jangan lakukan pencarian jika kata kunci terlalu pendek
if (strlen($keyword) < 2) {
    echo json_encode([]); // Kembalikan array kosong
    exit;
}

global $koneksi;

// Siapkan query untuk mencari produk berdasarkan nama atau kategori
// Menggunakan LIKE %...% untuk mencari kata kunci di mana saja dalam teks
$searchTerm = "%" . $keyword . "%";
$stmt = mysqli_prepare($koneksi, 
    "SELECT id, name, price, image_url, stock 
     FROM products 
     WHERE (name LIKE ? OR category LIKE ?) 
     AND status = 'active' 
     LIMIT 10"
);

mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Format harga sebelum dikirim sebagai JSON
    $row['formatted_price'] = format_price($row['price']);
    $products[] = $row;
}

// Kembalikan hasil dalam bentuk JSON
echo json_encode($products);