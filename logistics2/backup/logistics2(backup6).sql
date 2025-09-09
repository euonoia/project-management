-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2025 at 10:02 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `firebase_uid` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(100) NOT NULL DEFAULT '',
  `lastname` varchar(100) NOT NULL DEFAULT '',
  `role` varchar(50) NOT NULL DEFAULT 'admin',
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
(5, 'HEft8XrUAlhezOsvad6PKW1lV7D2', 'kenpogi@email.com', '', '', 'admin', '2025-08-21 14:03:01');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cost_analysis`
--

INSERT INTO `cost_analysis` (`id`, `user_id`, `registration_id`, `reservation_ref`, `distance_km`, `estimated_time`, `driver_earnings`, `passenger_fare`, `incentives`, `created_at`) VALUES
(5, 'USR-8863E8D9', 'REG-20250820-5E9813', 'VR25083109355407E75A', 490.05, '12 hours 15 minutes', 7401.00, 7431.00, 20.00, '2025-08-31 15:35:58'),
(6, 'USR-ACE941CB', 'REG-20250820-5E9813', 'VR250831093653DD60A6', 193.06, '4 hours 50 minutes', 2947.00, 2977.00, 20.00, '2025-08-31 15:36:57'),
(7, 'USR-C3A38CC6', 'REG-20250820-9AC8D8', 'VR25083109400948E017', 505.07, '12 hours 38 minutes', 7627.00, 7657.00, 20.00, '2025-08-31 15:40:14'),
(8, '1hYOFnvGd2P8nQ0DVQZzXiv0eEF3', 'REG-20250822-42E922', 'VR250831094920A57EC1', 212.88, '5 hours 19 minutes', 3243.00, 3273.00, 20.00, '2025-08-31 15:49:24');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `registration_id`, `user_id`, `mv_file_number`, `lto_orcr_number`, `registration_expiry`, `ltfrb_case_number`, `ltfrb_franchise_expiry`, `tnvs_accreditation_number`, `tnvs_expiry`, `passenger_capacity`, `fuel_type`, `created_at`, `updated_at`) VALUES
(9, 'REG-20250820-5E9813', 'USR-C3A38CC6', '123', '321', '2025-08-20', '312', '2025-08-20', '321', '2025-08-20', '312', 'gasoline', '2025-08-20 07:43:27', '2025-08-20 07:43:27'),
(10, 'REG-20250820-9AC8D8', 'USR-8863E8D9', '231', '231', '2025-08-20', '123', '2025-08-20', '312', '2025-08-20', '123', 'gasoline', '2025-08-20 11:29:08', '2025-08-20 11:29:08'),
(11, 'REG-20250821-01F032', 'USR-ACE941CB', '312', '231', '2025-08-22', '231', '2025-08-22', '123', '2025-08-22', '123', 'gasoline', '2025-08-21 16:52:41', '2025-08-21 16:52:41'),
(12, 'REG-20250822-42E922', 'USR-C7F57872', '312', '123', '2025-08-22', '123', '2025-08-22', '231', '2025-08-22', '123', 'gasoline', '2025-08-22 04:35:34', '2025-08-22 04:35:34');

-- --------------------------------------------------------

--
-- Table structure for table `saved_locations`
--

CREATE TABLE `saved_locations` (
  `id` int(11) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `vehicle_registration_id` varchar(128) NOT NULL,
  `type` enum('pickup','dropoff','other') DEFAULT 'other',
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_locations`
--

