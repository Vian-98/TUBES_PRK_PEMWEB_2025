# Panduan Instalasi POS UMKM Bengkel

## Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Edge)

## Langkah Instalasi

### 1. Persiapan Database
```sql
-- Buat database baru
CREATE DATABASE pos_bengkel CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Import schema database
mysql -u root -p pos_bengkel < database/pos_bengkel.sql
```

Atau melalui phpMyAdmin:
1. Buka phpMyAdmin
2. Buat database baru dengan nama `pos_bengkel`
3. Import file `database/pos_bengkel.sql`

### 2. Konfigurasi Database
Edit file `config/database.php` sesuai dengan konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pos_bengkel');
```

### 3. Deploy Aplikasi
**Untuk Laragon:**
1. Copy folder project ke `C:\laragon\www\`
2. Akses melalui browser: `http://localhost/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_11/`

**Untuk XAMPP:**
1. Copy folder project ke `C:\xampp\htdocs\`
2. Akses melalui browser: `http://localhost/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_11/`

### 4. Login Pertama Kali
Gunakan akun default admin:
- Email: `admin@posbengkel.com`
- Password: `password`

**PENTING**: Segera ganti password setelah login pertama!

## Struktur Folder
```
kelompok_11/
├── auth/               # Modul autentikasi
├── config/             # Konfigurasi database & session
├── css/                # Custom styling
├── dashboard/          # Modul dashboard
├── database/           # Schema database
├── documentation/      # Dokumentasi
├── inventory/          # Modul inventory
├── js/                 # JavaScript files
├── layout/             # Template layout
└── index.php           # Landing page
```

## Testing
1. Login dengan akun admin
2. Akses Dashboard - pastikan statistik muncul
3. Buka menu Inventory > Sparepart - cek CRUD sparepart
4. Buka menu Inventory > Supplier - cek CRUD supplier
5. Verifikasi auto-refresh dashboard (30 detik)

## Troubleshooting

### Error: Unknown database 'pos_bengkel'
**Solusi**: Import database terlebih dahulu menggunakan file `database/pos_bengkel.sql`

### Error: 404 Not Found
**Solusi**: Pastikan path URL sesuai dengan lokasi folder di document root

### Dashboard tidak menampilkan data
**Solusi**: 
1. Cek koneksi database di `config/database.php`
2. Pastikan ada data sample di database
3. Buka Console browser untuk cek error JavaScript

### Styling tidak muncul
**Solusi**: Pastikan file `css/custom.css` dapat diakses dan path benar

## Browser yang Direkomendasikan
- Google Chrome (versi terbaru)
- Mozilla Firefox (versi terbaru)
- Microsoft Edge (versi terbaru)

## Catatan Keamanan
1. Ganti password default admin
2. Jangan expose database credentials
3. Gunakan HTTPS di production
4. Validasi semua input dari user
5. Backup database secara berkala

## Support
Jika mengalami masalah, hubungi:
- Anggota 4 - Kelompok 11
- Email support: [email support]
