<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../config/database.php';

// Calculate base URL
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$basePath = str_replace('\\', '/', __DIR__ . '/..');
$baseUrl = str_replace($docRoot, '', $basePath);
?>

<h2 class="mb-3">Dashboard</h2>

<!-- Statistics Cards -->
<div class="row" id="stats-container">
        <div class="col-md-4 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <p class="mb-1 text-muted">Total Transaksi Hari Ini</p>
                    <h3 class="text-primary-blue" id="total-transaksi">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <p class="mb-1 text-muted">Omzet Hari Ini</p>
                    <h3 class="text-primary-blue" id="omzet-hari-ini">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <p class="mb-1 text-muted">Reservasi Aktif</p>
                    <h3 class="text-primary-blue" id="reservasi-aktif">-</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-exclamation-triangle me-2"></i>Stok Menipis
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Sparepart</th>
                                                <th>Stok</th>
                                                <th>Min Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody id="low-stock-table">
                                            <tr>
                                                <td colspan="3" class="text-center">Memuat data...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-graph-up me-2"></i>Sparepart Terlaris
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Sparepart</th>
                                                <th>Terjual</th>
                                            </tr>
                                        </thead>
                                        <tbody id="top-parts-table">
                                            <tr>
                                                <td colspan="2" class="text-center">Memuat data...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-clock-history me-2"></i>Transaksi Terakhir
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Pelanggan</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recent-transactions-table">
                                            <tr>
                                                <td colspan="5" class="text-center">Memuat data...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<script src="<?= $baseUrl ?>/js/dashboard_fetch.js"></script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>