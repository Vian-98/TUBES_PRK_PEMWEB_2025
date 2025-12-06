<?php
/**
 * KONFIGURASI DATABASE
 * Bengkel UMKM - POS System
 */

// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pos_bengkel');
define('DB_PORT', 3306);

// Buat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting untuk development (nonaktifkan di production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
