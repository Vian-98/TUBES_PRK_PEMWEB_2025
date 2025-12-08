<?php
// /reservasi/edit_reservasi.php
// Hanya include form_reservasi dengan query param id
if (empty($_GET['id'])) {
    header('Location: list.php'); exit;
}
require_once __DIR__ . '/form_reservasi.php';
