<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = getUser();
?>
<div class="sidebar">
    <nav class="nav flex-column">
        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>/dashboard/dashboard.php">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        
        <?php if (hasRole('admin')): ?>
        <a class="nav-link <?= strpos($currentPage, 'part_') === 0 || strpos($currentPage, 'supplier_') === 0 ? 'active' : '' ?>" href="<?= $baseUrl ?>/inventory/part_list.php">
            <i class="bi bi-box-seam me-2"></i>Inventori
        </a>
        <?php endif; ?>
        
        <hr class="my-2">
        
        <a class="nav-link" href="<?= $baseUrl ?>/auth/logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </nav>
</div>
