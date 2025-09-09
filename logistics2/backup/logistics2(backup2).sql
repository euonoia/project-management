-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2025 at 08:01 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `registration_id`, `user_id`, `mv_file_number`, `lto_orcr_number`, `registration_expiry`, `ltfrb_case_number`, `ltfrb_franchise_expiry`, `tnvs_accreditation_number`, `tnvs_expiry`, `passenger_capacity`, `fuel_type`, `created_at`) VALUES
(2, 'REG-20250819-A83690', 'USR-7FBAD23F', '123', '123', '2025-08-20', '231', '2025-08-20', '123', '2025-08-20', '123', 'gasoline', '2025-08-19 17:53:18');

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
(4, 'USR-7FBAD23F', 'Ken', 'Española', 'ken@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'operator'),
(5, 'USR-86B8CA93', 'ken', 'pogi', 'kenleibron@email.com', '827ccb0eea8a706c4c34a16891f84e7b', 'operator');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Dumping data for table `vehicle_insurance`
--

INSERT INTO `vehicle_insurance` (`id`, `registration_id_insurance`, `user_id`, `insurance_provider`, `policy_number`, `insurance_type`, `coverage_type`, `num_passengers_covered`, `start_date`, `expiration_date`, `premium_amount`, `renewal_reminders`, `status`, `agent_contact_person`, `scanned_copy_path`, `created_at`) VALUES
(11, 'REG-20250819-EA79F8', 'USR-7FBAD23F', '231', '123', '123', '123', 123, '2025-08-20', '2025-08-20', 123.00, 123, 'active', '123', 'logistics2/uploads/insurance/INS_20250819_195940_76af5090.jpg', '2025-08-19 17:59:40');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicle_insurance`
--
ALTER TABLE `vehicle_insurance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
