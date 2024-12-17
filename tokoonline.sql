-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2024 at 05:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tokoonline`
--

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `email` varchar(255) NOT NULL,
  `deskripsi` char(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keranjang`
--

INSERT INTO `keranjang` (`id`, `nama_produk`, `harga`, `qty`, `total`, `email`, `deskripsi`, `gambar`, `created_at`) VALUES
(4, 'Buku Tulisan', 5000.00, 2, 10000.00, 'aku123@gmail.com', 'Buku untuk catatan dengan 100 halaman', 'buku_tulisan.jpg', '2024-12-14 09:55:08'),
(21, 'Pulpen 0.5mm', 1500.00, 2, 3000.00, 'user123@gmail.com', 'Pulpen warna biru dengan tinta halus', 'images (1).jpg', '2024-12-17 11:21:13');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `tanggal_pesanan` datetime NOT NULL,
  `nama_penerima` varchar(255) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `metode_pembayaran` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL,
  `kondisi` enum('Diproses','Dibatalkan','Selesai','') NOT NULL,
  `tanggal_transfer` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan`
--

INSERT INTO `laporan` (`id`, `user_id`, `nama_produk`, `harga`, `qty`, `total_harga`, `tanggal_pesanan`, `nama_penerima`, `alamat_pengiriman`, `metode_pembayaran`, `catatan`, `kondisi`, `tanggal_transfer`) VALUES
(9, 2, 'Pulpen 0.5mm', 1500.00, 1, 1500.00, '2024-12-15 14:39:55', 'Diky', 'Jombang', 'Transfer', 'erd6ftguyh', 'Selesai', '2024-12-15 08:07:32'),
(10, 2, 'TEMPAT PENSIL POP IT/ TEMPAT PENSIL LUCU!! KOTAK PENSIL POP IT', 100000.00, 1, 100000.00, '2024-12-15 14:40:25', 'Diky', 'Jombang', 'Transfer', 'es5dr6tf7gyh', 'Diproses', '2024-12-15 08:07:37'),
(12, 2, 'Gelang Pria Rantai Gelang Titanium Keren - Putih', 114000.00, 2, 228000.00, '2024-12-15 15:13:05', 'Diky', 'Jombang', 'Transfer', 'DERF4GTRHYTU', 'Selesai', '2024-12-15 08:19:14'),
(13, 3, 'Gelang Kayu', 25000.00, 3, 75000.00, '2024-12-15 15:13:38', 'Yanto', 'Mojokerto', 'Transfer', '4RT56HYU', 'Selesai', '2024-12-15 11:47:47'),
(14, 3, 'Smartphone Android', 2500000.00, 2, 5000000.00, '2024-12-15 15:13:55', 'Yanto', 'Mojokerto', 'Transfer', 'Mojokerto', 'Selesai', '2024-12-15 12:46:51'),
(15, 2, 'Buku Tulisan', 5000.00, 1, 5000.00, '2024-12-17 10:55:59', 'Diky', 'Jombang', 'COD', 'SERDYUGHJ', 'Selesai', '2024-12-17 04:26:06');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `nama_produk` varchar(255) DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `kondisi` enum('Menunggu','Diproses','Terkirim','Dibatalkan') DEFAULT 'Menunggu',
  `tanggal_pesanan` timestamp NOT NULL DEFAULT current_timestamp(),
  `metode_pembayaran` enum('Transfer','COD') NOT NULL,
  `nama_penerima` varchar(255) NOT NULL,
  `nomor_telepon` varchar(20) NOT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `user_id`, `produk_id`, `nama_produk`, `harga`, `qty`, `total_harga`, `alamat_pengiriman`, `kondisi`, `tanggal_pesanan`, `metode_pembayaran`, `nama_penerima`, `nomor_telepon`, `catatan`) VALUES
(55, 2, 0, 'Buku Tulisan', 5000.00, 1, 305000.00, 'Jombang', 'Menunggu', '2024-12-17 04:19:43', 'COD', 'Diky', '08647687996557', 'RYTUGYIHJLK'),
(56, 2, 0, 'Headphone Bluetooth', 300000.00, 1, 305000.00, 'Jombang', 'Menunggu', '2024-12-17 04:19:43', 'COD', 'Diky', '08647687996557', 'RYTUGYIHJLK');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `deskripsi` text NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `stok` int(11) NOT NULL,
  `terjual` int(11) NOT NULL,
  `gambar` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `tanggal_ditambahkan` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('aktif','tidak aktif') DEFAULT 'aktif',
  `berat` decimal(10,2) NOT NULL,
  `panjang` decimal(10,2) NOT NULL,
  `lebar` decimal(10,2) NOT NULL,
  `tinggi` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama`, `harga`, `deskripsi`, `kategori`, `stok`, `terjual`, `gambar`, `tanggal_ditambahkan`, `status`, `berat`, `panjang`, `lebar`, `tinggi`) VALUES
