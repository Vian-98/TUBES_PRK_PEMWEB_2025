<?php
session_start();

require_once __DIR__ . '/cek_login.php';

$nama = $_SESSION['nama'] ?? 'User';

destroy_user_session();

session_start();
set_flash("Logout berhasil! Sampai jumpa lagi, $nama", "success");

header("Location: login.php");
exit();
?>