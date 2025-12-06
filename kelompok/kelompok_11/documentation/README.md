# Modul Anggota 4 - POS UMKM Bengkel

## Deskripsi
Modul yang dikerjakan oleh Anggota 4 mencakup:
1. **Modul Inventory**: CRUD Sparepart dan Supplier
2. **Modul Dashboard**: Statistik dan monitoring realtime
3. **UI/Layout**: Header, sidebar, footer dengan styling blue-gray palette
4. **Dokumentasi**: Dokumentasi lengkap sistem

## Struktur Branch
- `feature/inventory` - Fitur inventory (sparepart & supplier)
- `feature/dashboard` - Fitur dashboard dengan AJAX
- `feature/ui-docs` - UI layout dan dokumentasi

## Fitur yang Diimplementasikan

### 1. Modul Inventory
#### CRUD Sparepart
- `inventory/part_list.php` - Daftar sparepart dengan indikator low stock
- `inventory/part_add.php` - Tambah sparepart baru
- `inventory/part_edit.php` - Edit sparepart
- `inventory/part_delete.php` - Hapus sparepart

#### CRUD Supplier
- `inventory/supplier_list.php` - Daftar supplier
- `inventory/supplier_add.php` - Tambah supplier
- `inventory/supplier_edit.php` - Edit supplier
- `inventory/supplier_delete.php` - Hapus supplier

#### API Inventory
- `inventory/api_inventory.php` - JSON API untuk low stock items

### 2. Modul Dashboard
- `dashboard/dashboard.php` - Halaman dashboard utama
- `dashboard/api_dashboard.php` - JSON API untuk statistik
- `js/dashboard_fetch.js` - AJAX dengan auto-refresh 30 detik

**Statistik yang ditampilkan:**
- Total transaksi hari ini
- Omzet hari ini
- Reservasi aktif
- Sparepart stok menipis
- Sparepart terlaris
- Transaksi terakhir

### 3. UI/Layout
- `layout/header.php` - Header dengan navbar Bootstrap
- `layout/sidebar.php` - Sidebar dengan menu role-based
- `layout/footer.php` - Footer
- `css/custom.css` - Custom styling dengan palet warna:
  - Primary Blue: #294B93
  - Light Gray: #9B9B9B
  - Medium Gray: #656565
  - Dark Gray: #4C4C4C
  - White: #FFFFFF

### 4. Konfigurasi & Auth
- `config/database.php` - Koneksi database & helper functions
- `config/session.php` - Session management & role checking
- `auth/login.php` - Halaman login
- `auth/logout.php` - Logout handler

### 5. Database
- `database/pos_bengkel.sql` - Schema database lengkap dengan 12 tabel dan sample data

## Teknologi yang Digunakan
- **Backend**: PHP Native 7.4+ (no framework)
- **Frontend**: HTML5, CSS3, Bootstrap 5.3.0 (CDN)
- **JavaScript**: Vanilla JS dengan Fetch API
- **Database**: MySQL/MariaDB

## Role-Based Access
- **Admin**: Akses penuh ke semua modul termasuk inventory
- **Kasir**: Akses terbatas (tidak termasuk inventory)
- **Mekanik**: Akses terbatas (tidak termasuk inventory)

## Fitur Unggulan
1. **Dynamic Base URL**: Otomatis menghitung path dari document root
2. **Real-time Dashboard**: Auto-refresh setiap 30 detik
3. **Low Stock Alert**: Indikator visual untuk stok menipis
4. **Responsive Design**: Bootstrap 5 untuk tampilan mobile-friendly
5. **RESTful API**: JSON API untuk data fetching
6. **Role-Based Menu**: Menu dinamis berdasarkan role user

## Login Default
- Email: `admin@posbengkel.com`
- Password: `password`

## Instalasi
Lihat file `INSTALL.md` untuk panduan lengkap instalasi.

## Author
Anggota 4 - Kelompok 11
