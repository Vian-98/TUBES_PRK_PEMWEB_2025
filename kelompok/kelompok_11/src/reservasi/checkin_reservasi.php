<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? null;

    if ($id <= 0) {
        $_SESSION['error'] = "ID reservasi tidak valid!";
        header("Location: list.php");
        exit;
    }

    // Ambil data reservasi
    $reservasi = fetchOne("SELECT * FROM reservations WHERE id = $id");
    if (!$reservasi) {
        $_SESSION['error'] = "Reservasi tidak ditemukan!";
        header("Location: list.php");
        exit;
    }

    $now = date('Y-m-d H:i:s');
    $nama_pelanggan  = escape($reservasi['nama_pelanggan']);
    $telepon         = escape($reservasi['telepon'] ?? '');
    $layanan_id      = intval($reservasi['layanan_id'] ?? 0);

    // 1. Update status reservasi
    execute("UPDATE reservations SET status = 'in_progress', updated_at = '$now' WHERE id = $id");

    // 2. Buat transaksi draft
    $kode = 'TRX' . date('YmdHis') . rand(10,99);
    $sql_trx = "
        INSERT INTO transactions (kode, reservation_id, pelanggan_nama, pelanggan_telepon, status, created_at)
        VALUES ('$kode', $id, '$nama_pelanggan', '$telepon', 'draft', '$now')
    ";
    $trx_result = execute($sql_trx);
    $draft_id = $trx_result['insert_id'] ?? null;

    if ($draft_id && $layanan_id > 0) {
        // Ambil layanan
        $service = fetchOne("SELECT nama, harga FROM services WHERE id = $layanan_id");
        if ($service) {
            $nama_item = escape($service['nama']);
            $harga     = floatval($service['harga']);
            $subtotal  = $harga;

            // 3. Masukkan item ke transaction_items (KERANJANG AWAL)
            execute("
                INSERT INTO transaction_items 
                    (transaction_id, service_id, nama_item, qty, harga_unit, subtotal, created_at)
                VALUES 
                    ($draft_id, $layanan_id, '$nama_item', 1, $harga, $subtotal, '$now')
            ");
        }
    }

    // 4. Catat ke reservation_checkins
    execute("
        INSERT INTO reservation_checkins 
            (reservation_id, checked_in_at, checked_in_by, draft_transaction_id)
        VALUES 
            ($id, '$now', $user_id, " . ($draft_id ?: 'NULL') . ")
    ");

    $_SESSION['success'] = "Reservasi berhasil check-in! Draft transaksi dibuat dan siap di POS.";
    header("Location: list.php");
    exit;

} else {
    $_SESSION['error'] = "Akses tidak valid!";
    header("Location: list.php");
    exit;
}
?>
