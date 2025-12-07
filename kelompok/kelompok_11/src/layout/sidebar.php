<nav class="nav flex-column py-3 h-100 d-flex flex-column">
    <a class="nav-link text-white <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= $baseUrl ?>/dashboard/dashboard.php">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
    
    <?php if (hasRole('admin')): ?>
    <a class="nav-link text-white <?= strpos($currentPage, 'part_') === 0 || strpos($currentPage, 'supplier_') === 0 ? 'active' : '' ?>" href="<?= $baseUrl ?>/inventory/part_list.php">
        <i class="bi bi-box-seam me-2"></i>Inventori
    </a>
    <?php endif; ?>
    
    <div class="mt-auto border-top border-white border-opacity-25 pt-2">
        <a class="nav-link text-white" href="<?= $baseUrl ?>/auth/logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </div>
</nav>
