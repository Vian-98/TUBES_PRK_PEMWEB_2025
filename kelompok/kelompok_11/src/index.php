<?php
// Redirect to dashboard
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$basePath = str_replace('\\', '/', __DIR__);
$baseUrl = str_replace($docRoot, '', $basePath);

header('Location: ' . $baseUrl . '/dashboard/dashboard.php');
exit;
