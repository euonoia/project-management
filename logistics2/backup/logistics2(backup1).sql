-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 03:53 PM
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator','driver') DEFAULT 'operator'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `firstname`, `lastname`, `email`, `password`, `role`) VALUES
(4, 'USR-7FBAD23F', 'Ken', 'Española', 'ken@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'operator');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `registration_id` varchar(50) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `owner_firstname` varchar(100) DEFAULT NULL,
  `owner_lastname` varchar(100) DEFAULT NULL,
  `vehicle_plate` varchar(50) DEFAULT NULL,
  `conduction_sticker` varchar(50) DEFAULT NULL,
  `car_brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `chassis_number` varchar(100) DEFAULT NULL,
  `engine_number` varchar(100) DEFAULT NULL,
  `mv_file_number` varchar(100) DEFAULT NULL,
  `lto_orcr_number` varchar(100) DEFAULT NULL,
  `registration_expiry` date DEFAULT NULL,
  `ltfrb_case_number` varchar(100) DEFAULT NULL,
  `ltfrb_franchise_expiry` date DEFAULT NULL,
  `tnvs_accreditation_number` varchar(100) DEFAULT NULL,
  `tnvs_expiry` date DEFAULT NULL,
  `passenger_capacity` int(11) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `current_mileage` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `registration_id`, `user_id`, `owner_firstname`, `owner_lastname`, `vehicle_plate`, `conduction_sticker`, `car_brand`, `model`, `year`, `vehicle_type`, `color`, `chassis_number`, `engine_number`, `mv_file_number`, `lto_orcr_number`, `registration_expiry`, `ltfrb_case_number`, `ltfrb_franchise_expiry`, `tnvs_accreditation_number`, `tnvs_expiry`, `passenger_capacity`, `fuel_type`, `current_mileage`, `created_at`) VALUES
(35, 'REG-20250819-8625C0', 'USR-7FBAD23F', 'Ken', 'Española', '123', '123', '123', '123', '123', 'suv', '123', '123', '123', '123', '123', '2025-08-21', '123', '2025-08-19', '231', '2025-08-19', 123, 'diesel', 123, '2025-08-19 13:42:56');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_id` (`registration_id`);

--
-- Indexes for table `vehicle_insurance`
--
ALTER TABLE `vehicle_insurance`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `vehicle_insurance`
--
ALTER TABLE `vehicle_insurance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
