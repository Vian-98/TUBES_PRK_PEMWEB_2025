<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pos_bengkel');

// Koneksi ke database
function getConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

// Helper untuk query
function query($sql) {
    $conn = getConnection();
    $result = mysqli_query($conn, $sql);
    mysqli_close($conn);
    return $result;
}

// Helper untuk fetch all
function fetchAll($sql) {
    $result = query($sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Helper untuk fetch single
function fetchOne($sql) {
    $result = query($sql);
    return mysqli_fetch_assoc($result);
}
