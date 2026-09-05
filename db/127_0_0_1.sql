-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2026 at 03:04 PM
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
-- Database: `library_inventory_system`
--
CREATE DATABASE IF NOT EXISTS `library_inventory_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `library_inventory_system`;

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `publisher` varchar(150) DEFAULT NULL,
  `publication_year` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isbn` varchar(20) DEFAULT NULL,
  `shelf_location` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `class` varchar(100) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `source_of_fund` varchar(150) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`book_id`, `title`, `author`, `category_id`, `publisher`, `publication_year`, `created_at`, `updated_at`, `isbn`, `shelf_location`, `remarks`, `class`, `pages`, `source_of_fund`, `cost_price`) VALUES
(1, 'asdasd', 'asdasd', 1, 'asdasd', 2020, '2026-02-07 09:44:44', '2026-03-26 11:52:30', '021301239012', 'a1-16', 'sikret', 'fadfadfads', 200, 'purchased', 7000.00),
(5, 'San.Jose', 'demash', 7, 'lorenzo', 2026, '2026-03-26 08:20:17', '2026-03-26 08:20:17', '1233441144', 'a1-19', '', 'k', 200, 'purchased', 1500.00),
(8, 'dasfads', 'fadsfads', 6, 'adsfads', 2020, '2026-03-28 14:27:48', '2026-03-28 14:27:48', '123213123', 'dsfads', 'fadsfads', 'asdas', 1233, 'purchased', 1200.00),
(12, 'christbrown', 'christaw', 6, 'fadsfasddsfads', 2021, '2026-04-11 02:35:20', '2026-04-11 02:59:32', '123213211223', 'dfasdf', 'fasdfasd', 'fadsfasd', 100, 'adsfasdf', 1000.00),
(13, 'dfads', 'dfadsf', 6, 'fadsfads', 2026, '2026-04-12 07:07:52', '2026-04-12 07:07:52', '312312312', 'adsfadsffadsf', 'adsfadsfaadf', 'dsfadsfa', 100, 'purchased', 5000.00),
(15, 'Harry Potter ', 'JK Rowling', 7, 'New York Times', 1999, '2026-04-14 04:33:53', '2026-04-26 02:31:28', 'ISBN-0006', 'A1-A2', 'WOW', 'N/A', 200, 'Purchased', 6500.00),
(16, 'asdfawds', 'adsfadsfadsf', 6, 'asdfadsfg', 2026, '2026-04-14 04:37:56', '2026-04-14 04:37:56', 'adsfasdg', 'asdf', 'wow', 'NA', 100, 'rematch', 100.00),
(17, 'kupal', 'hary', 7, 'lol', 2015, '2026-04-14 05:17:52', '2026-04-14 05:17:52', '2143455623', 'as-1', 'ambot', 'NONE', 20445, 'ambot', 2545.00),
(18, 'asdfg', 'asdgadb', 7, 'dsfh', 2026, '2026-04-14 05:18:52', '2026-04-14 05:18:52', '2332523', 'asdgcbv', 'sadg', 'dsf', 213, 'asdf', 124.00),
(19, 'whattheheck', 'sadfg', 7, 'dsfg', 2026, '2026-04-14 05:22:27', '2026-04-23 14:18:43', '2341', 'adsf', 'dsaf', 'dfadf', 34, 'daf', 321.00),
(21, 'dictionary', 'michael alao', 9, 'dasdas', 1999, '2026-04-14 07:11:19', '2026-04-14 07:11:19', '3213213', 'dsfdsf', 'dsadas', 'adsdasd', 100, 'dasdas', 1500.00),
(23, 'adasd', 'asdfasdf', 6, 'fasdfasd', 2026, '2026-04-18 03:58:17', '2026-04-18 03:58:17', '12312312', 'fadsfasd', 'fadsfasd', 'adsfads', 123123, 'dsafsd', 12312.00),
(26, 'asdasd', 'qeqweqw', 6, 'asdas', 2026, '2026-04-21 08:56:25', '2026-04-21 08:56:59', '12321322', '12321321', '12321', '123213', 12312, 'qeqwe', 1232.00),
(27, 'Art Craft And Physical Education', 'Dr.Parvesh Goel', 6, 'Random Publications LLP', 2022, '2026-04-23 04:58:54', '2026-04-23 05:09:01', '978-93-93-93884-947', 'N/A', 'Hardbound', '613.7 Ar751', 314, 'Purchased', 6131.00),
(28, 'Office Management', 'Elisha Stephens', 6, 'Ed-Tech Press', 2025, '2026-04-23 05:07:38', '2026-04-23 05:07:38', '978-183535-7334-7', 'N/A', 'Hardbound', '653.3 St437', 277, 'Purchased', 6071.00),
(29, 'New book', '', 6, 'dashdas', 2026, '2026-04-23 08:53:07', '2026-04-23 08:53:07', '123123213', 'a17', 'hardbound', 'a', 317, 'purchased', 17000.00),
(30, 'The Great Gatsby', 'Erick Morales', 6, 'ABC Producion', 2001, '2026-04-26 02:25:47', '2026-04-26 02:29:29', 'ISBN-0003', 'A2', 'Available', 'None', 100, 'None', 1200.00),
(31, 'Java Programming', 'Linus Torvalds', 7, 'Codechum Corp.', 1999, '2026-04-26 02:27:11', '2026-04-26 02:29:40', 'ISBN-0004', 'AB-2', 'None', 'None', 150, 'None', 1500.00),
(32, 'The Summer I Turned Pretty', 'Jenny Han', 6, 'Amazon', 2005, '2026-04-26 02:28:23', '2026-04-26 02:29:01', 'ISBN-0001', 'AC-2', 'None', 'None', 250, 'None', 1200.00),
(33, 'Web Development w/ Javascript', 'Christian Malinao', 7, 'WLC Inc.', 2026, '2026-04-26 02:30:54', '2026-04-26 02:30:54', 'ISBN-0005', 'AB-2', 'NONE', 'None', 200, 'nONE', 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `book_copy`
--

CREATE TABLE `book_copy` (
  `copy_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `acquisition_number` varchar(50) DEFAULT NULL,
  `status` enum('Available','Borrowed') DEFAULT 'Available',
  `date_received` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_copy`
--

INSERT INTO `book_copy` (`copy_id`, `book_id`, `acquisition_number`, `status`, `date_received`, `created_at`, `updated_at`) VALUES
(1, 1, '012321312', 'Available', '2020-10-10', '2026-02-08 08:55:34', '2026-04-23 09:09:53'),
(3, 1, '21321321', 'Available', '2026-03-20', '2026-03-20 07:48:13', '2026-04-14 04:28:40'),
(4, 1, '312312312', 'Available', '2026-04-13', '2026-03-20 07:48:24', '2026-04-14 04:00:22'),
(5, 1, '213123122314', 'Available', '2026-03-20', '2026-03-20 07:48:57', '2026-04-25 16:47:00'),
(19, 5, '2312321', 'Available', '2026-04-13', '2026-03-29 17:07:15', '2026-04-23 07:10:46'),
(20, 1, '21321321344112', 'Available', '2026-03-30', '2026-03-29 17:31:39', '2026-04-13 16:15:01'),
(21, 1, '1232132132131', 'Available', '2026-03-30', '2026-03-29 17:40:02', '2026-04-24 15:03:09'),
(24, 1, '123213123444', 'Available', '2026-04-14', '2026-04-14 04:04:19', '2026-04-24 15:45:51'),
(25, 16, '2004213', 'Available', '2026-04-14', '2026-04-14 04:38:49', '2026-05-22 04:32:04'),
(26, 16, '12354', 'Available', '2026-04-14', '2026-04-14 05:19:37', '2026-04-14 05:19:37'),
(27, 13, '1243243', 'Available', '2026-04-14', '2026-04-14 06:12:18', '2026-04-14 06:12:18'),
(28, 21, '213213231231', 'Available', '2026-04-14', '2026-04-14 07:12:19', '2026-04-14 07:12:19'),
(29, 29, '2026-29-01', 'Available', '2026-04-23', '2026-04-23 08:53:52', '2026-04-23 09:09:13'),
(30, 29, '2026-29-02', 'Available', '2026-04-23', '2026-04-23 08:54:08', '2026-04-25 16:50:11'),
(31, 15, 'ACQ-0009', 'Available', '2026-04-25', '2026-04-25 12:00:16', '2026-05-22 07:57:56'),
(32, 15, 'ACQ-0010', 'Available', '2026-04-25', '2026-04-25 12:04:01', '2026-05-03 13:10:49'),
(33, 27, '123', 'Available', '2026-04-25', '2026-04-25 12:11:57', '2026-04-26 02:23:17'),
(34, 5, '1111', 'Available', '2026-04-26', '2026-04-25 16:39:46', '2026-04-26 02:23:30'),
(35, 12, '667', 'Available', '2026-04-26', '2026-04-25 16:40:03', '2026-04-26 02:23:24'),
(36, 1, '1972', 'Available', '2026-04-26', '2026-04-25 16:43:20', '2026-04-25 16:43:20'),
(37, 33, 'ACQ-0001', 'Available', '2026-04-26', '2026-04-26 02:32:04', '2026-05-04 14:42:55'),
(38, 33, 'ACQ-0002', 'Available', '2026-04-26', '2026-04-26 02:32:17', '2026-05-05 13:02:02'),
(39, 32, 'ACQ-0003', 'Available', '2026-04-26', '2026-04-26 02:32:28', '2026-05-05 13:01:08'),
(40, 32, 'ACQ-0004', 'Borrowed', '2026-04-26', '2026-04-26 02:32:40', '2026-05-04 12:41:32'),
(41, 31, 'ACQ-0005', 'Available', '2026-04-26', '2026-04-26 02:33:07', '2026-05-05 13:03:20'),
(42, 31, 'ACQ-0006', 'Available', '2026-04-26', '2026-04-26 02:33:14', '2026-04-26 02:33:14'),
(43, 30, 'ACQ-0007', 'Available', '2026-04-26', '2026-04-26 02:33:33', '2026-05-03 13:10:49'),
(44, 30, 'ACQ-0008', 'Available', '2026-04-26', '2026-04-26 02:33:41', '2026-05-05 13:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `borrower`
--

CREATE TABLE `borrower` (
  `borrower_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `id_type` varchar(50) NOT NULL,
  `Id_number` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `borrower_type` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive','Blocked') DEFAULT 'Active',
  `borrow_limit` int(11) DEFAULT 3,
  `date_registered` date DEFAULT curdate(),
  `remarks` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrower`
--

INSERT INTO `borrower` (`borrower_id`, `first_name`, `last_name`, `id_type`, `Id_number`, `email`, `phone_number`, `address`, `borrower_type`, `status`, `borrow_limit`, `date_registered`, `remarks`, `date_of_birth`, `gender`) VALUES
(1, 'clifford', 'leagspit', 'Driver’s License', '3213213', 'clifford.legaspi@wlcormoc.edu.ph', '09208878204', 'brgy.bagong buhay ormoc, ormoc', 'Student', 'Active', 4, '2026-03-05', NULL, '2025-06-03', 'Other'),
(10, 'michael', 'alao', 'Student ID', '321312312', 'michaelAlao@gmail.com', '21312312', 'brgy.westerrn dado ormoc city', 'Student', 'Active', 4, '2026-03-18', NULL, '2026-04-13', 'Male'),
(11, 'Christian', 'Hanz', 'Student ID', '123213213', 'christiandedil@gmail.com', '02312312', 'bagiong buhay', 'Student', 'Active', 4, '2026-03-24', NULL, '2026-04-13', 'Male'),
(14, 'angelica', 'bulante', 'Student ID', '1111', 'bulante@gmail.com', '90291302', 'brgy.mabini ormoc ormoc', 'Student', 'Active', 4, '2026-04-14', NULL, '2006-09-04', 'Female'),
(16, 'Chris', 'Malinao', 'Student ID', '11113214213', 'christianmalinao@gmail.cm', '0921348213', 'brgy oten', 'Student', 'Active', 4, '2026-04-22', NULL, '2026-04-22', 'Male'),
(21, 'new', 'member', 'Student ID', '1109', 'newbook@gmail.com', '1231231', 'brgy.matagok', 'Student', 'Active', 4, '2026-04-23', NULL, '2010-09-03', 'Male'),
(23, 'sadf', 'sadfsadf', 'Student ID', 'dsf', 'sdfsdf', 'sdfsdf', 'sdfsdf', 'Student', 'Active', 4, '2026-05-04', NULL, '2005-05-04', 'Male');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(6, 'General'),
(7, 'Information Technology'),
(1, 'negativity'),
(8, 'new'),
(9, 'new category'),
(15, 'thefuck');

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `fine_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `days_overdue` int(11) NOT NULL,
  `fine_date` date NOT NULL,
  `status` enum('Unpaid','Paid','Waived') DEFAULT 'Unpaid',
  `payment_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine`
--

INSERT INTO `fine` (`fine_id`, `transaction_id`, `borrower_id`, `amount`, `days_overdue`, `fine_date`, `status`, `payment_date`, `remarks`) VALUES
(2, 28, 14, 30.00, 3, '2026-04-24', 'Paid', '2026-04-24', NULL),
(3, 25, 10, 30.00, 3, '2026-04-24', 'Paid', '2026-04-19', NULL),
(4, 27, 14, 20.00, 2, '2026-04-23', 'Paid', '2026-04-23', NULL),
(5, 29, 14, 40.00, 4, '2026-04-26', 'Paid', '2026-05-22', NULL),
(6, 76, 14, 60.00, 6, '2026-05-11', 'Paid', '2026-05-22', NULL),
(7, 77, 16, 30.00, 3, '2026-05-11', 'Paid', '2026-05-22', NULL),
(8, 55, 11, 190.00, 19, '2026-05-22', 'Paid', '2026-05-22', NULL),
(9, 71, 16, 190.00, 19, '2026-05-22', 'Paid', '2026-05-22', NULL),
(10, 72, 16, 190.00, 19, '2026-05-22', 'Paid', '2026-05-22', NULL),
(11, 73, 16, 190.00, 19, '2026-05-22', 'Paid', '2026-05-22', NULL),
(12, 80, 16, 310.00, 31, '2026-06-30', 'Paid', '2026-06-30', NULL),
(13, 79, 14, 310.00, 31, '2026-06-30', 'Paid', '2026-06-30', NULL),
(14, 81, 14, 310.00, 31, '2026-06-30', 'Paid', '2026-06-30', NULL),
(15, 78, 11, 310.00, 31, '2026-06-30', 'Paid', '2026-06-30', NULL),
(16, 53, 10, 580.00, 58, '2026-06-30', 'Paid', '2026-06-30', NULL),
(17, 83, 16, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(18, 84, 16, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(19, 85, 16, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(20, 86, 16, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(21, 89, 14, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(22, 88, 14, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(23, 90, 14, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(24, 91, 14, 820.00, 82, '2026-07-30', 'Paid', '2026-07-30', NULL),
(25, 95, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-07-03', NULL),
(26, 96, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-07-03', NULL),
(27, 98, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-05-03', NULL),
(28, 97, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-07-03', NULL),
(29, 101, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-08-03', NULL),
(30, 99, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-08-03', NULL),
(31, 102, 14, 840.00, 84, '2026-08-03', 'Paid', '2026-08-03', NULL),
(32, 100, 14, 840.00, 84, '2026-08-03', 'Paid', '2026-08-03', NULL),
(33, 103, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-05-03', NULL),
(34, 104, 14, 530.00, 53, '2026-07-03', 'Paid', '2026-05-03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `copy_id` int(11) NOT NULL,
  `rental_date` date NOT NULL,
  `due_date` date NOT NULL,
  `returned_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `cancelled_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `borrower_id`, `book_id`, `copy_id`, `rental_date`, `due_date`, `returned_date`, `status`, `cancelled_date`) VALUES
(11, 10, 1, 21, '2026-04-13', '2026-04-20', NULL, 'Cancelled', NULL),
(12, 11, 1, 20, '2026-04-16', '2026-04-23', NULL, 'Cancelled', NULL),
(13, 11, 1, 20, '2026-04-15', '2026-04-22', NULL, 'Cancelled', NULL),
(14, 1, 1, 20, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(15, 1, 5, 19, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(16, 10, 5, 19, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(17, 11, 5, 19, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(18, 11, 5, 19, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(20, 11, 5, 19, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(21, 11, 5, 19, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(22, 11, 5, 19, '2026-04-14', '2026-04-21', NULL, 'Cancelled', NULL),
(25, 10, 1, 24, '2026-04-14', '2026-04-21', '2026-04-24', 'Returned', NULL),
(27, 14, 1, 1, '2026-04-14', '2026-04-21', '2026-04-23', 'Returned', NULL),
(28, 14, 1, 21, '2026-04-14', '2026-04-21', '2026-04-24', 'Returned', NULL),
(29, 14, 1, 5, '2026-04-14', '2026-04-21', '2026-04-26', 'Returned', NULL),
(30, 1, 5, 19, '2026-04-23', '2026-05-02', NULL, 'Cancelled', '2026-04-23'),
(31, 11, 5, 19, '2026-04-23', '2026-05-02', '2026-04-23', 'Returned', NULL),
(32, 21, 29, 30, '2026-04-23', '2026-05-02', '2026-04-26', 'Returned', NULL),
(33, 21, 29, 29, '2026-04-23', '2026-05-02', '2026-04-23', 'Returned', NULL),
(34, 14, 15, 31, '2026-04-25', '2026-05-05', '2026-04-25', 'Returned', NULL),
(35, 14, 15, 32, '2026-04-25', '2026-05-05', '2026-04-26', 'Returned', NULL),
(36, 14, 15, 31, '2026-04-25', '2026-05-02', '2026-04-26', 'Returned', NULL),
(37, 14, 15, 31, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(38, 14, 15, 31, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(39, 14, 15, 31, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(40, 14, 15, 31, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(41, 14, 15, 31, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(42, 14, 15, 32, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(43, 14, 15, 31, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(44, 14, 5, 34, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(45, 14, 12, 35, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(46, 14, 15, 31, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(47, 14, 12, 35, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(48, 14, 5, 34, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(49, 14, 27, 33, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(50, 14, 27, 33, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(51, 14, 27, 33, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(52, 14, 27, 33, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(53, 10, 15, 32, '2026-04-26', '2026-05-03', '2026-06-30', 'Returned', NULL),
(54, 11, 16, 25, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(55, 11, 16, 25, '2026-04-26', '2026-05-03', '2026-05-22', 'Returned', NULL),
(56, 14, 33, 38, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(57, 14, 32, 39, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(58, 14, 31, 41, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(59, 14, 30, 43, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(60, 10, 32, 40, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(61, 14, 33, 37, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(62, 14, 33, 37, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(63, 14, 32, 39, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(64, 14, 31, 41, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(65, 11, 33, 37, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(66, 14, 33, 38, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(67, 14, 30, 43, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(68, 14, 33, 38, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(69, 14, 32, 39, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(70, 16, 33, 37, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(71, 16, 32, 40, '2026-04-26', '2026-05-03', '2026-05-22', 'Returned', NULL),
(72, 16, 30, 43, '2026-04-26', '2026-05-03', '2026-05-22', 'Returned', NULL),
(73, 16, 15, 31, '2026-04-26', '2026-05-03', '2026-05-22', 'Returned', NULL),
(74, 14, 33, 37, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(75, 14, 33, 37, '2026-04-26', '2026-05-03', '2026-04-26', 'Returned', NULL),
(76, 14, 33, 37, '2026-04-26', '2026-05-05', '2026-05-11', 'Returned', NULL),
(77, 16, 33, 38, '2026-04-29', '2026-05-08', '2026-05-11', 'Returned', NULL),
(78, 11, 33, 37, '2026-05-22', '2026-05-30', '2026-06-30', 'Returned', NULL),
(79, 14, 33, 38, '2026-05-22', '2026-05-30', '2026-06-30', 'Returned', NULL),
(80, 16, 32, 40, '2026-05-22', '2026-05-30', '2026-06-30', 'Returned', NULL),
(81, 14, 31, 41, '2026-05-22', '2026-05-30', '2026-06-30', 'Returned', NULL),
(82, 14, 33, 37, '2026-06-30', '2026-07-08', '2026-04-30', 'Returned', NULL),
(83, 16, 33, 37, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(84, 16, 32, 39, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(85, 16, 31, 41, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(86, 16, 30, 43, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(87, 14, 33, 37, '2026-07-30', '2026-08-07', '2026-07-30', 'Returned', NULL),
(88, 14, 32, 39, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(89, 14, 33, 37, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(90, 14, 31, 41, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(91, 14, 30, 43, '2026-04-30', '2026-05-09', '2026-07-30', 'Returned', NULL),
(92, 16, 33, 37, '2026-05-03', '2026-05-11', '2026-05-03', 'Returned', NULL),
(93, 16, 32, 39, '2026-05-03', '2026-05-11', '2026-05-03', 'Returned', NULL),
(94, 14, 31, 41, '2026-05-03', '2026-05-11', '2026-05-03', 'Returned', NULL),
(95, 14, 33, 38, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(96, 14, 31, 41, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(97, 14, 15, 32, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(98, 14, 32, 39, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(99, 14, 33, 38, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(100, 14, 31, 41, '2026-05-03', '2026-05-11', '2026-08-03', 'Returned', NULL),
(101, 14, 15, 32, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(102, 14, 30, 43, '2026-05-03', '2026-05-11', '2026-08-03', 'Returned', NULL),
(103, 14, 33, 37, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(104, 14, 32, 39, '2026-05-03', '2026-05-11', '2026-07-03', 'Returned', NULL),
(107, 14, 33, 37, '2026-05-03', '2026-05-11', NULL, 'Cancelled', '2026-05-03'),
(108, 14, 32, 39, '2026-05-03', '2026-05-11', NULL, 'Cancelled', '2026-05-03'),
(109, 14, 33, 37, '2026-05-03', '2026-05-11', NULL, 'Cancelled', '2026-05-03'),
(110, 14, 33, 37, '2026-05-03', '2026-05-11', NULL, 'Cancelled', '2026-05-03'),
(111, 14, 33, 37, '2026-05-03', '2026-05-11', '2026-05-05', 'Returned', NULL),
(112, 14, 32, 39, '2026-05-03', '2026-05-11', '2026-05-05', 'Returned', NULL),
(113, 14, 31, 41, '2026-05-03', '2026-05-11', '2026-05-05', 'Returned', NULL),
(114, 14, 15, 32, '2026-05-03', '2026-05-11', '2026-05-05', 'Returned', NULL),
(115, 14, 32, 39, '2026-05-05', '2026-05-13', NULL, 'Cancelled', '2026-05-05'),
(116, 14, 33, 38, '2026-05-05', '2026-05-13', NULL, 'Cancelled', '2026-05-05'),
(117, 14, 30, 44, '2026-05-05', '2026-05-13', NULL, 'Cancelled', '2026-05-05'),
(118, 16, 33, 37, '2026-05-05', '2026-05-13', '2026-05-03', 'Returned', NULL),
(119, 14, 15, 32, '2026-05-05', '2026-05-13', '2026-05-03', 'Returned', NULL),
(120, 14, 30, 43, '2026-05-03', '2026-05-11', '2026-05-03', 'Returned', NULL),
(121, 14, 33, 37, '2026-05-04', '2026-05-12', '2026-05-04', 'Returned', NULL),
(122, 16, 33, 37, '2026-05-04', '2026-05-12', '2026-05-04', 'Returned', NULL),
(123, 16, 32, 40, '2026-05-04', '2026-05-12', NULL, 'Borrowed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `user_role`) VALUES
(1, 'admin', 'admin123', 'admin'),
(12, 'user', 'user', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `unique_isbn` (`isbn`),
  ADD KEY `fk_book_category` (`category_id`);

--
-- Indexes for table `book_copy`
--
ALTER TABLE `book_copy`
  ADD PRIMARY KEY (`copy_id`),
  ADD UNIQUE KEY `unique_book_copy` (`book_id`,`acquisition_number`),
  ADD KEY `idx_book_id` (`book_id`),
  ADD KEY `idx_isbn` (`acquisition_number`);

--
-- Indexes for table `borrower`
--
ALTER TABLE `borrower`
  ADD PRIMARY KEY (`borrower_id`),
  ADD UNIQUE KEY `unique_id_number` (`Id_number`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD PRIMARY KEY (`fine_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `copy_id` (`copy_id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `fk_book` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `book_copy`
--
ALTER TABLE `book_copy`
  MODIFY `copy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `borrower`
--
ALTER TABLE `borrower`
  MODIFY `borrower_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `fine`
--
ALTER TABLE `fine`
  MODIFY `fine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book`
--
ALTER TABLE `book`
  ADD CONSTRAINT `fk_book_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `book_copy`
--
ALTER TABLE `book_copy`
  ADD CONSTRAINT `fk_book_copy` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `fine`
--
ALTER TABLE `fine`
  ADD CONSTRAINT `fine_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`),
  ADD CONSTRAINT `fine_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `borrower` (`borrower_id`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `fk_book` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`copy_id`) REFERENCES `book_copy` (`copy_id`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `borrower` (`borrower_id`);
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

--
-- Dumping data for table `pma__export_templates`
--

INSERT INTO `pma__export_templates` (`id`, `username`, `export_type`, `template_name`, `template_data`) VALUES
(1, 'root', 'database', 'POS_SYSTEM', '{\"quick_or_custom\":\"quick\",\"what\":\"sql\",\"structure_or_data_forced\":\"0\",\"table_select[]\":[\"categories\",\"inventory_transactions\",\"payments\",\"products\",\"sales\",\"sale_items\",\"users\"],\"table_structure[]\":[\"categories\",\"inventory_transactions\",\"payments\",\"products\",\"sales\",\"sale_items\",\"users\"],\"table_data[]\":[\"categories\",\"inventory_transactions\",\"payments\",\"products\",\"sales\",\"sale_items\",\"users\"],\"aliases_new\":\"\",\"output_format\":\"sendit\",\"filename_template\":\"@DATABASE@\",\"remember_template\":\"on\",\"charset\":\"utf-8\",\"compression\":\"none\",\"maxsize\":\"\",\"codegen_structure_or_data\":\"data\",\"codegen_format\":\"0\",\"csv_separator\":\",\",\"csv_enclosed\":\"\\\"\",\"csv_escaped\":\"\\\"\",\"csv_terminated\":\"AUTO\",\"csv_null\":\"NULL\",\"csv_columns\":\"something\",\"csv_structure_or_data\":\"data\",\"excel_null\":\"NULL\",\"excel_columns\":\"something\",\"excel_edition\":\"win\",\"excel_structure_or_data\":\"data\",\"json_structure_or_data\":\"data\",\"json_unicode\":\"something\",\"latex_caption\":\"something\",\"latex_structure_or_data\":\"structure_and_data\",\"latex_structure_caption\":\"Structure of table @TABLE@\",\"latex_structure_continued_caption\":\"Structure of table @TABLE@ (continued)\",\"latex_structure_label\":\"tab:@TABLE@-structure\",\"latex_relation\":\"something\",\"latex_comments\":\"something\",\"latex_mime\":\"something\",\"latex_columns\":\"something\",\"latex_data_caption\":\"Content of table @TABLE@\",\"latex_data_continued_caption\":\"Content of table @TABLE@ (continued)\",\"latex_data_label\":\"tab:@TABLE@-data\",\"latex_null\":\"\\\\textit{NULL}\",\"mediawiki_structure_or_data\":\"structure_and_data\",\"mediawiki_caption\":\"something\",\"mediawiki_headers\":\"something\",\"htmlword_structure_or_data\":\"structure_and_data\",\"htmlword_null\":\"NULL\",\"ods_null\":\"NULL\",\"ods_structure_or_data\":\"data\",\"odt_structure_or_data\":\"structure_and_data\",\"odt_relation\":\"something\",\"odt_comments\":\"something\",\"odt_mime\":\"something\",\"odt_columns\":\"something\",\"odt_null\":\"NULL\",\"pdf_report_title\":\"\",\"pdf_structure_or_data\":\"structure_and_data\",\"phparray_structure_or_data\":\"data\",\"sql_include_comments\":\"something\",\"sql_header_comment\":\"\",\"sql_use_transaction\":\"something\",\"sql_compatibility\":\"NONE\",\"sql_structure_or_data\":\"structure_and_data\",\"sql_create_table\":\"something\",\"sql_auto_increment\":\"something\",\"sql_create_view\":\"something\",\"sql_procedure_function\":\"something\",\"sql_create_trigger\":\"something\",\"sql_backquotes\":\"something\",\"sql_type\":\"INSERT\",\"sql_insert_syntax\":\"both\",\"sql_max_query_size\":\"50000\",\"sql_hex_for_binary\":\"something\",\"sql_utc_time\":\"something\",\"texytext_structure_or_data\":\"structure_and_data\",\"texytext_null\":\"NULL\",\"xml_structure_or_data\":\"data\",\"xml_export_events\":\"something\",\"xml_export_functions\":\"something\",\"xml_export_procedures\":\"something\",\"xml_export_tables\":\"something\",\"xml_export_triggers\":\"something\",\"xml_export_views\":\"something\",\"xml_export_contents\":\"something\",\"yaml_structure_or_data\":\"data\",\"\":null,\"lock_tables\":null,\"as_separate_files\":null,\"csv_removeCRLF\":null,\"excel_removeCRLF\":null,\"json_pretty_print\":null,\"htmlword_columns\":null,\"ods_columns\":null,\"sql_dates\":null,\"sql_relation\":null,\"sql_mime\":null,\"sql_disable_fk\":null,\"sql_views_as_tables\":null,\"sql_metadata\":null,\"sql_create_database\":null,\"sql_drop_table\":null,\"sql_if_not_exists\":null,\"sql_simple_view_export\":null,\"sql_view_current_user\":null,\"sql_or_replace_view\":null,\"sql_truncate\":null,\"sql_delayed\":null,\"sql_ignore\":null,\"texytext_columns\":null}'),
(2, 'root', 'database', 'pos_system', '{\"quick_or_custom\":\"quick\",\"what\":\"sql\",\"structure_or_data_forced\":\"0\",\"table_select[]\":[\"categories\",\"inventory_transactions\",\"payments\",\"products\",\"sales\",\"sale_items\",\"users\"],\"table_structure[]\":[\"categories\",\"inventory_transactions\",\"payments\",\"products\",\"sales\",\"sale_items\",\"users\"],\"table_data[]\":[\"categories\",\"inventory_transactions\",\"payments\",\"products\",\"sales\",\"sale_items\",\"users\"],\"aliases_new\":\"\",\"output_format\":\"sendit\",\"filename_template\":\"@DATABASE@\",\"remember_template\":\"on\",\"charset\":\"utf-8\",\"compression\":\"none\",\"maxsize\":\"\",\"codegen_structure_or_data\":\"data\",\"codegen_format\":\"0\",\"csv_separator\":\",\",\"csv_enclosed\":\"\\\"\",\"csv_escaped\":\"\\\"\",\"csv_terminated\":\"AUTO\",\"csv_null\":\"NULL\",\"csv_columns\":\"something\",\"csv_structure_or_data\":\"data\",\"excel_null\":\"NULL\",\"excel_columns\":\"something\",\"excel_edition\":\"win\",\"excel_structure_or_data\":\"data\",\"json_structure_or_data\":\"data\",\"json_unicode\":\"something\",\"latex_caption\":\"something\",\"latex_structure_or_data\":\"structure_and_data\",\"latex_structure_caption\":\"Structure of table @TABLE@\",\"latex_structure_continued_caption\":\"Structure of table @TABLE@ (continued)\",\"latex_structure_label\":\"tab:@TABLE@-structure\",\"latex_relation\":\"something\",\"latex_comments\":\"something\",\"latex_mime\":\"something\",\"latex_columns\":\"something\",\"latex_data_caption\":\"Content of table @TABLE@\",\"latex_data_continued_caption\":\"Content of table @TABLE@ (continued)\",\"latex_data_label\":\"tab:@TABLE@-data\",\"latex_null\":\"\\\\textit{NULL}\",\"mediawiki_structure_or_data\":\"structure_and_data\",\"mediawiki_caption\":\"something\",\"mediawiki_headers\":\"something\",\"htmlword_structure_or_data\":\"structure_and_data\",\"htmlword_null\":\"NULL\",\"ods_null\":\"NULL\",\"ods_structure_or_data\":\"data\",\"odt_structure_or_data\":\"structure_and_data\",\"odt_relation\":\"something\",\"odt_comments\":\"something\",\"odt_mime\":\"something\",\"odt_columns\":\"something\",\"odt_null\":\"NULL\",\"pdf_report_title\":\"\",\"pdf_structure_or_data\":\"structure_and_data\",\"phparray_structure_or_data\":\"data\",\"sql_include_comments\":\"something\",\"sql_header_comment\":\"\",\"sql_use_transaction\":\"something\",\"sql_compatibility\":\"NONE\",\"sql_structure_or_data\":\"structure_and_data\",\"sql_create_table\":\"something\",\"sql_auto_increment\":\"something\",\"sql_create_view\":\"something\",\"sql_procedure_function\":\"something\",\"sql_create_trigger\":\"something\",\"sql_backquotes\":\"something\",\"sql_type\":\"INSERT\",\"sql_insert_syntax\":\"both\",\"sql_max_query_size\":\"50000\",\"sql_hex_for_binary\":\"something\",\"sql_utc_time\":\"something\",\"texytext_structure_or_data\":\"structure_and_data\",\"texytext_null\":\"NULL\",\"xml_structure_or_data\":\"data\",\"xml_export_events\":\"something\",\"xml_export_functions\":\"something\",\"xml_export_procedures\":\"something\",\"xml_export_tables\":\"something\",\"xml_export_triggers\":\"something\",\"xml_export_views\":\"something\",\"xml_export_contents\":\"something\",\"yaml_structure_or_data\":\"data\",\"\":null,\"lock_tables\":null,\"as_separate_files\":null,\"csv_removeCRLF\":null,\"excel_removeCRLF\":null,\"json_pretty_print\":null,\"htmlword_columns\":null,\"ods_columns\":null,\"sql_dates\":null,\"sql_relation\":null,\"sql_mime\":null,\"sql_disable_fk\":null,\"sql_views_as_tables\":null,\"sql_metadata\":null,\"sql_create_database\":null,\"sql_drop_table\":null,\"sql_if_not_exists\":null,\"sql_simple_view_export\":null,\"sql_view_current_user\":null,\"sql_or_replace_view\":null,\"sql_truncate\":null,\"sql_delayed\":null,\"sql_ignore\":null,\"texytext_columns\":null}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"pos_system\",\"table\":\"users\"},{\"db\":\"pos_system\",\"table\":\"categories\"},{\"db\":\"library_inventory_system\",\"table\":\"users\"},{\"db\":\"library_inventory_system\",\"table\":\"fine\"},{\"db\":\"library_inventory_system\",\"table\":\"book\"},{\"db\":\"library_inventory_system\",\"table\":\"borrower\"},{\"db\":\"library_inventory_system\",\"table\":\"transaction\"},{\"db\":\"library_inventory_system\",\"table\":\"book_copy\"},{\"db\":\"library_inventory_system\",\"table\":\"category\"},{\"db\":\"library_inventory_system\",\"table\":\"transactions\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'library_inventory_system', 'book_copy', '{\"sorted_col\":\"`book_copy`.`acquisition_number` ASC\"}', '2026-04-25 11:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2026-09-05 13:04:23', '{\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `pos_system`
--
CREATE DATABASE IF NOT EXISTS `pos_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pos_system`;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_type` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `reorder_level` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sale_date` datetime DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `sale_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `create_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `role`, `status`, `create_at`) VALUES
(1, 'admin', 'admin123', 'christian malinao', 'Admin', 'active', '2026-09-05 20:49:12'),
(2, 'manager', 'manager123', 'christian dedil', 'Manager', 'active', '2026-09-05 20:49:12'),
(3, 'cashier', 'cashier123', 'clifford legaspi', 'Cashier', 'active', '2026-09-05 20:49:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
