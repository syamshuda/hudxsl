-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Agu 2025 pada 12.25
-- Versi server: 10.4.6-MariaDB
-- Versi PHP: 8.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_shop`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `produk_id`, `jumlah`, `harga_satuan`) VALUES
(132, 129, 4, 2, 45000.00),
(133, 130, 4, 2, 45000.00),
(134, 131, 4, 2, 45000.00),
(135, 132, 6, 1, 15000.00),
(136, 133, 4, 1, 45000.00),
(137, 134, 4, 1, 45000.00),
(138, 135, 4, 1, 45000.00),
(139, 136, 4, 1, 45000.00),
(140, 137, 4, 3, 45000.00),
(141, 138, 5, 1, 8500.00),
(142, 139, 4, 1, 45000.00),
(143, 140, 4, 1, 45000.00),
(144, 141, 4, 1, 45000.00),
(145, 142, 4, 1, 45000.00),
(146, 143, 4, 1, 45000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `icon`) VALUES
(1, 'Elektronik', '/assets/img/icons/elektronik.png'),
(2, 'Fashion Pria', '/assets/img/icons/fashion_pria.png'),
(3, 'Fashion Wanita', '/assets/img/icons/fashion_wanita.png'),
(4, 'Kecantikan', '/assets/img/icons/kecantikan.png'),
(5, 'Rumah & Dapur', '/assets/img/icons/rumah_dapur.png'),
(6, 'Makanan', '/assets/img/icons/makanan.png'),
(7, 'Minuman', '/assets/img/icons/minuman.png'),
(8, 'Hobi & Koleksi', '/assets/img/icons/hobi.png'),
(9, 'Olahraga & Outdoor', '/assets/img/icons/olahraga.png'),
(10, 'Buku & Alat Tulis', '/assets/img/icons/buku.png'),
(11, 'Perawatan Hewan', '/assets/img/icons/hewan.png'),
(12, 'Lainnya', '/assets/img/icons/lainnya.png'),
(13, 'E-book', '/assets/img/icons/688fdda23743c.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `klaim_voucher`
--

CREATE TABLE `klaim_voucher` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `tanggal_klaim` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kurir_lokal`
--

