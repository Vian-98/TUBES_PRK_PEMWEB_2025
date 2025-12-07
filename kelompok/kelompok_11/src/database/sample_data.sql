-- =============================================
-- DATA SAMPLE UNTUK TESTING POS
-- Bengkel UMKM - Point of Sale System
-- =============================================

-- ROLES
INSERT INTO roles (id, nama, deskripsi, created_at) VALUES
(1, 'Admin', 'Administrator sistem', NOW()),
(2, 'Kasir', 'Operator kasir POS', NOW()),
(3, 'Mekanik', 'Teknisi bengkel', NOW()),
(4, 'Manager', 'Manager bengkel', NOW());

-- USERS (Password: password)
INSERT INTO users (nama, email, password, role_id, telepon, aktif, created_at) VALUES
('Admin System', 'admin@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '081234567890', 1, NOW()),
('Kasir Satu', 'kasir1@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567891', 1, NOW()),
('Kasir Dua', 'kasir2@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567892', 1, NOW()),
('Mekanik Joko', 'joko@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, '081234567893', 1, NOW()),
('Manager Budi', 'manager@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, '081234567894', 1, NOW());

-- SUPPLIERS
INSERT INTO suppliers (nama, kontak, telepon, alamat, created_at) VALUES
('PT Astra Otoparts', 'Bapak Hendra', '021-5551234', 'Jakarta Pusat', NOW()),
('CV Maju Jaya', 'Ibu Siti', '021-5551235', 'Tangerang', NOW()),
('Toko Sparepart Makmur', 'Bapak Anton', '021-5551236', 'Bekasi', NOW());

-- SERVICES (Layanan)
INSERT INTO services (nama, kode, deskripsi, harga, durasi_menit, created_at) VALUES
('Servis Ringan', 'SRV001', 'Ganti oli mesin, cek filter udara, cek rem', 150000, 60, NOW()),
('Servis Berat', 'SRV002', 'Overhaul mesin lengkap', 500000, 180, NOW()),
('Tune Up Mesin', 'SRV003', 'Tune up dan setting mesin', 200000, 90, NOW()),
('Ganti Kampas Rem', 'SRV004', 'Penggantian kampas rem depan/belakang', 120000, 45, NOW()),
('Spooring Balancing', 'SRV005', 'Spooring dan balancing roda', 100000, 30, NOW()),
('Cuci Mesin', 'SRV006', 'Pembersihan ruang mesin', 75000, 30, NOW()),
('Ganti Aki', 'SRV007', 'Penggantian aki mobil', 80000, 20, NOW()),
('Perbaikan AC', 'SRV008', 'Service AC mobil', 250000, 120, NOW());

-- PARTS (Sparepart)
INSERT INTO parts (nama, sku, deskripsi, harga_beli, harga_jual, stok, min_stok, supplier_id, created_at) VALUES
-- Oli & Filter
('Oli Mesin 1L Synthetic', 'PRT001', 'Oli mesin synthetic premium', 50000, 75000, 50, 10, 1, NOW()),
('Oli Mesin 1L Mineral', 'PRT002', 'Oli mesin mineral standar', 35000, 50000, 80, 15, 1, NOW()),
('Filter Oli Standar', 'PRT003', 'Filter oli universal', 25000, 40000, 100, 20, 1, NOW()),
('Filter Udara', 'PRT004', 'Filter udara mesin', 30000, 50000, 60, 15, 1, NOW()),

-- Busi & Pengapian
('Busi Standar', 'PRT005', 'Busi standar platinum', 15000, 25000, 200, 30, 2, NOW()),
('Busi Iridium', 'PRT006', 'Busi iridium premium', 45000, 70000, 100, 20, 2, NOW()),
('Kabel Busi Set', 'PRT007', 'Set kabel busi 4 cylinder', 80000, 120000, 40, 10, 2, NOW()),
('Koil Pengapian', 'PRT008', 'Koil pengapian standar', 150000, 220000, 25, 5, 2, NOW()),

-- Rem
('Kampas Rem Depan', 'PRT009', 'Kampas rem depan original', 80000, 120000, 30, 8, 1, NOW()),
('Kampas Rem Belakang', 'PRT010', 'Kampas rem belakang', 70000, 100000, 35, 8, 1, NOW()),
('Minyak Rem DOT 3', 'PRT011', 'Minyak rem DOT 3 500ml', 25000, 40000, 50, 10, 1, NOW()),
('Disc Brake Depan', 'PRT012', 'Piringan rem depan', 200000, 300000, 20, 5, 1, NOW()),

-- Aki & Kelistrikan
('Aki 45Ah', 'PRT013', 'Aki kering 45Ah', 400000, 550000, 15, 3, 3, NOW()),
('Aki 65Ah', 'PRT014', 'Aki kering 65Ah', 600000, 800000, 10, 3, 3, NOW()),
('Lampu Depan LED', 'PRT015', 'Lampu LED H4', 120000, 180000, 40, 10, 3, NOW()),

-- Lain-lain
('Radiator Coolant 1L', 'PRT016', 'Cairan pendingin radiator', 35000, 55000, 60, 15, 2, NOW()),
('Wiper Blade Set', 'PRT017', 'Sepasang wiper blade', 50000, 80000, 45, 10, 2, NOW()),
('Air Filter Cabin', 'PRT018', 'Filter udara kabin AC', 40000, 65000, 50, 12, 2, NOW()),
('Belt Fan', 'PRT019', 'Belt kipas mesin', 45000, 70000, 35, 8, 1, NOW()),
('Belt Alternator', 'PRT020', 'Belt alternator', 55000, 85000, 30, 8, 1, NOW());

-- RESERVATIONS (Sample untuk testing check-in)
INSERT INTO reservations (kode, nama_pelanggan, telepon, plat_kendaraan, jenis_kendaraan, layanan_id, tanggal, status, catatan, created_at) VALUES
('RSV20251205001', 'Budi Santoso', '08123456789', 'B 1234 XYZ', 'Toyota Avanza', 1, '2025-12-05 10:00:00', 'booked', 'Servis rutin bulanan', NOW()),
('RSV20251205002', 'Siti Nurhaliza', '08123456790', 'B 5678 ABC', 'Honda Jazz', 3, '2025-12-05 14:00:00', 'booked', 'Mesin agak brebet', NOW()),
('RSV20251206001', 'Ahmad Dahlan', '08123456791', 'B 9012 DEF', 'Daihatsu Xenia', 2, '2025-12-06 09:00:00', 'booked', 'Overhaul mesin', NOW());

-- TRANSAKSI SAMPLE (untuk testing API)
INSERT INTO transactions (kode, pelanggan_nama, pelanggan_telepon, total, diskon, grand_total, bayar, kembali, status, kasir_id, created_at) VALUES
('TRX20251201001', 'Customer Walk-in', '081234567899', 275000, 0, 275000, 300000, 25000, 'paid', 2, '2025-12-01 10:30:00'),
('TRX20251201002', 'Tono Suparman', '081234567898', 620000, 20000, 600000, 600000, 0, 'paid', 2, '2025-12-01 14:15:00'),
('TRX20251202001', 'Maria Ozawa', '081234567897', 175000, 0, 175000, 200000, 25000, 'paid', 2, '2025-12-02 09:45:00'),
('TRX20251203001', 'Joko Widodo', '081234567896', 550000, 50000, 500000, 500000, 0, 'paid', 3, '2025-12-03 11:20:00');

-- ITEMS untuk transaksi di atas
INSERT INTO transaction_items (transaction_id, service_id, part_id, nama_item, qty, harga_unit, subtotal, created_at) VALUES
-- TRX001
(1, 1, NULL, 'Servis Ringan', 1, 150000, 150000, '2025-12-01 10:30:00'),
(1, NULL, 1, 'Oli Mesin 1L Synthetic', 1, 75000, 75000, '2025-12-01 10:30:00'),
(1, NULL, 3, 'Filter Oli Standar', 1, 40000, 40000, '2025-12-01 10:30:00'),

-- TRX002
(2, 2, NULL, 'Servis Berat', 1, 500000, 500000, '2025-12-01 14:15:00'),
(2, NULL, 5, 'Busi Standar', 4, 25000, 100000, '2025-12-01 14:15:00'),
(2, NULL, 7, 'Kabel Busi Set', 1, 120000, 120000, '2025-12-01 14:15:00'),

-- TRX003
(3, 1, NULL, 'Servis Ringan', 1, 150000, 150000, '2025-12-02 09:45:00'),
(3, NULL, 5, 'Busi Standar', 1, 25000, 25000, '2025-12-02 09:45:00'),

-- TRX004
(4, 8, NULL, 'Perbaikan AC', 1, 250000, 250000, '2025-12-03 11:20:00'),
(4, NULL, 16, 'Radiator Coolant 1L', 2, 55000, 110000, '2025-12-03 11:20:00'),
(4, NULL, 18, 'Air Filter Cabin', 1, 65000, 65000, '2025-12-03 11:20:00'),
(4, NULL, 1, 'Oli Mesin 1L Synthetic', 2, 75000, 150000, '2025-12-03 11:20:00');

-- PAYMENTS
INSERT INTO transaction_payments (transaction_id, metode, jumlah, dibayar_pada, created_by, created_at) VALUES
(1, 'tunai', 300000, '2025-12-01 10:35:00', 2, '2025-12-01 10:35:00'),
(2, 'qris', 600000, '2025-12-01 14:20:00', 2, '2025-12-01 14:20:00'),
(3, 'tunai', 200000, '2025-12-02 09:50:00', 2, '2025-12-02 09:50:00'),
(4, 'transfer', 500000, '2025-12-03 11:25:00', 3, '2025-12-03 11:25:00');

-- STOCK MOVEMENTS (untuk tracking)
INSERT INTO stock_movements (part_id, tipe, qty, harga_unit, keterangan, created_by, created_at) VALUES
-- Penjualan TRX001
(1, 'keluar', 1, 75000, 'Penjualan transaksi: TRX20251201001', 2, '2025-12-01 10:35:00'),
(3, 'keluar', 1, 40000, 'Penjualan transaksi: TRX20251201001', 2, '2025-12-01 10:35:00'),

-- Penjualan TRX002
(5, 'keluar', 4, 25000, 'Penjualan transaksi: TRX20251201002', 2, '2025-12-01 14:20:00'),
(7, 'keluar', 1, 120000, 'Penjualan transaksi: TRX20251201002', 2, '2025-12-01 14:20:00'),

-- Penjualan TRX003
(5, 'keluar', 1, 25000, 'Penjualan transaksi: TRX20251202001', 2, '2025-12-02 09:50:00'),

-- Penjualan TRX004
(16, 'keluar', 2, 55000, 'Penjualan transaksi: TRX20251203001', 3, '2025-12-03 11:25:00'),
(18, 'keluar', 1, 65000, 'Penjualan transaksi: TRX20251203001', 3, '2025-12-03 11:25:00'),
(1, 'keluar', 2, 75000, 'Penjualan transaksi: TRX20251203001', 3, '2025-12-03 11:25:00');

-- Update stok sesuai movement
UPDATE parts SET stok = stok - 3 WHERE id = 1;  -- Oli Mesin
UPDATE parts SET stok = stok - 1 WHERE id = 3;  -- Filter Oli
UPDATE parts SET stok = stok - 5 WHERE id = 5;  -- Busi Standar
UPDATE parts SET stok = stok - 1 WHERE id = 7;  -- Kabel Busi
UPDATE parts SET stok = stok - 2 WHERE id = 16; -- Radiator Coolant
UPDATE parts SET stok = stok - 1 WHERE id = 18; -- Air Filter Cabin

-- AUDIT LOGS (sample)
INSERT INTO audit_logs (user_id, action, tabel, record_id, after_data, created_at) VALUES
(2, 'CREATE_TRANSACTION', 'transactions', 1, '{"kode":"TRX20251201001","grand_total":275000,"metode":"tunai"}', '2025-12-01 10:35:00'),
(2, 'CREATE_TRANSACTION', 'transactions', 2, '{"kode":"TRX20251201002","grand_total":600000,"metode":"qris"}', '2025-12-01 14:20:00'),
(2, 'CREATE_TRANSACTION', 'transactions', 3, '{"kode":"TRX20251202001","grand_total":175000,"metode":"tunai"}', '2025-12-02 09:50:00'),
(3, 'CREATE_TRANSACTION', 'transactions', 4, '{"kode":"TRX20251203001","grand_total":500000,"metode":"transfer"}', '2025-12-03 11:25:00');

-- =============================================
-- QUERY TESTING
-- =============================================

-- Test 1: Lihat semua transaksi
SELECT t.kode, t.pelanggan_nama, t.grand_total, u.nama as kasir 
FROM transactions t 
LEFT JOIN users u ON t.kasir_id = u.id 
WHERE t.status = 'paid' 
ORDER BY t.created_at DESC;

-- Test 2: Lihat detail transaksi dengan items
SELECT t.kode, ti.nama_item, ti.qty, ti.harga_unit, ti.subtotal
FROM transactions t
JOIN transaction_items ti ON t.id = ti.transaction_id
WHERE t.kode = 'TRX20251201001';

-- Test 3: Total omzet hari ini
SELECT COALESCE(SUM(grand_total), 0) as omzet_hari_ini
FROM transactions
WHERE DATE(created_at) = CURDATE() AND status = 'paid';

-- Test 4: Item terlaris
SELECT ti.nama_item, SUM(ti.qty) as total_terjual
FROM transaction_items ti
JOIN transactions t ON ti.transaction_id = t.id
WHERE t.status = 'paid'
GROUP BY ti.nama_item
ORDER BY total_terjual DESC
LIMIT 10;

-- Test 5: Cek stok yang hampir habis
SELECT nama, sku, stok, min_stok
FROM parts
WHERE stok <= min_stok
ORDER BY stok ASC;
