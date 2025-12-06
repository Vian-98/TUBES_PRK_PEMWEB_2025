<?php
// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Cek role user
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Require login - redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /TUBES_PRK_PEMWEB_2025/kelompok/kelompok_11/auth/login.php');
        exit;
    }
}

// Require role tertentu
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        die('Akses ditolak. Anda tidak memiliki hak akses.');
    }
}

// Get user data dari session
function getUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nama' => $_SESSION['nama'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? ''
    ];
}