(1, 'Pulpen 0.5mm', 1500.00, 'Pulpen warna biru dengan tinta halus', 'Alat Tulis', 87, 20, 'images (1).jpg', '2024-12-13 16:02:01', 'aktif', 0.02, 14.00, 1.00, 1.00),
(2, 'Buku Tulisan', 5000.00, 'Buku untuk catatan dengan 100 halaman', 'Alat Tulis', 195, 50, 'images.jpg', '2024-12-13 16:02:01', 'aktif', 0.30, 21.00, 14.00, 1.00),
(3, 'TEMPAT PENSIL POP IT/ TEMPAT PENSIL LUCU!! KOTAK PENSIL POP IT', 100000.00, 'Ukuran   P 20 X L 4 X T 7 CM\r\nBerat 100gram\r\nBahan Jelly Rubber\r\nKUALITAS DIJAMIN BAGUS', 'Alat Tulis', 1, 0, '33a24c0ddc4442d62b06ab849ebb1521.jpg', '2024-12-12 16:17:52', 'aktif', 0.10, 20.00, 4.00, 7.00),
(4, 'Gelang Pria Rantai Gelang Titanium Keren - Putih', 114000.00, 'Gelang Pria Rantai Gelang Titanium Keren - Putih', 'Aksesoris', 16, 0, 'oke.jpg', '2024-12-12 16:19:17', 'aktif', 0.01, 15.00, 5.00, 2.00),
(5, 'Gelang Kayu', 25000.00, 'Gelang kayu dengan desain etnik', 'Aksesoris', 47, 10, 'images (2).jpg', '2024-12-13 16:06:01', 'aktif', 0.10, 10.00, 10.00, 2.00),
(6, 'Kacamata Hitam', 75000.00, 'Kacamata hitam dengan UV protection', 'Aksesoris', 80, 30, 'sg-11134201-7rbk0-lksc191x84zc75.jpg', '2024-12-13 16:06:01', 'aktif', 0.20, 15.00, 6.00, 4.00),
(7, 'Smartphone Android', 2500000.00, 'Smartphone dengan layar 6.5 inch dan kamera 48MP', 'Elektronik', 48, 10, 'images (3).jpg', '2024-12-13 16:06:01', 'aktif', 0.40, 16.00, 8.00, 0.80),
(8, 'Headphone Bluetooth', 300000.00, 'Headphone nirkabel dengan noise-canceling', 'Elektronik', 60, 15, 'images (4).jpg', '2024-12-13 16:06:01', 'aktif', 0.30, 18.00, 7.00, 6.00),
(9, 'Kaos Pria Distro Lengan Pendek Kayser Time Baju T-Shirt Keren', 50000.00, 'Bahan Babyterry, Kualitas Bahan sedang.\r\n- Motif Sablon dengan heat press sistem  bukan manual (tangan)\r\n- Leher manset dan tangan manset menggunakan RIB Good Quality.\r\n- Lengan Pendek.\r\n\r\n>> DETAIL SIZE (Lengan Pendek dan Panjang Ukuran sama, hanya beda pada Lengannya) :\r\n- SIZE M - L : Lingkar Dada 104CM x Panjang baju 68CM\r\nLebar Baju 52CM\r\n\r\n- SIZE XL : Lingkar Dada 107CM x Panjang baju 70CM\r\nLebar Baju 54CM\r\n\r\n- Size XXL : Lingkar Dada 114CM x Panjang 72CM\r\nLebar Baju 57CM\r\n\r\nLeher manset dan tangan manset menggunakan RIB Good Quality\r\nNyaman di pakai\r\nTidak Pudar (Tajam)  & Tahan Lama dan tidak mudah Melar\r\nSisi jahitan, Samping + bawah sangat rapih.\r\n\r\nmengutamakan quality control sebelum pengiriman \r\n- Pengiriman 100% AMAN', 'Fasion', 243, 0, 'images (5).jpg', '2024-12-16 13:56:29', 'aktif', 0.40, 146.00, 89.00, 157.00);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat`
--

CREATE TABLE `riwayat` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `tanggal_pesanan` datetime NOT NULL,
  `nama_penerima` varchar(255) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `metode_pembayaran` varchar(255) NOT NULL,
  `tanggal_transfer` datetime NOT NULL,
  `kondisi` varchar(50) DEFAULT 'Selesai',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat`
