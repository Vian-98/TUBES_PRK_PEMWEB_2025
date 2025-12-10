<?php
// /reservasi/proses_reservasi.php
session_start();
require_once __DIR__ . '/db.php';



function sanitize($s){ return trim($s); }

$id = isset($_POST['id']) && $_POST['id']!=='' ? (int)$_POST['id'] : null;
$nama = sanitize($_POST['nama_pelanggan'] ?? '');
$telepon = sanitize($_POST['telepon'] ?? '');
$plat = sanitize($_POST['plat_kendaraan'] ?? '');
$jenis = sanitize($_POST['jenis_kendaraan'] ?? '');
$layanan_id = isset($_POST['layanan_id']) && $_POST['layanan_id']!=='' ? (int)$_POST['layanan_id'] : null;
$mekanik_id = isset($_POST['mekanik_id']) && $_POST['mekanik_id']!=='' ? (int)$_POST['mekanik_id'] : null;
$tanggal = sanitize($_POST['tanggal'] ?? '');
$status = in_array($_POST['status'] ?? 'booked', ['booked','in_progress','completed','canceled']) ? $_POST['status'] : 'booked';
$catatan = sanitize($_POST['catatan'] ?? '');

// Validasi
$errors = [];
if ($nama === '') $errors[] = "Nama pelanggan wajib diisi.";
if (empty($layanan_id)) $errors[] = "Pilih jenis layanan.";
if ($tanggal === '') $errors[] = "Tanggal & jam wajib diisi.";
// validasi datetime format: try parse
if ($tanggal !== '') {
    $dt = date_create($tanggal);
    if (!$dt) $errors[] = "Format tanggal tidak valid.";
    else $tanggal = $dt->format('Y-m-d H:i:s');
}

if ($errors) {
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'form_reservasi.php'));
    exit;
}

$now = date('Y-m-d H:i:s');
$created_by = null; // bisa diisi dari session user id jika ada

if ($id) {
    // Update
    $stmt = $mysqli->prepare("UPDATE reservations SET nama_pelanggan=?, telepon=?, plat_kendaraan=?, jenis_kendaraan=?, layanan_id=?, mekanik_id=?, tanggal=?, status=?, catatan=?, updated_at=? WHERE id=?");
    $stmt->bind_param('ssssiiisssi', $nama, $telepon, $plat, $jenis, $layanan_id, $mekanik_id, $tanggal, $status, $catatan, $now, $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Reservasi berhasil diupdate.";
    } else {
        $_SESSION['error'] = "Gagal update reservasi: " . $mysqli->error;
    }
    header('Location: edit_reservasi.php?id='.$id);
    exit;
} else {
    // Create: generate kode sederhana
    $kode = 'RSV' . date('ymdHis') . rand(100,999);
    $stmt = $mysqli->prepare("INSERT INTO reservations (kode, nama_pelanggan, telepon, plat_kendaraan, jenis_kendaraan, layanan_id, mekanik_id, tanggal, status, catatan, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssiisssis', $kode, $nama, $telepon, $plat, $jenis, $layanan_id, $mekanik_id, $tanggal, $status, $catatan, $created_by, $now);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Reservasi berhasil dibuat.";
        header('Location: list.php');
        exit;
    } else {
        $_SESSION['error'] = "Gagal membuat reservasi: " . $mysqli->error;
        header('Location: form_reservasi.php');
        exit;
    }
}
