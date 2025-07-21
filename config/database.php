<?php
// config/database.php

$host = '127.0.0.1';
$db   = 'kreasidb'; // Pastikan nama ini sama dengan database yang Anda buat
$user = 'root';
$pass = ''; // Kosongkan jika tidak ada password di XAMPP Anda

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
     die("Koneksi ke database gagal: " . $e->getMessage());
}
?>