CREATE TABLE `kurir_lokal` (
  `id` int(11) NOT NULL,
  `toko_id` int(11) NOT NULL,
  `nama_kurir` varchar(100) NOT NULL,
  `no_telepon` varchar(20) NOT NULL,
  `username_kurir` varchar(50) NOT NULL,
  `password_kurir` varchar(255) NOT NULL,
  `tipe_gaji` enum('persen','flat') NOT NULL DEFAULT 'flat',
  `nilai_gaji` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `kurir_lokal`
--

INSERT INTO `kurir_lokal` (`id`, `toko_id`, `nama_kurir`, `no_telepon`, `username_kurir`, `password_kurir`, `tipe_gaji`, `nilai_gaji`, `is_active`) VALUES
(2, 6, 'Mat Kurir', '085150684697', 'matkurir', '$2y$12$S/s2ID3EGedwVyFaxURNjeNdyweizWSmN1JQPy/DlMBu7hMuukcyG', 'persen', 50.00, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `tipe` enum('pembeli','penjual') NOT NULL,
  `sudah_dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `judul`, `pesan`, `link`, `tipe`, `sudah_dibaca`, `created_at`) VALUES
(80, 5, 'Pesanan COD Baru!', 'Pesanan #111 (COD) telah dibuat dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-05 02:38:03'),
(81, 4, 'Pesanan Dikirim!', 'Pesanan Anda #111 telah dikirim. Keterangan: Dikirim Dayat', '/detail_pesanan.php?id=111', 'pembeli', 0, '2025-08-05 02:39:36'),
(82, 5, 'Pesanan Baru!', 'Pesanan #112 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-05 03:24:24'),
(83, 4, 'Pesanan Dikirim!', 'Pesanan Anda #112 telah dikirim. Keterangan: sdgbsdfg', '/detail_pesanan.php?id=112', 'pembeli', 0, '2025-08-05 03:25:07'),
(84, 5, 'Pesanan COD Baru!', 'Pesanan #113 (COD) telah dibuat dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-05 05:17:13'),
(85, 4, 'Pesanan Dikirim!', 'Pesanan Anda #113 telah dikirim. Keterangan: Dikirim dayat', '/detail_pesanan.php?id=113', 'pembeli', 0, '2025-08-05 05:24:38'),
(86, 7, 'Pesanan Baru!', 'Pesanan #114 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-05 05:47:38'),
(87, 4, 'Pesanan Dikirim!', 'Pesanan Anda #114 telah dikirim. Keterangan: Produk digital telah dikirim via email', '/detail_pesanan.php?id=114', 'pembeli', 0, '2025-08-05 05:49:44'),
(88, 5, 'Pesanan Baru!', 'Pesanan #115 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-06 20:04:22'),
(89, 4, 'Pesanan Dikirim!', 'Pesanan Anda #115 telah dikirim. Keterangan: 1234abc', '/detail_pesanan.php?id=115', 'pembeli', 0, '2025-08-06 20:06:24'),
(90, 4, 'Pesanan Dikirim!', 'Pesanan Anda #116 telah dikirim. Keterangan: Jsjs', '/detail_pesanan.php?id=116', 'pembeli', 0, '2025-08-06 21:53:55'),
(91, 5, 'Pesanan Baru!', 'Pesanan #117 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-06 21:57:37'),
(92, 4, 'Pesanan Dikirim!', 'Pesanan Anda #117 telah dikirim. Keterangan: 1234abc', '/detail_pesanan.php?id=117', 'pembeli', 0, '2025-08-06 21:58:34'),
(93, 5, 'Pesanan Baru!', 'Pesanan #118 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-06 22:07:53'),
(94, 4, 'Pesanan Dikirim!', 'Pesanan Anda #118 telah dikirim. Keterangan: 1234abc', '/detail_pesanan.php?id=118', 'pembeli', 0, '2025-08-06 22:09:05'),
(95, 4, 'Pesanan Dikirim!', 'Pesanan Anda #123 telah dikirim. ', '/detail_pesanan.php?id=123', 'pembeli', 0, '2025-08-15 06:23:02'),
(96, 5, 'Pesanan Baru!', 'Pesanan #129 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 06:52:18'),
(97, 4, 'Pesanan Dikirim!', 'Pesanan Anda #129 telah dikirim. Keterangan: Oke', '/detail_pesanan.php?id=129', 'pembeli', 0, '2025-08-15 06:53:20'),
(98, 5, 'Pesanan Baru!', 'Pesanan #130 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 07:04:20'),
(99, 4, 'Pesanan Dikirim!', 'Pesanan Anda #130 telah dikirim. Keterangan: Ubub', '/detail_pesanan.php?id=130', 'pembeli', 0, '2025-08-15 07:04:33'),
(100, 5, 'Pesanan Baru!', 'Pesanan #131 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 07:20:34'),
(101, 4, 'Pesanan Dikirim!', 'Pesanan Anda #131 telah dikirim. Keterangan: Hh', '/detail_pesanan.php?id=131', 'pembeli', 0, '2025-08-15 07:20:52'),
(102, 7, 'Pesanan Baru!', 'Pesanan #132 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 09:45:30'),
(103, 4, 'Pesanan Dikirim!', 'Pesanan Anda #132 telah dikirim. Keterangan: Ajaj', '/detail_pesanan.php?id=132', 'pembeli', 0, '2025-08-15 09:46:09'),
(104, 5, 'Pesanan Baru!', 'Pesanan #133 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 09:53:03'),
(105, 4, 'Pesanan Dikirim!', 'Pesanan Anda #133 telah dikirim. Keterangan: Jsj', '/detail_pesanan.php?id=133', 'pembeli', 0, '2025-08-15 09:54:02'),
(106, 5, 'Pesanan Baru!', 'Pesanan #134 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 10:01:09'),
(107, 4, 'Pesanan Dikirim!', 'Pesanan Anda #134 telah dikirim. Keterangan: Dde', '/detail_pesanan.php?id=134', 'pembeli', 0, '2025-08-15 10:01:22'),
(108, 5, 'Pesanan Baru!', 'Pesanan #135 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 10:53:13'),
(109, 5, 'Pesanan Baru!', 'Pesanan #136 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 10:53:23'),
(110, 5, 'Pesanan Baru!', 'Pesanan #137 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-15 10:53:31'),
(111, 4, 'Pesanan Dikirim!', 'Pesanan Anda #135 telah dikirim. Keterangan: Bsbs', '/detail_pesanan.php?id=135', 'pembeli', 0, '2025-08-15 10:53:49'),
(112, 4, 'Pesanan Dikirim!', 'Pesanan Anda #136 telah dikirim. Keterangan: Ndndn', '/detail_pesanan.php?id=136', 'pembeli', 0, '2025-08-15 10:53:54'),
(113, 4, 'Pesanan Dikirim!', 'Pesanan Anda #137 telah dikirim. Keterangan: Rhhrh', '/detail_pesanan.php?id=137', 'pembeli', 0, '2025-08-15 10:53:58'),
(114, 7, 'Pesanan Baru!', 'Pesanan #138 telah dibayar dan perlu Anda proses.', '/penjual/pesanan_masuk.php', 'penjual', 0, '2025-08-16 06:56:34'),
(115, 4, 'Pesanan Dikirim!', 'Pesanan Anda #138 telah dikirim. Keterangan: Produk digital telah dikirim cek status pesanan', '/detail_pesanan.php?id=138', 'pembeli', 0, '2025-08-16 06:58:11'),
(116, 4, 'Pesanan Dikirim!', 'Pesanan Anda #139 telah dikirim. Keterangan: DIANTAR KURIR LOKAL', '/detail_pesanan.php?id=139', 'pembeli', 0, '2025-08-16 14:00:49'),
(117, 4, 'Pesanan Dikirim!', 'Pesanan Anda #140 telah dikirim. Keterangan: DIANTAR KURIR LOKAL', '/detail_pesanan.php?id=140', 'pembeli', 0, '2025-08-16 22:45:01'),
(118, 4, 'Pesanan Dikirim!', 'Pesanan Anda #141 telah dikirim. Keterangan: DIANTAR KURIR LOKAL', '/detail_pesanan.php?id=141', 'pembeli', 0, '2025-08-17 10:28:35'),
(119, 4, 'Pesanan Dikirim!', 'Pesanan Anda #142 telah dikirim. Keterangan: DIANTAR KURIR LOKAL', '/detail_pesanan.php?id=142', 'pembeli', 0, '2025-08-17 10:51:30'),
(120, 4, 'Pesanan Dikirim!', 'Pesanan Anda #143 telah dikirim. Keterangan: DIANTAR KURIR LOKAL', '/detail_pesanan.php?id=143', 'pembeli', 0, '2025-08-17 11:02:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ongkos_kirim`
--

CREATE TABLE `ongkos_kirim` (
  `id` int(11) NOT NULL,
  `kurir` varchar(50) NOT NULL,
  `provinsi_asal` varchar(255) NOT NULL,
  `kota_asal` varchar(255) NOT NULL,
  `kecamatan_asal` varchar(255) NOT NULL,
  `provinsi_tujuan` varchar(255) NOT NULL,
  `kota_tujuan` varchar(255) NOT NULL,
  `kecamatan_tujuan` varchar(255) NOT NULL,
  `biaya` decimal(10,2) NOT NULL,
  `estimasi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `ongkos_kirim`
--

INSERT INTO `ongkos_kirim` (`id`, `kurir`, `provinsi_asal`, `kota_asal`, `kecamatan_asal`, `provinsi_tujuan`, `kota_tujuan`, `kecamatan_tujuan`, `biaya`, `estimasi`) VALUES
(4, 'lokal', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', 2000.00, ''),
(5, 'jnt', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'BANGKALAN', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'BURNEH', 8000.00, ''),
(6, 'jnt', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'BANGKALAN', 'JAWA TIMUR', 'SURABAYA', 'GUBENG', 9000.00, ''),
(8, 'pos', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'BANGKALAN', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'BANGKALAN', 8000.00, ''),
(9, 'pos', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'BANGKALAN', 'JAWA TIMUR', 'SURABAYA', 'GUBENG', 9000.00, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
(1, 'syam.hunter@gmail.com', 'a83e40b7f8ac9b336922e9375343226e3855652862c736f6bf67b8a9f159c8a6', '2025-08-06 06:34:59'),
(2, 'syams.huda22@gmail.com', '31428f73e6b0d0aabb7f5fd883eebd4a95ef320c74dfe8d3be19bd2d0364030f', '2025-08-06 06:35:10'),
(3, 'syams.huda22@gmail.com', '14f416617ccd6c92ee319d422e20cd0cc08ae2ccc88cf6b50b91b7dfde73f0aa', '2025-08-06 06:44:14'),
(4, 'syams.huda22@gmail.com', '259d51590b4bfa3b881dd44d5ab8cbcbdd62e98acc6ed10b4430ff95c82ed6a3', '2025-08-06 06:52:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `metode` varchar(50) NOT NULL,
  `bukti_pembayaran` varchar(255) NOT NULL,
  `tanggal_bayar` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_konfirmasi` enum('menunggu','dikonfirmasi','ditolak') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `pesanan_id`, `jumlah_bayar`, `metode`, `bukti_pembayaran`, `tanggal_bayar`, `status_konfirmasi`) VALUES
(66, 138, 9499.00, 'Transfer Bank', '68a02b9224d21-Screenshot_20250816-095803.png', '2025-08-16 06:56:18', 'dikonfirmasi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penarikan_dana`
--

CREATE TABLE `penarikan_dana` (
  `id` int(11) NOT NULL,
  `toko_id` int(11) NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') NOT NULL DEFAULT 'pending',
  `tanggal_request` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_proses` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_pengaturan` varchar(50) NOT NULL,
  `nilai_pengaturan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_pengaturan`, `nilai_pengaturan`) VALUES
(1, 'persentase_komisi', '2.5'),
(2, 'nama_website', 'Sabaku.ID'),
(3, 'website_logo', '/uploads/logo/logo_1754514368.png'),
(4, 'nama_bank', 'BCA'),
(5, 'nomor_rekening', '1851470269'),
(6, 'nama_pemilik_rekening', 'Syamsul Huda'),
(7, 'qris_image', '/uploads/qris/qris_1754223195.jpg'),
(8, 'saldo_admin', '5837.5'),
(9, 'rajaongkir_api_key', 'qPnHRGDgfe3df6ae71c9ab29Zqoseq4N');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penggunaan_voucher`
--

CREATE TABLE `penggunaan_voucher` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `jumlah_digunakan` int(11) NOT NULL DEFAULT 1,
  `tanggal_digunakan` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengikut_toko`
--

CREATE TABLE `pengikut_toko` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID Pengguna yang mengikuti',
  `toko_id` int(11) NOT NULL COMMENT 'ID Toko yang diikuti',
  `tanggal_ikuti` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengikut_toko`
--

INSERT INTO `pengikut_toko` (`id`, `user_id`, `toko_id`, `tanggal_ikuti`) VALUES
(4, 4, 7, '2025-08-14 13:04:19'),
(6, 4, 6, '2025-08-14 18:41:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesan`
--

CREATE TABLE `pesan` (
  `id` int(11) NOT NULL,
  `pengirim_id` int(11) NOT NULL,
  `penerima_id` int(11) NOT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `isi_pesan` text NOT NULL,
  `sudah_dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pesan`
--

INSERT INTO `pesan` (`id`, `pengirim_id`, `penerima_id`, `produk_id`, `isi_pesan`, `sudah_dibaca`, `created_at`) VALUES
(10, 4, 5, NULL, 'kak apakah barang ini ada?', 1, '2025-08-05 06:13:16'),
(11, 4, 7, NULL, 'hai kak', 1, '2025-08-05 06:18:33'),
(12, 4, 5, NULL, 'haloo', 1, '2025-08-05 06:49:09'),
(13, 5, 4, NULL, 'Hai', 1, '2025-08-05 07:41:08'),
(14, 5, 4, NULL, 'Hai', 1, '2025-08-05 07:41:14'),
(15, 5, 4, NULL, 'Hai', 1, '2025-08-05 07:41:41'),
(16, 4, 5, NULL, 'Hj', 1, '2025-08-05 07:44:00'),
(17, 4, 5, NULL, 'Bzjz', 1, '2025-08-05 07:46:33'),
(18, 4, 5, NULL, 'Nzjzh', 1, '2025-08-05 07:46:40'),
(19, 4, 5, NULL, 'Tahu', 1, '2025-08-05 07:47:16'),
(20, 4, 5, NULL, 'tashunnl', 1, '2025-08-05 07:48:29'),
(21, 4, 5, NULL, 'dfghs', 1, '2025-08-05 07:51:42'),
(22, 4, 5, NULL, 'Nana', 1, '2025-08-05 07:59:08'),
(23, 4, 7, NULL, 'Hay', 1, '2025-08-05 10:19:52'),
(24, 4, 7, NULL, 'Halo', 1, '2025-08-05 10:20:54'),
(25, 4, 7, NULL, 'Halo', 1, '2025-08-05 10:30:47'),
(26, 4, 5, NULL, 'Iya kak', 1, '2025-08-05 11:06:19'),
(27, 4, 5, NULL, 'Iya kak', 1, '2025-08-05 11:06:20'),
(28, 4, 5, NULL, 'Haloo', 1, '2025-08-05 11:06:27'),
(29, 4, 7, NULL, 'Hi', 1, '2025-08-05 12:09:12'),
(30, 4, 7, NULL, 'Hi', 1, '2025-08-05 12:14:38'),
(31, 4, 5, NULL, 'Haloo', 1, '2025-08-05 13:10:06'),
(32, 4, 5, NULL, 'Cek cuk', 1, '2025-08-05 13:10:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `biaya_ongkir` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kode_unik` int(3) DEFAULT NULL,
  `total_dengan_kode` decimal(10,2) DEFAULT NULL,
  `voucher_kode_digunakan` varchar(50) DEFAULT NULL,
  `nilai_diskon_voucher` decimal(10,2) DEFAULT NULL,
  `dibayar_dengan_saldo` decimal(10,2) DEFAULT 0.00,
  `admin_konfirmasi_id` int(11) DEFAULT NULL,
  `status_pesanan` enum('menunggu_pembayaran','pending','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `nomor_resi` varchar(50) DEFAULT NULL,
  `tanggal_dikirim` datetime DEFAULT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kelurahan_desa` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `opsi_pengiriman` varchar(50) DEFAULT NULL,
  `kurir` varchar(50) DEFAULT NULL,
  `kurir_lokal_id` int(11) DEFAULT NULL,
  `status_pembayaran_kurir` enum('Belum Dibayar','Sudah Dibayar') NOT NULL DEFAULT 'Belum Dibayar',
  `kurir_foto_bukti` varchar(255) DEFAULT NULL,
  `kurir_catatan` text DEFAULT NULL,
  `kurir_jumlah_cod` decimal(12,2) DEFAULT NULL,
  `gaji_kurir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `metode_pembayaran` varchar(50) NOT NULL DEFAULT 'Transfer Bank',
  `catatan_pembeli` text DEFAULT NULL,
  `email_pengiriman_digital` varchar(255) DEFAULT NULL,
  `tanggal_pesanan` timestamp NOT NULL DEFAULT current_timestamp(),
  `smkn1_is_oncampus` tinyint(1) NOT NULL DEFAULT 0,
  `smkn1_jurusan` varchar(255) DEFAULT NULL,
  `smkn1_kelas` varchar(255) DEFAULT NULL,
  `smkn1_patokan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `pembeli_id`, `total_harga`, `biaya_ongkir`, `kode_unik`, `total_dengan_kode`, `voucher_kode_digunakan`, `nilai_diskon_voucher`, `dibayar_dengan_saldo`, `admin_konfirmasi_id`, `status_pesanan`, `nomor_resi`, `tanggal_dikirim`, `nama_penerima`, `no_telepon`, `alamat_lengkap`, `provinsi`, `kota`, `kecamatan`, `kelurahan_desa`, `kode_pos`, `opsi_pengiriman`, `kurir`, `kurir_lokal_id`, `status_pembayaran_kurir`, `kurir_foto_bukti`, `kurir_catatan`, `kurir_jumlah_cod`, `gaji_kurir`, `metode_pembayaran`, `catatan_pembeli`, `email_pengiriman_digital`, `tanggal_pesanan`, `smkn1_is_oncampus`, `smkn1_jurusan`, `smkn1_kelas`, `smkn1_patokan`) VALUES
(138, 4, 8500.00, 0.00, 999, 9499.00, NULL, 0.00, 0.00, 1, 'selesai', 'Produk digital telah dikirim cek status pesanan', '2025-08-16 13:58:11', '', '', '', '', '', '', '', '', NULL, NULL, NULL, 'Belum Dibayar', NULL, NULL, NULL, 0.00, 'Transfer Bank', '', '', '2025-08-16 06:55:55', 0, NULL, NULL, NULL),
(139, 4, 45000.00, 2000.00, 0, 47000.00, NULL, 0.00, 0.00, NULL, 'selesai', 'DIANTAR KURIR LOKAL', '2025-08-16 21:00:49', 'SYAMSUL HUDA', '085150684697', 'Jl. Kh. Mattangwar Dsn. Rabesen Timur rt 0 rw 0', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', 'Mlajah', '69161', NULL, 'lokal', 1, 'Belum Dibayar', NULL, NULL, NULL, 0.00, 'COD', '', '', '2025-08-16 14:00:12', 0, NULL, NULL, NULL),
(140, 4, 45000.00, 2000.00, 0, 47000.00, NULL, 0.00, 0.00, NULL, 'selesai', 'DIANTAR KURIR LOKAL', '2025-08-17 05:45:01', 'SYAMSUL HUDA', '085150684697', 'Jl. Kh. Mattangwar Dsn. Rabesen Timur rt 0 rw 0', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', 'Mlajah', '69161', NULL, 'lokal', 2, 'Belum Dibayar', NULL, NULL, NULL, 1000.00, 'COD', '', '', '2025-08-16 22:43:57', 0, NULL, NULL, NULL),
(141, 4, 45000.00, 2000.00, 0, 47000.00, NULL, 0.00, 0.00, NULL, 'selesai', 'DIANTAR KURIR LOKAL', '2025-08-17 17:28:35', 'SYAMSUL HUDA', '085150684697', 'Jl. Kh. Mattangwar Dsn. Rabesen Timur rt 0 rw 0', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', 'Mlajah', '69161', NULL, 'lokal', 2, 'Belum Dibayar', 'bukti_141_68a1b9a898a1d.jpg', '', 47000.00, 0.00, 'COD', '', '', '2025-08-17 10:28:14', 0, NULL, NULL, NULL),
(142, 4, 45000.00, 2000.00, 0, 47000.00, NULL, 0.00, 0.00, NULL, 'selesai', 'DIANTAR KURIR LOKAL', '2025-08-17 17:51:30', 'SYAMSUL HUDA', '085150684697', 'Jl. Kh. Mattangwar Dsn. Rabesen Timur rt 0 rw 0', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', 'Mlajah', '69161', NULL, 'lokal', 2, 'Belum Dibayar', 'bukti_142_68a1b9d63c192.jpg', '', 47000.00, 0.00, 'COD', '', '', '2025-08-17 10:49:05', 0, NULL, NULL, NULL),
(143, 4, 45000.00, 2000.00, 0, 47000.00, NULL, 0.00, 0.00, NULL, 'selesai', 'DIANTAR KURIR LOKAL', '2025-08-17 18:02:56', 'SYAMSUL HUDA', '085150684697', 'Jl. Kh. Mattangwar Dsn. Rabesen Timur rt 0 rw 0', 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', 'Mlajah', '69161', NULL, 'lokal', 2, 'Belum Dibayar', 'bukti_143_68a1b9dfc02ee.jpg', '', 47000.00, 1000.00, 'COD', '', '', '2025-08-17 11:01:24', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `toko_id` int(11) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `jenis_produk` enum('fisik','digital') NOT NULL DEFAULT 'fisik',
  `nama_produk` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `harga_diskon` decimal(10,2) DEFAULT NULL,
  `promo_mulai` datetime DEFAULT NULL,
  `promo_akhir` datetime DEFAULT NULL,
  `stok` int(11) NOT NULL,
  `berat` int(11) DEFAULT 0 COMMENT 'Berat dalam gram',
  `link_digital` text DEFAULT NULL COMMENT 'Link unduhan untuk produk digital',
  `gambar_produk` varchar(255) NOT NULL,
  `gambar_produk_2` varchar(255) DEFAULT NULL,
  `gambar_produk_3` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_moderasi` enum('disetujui','ditinjau','ditolak') NOT NULL DEFAULT 'ditinjau'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `toko_id`, `kategori_id`, `jenis_produk`, `nama_produk`, `deskripsi`, `harga`, `harga_diskon`, `promo_mulai`, `promo_akhir`, `stok`, `berat`, `link_digital`, `gambar_produk`, `gambar_produk_2`, `gambar_produk_3`, `created_at`, `status_moderasi`) VALUES
(4, 6, 4, 'fisik', 'Hanasui Lips Cream Matcha', 'Hanasui lips cream matcha', 49000.00, 45000.00, '2025-07-04 18:31:00', '2025-12-04 18:31:00', 40, 1000, NULL, '68909a3d327c8-1000709872.avif', '68909a3d32fbe-1000709874.avif', '68909a3d332ee-1000709876.avif', '2025-08-04 11:32:13', 'disetujui'),
(5, 7, 13, 'digital', 'Kitab Putih Tiktok Affiliate', 'Kitab putih tiktok affiliate cocok untuk pemula yang ingin memulai affiliate', 49000.00, 8500.00, '2025-07-05 12:42:00', '2026-03-05 12:42:00', 995, 0, 'https://drive.google.com/file/d/1LWdf4zSyiq-PMZAhyWL50wUo-8YZDgrP/view?usp=drivesdk', '68919a07dd721-1000605537.png', NULL, NULL, '2025-08-05 05:43:35', 'disetujui'),
(6, 7, 1, 'fisik', 'Headset ori', 'Headset', 78000.00, 15000.00, NULL, NULL, 994, 1000, NULL, '689bbfaa8ef2c-1000716572.png', NULL, NULL, '2025-08-12 22:26:50', 'disetujui');

-- --------------------------------------------------------

--
-- Struktur dari tabel `promo_banner`
--

CREATE TABLE `promo_banner` (
  `id` int(11) NOT NULL,
  `nama_banner` varchar(255) NOT NULL,
  `gambar_banner` varchar(255) NOT NULL,
  `link_tujuan` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `promo_banner`
--

INSERT INTO `promo_banner` (`id`, `nama_banner`, `gambar_banner`, `link_tujuan`, `urutan`, `status`, `created_at`) VALUES
(3, 'Promo 8.8', '688a0b98a90de-1000703504.png', '', 1, 'aktif', '2025-07-30 12:10:00'),
(4, 'Promo 17', '688a0baad32bb-1000703505.png', '', 2, 'aktif', '2025-07-30 12:10:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_saldo_pembeli`
--

CREATE TABLE `riwayat_saldo_pembeli` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pesanan_id` int(11) DEFAULT NULL,
  `jenis_transaksi` enum('masuk','keluar') NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `saldo_sebelum` decimal(12,2) NOT NULL,
  `saldo_sesudah` decimal(12,2) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_transaksi_admin`
--

CREATE TABLE `riwayat_transaksi_admin` (
  `id` int(11) NOT NULL,
  `admin_user_id` int(11) NOT NULL,
  `jenis_transaksi` enum('komisi_masuk','penarikan_seller_selesai','penarikan_seller_ditolak','manual_input','manual_output') NOT NULL,
  `referensi_id` int(11) DEFAULT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `riwayat_transaksi_admin`
--

INSERT INTO `riwayat_transaksi_admin` (`id`, `admin_user_id`, `jenis_transaksi`, `referensi_id`, `jumlah`, `deskripsi`, `tanggal_transaksi`) VALUES
(58, 1, 'komisi_masuk', 138, 212.50, 'Komisi 2.5% dari Pesanan #138', '2025-08-16 06:59:06'),
(59, 1, 'komisi_masuk', 140, 1125.00, 'Komisi 2.5% dari Pesanan #140', '2025-08-16 22:45:49'),
(60, 1, 'komisi_masuk', 141, 1125.00, 'Komisi 2.5% dari Pesanan #141', '2025-08-17 11:14:48'),
(61, 1, 'komisi_masuk', 142, 1125.00, 'Komisi 2.5% dari Pesanan #142', '2025-08-17 11:15:34'),
(62, 1, 'komisi_masuk', 143, 1125.00, 'Komisi 2.5% dari Pesanan #143', '2025-08-17 11:15:43'),
(63, 1, 'komisi_masuk', 139, 1125.00, 'Komisi 2.5% dari Pesanan #139', '2025-08-17 11:32:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_transaksi_penjual`
--

CREATE TABLE `riwayat_transaksi_penjual` (
  `id` int(11) NOT NULL,
  `toko_id` int(11) NOT NULL,
  `pesanan_id` int(11) DEFAULT NULL,
  `penarikan_id` int(11) DEFAULT NULL,
  `jenis_transaksi` enum('masuk','keluar') NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `riwayat_transaksi_penjual`
--

INSERT INTO `riwayat_transaksi_penjual` (`id`, `toko_id`, `pesanan_id`, `penarikan_id`, `jenis_transaksi`, `jumlah`, `deskripsi`, `tanggal_transaksi`) VALUES
(100, 7, 138, NULL, 'masuk', 8287.50, 'Pendapatan Pesanan #138 (Produk: Rp 8,500, Ongkir: Rp 0, Diskon: -Rp 0, Cashback: -Rp 0, Pot. Ongkir: -Rp 0, Komisi: -Rp 212.50)', '2025-08-16 06:59:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `toko`
--

CREATE TABLE `toko` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_toko` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `logo_toko` varchar(255) DEFAULT 'default_logo.png',
  `banner_toko` varchar(255) DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_wajah` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status_verifikasi` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `rating_toko` decimal(3,2) NOT NULL DEFAULT 0.00,
  `jumlah_pengikut` int(11) NOT NULL DEFAULT 0,
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `alamat_toko` text DEFAULT NULL,
  `id_kota_asal` int(11) DEFAULT NULL COMMENT 'ID Kota dari RajaOngkir',
  `id_provinsi_asal` int(11) DEFAULT NULL COMMENT 'ID Provinsi dari RajaOngkir',
  `is_smkn1` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Toko di SMKN1, 0=Bukan',
  `nama_bank` varchar(50) DEFAULT NULL,
  `nomor_rekening` varchar(50) DEFAULT NULL,
  `nama_pemilik_rekening` varchar(100) DEFAULT NULL,
  `is_smkn1_location` tinyint(1) NOT NULL DEFAULT 0,
  `provinsi` varchar(255) DEFAULT NULL,
  `kota` varchar(255) DEFAULT NULL,
  `kecamatan` varchar(255) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `kelurahan` varchar(255) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `is_smkn1_bangkalan` tinyint(1) NOT NULL DEFAULT 0,
  `alamat_provinsi` varchar(100) DEFAULT NULL,
  `alamat_kota` varchar(100) DEFAULT NULL,
  `alamat_kecamatan` varchar(100) DEFAULT NULL,
  `alamat_kelurahan` varchar(100) DEFAULT NULL,
  `alamat_kode_pos` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `toko`
--

INSERT INTO `toko` (`id`, `user_id`, `nama_toko`, `deskripsi`, `logo_toko`, `banner_toko`, `foto_ktp`, `foto_wajah`, `is_active`, `status_verifikasi`, `rating_toko`, `jumlah_pengikut`, `saldo`, `alamat_toko`, `id_kota_asal`, `id_provinsi_asal`, `is_smkn1`, `nama_bank`, `nomor_rekening`, `nama_pemilik_rekening`, `is_smkn1_location`, `provinsi`, `kota`, `kecamatan`, `kode_pos`, `kelurahan`, `alamat_lengkap`, `is_smkn1_bangkalan`, `alamat_provinsi`, `alamat_kota`, `alamat_kecamatan`, `alamat_kelurahan`, `alamat_kode_pos`) VALUES
(6, 5, 'BC SMKN 1 Bangkalan', 'Selamat datang di Bisnis Center SMKN 1 Bangkalan, dan selamat berbelanja', '68908f2ea7d41-LOGO JEK PEELANG.png', '689e33fe92f44-Copy of Untitled Design.png', NULL, NULL, 1, 'disetujui', 5.00, 1, 0.00, 'Jl. Kenanga No. 4 Mlajah Bangkalan', NULL, NULL, 1, NULL, NULL, NULL, 1, 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'SMKN 1 BANGKALAN', '69111', NULL, 'Jl. Kenanga No. 4 Bangkalan', 0, NULL, NULL, NULL, NULL, NULL),
(7, 7, 'EBUKKU', 'Menyediakan e-book murah dan berkualitas', '68919857332e3-Screenshot_20250512-024423.png', '', NULL, NULL, 1, 'disetujui', 5.00, 1, 8287.50, 'Jl. Kenanga No 5. Mlajah Bangkalan', NULL, NULL, 0, NULL, NULL, NULL, 0, 'JAWA TIMUR', 'KABUPATEN BANGKALAN', 'BANGKALAN', '69161', NULL, 'Jl. Kenanga No. 5 Mlajah Bangkalan', 0, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `top_up`
--

CREATE TABLE `top_up` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `bukti_transfer` varchar(255) NOT NULL,
  `status` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `tanggal_request` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_proses` timestamp NULL DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ulasan`
--

CREATE TABLE `ulasan` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `komentar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `ulasan`
--

INSERT INTO `ulasan` (`id`, `produk_id`, `pembeli_id`, `pesanan_id`, `rating`, `komentar`, `created_at`) VALUES
(17, 1, 4, 74, 5, '', '2025-08-03 01:44:42'),
(18, 1, 4, 77, 5, '', '2025-08-03 09:47:33'),
(19, 1, 4, 79, 5, '', '2025-08-03 10:43:34'),
(20, 1, 4, 80, 5, '', '2025-08-03 10:59:45'),
(21, 3, 4, 85, 5, '', '2025-08-04 01:56:20'),
(22, 1, 4, 86, 5, '', '2025-08-04 02:05:18'),
(23, 3, 4, 86, 5, '', '2025-08-04 02:05:26'),
(24, 1, 4, 83, 5, '', '2025-08-04 02:09:26'),
(25, 4, 4, 100, 5, 'Bagus', '2025-08-04 11:43:47'),
(26, 4, 4, 110, 5, '', '2025-08-05 01:52:58'),
(27, 4, 4, 111, 5, '', '2025-08-05 02:40:36'),
(28, 4, 4, 112, 5, '', '2025-08-05 03:26:04'),
(29, 4, 4, 113, 5, '', '2025-08-05 05:27:40'),
(30, 5, 4, 114, 5, '', '2025-08-05 08:16:51'),
(31, 4, 4, 115, 5, '', '2025-08-06 20:07:41'),
(32, 4, 4, 130, 5, '', '2025-08-15 07:04:54'),
(33, 4, 4, 136, 5, '', '2025-08-15 19:41:39');

--
-- Trigger `ulasan`
--
DELIMITER $$
CREATE TRIGGER `update_rating_toko_after_delete` AFTER DELETE ON `ulasan` FOR EACH ROW BEGIN

    DECLARE toko_id_updated INT;

    SELECT toko_id INTO toko_id_updated FROM produk WHERE id = OLD.produk_id;

    UPDATE toko t

    SET t.rating_toko = (

        SELECT COALESCE(AVG(u.rating), 0) -- Gunakan COALESCE untuk handle jika tidak ada ulasan tersisa

        FROM ulasan u

        JOIN produk p ON u.produk_id = p.id

        WHERE p.toko_id = toko_id_updated

    )

    WHERE t.id = toko_id_updated;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_rating_toko_after_insert` AFTER INSERT ON `ulasan` FOR EACH ROW BEGIN

    DECLARE toko_id_updated INT;

    -- Cari tahu ID toko dari produk yang diberi ulasan

    SELECT toko_id INTO toko_id_updated FROM produk WHERE id = NEW.produk_id;

    -- Hitung ulang rating rata-rata untuk toko tersebut dan perbarui tabel toko

    UPDATE toko t

    SET t.rating_toko = (

        SELECT AVG(u.rating)

        FROM ulasan u

        JOIN produk p ON u.produk_id = p.id

        WHERE p.toko_id = toko_id_updated

    )

    WHERE t.id = toko_id_updated;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_rating_toko_after_update` AFTER UPDATE ON `ulasan` FOR EACH ROW BEGIN

    DECLARE toko_id_updated INT;

    SELECT toko_id INTO toko_id_updated FROM produk WHERE id = NEW.produk_id;

    UPDATE toko t

    SET t.rating_toko = (

        SELECT AVG(u.rating)

        FROM ulasan u

        JOIN produk p ON u.produk_id = p.id

        WHERE p.toko_id = toko_id_updated

    )

    WHERE t.id = toko_id_updated;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','penjual','pembeli') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `can_use_cod` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Tidak Bisa, 1=Bisa',
  `saldo` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `no_telepon`, `foto_profil`, `nama_lengkap`, `role`, `created_at`, `is_active`, `can_use_cod`, `saldo`) VALUES
(1, 'admin1', '$2y$10$9X7G/G7oeJWUF46xZwhrtOg6ub575HPSy5bNYP26YwXXN5tdvOmVW', 'admin@smeksaba.sch.id', NULL, NULL, 'Administrator', 'admin', '2025-07-29 01:29:09', 1, 0, 129300.00),
(2, 'tokobudi', '$2y$10$fWJ0hJ5b2N7aE9fB.P2m...cOvhUn4aO3H.bU6LO1s5s.YmO2i', 'budi.penjual@example.com', NULL, NULL, 'Budi Santoso', 'penjual', '2025-07-29 01:29:09', 0, 0, 0.00),
(3, 'anita_pembeli', '$2y$10$fWJ0hJ5b2N7aE9fB.P2m...cOvhUn4aO3H.bU6LO1s5s.YmO2i', 'anita.pembeli@example.com', NULL, NULL, 'Anita Putri', 'pembeli', '2025-07-29 01:29:09', 0, 0, 0.00),
(4, 's.huda21', '$2y$10$EqGoqIldJ/7QEFcp86elYe/8OstcZkgLthKDy0mESQFfjaPBvES.y', 'syams.huda22@gmail.com', '085150684697', 'user_4_1755211308.jpg', 'SYAMSUL HUDA', 'pembeli', '2025-07-29 05:01:32', 1, 1, 200.00),
(5, 's.huda22', '$2y$10$Ei1wxkhbT7E5X15FOP6VzewEAJW9chXFYIru5zFnh/m6QSl3f9gV6', 'pm.smkn1bangkalan@gmail.com', NULL, NULL, 'SYAMSUL HUDA', 'penjual', '2025-07-29 05:03:46', 1, 1, 0.00),
(6, 'admin2', '$2y$10$9X7G/G7oeJWUF46xZwhrtOg6ub575HPSy5bNYP26YwXXN5tdvOmVW', 'adminbaru@email.com', NULL, NULL, 'Admin SmeksabaShop', 'admin', '2025-07-29 05:12:50', 1, 0, 129300.00),
(7, 's.huda23', '$2y$10$XRoXUJRN2oRVU89PzSY7He3BQ4kR4Qy7pIL3xU4YNPdd3uv8jB5bW', 'syam.hunter@gmail.com', NULL, NULL, 'Syamsul Huda', 'penjual', '2025-08-03 02:39:32', 1, 0, 0.00),
(8, 's.huda24', '$2y$10$UIzHKmxw6Bu2WcEtvoQDDOmWziOSCJos6plJBy0ab44qdbwi0Ef2m', 's.huda24@gmail.com', NULL, NULL, 'Syamsul Huda', 'penjual', '2025-08-03 02:45:38', 0, 0, 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `voucher`
--

CREATE TABLE `voucher` (
  `id` int(11) NOT NULL,
  `toko_id` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `jenis_voucher` enum('diskon','cashback','gratis_ongkir') NOT NULL DEFAULT 'diskon',
  `nilai` decimal(10,2) NOT NULL,
  `min_pembelian` decimal(10,2) DEFAULT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_akhir` datetime NOT NULL,
  `jumlah_penggunaan_total` int(11) DEFAULT NULL,
  `jumlah_digunakan_saat_ini` int(11) DEFAULT 0,
  `limit_per_pembeli` int(11) DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `voucher`
--

INSERT INTO `voucher` (`id`, `toko_id`, `kode`, `jenis_voucher`, `nilai`, `min_pembelian`, `tanggal_mulai`, `tanggal_akhir`, `jumlah_penggunaan_total`, `jumlah_digunakan_saat_ini`, `limit_per_pembeli`, `is_active`, `created_at`) VALUES
(3, 6, 'PROMO8', 'diskon', 1.50, 20000.00, '2025-07-15 03:15:00', '2025-11-04 18:49:00', 100, 1, 1, 1, '2025-08-04 11:49:57'),
(4, 7, 'PROMO8.8', 'diskon', 1.50, 10000.00, '2025-07-13 14:55:00', '2025-11-13 14:55:00', 100, 0, 1, 1, '2025-08-13 07:59:43'),
(5, 6, 'CASH8', 'cashback', 200.00, 20000.00, '2025-07-15 03:27:00', '2025-10-15 03:27:00', 20, 1, 1, 1, '2025-08-14 20:27:46'),
(6, 6, 'ONGKIR8', 'gratis_ongkir', 8000.00, 120000.00, '2025-07-15 05:17:00', '2025-11-15 05:17:00', 100, 1, 1, 1, '2025-08-14 22:17:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `klaim_voucher`
--
ALTER TABLE `klaim_voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_voucher_unik` (`user_id`,`voucher_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indeks untuk tabel `kurir_lokal`
--
ALTER TABLE `kurir_lokal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_kurir` (`username_kurir`),
  ADD KEY `toko_id` (`toko_id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `ongkos_kirim`
--
ALTER TABLE `ongkos_kirim`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_index` (`email`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`);

--
-- Indeks untuk tabel `penarikan_dana`
--
ALTER TABLE `penarikan_dana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toko_id` (`toko_id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`);

--
-- Indeks untuk tabel `penggunaan_voucher`
--
ALTER TABLE `penggunaan_voucher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `pembeli_id` (`pembeli_id`);

--
-- Indeks untuk tabel `pengikut_toko`
--
ALTER TABLE `pengikut_toko`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_toko_unik` (`user_id`,`toko_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `toko_id` (`toko_id`);

--
-- Indeks untuk tabel `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengirim_id` (`pengirim_id`),
  ADD KEY `penerima_id` (`penerima_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembeli_id` (`pembeli_id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toko_id` (`toko_id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `promo_banner`
--
ALTER TABLE `promo_banner`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat_saldo_pembeli`
--
ALTER TABLE `riwayat_saldo_pembeli`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pesanan_id` (`pesanan_id`);

--
-- Indeks untuk tabel `riwayat_transaksi_admin`
--
ALTER TABLE `riwayat_transaksi_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_user_id` (`admin_user_id`),
  ADD KEY `referensi_id` (`referensi_id`);

--
-- Indeks untuk tabel `riwayat_transaksi_penjual`
--
ALTER TABLE `riwayat_transaksi_penjual`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toko_id` (`toko_id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `penarikan_id` (`penarikan_id`);

--
-- Indeks untuk tabel `toko`
--
ALTER TABLE `toko`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `top_up`
--
ALTER TABLE `top_up`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `pembeli_id` (`pembeli_id`),
  ADD KEY `pesanan_id` (`pesanan_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `toko_id` (`toko_id`);

--
-- Indeks untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pembeli_produk` (`pembeli_id`,`produk_id`),
  ADD KEY `pembeli_id` (`pembeli_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `klaim_voucher`
--
ALTER TABLE `klaim_voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kurir_lokal`
--
ALTER TABLE `kurir_lokal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT untuk tabel `ongkos_kirim`
--
ALTER TABLE `ongkos_kirim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT untuk tabel `penarikan_dana`
--
ALTER TABLE `penarikan_dana`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `penggunaan_voucher`
--
ALTER TABLE `penggunaan_voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pengikut_toko`
--
ALTER TABLE `pengikut_toko`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `promo_banner`
--
ALTER TABLE `promo_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `riwayat_saldo_pembeli`
--
ALTER TABLE `riwayat_saldo_pembeli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `riwayat_transaksi_admin`
--
ALTER TABLE `riwayat_transaksi_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `riwayat_transaksi_penjual`
--
ALTER TABLE `riwayat_transaksi_penjual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT untuk tabel `toko`
--
ALTER TABLE `toko`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `top_up`
--
ALTER TABLE `top_up`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `kurir_lokal`
--
ALTER TABLE `kurir_lokal`
  ADD CONSTRAINT `kurir_lokal_ibfk_1` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`);

--
-- Ketidakleluasaan untuk tabel `penarikan_dana`
--
ALTER TABLE `penarikan_dana`
  ADD CONSTRAINT `penarikan_dana_ibfk_1` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `pesan_ibfk_1` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesan_ibfk_2` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesan_ibfk_3` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_transaksi_penjual`
--
ALTER TABLE `riwayat_transaksi_penjual`
  ADD CONSTRAINT `riwayat_transaksi_penjual_ibfk_1` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_transaksi_penjual_ibfk_2` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_transaksi_penjual_ibfk_3` FOREIGN KEY (`penarikan_id`) REFERENCES `penarikan_dana` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `toko`
--
ALTER TABLE `toko`
  ADD CONSTRAINT `toko_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `top_up`
--
ALTER TABLE `top_up`
  ADD CONSTRAINT `top_up_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
