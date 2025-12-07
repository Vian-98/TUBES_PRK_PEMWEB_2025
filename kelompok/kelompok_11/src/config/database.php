<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pos_bengkel');

// Global connection
$conn = null;

// Koneksi ke database
function getConnection() {
    global $conn;
    
    if ($conn === null) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$conn) {
            die("Koneksi database gagal: " . mysqli_connect_error());
        }
        
        mysqli_set_charset($conn, 'utf8mb4');
    }
    
    return $conn;
}

// Helper untuk query (return mysqli_result untuk backward compatibility)
function query($sql) {
    $conn = getConnection();
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        error_log("Query error: " . mysqli_error($conn) . " | SQL: " . $sql);
        return false;
    }
    
    // If SELECT query, return array of results for compatibility with auth module
    if (stripos(trim($sql), 'SELECT') === 0) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // For INSERT, UPDATE, DELETE - return result
    return $result;
}

// Helper untuk execute (untuk INSERT, UPDATE, DELETE)
function execute($sql) {
    $conn = getConnection();
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

// Helper untuk escape string
function escape($string) {
    $conn = getConnection();
    return mysqli_real_escape_string($conn, trim($string));
}

// Helper untuk fetch all
function fetchAll($sql) {
    $result = query($sql);
    if (is_array($result)) {
        return $result;
    }
    
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

// Helper untuk fetch single
function fetchOne($sql) {
    $result = query($sql);
    if (is_array($result)) {
        return $result[0] ?? null;
    }
    return $result ? mysqli_fetch_assoc($result) : null;
}
