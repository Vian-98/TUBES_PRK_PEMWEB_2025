<?php
// /reservasi/proses_reservasi.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

// Ambil data dari form
$id               = isset($_POST['id']) ? intval($_POST['id']) : null;
$nama_pelanggan   = $_POST['nama_pelanggan'] ?? '';
$telepon          = $_POST['telepon'] ?? '';
$plat_kendaraan   = $_POST['plat_kendaraan'] ?? '';
$jenis_kendaraan  = $_POST['jenis_kendaraan'] ?? '';
$layanan_id       = intval($_POST['layanan_id'] ?? 0);
$mekanik_id       = intval($_POST['mekanik_id'] ?? 0);
$tanggal          = $_POST['tanggal'] ?? '';
$catatan          = $_POST['catatan'] ?? '';
$status           = 'booked'; // status baru selalu "booked"

// Validasi sederhana
if (
    !$nama_pelanggan ||
    !$telepon ||
    !$plat_kendaraan ||
    !$layanan_id ||
    !$mekanik_id ||
    !$tanggal
) {
    $_SESSION['error'] = "Semua field wajib diisi!";
    header("Location: form_reservasi.php" . ($id ? "?id=$id" : ""));
    exit;
}

// Escape string untuk keamanan
$nama_pelanggan  = escape($nama_pelanggan);
$telepon         = escape($telepon);
$plat_kendaraan  = escape($plat_kendaraan);
$jenis_kendaraan = escape($jenis_kendaraan);
$catatan         = escape($catatan);
$tanggal         = escape($tanggal);
$now             = date('Y-m-d H:i:s');

if ($id) {
    // UPDATE RESERVASI
    $sql = "
        UPDATE reservations SET 
            nama_pelanggan  = '$nama_pelanggan',
            telepon         = '$telepon',
            plat_kendaraan  = '$plat_kendaraan',
            jenis_kendaraan = '$jenis_kendaraan',
            layanan_id      = $layanan_id,
            mekanik_id      = $mekanik_id,
            tanggal         = '$tanggal',
            catatan         = '$catatan',
            updated_at      = '$now'
        WHERE id = $id
    ";

    execute($sql);
    $_SESSION['success'] = "Reservasi berhasil diupdate!";
} else {
    // GENERATE KODE RESERVASI BARU
    $kode = 'RSV' . date('YmdHis') . rand(10, 99);
    $kode = escape($kode);

    // INSERT RESERVASI BARU
    $sql = "
        INSERT INTO reservations 
        (kode, nama_pelanggan, telepon, plat_kendaraan, jenis_kendaraan, layanan_id, mekanik_id, tanggal, status, catatan, created_by, created_at, updated_at)
        VALUES 
        ('$kode', '$nama_pelanggan', '$telepon', '$plat_kendaraan', '$jenis_kendaraan', 
         $layanan_id, $mekanik_id, '$tanggal', 'booked', '$catatan', {$_SESSION['user_id']}, '$now', '$now')
    ";

    execute($sql);
    $_SESSION['success'] = "Reservasi berhasil dibuat!";
}

// Redirect kembali ke list
header("Location: list.php");
exit;
?>
