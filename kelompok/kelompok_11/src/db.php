<?php
// /database/db.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'pos umkm bengkel'; // ganti sesuai nama DB Anda

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die("DB connect error: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
