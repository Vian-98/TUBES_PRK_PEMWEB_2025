<?php
session_start();

// Koneksi database
require_once 'config/database.php';

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Ambil data dari form
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Validasi input kosong
if (empty($email) || empty($password)) {
    header('Location: login.php?error=empty');
    exit;
}

// Query user berdasarkan email
$stmt = $conn->prepare("SELECT u.*, r.nama as role_nama 
                        FROM users u 
                        LEFT JOIN roles r ON u.role_id = r.id 
                        WHERE u.email = ? AND u.aktif = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah user ditemukan
if ($result->num_rows === 0) {
    header('Location: login.php?error=invalid');
    exit;
}

$user = $result->fetch_assoc();

// Verifikasi password
if (!password_verify($password, $user['password'])) {
    header('Location: login.php?error=invalid');
    exit;
}

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['email'] = $user['email'];
$_SESSION['role_id'] = $user['role_id'];
$_SESSION['role_nama'] = $user['role_nama'];
$_SESSION['login_time'] = time();

// Insert audit log
$action = "LOGIN";
$after_data = json_encode(['user_id' => $user['id'], 'email' => $email]);
$stmt_audit = $conn->prepare("INSERT INTO audit_logs (user_id, action, tabel, after_data, created_at) 
                               VALUES (?, ?, 'users', ?, NOW())");
$stmt_audit->bind_param("iss", $user['id'], $action, $after_data);
$stmt_audit->execute();

// Redirect berdasarkan role
if ($user['role_id'] == 2) {
    // Kasir -> langsung ke POS
    header('Location: pos/pos.php');
} else {
    // Lainnya -> ke dashboard (nanti dibuat anggota 4)
    header('Location: pos/pos.php');
}

exit;
?>
