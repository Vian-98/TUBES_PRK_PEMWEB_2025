-- Database POS UMKM Bengkel
CREATE DATABASE IF NOT EXISTS pos_bengkel CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pos_bengkel;

CREATE TABLE `roles` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `deskripsi` text,
  `created_at` datetime,
  `updated_at` datetime
) COMMENT = 'Menyimpan daftar peran pengguna (admin, kasir, mekanik)';

CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int NOT NULL,
  `telepon` varchar(30),
  `alamat` text,
  `aktif` boolean DEFAULT true,
  `created_at` datetime,
  `updated_at` datetime
) COMMENT = 'Menyimpan data pengguna sistem';

CREATE TABLE `suppliers` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `kontak` varchar(100),
  `telepon` varchar(30),
  `alamat` text,
  `created_at` datetime,
  `updated_at` datetime
) COMMENT = 'Menyimpan data pemasok sparepart';

CREATE TABLE `services` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `kode` varchar(50) UNIQUE NOT NULL,
  `deskripsi` text,
  `harga` decimal(13,2) DEFAULT 0,
  `durasi_menit` int,
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `parts` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nama` varchar(200) NOT NULL,
  `sku` varchar(100) UNIQUE,
  `image_url` varchar(255),
  `deskripsi` text,
  `harga_beli` decimal(13,2) DEFAULT 0,
  `harga_jual` decimal(13,2) DEFAULT 0,
  `stok` int DEFAULT 0,
  `supplier_id` int,
  `min_stok` int DEFAULT 0,
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `stock_movements` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `part_id` int NOT NULL,
  `tipe` ENUM ('masuk', 'keluar', 'penyesuaian') NOT NULL,
  `qty` int NOT NULL,
  `harga_unit` decimal(13,2),
  `keterangan` text,
  `created_by` int,
  `created_at` datetime
);

CREATE TABLE `reservations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `kode` varchar(100) UNIQUE NOT NULL,
  `nama_pelanggan` varchar(150) NOT NULL,
  `telepon` varchar(30),
  `plat_kendaraan` varchar(50),
  `jenis_kendaraan` varchar(100),
  `layanan_id` int,
  `mekanik_id` int,
  `tanggal` datetime NOT NULL,
  `status` ENUM ('booked', 'in_progress', 'completed', 'canceled') NOT NULL,
  `catatan` text,
  `created_by` int,
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `reservation_checkins` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `reservation_id` int NOT NULL,
  `checked_in_at` datetime NOT NULL,
  `checked_in_by` int,
  `draft_transaction_id` int,
  `catatan` text,
  `created_at` datetime
);

CREATE TABLE `transactions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `kode` varchar(100) UNIQUE NOT NULL,
  `reservation_id` int,
  `pelanggan_nama` varchar(150),
  `pelanggan_telepon` varchar(30),
  `total` decimal(13,2) DEFAULT 0,
  `diskon` decimal(13,2) DEFAULT 0,
  `grand_total` decimal(13,2) DEFAULT 0,
  `bayar` decimal(13,2) DEFAULT 0,
  `kembali` decimal(13,2) DEFAULT 0,
  `status` ENUM ('draft', 'paid', 'cancelled', 'refunded') DEFAULT 'paid',
  `kasir_id` int,
  `created_at` datetime,
  `updated_at` datetime
);

CREATE TABLE `transaction_items` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `service_id` int,
  `part_id` int,
  `nama_item` varchar(200) NOT NULL,
  `qty` int NOT NULL DEFAULT 1,
  `harga_unit` decimal(13,2) DEFAULT 0,
  `subtotal` decimal(13,2) DEFAULT 0,
  `created_at` datetime
);

CREATE TABLE `transaction_payments` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `metode` ENUM ('tunai', 'qris', 'transfer') NOT NULL,
  `jumlah` decimal(13,2) NOT NULL,
  `rincian` text,
  `dibayar_pada` datetime,
  `created_by` int,
  `created_at` datetime
);

CREATE TABLE `audit_logs` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int,
  `action` varchar(150),
  `tabel` varchar(100),
  `record_id` int,
  `before_data` text,
  `after_data` text,
  `created_at` datetime
);

-- Foreign Keys
ALTER TABLE `users` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
ALTER TABLE `parts` ADD FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);
ALTER TABLE `reservations` ADD FOREIGN KEY (`layanan_id`) REFERENCES `services` (`id`);
ALTER TABLE `reservations` ADD FOREIGN KEY (`mekanik_id`) REFERENCES `users` (`id`);
ALTER TABLE `reservations` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `reservation_checkins` ADD FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);
ALTER TABLE `reservation_checkins` ADD FOREIGN KEY (`checked_in_by`) REFERENCES `users` (`id`);
ALTER TABLE `transactions` ADD FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`);
ALTER TABLE `transactions` ADD FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`);
ALTER TABLE `transaction_items` ADD FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`);
ALTER TABLE `transaction_items` ADD FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);
ALTER TABLE `transaction_items` ADD FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`);
ALTER TABLE `stock_movements` ADD FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`);
ALTER TABLE `stock_movements` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `transaction_payments` ADD FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`);
ALTER TABLE `transaction_payments` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `audit_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- ROLES
INSERT INTO `roles` (`id`, `nama`, `deskripsi`, `created_at`) VALUES
(1, 'Admin', 'Administrator sistem', NOW()),
(2, 'Kasir', 'Operator kasir POS', NOW()),
(3, 'Mekanik', 'Teknisi bengkel', NOW()),
(4, 'Manager', 'Manager bengkel', NOW());

