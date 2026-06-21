-- Velora Luxury E-commerce Database Schema & Sample Data
-- Suitable for importation in phpMyAdmin (MySQL)
-- Database Name: kritika

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create Database if not exists
CREATE DATABASE IF NOT EXISTS `kritika` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `kritika`;

-- --------------------------------------------------------

--
-- Table structure for table `login` (Admin accounts)
--
DROP TABLE IF EXISTS `login`;
CREATE TABLE IF NOT EXISTS `login` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `USER` varchar(255) NOT NULL UNIQUE,
  `PASSWORD` varchar(255) NOT NULL, -- Supporting plaintext 'kritika' for legacy, and secure hashes
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Sample data for table `login`
--
INSERT INTO `login` (`Id`, `USER`, `PASSWORD`) VALUES
(1, 'kritika', 'kritika');

-- --------------------------------------------------------

--
-- Table structure for table `rate` (Precious metal market valuations)
--
DROP TABLE IF EXISTS `rate`;
CREATE TABLE IF NOT EXISTS `rate` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Date` varchar(255) NOT NULL,
  `Gold` varchar(255) NOT NULL,
  `Silver` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

--
-- Sample data for table `rate`
--
INSERT INTO `rate` (`Id`, `Date`, `Gold`, `Silver`) VALUES
(1, '2026-06-15', '95,000', '1,100'),
(2, '2026-06-16', '96,200', '1,150'),
(3, '2026-06-17', '97,000', '1,200'),
(4, '2026-06-18', '98,000', '1,220'),
(5, '2026-06-19', '98,500', '1,250'),
(6, '2026-06-20', '99,000', '1,280'),
(7, '2026-06-21', '1,01,200', '1,320');

-- --------------------------------------------------------

--
-- Table structure for table `userinfodata` (Customer inquiries & Custom designs)
--
DROP TABLE IF EXISTS `userinfodata`;
CREATE TABLE IF NOT EXISTS `userinfodata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT '',
  `interestedon` varchar(255) DEFAULT '',
  `image` varchar(255) DEFAULT '',
  `comment` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

--
-- Sample data for table `userinfodata`
--
INSERT INTO `userinfodata` (`id`, `user`, `email`, `mobile`, `address`, `code`, `interestedon`, `image`, `comment`) VALUES
(1, 'Hari Bahadur', 'haribahadur@gmail.com', '9861874521', 'Chabahil, Kathmandu', '#03', 'Anklets', '', 'I would like to inquire about the custom delivery timeframe. I want a 24 caret silver anklet weighing 2 tolas.'),
(2, 'Kritika Chhetri', 'kritika@gmail.com', '9874563210', 'Dang', '#01', 'Necklace', '', 'Looking for custom gold polishing alongside the purchase.'),
(3, 'Shyam Paudel', 'shyam.paudel@gmail.com', '9874563210', 'Melamchi', '#02', 'Rings', '', 'Looking for engagement resizing options.'),
(4, 'Binda Chhetri', 'binda.chhetri@gmail.com', '9874563210', 'Kathmandu', '#04', 'Mangalsutra', '', 'Interested in customized rose gold chain length adjustments.');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
