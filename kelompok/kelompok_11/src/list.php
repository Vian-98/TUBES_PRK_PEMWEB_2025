<?php
// /reservasi/list.php
session_start();
require_once __DIR__ . '/db.php';



if (!empty($_SESSION['error'])) { $err = $_SESSION['error']; unset($_SESSION['error']); }
if (!empty($_SESSION['success'])) { $ok = $_SESSION['success']; unset($_SESSION['success']); }

// Ambil data reservasi
$q = "SELECT r.*, s.nama AS layanan_nama, u.nama AS mekanik_nama
      FROM reservations r
      LEFT JOIN services s ON r.layanan_id = s.id
      LEFT JOIN users u ON r.mekanik_id = u.id
      ORDER BY r.tanggal DESC";
$res = $mysqli->query($q);
$rows = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Daftar Reservasi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <h2>Reservasi</h2>
  <?php if(!empty($err)): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>
  <?php if(!empty($ok)): ?><div class="alert alert-success"><?= $ok ?></div><?php endif; ?>

  <a href="form_reservasi.php" class="btn btn-primary mb-3">Buat Reservasi Baru</a>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>Kode</th>
        <th>Nama</th>
        <th>Telepon</th>
        <th>Plat</th>
        <th>Layanan</th>
        <th>Mekanik</th>
        <th>Tanggal</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $i=>$r): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($r['kode']) ?></td>
          <td><?= htmlspecialchars($r['nama_pelanggan']) ?></td>
          <td><?= htmlspecialchars($r['telepon']) ?></td>
          <td><?= htmlspecialchars($r['plat_kendaraan']) ?></td>
          <td><?= htmlspecialchars($r['layanan_nama']) ?></td>
          <td><?= htmlspecialchars($r['mekanik_nama']) ?></td>
          <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($r['tanggal']))) ?></td>
          <td><?= htmlspecialchars($r['status']) ?></td>
          <td>
            <a href="edit_reservasi.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="delete_reservasi.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus reservasi ini?')">Delete</a>
            <?php if ($r['status'] === 'booked'): ?>
              <form method="post" action="checkin_reservasi.php" style="display:inline">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-sm btn-success" onclick="return confirm('Check-in dan buat draft transaksi?')">Check-in</button>
              </form>
            <?php else: ?>
              <small>-</small>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (count($rows)===0): ?>
        <tr><td colspan="10" class="text-center">Belum ada reservasi.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
