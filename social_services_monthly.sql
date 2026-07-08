-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2026 at 05:07 AM
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
-- Database: `social_services_monthly`
--

-- --------------------------------------------------------

--
-- Table structure for table `ag_offices`
--

CREATE TABLE `ag_offices` (
  `id` int(11) NOT NULL,
  `district` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ag_offices`
--

INSERT INTO `ag_offices` (`id`, `district`, `name`) VALUES
(1, 'Kurunegala', 'අලව්ව (Alawwa)'),
(2, 'Kurunegala', 'අඹන්පොළ (Ambanpola)'),
(3, 'Kurunegala', 'බමුණාකොටුව (Bamunakotuwa)'),
(4, 'Kurunegala', 'බිංගිරිය (Bingiriya)'),
(5, 'Kurunegala', 'ඇහැටුවැව (Ehetuwewa)'),
(6, 'Kurunegala', 'ගල්ගමුව (Galgamuwa)'),
(7, 'Kurunegala', 'ගනේවත්ත (Ganewatta)'),
(8, 'Kurunegala', 'ගිරිබාව (Giribawa)'),
(9, 'Kurunegala', 'ඉබ්බාගමුව (Ibbagamuwa)'),
(10, 'Kurunegala', 'කොබෙයිගනේ (Kobeigane)'),
(11, 'Kurunegala', 'කොටවෙහෙර (Kotavehera)'),
(12, 'Kurunegala', 'කුලියාපිටිය නැගෙනහිර (Kuliyapitiya East)'),
(13, 'Kurunegala', 'කුලියාපිටිය බටහිර (Kuliyapitiya West)'),
(14, 'Kurunegala', 'කුරුණෑගල (Kurunegala)'),
(15, 'Kurunegala', 'මහව (Maho)'),
(16, 'Kurunegala', 'මල්ලවපිටිය (Mallawapitiya)'),
(17, 'Kurunegala', 'මස්පොත (Maspotha)'),
(18, 'Kurunegala', 'මාවතගම (Mawathagama)'),
(19, 'Kurunegala', 'නාරම්මල (Narammala)'),
(20, 'Kurunegala', 'නිකවැරටිය (Nikaweratiya)'),
(21, 'Kurunegala', 'පඬුවස්නුවර බටහිර (Panduwasnuwara West)'),
(22, 'Kurunegala', 'පන්නල (Pannala)'),
(23, 'Kurunegala', 'පොල්ගහවෙල (Polgahawela)'),
(24, 'Kurunegala', 'පොල්පිතිගම (Polpithigama)'),
(25, 'Kurunegala', 'රස්නායකපුර (Rasnayakapura)'),
(26, 'Kurunegala', 'රිදීගම (Rideegama)'),
(27, 'Kurunegala', 'උඩුබැද්දාව (Udubaddawa)'),
(28, 'Kurunegala', 'වාරියපොළ (Wariyapola)'),
(29, 'Kurunegala', 'වීරඹුගෙදර (Weerambugedera)'),
(30, 'Kurunegala', 'පඬුවස්නුවර නැගෙනහිර (Panduwasnuwara East)'),
(31, 'Puttalam', 'ආණමඩුව (Anamaduwa)'),
(32, 'Puttalam', 'ආරච්චිකට්ටුව (Arachchikattuwa)'),
(33, 'Puttalam', 'හලාවත (Chilaw)'),
(34, 'Puttalam', 'දංකොටුව (Dankotuwa)'),
(35, 'Puttalam', 'කල්පිටිය (Kalpitiya)'),
(36, 'Puttalam', 'කරුවලගස්වැව (Karuwalagaswewa)'),
(37, 'Puttalam', 'මාදම්පේ (Madampe)'),
(38, 'Puttalam', 'මහකුඹුක්කඩවල (Mahakumbukkadawala)'),
(39, 'Puttalam', 'මහවැව (Mahawewa)'),
(40, 'Puttalam', 'මුන්දලම (Mundel)'),
(41, 'Puttalam', 'නාත්තන්ඩිය (Nattandiya)'),
(42, 'Puttalam', 'නවගත්තේගම (Nawagattegama)'),
(43, 'Puttalam', 'පල්ලම (Pallama)'),
(44, 'Puttalam', 'පුත්තලම (Puttalam)'),
(45, 'Puttalam', 'වනතවිල්ලුව (Vanathavilluwa)'),
(46, 'Puttalam', 'වෙන්නප්පුව (Wennappuwa)');

-- --------------------------------------------------------

--
-- Table structure for table `assistance_records`
--

CREATE TABLE `assistance_records` (
  `id` int(11) NOT NULL,
  `ag_office_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `assistance_type` enum('financial','equipment') NOT NULL,
  `category` varchar(255) NOT NULL,
  `estimated_beneficiaries` int(11) DEFAULT 0,
  `actual_beneficiaries` int(11) DEFAULT 0,
  `amount` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assistance_records`
--

INSERT INTO `assistance_records` (`id`, `ag_office_id`, `year`, `month`, `assistance_type`, `category`, `estimated_beneficiaries`, `actual_beneficiaries`, `amount`) VALUES
(1, 1, 2026, 4, 'financial', 'මහජනාධාර', 10, 7, 7000.00),
(2, 1, 2026, 4, 'financial', 'පිලිකාධාර', 0, 0, 0.00),
(3, 1, 2026, 4, 'financial', 'තැලසීමියාධාර', 0, 0, 0.00),
(4, 1, 2026, 4, 'financial', 'ක්ෂය රෝගාධාර', 0, 0, 0.00),
(5, 1, 2026, 4, 'financial', 'ලාදුරු ආධාර', 0, 0, 0.00),
(6, 1, 2026, 4, 'financial', 'සිසුමිණ ශිෂ්‍යාධාර', 0, 0, 0.00),
(7, 1, 2026, 4, 'financial', 'විශේෂ වෛද්‍යාධාර', 0, 0, 0.00),
(8, 1, 2026, 4, 'financial', 'වකුගඩු ආධාර', 0, 0, 0.00),
(9, 1, 2026, 5, 'financial', 'මහජනාධාර', 10, 5, 500.00),
(10, 1, 2026, 5, 'financial', 'පිලිකාධාර', 0, 0, 0.00),
(11, 1, 2026, 5, 'financial', 'තැලසීමියාධාර', 0, 0, 0.00),
(12, 1, 2026, 5, 'financial', 'ක්ෂය රෝගාධාර', 0, 0, 0.00),
(13, 1, 2026, 5, 'financial', 'ලාදුරු ආධාර', 0, 0, 0.00),
(14, 1, 2026, 5, 'financial', 'සිසුමිණ ශිෂ්‍යාධාර', 0, 0, 0.00),
(15, 1, 2026, 5, 'financial', 'විශේෂ වෛද්‍යාධාර', 0, 0, 0.00),
(16, 1, 2026, 5, 'financial', 'වකුගඩු ආධාර', 0, 0, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ag_offices`
--
ALTER TABLE `ag_offices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assistance_records`
--
ALTER TABLE `assistance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_record` (`ag_office_id`,`year`,`month`,`assistance_type`,`category`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ag_offices`
--
ALTER TABLE `ag_offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `assistance_records`
--
ALTER TABLE `assistance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assistance_records`
--
ALTER TABLE `assistance_records`
  ADD CONSTRAINT `assistance_records_ibfk_1` FOREIGN KEY (`ag_office_id`) REFERENCES `ag_offices` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
