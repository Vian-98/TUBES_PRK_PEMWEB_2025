<?php
session_start();
session_destroy();

// Calculate base URL
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$basePath = str_replace('\\', '/', __DIR__ . '/..');
$baseUrl = str_replace($docRoot, '', $basePath);

header('Location: ' . $baseUrl . '/auth/login.php');
exit;
