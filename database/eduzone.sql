-- Skema database Bimbel EduZone dengan data contoh (fiktif).
-- Akun login contoh: admin@example.com / admin123, tutor1@tutor / tutor123,
-- siswa1@siswa / siswa123 (password contoh sengaja teks biasa; otomatis diubah menjadi hash saat login pertama).
-- Kalau database eduzone sudah ada, hapus dulu agar impor tidak gagal: DROP DATABASE eduzone;

CREATE DATABASE IF NOT EXISTS `eduzone` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `eduzone`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eduzone`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `absen_ID` int(11) NOT NULL,
  `siswa_ID` int(11) NOT NULL,
  `absen_DATE` date NOT NULL,
  `absen_SESSION` varchar(20) NOT NULL,
  `absen_STATUS` varchar(20) NOT NULL,
  `tutor_ID` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`absen_ID`, `siswa_ID`, `absen_DATE`, `absen_SESSION`, `absen_STATUS`, `tutor_ID`) VALUES
(19, 1, '2026-06-07', '2', 'Excused', 1),
(20, 2, '2026-06-07', '3', 'Present', 1),
(21, 4, '2026-06-07', '1', 'Present', 1),
(22, 1, '2026-06-10', '3', 'Present', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_ID` char(2) NOT NULL,
  `admin_USER` char(30) NOT NULL,
  `admin_PASS` char(255) NOT NULL,
  `admin_NAME` char(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_ID`, `admin_USER`, `admin_PASS`, `admin_NAME`) VALUES
('1', 'admin@example.com', 'admin123', 'Admin Contoh');

-- --------------------------------------------------------

--
-- Table structure for table `bio_siswa`
--

CREATE TABLE `bio_siswa` (
  `siswa_ID` char(2) NOT NULL,
  `siswa_NAME` char(30) NOT NULL,
  `siswa_TINGKAT` char(3) NOT NULL,
  `siswa_KELAS` char(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bio_siswa`
--

INSERT INTO `bio_siswa` (`siswa_ID`, `siswa_NAME`, `siswa_TINGKAT`, `siswa_KELAS`) VALUES
('1', 'Siswa Contoh Satu', 'SMA', '12'),
('2', 'Siswa Contoh Dua', 'SMP', '8'),
('3', 'Siswa Contoh Tiga', 'SD', '3'),
('4', 'Siswa Contoh Empat', 'SD', '1');

-- --------------------------------------------------------

--
-- Table structure for table `bio_tutor`
--

CREATE TABLE `bio_tutor` (
  `tutor_ID` int(2) NOT NULL,
  `tutor_NAME` char(35) NOT NULL,
  `tutor_BOD` date NOT NULL,
  `tutor_KET` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bio_tutor`
--

INSERT INTO `bio_tutor` (`tutor_ID`, `tutor_NAME`, `tutor_BOD`, `tutor_KET`) VALUES
(1, 'Tutor Contoh Satu', '1995-01-01', 'Pengajar Matematika dan IPA'),
(2, 'Tutor Contoh Dua', '1996-02-02', ''),
(3, 'Tutor Contoh Tiga', '1997-03-03', '');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `admin_ID` int(2) NOT NULL,
  `tutor_ID` char(2) NOT NULL,
  `tanggal` date NOT NULL,
  `sesi` int(4) NOT NULL,
  `tingkat` char(3) NOT NULL,
  `kelas` char(3) NOT NULL,
  `mapel` char(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`admin_ID`, `tutor_ID`, `tanggal`, `sesi`, `tingkat`, `kelas`, `mapel`) VALUES
(1, '2', '2026-05-29', 3, 'SMP', '7', 'Matematika'),
(1, '3', '2026-06-01', 2, 'SMP', '7', 'Matematika'),
(1, '3', '2026-06-01', 3, 'SMP', '8', 'IPA'),
(1, '1', '2026-06-07', 1, 'SD', '1', 'IPA'),
(1, '1', '2026-06-07', 2, 'SMA', '12', 'Matematika'),
(1, '1', '2026-06-07', 3, 'SMP', '8', 'Aljabar'),
(1, '1', '2026-06-10', 3, 'SMA', '12', 'FISIKA');

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `materi_ID` int(11) NOT NULL,
  `tutor_ID` int(2) NOT NULL,
  `materi_TITLE` varchar(255) NOT NULL,
  `materi_DESC` text DEFAULT NULL,
  `materi_TINGKAT` varchar(10) NOT NULL,
  `materi_KELAS` varchar(10) NOT NULL,
  `materi_MAPEL` varchar(100) NOT NULL,
  `video_FILENAME` varchar(255) DEFAULT NULL,
  `video_PATH` varchar(255) DEFAULT NULL,
  `pdf_FILENAME` varchar(255) DEFAULT NULL,
  `pdf_PATH` varchar(255) DEFAULT NULL,
  `created_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`materi_ID`, `tutor_ID`, `materi_TITLE`, `materi_DESC`, `materi_TINGKAT`, `materi_KELAS`, `materi_MAPEL`, `video_FILENAME`, `video_PATH`, `pdf_FILENAME`, `pdf_PATH`, `created_AT`) VALUES
(1, 1, 'Contoh Materi Limit Fungsi', 'Materi contoh tanpa berkas', 'SMA', '12', 'Matematika', NULL, NULL, NULL, NULL, '2026-06-14 18:07:02');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `pembayaran_ID` char(8) NOT NULL,
  `siswa_ID` char(2) NOT NULL,
  `pembayaran_STATUS` char(8) NOT NULL,
  `pembayaran_TENGGAT` date NOT NULL,
  `pembayaran_TANGGAL` date DEFAULT NULL,
  `pembayaran_NOMINAL` char(30) DEFAULT NULL,
  `kode_va` char(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`pembayaran_ID`, `siswa_ID`, `pembayaran_STATUS`, `pembayaran_TENGGAT`, `pembayaran_TANGGAL`, `pembayaran_NOMINAL`, `kode_va`) VALUES
('PAY-0001', '1', 'NOT PAID', '2026-06-15', NULL, '1500000', '9000000001'),
('PAY-0002', '2', 'PAID', '2026-05-02', '2026-05-31', '1200000', '9000000002'),
('PAY-0003', '3', 'PAID', '2026-06-08', '2026-06-07', '1500000', '9000000003'),
('PAY-0004', '4', 'PAID', '2025-10-16', '2025-10-15', '500000', '9000000004');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `siswa_ID` char(2) NOT NULL,
  `siswa_USER` char(30) NOT NULL,
  `siswa_PASS` char(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`siswa_ID`, `siswa_USER`, `siswa_PASS`) VALUES
('1', 'siswa1@siswa', 'siswa123'),
('2', 'siswa2@siswa', 'siswa123'),
('3', 'siswa3@siswa', 'siswa123'),
('4', 'siswa4@siswa', 'siswa123');

-- --------------------------------------------------------

--
-- Table structure for table `tutor`
--

CREATE TABLE `tutor` (
  `tutor_ID` char(2) NOT NULL,
  `tutor_USER` char(30) NOT NULL,
  `tutor_PASS` char(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutor`
--

INSERT INTO `tutor` (`tutor_ID`, `tutor_USER`, `tutor_PASS`) VALUES
('1', 'tutor1@tutor', 'tutor123'),
('2', 'tutor2@tutor', 'tutor123'),
('3', 'tutor3@tutor', 'tutor123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`absen_ID`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_ID`);

--
-- Indexes for table `bio_siswa`
--
ALTER TABLE `bio_siswa`
  ADD PRIMARY KEY (`siswa_ID`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`tanggal`,`sesi`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`materi_ID`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`pembayaran_ID`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`siswa_ID`);

--
-- Indexes for table `tutor`
--
ALTER TABLE `tutor`
  ADD PRIMARY KEY (`tutor_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `absen_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `materi_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
