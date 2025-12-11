<?php
// /reservasi/delete_reservasi.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

$conn = getConnection();
if (!$conn) {
    die('Koneksi database tidak tersedia.');
}

if (empty($_GET['id'])) {
    $_SESSION['error'] = "ID reservasi tidak diberikan.";
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];

// Ambil transaksi terkait
$stmt = $conn->prepare("SELECT id FROM transactions WHERE reservation_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$txIds = [];
while ($row = $res->fetch_assoc()) {
    $txIds[] = $row['id'];
}
$stmt->close();

if (count($txIds)) {
    $in = implode(',', array_map('intval', $txIds));
    $conn->query("DELETE FROM transaction_items WHERE transaction_id IN ($in)");
    $conn->query("DELETE FROM transaction_payments WHERE transaction_id IN ($in)");
    $conn->query("DELETE FROM transactions WHERE id IN ($in)");
}

// Hapus checkins
$stmt = $conn->prepare("DELETE FROM reservation_checkins WHERE reservation_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

// Hapus reservasi
$stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    $_SESSION['success'] = "Reservasi berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus reservasi: " . $conn->error;
}
$stmt->close();

header('Location: list.php');
exit;
