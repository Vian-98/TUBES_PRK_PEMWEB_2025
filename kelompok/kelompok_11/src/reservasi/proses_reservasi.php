<?php
// /reservasi/proses_reservasi.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

// Ambil data dari form
$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$nama_pelanggan = $_POST['nama_pelanggan'] ?? '';
$telepon = $_POST['telepon'] ?? '';
$plat_kendaraan = $_POST['plat_kendaraan'] ?? '';
$layanan_id = intval($_POST['layanan_id'] ?? 0);
$mekanik_id = intval($_POST['mekanik_id'] ?? 0);
$tanggal = $_POST['tanggal'] ?? '';
$status = $_POST['status'] ?? 'booked';

// Validasi sederhana
if (!$nama_pelanggan || !$telepon || !$plat_kendaraan || !$layanan_id || !$mekanik_id || !$tanggal) {
    $_SESSION['error'] = "Semua field wajib diisi!";
    header("Location: form_reservasi.php" . ($id ? "?id=$id" : ""));
    exit;
}

// Escape string untuk keamanan
$nama_pelanggan = escape($nama_pelanggan);
$telepon = escape($telepon);
$plat_kendaraan = escape($plat_kendaraan);
$tanggal = escape($tanggal);
$status = escape($status);

if ($id) {
    // Update reservasi
    $sql = "UPDATE reservations SET 
        nama_pelanggan = '$nama_pelanggan',
        telepon = '$telepon',
        plat_kendaraan = '$plat_kendaraan',
        layanan_id = $layanan_id,
        mekanik_id = $mekanik_id,
        tanggal = '$tanggal',
        status = '$status'
        WHERE id = $id";
    execute($sql);
    $_SESSION['success'] = "Reservasi berhasil diupdate!";
} else {
    // Generate kode reservasi baru
    $kode = 'RSV' . date('YmdHis') . rand(10,99);
    $kode = escape($kode);

    // Insert reservasi baru
    $sql = "INSERT INTO reservations (kode, nama_pelanggan, telepon, plat_kendaraan, layanan_id, mekanik_id, tanggal, status)
            VALUES ('$kode', '$nama_pelanggan', '$telepon', '$plat_kendaraan', $layanan_id, $mekanik_id, '$tanggal', '$status')";
    execute($sql);
    $_SESSION['success'] = "Reservasi berhasil dibuat!";
}

header("Location: list.php");
exit;
?>
