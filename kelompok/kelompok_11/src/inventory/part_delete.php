<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/cek_login.php';

require_role(['Admin']);

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $conn = getConnection();
    $sql = "DELETE FROM parts WHERE id = " . intval($id);
    mysqli_query($conn, $sql);
    mysqli_close($conn);
}

// Calculate base URL
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$basePath = str_replace('\\', '/', __DIR__ . '/..');
$baseUrl = str_replace($docRoot, '', $basePath);

header('Location: ' . $baseUrl . '/inventory/part_list.php');
exit;
