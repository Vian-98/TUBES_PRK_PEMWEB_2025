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

-- Insert default roles
INSERT INTO `roles` (`nama`, `deskripsi`, `created_at`) VALUES
('admin', 'Administrator dengan akses penuh', NOW()),
('kasir', 'Kasir POS', NOW()),
('mekanik', 'Mekanik bengkel', NOW());

-- Insert default admin (password: password)
INSERT INTO `users` (`nama`, `email`, `password`, `role_id`, `aktif`, `created_at`) VALUES
('Administrator', 'admin@posbengkel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, true, NOW());

-- Insert sample suppliers
INSERT INTO `suppliers` (`nama`, `kontak`, `telepon`, `alamat`, `created_at`) VALUES
('PT Auto Parts Indonesia', 'John Doe', '081234567890', 'Jakarta Pusat', NOW()),
('CV Sparepart Motor', 'Jane Smith', '082345678901', 'Bandung', NOW());

-- Insert sample services
INSERT INTO `services` (`nama`, `kode`, `deskripsi`, `harga`, `durasi_menit`, `created_at`) VALUES
('Service Rutin', 'SRV001', 'Ganti oli + cek mesin', 150000, 60, NOW()),
('Ganti Ban', 'SRV002', 'Ganti ban motor', 200000, 30, NOW()),
('Tune Up', 'SRV003', 'Tune up lengkap', 300000, 90, NOW());

-- Insert sample parts
INSERT INTO `parts` (`nama`, `sku`, `deskripsi`, `harga_beli`, `harga_jual`, `stok`, `supplier_id`, `min_stok`, `created_at`) VALUES
('Oli Mesin 1L', 'OLI001', 'Oli mesin berkualitas tinggi', 40000, 55000, 50, 1, 10, NOW()),
('Ban Tubeless 80/90', 'BAN001', 'Ban tubeless ukuran 80/90', 150000, 200000, 20, 1, 5, NOW()),
('Ban Tubeless 90/90', 'BAN002', 'Ban tubeless ukuran 90/90', 170000, 220000, 15, 1, 5, NOW()),
('Filter Oli', 'FILTER001', 'Filter oli original', 15000, 25000, 30, 2, 10, NOW());
