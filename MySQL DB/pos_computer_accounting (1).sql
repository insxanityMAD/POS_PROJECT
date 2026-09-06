-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2026 at 12:01 PM
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
-- Database: `pos_computer_accounting`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `status`) VALUES
(1, 'Electronics', 'Affordable electronic gadgets', 'Active'),
(2, 'Hardware', 'Affordable home equipment and tools for building or fixing house', 'Active'),
(3, 'School Supplies', 'Affordable school supplies', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `discount_types`
--

CREATE TABLE `discount_types` (
  `discount_id` int(11) NOT NULL,
  `discount_name` varchar(50) NOT NULL,
  `discount_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `requires_id` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discount_types`
--

INSERT INTO `discount_types` (`discount_id`, `discount_name`, `discount_rate`, `requires_id`, `is_active`) VALUES
(1, 'PWD', 20.00, 1, 1),
(2, 'Senior Citizen', 20.00, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expense_category` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `transaction_type` enum('RESTOCK','SALE','LOSS','ADJUSTMENT','RETURN') NOT NULL,
  `quantity` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`transaction_id`, `product_id`, `user_id`, `supplier_id`, `transaction_type`, `quantity`, `transaction_date`, `remarks`) VALUES
(1, 1, 1, NULL, 'SALE', 3, '2026-09-06 16:00:34', 'Sale #RCT202609062C84BD'),
(2, 2, 1, NULL, 'SALE', 1, '2026-09-06 16:02:47', 'Sale #RCT20260906762144'),
(3, 3, 1, NULL, 'SALE', 5, '2026-09-06 16:28:31', 'Sale #RCT20260906FAE206'),
(4, 2, 1, 1, 'RESTOCK', 2, '2026-09-06 16:43:13', NULL),
(5, 2, 1, 2, 'RESTOCK', 3, '2026-09-06 16:59:05', NULL),
(6, 1, 1, 1, 'RESTOCK', 20, '2026-09-06 16:59:28', NULL),
(7, 1, 1, NULL, 'SALE', 10, '2026-09-06 17:00:26', 'Sale #RCT20260906A17BEC'),
(8, 4, 1, 1, 'RESTOCK', 10, '2026-09-06 17:03:10', NULL),
(9, 1, 1, NULL, 'SALE', 1, '2026-09-06 17:09:32', 'Sale #RCT20260906C2E1B4'),
(10, 4, 1, NULL, 'SALE', 1, '2026-09-06 17:09:32', 'Sale #RCT20260906C2E1B4'),
(11, 1, 1, NULL, 'SALE', 1, '2026-09-06 17:13:48', 'Sale #RCT20260906C5A439'),
(12, 4, 1, NULL, 'SALE', 1, '2026-09-06 17:13:48', 'Sale #RCT20260906C5A439'),
(13, 3, 1, 2, 'RESTOCK', 15, '2026-09-06 17:15:41', NULL),
(14, 2, 1, NULL, 'SALE', 1, '2026-09-06 17:21:29', 'Sale #RCT202609069C567A'),
(15, 1, 1, NULL, 'SALE', 8, '2026-09-06 17:33:47', 'Sale #RCT20260906B208EA'),
(16, 4, 1, 1, 'RESTOCK', 25, '2026-09-06 17:34:46', NULL),
(17, 1, 1, 1, 'RESTOCK', 10, '2026-09-06 17:37:54', NULL),
(18, 2, 1, NULL, 'SALE', 1, '2026-09-06 17:45:49', 'Sale #RCT20260906DA0A8B'),
(19, 2, 1, NULL, 'RETURN', 1, '2026-09-06 17:46:10', 'Voided sale RCT20260906DA0A8B');

-- --------------------------------------------------------

--
-- Table structure for table `losses`
--

CREATE TABLE `losses` (
  `loss_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` enum('Expired','Damaged','Missing','Other') NOT NULL,
  `loss_date` datetime NOT NULL DEFAULT current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `payment_method` enum('Cash','GCash','Maya','Bank') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `sale_id`, `payment_method`, `amount_paid`, `change_amount`, `reference_number`, `payment_date`) VALUES
(1, 1, 'Cash', 100000.00, 16000.00, NULL, '2026-09-06 16:00:34'),
(2, 2, 'GCash', 150000.00, 0.00, NULL, '2026-09-06 16:02:47'),
(3, 3, 'GCash', 75000.00, 0.00, NULL, '2026-09-06 16:28:31'),
(4, 4, 'Cash', 1000000.00, 750000.00, NULL, '2026-09-06 17:00:26'),
(5, 5, 'Cash', 100000.00, 67500.00, NULL, '2026-09-06 17:09:32'),
(6, 6, 'Cash', 100000.00, 63600.00, NULL, '2026-09-06 17:13:48'),
(7, 7, 'GCash', 150000.00, 0.00, NULL, '2026-09-06 17:21:29'),
(8, 8, 'Cash', 1000000.00, 776000.00, NULL, '2026-09-06 17:33:47'),
(9, 9, 'Cash', 1000000.00, 832000.00, NULL, '2026-09-06 17:45:49');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `regular_hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`payroll_id`, `period_id`, `user_id`, `hourly_rate`, `regular_hours`, `overtime_hours`, `gross_pay`, `total_deductions`, `net_pay`) VALUES
(1, 1, 4, 1000.00, 105.00, 8.00, 115000.00, 5000.00, 110000.00),
(2, 1, 3, 100.00, 100.00, 0.00, 10000.00, 0.00, 10000.00),
(3, 1, 1, 5000.00, 150.00, 24.00, 900000.00, 10000.00, 890000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deductions`
--

CREATE TABLE `payroll_deductions` (
  `deduction_id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `deduction_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_deductions`
--

INSERT INTO `payroll_deductions` (`deduction_id`, `payroll_id`, `deduction_name`, `amount`) VALUES
(1, 1, 'SSS', 5000.00),
(4, 3, 'TAX', 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pay_periods`
--

CREATE TABLE `pay_periods` (
  `period_id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `pay_date` date DEFAULT NULL,
  `status` enum('Open','Processed','Closed') NOT NULL DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pay_periods`
--

INSERT INTO `pay_periods` (`period_id`, `period_name`, `start_date`, `end_date`, `pay_date`, `status`) VALUES
(1, 'SEPT 1-15, 2026', '2026-09-01', '2026-09-15', NULL, 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 5,
  `expiration_date` date DEFAULT NULL,
  `status` enum('Active','Inactive','Expired') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_code`, `barcode`, `product_name`, `description`, `cost_price`, `selling_price`, `stock_quantity`, `reorder_level`, `expiration_date`, `status`, `created_at`) VALUES
(1, 1, 'NK000001', 'BN000001', 'Nike Headset', 'A headset with built-in dolby atmos', 20000.00, 25000.00, 10, 2, '2036-08-08', 'Active', '2026-09-06 15:28:40'),
(2, 2, 'RT000002', 'BC0000004', 'Rooftop', 'Dolor color dolor sample', 10000.00, 150000.00, 13, 9, '2026-09-06', 'Active', '2026-09-06 15:33:44'),
(3, 2, 'W0000008', 'TB0000009', 'Wood', 'SAMPLE', 10000.00, 15000.00, 15, 5, NULL, 'Active', '2026-09-06 16:27:16'),
(4, 2, 'NK000002', 'BN000005', 'Nike Shoes', NULL, 5000.00, 7500.00, 33, 3, NULL, 'Active', '2026-09-06 17:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `product_suppliers`
--

CREATE TABLE `product_suppliers` (
  `product_supplier_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `supplier_product_code` varchar(100) DEFAULT NULL,
  `last_cost_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_suppliers`
--

INSERT INTO `product_suppliers` (`product_supplier_id`, `product_id`, `supplier_id`, `supplier_product_code`, `last_cost_price`) VALUES
(1, 1, 1, NULL, NULL),
(3, 2, 2, NULL, NULL),
(4, 3, 2, NULL, NULL),
(5, 4, 1, NULL, 5000.00);

-- --------------------------------------------------------

--
-- Table structure for table `qr_payment_settings`
--

CREATE TABLE `qr_payment_settings` (
  `qr_id` int(11) NOT NULL,
  `payment_name` enum('GCash','Maya','Bank') NOT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `qr_image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_payment_settings`
--

INSERT INTO `qr_payment_settings` (`qr_id`, `payment_name`, `account_name`, `account_number`, `qr_image_path`, `is_active`) VALUES
(1, 'GCash', NULL, NULL, NULL, 1),
(2, 'Maya', NULL, NULL, NULL, 1),
(3, 'Bank', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(30) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'Admin', 'Full system access and configuration'),
(2, 'Manager', 'Management, inventory, reports and payroll access'),
(3, 'Cashier', 'POS sales and payment processing'),
(4, 'Supplier', 'Supplier account access');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_id_number` varchar(100) DEFAULT NULL,
  `tax_id` int(11) DEFAULT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_status` enum('Completed','Voided','Pending') NOT NULL DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `receipt_number`, `user_id`, `sale_date`, `subtotal`, `discount_id`, `discount_amount`, `customer_id_number`, `tax_id`, `tax_amount`, `total_amount`, `sale_status`) VALUES
(1, 'RCT202609062C84BD', 1, '2026-09-06 16:00:34', 75000.00, NULL, 0.00, NULL, 1, 9000.00, 84000.00, 'Completed'),
(2, 'RCT20260906762144', 1, '2026-09-06 16:02:47', 150000.00, NULL, 0.00, NULL, NULL, 0.00, 150000.00, 'Completed'),
(3, 'RCT20260906FAE206', 1, '2026-09-06 16:28:31', 75000.00, NULL, 0.00, NULL, NULL, 0.00, 75000.00, 'Completed'),
(4, 'RCT20260906A17BEC', 1, '2026-09-06 17:00:26', 250000.00, NULL, 0.00, NULL, NULL, 0.00, 250000.00, 'Completed'),
(5, 'RCT20260906C2E1B4', 1, '2026-09-06 17:09:32', 32500.00, NULL, 0.00, NULL, NULL, 0.00, 32500.00, 'Completed'),
(6, 'RCT20260906C5A439', 1, '2026-09-06 17:13:48', 32500.00, NULL, 0.00, NULL, 1, 3900.00, 36400.00, 'Completed'),
(7, 'RCT202609069C567A', 1, '2026-09-06 17:21:29', 150000.00, NULL, 0.00, NULL, NULL, 0.00, 150000.00, 'Completed'),
(8, 'RCT20260906B208EA', 1, '2026-09-06 17:33:47', 200000.00, NULL, 0.00, NULL, 1, 24000.00, 224000.00, 'Completed'),
(9, 'RCT20260906DA0A8B', 1, '2026-09-06 17:45:49', 150000.00, NULL, 0.00, NULL, 1, 18000.00, 168000.00, 'Voided');

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

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`sale_item_id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 3, 25000.00, 75000.00),
(2, 2, 2, 1, 150000.00, 150000.00),
(3, 3, 3, 5, 15000.00, 75000.00),
(4, 4, 1, 10, 25000.00, 250000.00),
(5, 5, 1, 1, 25000.00, 25000.00),
(6, 5, 4, 1, 7500.00, 7500.00),
(7, 6, 1, 1, 25000.00, 25000.00),
(8, 6, 4, 1, 7500.00, 7500.00),
(9, 7, 2, 1, 150000.00, 150000.00),
(10, 8, 1, 8, 25000.00, 200000.00),
(11, 9, 2, 1, 150000.00, 150000.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_code` varchar(50) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_code`, `supplier_name`, `contact_person`, `phone`, `email`, `address`, `status`, `created_at`) VALUES
(1, 'NK-INT', 'Nike', 'Mr. Lee Men Hoe', '095104654135', 'leedoestwerk@gmail.com', '101 Pwanyang Busan 67 Street, KR', 'Active', '2026-09-06 16:41:47'),
(2, 'ACE-HARDWARE_PH', 'Ace Hardware', 'Mr. Chris Brown', '0953211315', 'chrizbrezzy@gmail.com', '207 LA Street Apt B, 72390, US', 'Active', '2026-09-06 16:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `log_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`log_id`, `user_id`, `action`, `module`, `record_id`, `description`, `ip_address`, `log_date`) VALUES
(1, 1, 'SALE', 'POS', 8, 'Completed sale RCT20260906B208EA - Total: 224,000.00 via Cash', '::1', '2026-09-06 17:33:47'),
(2, 1, 'RESTOCK', 'Inventory', 4, 'Restocked +25 units of \"Nike Shoes\" from Nike', '::1', '2026-09-06 17:34:46'),
(3, 1, 'RESTOCK', 'Inventory', 1, 'Restocked +10 units of \"Nike Headset\" from Nike', '::1', '2026-09-06 17:37:54'),
(4, 1, 'UPDATE', 'Payroll', 2, 'Processed payroll for christian  dedil - Net Pay: 10,000.00', '::1', '2026-09-06 17:41:33'),
(5, 1, 'LOGOUT', 'Auth', 1, 'christian malinao logged out', '::1', '2026-09-06 17:43:47'),
(6, 1, 'LOGIN', 'Auth', 1, 'christian malinao logged in', '::1', '2026-09-06 17:43:52'),
(7, 1, 'SALE', 'POS', 9, 'Completed sale RCT20260906DA0A8B - Total: 168,000.00 via Cash', '::1', '2026-09-06 17:45:49'),
(8, 1, 'VOID', 'POS', 9, 'Voided sale RCT20260906DA0A8B', '::1', '2026-09-06 17:46:10'),
(9, 1, 'LOGOUT', 'Auth', 1, 'christian malinao logged out', '::1', '2026-09-06 17:46:55'),
(10, 4, 'LOGIN', 'Auth', 4, 'Jean Astejada logged in', '::1', '2026-09-06 17:47:18'),
(11, 4, 'LOGOUT', 'Auth', 4, 'Jean Astejada logged out', '::1', '2026-09-06 17:49:22'),
(12, 1, 'LOGIN', 'Auth', 1, 'christian malinao logged in', '::1', '2026-09-06 17:50:09'),
(13, 1, 'LOGOUT', 'Auth', 1, 'christian malinao logged out', '::1', '2026-09-06 17:59:58'),
(14, 4, 'LOGIN', 'Auth', 4, 'Jean Astejada logged in', '::1', '2026-09-06 18:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `tax_settings`
--

CREATE TABLE `tax_settings` (
  `tax_id` int(11) NOT NULL,
  `tax_name` varchar(50) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tax_settings`
--

INSERT INTO `tax_settings` (`tax_id`, `tax_name`, `tax_rate`, `is_active`, `created_at`) VALUES
(1, 'Tax 12%', 12.00, 1, '2026-09-06 12:31:33'),
(2, 'Tax 16%', 16.00, 0, '2026-09-06 12:31:33'),
(3, 'Tax 20%', 20.00, 0, '2026-09-06 12:31:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `username`, `password`, `full_name`, `email`, `status`, `created_at`) VALUES
(1, 1, 'admin', 'admin123', 'christian malinao', 'sample@gmail.com', 'Active', '2026-09-06 13:12:03'),
(3, 1, 'chan', 'sample', 'christian  dedil', 'sample123@gmail', 'Active', '2026-09-06 13:28:06'),
(4, 1, 'sample', 'sample123', 'Jean Astejada', 'jinastejada@gmail.com', 'Active', '2026-09-06 14:21:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `discount_types`
--
ALTER TABLE `discount_types`
  ADD PRIMARY KEY (`discount_id`),
  ADD UNIQUE KEY `discount_name` (`discount_name`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_inventory_date` (`transaction_date`);

--
-- Indexes for table `losses`
--
ALTER TABLE `losses`
  ADD PRIMARY KEY (`loss_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `idx_payroll_user` (`user_id`),
  ADD KEY `idx_payroll_period` (`period_id`);

--
-- Indexes for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD PRIMARY KEY (`deduction_id`),
  ADD KEY `payroll_id` (`payroll_id`);

--
-- Indexes for table `pay_periods`
--
ALTER TABLE `pay_periods`
  ADD PRIMARY KEY (`period_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_products_name` (`product_name`),
  ADD KEY `idx_products_barcode` (`barcode`);

--
-- Indexes for table `product_suppliers`
--
ALTER TABLE `product_suppliers`
  ADD PRIMARY KEY (`product_supplier_id`),
  ADD UNIQUE KEY `uq_product_supplier` (`product_id`,`supplier_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `qr_payment_settings`
--
ALTER TABLE `qr_payment_settings`
  ADD PRIMARY KEY (`qr_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `discount_id` (`discount_id`),
  ADD KEY `tax_id` (`tax_id`),
  ADD KEY `idx_sales_date` (`sale_date`),
  ADD KEY `idx_sales_status` (`sale_status`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `supplier_code` (`supplier_code`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_logs_date` (`log_date`);

--
-- Indexes for table `tax_settings`
--
ALTER TABLE `tax_settings`
  ADD PRIMARY KEY (`tax_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discount_types`
--
ALTER TABLE `discount_types`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `losses`
--
ALTER TABLE `losses`
  MODIFY `loss_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  MODIFY `deduction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pay_periods`
--
ALTER TABLE `pay_periods`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_suppliers`
--
ALTER TABLE `product_suppliers`
  MODIFY `product_supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qr_payment_settings`
--
ALTER TABLE `qr_payment_settings`
  MODIFY `qr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tax_settings`
--
ALTER TABLE `tax_settings`
  MODIFY `tax_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `losses`
--
ALTER TABLE `losses`
  ADD CONSTRAINT `losses_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `losses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `pay_periods` (`period_id`),
  ADD CONSTRAINT `payroll_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD CONSTRAINT `payroll_deductions_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`payroll_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `product_suppliers`
--
ALTER TABLE `product_suppliers`
  ADD CONSTRAINT `product_suppliers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `product_suppliers_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`discount_id`) REFERENCES `discount_types` (`discount_id`),
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`tax_id`) REFERENCES `tax_settings` (`tax_id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
