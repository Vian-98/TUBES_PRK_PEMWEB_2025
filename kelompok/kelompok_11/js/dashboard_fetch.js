// Get base URL dynamically
const scripts = document.getElementsByTagName('script');
const currentScript = scripts[scripts.length - 1];
const scriptSrc = currentScript.src;
const baseUrl = scriptSrc.substring(0, scriptSrc.lastIndexOf('/js/'));

// Fetch dashboard data
function fetchDashboardData() {
    fetch(baseUrl + '/dashboard/api_dashboard.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                updateDashboard(result.data);
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
        });
}

// Update dashboard UI
function updateDashboard(data) {
    // Update statistics
    document.getElementById('total-transaksi').textContent = data.total_transaksi;
    document.getElementById('omzet-hari-ini').textContent = 
        'Rp ' + parseInt(data.omzet_hari_ini).toLocaleString('id-ID');
    document.getElementById('reservasi-aktif').textContent = data.reservasi_aktif;
    
    // Update low stock table
    const lowStockTable = document.getElementById('low-stock-table');
    if (data.low_stock.length === 0) {
        lowStockTable.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Semua stok aman</td></tr>';
    } else {
        lowStockTable.innerHTML = data.low_stock.map(item => `
            <tr>
                <td>${item.nama}</td>
                <td><span class="badge badge-low-stock">${item.stok}</span></td>
                <td>${item.min_stok}</td>
            </tr>
        `).join('');
    }
    
    // Update top parts table
    const topPartsTable = document.getElementById('top-parts-table');
    if (data.top_parts.length === 0) {
        topPartsTable.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Belum ada data</td></tr>';
    } else {
        topPartsTable.innerHTML = data.top_parts.map(item => `
            <tr>
                <td>${item.nama}</td>
                <td><strong>${item.total_qty}</strong></td>
            </tr>
        `).join('');
    }
    
    // Update recent transactions table
    const recentTable = document.getElementById('recent-transactions-table');
    if (data.recent_transactions.length === 0) {
        recentTable.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Belum ada transaksi</td></tr>';
    } else {
        recentTable.innerHTML = data.recent_transactions.map(item => {
            const statusBadge = item.status === 'paid' ? 'bg-success' : 
                               item.status === 'draft' ? 'bg-secondary' : 'bg-danger';
            return `
                <tr>
                    <td>${item.kode}</td>
                    <td>${item.pelanggan_nama || '-'}</td>
                    <td>Rp ${parseInt(item.grand_total).toLocaleString('id-ID')}</td>
                    <td><span class="badge ${statusBadge}">${item.status}</span></td>
                    <td>${new Date(item.created_at).toLocaleString('id-ID')}</td>
                </tr>
            `;
        }).join('');
    }
}

// Fetch on page load
fetchDashboardData();

// Auto-refresh every 30 seconds
setInterval(fetchDashboardData, 30000);
