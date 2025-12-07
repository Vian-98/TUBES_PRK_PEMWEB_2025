<?php
// Redirect to login or dashboard based on session
session_start();

if (isset($_SESSION['user_id'])) {
    // User logged in, go to dashboard router
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $basePath = str_replace('\\', '/', __DIR__);
    $baseUrl = str_replace($docRoot, '', $basePath);
    
    header('Location: ' . $baseUrl . '/dashboard/index.php');
} else {
    // Not logged in, go to login
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $basePath = str_replace('\\', '/', __DIR__);
    $baseUrl = str_replace($docRoot, '', $basePath);
    
    header('Location: ' . $baseUrl . '/auth/login.php');
}
exit;