-- USERS (Password: password)
INSERT INTO `users` (`nama`, `email`, `password`, `role_id`, `telepon`, `aktif`, `created_at`) VALUES
('Admin System', 'admin@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '081234567890', 1, NOW()),
('Kasir Satu', 'kasir1@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567891', 1, NOW()),
('Kasir Dua', 'kasir2@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '081234567892', 1, NOW()),
('Mekanik Joko', 'joko@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, '081234567893', 1, NOW()),
('Manager Budi', 'manager@bengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, '081234567894', 1, NOW());

-- SUPPLIERS
INSERT INTO `suppliers` (`nama`, `kontak`, `telepon`, `alamat`, `created_at`) VALUES
('PT Astra Otoparts', 'Bapak Hendra', '021-5551234', 'Jakarta Pusat', NOW()),
('CV Maju Jaya', 'Ibu Siti', '021-5551235', 'Tangerang', NOW()),
('Toko Sparepart Makmur', 'Bapak Anton', '021-5551236', 'Bekasi', NOW());

-- SERVICES
INSERT INTO `services` (`nama`, `kode`, `deskripsi`, `harga`, `durasi_menit`, `created_at`) VALUES
('Servis Ringan', 'SRV001', 'Ganti oli mesin, cek filter udara, cek rem', 150000, 60, NOW()),
('Servis Berat', 'SRV002', 'Overhaul mesin lengkap', 500000, 180, NOW()),
('Tune Up Mesin', 'SRV003', 'Tune up dan setting mesin', 200000, 90, NOW()),
('Ganti Kampas Rem', 'SRV004', 'Penggantian kampas rem depan/belakang', 120000, 45, NOW()),
('Spooring Balancing', 'SRV005', 'Spooring dan balancing roda', 100000, 30, NOW()),
('Cuci Mesin', 'SRV006', 'Pembersihan ruang mesin', 75000, 30, NOW()),
('Ganti Aki', 'SRV007', 'Penggantian aki mobil', 80000, 20, NOW()),
('Perbaikan AC', 'SRV008', 'Service AC mobil', 250000, 120, NOW());

-- PARTS (Sparepart)
INSERT INTO `parts` (`nama`, `sku`, `deskripsi`, `harga_beli`, `harga_jual`, `stok`, `min_stok`, `supplier_id`, `created_at`) VALUES
-- Oli & Filter
('Oli Mesin 1L Synthetic', 'PRT001', 'Oli mesin synthetic premium', 50000, 75000, 47, 10, 1, NOW()),
('Oli Mesin 1L Mineral', 'PRT002', 'Oli mesin mineral standar', 35000, 50000, 80, 15, 1, NOW()),
('Filter Oli Standar', 'PRT003', 'Filter oli universal', 25000, 40000, 99, 20, 1, NOW()),
('Filter Udara', 'PRT004', 'Filter udara mesin', 30000, 50000, 60, 15, 1, NOW()),
-- Busi & Pengapian
('Busi Standar', 'PRT005', 'Busi standar platinum', 15000, 25000, 195, 30, 2, NOW()),
('Busi Iridium', 'PRT006', 'Busi iridium premium', 45000, 70000, 100, 20, 2, NOW()),
('Kabel Busi Set', 'PRT007', 'Set kabel busi 4 cylinder', 80000, 120000, 39, 10, 2, NOW()),
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
('Radiator Coolant 1L', 'PRT016', 'Cairan pendingin radiator', 35000, 55000, 58, 15, 2, NOW()),
('Wiper Blade Set', 'PRT017', 'Sepasang wiper blade', 50000, 80000, 45, 10, 2, NOW()),
('Air Filter Cabin', 'PRT018', 'Filter udara kabin AC', 40000, 65000, 49, 12, 2, NOW()),
('Belt Fan', 'PRT019', 'Belt kipas mesin', 45000, 70000, 35, 8, 1, NOW()),
('Belt Alternator', 'PRT020', 'Belt alternator', 55000, 85000, 30, 8, 1, NOW());

-- RESERVATIONS
INSERT INTO `reservations` (`kode`, `nama_pelanggan`, `telepon`, `plat_kendaraan`, `jenis_kendaraan`, `layanan_id`, `tanggal`, `status`, `catatan`, `created_at`) VALUES
('RSV20251205001', 'Budi Santoso', '08123456789', 'B 1234 XYZ', 'Toyota Avanza', 1, '2025-12-05 10:00:00', 'booked', 'Servis rutin bulanan', NOW()),
('RSV20251205002', 'Siti Nurhaliza', '08123456790', 'B 5678 ABC', 'Honda Jazz', 3, '2025-12-05 14:00:00', 'booked', 'Mesin agak brebet', NOW()),
('RSV20251206001', 'Ahmad Dahlan', '08123456791', 'B 9012 DEF', 'Daihatsu Xenia', 2, '2025-12-06 09:00:00', 'booked', 'Overhaul mesin', NOW());

-- TRANSAKSI SAMPLE
INSERT INTO `transactions` (`kode`, `pelanggan_nama`, `pelanggan_telepon`, `total`, `diskon`, `grand_total`, `bayar`, `kembali`, `status`, `kasir_id`, `created_at`) VALUES
('TRX20251201001', 'Customer Walk-in', '081234567899', 275000, 0, 275000, 300000, 25000, 'paid', 2, '2025-12-01 10:30:00'),
('TRX20251201002', 'Tono Suparman', '081234567898', 620000, 20000, 600000, 600000, 0, 'paid', 2, '2025-12-01 14:15:00'),
('TRX20251202001', 'Maria Ozawa', '081234567897', 175000, 0, 175000, 200000, 25000, 'paid', 2, '2025-12-02 09:45:00'),
('TRX20251203001', 'Joko Widodo', '081234567896', 550000, 50000, 500000, 500000, 0, 'paid', 3, '2025-12-03 11:20:00'),
-- Transaksi hari ini (untuk testing dashboard)
('TRX20251206001', 'Andi Pratama', '081234567895', 225000, 0, 225000, 250000, 25000, 'paid', 2, CONCAT(CURDATE(), ' 09:15:00')),
('TRX20251206002', 'Siti Aminah', '081234567894', 370000, 20000, 350000, 350000, 0, 'paid', 2, CONCAT(CURDATE(), ' 11:30:00')),
('TRX20251206003', 'Budi Setiawan', '081234567893', 180000, 0, 180000, 200000, 20000, 'paid', 3, CONCAT(CURDATE(), ' 14:45:00'));

-- TRANSACTION ITEMS
INSERT INTO `transaction_items` (`transaction_id`, `service_id`, `part_id`, `nama_item`, `qty`, `harga_unit`, `subtotal`, `created_at`) VALUES
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
(4, NULL, 1, 'Oli Mesin 1L Synthetic', 2, 75000, 150000, '2025-12-03 11:20:00'),
-- TRX005 (hari ini)
(5, 1, NULL, 'Servis Ringan', 1, 150000, 150000, CONCAT(CURDATE(), ' 09:15:00')),
(5, NULL, 1, 'Oli Mesin 1L Synthetic', 1, 75000, 75000, CONCAT(CURDATE(), ' 09:15:00')),
-- TRX006 (hari ini)
(6, 3, NULL, 'Tune Up Mesin', 1, 200000, 200000, CONCAT(CURDATE(), ' 11:30:00')),
(6, NULL, 5, 'Busi Standar', 4, 25000, 100000, CONCAT(CURDATE(), ' 11:30:00')),
(6, NULL, 3, 'Filter Oli Standar', 1, 40000, 40000, CONCAT(CURDATE(), ' 11:30:00')),
-- TRX007 (hari ini)
(7, 1, NULL, 'Servis Ringan', 1, 150000, 150000, CONCAT(CURDATE(), ' 14:45:00')),
(7, NULL, 4, 'Filter Udara', 1, 50000, 50000, CONCAT(CURDATE(), ' 14:45:00'));

-- PAYMENTS
INSERT INTO `transaction_payments` (`transaction_id`, `metode`, `jumlah`, `dibayar_pada`, `created_by`, `created_at`) VALUES
(1, 'tunai', 300000, '2025-12-01 10:35:00', 2, '2025-12-01 10:35:00'),
(2, 'qris', 600000, '2025-12-01 14:20:00', 2, '2025-12-01 14:20:00'),
(3, 'tunai', 200000, '2025-12-02 09:50:00', 2, '2025-12-02 09:50:00'),
(4, 'transfer', 500000, '2025-12-03 11:25:00', 3, '2025-12-03 11:25:00'),
-- Payments hari ini
(5, 'tunai', 250000, CONCAT(CURDATE(), ' 09:20:00'), 2, CONCAT(CURDATE(), ' 09:20:00')),
(6, 'qris', 350000, CONCAT(CURDATE(), ' 11:35:00'), 2, CONCAT(CURDATE(), ' 11:35:00')),
(7, 'tunai', 200000, CONCAT(CURDATE(), ' 14:50:00'), 3, CONCAT(CURDATE(), ' 14:50:00'));
