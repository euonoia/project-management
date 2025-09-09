-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2025 at 01:35 PM
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
(10, 'REG-20250820-9AC8D8', 'USR-8863E8D9', '231', '231', '2025-08-20', '123', '2025-08-20', '312', '2025-08-20', '123', 'gasoline', '2025-08-20 11:29:08', '2025-08-20 11:29:08');

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
(8, 'USR-8863E8D9', 'Ken', 'pogi', '', '', '', 'ken@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'driver', '2025-08-20 10:27:11', '2025-08-20 11:29:08');

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
(11, 'REG-20250820-9AC8D8', 'USR-8863E8D9', '123', '123', '123', '123', '123', 'suv', '123', '123', '231', '123', 'electric', '123', '2025-08-20 11:29:08', '2025-08-20 11:29:08');

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
(23, 'REG-20250820-9AC8D8', 'USR-8863E8D9', '231', '123', '231', '123', 123, '2025-08-20', '2025-08-20', 123.00, 123, 'active', '123', 'logistics2/uploads/insurance/INS_20250820_132908_e759953d.jpg', '2025-08-20 11:29:08', '2025-08-20 11:29:08');

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
(4, 'USR-C3A38CC6', 'VR250820103017B41E6', 'REG-20250820-5E9813', '123_abc', '2025-08-20', '2025-08-20 03:03:00', '2025-08-20 06:00:00', 'warehouse', 'mainhouse', 'boss dian', 'need to transfer goods', 1, 'Completed', 'Ken Española', '', '2025-08-20 10:32:00', '2025-08-20 10:32:00', NULL, 5, 'amzing', '2025-08-20 08:30:17', '2025-08-20 08:32:41'),
(5, 'USR-8863E8D9', 'VR25082012505810D67', 'REG-20250820-5E9813', '123_abc', '2025-08-20', '2025-08-20 18:50:00', '2025-08-20 20:50:00', 'warehouse', 'mainhouse', 'lebron', 'pupunta ng lakers', 2, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-20 10:50:58', '2025-08-20 10:50:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vehicle_insurance`
--
ALTER TABLE `vehicle_insurance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `vehicle_reservations`
--
ALTER TABLE `vehicle_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
