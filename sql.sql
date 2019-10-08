v-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 08, 2019 at 10:26 AM
-- Server version: 5.6.41-84.1
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mobacom_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `country_state`
--

CREATE TABLE `country_state` (
  `ref` int(11) NOT NULL,
  `country` int(11) NOT NULL,
  `state` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `country_state`
--

INSERT INTO `country_state` (`ref`, `country`, `state`, `status`, `create_time`, `modify_time`) VALUES
(1, 1, 'Abia', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 1, 'Federal Capital Territory', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 1, 'Adamawa', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 1, 'Akwa Ibom', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 1, 'Anambra', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 1, 'Bauchi', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 1, 'Bayelsa', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 1, 'Benue', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 1, 'Borno', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 1, 'Cross River', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 1, 'Delta', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 1, 'Ebonyi', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 1, 'Edo', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 1, 'Ekiti', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 1, 'Enugu', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(16, 1, 'Gombe', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 1, 'Imo', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(18, 1, 'Jigawa', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(19, 1, 'Kaduna', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(20, 1, 'kano', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(21, 1, 'Katsina', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(22, 1, 'Kebbi', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(23, 1, 'Kogi', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(24, 1, 'Kwara', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(25, 1, 'Lagos', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(26, 1, 'Nassarawa', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(27, 1, 'Niger', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(28, 1, 'Ogun', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(29, 1, 'Ondo', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(30, 1, 'Osun', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(31, 1, 'Oyo', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(32, 1, 'Plateau', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(33, 1, 'Rivers', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(34, 1, 'Sokoto', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(35, 1, 'Taraba', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(36, 1, 'Yobe', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(37, 1, 'Zamfara', 'ACTIVE', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `country_state`
--
ALTER TABLE `country_state`
  ADD PRIMARY KEY (`ref`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `country_state`
--
ALTER TABLE `country_state`
  MODIFY `ref` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
