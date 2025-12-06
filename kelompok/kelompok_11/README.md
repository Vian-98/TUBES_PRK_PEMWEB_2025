# POS UMKM Bengkel - Kelompok 11

## 👥 Anggota Kelompok

**Ketua Kelompok:**
- MUHAMMAD FAVIAN RIZKI (2315061067)

**Anggota:**
1. Daffa Raihan Permana (2315061082) - Anggota 1
2. Ary Nanda Pratama (2315061039) - Anggota 2
3. Tomy Arya Fiosa (2315061110) - Anggota 3
4. **[Anggota 4]** - Inventory Module, Dashboard, UI/Layout

---

## 📝 Deskripsi Project

Sistem Point of Sale (POS) untuk UMKM Bengkel yang mengelola transaksi servis kendaraan, inventori sparepart, reservasi pelanggan, dan laporan keuangan. Aplikasi berbasis web dengan role-based access untuk Admin, Kasir, dan Mekanik.

**Tema:** Digital Transformation for SMEs (POS, Marketplace, Inventori)

---

## ✨ Fitur Utama

### 1. User Management
- ✅ Login dengan role-based access (Admin, Kasir, Mekanik)
- ✅ Logout
- ✅ Session management
- ⚠️ Registrasi (dalam pengembangan)

### 2. Modul Inventory (Admin Only)
- ✅ CRUD Sparepart (tambah, edit, hapus, list)
- ✅ CRUD Supplier (tambah, edit, hapus, list)
- ✅ Low stock indicator
- ✅ API JSON untuk monitoring stok

### 3. Dashboard Real-time
- ✅ Statistik transaksi harian
- ✅ Omzet hari ini
- ✅ Reservasi aktif
- ✅ Alert stok menipis
- ✅ Sparepart terlaris
- ✅ Auto-refresh AJAX (30 detik)

### 4. Database
- ✅ 12 tabel relational (roles, users, suppliers, parts, services, transactions, dll)
- ✅ Foreign key constraints
- ✅ Sample data lengkap

---

## 🛠️ Teknologi yang Digunakan

- **Frontend:** HTML5, CSS3, Bootstrap 5.3.0 (CDN)
- **JavaScript:** Vanilla JS (Fetch API) - **No Framework**
- **Backend:** PHP Native 7.4+ - **No Framework**
- **Database:** MySQL/MariaDB
- **Version Control:** Git & GitHub
- **Server:** Apache (Laragon/XAMPP)

---

## 🚀 Cara Instalasi & Menjalankan

### 1. Persiapan Database

```sql
-- Buat database
CREATE DATABASE pos_bengkel CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Import schema
mysql -u root -p pos_bengkel < src/database/pos_bengkel.sql
```

Atau via phpMyAdmin:
1. Buat database `pos_bengkel`
2. Import file `src/database/pos_bengkel.sql`

### 2. Konfigurasi Database

Edit file `src/config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Sesuaikan password MySQL Anda
define('DB_NAME', 'pos_bengkel');
```

### 3. Deploy Aplikasi

**Untuk Laragon:**
```bash
# Copy project ke C:\laragon\www\
# Akses: http://localhost/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_11/src/
```

**Untuk XAMPP:**
```bash
# Copy project ke C:\xampp\htdocs\
# Akses: http://localhost/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_11/src/
```

### 4. Login

**Default Admin:**
- Email: `admin@posbengkel.com`
- Password: `password`

---

## 📁 Struktur Folder

```
kelompok_11/
├── README.md                    # Dokumentasi utama
├── BRANCH_INFO.md              # Info branch strategy
├── PROJECT_SUMMARY.md          # Ringkasan lengkap
├── documentation/              # Dokumentasi teknis
│   ├── README.md              # Fitur Anggota 4
│   ├── INSTALL.md             # Panduan instalasi detail
│   ├── ERD.txt                # Entity Relationship Diagram
│   └── struktur_branch.txt    # Git workflow
└── src/                        # Source code
    ├── auth/                  # Login & logout
    ├── config/                # Database & session config
    ├── css/                   # Custom styling
    ├── dashboard/             # Dashboard & API
    ├── database/              # SQL schema
    ├── inventory/             # CRUD sparepart & supplier
    ├── js/                    # JavaScript files
    ├── layout/                # Header, sidebar, footer
    └── index.php              # Landing page
```

---

## 🎨 Pembagian Tugas Anggota 4

**Branch:** `feature/inventory`

**Tanggung Jawab:**
1. **Modul Inventory** - CRUD Sparepart & Supplier (9 files)
2. **Modul Dashboard** - Real-time statistics dengan AJAX (3 files)
3. **UI/Layout** - Header, sidebar, footer, custom CSS (4 files)
4. **Config & Auth** - Database connection, session, login/logout (4 files)
5. **Dokumentasi** - README, INSTALL, ERD, Git workflow (4 files)

**Total:** 24 files + database schema

**Commits:** 10 commits bertahap dengan conventional commit messages

---

## 📊 Database Schema (ERD)

Database terdiri dari **12 tabel utama:**

1. `roles` - Role pengguna (admin, kasir, mekanik)
2. `users` - Data pengguna sistem
3. `suppliers` - Data pemasok sparepart
4. `services` - Jenis layanan bengkel
5. `parts` - Data sparepart/inventori
6. `stock_movements` - Riwayat pergerakan stok
7. `reservations` - Reservasi pelanggan
8. `reservation_checkins` - Check-in reservasi
9. `transactions` - Transaksi penjualan
10. `transaction_items` - Detail item transaksi
11. `transaction_payments` - Pembayaran transaksi
12. `audit_logs` - Log aktivitas sistem

**Lihat detail:** `documentation/ERD.txt`

---

## 🔐 Role & Hak Akses

| Role | Dashboard | Inventory | Transaksi |
|------|-----------|-----------|-----------|
| Admin | ✅ | ✅ | ✅ |
| Kasir | ✅ | ❌ | ✅ |
| Mekanik | ✅ | ❌ | ❌ |

---

## 📸 Screenshot

*(Screenshot akan ditambahkan setelah deployment)*

---

## 🐛 Troubleshooting

**Error: Unknown database 'pos_bengkel'**
- Import file `src/database/pos_bengkel.sql` terlebih dahulu

**Error: 404 Not Found**
- Pastikan path URL sesuai dengan lokasi folder

**Dashboard tidak refresh**
- Cek Console browser untuk error JavaScript
- Pastikan API `dashboard/api_dashboard.php` bisa diakses

---

## 📞 Support

Dokumentasi lengkap ada di folder `documentation/`

---

**Status:** ✅ Production Ready  
**Last Update:** December 6, 2025  
**Branch:** feature/inventory