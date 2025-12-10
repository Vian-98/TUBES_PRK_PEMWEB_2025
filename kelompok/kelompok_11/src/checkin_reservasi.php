<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    $_SESSION['error'] = "Request invalid.";
    header('Location: list.php');
    exit;
}

$id = (int)$_POST['id'];

// Ambil data reservasi + layanan
$stmt = $mysqli->prepare("
    SELECT r.*, 
           s.nama AS layanan_nama, 
           s.harga AS layanan_harga 
    FROM reservations r
    LEFT JOIN services s ON r.layanan_id = s.id
    WHERE r.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();
$stmt->close();

if (!$reservation) {
    $_SESSION['error'] = "Reservasi tidak ditemukan.";
    header('Location: list.php');
    exit;
}

$now = date('Y-m-d H:i:s');
$checked_in_by = null; // bisa diisi ID user login jika ada

$mysqli->begin_transaction();

try {

    /* ---------------------------------------------------
       1. INSERT KE reservation_checkins
    --------------------------------------------------- */
    $catatan = "Check-in via web";

    $stmt = $mysqli->prepare("
        INSERT INTO reservation_checkins
        (reservation_id, checked_in_at, checked_in_by, catatan, created_at)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        'iisss',
        $id,
        $now,
        $checked_in_by,
        $catatan,
        $now
    );

    $stmt->execute();
    $checkin_id = $stmt->insert_id;
    $stmt->close();



    /* ---------------------------------------------------
       2. INSERT KE transactions (DRAFT)
       Kolom sesuai tabel kamu (13 kolom)
    --------------------------------------------------- */

    $kodeTx = "TRX" . date('ymdHis') . rand(100,999);
    $pel_nama = $reservation['nama_pelanggan'];
    $pel_tel  = $reservation['telepon'];
    $total    = (float)$reservation['layanan_harga'];
    $diskon   = 0;
    $grand    = $total;
    $bayar    = 0;
    $kembali  = 0;
    $status   = "draft";

    $stmt = $mysqli->prepare("
        INSERT INTO transactions 
        (kode, reservation_id, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // FORMAT BENAR (13 tipe): sissdddddsiss
    $stmt->bind_param(
        'sissdddddsiss',
        $kodeTx,
        $id,
        $pel_nama,
        $pel_tel,
        $total,
        $diskon,
        $grand,
        $bayar,
        $kembali,
        $status,
        $checked_in_by,
        $now,
        $now
    );

    $stmt->execute();
    $txId = $stmt->insert_id;
    $stmt->close();



    /* ---------------------------------------------------
       3. INSERT KE transaction_items
    --------------------------------------------------- */
    if (!empty($reservation['layanan_id'])) {

        $service_id  = (int)$reservation['layanan_id'];
        $part_id     = null;
        $nama_item   = $reservation['layanan_nama'];
        $qty         = 1;
        $harga_unit  = (float)$reservation['layanan_harga'];
        $subtotal    = $harga_unit * $qty;
        $created_at  = $now;

        $stmt = $mysqli->prepare("
            INSERT INTO transaction_items
            (transaction_id, service_id, part_id, nama_item, qty, harga_unit, subtotal, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            'iiisddds',
            $txId,
            $service_id,
            $part_id,
            $nama_item,
            $qty,
            $harga_unit,
            $subtotal,
            $created_at
        );

        $stmt->execute();
        $stmt->close();
    }



    /* ---------------------------------------------------
       4. UPDATE reservation_checkins → set draft transaction
    --------------------------------------------------- */
    $stmt = $mysqli->prepare("
        UPDATE reservation_checkins 
        SET draft_transaction_id = ? 
        WHERE id = ?
    ");
    $stmt->bind_param('ii', $txId, $checkin_id);
    $stmt->execute();
    $stmt->close();



    /* ---------------------------------------------------
       5. UPDATE STATUS RESERVASI → in_progress
    --------------------------------------------------- */
    $stmt = $mysqli->prepare("
        UPDATE reservations 
        SET status='in_progress', updated_at=? 
        WHERE id=?
    ");
    $stmt->bind_param('si', $now, $id);
    $stmt->execute();
    $stmt->close();



    /* ---------------------------------------------------
       COMMIT SEMUA
    --------------------------------------------------- */
    $mysqli->commit();

    $_SESSION['success'] = "Check-in berhasil. Draft transaksi dibuat (ID: $txId).";
    header('Location: list.php');
    exit;

} catch (Exception $e) {

    $mysqli->rollback();
    $_SESSION['error'] = "Gagal check-in: " . $e->getMessage();
    header('Location: list.php');
    exit;
}