INSERT INTO `saved_locations` (`id`, `user_id`, `vehicle_registration_id`, `type`, `latitude`, `longitude`, `address`, `created_at`) VALUES
(139, 'USR-8863E8D9', 'REG-20250820-5E9813', 'pickup', 14.5348194, 120.9835441, 'Mall of Asia, Globe Rotonda, Metropolitan Park, Barangay 76, Zone 10, District 1, Pasay, Southern Manila District, Metro Manila, 1308, Philippines', '2025-08-31 07:35:54'),
(140, 'USR-8863E8D9', 'REG-20250820-5E9813', 'dropoff', 10.8657111, 123.4867509, 'Visayas, Negros Island Region, Philippines', '2025-08-31 07:35:54'),
(141, 'USR-ACE941CB', 'REG-20250820-5E9813', 'pickup', 14.7291423, 121.0367323, 'Bestlink College of the Philippines - Criminology Department, Heavenly Drive, San Agustin, 5th District, Caloocan, Eastern Manila District, Metro Manila, 1400, Philippines', '2025-08-31 07:36:53'),
(142, 'USR-ACE941CB', 'REG-20250820-5E9813', 'dropoff', 16.4119905, 120.5933719, 'Baguio, Cordillera Administrative Region, 2600, Philippines', '2025-08-31 07:36:53'),
(143, 'USR-C3A38CC6', 'REG-20250820-9AC8D8', 'pickup', 14.7291423, 121.0367323, 'Bestlink College of the Philippines - Criminology Department, Heavenly Drive, San Agustin, 5th District, Caloocan, Eastern Manila District, Metro Manila, 1400, Philippines', '2025-08-31 07:40:09'),
(144, 'USR-C3A38CC6', 'REG-20250820-9AC8D8', 'dropoff', 10.8657111, 123.4867509, 'Visayas, Negros Island Region, Philippines', '2025-08-31 07:40:09'),
(145, '1hYOFnvGd2P8nQ0DVQZzXiv0eEF3', 'REG-20250822-42E922', 'pickup', 14.5348194, 120.9835441, 'Mall of Asia, Globe Rotonda, Metropolitan Park, Barangay 76, Zone 10, District 1, Pasay, Southern Manila District, Metro Manila, 1308, Philippines', '2025-08-31 07:49:19'),
(146, '1hYOFnvGd2P8nQ0DVQZzXiv0eEF3', 'REG-20250822-42E922', 'dropoff', 16.4119905, 120.5933719, 'Baguio, Cordillera Administrative Region, 2600, Philippines', '2025-08-31 07:49:19');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `firstname`, `lastname`, `age`, `gender`, `contact`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(6, 'USR-C3A38CC6', 'Ken', 'Española', '', '', '', 'kenleibron@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'driver', '2025-08-20 07:14:08', '2025-08-20 07:14:08'),
(8, 'USR-8863E8D9', 'Ken', 'pogi', '', '', '', 'ken@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'driver', '2025-08-20 10:27:11', '2025-08-20 11:29:08'),
(9, 'USR-ACE941CB', 'lebron', 'james', '', '', '', 'lebron@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', '2025-08-20 11:52:29', '2025-08-21 16:57:24'),
(10, 'USR-C7F57872', 'lebron', 'james', '', '', '', 'lakers@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'driver', '2025-08-21 13:44:18', '2025-08-22 04:35:34'),
(11, 'USR-60991B87', 'ako', 'si', '', '', '', 'lele@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'user', '2025-08-22 04:36:32', '2025-08-22 04:36:32');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `registration_id`, `user_id`, `conduction_sticker`, `vehicle_plate`, `car_brand`, `model`, `year`, `vehicle_type`, `color`, `passenger_capacity`, `chassis_number`, `engine_number`, `fuel_type`, `current_mileage`, `created_at`, `updated_at`) VALUES
(10, 'REG-20250820-5E9813', 'USR-C3A38CC6', 'lbgf_234', '123_abc', 'toyota', 'innova', '2020', 'sedan', 'red', '312', '123abcd090', '213bcdv912', 'hybrid', '123', '2025-08-20 07:43:27', '2025-08-20 07:43:27'),
(11, 'REG-20250820-9AC8D8', 'USR-8863E8D9', '123', '123', '123', '123', '123', 'suv', '123', '123', '231', '123', 'electric', '123', '2025-08-20 11:29:08', '2025-08-20 11:29:08'),
(13, 'REG-20250822-42E922', 'USR-C7F57872', '123', 'lebron', '123', '231', '123', 'suv', '312', '123', '123', '123', 'electric', '123', '2025-08-22 04:35:34', '2025-08-22 04:35:34');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_insurance`
--

INSERT INTO `vehicle_insurance` (`id`, `registration_id_insurance`, `user_id`, `insurance_provider`, `policy_number`, `insurance_type`, `coverage_type`, `num_passengers_covered`, `start_date`, `expiration_date`, `premium_amount`, `renewal_reminders`, `status`, `agent_contact_person`, `scanned_copy_path`, `created_at`, `updated_at`) VALUES
(22, 'REG-20250820-5E9813', 'USR-C3A38CC6', '312', '123', '312', '312', 312, '2025-08-20', '2025-08-20', 123.00, 231, 'active', '231', 'logistics2/uploads/insurance/INS_20250820_094327_2f785fa7.jpg', '2025-08-20 07:43:27', '2025-08-20 07:43:27'),
(23, 'REG-20250820-9AC8D8', 'USR-8863E8D9', '231', '123', '231', '123', 123, '2025-08-20', '2025-08-20', 123.00, 123, 'active', '123', 'logistics2/uploads/insurance/INS_20250820_132908_e759953d.jpg', '2025-08-20 11:29:08', '2025-08-20 11:29:08'),
(24, 'REG-20250821-01F032', 'USR-ACE941CB', '123', '123', '123', '123', 123, '2025-08-22', '2025-08-22', 312.00, 2, 'active', '123', 'logistics2/uploads/insurance/INS_20250821_185241_a43392cc.jpg', '2025-08-21 16:52:41', '2025-08-21 16:52:41'),
(25, 'REG-20250822-42E922', 'USR-C7F57872', '123', '123', '123', '123', 123, '2025-08-22', '2025-08-22', 123.00, 123, 'active', '123', 'logistics2/uploads/insurance/INS_20250822_063534_501da06f.jpg', '2025-08-22 04:35:34', '2025-08-22 04:35:34');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_reservations`
--

INSERT INTO `vehicle_reservations` (`id`, `user_id`, `reservation_ref`, `vehicle_registration_id`, `vehicle_plate`, `trip_date`, `pickup_datetime`, `dropoff_datetime`, `pickup_location`, `dropoff_location`, `requester_name`, `purpose`, `passengers_count`, `status`, `assigned_driver`, `driver_contact`, `dispatch_time`, `arrival_time`, `odometer_start`, `odometer_end`, `notes`, `created_at`, `updated_at`) VALUES
(66, 'USR-8863E8D9', 'VR25083109355407E75A', 'REG-20250820-5E9813', '123_abc', '2025-08-31', '2025-08-31 15:35:00', '2025-08-31 17:35:00', 'Mall of Asia, Globe Rotonda, Metropolitan Park, Barangay 76, Zone 10, District 1, Pasay, Southern Manila District, Metro Manila, 1308, Philippines', 'Visayas, Negros Island Region, Philippines', 'boss dian', 'need to transfer goods', 2, 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 07:35:54', '2025-08-31 07:45:09'),
(67, 'USR-ACE941CB', 'VR250831093653DD60A6', 'REG-20250820-5E9813', '123_abc', '2025-08-31', '2025-08-31 15:36:00', '2025-08-31 18:36:00', 'Bestlink College of the Philippines - Criminology Department, Heavenly Drive, San Agustin, 5th District, Caloocan, Eastern Manila District, Metro Manila, 1400, Philippines', 'Baguio, Cordillera Administrative Region, 2600, Philippines', 'boss dian', 'need to transfer goods', 3, 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 07:36:53', '2025-08-31 07:44:58'),
(68, 'USR-C3A38CC6', 'VR25083109400948E017', 'REG-20250820-9AC8D8', '123', '2025-08-31', '2025-08-31 15:39:00', '2025-08-31 19:39:00', 'Bestlink College of the Philippines - Criminology Department, Heavenly Drive, San Agustin, 5th District, Caloocan, Eastern Manila District, Metro Manila, 1400, Philippines', 'Visayas, Negros Island Region, Philippines', 'boss dian', 'need to transfer goods', 3, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 07:40:09', '2025-08-31 07:40:09'),
(69, '1hYOFnvGd2P8nQ0DVQZzXiv0eEF3', 'VR250831094920A57EC1', 'REG-20250822-42E922', 'lebron', '2025-08-31', '2025-08-31 15:48:00', '2025-08-31 17:48:00', 'Mall of Asia, Globe Rotonda, Metropolitan Park, Barangay 76, Zone 10, District 1, Pasay, Southern Manila District, Metro Manila, 1308, Philippines', 'Baguio, Cordillera Administrative Region, 2600, Philippines', 'ako si', 'need to transfer goods', 2, 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-31 07:49:20', '2025-08-31 07:50:07');

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
-- Indexes for table `saved_locations`
--
ALTER TABLE `saved_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_vehicle` (`vehicle_registration_id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cost_analysis`
--
ALTER TABLE `cost_analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `saved_locations`
--
ALTER TABLE `saved_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vehicle_insurance`
--
ALTER TABLE `vehicle_insurance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `vehicle_reservations`
--
ALTER TABLE `vehicle_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
