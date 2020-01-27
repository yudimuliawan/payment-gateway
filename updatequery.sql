ALTER TABLE `invoice` ADD `kode_pembayaran` VARCHAR(100) NULL AFTER `grand_total`, ADD `jenis_pembayaran` ENUM('ovo','gopay','lainnya') NULL DEFAULT 'lainnya' AFTER `kode_pembayaran`;
