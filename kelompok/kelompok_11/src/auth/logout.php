<?php
session_start();

require_once __DIR__ . '/cek_login.php';

$username = $_SESSION['username'] ?? 'User';

destroy_user_session();

session_start();
set_flash("Logout berhasil! Sampai jumpa lagi, $username", "success");

header("Location: login.php");
exit();
?>