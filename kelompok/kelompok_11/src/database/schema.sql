CREATE TABLE `roles` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `deskripsi` text,
  `created_at` datetime,
  `updated_at` datetime
);

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
);

CREATE TABLE `suppliers` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `kontak` varchar(100),
  `telepon` varchar(30),
  `alamat` text,
  `created_at` datetime,
  `updated_at` datetime
);

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

ALTER TABLE `transaction_payments` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

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

ALTER TABLE `audit_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
