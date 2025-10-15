-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 04:49 AM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tnvs`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(10) UNSIGNED NOT NULL,
  `firebase_uid` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `firstname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lastname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `firebase_uid`, `email`, `firstname`, `lastname`, `role`, `created_at`) VALUES
(1, 'bUYJId4xhgZaJTlAXBlb85dAMvb2', 'admin@email.com', '', '', 'admin', '2025-08-21 11:52:29'),
(2, '7TNMuehUACZq9aYR9E13e3wJGx63', 'admin@email.com', '', '', 'admin', '2025-08-21 11:59:57'),
(3, '1hYOFnvGd2P8nQ0DVQZzXiv0eEF3', 'marbenken06@yahoo.com', 'ken', 'pola', 'admin', '2025-08-21 13:36:08'),
(4, 'pNoW4G1SuwfUJ4aspT7nB5RFn5t2', 'ako@email.com', '', '', 'admin', '2025-08-21 13:53:56'),
(5, 'HEft8XrUAlhezOsvad6PKW1lV7D2', 'kenpogi@email.com', '', '', 'admin', '2025-08-21 14:03:01'),
(6, 'gtt5r83SpgQpY81EsKG5pA2Zr7D2', 'damian@email.com', '', '', 'admin', '2025-09-15 16:01:41');

-- --------------------------------------------------------

--
-- Table structure for table `cost_analysis`
--

