<?php
// /reservasi/form_reservasi.php
session_start();
require_once __DIR__ . '/db.php';



$editing = false;
$reservation = [
    'id' => '',
    'kode' => '',
    'nama_pelanggan' => '',
    'telepon' => '',
    'plat_kendaraan' => '',
    'jenis_kendaraan' => '',
    'layanan_id' => '',
    'mekanik_id' => '',
    'tanggal' => '',
    'status' => 'booked',
    'catatan' => ''
];

$services = [];
$mechanics = [];

// Ambil layanan & mekanik
$res = $mysqli->query("SELECT id, nama, harga, durasi_menit FROM services ORDER BY nama");
while ($r = $res->fetch_assoc()) $services[] = $r;

$res = $mysqli->query("SELECT id, nama FROM users WHERE role_id IS NOT NULL ORDER BY nama"); // asumsi mekanik tersaring via role
while ($r = $res->fetch_assoc()) $mechanics[] = $r;

if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $rs = $stmt->get_result();
    if ($row = $rs->fetch_assoc()) {
        $editing = true;
        $reservation = $row;
        // format datetime for input[type=datetime-local]
        $reservation['tanggal'] = date('Y-m-d\TH:i', strtotime($reservation['tanggal']));
    } else {
        $_SESSION['error'] = "Reservasi tidak ditemukan.";
        header('Location: list.php'); exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= $editing ? 'Edit' : 'Buat' ?> Reservasi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <h2><?= $editing ? 'Edit' : 'Buat' ?> Reservasi</h2>

  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <form method="post" action="proses_reservasi.php" novalidate>
    <input type="hidden" name="id" value="<?= htmlspecialchars($reservation['id']) ?>">
    <div class="mb-3">
      <label class="form-label">Nama Pelanggan *</label>
      <input name="nama_pelanggan" class="form-control" required value="<?= htmlspecialchars($reservation['nama_pelanggan']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">No. HP</label>
      <input name="telepon" class="form-control" value="<?= htmlspecialchars($reservation['telepon']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Plat / No Kendaraan</label>
      <input name="plat_kendaraan" class="form-control" value="<?= htmlspecialchars($reservation['plat_kendaraan']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Jenis Kendaraan</label>
      <input name="jenis_kendaraan" class="form-control" value="<?= htmlspecialchars($reservation['jenis_kendaraan']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Jenis Layanan *</label>
      <select name="layanan_id" class="form-select" required>
        <option value="">-- pilih layanan --</option>
        <?php foreach($services as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $s['id']==$reservation['layanan_id'] ? 'selected':'' ?>>
            <?= htmlspecialchars($s['nama']) ?> (<?= number_format($s['harga'],0,',','.') ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Mekanik</label>
      <select name="mekanik_id" class="form-select">
        <option value="">-- pilih mekanik --</option>
        <?php foreach($mechanics as $m): ?>
          <option value="<?= $m['id'] ?>" <?= $m['id']==$reservation['mekanik_id'] ? 'selected':'' ?>>
            <?= htmlspecialchars($m['nama']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Tanggal & Jam *</label>
      <input type="datetime-local" name="tanggal" class="form-control" required value="<?= htmlspecialchars($reservation['tanggal']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <?php
        $statuses = ['booked'=>'booked','in_progress'=>'in_progress','completed'=>'completed','canceled'=>'canceled'];
        foreach($statuses as $k=>$v): ?>
          <option value="<?= $k?>" <?= $k==$reservation['status'] ? 'selected':'' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Catatan</label>
      <textarea name="catatan" class="form-control"><?= htmlspecialchars($reservation['catatan']) ?></textarea>
    </div>

    <button class="btn btn-primary"><?= $editing ? 'Simpan Perubahan' : 'Buat Reservasi' ?></button>
    <a href="list.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>
</body>
</html>
