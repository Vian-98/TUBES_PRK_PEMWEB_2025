<?php
if (empty($_GET['id'])) {
    header('Location: list.php'); exit;
}
require_once __DIR__ . '/form_reservasi.php';