--

INSERT INTO `riwayat` (`id`, `user_id`, `nama_produk`, `harga`, `qty`, `total_harga`, `tanggal_pesanan`, `nama_penerima`, `alamat_pengiriman`, `metode_pembayaran`, `tanggal_transfer`, `kondisi`, `created_at`) VALUES
(65, 2, 'Pulpen 0.5mm', 1500.00, 1, 1500.00, '2024-12-15 14:39:55', 'Diky Aja', 'Jombang Kota', 'Transfer', '2024-12-15 15:07:32', 'Selesai', '2024-12-15 12:58:17'),
(66, 2, 'TEMPAT PENSIL POP IT/ TEMPAT PENSIL LUCU!! KOTAK PENSIL POP IT', 100000.00, 1, 100000.00, '2024-12-15 14:40:25', 'Diky Aja', 'Jombang Kota', 'Transfer', '2024-12-15 15:07:37', 'Selesai', '2024-12-15 12:58:17'),
(67, 2, 'Gelang Pria Rantai Gelang Titanium Keren - Putih', 114000.00, 2, 228000.00, '2024-12-14 15:13:05', 'Diky Aja', 'Jombang Kota', 'Transfer', '2024-12-14 15:19:14', 'Selesai', '2024-12-14 12:58:17'),
(68, 3, 'Gelang Kayu', 25000.00, 3, 75000.00, '2024-12-15 15:13:38', 'Yanto', 'Mojokerto', 'Transfer', '2024-12-13 18:47:47', 'Selesai', '2024-12-15 12:58:21'),
(69, 3, 'Smartphone Android', 2500000.00, 2, 5000000.00, '2024-12-15 15:13:55', 'Yanto', 'Mojokerto', 'Transfer', '2024-12-13 19:46:51', 'Selesai', '2024-12-15 12:58:21'),
(70, 2, 'Buku Tulisan', 5000.00, 1, 5000.00, '2024-12-17 10:55:59', 'Diky', 'Jombang', 'COD', '2024-12-17 11:26:06', 'Selesai', '2024-12-17 04:26:22');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nama_panjang` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `domisili` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` enum('admin','user') NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `nama_panjang`, `alamat`, `domisili`, `password`, `type`, `tanggal`) VALUES
(1, 'diky', 'diky123@gmail.com', 'Diky Kurniawan', 'Jombang', 'Jombang', '$2y$10$2hVfH8sk9w.PZCOfJCyJc.2UhMP8pfobeJD05n8n/4s1OpsR/74Ni', 'admin', '2024-12-12 07:32:51'),
(2, 'user', 'user123@gmail.com', 'Username', 'Jombang', 'Jombang', '$2y$10$RAbq0v3f3q8vY9y2HzhFX.wedNsDalHf8s345VxrZoMdBoUo0xCvS', 'user', '2024-12-12 07:33:40'),
(3, 'aku', 'aku123@gmail.com', 'akusaja', 'Mojokerto', 'Mojokerto', '$2y$10$GAGBeWSl.GR6hC8HwcU/Auon4I9E97MCSRRxAzcmcoXQNrpR6TUxC', 'user', '2024-12-14 02:52:02'),
(4, 'yanto', 'yanto123@gmail.com', 'Yanto Resink', 'Kepuhdoko', 'Kepuhdoko', '$2y$10$wQm8pzleVB8MVgYgxCCGculd3Partns6Rz6R2YsYOiSPabPpPl9RS', 'user', '2024-12-15 17:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat`
--
ALTER TABLE `riwayat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `riwayat`
--
ALTER TABLE `riwayat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
