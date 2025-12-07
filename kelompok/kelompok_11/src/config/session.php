<?php
// STUB SESSION - Untuk testing modul inventory & dashboard
// File ini akan diganti oleh Anggota 1 (User Management)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set dummy user untuk testing
// UBAH ROLE DI SINI: 'Admin', 'Kasir', atau 'Mekanik'
$test_role = 'Admin'; // <-- GANTI ROLE DI SINI

// Force update session (always override untuk testing)
$_SESSION['user_id'] = 1;
$_SESSION['nama'] = $test_role . ' Testing';
$_SESSION['email'] = strtolower($test_role) . '@bengkel.com';
$_SESSION['role'] = $test_role;

// Stub functions
function isLoggedIn() {
    return true; // Always logged in untuk testing
}

function hasRole($role) {
    return true; // Always has role untuk testing
}

function requireLogin() {
    // Do nothing - always logged in
}

function requireRole($role) {
    // Do nothing - always has role
}

function getUser() {
    return [
        'id' => $_SESSION['user_id'] ?? 1,
        'nama' => $_SESSION['nama'] ?? 'Admin Testing',
        'email' => $_SESSION['email'] ?? 'admin@bengkel.com',
        'role' => $_SESSION['role'] ?? 'Admin'
    ];
}
