<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);

    // Update status reservasi jadi in_progress (sesuai ENUM)
    $sql = "UPDATE reservations SET status = 'in_progress' WHERE id = $id";
    execute($sql);

    // Buat draft transaksi baru (tanpa field reservasi_id)
    $reservasi = fetchOne("SELECT * FROM reservations WHERE id = $id");
    if ($reservasi) {
        $kode_transaksi = 'TRX' . date('YmdHis') . rand(10,99);
        $pelanggan_nama = escape($reservasi['nama_pelanggan']);
        $status_trx = 'draft';
        $sql_trx = "INSERT INTO transactions (kode, pelanggan_nama, status)
                    VALUES ('$kode_transaksi', '$pelanggan_nama', '$status_trx')";
        execute($sql_trx);
    }

    $_SESSION['success'] = "Reservasi berhasil check-in dan draft transaksi dibuat!";
    header("Location: list.php");
    exit;
} else {
    $_SESSION['error'] = "Akses tidak valid!";
    header("Location: list.php");
    exit;
}
?>