CREATE TABLE `cost_analysis` (
  `id` int(11) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `registration_id` varchar(64) NOT NULL,
  `reservation_ref` varchar(64) NOT NULL,
  `distance_km` decimal(8,2) NOT NULL,
  `estimated_time` varchar(32) NOT NULL,
  `driver_earnings` decimal(10,2) NOT NULL,
  `passenger_fare` decimal(10,2) NOT NULL,
  `incentives` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cost_analysis`
--

INSERT INTO `cost_analysis` (`id`, `user_id`, `registration_id`, `reservation_ref`, `distance_km`, `estimated_time`, `driver_earnings`, `passenger_fare`, `incentives`, `created_at`) VALUES
(64, 'USR-B15A6F2B', 'REG-20250917-C34E96', 'VR251003200232708CD9', '5.30', '8 minutes', '130.00', '160.00', '20.00', '2025-10-04 02:02:36'),
(65, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510041544169517FE', '193.47', '4 hours 50 minutes', '2952.00', '2982.00', '20.00', '2025-10-04 21:44:21'),
(66, 'USR-9E5D9266', 'REG-20250917-C34E96', 'VR2510061607545AF930', '193.47', '4 hours 50 minutes', '2952.00', '2982.00', '20.00', '2025-10-06 22:07:58'),
(67, 'USR-E222B652', 'REG-20250917-C34E96', 'VR251006161011316C12', '20.40', '31 minutes', '357.00', '387.00', '20.00', '2025-10-06 22:10:15'),
(68, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251010085917D741E5', '193.47', '4 hours 50 minutes', '2952.00', '2982.00', '20.00', '2025-10-10 14:59:22'),
(69, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101116424B439E', '1.13', '2 minutes', '68.00', '98.00', '20.00', '2025-10-10 17:16:46'),
(70, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101136054024AE', '1.21', '2 minutes', '68.00', '98.00', '20.00', '2025-10-10 17:36:10'),
(71, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251010120105330E19', '1.07', '2 minutes', '67.00', '97.00', '20.00', '2025-10-10 18:01:10'),
(72, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101206249B3DDD', '1.93', '3 minutes', '79.00', '109.00', '20.00', '2025-10-10 18:06:28'),
(73, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101212118253C2', '1.02', '2 minutes', '66.00', '96.00', '20.00', '2025-10-10 18:12:15'),
(74, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251010121747B11FD1', '0.94', '1 minute', '63.00', '93.00', '20.00', '2025-10-10 18:17:51'),
(75, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251010122039ABFFE2', '4.54', '7 minutes', '119.00', '149.00', '20.00', '2025-10-10 18:20:43'),
(76, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251010122404DB7A36', '1.56', '2 minutes', '73.00', '103.00', '20.00', '2025-10-10 18:24:08'),
(77, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251010123055506A39', '1.11', '2 minutes', '67.00', '97.00', '20.00', '2025-10-10 18:30:59'),
(78, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR25101012393017C2DC', '2.70', '4 minutes', '90.00', '120.00', '20.00', '2025-10-10 18:39:34'),
(79, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101307223451BD', '5.37', '8 minutes', '130.00', '160.00', '20.00', '2025-10-10 19:07:26'),
(80, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101322479B7151', '1.16', '2 minutes', '68.00', '98.00', '20.00', '2025-10-10 19:22:51'),
(81, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251010133628C3AE52', '5.35', '8 minutes', '130.00', '160.00', '20.00', '2025-10-10 19:36:32'),
(82, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101344165905B6', '2.94', '4 minutes', '93.00', '123.00', '20.00', '2025-10-10 19:44:20'),
(83, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510101421134C6170', '3.67', '6 minutes', '106.00', '136.00', '20.00', '2025-10-10 20:21:18'),
(84, 'USR-B15A6F2B', 'REG-20250917-C34E96', 'VR251010142222A4FED1', '3.10', '5 minutes', '97.00', '127.00', '20.00', '2025-10-10 20:22:26'),
(85, 'USR-B15A6F2B', 'REG-20250917-C34E96', 'VR251011152153F0FB18', '3.89', '6 minutes', '109.00', '139.00', '20.00', '2025-10-11 21:21:58'),
(86, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251013132431CAFEF4', '4.19', '6 minutes', '112.00', '142.00', '20.00', '2025-10-13 19:24:35'),
(87, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251013134314BB3F05', '2.64', '4 minutes', '90.00', '120.00', '20.00', '2025-10-13 19:43:18'),
(88, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251013134719083789', '2.39', '4 minutes', '87.00', '117.00', '20.00', '2025-10-13 19:47:23'),
(89, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251013135055DC0D52', '3.02', '5 minutes', '96.00', '126.00', '20.00', '2025-10-13 19:50:59'),
(90, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR25101314114436A665', '2.57', '4 minutes', '89.00', '119.00', '20.00', '2025-10-13 20:11:48'),
(91, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR2510131414293DC5C8', '3.08', '5 minutes', '97.00', '127.00', '20.00', '2025-10-13 20:14:34'),
(92, 'USR-895E821C', 'REG-20250917-1D7E53', 'VR251013152018E19355', '8.47', '13 minutes', '178.00', '208.00', '20.00', '2025-10-13 21:20:23'),
(93, 'USR-B15A6F2B', 'REG-20250917-1D7E53', 'VR25101317295078A709', '8.47', '13 minutes', '178.00', '208.00', '20.00', '2025-10-13 23:29:54'),
(94, 'USR-CC506E3C', 'REG-20250917-C34E96', 'VR25101406453394DFE1', '20.94', '31 minutes', '363.00', '393.00', '20.00', '2025-10-14 12:45:37'),
(95, 'USR-895E821C', 'REG-20250917-C34E96', 'VR251014092531DD2BFD', '8.42', '13 minutes', '177.00', '207.00', '20.00', '2025-10-14 15:25:35');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `registration_id` varchar(50) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `mv_file_number` varchar(50) NOT NULL,
  `lto_orcr_number` varchar(50) NOT NULL,
  `registration_expiry` date NOT NULL,
  `ltfrb_case_number` varchar(100) NOT NULL,
  `ltfrb_franchise_expiry` varchar(100) NOT NULL,
  `tnvs_accreditation_number` varchar(100) NOT NULL,
  `tnvs_expiry` varchar(100) NOT NULL,
  `passenger_capacity` varchar(100) NOT NULL,
  `fuel_type` enum('gasoline','diesel','electric','hybrid','others') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `registration_id`, `user_id`, `mv_file_number`, `lto_orcr_number`, `registration_expiry`, `ltfrb_case_number`, `ltfrb_franchise_expiry`, `tnvs_accreditation_number`, `tnvs_expiry`, `passenger_capacity`, `fuel_type`, `created_at`, `updated_at`) VALUES
(9, 'REG-20250820-5E9813', 'USR-C3A38CC6', '123', '321', '2025-08-20', '312', '2025-08-20', '321', '2025-08-20', '312', 'gasoline', '2025-08-20 07:43:27', '2025-08-20 07:43:27'),
(10, 'REG-20250820-9AC8D8', 'USR-8863E8D9', '231', '231', '2025-08-20', '123', '2025-08-20', '312', '2025-08-20', '123', 'gasoline', '2025-08-20 11:29:08', '2025-08-20 11:29:08'),
(11, 'REG-20250821-01F032', 'USR-ACE941CB', '312', '231', '2025-08-22', '231', '2025-08-22', '123', '2025-08-22', '123', 'gasoline', '2025-08-21 16:52:41', '2025-08-21 16:52:41'),
(12, 'REG-20250822-42E922', 'USR-C7F57872', '312', '123', '2025-08-22', '123', '2025-08-22', '231', '2025-08-22', '123', 'gasoline', '2025-08-22 04:35:34', '2025-08-22 04:35:34'),
(13, 'REG-20250917-1D7E53', 'USR-CC506E3C', 'MV-2020-091234', 'ORCR-567890', '2026-09-15', 'FR-09202019-00123', '2027-09-15', 'TNVS-PH-876543', '2026-09-15', '4', 'gasoline', '2025-09-17 15:23:44', '2025-09-17 15:23:44'),
(14, 'REG-20250917-C34E96', 'USR-B84F4940', 'MV-2021-076543', 'ORCR-998877', '2026-08-20', 'FR-082021-00987', '2027-08-20', 'TNVS-CB-654321', '2026-08-20', '5', 'gasoline', '2025-09-17 15:37:11', '2025-09-17 15:37:11'),
(15, 'REG-20251001-82E318', 'USR-9E5D9266', '123', '123', '2025-10-01', '231', '2025-10-01', '123', '2025-10-01', '7', 'gasoline', '2025-10-01 07:57:29', '2025-10-01 07:57:29');

-- --------------------------------------------------------

--
-- Table structure for table `driver_performance`
--

CREATE TABLE `driver_performance` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `rated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `saved_locations`
--

CREATE TABLE `saved_locations` (
  `id` int(11) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `reservation_ref` varchar(50) DEFAULT NULL,
  `vehicle_registration_id` varchar(128) NOT NULL,
  `type` enum('pickup','dropoff','other') DEFAULT 'other',
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `saved_locations`
--

INSERT INTO `saved_locations` (`id`, `user_id`, `reservation_ref`, `vehicle_registration_id`, `type`, `latitude`, `longitude`, `address`, `created_at`) VALUES
(173, 'USR-895E821C', 'VR2509171922496BFA4C', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', '2025-09-17 17:22:49'),
(174, 'USR-895E821C', 'VR2509171922496BFA4C', 'REG-20250917-1D7E53', 'dropoff', '14.7396955', '121.0191688', 'Deparo, Zone 15, Caybiga, District 1, Caloocan, Northern Manila District, Metro Manila, 1420, Philippines', '2025-09-17 17:22:49'),
(175, 'USR-895E821C', 'VR2509220827249F32B7', 'REG-20250917-1D7E53', 'pickup', '14.5793612', '120.9950209', 'Paco, Fifth District, Manila, Capital District, Metro Manila, 1007, Philippines', '2025-09-22 06:27:24'),
(176, 'USR-895E821C', 'VR2509220827249F32B7', 'REG-20250917-1D7E53', 'dropoff', '14.7291423', '121.0367323', 'Bestlink College of the Philippines - Criminology Department, Heavenly Drive, San Agustin, 5th District, Caloocan, Eastern Manila District, Metro Manila, 1400, Philippines', '2025-09-22 06:27:24'),
(177, 'USR-895E821C', 'VR250922094914FA27A6', 'REG-20250917-1D7E53', 'pickup', '14.5356015', '120.9834959', 'Mall of Asia, Pacific Drive Bicycle Lane, Barangay 76, Zone 10, District 1, Pasay, Southern Manila District, Metro Manila, 1308, Philippines', '2025-09-22 07:49:14'),
(178, 'USR-895E821C', 'VR250922094914FA27A6', 'REG-20250917-1D7E53', 'dropoff', '14.7285591', '121.0416147', 'Bestlink College of the Philippines, Quirno Highway, Quezon City, Metro Manila, Philippines', '2025-09-22 07:49:14'),
(179, 'USR-9E5D9266', 'VR250922095226F41DE5', 'REG-20250917-C34E96', 'pickup', '16.4119905', '120.5933719', 'Baguio, Cordillera Administrative Region, 2600, Philippines', '2025-09-22 07:52:26'),
(180, 'USR-9E5D9266', 'VR250922095226F41DE5', 'REG-20250917-C34E96', 'dropoff', '10.4700000', '123.8300000', 'Cebu, Philippines', '2025-09-22 07:52:26'),
(181, 'USR-895E821C', 'VR25092213432622B4C9', 'REG-20250917-1D7E53', 'pickup', '14.5324617', '120.9968110', 'Baclaran, ParaÃ±aque, Metro Manila, Philippines', '2025-09-22 11:43:26'),
(182, 'USR-895E821C', 'VR25092213432622B4C9', 'REG-20250917-1D7E53', 'dropoff', '14.5008116', '120.9915327', 'ParaÃ±aque, Metro Manila, Philippines', '2025-09-22 11:43:26'),
(183, 'USR-E222B652', 'VR2509221513496BAB83', 'REG-20250917-1D7E53', 'pickup', '14.5344077', '120.9984558', 'Baclaran, Taft Avenue, Barangay 145, Zone 16, District 1, Pasay, Southern Manila District, Metro Manila, 1302, Philippines', '2025-09-22 13:13:49'),
(184, 'USR-E222B652', 'VR2509221513496BAB83', 'REG-20250917-1D7E53', 'dropoff', '14.6547213', '121.0663102', 'University of the Philippines Diliman, Rielinton Street, UP Campus, Diliman, 4th District, Quezon City, Eastern Manila District, Metro Manila, 1100, Philippines', '2025-09-22 13:13:49'),
(185, 'USR-CC506E3C', 'VR2509261713249B8C5C', 'REG-20250917-C34E96', 'pickup', '14.5995000', '120.9842000', '', '2025-09-26 15:13:24'),
(186, 'USR-CC506E3C', 'VR2509261713249B8C5C', 'REG-20250917-C34E96', 'dropoff', '14.5995000', '120.9842000', '', '2025-09-26 15:13:24'),
(187, 'USR-CC506E3C', 'VR2509261714419443B2', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', '2025-09-26 15:14:41'),
(188, 'USR-CC506E3C', 'VR2509261714419443B2', 'REG-20250917-C34E96', 'dropoff', '14.7055228', '121.0742821', 'Fairview, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1122, Philippines', '2025-09-26 15:14:41'),
(189, 'USR-CC506E3C', 'VR250926200043620F3C', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', '2025-09-26 18:00:43'),
(190, 'USR-CC506E3C', 'VR250926200043620F3C', 'REG-20250917-C34E96', 'dropoff', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quezon City, Metro Manila, Philippines', '2025-09-26 18:00:43'),
(191, 'USR-CC506E3C', 'VR2509270810240053C2', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', '2025-09-27 06:10:24'),
(192, 'USR-CC506E3C', 'VR2509270810240053C2', 'REG-20250917-C34E96', 'dropoff', '14.7055228', '121.0742821', 'Fairview, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1122, Philippines', '2025-09-27 06:10:24'),
(193, 'USR-CC506E3C', 'VR25092713125526EFDD', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', '2025-09-27 11:12:55'),
(194, 'USR-CC506E3C', 'VR25092713125526EFDD', 'REG-20250917-C34E96', 'dropoff', '14.7055228', '121.0742821', 'Fairview, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1122, Philippines', '2025-09-27 11:12:55'),
(195, 'USR-CC506E3C', 'VR250929073442E0BAA3', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', '2025-09-29 05:34:42'),
(196, 'USR-CC506E3C', 'VR250929073442E0BAA3', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', 'Baguio, Cordillera Administrative Region, 2600, Philippines', '2025-09-29 05:34:42'),
(197, 'USR-CC506E3C', 'VR250929075337E33121', 'REG-20250917-C34E96', 'pickup', '16.4119905', '120.5933719', 'Baguio, Cordillera Administrative Region, Philippines', '2025-09-29 05:53:37'),
(198, 'USR-CC506E3C', 'VR250929075337E33121', 'REG-20250917-C34E96', 'dropoff', '14.7291423', '121.0367323', 'Bestlink College of the Philippines - Criminology Department, Heavenly Drive, San Agustin, 5th District, Caloocan, Eastern Manila District, Metro Manila, 1400, Philippines', '2025-09-29 05:53:38'),
(199, 'USR-CC506E3C', 'VR250929075626442071', 'REG-20250917-C34E96', 'pickup', '16.4119905', '120.5933719', 'Baguio, Cordillera Administrative Region, 2600, Philippines', '2025-09-29 05:56:26'),
(200, 'USR-CC506E3C', 'VR250929075626442071', 'REG-20250917-C34E96', 'dropoff', '14.7264963', '121.0414651', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', '2025-09-29 05:56:26'),
(201, 'USR-CC506E3C', 'VR250929111125A2C5D1', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 09:11:25'),
(202, 'USR-CC506E3C', 'VR250929111125A2C5D1', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 09:11:25'),
(203, 'USR-CC506E3C', 'VR250929111525DDAEEA', 'REG-20250917-C34E96', 'pickup', '14.7291423', '121.0367323', NULL, '2025-09-29 09:15:25'),
(204, 'USR-CC506E3C', 'VR250929111525DDAEEA', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 09:15:25'),
(205, 'USR-CC506E3C', 'VR25092911263399A72B', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 09:26:33'),
(206, 'USR-CC506E3C', 'VR25092911263399A72B', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 09:26:33'),
(207, 'USR-CC506E3C', 'VR25092912020134157D', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 10:02:01'),
(208, 'USR-CC506E3C', 'VR25092912020134157D', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 10:02:01'),
(209, 'USR-CC506E3C', 'VR250929125205350AEE', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 10:52:05'),
(210, 'USR-CC506E3C', 'VR250929125205350AEE', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 10:52:05'),
(211, 'USR-CC506E3C', 'VR250929171129301A0A', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 15:11:29'),
(212, 'USR-CC506E3C', 'VR250929171129301A0A', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 15:11:29'),
(213, 'USR-CC506E3C', 'VR25092917221539955F', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 15:22:15'),
(214, 'USR-CC506E3C', 'VR25092917221539955F', 'REG-20250917-C34E96', 'dropoff', '14.5356015', '120.9834959', NULL, '2025-09-29 15:22:15'),
(215, 'USR-CC506E3C', 'VR250929175254BF1A1D', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 15:52:54'),
(216, 'USR-CC506E3C', 'VR250929175254BF1A1D', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 15:52:54'),
(217, 'USR-CC506E3C', 'VR25092917574014400D', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 15:57:40'),
(218, 'USR-CC506E3C', 'VR25092917574014400D', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 15:57:40'),
(219, 'USR-CC506E3C', 'VR250929175744EB176A', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 15:57:44'),
(220, 'USR-CC506E3C', 'VR250929175744EB176A', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 15:57:44'),
(221, 'USR-CC506E3C', 'VR2509291800469F34A4', 'REG-20250917-C34E96', 'pickup', '14.5348194', '120.9835441', NULL, '2025-09-29 16:00:46'),
(222, 'USR-CC506E3C', 'VR2509291800469F34A4', 'REG-20250917-C34E96', 'dropoff', '14.6503760', '121.0676426', NULL, '2025-09-29 16:00:46'),
(223, 'USR-CC506E3C', 'VR250929181630C94975', 'REG-20250917-C34E96', 'pickup', '14.5321910', '120.9837424', NULL, '2025-09-29 16:16:30'),
(224, 'USR-CC506E3C', 'VR250929181630C94975', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 16:16:30'),
(225, 'USR-B15A6F2B', 'VR25092918184882E72A', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-09-29 16:18:48'),
(226, 'USR-B15A6F2B', 'VR25092918184882E72A', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-09-29 16:18:48'),
(227, 'USR-9E5D9266', 'VR251001092733DC031C', 'REG-20250917-1D7E53', 'pickup', '14.7291423', '121.0367323', NULL, '2025-10-01 07:27:33'),
(228, 'USR-9E5D9266', 'VR251001092733DC031C', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-01 07:27:33'),
(229, 'USR-9E5D9266', 'VR251001093626E76C83', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 07:36:26'),
(230, 'USR-9E5D9266', 'VR251001093626E76C83', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-01 07:36:26'),
(231, 'USR-9E5D9266', 'VR251001105335B2840F', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 08:53:35'),
(232, 'USR-9E5D9266', 'VR251001105335B2840F', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-01 08:53:35'),
(233, 'USR-9E5D9266', 'VR251001105546E4D86E', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 08:55:46'),
(234, 'USR-9E5D9266', 'VR251001105546E4D86E', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-01 08:55:46'),
(235, 'USR-B15A6F2B', 'VR251001105646D1AB43', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 08:56:46'),
(236, 'USR-B15A6F2B', 'VR251001105646D1AB43', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-01 08:56:46'),
(237, 'USR-B84F4940', 'VR251001110044D9EBED', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 09:00:44'),
(238, 'USR-B84F4940', 'VR251001110044D9EBED', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-01 09:00:44'),
(239, 'USR-895E821C', 'VR2510011140349FE323', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 09:40:34'),
(240, 'USR-895E821C', 'VR2510011140349FE323', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-01 09:40:34'),
(241, 'USR-B84F4940', 'VR251001114146064D75', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 09:41:46'),
(242, 'USR-B84F4940', 'VR251001114146064D75', 'REG-20250917-1D7E53', 'dropoff', '14.5321910', '120.9837424', NULL, '2025-10-01 09:41:47'),
(243, 'USR-B84F4940', 'VR251001115324BC4B2E', 'REG-20250917-1D7E53', 'pickup', '14.7291423', '121.0367323', NULL, '2025-10-01 09:53:24'),
(244, 'USR-B84F4940', 'VR251001115324BC4B2E', 'REG-20250917-1D7E53', 'dropoff', '14.5348194', '120.9835441', NULL, '2025-10-01 09:53:24'),
(245, 'USR-9E5D9266', 'VR251001115405837523', 'REG-20250917-C34E96', 'pickup', '14.7291423', '121.0367323', NULL, '2025-10-01 09:54:05'),
(246, 'USR-9E5D9266', 'VR251001115405837523', 'REG-20250917-C34E96', 'dropoff', '14.5436995', '120.9946503', NULL, '2025-10-01 09:54:05'),
(247, 'USR-895E821C', 'VR2510011247191F3D18', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 10:47:19'),
(248, 'USR-895E821C', 'VR2510011247191F3D18', 'REG-20250917-1D7E53', 'dropoff', '14.5356015', '120.9834959', NULL, '2025-10-01 10:47:19'),
(249, 'USR-895E821C', 'VR251001133248747893', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 11:32:48'),
(250, 'USR-895E821C', 'VR251001133248747893', 'REG-20250917-1D7E53', 'dropoff', '14.5356015', '120.9834959', NULL, '2025-10-01 11:32:48'),
(251, 'USR-895E821C', 'VR251001152340264693', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 13:23:40'),
(252, 'USR-895E821C', 'VR251001152340264693', 'REG-20250917-1D7E53', 'dropoff', '14.5321910', '120.9837424', NULL, '2025-10-01 13:23:40'),
(253, 'gtt5r83SpgQpY81EsKG5pA2Zr7D2', 'VR251001180729DA1445', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-01 16:07:29'),
(254, 'gtt5r83SpgQpY81EsKG5pA2Zr7D2', 'VR251001180729DA1445', 'REG-20250917-1D7E53', 'dropoff', '14.7245857', '121.0659457', NULL, '2025-10-01 16:07:29'),
(255, 'USR-895E821C', 'VR2510031920306909AD', 'REG-20250917-C34E96', 'pickup', '14.7291423', '121.0367323', NULL, '2025-10-03 17:20:30'),
(256, 'USR-895E821C', 'VR2510031920306909AD', 'REG-20250917-C34E96', 'dropoff', '10.4700000', '123.8300000', NULL, '2025-10-03 17:20:30'),
(257, 'gtt5r83SpgQpY81EsKG5pA2Zr7D2', 'VR25100319541922A805', 'REG-20250917-C34E96', 'pickup', '14.5974549', '120.9662533', NULL, '2025-10-03 17:54:19'),
(258, 'gtt5r83SpgQpY81EsKG5pA2Zr7D2', 'VR25100319541922A805', 'REG-20250917-C34E96', 'dropoff', '14.6001128', '120.9973240', NULL, '2025-10-03 17:54:19'),
(259, 'USR-CC506E3C', 'VR2510031955512D2F20', 'REG-20250917-C34E96', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-03 17:55:51'),
(260, 'USR-CC506E3C', 'VR2510031955512D2F20', 'REG-20250917-C34E96', 'dropoff', '14.6079825', '120.9874749', NULL, '2025-10-03 17:55:51'),
(261, 'USR-9E5D9266', 'VR251003195635C2BA2E', 'REG-20250917-1D7E53', 'pickup', '14.6084186', '120.9686565', NULL, '2025-10-03 17:56:35'),
(262, 'USR-9E5D9266', 'VR251003195635C2BA2E', 'REG-20250917-1D7E53', 'dropoff', '14.5956276', '121.0108852', NULL, '2025-10-03 17:56:35'),
(263, 'USR-895E821C', 'VR25100319591979D649', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-03 17:59:19'),
(264, 'USR-895E821C', 'VR25100319591979D649', 'REG-20250917-1D7E53', 'dropoff', '14.6896063', '121.0336619', NULL, '2025-10-03 17:59:19'),
(265, 'USR-B15A6F2B', 'VR251003200232708CD9', 'REG-20250917-C34E96', 'pickup', '14.5974549', '120.9775829', NULL, '2025-10-03 18:02:32'),
(266, 'USR-B15A6F2B', 'VR251003200232708CD9', 'REG-20250917-C34E96', 'dropoff', '14.5939664', '121.0266781', NULL, '2025-10-03 18:02:32'),
(267, 'USR-895E821C', 'VR2510041544169517FE', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-04 13:44:16'),
(268, 'USR-895E821C', 'VR2510041544169517FE', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-04 13:44:16'),
(269, 'USR-9E5D9266', 'VR2510061607545AF930', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-06 14:07:54'),
(270, 'USR-9E5D9266', 'VR2510061607545AF930', 'REG-20250917-C34E96', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-06 14:07:54'),
(271, 'USR-E222B652', 'VR251006161011316C12', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-06 14:10:11'),
(272, 'USR-E222B652', 'VR251006161011316C12', 'REG-20250917-C34E96', 'dropoff', '14.5447555', '121.0671340', NULL, '2025-10-06 14:10:11'),
(273, 'USR-895E821C', 'VR251010085917D741E5', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-10 06:59:17'),
(274, 'USR-895E821C', 'VR251010085917D741E5', 'REG-20250917-1D7E53', 'dropoff', '16.4119905', '120.5933719', NULL, '2025-10-10 06:59:17'),
(275, 'USR-895E821C', 'VR2510101116424B439E', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 09:16:42'),
(276, 'USR-895E821C', 'VR2510101116424B439E', 'REG-20250917-1D7E53', 'dropoff', '14.5903116', '120.9887409', NULL, '2025-10-10 09:16:42'),
(277, 'USR-895E821C', 'VR2510101136054024AE', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 09:36:05'),
(278, 'USR-895E821C', 'VR2510101136054024AE', 'REG-20250917-1D7E53', 'dropoff', '14.5896471', '120.9794712', NULL, '2025-10-10 09:36:05'),
(279, 'USR-895E821C', 'VR251010120105330E19', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 10:01:05'),
(280, 'USR-895E821C', 'VR251010120105330E19', 'REG-20250917-1D7E53', 'dropoff', '14.5904778', '120.9806728', NULL, '2025-10-10 10:01:06'),
(281, 'USR-895E821C', 'VR2510101206249B3DDD', 'REG-20250917-1D7E53', 'pickup', '14.6007773', '120.9815311', NULL, '2025-10-10 10:06:24'),
(282, 'USR-895E821C', 'VR2510101206249B3DDD', 'REG-20250917-1D7E53', 'dropoff', '14.5861585', '120.9911442', NULL, '2025-10-10 10:06:24'),
(283, 'USR-895E821C', 'VR2510101212118253C2', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 10:12:11'),
(284, 'USR-895E821C', 'VR2510101212118253C2', 'REG-20250917-1D7E53', 'dropoff', '14.5918068', '120.9894276', NULL, '2025-10-10 10:12:11'),
(285, 'USR-895E821C', 'VR251010121747B11FD1', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 10:17:47'),
(286, 'USR-895E821C', 'VR251010121747B11FD1', 'REG-20250917-1D7E53', 'dropoff', '14.6057608', '120.9782696', NULL, '2025-10-10 10:17:47'),
(287, 'USR-895E821C', 'VR251010122039ABFFE2', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 10:20:39'),
(288, 'USR-895E821C', 'VR251010122039ABFFE2', 'REG-20250917-1D7E53', 'dropoff', '14.6192156', '121.0211849', NULL, '2025-10-10 10:20:39'),
(289, 'USR-895E821C', 'VR251010122404DB7A36', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 10:24:04'),
(290, 'USR-895E821C', 'VR251010122404DB7A36', 'REG-20250917-1D7E53', 'dropoff', '14.6037674', '120.9980106', NULL, '2025-10-10 10:24:04'),
(291, 'USR-895E821C', 'VR251010123055506A39', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 10:30:55'),
(292, 'USR-895E821C', 'VR251010123055506A39', 'REG-20250917-1D7E53', 'dropoff', '14.6069236', '120.9772396', NULL, '2025-10-10 10:30:55'),
(293, 'USR-895E821C', 'VR25101012393017C2DC', 'REG-20250917-1D7E53', 'pickup', '14.5871552', '120.9913158', NULL, '2025-10-10 10:39:30'),
(294, 'USR-895E821C', 'VR25101012393017C2DC', 'REG-20250917-1D7E53', 'dropoff', '14.6112425', '120.9942341', NULL, '2025-10-10 10:39:30'),
(295, 'USR-895E821C', 'VR2510101307223451BD', 'REG-20250917-1D7E53', 'pickup', '14.6258597', '121.0259914', NULL, '2025-10-10 11:07:22'),
(296, 'USR-895E821C', 'VR2510101307223451BD', 'REG-20250917-1D7E53', 'dropoff', '14.5995000', '120.9842000', NULL, '2025-10-10 11:07:22'),
(297, 'USR-895E821C', 'VR2510101322479B7151', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 11:22:47'),
(298, 'USR-895E821C', 'VR2510101322479B7151', 'REG-20250917-1D7E53', 'dropoff', '14.5934680', '120.9930325', NULL, '2025-10-10 11:22:47'),
(299, 'USR-895E821C', 'VR251010133628C3AE52', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 11:36:28'),
(300, 'USR-895E821C', 'VR251010133628C3AE52', 'REG-20250917-1D7E53', 'dropoff', '14.6263579', '121.0254765', NULL, '2025-10-10 11:36:28'),
(301, 'USR-895E821C', 'VR2510101344165905B6', 'REG-20250917-1D7E53', 'pickup', '14.6009434', '121.0055637', NULL, '2025-10-10 11:44:16'),
(302, 'USR-895E821C', 'VR2510101344165905B6', 'REG-20250917-1D7E53', 'dropoff', '14.6125713', '120.9810162', NULL, '2025-10-10 11:44:16'),
(303, 'USR-895E821C', 'VR2510101421134C6170', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 12:21:13'),
(304, 'USR-895E821C', 'VR2510101421134C6170', 'REG-20250917-1D7E53', 'dropoff', '14.6109103', '121.0162067', NULL, '2025-10-10 12:21:13'),
(305, 'USR-B15A6F2B', 'VR251010142222A4FED1', 'REG-20250917-C34E96', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-10 12:22:22'),
(306, 'USR-B15A6F2B', 'VR251010142222A4FED1', 'REG-20250917-C34E96', 'dropoff', '14.6019401', '121.0129452', NULL, '2025-10-10 12:22:22'),
(307, 'USR-B15A6F2B', 'VR251011152153F0FB18', 'REG-20250917-C34E96', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-11 13:21:53'),
(308, 'USR-B15A6F2B', 'VR251011152153F0FB18', 'REG-20250917-C34E96', 'dropoff', '14.6137341', '121.0172367', NULL, '2025-10-11 13:21:53'),
(309, 'USR-895E821C', 'VR251013132431CAFEF4', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-13 11:24:31'),
(310, 'USR-895E821C', 'VR251013132431CAFEF4', 'REG-20250917-1D7E53', 'dropoff', '14.6896063', '121.0336619', NULL, '2025-10-13 11:24:31'),
(311, 'USR-895E821C', 'VR251013134314BB3F05', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-13 11:43:14'),
(312, 'USR-895E821C', 'VR251013134314BB3F05', 'REG-20250917-1D7E53', 'dropoff', '14.5951292', '121.0083103', NULL, '2025-10-13 11:43:14'),
(313, 'USR-895E821C', 'VR251013134719083789', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-13 11:47:19'),
(314, 'USR-895E821C', 'VR251013134719083789', 'REG-20250917-1D7E53', 'dropoff', '14.5984516', '121.0064220', NULL, '2025-10-13 11:47:19'),
(315, 'USR-895E821C', 'VR251013135055DC0D52', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-13 11:50:55'),
(316, 'USR-895E821C', 'VR251013135055DC0D52', 'REG-20250917-1D7E53', 'dropoff', '14.5909761', '121.0108852', NULL, '2025-10-13 11:50:55'),
(317, 'USR-895E821C', 'VR25101314114436A665', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-13 12:11:44'),
(318, 'USR-895E821C', 'VR25101314114436A665', 'REG-20250917-1D7E53', 'dropoff', '14.5972888', '121.0079670', NULL, '2025-10-13 12:11:44'),
(319, 'USR-895E821C', 'VR2510131414293DC5C8', 'REG-20250917-1D7E53', 'pickup', '14.5995000', '120.9842000', NULL, '2025-10-13 12:14:29'),
(320, 'USR-895E821C', 'VR2510131414293DC5C8', 'REG-20250917-1D7E53', 'dropoff', '14.5856601', '121.0089970', NULL, '2025-10-13 12:14:30'),
(321, 'USR-895E821C', 'VR251013152018E19355', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-13 13:20:18'),
(322, 'USR-895E821C', 'VR251013152018E19355', 'REG-20250917-1D7E53', 'dropoff', '14.6542244', '121.0663607', NULL, '2025-10-13 13:20:18'),
(323, 'USR-B15A6F2B', 'VR25101317295078A709', 'REG-20250917-1D7E53', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-13 15:29:50'),
(324, 'USR-B15A6F2B', 'VR25101317295078A709', 'REG-20250917-1D7E53', 'dropoff', '14.6542244', '121.0663607', NULL, '2025-10-13 15:29:50'),
(325, 'USR-CC506E3C', 'VR25101406453394DFE1', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-14 04:45:33'),
(326, 'USR-CC506E3C', 'VR25101406453394DFE1', 'REG-20250917-C34E96', 'dropoff', '14.5436995', '120.9946503', NULL, '2025-10-14 04:45:33'),
(327, 'USR-895E821C', 'VR251014092531DD2BFD', 'REG-20250917-C34E96', 'pickup', '14.7264963', '121.0414651', NULL, '2025-10-14 07:25:31'),
(328, 'USR-895E821C', 'VR251014092531DD2BFD', 'REG-20250917-C34E96', 'dropoff', '14.6547213', '121.0663102', NULL, '2025-10-14 07:25:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `age` varchar(100) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','driver') DEFAULT 'user',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `firstname`, `lastname`, `age`, `gender`, `contact`, `email`, `password`, `role`, `status`, `last_login`, `failed_attempts`, `created_at`, `updated_at`) VALUES
(12, 'USR-CC506E3C', 'John', 'Doe', '', '', '', 'johndoe@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'driver', 'active', NULL, 0, '2025-09-17 15:17:59', '2025-09-17 15:23:45'),
(13, 'USR-895E821C', 'James', 'Smit', '', '', '', 'Jamessmith@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', 'active', NULL, 0, '2025-09-17 15:25:03', '2025-09-17 15:25:03'),
(14, 'USR-E222B652', 'Will', 'Samuel', '', '', '', 'willsamuel@emai.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', 'active', NULL, 0, '2025-09-17 15:31:46', '2025-09-17 15:31:46'),
(15, 'USR-B84F4940', 'Richard', 'William', '', '', '', 'richardwilliam@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'driver', 'active', NULL, 0, '2025-09-17 15:33:42', '2025-09-17 15:37:11'),
(16, 'USR-9E5D9266', 'Walter', 'Frederick', '', '', '', 'walterfrederick@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'driver', 'active', NULL, 0, '2025-09-17 15:38:22', '2025-10-01 07:57:29'),
(17, 'USR-B15A6F2B', 'jack', 'daniels', '', '', '', 'jackdaniels@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', 'active', NULL, 0, '2025-09-26 06:53:13', '2025-09-26 06:53:13'),
(18, 'USR-A26977B7', 'will', 'stubs', '', '', '', 'willstubs@email.com', 'e10adc3949ba59abbe56e057f20f883e', 'user', 'active', NULL, 0, '2025-09-26 07:11:47', '2025-09-26 07:11:47'),
(19, 'USR-67B510A1', 'jack', 'Smit', '15', 'male', '12345678910', 'jacksmit@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', 'active', NULL, 0, '2025-09-26 09:07:23', '2025-09-26 09:07:23'),
(20, 'USR-96243CC6', 'ken', 'smith', '18', 'male', '1234567891013', 'richardemilson@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', 'active', NULL, 0, '2025-09-27 08:43:54', '2025-09-27 08:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `registration_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `conduction_sticker` varchar(100) NOT NULL,
  `vehicle_plate` varchar(100) NOT NULL,
  `car_brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` varchar(50) NOT NULL,
  `vehicle_type` enum('sedan','suv','hatchback','mpv','van','others') NOT NULL,
  `color` varchar(50) NOT NULL,
  `passenger_capacity` varchar(100) NOT NULL,
  `chassis_number` varchar(50) NOT NULL,
  `engine_number` varchar(50) NOT NULL,
  `fuel_type` enum('gasoline','diesel','electric','hybrid','others') NOT NULL,
  `current_mileage` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `registration_id`, `user_id`, `conduction_sticker`, `vehicle_plate`, `car_brand`, `model`, `year`, `vehicle_type`, `color`, `passenger_capacity`, `chassis_number`, `engine_number`, `fuel_type`, `current_mileage`, `created_at`, `updated_at`) VALUES
(14, 'REG-20250917-1D7E53', 'USR-CC506E3C', 'CND56789', 'ABC-1234', 'Toyota', 'Vios G', '2019', 'sedan', 'Silver', '4', 'JTDBU4EE9B9123456', '1NZ-1234567', 'gasoline', '62,350 km', '2025-09-17 15:23:44', '2025-09-17 15:23:44'),
(15, 'REG-20250917-C34E96', 'USR-B84F4940', 'CND45321', 'ZXC-9876', 'Honda', 'City RS Turbo', '2021', 'sedan', 'Black', '5', 'MRHFC1650MP123456', 'L15B7-654321', 'diesel', '34,120 km', '2025-09-17 15:37:11', '2025-09-17 15:37:11');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_insurance`
--

CREATE TABLE `vehicle_insurance` (
  `id` int(11) NOT NULL,
  `registration_id_insurance` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `insurance_provider` varchar(100) NOT NULL,
  `policy_number` varchar(100) NOT NULL,
  `insurance_type` varchar(100) NOT NULL,
  `coverage_type` varchar(100) NOT NULL,
  `num_passengers_covered` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `expiration_date` date NOT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `renewal_reminders` int(11) NOT NULL,
  `status` enum('active','expired') NOT NULL,
  `agent_contact_person` varchar(100) NOT NULL,
  `scanned_copy_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vehicle_insurance`
--

INSERT INTO `vehicle_insurance` (`id`, `registration_id_insurance`, `user_id`, `insurance_provider`, `policy_number`, `insurance_type`, `coverage_type`, `num_passengers_covered`, `start_date`, `expiration_date`, `premium_amount`, `renewal_reminders`, `status`, `agent_contact_person`, `scanned_copy_path`, `created_at`, `updated_at`) VALUES
(26, 'REG-20250917-1D7E53', 'USR-CC506E3C', 'AXA Philippines', 'AXA-POL-0920223456', 'Comprehensive', 'Own Damage, Theft, Acts of Nature, TPL', 5, '2025-09-12', '2026-09-01', '18500.00', 30, 'active', 'Maria Dela Cruz (AXA Agent)', 'logistics2/uploads/insurance/INS_20250917_172344_3ab5ebdb.jpg', '2025-09-17 15:23:45', '2025-09-17 15:23:45'),
(27, 'REG-20250917-C34E96', 'USR-B84F4940', 'Malayan Insurance', 'MAL-INS-20215432', 'Comprehensive', 'Own Damage, Theft, Fire, Flood, TPL', 5, '2025-08-15', '2026-08-15', '21750.00', 45, 'active', 'Roberto Cruz (Malayan Insurance)', 'logistics2/uploads/insurance/INS_20250917_173711_1056f5b3.jpg', '2025-09-17 15:37:11', '2025-09-17 15:37:11');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_reservations`
--

CREATE TABLE `vehicle_reservations` (
  `id` int(11) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `reservation_ref` varchar(32) NOT NULL,
  `vehicle_registration_id` varchar(128) NOT NULL,
  `vehicle_plate` varchar(64) NOT NULL,
  `trip_date` date NOT NULL,
  `pickup_datetime` datetime NOT NULL,
  `dropoff_datetime` datetime NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `requester_name` varchar(128) NOT NULL,
  `purpose` text DEFAULT NULL,
  `passengers_count` int(11) NOT NULL,
  `status` enum('Pending','Approved','Dispatched','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `assigned_driver` varchar(128) DEFAULT NULL,
  `driver_contact` varchar(64) DEFAULT NULL,
  `dispatch_time` datetime DEFAULT NULL,
  `arrival_time` datetime DEFAULT NULL,
  `odometer_start` int(11) DEFAULT NULL,
  `odometer_end` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vehicle_reservations`
--

INSERT INTO `vehicle_reservations` (`id`, `user_id`, `reservation_ref`, `vehicle_registration_id`, `vehicle_plate`, `trip_date`, `pickup_datetime`, `dropoff_datetime`, `pickup_location`, `dropoff_location`, `requester_name`, `purpose`, `passengers_count`, `status`, `assigned_driver`, `driver_contact`, `dispatch_time`, `arrival_time`, `odometer_start`, `odometer_end`, `notes`, `created_at`, `updated_at`) VALUES
(154, 'USR-B15A6F2B', 'VR25101317295078A709', 'REG-20250917-1D7E53', 'ABC-1234', '2025-10-13', '2025-10-13 23:29:00', '2025-10-13 13:29:00', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', 'University of the Philippines Diliman, Kristong Hari Street, Pook Dagohoy, UP Campus, 4th District, Quezon City, Eastern Manila District, Metro Manila, 1100, Philippines', 'jack daniels', 'kakain kami', 4, 'Dispatched', 'John Doe', '', '2025-10-13 17:30:04', NULL, 0, NULL, NULL, '2025-10-13 15:29:50', '2025-10-13 15:30:04'),
(156, 'USR-895E821C', 'VR251014092531DD2BFD', 'REG-20250917-C34E96', 'ZXC-9876', '2025-10-17', '2025-10-17 15:24:00', '2025-10-17 18:24:00', 'Bestlink College of the Philippines, Quirino Highway, Quezon City, Metro Manila, Philippines', 'University of the Philippines Diliman, Fatima Street, Quezon City, Metro Manila, Philippines', 'James Smit', 'kakain kami', 5, 'Cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-14 07:25:31', '2025-10-14 07:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_reservations_history`
--

CREATE TABLE `vehicle_reservations_history` (
  `id` int(11) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `reservation_ref` varchar(32) NOT NULL,
  `vehicle_registration_id` varchar(128) NOT NULL,
  `vehicle_plate` varchar(64) NOT NULL,
  `trip_date` date NOT NULL,
  `pickup_datetime` datetime NOT NULL,
  `dropoff_datetime` datetime NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `requester_name` varchar(128) NOT NULL,
  `purpose` text DEFAULT NULL,
  `passengers_count` int(11) NOT NULL,
  `status` enum('Pending','Approved','Dispatched','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `assigned_driver` varchar(128) DEFAULT NULL,
  `driver_contact` varchar(64) DEFAULT NULL,
  `dispatch_time` datetime DEFAULT NULL,
  `arrival_time` datetime DEFAULT NULL,
  `odometer_start` int(11) DEFAULT NULL,
  `odometer_end` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `moved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vehicle_reservations_history`
--

INSERT INTO `vehicle_reservations_history` (`id`, `user_id`, `reservation_ref`, `vehicle_registration_id`, `vehicle_plate`, `trip_date`, `pickup_datetime`, `dropoff_datetime`, `pickup_location`, `dropoff_location`, `requester_name`, `purpose`, `passengers_count`, `status`, `assigned_driver`, `driver_contact`, `dispatch_time`, `arrival_time`, `odometer_start`, `odometer_end`, `notes`, `created_at`, `updated_at`, `moved_at`) VALUES
(142, 'USR-895E821C', 'VR251010133628C3AE52', 'REG-20250917-1D7E53', 'ABC-1234', '2025-10-10', '2025-10-10 19:36:00', '2025-10-10 20:36:00', '14.59950, 120.98420', '14.62636, 121.02548', 'James Smit', 'kakain kami', 4, 'Completed', 'John Doe', '23', '2025-10-10 13:36:00', '2025-10-10 13:36:00', NULL, 2, '', '2025-10-10 11:36:28', '2025-10-10 11:36:53', '2025-10-10 11:36:53'),
(143, 'USR-895E821C', 'VR2510101344165905B6', 'REG-20250917-1D7E53', 'ABC-1234', '2025-10-10', '2025-10-10 19:44:00', '2025-10-10 20:44:00', 'C. Arellano Street, Santa Mesa, Sixth District, Manila, Capital District, Metro Manila, 1016, Philippines', 'Francisco Balagtas Elementary School, Ipil Street, Santa Cruz, Third District, Manila, Capital District, Metro Manila, 1003, Philippines', 'James Smit', 'kakain kami', 4, 'Completed', 'John Doe', '23', '2025-10-10 13:48:00', '2025-10-10 14:20:00', NULL, 2, '23', '2025-10-10 11:44:16', '2025-10-10 12:20:46', '2025-10-10 12:20:46'),
(145, 'USR-B15A6F2B', 'VR251010142222A4FED1', 'REG-20250917-C34E96', 'ZXC-9876', '2025-10-10', '2025-10-10 20:22:00', '2025-10-10 21:22:00', 'JG Plaza, 718, P. Paterno Street, Barangay 307, Quiapo, Third District, Manila, Capital District, Metro Manila, 1001, Philippines', 'One Faith Ministries, 4427, Santol Extension, 508, Santa Mesa, Sixth District, Manila, Capital District, Metro Manila, 1016, Philippines', 'jack daniels', 'kakain kami', 5, 'Completed', 'Richard William', '21', '2025-10-10 14:27:00', '2025-10-11 15:19:00', 21, 12313, 'galing', '2025-10-10 12:22:22', '2025-10-11 13:19:50', '2025-10-11 13:19:50'),
(146, 'USR-B15A6F2B', 'VR251011152153F0FB18', 'REG-20250917-C34E96', 'ZXC-9876', '2025-10-11', '2025-10-11 21:21:00', '2025-10-11 23:21:00', 'JG Plaza, 718, P. Paterno Street, Barangay 307, Quiapo, Third District, Manila, Capital District, Metro Manila, 1001, Philippines', 'Cosmopoltian Memorial Chapels, Gregorio Araneta Avenue, Araneta Village, DoÃ±a Imelda, Galas, 4th District, Quezon City, Eastern Manila District, Metro Manila, 1113, Philippines', 'jack daniels', 'kakain kami', 5, 'Completed', 'Richard William', '23', '2025-10-13 13:41:00', '2025-10-13 13:46:00', NULL, 786, 'hjjb', '2025-10-11 13:21:53', '2025-10-13 11:46:55', '2025-10-13 11:46:55'),
(147, 'USR-895E821C', 'VR251013132431CAFEF4', 'REG-20250917-1D7E53', 'ABC-1234', '2025-10-13', '2025-10-13 19:24:00', '2025-10-13 21:24:00', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', 'Sauyo, 6th District, Quezon City, Eastern Manila District, Metro Manila, 1116, Philippines', 'James Smit', 'kakain kami', 4, 'Completed', 'John Doe', '3424', '2025-10-13 13:24:00', '2025-10-13 11:35:40', NULL, 12345, 'Trip completed via app', '2025-10-13 11:24:31', '2025-10-13 11:35:39', '2025-10-13 11:35:39'),
(149, 'USR-895E821C', 'VR251013134719083789', 'REG-20250917-1D7E53', 'ABC-1234', '2025-10-13', '2025-10-13 19:47:00', '2025-10-13 21:47:00', 'JG Plaza, 718, P. Paterno Street, Barangay 307, Quiapo, Third District, Manila, Capital District, Metro Manila, 1001, Philippines', 'Road 12, Santa Mesa, Sixth District, Manila, Capital District, Metro Manila, 1016, Philippines', 'James Smit', 'kakain kami', 4, 'Completed', 'John Doe', '', '2025-10-13 13:47:32', '2025-10-13 11:48:27', 0, 12345, 'Trip completed via app', '2025-10-13 11:47:19', '2025-10-13 11:48:27', '2025-10-13 11:48:27'),
(152, 'USR-895E821C', 'VR2510131414293DC5C8', 'REG-20250917-1D7E53', 'ABC-1234', '2025-10-13', '2025-10-13 20:14:00', '2025-10-13 22:14:00', 'JG Plaza, 718, P. Paterno Street, Barangay 307, Quiapo, Third District, Manila, Capital District, Metro Manila, 1001, Philippines', 'J. Posadas Street, Barrio Puso, Santa Ana, Sixth District, Manila, Capital District, Metro Manila, 1009, Philippines', 'James Smit', 'kakain kami', 4, 'Completed', 'John Doe', '', '2025-10-13 14:14:45', '2025-10-13 14:18:00', 0, 0, NULL, '2025-10-13 12:14:30', '2025-10-13 12:18:11', '2025-10-13 12:18:11'),
(153, 'USR-895E821C', 'VR251013152018E19355', 'REG-20250917-1D7E53', 'ABC-1234', '2025-10-13', '2025-10-13 21:20:00', '2025-10-13 23:20:00', 'Bestlink College of the Philippines, Quirino Highway, Santa Monica, 5th District, Quezon City, Eastern Manila District, Metro Manila, 1117, Philippines', 'University of the Philippines Diliman, Kristong Hari Street, Pook Dagohoy, UP Campus, 4th District, Quezon City, Eastern Manila District, Metro Manila, 1100, Philippines', 'James Smit', 'kakain kami', 4, 'Completed', 'John Doe', '', '2025-10-13 15:20:29', '2025-10-13 17:15:00', 0, 0, NULL, '2025-10-13 13:20:19', '2025-10-13 15:16:15', '2025-10-13 15:16:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_admin_uid` (`firebase_uid`);

--
-- Indexes for table `cost_analysis`
--
ALTER TABLE `cost_analysis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `reservation_ref` (`reservation_ref`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_performance`
--
ALTER TABLE `driver_performance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_locations`
--
ALTER TABLE `saved_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_vehicle` (`vehicle_registration_id`),
  ADD KEY `reservation_ref` (`reservation_ref`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_insurance`
--
ALTER TABLE `vehicle_insurance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_reservations`
--
ALTER TABLE `vehicle_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_ref` (`reservation_ref`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_vehicle` (`vehicle_registration_id`),
  ADD KEY `idx_pickup` (`pickup_datetime`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `vehicle_reservations_history`
--
ALTER TABLE `vehicle_reservations_history`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cost_analysis`
--
ALTER TABLE `cost_analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `driver_performance`
--
ALTER TABLE `driver_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_locations`
--
ALTER TABLE `saved_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=329;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `vehicle_insurance`
--
ALTER TABLE `vehicle_insurance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `vehicle_reservations`
--
ALTER TABLE `vehicle_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `vehicle_reservations_history`
--
ALTER TABLE `vehicle_reservations_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
