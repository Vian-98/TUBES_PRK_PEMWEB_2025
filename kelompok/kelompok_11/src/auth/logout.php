<?php
session_start();

$base_path = $_SERVER['DOCUMENT_ROOT'] . '/pos_ukm';
require_once $base_path . '/auth/cek_login.php';

$username = $_SESSION['username'] ?? 'User';

destroy_user_session();

session_start();
set_flash("Logout berhasil! Sampai jumpa lagi, $username", "success");

header("Location: /pos_ukm/auth/login.php");
exit();
?>