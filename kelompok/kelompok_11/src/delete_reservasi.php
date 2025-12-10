<?php
// /reservasi/delete_reservasi.php
session_start();
require_once __DIR__ . '/db.php';



if (empty($_GET['id'])) {
    $_SESSION['error'] = "ID reservasi tidak diberikan.";
    header('Location: list.php'); exit;
}

$id = (int)$_GET['id'];

// Hapus berantai: reservation_checkins, transactions (jika draft dibuat), transaction_items, reservation
// Ambil transaksi terkait
$stmt = $mysqli->prepare("SELECT id FROM transactions WHERE reservation_id = ?");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$txIds = [];
while ($row = $res->fetch_assoc()) $txIds[] = $row['id'];
$stmt->close();

if (count($txIds)) {
    $in = implode(',', array_map('intval',$txIds));
    $mysqli->query("DELETE FROM transaction_items WHERE transaction_id IN ($in)");
    $mysqli->query("DELETE FROM transaction_payments WHERE transaction_id IN ($in)");
    $mysqli->query("DELETE FROM transactions WHERE id IN ($in)");
}

// Hapus checkins
$stmt = $mysqli->prepare("DELETE FROM reservation_checkins WHERE reservation_id = ?");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->close();

// Hapus reservasi
$stmt = $mysqli->prepare("DELETE FROM reservations WHERE id = ?");
$stmt->bind_param('i',$id);
if ($stmt->execute()) {
    $_SESSION['success'] = "Reservasi berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus reservasi: " . $mysqli->error;
}
$stmt->close();
header('Location: list.php');
exit;
