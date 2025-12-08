<?php
// /reservasi/api_reservasi.php
require_once __DIR__ . '/db.php';



header('Content-Type: application/json; charset=utf-8');

// optional filters: status, date_from, date_to, limit
$status = isset($_GET['status']) ? $_GET['status'] : null;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

$params = [];
$where = [];

if ($status && in_array($status, ['booked','in_progress','completed','canceled'])) {
    $where[] = "r.status = ?";
    $params[] = $status;
}
if ($date_from) {
    $where[] = "r.tanggal >= ?";
    $params[] = date('Y-m-d H:i:s', strtotime($date_from));
}
if ($date_to) {
    $where[] = "r.tanggal <= ?";
    $params[] = date('Y-m-d 23:59:59', strtotime($date_to));
}

$sql = "SELECT r.id, r.kode, r.nama_pelanggan, r.telepon, r.plat_kendaraan, r.jenis_kendaraan, r.tanggal, r.status, r.catatan,
               s.id AS layanan_id, s.nama AS layanan_nama, s.harga AS layanan_harga,
               u.id AS mekanik_id, u.nama AS mekanik_nama
        FROM reservations r
        LEFT JOIN services s ON r.layanan_id = s.id
        LEFT JOIN users u ON r.mekanik_id = u.id";

if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY r.tanggal ASC LIMIT ?";

$stmt = $mysqli->prepare($sql);
$types = '';
if (count($params)) {
    foreach($params as $_) $types .= 's';
}
$types .= 'i';
$bindParams = $params;
$bindParams[] = $limit;

if ($types) {
    $refs = [];
    $refs[] = & $types;
    foreach ($bindParams as $k => $v) {
        $refs[] = & $bindParams[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
} else {
    $stmt->bind_param('i', $limit);
}

$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($r = $res->fetch_assoc()) {
    $data[] = [
        'id'=>$r['id'],
        'kode'=>$r['kode'],
        'nama_pelanggan'=>$r['nama_pelanggan'],
        'telepon'=>$r['telepon'],
        'plat_kendaraan'=>$r['plat_kendaraan'],
        'jenis_kendaraan'=>$r['jenis_kendaraan'],
        'tanggal'=>$r['tanggal'],
        'status'=>$r['status'],
        'catatan'=>$r['catatan'],
        'layanan'=>[
            'id'=>$r['layanan_id'],
            'nama'=>$r['layanan_nama'],
            'harga'=>$r['layanan_harga']
        ],
        'mekanik'=>[
            'id'=>$r['mekanik_id'],
            'nama'=>$r['mekanik_nama']
        ]
    ];
}
echo json_encode(['success'=>true, 'count'=>count($data), 'data'=>$data], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
