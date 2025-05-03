-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 06:30 AM
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
-- Database: `trasic`
--

-- --------------------------------------------------------

--
-- Table structure for table `job_offers`
--

CREATE TABLE `job_offers` (
  `id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `position_title` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `job_type` enum('Full-Time','Part-Time','Contract','Internship') DEFAULT NULL,
  `date_posted` date DEFAULT NULL,
  `apply_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_offers`
--

INSERT INTO `job_offers` (`id`, `company_name`, `position_title`, `location`, `job_type`, `date_posted`, `apply_link`) VALUES
(1, 'Del Monte Philippines Inc.', 'Quality Assurance Analyst', 'Manolo Fortich, Bukidnon', 'Full-Time', '2025-03-20', 'https://delmonteph.com/careers/qa-analyst'),
(2, 'Sumifru Philippines Corp.', 'Production Supervisor', 'Quezon, Bukidnon', 'Full-Time', '2025-03-18', 'https://sumifru.com.ph/careers/production-supervisor'),
(3, 'Lantapan Agri Ventures Inc.', 'Agriculturist', 'Lantapan, Bukidnon', 'Full-Time', '2025-03-15', 'https://lantapanagri.com/jobs/agriculturist'),
(4, 'Kasilak Development Foundation', 'Community Development Officer', 'Valencia City, Bukidnon', 'Contract', '2025-03-10', 'https://kasilak.org.ph/jobs/cdo'),
(5, 'Monde Nissin Corporation', 'Machine Operator', 'Malaybalay City, Bukidnon', 'Full-Time', '2025-03-12', 'https://mondenissin.com.ph/apply/machine-operator'),
(6, 'San Miguel Foods Inc.', 'Warehouse Staff', 'Maramag, Bukidnon', 'Part-Time', '2025-03-17', 'https://sanmiguelfoods.com.ph/careers/warehouse-staff'),
(7, 'Dole Philippines Inc.', 'HR Assistant', 'Quezon, Bukidnon', 'Full-Time', '2025-03-11', 'https://dole.com.ph/jobs/hr-assistant'),
(8, 'Universal Robina Corporation', 'Electrical Technician', 'Valencia City, Bukidnon', 'Full-Time', '2025-03-14', 'https://urc.com.ph/careers/electrical-tech'),
(9, 'La Fortuna Farms', 'Farm Supervisor', 'Don Carlos, Bukidnon', 'Full-Time', '2025-03-09', 'https://lafortuna.com.ph/jobs/supervisor'),
(10, 'Northern Mindanao Medical Center', 'Medical Technologist', 'Malaybalay City, Bukidnon', 'Full-Time', '2025-03-13', 'https://nmmc.gov.ph/careers/med-tech'),
(11, 'Mindanao State University', 'IT Support Specialist', 'Maramag, Bukidnon', 'Contract', '2025-03-10', 'https://msu.edu.ph/jobs/it-support'),
(12, 'Bukidnon State University', 'Guidance Counselor', 'Malaybalay City, Bukidnon', 'Full-Time', '2025-03-08', 'https://buksu.edu.ph/jobs/guidance'),
(13, 'Mount Kitanglad Agri Dev Corp.', 'Environmental Officer', 'Lantapan, Bukidnon', 'Full-Time', '2025-03-07', 'https://kitangladagri.com/jobs/environmental'),
(14, 'Taipan Agri Ventures', 'Finance Associate', 'Kibawe, Bukidnon', 'Full-Time', '2025-03-16', 'https://taipan-agri.com/careers/finance'),
(15, 'Philippine Carabao Center', 'Research Assistant', 'Central Mindanao University, Maramag', 'Internship', '2025-03-19', 'https://carabaocenter.gov.ph/internship'),
(16, 'Nestle Philippines Inc.', 'Logistics Coordinator', 'Cagayan de Oro (near Bukidnon)', 'Full-Time', '2025-03-21', 'https://nestle.com.ph/jobs/logistics'),
(17, 'Green Mindanao Energy Corp.', 'Mechanical Engineer', 'Quezon, Bukidnon', 'Full-Time', '2025-03-22', 'https://greenmindanao.com.ph/apply/engineer'),
(18, 'AgriFarm Solutions', 'Agri Technician', 'Valencia City, Bukidnon', 'Contract', '2025-03-20', 'https://agrifarmsol.com.ph/jobs/tech'),
(19, 'Bukidnon Hydro Energy Corp.', 'Civil Engineer', 'Impasug-ong, Bukidnon', 'Full-Time', '2025-03-23', 'https://bhec.com.ph/careers/civil'),
(20, 'NorMinCorp', 'Marketing Associate', 'Malaybalay City, Bukidnon', 'Part-Time', '2025-03-19', 'https://normincorp.com/jobs/marketing');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'HEHE', '$2y$10$9Q4eSxHrJs66BpJfn//YHOw1ixn4QxJ3.HFXc6NsoYw0NgyrfAJna', '2025-03-13 17:45:46'),
(2, 'mar', '$2y$10$2WnDfpSOZyCVzO2f6U2XJeavRqru7pCSmTXfZHJqgnpiF90k61R4u', '2025-03-13 18:46:33'),
(3, 'althia', '$2y$10$Ur7mFdRF3SsxyZt9vLWAmeh.2PET.9wHPXiMXKTVrtr45OpT8iZVK', '2025-03-24 13:01:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_offers`
--
ALTER TABLE `job_offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_offers`
--
ALTER TABLE `job_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;