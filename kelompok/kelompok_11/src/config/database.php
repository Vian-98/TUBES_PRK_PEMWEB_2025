<?php
/**
 * ================================================
 * DATABASE CONNECTION - FINAL FIX
 * ================================================
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pos_ukm');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

function query($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return [];
    }
    
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    
    return $rows;
}

function execute($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return [
            'success' => false,
            'error' => mysqli_error($conn)
        ];
    }
    
    return [
        'success' => true,
        'affected_rows' => mysqli_affected_rows($conn),
        'insert_id' => mysqli_insert_id($conn)
    ];
}

function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($string));
}
?>