-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Bulan Mei 2026 pada 15.16
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `speakon_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_lockouts`
--

CREATE TABLE `account_lockouts` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `locked_at` datetime NOT NULL DEFAULT current_timestamp(),
  `locked_until` datetime NOT NULL,
  `reason` varchar(100) NOT NULL DEFAULT 'too_many_attempts'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `user_role` varchar(20) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_role`, `action_type`, `entity_type`, `entity_id`, `metadata`, `ip_address`, `created_at`) VALUES
(1, NULL, NULL, 'login_failed', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-08 14:23:11'),
(2, NULL, NULL, 'login_failed', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-08 14:24:35'),
(3, NULL, NULL, 'login_failed', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-08 14:24:37'),
(4, NULL, NULL, 'login_failed', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-08 14:26:58'),
(5, '00000000-0000-0000-0000-000000000001', 'superadmin', 'login', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-08 14:34:38'),
(6, '00000000-0000-0000-0000-000000000001', 'superadmin', 'login', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-08 14:36:19'),
(7, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 14:43:06'),
(8, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 14:46:00'),
(9, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 14:46:21'),
(10, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 14:46:42'),
(11, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 14:55:36'),
(12, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 15:15:46'),
(13, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:15:49'),
(14, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 15:16:29'),
(15, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:18:37'),
(16, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:18:59'),
(17, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:19:45'),
(18, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:21:00'),
(19, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:21:24'),
(20, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:22:05'),
(21, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:22:09'),
(22, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:22:13'),
(23, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 15:22:28'),
(24, '00000000-0000-0000-0000-000000000003', 'siswa', 'recording_uploaded', 'recording', '8e4262d6-2aca-4a17-8ac7-ec57e105de18', '{\"level_id\":\"1\",\"file_size\":150026}', '::1', '2026-05-08 15:25:31'),
(25, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:25:57'),
(26, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:26:11'),
(27, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:30:04'),
(28, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:30:45'),
(29, '00000000-0000-0000-0000-000000000002', 'dosen', 'level_unlocked', 'level', '2', '{\"student_id\":\"00000000-0000-0000-0000-000000000003\",\"triggered_by_feedback\":\"7f3850b1-d23d-49de-9893-e2f79b6562c1\"}', NULL, '2026-05-08 15:31:37'),
(30, NULL, NULL, 'notification_sent', 'notification', 'e062caf7-afed-4cd4-b937-5a89274d0049', '{\"student_id\":\"00000000-0000-0000-0000-000000000003\",\"feedback_id\":\"7f3850b1-d23d-49de-9893-e2f79b6562c1\"}', NULL, '2026-05-08 15:31:37'),
(31, '00000000-0000-0000-0000-000000000002', 'dosen', 'feedback_given', 'feedback', '7f3850b1-d23d-49de-9893-e2f79b6562c1', '{\"recording_id\":\"8e4262d6-2aca-4a17-8ac7-ec57e105de18\",\"pass_status\":\"lulus\"}', '::1', '2026-05-08 15:31:37'),
(32, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:31:43'),
(33, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 15:31:49'),
(34, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:42:20'),
(35, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:42:26'),
(36, '00000000-0000-0000-0000-000000000001', 'superadmin', 'login', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-08 15:45:30'),
(37, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:47:05'),
(38, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 15:47:11'),
(39, '00000000-0000-0000-0000-000000000003', 'siswa', 'recording_uploaded', 'recording', 'f5d35971-ef7c-4c93-a0e1-960d8d0c6939', '{\"level_id\":\"1\",\"file_size\":192530}', '::1', '2026-05-08 15:50:05'),
(40, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:50:42'),
(41, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:50:48'),
(42, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 15:51:11'),
(43, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:53:52'),
(44, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 15:54:20'),
(45, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 15:59:01'),
(46, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 16:05:07'),
(47, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-08 16:05:11'),
(48, '00000000-0000-0000-0000-000000000003', 'siswa', 'recording_uploaded', 'recording', '9493f418-83bf-404c-b70c-30c1e5530884', '{\"level_id\":\"1\",\"file_size\":53426}', '::1', '2026-05-08 16:05:48'),
(49, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-08 16:06:07'),
(50, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-08 16:06:12'),
(51, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:30:27'),
(52, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-09 09:30:35'),
(53, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:30:49'),
(54, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 09:31:24'),
(55, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:32:18'),
(56, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-09 09:32:26'),
(57, '00000000-0000-0000-0000-000000000002', 'dosen', 'level_unlocked', 'level', '2', '{\"student_id\":\"00000000-0000-0000-0000-000000000003\",\"triggered_by_feedback\":\"30a5c91f-1f93-4172-ab3b-90a754a0c140\"}', NULL, '2026-05-09 09:32:39'),
(58, NULL, NULL, 'notification_sent', 'notification', '706b2f73-3e94-4ab0-b8e4-745ad6d4feef', '{\"student_id\":\"00000000-0000-0000-0000-000000000003\",\"feedback_id\":\"30a5c91f-1f93-4172-ab3b-90a754a0c140\"}', NULL, '2026-05-09 09:32:40'),
(59, '00000000-0000-0000-0000-000000000002', 'dosen', 'feedback_given', 'feedback', '30a5c91f-1f93-4172-ab3b-90a754a0c140', '{\"recording_id\":\"9493f418-83bf-404c-b70c-30c1e5530884\",\"pass_status\":\"lulus\"}', '::1', '2026-05-09 09:32:40'),
(60, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:33:15'),
(61, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 09:33:21'),
(62, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:34:28'),
(63, '00000000-0000-0000-0000-000000000001', 'superadmin', 'login', 'session', NULL, '{\"email\":\"admin@speakon.id\"}', '::1', '2026-05-09 09:35:02'),
(64, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:35:52'),
(65, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 09:46:38'),
(66, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:58:46'),
(67, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 09:58:52'),
(68, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 09:59:18'),
(69, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 09:59:51'),
(70, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:08:19'),
(71, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-09 10:08:25'),
(72, '00000000-0000-0000-0000-000000000002', 'dosen', 'feedback_updated', 'feedback', '30a5c91f-1f93-4172-ab3b-90a754a0c140', '{\"pass_status\":\"lulus\"}', '::1', '2026-05-09 10:08:38'),
(73, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:08:42'),
(74, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 10:08:46'),
(75, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:12:00'),
(76, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 10:12:06'),
(77, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:12:10'),
(78, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-09 10:12:15'),
(79, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:12:25'),
(80, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 10:12:29'),
(81, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:24:04'),
(82, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 10:27:32'),
(83, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:41:36'),
(84, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 10:47:26'),
(85, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:57:56'),
(86, '00000000-0000-0000-0000-000000000003', 'siswa', 'login', 'session', NULL, '{\"email\":\"siswa@speakon.id\"}', '::1', '2026-05-09 10:58:07'),
(87, '00000000-0000-0000-0000-000000000003', 'siswa', 'recording_uploaded', 'recording', '60ca5925-7480-4e4c-85f0-b114c8a1fa53', '{\"level_id\":\"1\",\"file_size\":65984}', '::1', '2026-05-09 10:59:08'),
(88, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 10:59:16'),
(89, '00000000-0000-0000-0000-000000000002', 'dosen', 'login', 'session', NULL, '{\"email\":\"dosen@speakon.id\"}', '::1', '2026-05-09 10:59:22'),
(90, NULL, NULL, 'logout', 'session', NULL, '{\"email\":\"\"}', '::1', '2026-05-09 11:21:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `feedback`
--

CREATE TABLE `feedback` (
  `id` char(36) NOT NULL,
  `recording_id` char(36) NOT NULL,
  `dosen_id` char(36) NOT NULL,
  `comment` text NOT NULL,
  `pass_status` enum('lulus','tidak_lulus') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `levels`
--

CREATE TABLE `levels` (
  `id` tinyint(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `order_index` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `levels`
--

INSERT INTO `levels` (`id`, `name`, `description`, `order_index`) VALUES
(1, 'Basic Pronunciation', 'Latihan pengucapan kata dasar bahasa Inggris dengan benar', 1),
(2, 'Intonation', 'Latihan intonasi dan ritme dalam kalimat bahasa Inggris', 2),
(3, 'Guided Dialogue', 'Percakapan terbimbing dengan skrip dan panduan konteks', 3),
(4, 'Scenario-based Roleplay', 'Simulasi situasi nyata seperti wawancara dan presentasi', 4),
(5, 'Independent Speaking', 'Monolog bebas tanpa panduan untuk mengekspresikan ide', 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `level_materials`
--

CREATE TABLE `level_materials` (
  `id` char(36) NOT NULL,
  `level_id` tinyint(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `order_index` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `attempted_at`, `success`) VALUES
('02c7a859-7e06-44f0-85c2-e3c46625fa69', 'siswa@speakon.id', '::1', '2026-05-09 09:59:51', 1),
('073b5416-f34d-49c6-b087-c56edc8fd679', 'dosen@speakon.id', '::1', '2026-05-08 15:21:23', 1),
('0a6712f5-1025-4320-bd1d-d888b6ac82f3', 'siswa@speakon.id', '::1', '2026-05-09 10:12:29', 1),
('1b1c2969-71c0-45db-a369-b19196c7e546', 'admin@speakon.id', '::1', '2026-05-08 14:23:11', 0),
('24f4a818-447a-4cb9-afc2-b92d4f13446c', 'siswa@speakon.id', '::1', '2026-05-09 10:58:07', 1),
('37eed28f-83f8-40d7-a020-3d38882d1552', 'siswa@speakon.id', '::1', '2026-05-08 15:22:27', 1),
('38b65b8d-7c02-4f70-aecb-45d62e20e316', 'siswa@speakon.id', '::1', '2026-05-08 16:05:11', 1),
('3ed06f3f-e471-427f-9d16-859596dd2d03', 'admin@speakon.id', '::1', '2026-05-08 14:26:58', 0),
('409a59a7-eda2-491e-9ce2-e05cdd642b6c', 'dosen@speakon.id', '::1', '2026-05-08 16:06:12', 1),
('44eb041b-784e-45c3-bdb8-b8efe653af7d', 'admin@speakon.id', '::1', '2026-05-08 14:34:38', 1),
('4d004d88-9cb7-4dd6-9bab-f302bd5d2105', 'dosen@speakon.id', '::1', '2026-05-08 15:22:09', 1),
('4d3ad1cb-3e27-4c02-bd06-ff85005ff2a3', 'dosen@speakon.id', '::1', '2026-05-08 15:50:48', 1),
('4f4124a1-62d3-4919-aded-21260cfccd01', 'siswa@speakon.id', '::1', '2026-05-08 15:31:49', 1),
('50a6b691-3eb7-4a02-b402-37603205a21a', 'admin@speakon.id', '::1', '2026-05-09 09:35:01', 1),
('52fd34f9-43da-4150-bcd0-7ed572d67f8c', 'siswa@speakon.id', '::1', '2026-05-09 09:46:38', 1),
('5dcf6333-42ca-495e-b00c-7a7a172641f6', 'siswa@speakon.id', '::1', '2026-05-09 10:08:46', 1),
('5f6db999-84d4-428c-82ff-df3be3557a46', 'dosen@speakon.id', '::1', '2026-05-08 15:54:20', 1),
('6d05d39e-d09a-4a6f-9b90-14ac5c71c8b5', 'dosen@speakon.id', '::1', '2026-05-08 15:15:49', 1),
('73a6b2dd-b0b3-42e8-bd44-99ed948f1768', 'admin@speakon.id', '::1', '2026-05-08 14:24:37', 0),
('774b08e8-fe86-4312-bd1e-283dcdb61e20', 'admin@speakon.id', '::1', '2026-05-08 14:30:08', 1),
('795bd189-fa76-4d59-acfa-cdcd83880ab9', 'siswa@speakon.id', '::1', '2026-05-08 15:47:11', 1),
('845dd218-7890-4c6c-bfec-fa4745a8e719', 'admin@speakon.id', '::1', '2026-05-08 14:30:10', 1),
('8b4c3dc0-150a-4b66-947e-e605aec0a290', 'dosen@speakon.id', '::1', '2026-05-08 15:21:00', 1),
('8e50bfb4-8a7a-4b6c-aa85-2d72f2a0a348', 'dosen@speakon.id', '::1', '2026-05-08 15:30:04', 1),
('900d8316-afe9-4dd5-9fc8-77d477c99e0c', 'siswa@speakon.id', '::1', '2026-05-09 10:47:26', 1),
('9331485f-bc0f-4664-9bd6-6cbfe5c2c50c', 'dosen@speakon.id', '::1', '2026-05-09 10:12:15', 1),
('9750bb17-49cf-46cd-b6ff-d6a956160af1', 'dosen@speakon.id', '::1', '2026-05-08 15:30:45', 1),
('9ac57e45-e397-42e3-8f90-e16b1ea857e3', 'siswa@speakon.id', '::1', '2026-05-09 10:12:06', 1),
('9cb3dd22-408e-4126-9c1d-53858d2d5f5a', 'siswa@speakon.id', '::1', '2026-05-08 15:16:28', 1),
('a31499bc-286e-46ae-9535-a026ecf5a5f7', 'siswa@speakon.id', '::1', '2026-05-08 15:15:46', 1),
('a3cf6a85-b640-4969-88cb-af19c7b6e3a7', 'siswa@speakon.id', '::1', '2026-05-08 14:46:41', 1),
('a5a5bb82-674a-4d98-8ee2-7ec049b314ac', 'dosen@speakon.id', '::1', '2026-05-08 15:18:58', 1),
('b70a719a-c0a0-4f84-bdaf-bbf2261a1d05', 'dosen@speakon.id', '::1', '2026-05-08 15:42:26', 1),
('bfd9667d-b702-453d-8886-edd30d1c9490', 'dosen@speakon.id', '::1', '2026-05-08 15:53:52', 1),
('c02bc17e-89e0-4424-9d04-86e6a77974d8', 'dosen@speakon.id', '::1', '2026-05-08 15:26:11', 1),
('c0eb6543-f587-4d53-a953-8373e86488bc', 'admin@speakon.id', '::1', '2026-05-08 14:31:41', 1),
('c44fddc8-f46a-43ba-8c32-3d36f2dfc73a', 'dosen@speakon.id', '::1', '2026-05-09 10:59:22', 1),
('cb92532a-3915-4f48-945a-26cafbdc4d28', 'dosen@speakon.id', '::1', '2026-05-09 09:30:35', 1),
('ce53c7c3-d79a-4397-b130-59b60f8616c5', 'admin@speakon.id', '::1', '2026-05-08 14:36:19', 1),
('d710da38-ec9c-4139-9b2f-196e84f9b4c4', 'dosen@speakon.id', '::1', '2026-05-09 09:32:26', 1),
('d78b5f8f-adaf-4131-b322-13b75334ed49', 'siswa@speakon.id', '::1', '2026-05-09 09:33:21', 1),
('d791493e-cf66-4344-b715-c475dc36bf44', 'dosen@speakon.id', '::1', '2026-05-08 15:19:45', 1),
('d796883b-f61f-4847-8108-926aee80df94', 'siswa@speakon.id', '::1', '2026-05-09 10:27:32', 1),
('ddb947f0-9e13-44ca-93be-0133745a0d6c', 'dosen@speakon.id', '::1', '2026-05-09 10:08:25', 1),
('e3487ad3-9a75-414f-8380-fcbb6a812cf6', 'dosen@speakon.id', '::1', '2026-05-08 14:45:59', 1),
('e3fb286d-11c0-49ed-ad21-de669dac010a', 'siswa@speakon.id', '::1', '2026-05-09 09:58:52', 1),
('ea159706-306a-46e7-8790-15401df78a71', 'siswa@speakon.id', '::1', '2026-05-09 09:31:24', 1),
('eb28932f-dbb4-47b2-bfd1-5a4ceb3a6516', 'admin@speakon.id', '::1', '2026-05-08 14:24:35', 0),
('faddeb90-17d7-4d85-9c98-fe4b7efe1605', 'siswa@speakon.id', '::1', '2026-05-08 15:59:01', 1),
('fd14af0a-1eb2-444a-9c10-71e340e6b8cb', 'admin@speakon.id', '::1', '2026-05-08 15:45:30', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `student_id` char(36) NOT NULL,
  `feedback_id` char(36) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `recordings`
--

CREATE TABLE `recordings` (
  `id` char(36) NOT NULL,
  `student_id` char(36) NOT NULL,
  `level_id` tinyint(4) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size_bytes` int(11) NOT NULL,
  `duration_seconds` decimal(6,2) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_current` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `recordings`
--

INSERT INTO `recordings` (`id`, `student_id`, `level_id`, `file_path`, `file_size_bytes`, `duration_seconds`, `uploaded_at`, `is_current`) VALUES
('60ca5925-7480-4e4c-85f0-b114c8a1fa53', '00000000-0000-0000-0000-000000000003', 1, 'uploads/recordings/00000000-0000-0000-0000-000000000003_1_1778324348.webm', 65984, NULL, '2026-05-09 10:59:08', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `revoked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `created_at`, `revoked_at`) VALUES
('046fe963-5e8d-482b-b037-27c05b6f4c70', '00000000-0000-0000-0000-000000000001', '290f0e2b0f5bca48f97cfdadc0d5e5f72a6e1657754e457c522867dccd6e05a1', '2026-05-16 11:35:02', '2026-05-09 09:35:02', '2026-05-09 09:35:52'),
('05222ab5-9802-43f4-8c27-69a6392ecaa4', '00000000-0000-0000-0000-000000000001', '463aa59752ec03b8fb06e8946c9754e4f6aca646a39ebf7d1c530ba53de9cb79', '2026-05-15 17:45:30', '2026-05-08 15:45:30', NULL),
('0b6dd081-4794-46cd-9a14-a30e6fe60eb7', '00000000-0000-0000-0000-000000000003', '0300467ba7fff8aa13bd6d74c79437634995b44548987510b1eef09684d82e98', '2026-05-16 12:58:07', '2026-05-09 10:58:07', '2026-05-09 10:59:16'),
('0da30881-b3ff-46de-904d-0501594e675e', '00000000-0000-0000-0000-000000000002', 'f930134dc6f5bf275141fda4d70bf2c6b7648c014352bc4c20614d0f03c1780c', '2026-05-15 17:21:00', '2026-05-08 15:21:00', NULL),
('1ce33e53-26d6-4a6d-8809-b6bbe0fe38ca', '00000000-0000-0000-0000-000000000003', '93d35289926cf76796288a5494abc0400731e63b0cf06e617a707f94720da0af', '2026-05-16 11:58:52', '2026-05-09 09:58:52', '2026-05-09 09:59:18'),
('1db4b76f-25f5-477c-8500-2ba39b3af3fe', '00000000-0000-0000-0000-000000000003', 'b0063b5eb14e0c4f293488ee876d189022bcd19b3426ecc7491882e5f7d8a4d7', '2026-05-15 17:47:11', '2026-05-08 15:47:11', '2026-05-08 15:50:42'),
('324dd886-9309-4ff1-a5db-0e83d55a98d2', '00000000-0000-0000-0000-000000000002', 'bd0c0c4760934387a8fce5bfb48972becb3f323feca538fe2b4ffe2a2652b237', '2026-05-15 17:15:49', '2026-05-08 15:15:49', NULL),
('3b700c50-c109-4e35-b3b3-a4c13aa07d4b', '00000000-0000-0000-0000-000000000002', '5ebd9fbe31e860c2d59dbf9363738cd50c810c0267b5a3a142dab22e30fbcaa8', '2026-05-16 12:59:22', '2026-05-09 10:59:22', '2026-05-09 11:21:26'),
('3fca5463-6988-4c08-bc79-fd4f693d716f', '00000000-0000-0000-0000-000000000001', '813bf32f85921d635aaf5431febcd87831811f9d9207a5ed2771f70c5c742e12', '2026-05-15 16:36:19', '2026-05-08 14:36:19', '2026-05-08 14:43:06'),
('4022ebb8-327b-4e49-b767-8efafad157ca', '00000000-0000-0000-0000-000000000002', '11139ec5a87cc4c8f308d03ad5c5b3d4f0ac4003c1007a37cb812a47a31cb2d4', '2026-05-15 16:46:00', '2026-05-08 14:46:00', '2026-05-08 14:46:21'),
('41d0d7b2-f6bc-4ce4-b2f9-730dbe97bf4c', '00000000-0000-0000-0000-000000000002', '7daec913be113a7a934976dba0538fa3513b6d5aac20fa156e4867a863a8a546', '2026-05-15 17:19:45', '2026-05-08 15:19:45', NULL),
('4468a5ec-2a6b-4682-a3b6-98d05709470a', '00000000-0000-0000-0000-000000000003', '699a820086f3e978dad14c854bb7c4639bfb1ab36faa355c187efcc63d748526', '2026-05-16 12:08:46', '2026-05-09 10:08:46', '2026-05-09 10:12:00'),
('48da96f1-3dad-4403-9118-ea4e928bee73', '00000000-0000-0000-0000-000000000002', 'd539942759920332874af1d8face255b88c235db892ef103fb890305af31e687', '2026-05-16 12:12:15', '2026-05-09 10:12:15', '2026-05-09 10:12:25'),
('53725a89-7ac9-48e0-b8de-cef6ee562d8c', '00000000-0000-0000-0000-000000000003', '47b0a349887a49eca37660a01d24498bc4d185aadf868a805196511df4d14fc2', '2026-05-16 12:47:26', '2026-05-09 10:47:26', '2026-05-09 10:57:56'),
('5411890a-cb1a-45f5-ab2e-0e998525af6a', '00000000-0000-0000-0000-000000000003', '2f520ef39430f3e27bfaf5768a2f90de02e014981631e8b664ea3e61cd506804', '2026-05-15 17:16:28', '2026-05-08 15:16:28', '2026-05-08 15:18:37'),
('557cb366-a68c-4d29-af3a-24dfb8bf44e5', '00000000-0000-0000-0000-000000000003', '158d8ca162ed3a2707fc9549ba251854cb8665e1c443b23fbc868c4c62c9ce6c', '2026-05-15 17:22:27', '2026-05-08 15:22:27', '2026-05-08 15:25:57'),
('5cbad39a-612d-4d08-902a-4a1396fdd4c8', '00000000-0000-0000-0000-000000000003', '8cc376b6af65c312a0118c7fec6df9a84b971b1cf9a683dada3b8227e9fcd8c4', '2026-05-15 16:46:41', '2026-05-08 14:46:41', '2026-05-08 14:55:36'),
('6afb1fbc-a61e-4ac6-bece-5f7fa41bd13b', '00000000-0000-0000-0000-000000000002', '194b51cd1e6497c8607dcc84806a68144ae92167671ff4fb30eb0399c44a2c6a', '2026-05-15 17:42:26', '2026-05-08 15:42:26', '2026-05-08 15:47:05'),
('73651382-d831-43ae-b93f-6b72bee3cb6e', '00000000-0000-0000-0000-000000000002', '4184e3cb57408e3399d4b72b0fb5e128a76bca1007ca217257ad85f6586055e6', '2026-05-15 17:30:04', '2026-05-08 15:30:04', NULL),
('7b97936f-72d7-493e-8af4-c7a74da1bbf7', '00000000-0000-0000-0000-000000000003', '0b1a73fae9fdf14e6eb71124a6849234f6a7fc500c9725c42a0060c4c4cb894b', '2026-05-16 12:12:29', '2026-05-09 10:12:29', '2026-05-09 10:24:04'),
('8cf3b1ca-6b76-4196-be4a-d1ecd90ddd51', '00000000-0000-0000-0000-000000000003', '7ec9c69365ced2fe094092cbc5a73189efbe8cb9aaf4b4b1b8aea4730c044c50', '2026-05-16 11:31:24', '2026-05-09 09:31:24', '2026-05-09 09:32:18'),
('902f8c53-2139-4beb-be92-e134993eeeea', '00000000-0000-0000-0000-000000000002', '17ea61bd2a45ebd19bc5bbbf13a3d6f6b21e0e0a6b69c831ff3f38e597cac05f', '2026-05-15 17:26:11', '2026-05-08 15:26:11', '2026-05-08 15:31:43'),
('90ab176e-b07e-4ea8-b892-26d744f160ed', '00000000-0000-0000-0000-000000000002', '480efa8a876b77618809fee8db10825c4d589d02ed26365ff66c3de93b144d7f', '2026-05-15 17:53:52', '2026-05-08 15:53:52', NULL),
('997affd0-b31c-4a94-a57e-e0805f056933', '00000000-0000-0000-0000-000000000003', '8cd9b2604389069e37ff1a67d26b48110a71db751b4e67c2e3c77f3f638ac130', '2026-05-15 17:31:49', '2026-05-08 15:31:49', '2026-05-08 15:42:20'),
('9ab8f85d-5ec1-46ef-b27d-f96bcd3ae508', '00000000-0000-0000-0000-000000000002', '84d084f69e7a66ce555eec6cc5c7d59c0f3f617ad189a37ce508e6600a1a0275', '2026-05-15 17:21:23', '2026-05-08 15:21:23', NULL),
('a54476e6-62ea-4782-b1f9-aa238775c86c', '00000000-0000-0000-0000-000000000002', '1ae4e5df550bbf31f70fdad99b7c31e168e3d23ffabba0066cc8af093b7dfa01', '2026-05-15 17:50:48', '2026-05-08 15:50:48', '2026-05-08 15:51:11'),
('afc2762c-8266-4e21-9b52-540c7e71f7d4', '00000000-0000-0000-0000-000000000003', '913087d60bddc0d6bd01d8738be7f70102d4ebb02140e630c4f21bd578049bd0', '2026-05-16 11:46:38', '2026-05-09 09:46:38', '2026-05-09 09:58:46'),
('b1651337-9cbe-4559-80d6-1ba55460406e', '00000000-0000-0000-0000-000000000003', 'a5174c682c6c81a86eabfd8c592b22ea810e3c94707d530777b60f570b5606b4', '2026-05-15 17:59:01', '2026-05-08 15:59:01', '2026-05-08 16:05:07'),
('b46cc47d-6249-4954-ab42-532cc11aa152', '00000000-0000-0000-0000-000000000003', '4c01ae8a9c06393bda023430e22097f28976c9ca2cb0507fd0377cc51751c293', '2026-05-15 17:15:46', '2026-05-08 15:15:46', NULL),
('b660f113-af03-4eab-95e4-41fcfb7084f9', '00000000-0000-0000-0000-000000000003', '47cba6605ba73ce67d874c3256e65e5390fccf4dcb5573e0d4057634240ba190', '2026-05-16 12:12:06', '2026-05-09 10:12:06', '2026-05-09 10:12:10'),
('b8e143f6-5992-4449-bdf7-122b2b98db2f', '00000000-0000-0000-0000-000000000002', '97ae64c36138b69f2959b56b6ada913423fa408471c77d37bfec16554c043f4a', '2026-05-15 18:06:12', '2026-05-08 16:06:12', '2026-05-09 09:30:27'),
('b9c69b25-12b9-4f6b-a82c-4d2b0538d94f', '00000000-0000-0000-0000-000000000003', '3fbcea5b3b708eded8c821dbe887293973854c6add804d6a7ba4d1d1fbb3b613', '2026-05-16 11:59:51', '2026-05-09 09:59:51', '2026-05-09 10:08:19'),
('c1f08bd2-4a7b-4fd3-b9a6-b26507ea2324', '00000000-0000-0000-0000-000000000002', '2ce72ff5624dbbf02e016d8abd6c347b92d21f03b204da40336e8e8e67e3cc54', '2026-05-15 17:22:09', '2026-05-08 15:22:09', '2026-05-08 15:22:13'),
('ca1a30f2-99e5-4afe-85ef-0ad56c6b85e8', '00000000-0000-0000-0000-000000000002', 'f653270df812f05d8cfd2cc1f42d4b0e22c4693423d1542d1732ef66fb00e729', '2026-05-16 11:32:26', '2026-05-09 09:32:26', '2026-05-09 09:33:15'),
('ca39be06-d88a-4a44-9310-779f92406506', '00000000-0000-0000-0000-000000000002', 'e693a4d5145c326d38eeb2c7fd8b5b42f3340e8fae45f42c0882fefbf4dcb723', '2026-05-15 17:54:20', '2026-05-08 15:54:20', NULL),
('d0dd38e5-1274-4bda-aeb8-48eba94a160b', '00000000-0000-0000-0000-000000000003', 'a79c33c34dbb1a4aa0760c91bb71536d086b68f4e7192c43f0642186931b583c', '2026-05-15 18:05:11', '2026-05-08 16:05:11', '2026-05-08 16:06:07'),
('d19302e6-7ffc-43fb-87e5-f0edff75cb5d', '00000000-0000-0000-0000-000000000003', 'd55d5e04f331cf8d0320952a0757834dd91630cbd3e91d808f8c8e31ee3e6902', '2026-05-16 11:33:21', '2026-05-09 09:33:21', '2026-05-09 09:34:28'),
('d8796c6e-4316-4e73-a829-c0ce83e7ab35', '00000000-0000-0000-0000-000000000002', '8a9437083b4b59df3b7264b49622ac7f7916addc42db4f1deaaa4f8f0e0b90f8', '2026-05-16 11:30:35', '2026-05-09 09:30:35', '2026-05-09 09:30:49'),
('daeaada8-a85c-428b-bca2-2126f6a4259a', '00000000-0000-0000-0000-000000000002', 'e3d518bb7f1413997e99a4e448a9a67be78cc2c3c4e7fe9400cdf6bef7664ad9', '2026-05-16 12:08:25', '2026-05-09 10:08:25', '2026-05-09 10:08:42'),
('dea67060-992c-4e58-9a73-b4ce0d9febb2', '00000000-0000-0000-0000-000000000002', '8dfce26d1df5c90da48d05ae4f84c8588d7698565b06cbb3355f5b65d40a51a4', '2026-05-15 17:30:45', '2026-05-08 15:30:45', NULL),
('ecfb0e32-3fc2-42d0-87ec-ebca80d7868d', '00000000-0000-0000-0000-000000000001', 'e916dfede4d9ba649a48e140116bc56a85bdd7ad754a11e31dbe51e2db230987', '2026-05-15 16:34:38', '2026-05-08 14:34:38', NULL),
('f12d04d3-3063-4557-9b77-2c695e690317', '00000000-0000-0000-0000-000000000003', '76da4f7cc4eaa406f728948b61fd5c881e9fdcdedc67b9063259e944ce5a1353', '2026-05-16 12:27:32', '2026-05-09 10:27:32', '2026-05-09 10:41:36'),
('f83de42b-aeee-466e-aacf-74ca01686697', '00000000-0000-0000-0000-000000000002', '847cb23a404612d6da2e899565c0f32deccf4f9bbf206d716220faf750a94398', '2026-05-15 17:18:59', '2026-05-08 15:18:59', '2026-05-08 15:22:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `student_level_progress`
--

CREATE TABLE `student_level_progress` (
  `id` char(36) NOT NULL,
  `student_id` char(36) NOT NULL,
  `level_id` tinyint(4) NOT NULL,
  `status` enum('locked','active','passed') NOT NULL DEFAULT 'locked',
  `unlocked_at` datetime DEFAULT NULL,
  `passed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `student_level_progress`
--

INSERT INTO `student_level_progress` (`id`, `student_id`, `level_id`, `status`, `unlocked_at`, `passed_at`) VALUES
('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000003', 1, 'active', '2026-05-09 17:30:17', NULL),
('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000003', 2, 'locked', NULL, NULL),
('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000003', 3, 'locked', NULL, NULL),
('00000000-0000-0000-0000-000000000013', '00000000-0000-0000-0000-000000000003', 4, 'locked', NULL, NULL),
('00000000-0000-0000-0000-000000000014', '00000000-0000-0000-0000-000000000003', 5, 'locked', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','dosen','siswa') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `language_pref` enum('id','en') NOT NULL DEFAULT 'id',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `is_active`, `language_pref`, `created_at`, `updated_at`, `created_by`) VALUES
('00000000-0000-0000-0000-000000000001', 'Super Admin', 'admin@speakon.id', '$2y$12$EwWNtNdv.QSuWZa8aBl03.mbM30PRCT0BMBHFo6UaWzAY6r5ZBxd2', 'superadmin', 1, 'id', '2026-05-08 21:22:20', '2026-05-08 21:29:45', NULL),
('00000000-0000-0000-0000-000000000002', 'Budi Santoso', 'dosen@speakon.id', '$2y$12$KMXT1UZCn/BTYq33qw9SOeTEOFH6rNxS18X.nnSYMTSHSJWTA7rn.', 'dosen', 1, 'id', '2026-05-08 21:45:23', '2026-05-08 21:45:23', '00000000-0000-0000-0000-000000000001'),
('00000000-0000-0000-0000-000000000003', 'Andi Pratama', 'siswa@speakon.id', '$2y$12$MBtgl.70ouoMNQk6FBWfQuVN47/Qqx4W.FZSHnmKSW0mPaCoOVJby', 'siswa', 1, 'id', '2026-05-08 21:45:23', '2026-05-08 21:45:23', '00000000-0000-0000-0000-000000000001');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `account_lockouts`
--
ALTER TABLE `account_lockouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account_lockouts_user` (`user_id`),
  ADD KEY `idx_account_lockouts_until` (`locked_until`);

--
-- Indeks untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_logs_user_id` (`user_id`),
  ADD KEY `idx_audit_logs_action_type` (`action_type`),
  ADD KEY `idx_audit_logs_created_at` (`created_at`);

--
-- Indeks untuk tabel `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_recording` (`recording_id`),
  ADD KEY `idx_feedback_dosen` (`dosen_id`);

--
-- Indeks untuk tabel `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `level_materials`
--
ALTER TABLE `level_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_level_materials_level` (`level_id`);

--
-- Indeks untuk tabel `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_email_time` (`email`,`attempted_at`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_feedback` (`feedback_id`),
  ADD KEY `idx_notifications_student_unread` (`student_id`,`is_read`);

--
-- Indeks untuk tabel `recordings`
--
ALTER TABLE `recordings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recordings_level` (`level_id`),
  ADD KEY `idx_recordings_student_level` (`student_id`,`level_id`),
  ADD KEY `idx_recordings_student_current` (`student_id`,`is_current`);

--
-- Indeks untuk tabel `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refresh_tokens_user` (`user_id`),
  ADD KEY `idx_refresh_tokens_expires` (`expires_at`);

--
-- Indeks untuk tabel `student_level_progress`
--
ALTER TABLE `student_level_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_level` (`student_id`,`level_id`),
  ADD KEY `fk_slp_level` (`level_id`),
  ADD KEY `idx_slp_student` (`student_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `fk_users_created_by` (`created_by`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `account_lockouts`
--
ALTER TABLE `account_lockouts`
  ADD CONSTRAINT `fk_account_lockouts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_dosen` FOREIGN KEY (`dosen_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_feedback_recording` FOREIGN KEY (`recording_id`) REFERENCES `recordings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `level_materials`
--
ALTER TABLE `level_materials`
  ADD CONSTRAINT `fk_level_materials_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `recordings`
--
ALTER TABLE `recordings`
  ADD CONSTRAINT `fk_recordings_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`),
  ADD CONSTRAINT `fk_recordings_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `fk_refresh_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `student_level_progress`
--
ALTER TABLE `student_level_progress`
  ADD CONSTRAINT `fk_slp_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`),
  ADD CONSTRAINT `fk_slp_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
