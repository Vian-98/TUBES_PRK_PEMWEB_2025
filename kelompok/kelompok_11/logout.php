<?php
session_start();

// Ambil user_id sebelum destroy session (untuk audit log)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Koneksi database
require_once 'config/database.php';

// Insert audit log
if ($user_id) {
    $action = "LOGOUT";
    $stmt_audit = $conn->prepare("INSERT INTO audit_logs (user_id, action, tabel, created_at) 
                                   VALUES (?, ?, 'users', NOW())");
    $stmt_audit->bind_param("is", $user_id, $action);
    $stmt_audit->execute();
}

// Destroy session
session_destroy();

// Redirect ke login
header('Location: login.php');
exit;
?>
