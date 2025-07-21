<?php
session_start();
require_once 'config/database.php';

$action = $_POST['action'] ?? '';

// --- LOGIKA UNTUK REGISTRASI PEMBELI ---
if ($action == 'register_pembeli') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password_hash, role) VALUES (?, ?, ?, 'pembeli')");
    $stmt->execute([$fullname, $email, $password_hash]);
    header("Location: loginpembeli.php?status=reg_success");
    exit();
}

// --- LOGIKA UNTUK REGISTRASI PENJUAL ---
if ($action == 'register_penjual') {
    $owner_name = $_POST['owner-name'];
    $store_name = $_POST['store-name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = $_POST['phone'];

    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password_hash, role, store_name, phone) VALUES (?, ?, ?, 'penjual', ?, ?)");
    $stmt->execute([$owner_name, $email, $password, $store_name, $phone]);
    header("Location: loginpenjual.php?status=reg_success");
    exit();
}

// --- LOGIKA UNTUK LOGIN (PEMBELI & PENJUAL) ---
if ($action == 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['fullname'];
        
        if ($user['role'] == 'pembeli') {
            header("Location: profil-pembeli.php");
        } else {
            header("Location: dashboard-penjual.php");
        }
        exit();
    } else {
        $from = $_POST['from_page'];
        header("Location: $from?status=login_failed");
        exit();
    }
}
?>