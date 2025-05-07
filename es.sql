-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 10:01 AM
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
-- Database: `es`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_offer_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `status` enum('pending','reviewed','shortlisted','rejected','hired') DEFAULT 'pending',
  `cover_letter` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_offers`
--

CREATE TABLE `job_offers` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `location` enum('Bukidnon','Manolo Fortich','Damilag','Phillips') NOT NULL,
  `job_type` varchar(50) NOT NULL,
  `salary_range` varchar(50) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `status` enum('active','closed','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed','blocked') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Job Portal', 'Name of the website', NULL),
(2, 'max_login_attempts', '5', 'Maximum login attempts before account lockout', NULL),
(3, 'session_timeout', '3600', 'Session timeout in seconds', NULL),
(4, 'maintenance_mode', 'false', 'Whether the site is in maintenance mode', NULL),
(5, 'registration_enabled', 'true', 'Whether new user registration is enabled', NULL),
(6, 'default_user_role', 'jobseeker', 'Default role for new users', NULL),
(7, 'max_job_postings', '10', 'Maximum number of job postings per employer', NULL),
(8, 'max_applications', '50', 'Maximum number of applications per jobseeker', NULL),
(9, 'allowed_locations', 'Bukidnon,Manolo Fortich,Damilag,Phillips', 'Allowed job locations', NULL),
(10, 'job_types', 'full-time,part-time,contract,internship', 'Allowed job types', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `terms_agreement`
--

CREATE TABLE `terms_agreement` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agreed` tinyint(1) DEFAULT 0,
  `agreed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('jobseeker','employer','admin') NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `verification_code` varchar(6) DEFAULT NULL,
  `verification_code_expires` datetime DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `two_factor_secret`, `two_factor_enabled`, `verification_code`, `verification_code_expires`, `last_login`, `profile_completed`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NULL, 0, NULL, NULL, NULL, 0, '2025-05-06 10:29:53', '2025-05-06 10:29:53');

-- --------------------------------------------------------

--
-- Table structure for table `user_credentials`
--

CREATE TABLE `user_credentials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `employment_status` enum('student','graduate','employed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_activity_logs_created_at` (`created_at`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_user_id` (`user_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_offer_id` (`job_offer_id`),
  ADD KEY `applicant_id` (`applicant_id`),
  ADD KEY `resume_id` (`resume_id`),
  ADD KEY `idx_job_applications_status` (`status`);

--
-- Indexes for table `job_offers`
--
ALTER TABLE `job_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `idx_job_offers_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_security_logs_created_at` (`created_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `terms_agreement`
--
ALTER TABLE `terms_agreement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- Indexes for table `user_credentials`
--
ALTER TABLE `user_credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_offers`
--
ALTER TABLE `job_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `terms_agreement`
--
ALTER TABLE `terms_agreement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_credentials`
--
ALTER TABLE `user_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`),
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `job_applications_ibfk_3` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`);

--
-- Constraints for table `job_offers`
--
ALTER TABLE `job_offers`
  ADD CONSTRAINT `job_offers_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `terms_agreement`
--
ALTER TABLE `terms_agreement`
  ADD CONSTRAINT `terms_agreement_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_credentials`
--
ALTER TABLE `user_credentials`
  ADD CONSTRAINT `user_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
