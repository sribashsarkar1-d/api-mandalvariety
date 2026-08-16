-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 16, 2026 at 07:38 AM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u391326945_mandal`
--

-- --------------------------------------------------------

--
-- Table structure for table `age_verifications`
--

CREATE TABLE `age_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age_threshold` int(11) DEFAULT 18,
  `verified_age` int(11) DEFAULT NULL,
  `method` enum('document','self_declaration','facial_estimation','manual_review') DEFAULT 'document',
  `document_type` varchar(100) DEFAULT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `document_front` varchar(255) DEFAULT NULL,
  `document_back` varchar(255) DEFAULT NULL,
  `selfie_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected') DEFAULT 'pending',
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(61, 151, '2026-06-10 16:55:14', '2026-06-10 16:55:14'),
(62, 152, '2026-06-12 12:44:46', '2026-06-12 12:44:46'),
(63, 154, '2026-06-19 09:24:32', '2026-06-19 09:24:32'),
(65, 156, '2026-07-04 06:42:49', '2026-07-04 06:42:49'),
(66, 165, '2026-07-05 04:30:55', '2026-07-05 04:30:55'),
(67, 167, '2026-07-06 15:48:19', '2026-07-06 15:48:19'),
(68, 168, '2026-07-10 17:07:07', '2026-07-10 17:07:07'),
(69, 170, '2026-08-07 02:28:09', '2026-08-07 02:28:09'),
(70, 174, '2026-08-07 15:21:36', '2026-08-07 15:21:36'),
(71, 175, '2026-08-08 15:01:36', '2026-08-08 15:01:36'),
(72, 176, '2026-08-09 15:15:49', '2026-08-09 15:15:49');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `price_at_purchase`, `created_at`) VALUES
(133, 61, 61, 9, 90.00, '2026-07-05 15:51:30'),
(135, 68, 68, 9, 50.00, '2026-07-10 17:07:07'),
(136, 68, 67, 101, 175.00, '2026-07-10 17:07:20'),
(137, 68, 61, 1, 90.00, '2026-07-10 17:13:05'),
(138, 68, 63, 1, 44.00, '2026-07-10 17:13:18'),
(148, 66, 61, 1, 90.00, '2026-07-12 07:35:50'),
(158, 63, 73, 1, 35.00, '2026-08-01 13:25:29'),
(159, 63, 74, 1, 37.00, '2026-08-01 13:25:36'),
(160, 63, 147, 1, 35.00, '2026-08-01 13:25:51'),
(161, 63, 146, 1, 60.00, '2026-08-01 13:25:54'),
(162, 63, 148, 1, 60.00, '2026-08-01 13:26:00');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`) VALUES
(52, 'Grocery', 'grocery', 'Grocery items', 'categories/1781108242_5753.jpg', 1, '2026-06-10 16:17:22'),
(53, 'Beauty & Personal Care', 'beauty-personal-care', NULL, NULL, 1, '2026-06-15 15:20:51'),
(54, 'Hair Care', 'hair-care', NULL, NULL, 1, '2026-06-17 09:04:34'),
(55, 'Home& living', 'home-living', 'Best product', NULL, 1, '2026-06-17 14:35:02');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `discount` int(11) DEFAULT NULL,
  `expiry` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_boys`
--

CREATE TABLE `delivery_boys` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery_boys`
--

INSERT INTO `delivery_boys` (`id`, `name`, `email`, `phone`, `password`, `vehicle_type`, `vehicle_number`, `is_active`, `is_available`, `created_at`, `updated_at`) VALUES
(3, 'ram', 'ram@gmail.com', '7878787878', '$2y$10$yXIsUU4/t9A66ZCSGpics.X5bJ4z.AP.mc1SWtYTLwmL4sodkG6VC', 'Bike', 'Wbs7c5666', 1, 1, '2026-06-12 12:47:13', '2026-06-12 12:47:13'),
(4, 'Yash roy', 'sribashsarkarblp@gmail.com', '09083646603', '$2y$10$eT.dMzf49uMSU9U3EOsz8.c9vxIZsbJTguS82c0TT9KAQqI1ZQHV2', 'Bike', 'Wbs7c5666', 1, 1, '2026-07-01 06:03:32', '2026-07-01 06:03:32');

-- --------------------------------------------------------

--
-- Table structure for table `employee_categories`
--

CREATE TABLE `employee_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_categories`
--

INSERT INTO `employee_categories` (`id`, `name`, `image`, `description`, `status`, `created_at`, `updated_at`) VALUES
(4, 'grocery', NULL, NULL, 'active', '2026-08-15 20:49:19', '2026-08-15 20:49:19'),
(5, 'Beauty', NULL, NULL, 'active', '2026-08-15 20:49:32', '2026-08-15 20:49:32');

-- --------------------------------------------------------

--
-- Table structure for table `employee_credit_payments`
--

CREATE TABLE `employee_credit_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','upi','card') NOT NULL DEFAULT 'cash',
  `transaction_id` varchar(150) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_credit_payments`
--

INSERT INTO `employee_credit_payments` (`id`, `customer_id`, `employee_id`, `amount`, `payment_method`, `transaction_id`, `notes`, `created_at`) VALUES
(1, 2, 1, 10.00, 'cash', '', 'Payment for Invoice #INV-2026-00012', '2026-08-15 13:45:28'),
(2, 2, 3, 45.00, 'cash', '', 'Payment for Invoice #INV-2026-00013', '2026-08-15 16:04:14'),
(3, 2, 3, 63.00, 'cash', '', 'baki', '2026-08-15 16:22:34'),
(4, 2, 1, 60.00, 'cash', '', 'Payment for Invoice #INV-2026-00015', '2026-08-15 16:24:37'),
(5, 2, 1, 22.00, 'cash', '', 'dilo', '2026-08-15 16:35:20'),
(6, 2, 1, 10.00, 'cash', '', '', '2026-08-15 16:35:51'),
(7, 2, 3, 8.00, 'cash', '', 'Payment for Invoice #INV-2026-00020', '2026-08-15 18:06:31'),
(8, 4, 5, 10.00, 'cash', '', 'Payment for Invoice #INV-2026-00001', '2026-08-15 20:53:18'),
(9, 7, 8, 10.00, 'cash', '', 'Payment for Invoice #INV-2026-00004', '2026-08-16 06:31:21'),
(10, 7, 8, 70.00, 'cash', '', 'Payment for Invoice #INV-2026-00005', '2026-08-16 06:32:04'),
(11, 7, 8, 50.00, 'cash', '', '', '2026-08-16 06:32:59'),
(12, 8, 8, 15.00, 'cash', '', 'Payment for Invoice #INV-2026-00006', '2026-08-16 06:39:27'),
(13, 8, 7, 2.00, 'cash', '', '', '2026-08-16 06:44:08'),
(14, 7, 8, 10.00, 'cash', '', '', '2026-08-16 06:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `employee_customers`
--

CREATE TABLE `employee_customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total_purchase` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_bills` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_customers`
--

INSERT INTO `employee_customers` (`id`, `name`, `phone`, `email`, `address`, `total_purchase`, `total_bills`, `status`, `created_at`, `updated_at`) VALUES
(4, 'ziaul mandal', '9635160436', NULL, NULL, 20.00, 1, 'active', '2026-08-15 20:53:02', '2026-08-15 20:53:18'),
(5, 'ram ji', '9635160436', NULL, NULL, 510.00, 3, 'active', '2026-08-15 20:54:15', '2026-08-16 07:09:25'),
(6, 'kush mandal', '8878776554', NULL, NULL, 0.00, 0, 'active', '2026-08-15 20:54:32', '2026-08-15 20:54:32'),
(7, 'Sam', '9083646603', NULL, NULL, 160.00, 2, 'active', '2026-08-16 06:30:58', '2026-08-16 06:32:04'),
(8, 'Fam', '9083646603', NULL, NULL, 20.00, 1, 'active', '2026-08-16 06:37:35', '2026-08-16 06:39:27');

-- --------------------------------------------------------

--
-- Table structure for table `employee_customer_ledger`
--

CREATE TABLE `employee_customer_ledger` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED DEFAULT NULL,
  `credit_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_type` enum('sale_credit','payment','return','adjustment') NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `previous_due` decimal(14,2) NOT NULL DEFAULT 0.00,
  `new_due` decimal(14,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_customer_ledger`
--

INSERT INTO `employee_customer_ledger` (`id`, `customer_id`, `employee_id`, `sale_id`, `credit_payment_id`, `transaction_type`, `amount`, `previous_due`, `new_due`, `description`, `created_at`) VALUES
(13, 4, 5, 21, NULL, 'sale_credit', 20.00, 0.00, 20.00, 'Sale Invoice #INV-2026-00001', '2026-08-15 20:53:18'),
(14, 4, 5, 21, 8, 'payment', 10.00, 20.00, 10.00, 'Payment against Invoice #INV-2026-00001', '2026-08-15 20:53:18'),
(15, 7, 8, 24, NULL, 'sale_credit', 40.00, 0.00, 40.00, 'Sale Invoice #INV-2026-00004', '2026-08-16 06:31:21'),
(16, 7, 8, 24, 9, 'payment', 10.00, 40.00, 30.00, 'Payment against Invoice #INV-2026-00004', '2026-08-16 06:31:21'),
(17, 7, 8, 25, NULL, 'sale_credit', 120.00, 30.00, 150.00, 'Sale Invoice #INV-2026-00005', '2026-08-16 06:32:04'),
(18, 7, 8, 25, 10, 'payment', 70.00, 150.00, 80.00, 'Payment against Invoice #INV-2026-00005', '2026-08-16 06:32:04'),
(19, 7, 8, NULL, 11, 'payment', 50.00, 80.00, 30.00, 'Payment received via CASH', '2026-08-16 06:32:59'),
(20, 8, 8, 26, NULL, 'sale_credit', 20.00, 0.00, 20.00, 'Sale Invoice #INV-2026-00006', '2026-08-16 06:39:27'),
(21, 8, 8, 26, 12, 'payment', 15.00, 20.00, 5.00, 'Payment against Invoice #INV-2026-00006', '2026-08-16 06:39:27'),
(22, 8, 7, NULL, 13, 'payment', 2.00, 5.00, 3.00, 'Payment received via CASH', '2026-08-16 06:44:08'),
(23, 7, 8, NULL, 14, 'payment', 10.00, 30.00, 20.00, 'Payment received via CASH', '2026-08-16 06:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `employee_expenses`
--

CREATE TABLE `employee_expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_notifications`
--

CREATE TABLE `employee_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-bell',
  `type` enum('sale','login','register','system') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_notifications`
--

INSERT INTO `employee_notifications` (`id`, `title`, `message`, `icon`, `type`, `is_read`, `created_at`) VALUES
(26, 'New Registration', 'New admin registered: ram Sarkar', 'bi-bell', 'register', 1, '2026-08-15 20:48:12'),
(27, 'User Login', 'ram Sarkar logged in.', 'bi-bell', 'login', 1, '2026-08-15 20:48:17'),
(28, 'New Registration', 'New employee registered: sribashsarkar', 'bi-bell', 'register', 1, '2026-08-15 20:52:14'),
(29, 'User Login', 'sribashsarkar logged in.', 'bi-bell', 'login', 1, '2026-08-15 20:52:32'),
(30, 'New Sale Generated', 'Invoice #INV-2026-00001 created by sribashsarkar for ₹20.00', 'bi-bell', 'sale', 1, '2026-08-15 20:53:18'),
(31, 'New Sale Generated', 'Invoice #INV-2026-00002 created by sribashsarkar for ₹60.00', 'bi-bell', 'sale', 1, '2026-08-15 20:54:49'),
(32, 'New Sale Generated', 'Invoice #INV-2026-00003 created by sribashsarkar for ₹340.00', 'bi-bell', 'sale', 1, '2026-08-15 20:55:47'),
(33, 'User Login', 'ram Sarkar logged in.', 'bi-bell', 'login', 1, '2026-08-15 20:57:00'),
(34, 'User Login', 'ram Sarkar logged in.', 'bi-bell', 'login', 1, '2026-08-16 06:18:58'),
(35, 'New Registration', 'New admin registered: Ajgar Ali', 'bi-bell', 'register', 1, '2026-08-16 06:25:03'),
(36, 'User Login', 'Ajgar Ali logged in.', 'bi-bell', 'login', 1, '2026-08-16 06:25:19'),
(37, 'New Registration', 'New admin registered: Ziaul', 'bi-bell', 'register', 1, '2026-08-16 06:29:17'),
(38, 'New Registration', 'New employee registered: Ajgar Ali', 'bi-bell', 'register', 1, '2026-08-16 06:29:33'),
(39, 'User Login', 'Ziaul logged in.', 'bi-bell', 'login', 1, '2026-08-16 06:29:38'),
(40, 'User Login', 'Ajgar Ali logged in.', 'bi-bell', 'login', 1, '2026-08-16 06:29:58'),
(41, 'New Sale Generated', 'Invoice #INV-2026-00004 created by Ajgar Ali for ₹40.00', 'bi-bell', 'sale', 1, '2026-08-16 06:31:21'),
(42, 'New Sale Generated', 'Invoice #INV-2026-00005 created by Ajgar Ali for ₹120.00', 'bi-bell', 'sale', 1, '2026-08-16 06:32:04'),
(43, 'User Login', 'Ajgar Ali logged in.', 'bi-bell', 'login', 1, '2026-08-16 06:35:05'),
(44, 'New Sale Generated', 'Invoice #INV-2026-00006 created by Ajgar Ali for ₹20.00', 'bi-bell', 'sale', 1, '2026-08-16 06:39:27'),
(45, 'User Login', 'Ziaul logged in.', 'bi-bell', 'login', 1, '2026-08-16 06:47:10'),
(46, 'New Sale Generated', 'Invoice #INV-2026-00007 created by Ziaul for ₹110.00', 'bi-bell', 'sale', 0, '2026-08-16 07:09:25'),
(47, 'User Login', 'Ajgar Ali logged in.', 'bi-bell', 'login', 0, '2026-08-16 07:11:48'),
(48, 'User Login', 'Ajgar Ali logged in.', 'bi-bell', 'login', 0, '2026-08-16 07:35:05');

-- --------------------------------------------------------

--
-- Table structure for table `employee_payments`
--

CREATE TABLE `employee_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','upi','card','mixed','credit') NOT NULL DEFAULT 'cash',
  `transaction_id` varchar(150) DEFAULT NULL,
  `payment_status` enum('success','pending','failed') NOT NULL DEFAULT 'success',
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_payments`
--

INSERT INTO `employee_payments` (`id`, `sale_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `paid_at`) VALUES
(20, 21, 10.00, 'cash', '', 'success', '2026-08-15 20:53:18'),
(21, 22, 60.00, 'upi', '', 'success', '2026-08-15 20:54:49'),
(22, 23, 340.00, 'cash', '', 'success', '2026-08-15 20:55:47'),
(23, 24, 10.00, 'cash', '', 'success', '2026-08-16 06:31:21'),
(24, 25, 70.00, 'cash', '', 'success', '2026-08-16 06:32:04'),
(25, 26, 15.00, 'cash', '', 'success', '2026-08-16 06:39:27'),
(26, 27, 110.00, 'cash', '', 'success', '2026-08-16 07:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `employee_products`
--

CREATE TABLE `employee_products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `unit` enum('piece','kg','gram','liter','ml','packet','box','dozen') NOT NULL DEFAULT 'piece',
  `purchase_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `mrp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `minimum_stock` decimal(12,3) NOT NULL DEFAULT 0.000,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_products`
--

INSERT INTO `employee_products` (`id`, `category_id`, `name`, `sku`, `barcode`, `image`, `brand`, `unit`, `purchase_price`, `selling_price`, `mrp`, `gst_percent`, `discount`, `expiry_date`, `minimum_stock`, `description`, `status`, `created_at`, `updated_at`) VALUES
(8, 4, 'potato', 'SKU-E207C4', '890288387142', '6a80d12803af2-1000301759.jpg', '', 'piece', 0.00, 20.00, 0.00, 0.00, 0.00, '2072-08-16', 10.000, 'this is best one', 'active', '2026-08-15 20:50:48', '2026-08-15 20:50:48'),
(9, 4, 'Avcd', 'SKU-B272CA', '890177230255', '6a815d36a4b9c-1000300176.jpg', '', 'piece', 0.00, 25.00, 0.00, 0.00, 0.00, '2053-08-16', 5.000, 'Fhffu', 'active', '2026-08-16 06:48:22', '2026-08-16 06:48:22');

-- --------------------------------------------------------

--
-- Table structure for table `employee_product_stock`
--

CREATE TABLE `employee_product_stock` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT 0.000,
  `reserved_quantity` decimal(12,3) NOT NULL DEFAULT 0.000,
  `stock_status` enum('in_stock','low_stock','out_of_stock') NOT NULL DEFAULT 'in_stock',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_product_stock`
--

INSERT INTO `employee_product_stock` (`id`, `product_id`, `quantity`, `reserved_quantity`, `stock_status`, `updated_at`) VALUES
(6, 8, 67.000, 0.000, 'in_stock', '2026-08-16 07:09:25'),
(7, 9, 2.000, 0.000, 'low_stock', '2026-08-16 07:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `employee_purchases`
--

CREATE TABLE `employee_purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','partial','pending') NOT NULL DEFAULT 'paid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_purchase_items`
--

CREATE TABLE `employee_purchase_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT 1.000,
  `unit` varchar(30) NOT NULL,
  `purchase_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_sales`
--

CREATE TABLE `employee_sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','upi','card','mixed','credit') NOT NULL DEFAULT 'cash',
  `payment_status` enum('paid','partial','pending') NOT NULL DEFAULT 'paid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_sales`
--

INSERT INTO `employee_sales` (`id`, `invoice_number`, `customer_id`, `employee_id`, `subtotal`, `discount`, `gst_amount`, `grand_total`, `payment_method`, `payment_status`, `notes`, `created_at`) VALUES
(21, 'INV-2026-00001', 4, 5, 20.00, 0.00, 0.00, 20.00, 'cash', 'partial', NULL, '2026-08-15 20:53:18'),
(22, 'INV-2026-00002', 5, 5, 60.00, 0.00, 0.00, 60.00, 'upi', 'paid', NULL, '2026-08-15 20:54:49'),
(23, 'INV-2026-00003', 5, 5, 340.00, 0.00, 0.00, 340.00, 'cash', 'paid', NULL, '2026-08-15 20:55:47'),
(24, 'INV-2026-00004', 7, 8, 40.00, 0.00, 0.00, 40.00, 'cash', 'partial', NULL, '2026-08-16 06:31:21'),
(25, 'INV-2026-00005', 7, 8, 120.00, 0.00, 0.00, 120.00, 'cash', 'partial', NULL, '2026-08-16 06:32:04'),
(26, 'INV-2026-00006', 8, 8, 20.00, 0.00, 0.00, 20.00, 'cash', 'partial', NULL, '2026-08-16 06:39:27'),
(27, 'INV-2026-00007', 5, 7, 110.00, 0.00, 0.00, 110.00, 'cash', 'paid', NULL, '2026-08-16 07:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `employee_sale_items`
--

CREATE TABLE `employee_sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT 1.000,
  `unit` varchar(30) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_sale_items`
--

INSERT INTO `employee_sale_items` (`id`, `sale_id`, `product_id`, `product_name`, `quantity`, `unit`, `unit_price`, `discount`, `gst_percent`, `gst_amount`, `total_price`) VALUES
(42, 21, 8, 'potato', 1.000, 'piece', 20.00, 0.00, 0.00, 0.00, 20.00),
(43, 22, 8, 'potato', 3.000, 'piece', 20.00, 0.00, 0.00, 0.00, 60.00),
(44, 23, 8, 'potato', 17.000, 'piece', 20.00, 0.00, 0.00, 0.00, 340.00),
(45, 24, 8, 'potato', 2.000, 'piece', 20.00, 0.00, 0.00, 0.00, 40.00),
(46, 25, 8, 'potato', 6.000, 'piece', 20.00, 0.00, 0.00, 0.00, 120.00),
(47, 26, 8, 'potato', 1.000, 'piece', 20.00, 0.00, 0.00, 0.00, 20.00),
(48, 27, 9, 'Avcd', 2.000, 'piece', 25.00, 0.00, 0.00, 0.00, 50.00),
(49, 27, 8, 'potato', 3.000, 'piece', 20.00, 0.00, 0.00, 0.00, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee_stock_movements`
--

CREATE TABLE `employee_stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `movement_type` enum('purchase','sale','return','adjustment','damage','expired') NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_stock_movements`
--

INSERT INTO `employee_stock_movements` (`id`, `product_id`, `employee_id`, `movement_type`, `quantity`, `reference_id`, `note`, `created_at`) VALUES
(44, 8, 5, 'sale', 1.000, 21, 'Sale Invoice: INV-2026-00001', '2026-08-15 20:53:18'),
(45, 8, 5, 'sale', 3.000, 22, 'Sale Invoice: INV-2026-00002', '2026-08-15 20:54:49'),
(46, 8, 5, 'sale', 17.000, 23, 'Sale Invoice: INV-2026-00003', '2026-08-15 20:55:47'),
(47, 8, 8, 'sale', 2.000, 24, 'Sale Invoice: INV-2026-00004', '2026-08-16 06:31:21'),
(48, 8, 8, 'sale', 6.000, 25, 'Sale Invoice: INV-2026-00005', '2026-08-16 06:32:04'),
(49, 8, 8, 'sale', 1.000, 26, 'Sale Invoice: INV-2026-00006', '2026-08-16 06:39:27'),
(50, 9, 7, 'sale', 2.000, 27, 'Sale Invoice: INV-2026-00007', '2026-08-16 07:09:25'),
(51, 8, 7, 'sale', 3.000, 27, 'Sale Invoice: INV-2026-00007', '2026-08-16 07:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `employee_suppliers`
--

CREATE TABLE `employee_suppliers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_users`
--

CREATE TABLE `employee_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_otp` varchar(10) DEFAULT NULL,
  `reset_otp_expiry` datetime DEFAULT NULL,
  `role` enum('admin','employee') NOT NULL DEFAULT 'employee',
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_users`
--

INSERT INTO `employee_users` (`id`, `name`, `email`, `password`, `reset_otp`, `reset_otp_expiry`, `role`, `phone`, `profile_image`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(4, 'ram Sarkar', 'sribashsarkarblp@gmail.com', '$2y$10$9XqqfGPEH7IiY0zfXx5LzOIz1B8NloxCvo5VkXEPM85yrFEMR8CoG', NULL, NULL, 'admin', NULL, NULL, 'active', '2026-08-16 06:18:58', '2026-08-15 20:48:12', '2026-08-16 06:18:58'),
(5, 'sribashsarkar', 'sribashsarkar3467@gmail.com', '$2y$10$H1s54QOHj92kNPBDf7P1QOWNB35cAUyLJwZ71S2rYFUYQbOvV/XWK', NULL, NULL, 'employee', NULL, NULL, 'active', '2026-08-15 20:52:32', '2026-08-15 20:52:14', '2026-08-15 20:52:32'),
(6, 'Ajgar Ali', 'ajgarali70009@gmail.com', '$2y$10$iFpklTQKYJbshBtZ4l3Z5.pfofxKMw6rFfWXUnazrKC9XFrNJch8q', NULL, NULL, 'admin', NULL, NULL, 'active', '2026-08-16 06:25:19', '2026-08-16 06:25:03', '2026-08-16 06:25:19'),
(7, 'Ziaul', 'ziaulmandal20@gmail.com', '$2y$10$5BaUHu8WgBR0euH6/0UuDODG5mTr44gxVq1bvuF.I0gAft/OYCpvS', NULL, NULL, 'admin', NULL, NULL, 'active', '2026-08-16 06:47:10', '2026-08-16 06:29:17', '2026-08-16 06:47:10'),
(8, 'Ajgar Ali', 'u42954276@gmail.com', '$2y$10$QOnYTCAP7D8yfocjcN6qVueKMAD.eU87AzErdVpvkGWixhVwDVUTq', NULL, NULL, 'employee', NULL, NULL, 'active', '2026-08-16 07:35:05', '2026-08-16 06:29:33', '2026-08-16 07:35:05');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_purchases`
--

CREATE TABLE `inventory_purchases` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'pcs',
  `purchase_price` decimal(10,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_purchases`
--

INSERT INTO `inventory_purchases` (`id`, `product_name`, `quantity`, `unit`, `purchase_price`, `purchase_date`, `expiry_date`, `created_at`) VALUES
(10, '1 Clinic+ shampoo', 1.000, 'pcs', 11.91, '2026-06-11', NULL, '2026-06-11 10:51:01'),
(11, '1 sunsilk', 1.000, 'pcs', 11.91, '2026-06-11', NULL, '2026-06-11 10:51:29'),
(12, '2 heddensolder', 1.000, 'pcs', 25.00, '2026-06-11', NULL, '2026-06-11 10:52:51'),
(13, '45stafree', 1.000, 'pcs', 37.00, '2026-06-11', NULL, '2026-06-11 11:14:20'),
(14, '20ukunmaratel', 1.000, 'pcs', 13.20, '2026-06-11', NULL, '2026-06-11 11:17:08'),
(15, 'Kar', 10.000, 'pcs', 75.00, '2026-06-11', NULL, '2026-06-11 11:18:10'),
(16, '100vasmal', 1.000, 'pcs', 56.00, '2026-06-11', NULL, '2026-06-11 11:18:53'),
(17, 'Kishanmalom b', 1.000, 'box', 170.00, '2026-06-11', NULL, '2026-06-11 11:20:17'),
(18, 'Kishanmolom', 1.000, 'box', 70.00, '2026-06-11', NULL, '2026-06-11 11:21:28'),
(19, 'Nailpolish removar (ads)', 1.000, 'pcs', 12.00, '2026-06-11', NULL, '2026-06-11 11:22:49'),
(20, 'Baby brush', 1.000, 'pcs', 65.00, '2026-06-11', NULL, '2026-06-11 11:23:30'),
(21, 'Skin sunrise', 1.000, 'pcs', 35.00, '2026-06-11', NULL, '2026-06-11 11:24:02'),
(22, '25g fair garam', 1.000, 'pcs', 63.00, '2026-06-11', NULL, '2026-06-11 11:24:58'),
(23, '50g jasmin', 1.000, 'pcs', 17.50, '2026-06-11', NULL, '2026-06-11 11:26:42'),
(25, 'Himsital', 1.000, 'pcs', 55.00, '2026-06-11', NULL, '2026-06-11 11:32:28'),
(26, 'Vaslin bodylotion', 1.000, 'pcs', 78.00, '2026-06-11', NULL, '2026-06-11 11:33:10'),
(27, '90himalaya fech wash', 1.000, 'pcs', 68.00, '2026-06-11', NULL, '2026-06-11 11:34:13'),
(28, '200g jack bodyoil', 1.000, 'pcs', 95.00, '2026-06-11', NULL, '2026-06-11 11:34:46'),
(29, '200g keokarpin bodyoil', 1.000, 'pcs', 102.00, '2026-06-11', NULL, '2026-06-11 11:35:41'),
(30, '200g seba bodyoil', 1.000, 'pcs', 50.00, '2026-06-11', NULL, '2026-06-11 11:36:38'),
(31, '40 detol liquit', 1.000, 'pcs', 37.00, '2026-06-11', NULL, '2026-06-11 11:37:33'),
(32, '200 gkeokarpin hairoil', 1.000, 'pcs', 80.00, '2026-06-11', NULL, '2026-06-11 11:38:16'),
(33, '50takar suthal', 1.000, 'pcs', 45.00, '2026-06-11', NULL, '2026-06-11 12:13:36'),
(34, '58takar comfort', 1.000, 'pcs', 51.00, '2026-06-11', NULL, '2026-06-11 12:22:35'),
(35, 'Neha mehendi', 1.000, 'box', 156.00, '2026-06-11', NULL, '2026-06-11 12:23:15'),
(36, 'Kaveri mehendi', 1.000, 'box', 150.00, '2026-06-11', NULL, '2026-06-11 12:23:47'),
(37, 'Gaderset kakra pata', 1.000, 'pcs', 80.00, '2026-06-11', NULL, '2026-06-11 12:25:08'),
(38, '28 Geletgard rezar', 1.000, 'pcs', 20.50, '2026-06-11', NULL, '2026-06-11 12:27:01'),
(39, '1navoratna', 1.000, 'pcs', 336.00, '2026-06-11', NULL, '2026-06-11 12:37:31'),
(40, 'Tishu baby', 1.000, 'pcs', 50.00, '2026-06-11', NULL, '2026-06-11 12:38:17'),
(41, 'Kaka jira', 1.000, 'kg', 250.00, '2026-06-11', NULL, '2026-06-11 13:53:56'),
(42, '500salimar holud', 500.000, 'g', 120.00, '2026-06-11', NULL, '2026-06-11 13:55:15'),
(43, 'Potato chips', 1.000, 'box', 800.00, '2026-06-11', NULL, '2026-06-11 14:00:14'),
(44, 'Holud rasi', 1.000, 'kg', 185.00, '2026-06-11', NULL, '2026-06-11 14:06:55'),
(45, 'Kalo guna', 1.000, 'kg', 90.00, '2026-06-11', NULL, '2026-06-11 14:07:31'),
(46, 'Majhari mukhari', 1.000, 'pcs', 18.00, '2026-06-11', NULL, '2026-06-11 14:08:40'),
(47, 'Top', 1.000, 'pcs', 12.00, '2026-06-11', NULL, '2026-06-11 14:09:12'),
(48, 'Vitamin', 1.000, 'pcs', 5.00, '2026-06-11', NULL, '2026-06-11 14:09:48'),
(49, '500amulgold', 1.000, 'box', 980.00, '2026-06-11', NULL, '2026-06-11 14:13:18'),
(50, '5garammasola jar', 1.000, 'box', 440.00, '2026-06-11', NULL, '2026-06-11 14:14:12'),
(51, '1 litar sonali', 1.000, 'box', 2624.00, '2026-06-11', NULL, '2026-06-11 14:16:32'),
(52, '500 sonali', 1.000, 'box', 2970.00, '2026-06-11', NULL, '2026-06-11 14:17:24'),
(53, '10 gota garammasola', 1.000, 'box', 140.00, '2026-06-11', NULL, '2026-06-11 14:18:14'),
(54, 'Mukhari b', 1.000, 'pcs', 30.00, '2026-06-11', NULL, '2026-06-11 14:19:21'),
(55, 'M cil', 1.000, 'pcs', 170.00, '2026-06-11', NULL, '2026-06-11 14:19:49'),
(56, 'Karuna sutli', 1.000, 'kg', 140.00, '2026-06-11', NULL, '2026-06-11 14:21:02'),
(57, 'Super sutli', 1.000, 'kg', 130.00, '2026-06-11', NULL, '2026-06-11 14:21:35'),
(58, 'Mukhari c', 1.000, 'pcs', 13.00, '2026-06-11', NULL, '2026-06-11 14:23:17'),
(59, 'Guna', 1.000, 'kg', 92.00, '2026-06-11', NULL, '2026-06-11 14:23:44'),
(60, 'Rimjhim sari', 1.000, 'pcs', 200.00, '2026-06-11', NULL, '2026-06-11 14:25:34'),
(61, 'Headoffice sari', 1.000, 'pcs', 300.00, '2026-06-11', NULL, '2026-06-11 14:26:09'),
(62, 'Nippal kama', 1.000, 'box', 110.00, '2026-06-11', NULL, '2026-06-11 14:30:40'),
(63, 'Dona', 1.000, 'pcs', 220.00, '2026-06-11', NULL, '2026-06-11 14:31:26'),
(64, '5000 vimal basta', 1.000, 'box', 3250.00, '2026-06-11', NULL, '2026-06-11 14:34:00'),
(65, 'Chira basta', 1.000, 'box', 880.00, '2026-06-11', NULL, '2026-06-11 14:34:36'),
(66, 'K meghi', 1.000, 'box', 220.00, '2026-06-11', NULL, '2026-06-11 14:35:42'),
(67, '100 salimar holud', 1.000, 'kg', 255.00, '2026-06-11', NULL, '2026-06-11 14:36:59'),
(68, '100Sampa holud', 1.000, 'kg', 210.00, '2026-06-11', NULL, '2026-06-11 14:37:42'),
(69, '50g jiragura', 1.000, 'kg', 480.00, '2026-06-11', NULL, '2026-06-11 14:40:07'),
(70, 'Nima set', 1.000, 'pcs', 65.00, '2026-06-11', NULL, '2026-06-11 14:40:53'),
(71, '50g lanka', 1.000, 'kg', 400.00, '2026-06-11', NULL, '2026-06-11 14:42:54'),
(72, 'Mahabhog chal', 1.000, 'packet', 910.00, '2026-06-11', NULL, '2026-06-11 14:43:46'),
(73, 'Papar (papad)', 5.000, 'kg', 210.00, '2026-06-11', NULL, '2026-06-11 14:44:37'),
(74, '16aana chana', 1.000, 'kg', 280.00, '2026-06-11', NULL, '2026-06-11 14:46:07'),
(75, 'Kama dudh', 1.000, 'box', 415.00, '2026-06-11', NULL, '2026-06-11 14:47:49'),
(76, 'Tata cha agni', 1.000, 'packet', 155.00, '2026-06-11', NULL, '2026-06-11 14:49:20'),
(77, '1 rifine', 1.000, 'box', 1280.00, '2026-06-11', NULL, '2026-06-11 14:50:31'),
(78, '500 rifine', 1.000, 'box', 1300.00, '2026-06-11', NULL, '2026-06-11 14:50:55'),
(79, '30 merie', 1.000, 'box', 495.00, '2026-06-11', NULL, '2026-06-11 14:51:41'),
(80, '10 sunlight', 1.000, 'box', 1050.00, '2026-06-11', NULL, '2026-06-11 14:52:25'),
(81, 'K meghi', 1.000, 'box', 425.00, '2026-06-11', NULL, '2026-06-11 14:53:00'),
(82, 'Chokor', 1.000, 'packet', 1270.00, '2026-06-11', NULL, '2026-06-11 16:09:00'),
(83, '50g nihar', 1.000, 'pcs', 17.50, '2026-06-11', NULL, '2026-06-11 16:16:42'),
(84, 'Colgate brush', 1.000, 'pcs', 120.00, '2026-06-11', NULL, '2026-06-11 16:17:21'),
(85, 'Ponds pata', 1.000, 'pcs', 105.00, '2026-06-11', NULL, '2026-06-11 16:17:45'),
(86, 'Medikar ukun shampoo', 1.000, 'pcs', 175.00, '2026-06-11', NULL, '2026-06-11 16:18:52'),
(87, 'Kama nailpalish', 1.000, 'pcs', 70.00, '2026-06-11', NULL, '2026-06-11 16:19:49'),
(88, 'Roopa', 1.000, 'pcs', 6.00, '2026-06-11', NULL, '2026-06-11 16:21:11'),
(89, '15 gelet blet', 1.000, 'pcs', 108.00, '2026-06-11', NULL, '2026-06-11 16:22:25'),
(90, 'Joy sabuj', 1.000, 'box', 190.00, '2026-06-11', NULL, '2026-06-11 16:23:02'),
(91, '15 godrej', 1.000, 'box', 125.00, '2026-06-11', NULL, '2026-06-11 16:23:34'),
(92, '15 dove saban', 1.000, 'pcs', 12.50, '2026-06-11', NULL, '2026-06-11 16:24:20'),
(93, '100g Amla', 1.000, 'pcs', 43.33, '2026-06-11', NULL, '2026-06-11 16:25:31'),
(94, '200g jasmin', 1.000, 'pcs', 81.00, '2026-06-11', NULL, '2026-06-11 16:26:10'),
(95, '100g bajaj', 1.000, 'pcs', 75.00, '2026-06-11', NULL, '2026-06-11 16:27:19'),
(96, '10 pata kajal', 1.000, 'pcs', 72.00, '2026-06-11', NULL, '2026-06-11 16:28:10'),
(97, 'Fair pata', 1.000, 'pcs', 215.00, '2026-06-11', NULL, '2026-06-11 16:28:47'),
(98, 'Set wet', 1.000, 'pcs', 105.00, '2026-06-11', NULL, '2026-06-11 16:29:29'),
(99, 'Vasmol pata', 1.000, 'pcs', 80.00, '2026-06-11', NULL, '2026-06-11 16:30:28'),
(100, 'Elosine', 1.000, 'box', 589.00, '2026-06-11', NULL, '2026-06-11 16:30:59'),
(101, '200g nihar 88', 1.000, 'pcs', 78.00, '2026-06-11', NULL, '2026-06-11 16:31:43'),
(102, '20 Godrej powder', 1.000, 'box', 85.00, '2026-06-11', NULL, '2026-06-11 16:35:29'),
(103, '3 kabja', 1.000, 'box', 145.00, '2026-06-11', NULL, '2026-06-11 16:38:08'),
(104, '4 kabja', 1.000, 'box', 145.00, '2026-06-11', NULL, '2026-06-11 16:38:32'),
(105, 'Baltu all', 1.000, 'kg', 75.00, '2026-06-11', NULL, '2026-06-11 16:40:32'),
(106, 'Kishan chaka', 1.000, 'kg', 160.00, '2026-06-11', NULL, '2026-06-11 16:41:11'),
(107, 'Mota sutli super chaka', 1.000, 'kg', 130.00, '2026-06-11', NULL, '2026-06-11 16:42:12'),
(108, 'Maharaja kal bati', 10.000, 'pcs', 200.00, '2026-06-11', NULL, '2026-06-11 16:43:09'),
(109, 'Masha dhup', 5.000, 'pcs', 380.00, '2026-06-13', NULL, '2026-06-13 09:12:37'),
(110, '5 kirim blast', 14.000, 'pcs', 480.00, '2026-06-13', NULL, '2026-06-13 09:15:11'),
(111, 'C Chhoyabin', 5.000, 'kg', 400.00, '2026-06-13', NULL, '2026-06-13 12:16:16'),
(112, 'B chhoyabin', 1.000, 'kg', 100.00, '2026-06-13', NULL, '2026-06-13 12:17:14'),
(113, 'Chini', 1.000, 'pcs', 2300.00, '2026-06-14', NULL, '2026-06-14 02:38:00'),
(114, 'Barli', 1.000, 'kg', 50.00, '2026-06-14', NULL, '2026-06-14 02:38:20'),
(115, '5 nakuldana', 1.000, 'packet', 65.00, '2026-06-14', NULL, '2026-06-14 02:38:56'),
(116, '5 loknath', 1.000, 'packet', 50.00, '2026-06-14', NULL, '2026-06-14 02:39:25'),
(117, 'B chhoyabin', 5.000, 'kg', 450.00, '2026-06-14', NULL, '2026-06-14 02:43:53'),
(118, '500 Bestchayech', 1.000, 'box', 1390.00, '2026-06-14', NULL, '2026-06-14 02:45:41'),
(119, '10 Sampa holud', 1.000, 'packet', 145.00, '2026-06-14', NULL, '2026-06-14 02:48:13'),
(120, '100 takar b lux', 4.000, 'pcs', 95.00, '2026-06-14', NULL, '2026-06-14 02:49:27'),
(121, '10 safed', 1.000, 'box', 950.00, '2026-06-18', NULL, '2026-06-18 10:48:45'),
(122, '10packet chhoyabin', 1.000, 'box', 380.00, '2026-06-18', NULL, '2026-06-18 10:52:49'),
(123, 'Varat', 1.000, 'box', 250.00, '2026-06-18', NULL, '2026-06-18 10:55:11'),
(124, 'Nirma set', 3.000, 'pcs', 65.00, '2026-06-18', NULL, '2026-06-18 10:56:29'),
(125, 'Luch muri', 1.000, 'box', 690.00, '2026-06-18', NULL, '2026-06-18 10:57:12'),
(126, 'Kata supari valo', 1.000, 'kg', 460.00, '2026-06-20', NULL, '2026-06-20 17:42:43'),
(127, 'Kata supari kama', 1.000, 'kg', 410.00, '2026-06-20', NULL, '2026-06-20 17:43:22'),
(128, 'Flake', 1.000, 'box', 82.00, '2026-06-20', NULL, '2026-06-20 17:43:57'),
(129, 'Signature+jarda', 1.000, 'packet', 430.00, '2026-06-20', NULL, '2026-06-20 17:45:11'),
(130, 'Mint', 1.000, 'box', 110.00, '2026-06-20', NULL, '2026-06-20 17:45:58'),
(131, '10 darymilk', 1.000, 'box', 500.00, '2026-06-20', NULL, '2026-06-20 17:47:14'),
(132, 'Fech&body', 1.000, 'pcs', 35.00, '2026-06-27', NULL, '2026-06-27 02:03:35'),
(133, 'Charcal', 1.000, 'box', 72.00, '2026-06-27', NULL, '2026-06-27 02:04:23'),
(134, 'Blender', 1.000, 'pcs', 12.00, '2026-06-27', NULL, '2026-06-27 02:05:09'),
(135, 'Baby lipistic', 1.000, 'box', 108.00, '2026-06-27', NULL, '2026-06-27 02:06:10'),
(136, 'Ukun chironi mota', 1.000, 'box', 36.00, '2026-06-27', NULL, '2026-06-27 02:08:40'),
(137, 'Kajol pata', 1.000, 'box', 70.00, '2026-06-27', NULL, '2026-06-27 02:10:15'),
(138, 'Sent pata', 1.000, 'pcs', 35.00, '2026-06-27', NULL, '2026-06-27 02:12:05'),
(139, 'Ayna', 1.000, 'box', 84.00, '2026-06-27', NULL, '2026-06-27 02:13:57'),
(140, 'Ayna', 1.000, 'box', 140.00, '2026-06-27', NULL, '2026-06-27 02:14:16'),
(141, 'Ayna', 1.000, 'box', 216.00, '2026-06-27', NULL, '2026-06-27 02:14:33'),
(142, 'Comfy', 1.000, 'pcs', 21.00, '2026-06-27', NULL, '2026-06-27 02:15:59'),
(143, 'Ponds lotion', 1.000, 'pcs', 105.00, '2026-06-27', NULL, '2026-06-27 02:18:25'),
(144, '100keokarpin hairoil', 1.000, 'pcs', 45.00, '2026-06-27', NULL, '2026-06-27 02:24:27'),
(145, '30 Cleen clear fech was', 1.000, 'pcs', 23.00, '2026-06-27', NULL, '2026-06-27 02:35:45'),
(146, 'Clean clear fech was', 1.000, 'pcs', 84.00, '2026-06-27', NULL, '2026-06-27 02:36:53'),
(147, 'Clo', 1.000, 'box', 360.00, '2026-06-27', NULL, '2026-06-27 02:38:51'),
(148, '100g jasmin', 1.000, 'pcs', 37.00, '2026-06-27', NULL, '2026-06-27 02:50:21'),
(149, 'Kar', 1.000, 'box', 75.00, '2026-06-27', NULL, '2026-06-27 03:10:31'),
(150, '200g seba body oil', 1.000, 'pcs', 50.00, '2026-06-27', NULL, '2026-06-27 03:21:06'),
(151, '10 Tata', 1.000, 'box', 170.00, '2026-06-28', NULL, '2026-06-28 02:16:56'),
(153, '20jankee', 1.000, 'box', 300.00, '2026-06-28', NULL, '2026-06-28 02:17:58'),
(156, '2ltr sprite', 1.000, 'box', 800.00, '2026-06-28', NULL, '2026-06-28 02:20:07'),
(157, '10 Litchi', 1.000, 'box', 280.00, '2026-06-28', NULL, '2026-06-28 02:21:33'),
(158, '10 sting', 1.000, 'box', 170.00, '2026-06-29', NULL, '2026-06-29 15:42:31'),
(159, '10 campa', 1.000, 'box', 240.00, '2026-06-29', NULL, '2026-06-29 15:42:54'),
(160, '10 jankee', 1.000, 'pcs', 7.33, '2026-06-29', NULL, '2026-06-29 15:43:31'),
(161, '20 sting', 1.000, 'box', 410.00, '2026-06-29', NULL, '2026-06-29 15:43:53'),
(162, '20 sprite', 1.000, 'box', 490.00, '2026-06-29', NULL, '2026-06-29 15:44:24'),
(163, '2ltr mango', 1.000, 'box', 540.00, '2026-06-29', NULL, '2026-06-29 15:45:02'),
(164, '600 mango', 1.000, 'box', 450.00, '2026-06-29', NULL, '2026-06-29 15:45:24'),
(165, '10 packet fruty', 1.000, 'box', 230.00, '2026-06-29', NULL, '2026-06-29 15:50:38'),
(166, '10 packet maaza', 1.000, 'box', 310.00, '2026-06-29', NULL, '2026-06-29 15:51:12'),
(167, 'L huggis', 1.000, 'pcs', 160.00, '2026-06-29', NULL, '2026-06-29 15:53:15'),
(168, 'M huggis', 1.000, 'pcs', 135.00, '2026-06-29', NULL, '2026-06-29 15:53:49'),
(169, '500 campa', 1.000, 'box', 400.00, '2026-07-05', NULL, '2026-07-05 08:37:35'),
(170, 'Amul kool', 1.000, 'box', 530.00, '2026-07-05', NULL, '2026-07-05 08:39:37'),
(171, 'Sada til', 1.000, 'kg', 180.00, '2026-07-09', NULL, '2026-07-09 10:59:16'),
(172, 'Tarkata', 1.000, 'kg', 78.00, '2026-07-09', NULL, '2026-07-09 11:14:14'),
(173, '2\"kabja', 1.000, 'pcs', 6.00, '2026-07-24', NULL, '2026-07-24 16:18:49'),
(174, '2.5\"kabja', 1.000, 'pcs', 6.66, '2026-07-24', NULL, '2026-07-24 16:19:40'),
(175, '3\"kabja', 1.000, 'pcs', 7.50, '2026-07-24', NULL, '2026-07-24 16:20:22'),
(176, '75 sitkani', 1.000, 'pcs', 19.00, '2026-07-24', NULL, '2026-07-24 16:21:16'),
(177, 'Alominiyam wasar', 1.000, 'kg', 800.00, '2026-07-24', NULL, '2026-07-24 16:21:47'),
(178, 'Vitamin', 1.000, 'packet', 4.50, '2026-07-24', NULL, '2026-07-24 16:22:18'),
(179, 'Tin skuru', 1.000, 'kg', 110.00, '2026-07-24', NULL, '2026-07-24 16:23:01'),
(180, '5\"handel', 1.000, 'pcs', 16.00, '2026-07-24', NULL, '2026-07-24 16:24:11'),
(181, '6\"handel', 1.000, 'pcs', 17.00, '2026-07-24', NULL, '2026-07-24 16:24:37'),
(182, '1\"-1.5\" tarkata', 1.000, 'kg', 80.00, '2026-07-24', NULL, '2026-07-24 16:25:51'),
(183, '2\"-4\"tarkata', 1.000, 'kg', 70.00, '2026-07-24', NULL, '2026-07-24 16:37:31'),
(184, '2ltr campa', 1.000, 'pcs', 65.00, '2026-07-25', NULL, '2026-07-25 06:00:06'),
(185, '10mango', 500.000, 'g', 260.00, '2026-07-27', NULL, '2026-07-27 02:26:16'),
(186, '1200 mango', 1.000, 'box', 550.00, '2026-08-14', NULL, '2026-08-14 08:13:33'),
(187, '20 mango', 1.000, 'box', 360.00, '2026-08-14', NULL, '2026-08-14 08:15:26');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_users`
--

CREATE TABLE `inventory_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_users`
--

INSERT INTO `inventory_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'a@gmail.com', '$2y$10$NJizaWNvqpxcubLYdKRqIe1XQKTbmaJUSICmuLkNCo98V6LVhKCNu', '2026-06-11 08:30:33'),
(2, 'b@gmail.com', '$2y$10$gAhyYzIyPddUbLYfr0e8su.cv8nZBAo77ZsXRI4mNTchRYsOlYdOu', '2026-06-11 08:32:44'),
(3, 'Ziaul', '$2y$10$XuM0htEIYdGtP8PQzIRgeueDCaIiq2zI6a.ls5vGKj2yq4hCYVWfq', '2026-06-11 08:51:21'),
(4, 'Ziaull', '$2y$10$q9dQ9R9kIqUH5kUOleIawOaD7qBA35.rdm9L31nw90kJ9gZspD8li', '2026-06-13 08:41:40');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `offer_name` varchar(255) NOT NULL,
  `offer_type` enum('flat','percent') NOT NULL,
  `offer_value` decimal(10,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `priority` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `product_id`, `category_id`, `offer_name`, `offer_type`, `offer_value`, `start_date`, `end_date`, `status`, `priority`, `created_at`, `updated_at`) VALUES
(85, 132, NULL, 'Grand launch sale', 'flat', 4.00, NULL, NULL, 'active', 0, '2026-06-20 16:22:24', '2026-06-20 16:22:24'),
(86, 128, NULL, 'Grand launch sale', 'flat', 20.00, NULL, NULL, 'active', 0, '2026-06-20 16:25:09', '2026-06-20 16:25:09'),
(92, 143, NULL, 'Grand launch sale', 'flat', 94.00, NULL, NULL, 'active', 0, '2026-06-26 17:17:18', '2026-06-26 17:17:18'),
(93, 135, NULL, 'Summer Beauty Offer', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-06-28 02:28:00', '2026-06-28 02:28:00'),
(94, 126, NULL, 'Summer Beauty Offer', 'flat', 20.00, NULL, NULL, 'active', 0, '2026-06-28 02:32:05', '2026-06-28 02:32:05'),
(95, 119, NULL, 'Summer Beauty Offer', 'flat', 110.00, NULL, NULL, 'active', 0, '2026-06-28 03:22:40', '2026-06-28 03:22:40'),
(96, 118, NULL, 'Summer Beauty Offer', 'flat', 10.00, NULL, NULL, 'active', 0, '2026-06-28 03:23:44', '2026-06-28 03:23:44'),
(97, 106, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-06-28 03:44:36', '2026-06-28 03:44:36'),
(98, 104, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-06-28 03:46:05', '2026-06-28 03:46:05'),
(99, 103, NULL, 'Grand launch sale', 'flat', 3.00, NULL, NULL, 'active', 0, '2026-06-28 06:15:33', '2026-06-28 06:15:33'),
(100, 102, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-06-28 08:09:02', '2026-06-28 08:09:02'),
(101, 101, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-06-28 08:11:09', '2026-06-28 08:11:09'),
(102, 100, NULL, 'Grand launch sale', 'flat', 3.00, NULL, NULL, 'active', 0, '2026-06-28 08:11:38', '2026-06-28 08:11:38'),
(103, 93, NULL, 'Grand launch sale', 'flat', 130.00, NULL, NULL, 'active', 0, '2026-06-28 08:44:56', '2026-06-28 08:44:56'),
(104, 87, NULL, 'Summer Beauty Offer', 'flat', 10.00, NULL, NULL, 'active', 0, '2026-06-28 11:12:54', '2026-06-28 11:12:54'),
(105, 86, NULL, 'Summer Beauty Offer', 'flat', 4.00, NULL, NULL, 'active', 0, '2026-06-28 11:13:36', '2026-06-28 11:13:36'),
(106, 82, NULL, 'Grand launch sale', 'flat', 20.00, NULL, NULL, 'active', 0, '2026-06-28 12:14:35', '2026-06-28 12:14:35'),
(107, 78, NULL, 'Grand launch sale', 'flat', 130.00, NULL, NULL, 'active', 0, '2026-06-28 12:20:46', '2026-06-28 12:20:46'),
(108, 73, NULL, 'Grand Launch Sale', 'flat', 5.00, NULL, NULL, 'active', 0, '2026-06-28 12:21:23', '2026-06-28 12:21:23'),
(109, 74, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-06-28 12:21:49', '2026-06-28 12:21:49'),
(110, 70, NULL, 'Grand Launch Sale', 'flat', 1.16, NULL, NULL, 'active', 0, '2026-06-29 02:18:27', '2026-06-29 02:18:27'),
(111, 69, NULL, 'Grand Launch Sale', 'flat', 3.80, NULL, NULL, 'active', 0, '2026-06-29 02:19:42', '2026-06-29 02:19:42'),
(112, 67, NULL, 'Grand Launch Sale', 'flat', 5.00, NULL, NULL, 'active', 0, '2026-06-29 02:21:53', '2026-06-29 02:21:53'),
(113, 66, NULL, 'Grand launch sale', 'flat', 40.00, NULL, NULL, 'active', 0, '2026-06-29 02:22:37', '2026-06-29 02:22:37'),
(114, 60, NULL, 'Summer Beauty Offer', 'flat', 94.00, NULL, NULL, 'active', 0, '2026-06-29 03:14:42', '2026-06-29 03:14:42'),
(118, 144, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-07-06 16:13:48', '2026-07-06 16:13:48'),
(122, 156, NULL, 'Grand launch sale', 'flat', 10.00, NULL, NULL, 'active', 0, '2026-07-17 13:03:55', '2026-07-17 13:03:55'),
(123, 155, NULL, 'Grand launch sale', 'flat', 10.00, NULL, NULL, 'active', 0, '2026-07-17 13:04:23', '2026-07-17 13:04:23'),
(124, 153, NULL, 'Grand launch sale', 'flat', 5.00, NULL, NULL, 'active', 0, '2026-07-17 13:05:36', '2026-07-17 13:05:36'),
(125, 180, NULL, 'Grand launch sale', 'flat', 5.00, NULL, NULL, 'active', 0, '2026-07-29 12:45:14', '2026-07-29 12:45:14'),
(127, 189, NULL, 'Grand launch sale', 'flat', 5.00, NULL, NULL, 'active', 0, '2026-07-30 17:43:25', '2026-07-30 17:43:25'),
(128, 190, NULL, 'Grand launch sale', 'flat', 9.00, NULL, NULL, 'active', 0, '2026-07-30 17:45:54', '2026-07-30 17:45:54'),
(130, 194, NULL, 'Grand launch sale', 'flat', 3.00, NULL, NULL, 'active', 0, '2026-07-31 11:17:48', '2026-07-31 11:17:48'),
(131, 196, NULL, 'Grand launch sale', 'flat', 10.00, NULL, NULL, 'active', 0, '2026-07-31 11:34:16', '2026-07-31 11:34:16'),
(132, 198, NULL, 'Grand launch sale', 'flat', 21.00, NULL, NULL, 'active', 0, '2026-07-31 11:59:31', '2026-07-31 11:59:31'),
(133, 188, NULL, 'Grand launch sale', 'flat', 3.00, NULL, NULL, 'active', 0, '2026-08-01 12:02:45', '2026-08-01 12:02:45'),
(134, 204, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-08-01 12:30:09', '2026-08-01 12:30:09'),
(135, 205, NULL, 'Grand launch sale', 'flat', 2.00, NULL, NULL, 'active', 0, '2026-08-01 12:56:43', '2026-08-01 12:56:43');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_charge` decimal(8,2) DEFAULT 0.00,
  `tax_amount` decimal(8,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled','returned') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `delivery_address` text NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `delivery_eta` time DEFAULT NULL,
  `assigned_delivery_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tracking_status` varchar(50) DEFAULT 'Order Placed',
  `admin_remark` text DEFAULT NULL,
  `delivery_otp` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `delivery_charge`, `tax_amount`, `grand_total`, `status`, `payment_status`, `delivery_address`, `pincode`, `delivery_eta`, `assigned_delivery_id`, `notes`, `created_at`, `updated_at`, `tracking_status`, `admin_remark`, `delivery_otp`) VALUES
(70, 151, 'NEX202606103523', 30.00, 50.00, 5.40, 85.40, 'cancelled', 'refunded', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-06-10 16:57:16', '2026-06-10 17:00:25', 'ordered', '', NULL),
(71, 152, 'NEX202606126955', 86.00, 50.00, 15.48, 151.48, 'cancelled', 'refunded', 'Ek Tower, Newtown, New Town, Presidency Division, West Bengal, 700161, India', '000000', NULL, NULL, NULL, '2026-06-12 12:46:02', '2026-06-16 02:01:59', 'delivered', '', NULL),
(72, 152, 'NEX202606178345', 180.00, 50.00, 32.40, 262.40, 'cancelled', 'failed', '6JJ5+578, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-06-17 09:45:36', '2026-06-17 11:19:18', 'ordered', '', NULL),
(73, 154, 'NEX202606196642', 2732.80, 50.00, 491.90, 3274.70, 'out_for_delivery', 'pending', 'No Address Provided', '000000', NULL, NULL, NULL, '2026-06-19 10:02:42', '2026-06-22 09:34:49', 'packed', '', NULL),
(74, 152, 'NEX202607014591', 318.00, 10.00, 3.18, 331.18, 'pending', 'pending', '6HVM+QGH, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-01 05:58:28', '2026-07-01 05:58:28', 'Order Placed', NULL, NULL),
(75, 152, 'NEX202607011053', 180.00, 10.00, 0.00, 190.00, 'delivered', 'paid', '6HVM+QGH, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, 4, NULL, '2026-07-01 06:00:52', '2026-07-05 10:27:33', 'delivered', '', '821665'),
(76, 152, 'NEX202607018997', 30.00, 10.00, 0.00, 40.00, 'cancelled', 'pending', '6HVM+QGH, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-01 07:14:07', '2026-07-03 09:56:20', 'Order Placed', NULL, NULL),
(77, 152, 'NEX202607033242', 3728.00, 10.00, 0.00, 3738.00, 'cancelled', 'pending', 'Sribashsarkar • 9083646603, 1, balaram pur, Landmark: shjs, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-07-03 15:40:13', '2026-07-03 15:41:48', 'Order Placed', NULL, NULL),
(78, 152, 'NEX202607037639', 159.00, 10.00, 0.00, 169.00, 'cancelled', 'pending', 'Sribashsarkar • 9083646603, 1, balaram pur, Landmark: shjs, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-07-03 15:41:32', '2026-07-03 15:41:43', 'Order Placed', NULL, NULL),
(79, 152, 'NEX202607039040', 60.00, 10.00, 0.00, 70.00, 'pending', 'pending', 'Sribashsarkar • 9083646603, 1, balaram pur, Landmark: shjs, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-07-03 15:43:37', '2026-07-03 15:43:37', 'Order Placed', NULL, NULL),
(80, 152, 'NEX202607036897', 159.00, 10.00, 0.00, 169.00, 'pending', 'pending', '6JH4+47, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-03 16:16:21', '2026-07-03 16:16:21', 'Order Placed', NULL, NULL),
(81, 152, 'NEX202607034346', 159.00, 10.00, 0.00, 169.00, 'delivered', 'paid', '6JH4+47, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, 4, NULL, '2026-07-03 18:13:22', '2026-07-03 18:39:04', 'delivered', '', '267929'),
(82, 156, 'NEX202607049761', 477.00, 10.00, 0.00, 487.00, 'delivered', 'paid', '6JH4+47, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, 4, NULL, '2026-07-04 06:43:32', '2026-07-04 09:24:35', 'delivered', '', '584476'),
(83, 156, 'NEX202607045981', 318.00, 10.00, 0.00, 328.00, 'pending', 'pending', '6JH4+47, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-04 09:43:29', '2026-07-04 09:43:29', 'Order Placed', NULL, NULL),
(84, 165, 'NEX202607053797', 390.00, 10.00, 0.00, 400.00, 'cancelled', 'pending', 'shouldhukri joradighi', '000000', NULL, NULL, NULL, '2026-07-05 04:44:40', '2026-07-05 05:16:35', 'ordered', '', NULL),
(85, 152, 'NEX202607057179', 159.00, 10.00, 0.00, 169.00, 'cancelled', 'pending', '6HQR+JXF, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-05 05:12:48', '2026-07-05 05:12:55', 'Order Placed', NULL, NULL),
(86, 152, 'NEX202607057288', 159.00, 10.00, 0.00, 169.00, 'pending', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-05 09:54:00', '2026-07-05 09:54:00', 'Order Placed', NULL, NULL),
(87, 165, 'NEX202607057411', 12075.00, 10.00, 0.00, 12085.00, 'cancelled', 'pending', 'Ajgar • 8967092471, 76, shouldhukri, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-07-05 09:55:04', '2026-07-05 09:55:13', 'Order Placed', NULL, NULL),
(88, 152, 'NEX202607052682', 2634.00, 10.00, 0.00, 2644.00, 'pending', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-05 10:32:48', '2026-07-05 10:32:48', 'Order Placed', NULL, NULL),
(89, 167, 'NEX202607063676', 90.00, 10.00, 0.00, 100.00, 'delivered', 'paid', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-06 15:51:20', '2026-07-06 15:52:46', 'delivered', '', NULL),
(90, 165, 'NEX202607125652', 1020.00, 10.00, 0.00, 1030.00, 'delivered', 'paid', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-12 05:54:05', '2026-07-12 05:59:05', 'delivered', '', NULL),
(91, 152, 'NEX202607123576', 318.00, 10.00, 0.00, 328.00, 'pending', 'pending', '6HWV+GXX, Jiranpur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-12 06:43:23', '2026-07-12 06:43:23', 'Order Placed', NULL, NULL),
(92, 154, 'NEX202607126909', 240.00, 10.00, 0.00, 250.00, 'cancelled', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-07-12 08:59:17', '2026-07-12 09:00:12', 'Order Placed', NULL, NULL),
(93, 154, 'NEX202608019650', 178.00, 10.00, 0.00, 188.00, 'delivered', 'paid', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, 4, NULL, '2026-08-01 12:10:54', '2026-08-01 13:55:33', 'delivered', '', '210096'),
(94, 152, 'NEX202608019523', 5.00, 10.00, 0.00, 15.00, 'cancelled', 'pending', '6HWV+GXX, Jiranpur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-08-01 14:28:14', '2026-08-01 14:28:37', 'Order Placed', NULL, NULL),
(95, 170, 'NEX202608079072', 30.00, 10.00, 0.00, 40.00, 'cancelled', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-08-07 02:31:03', '2026-08-07 02:32:14', 'Order Placed', NULL, NULL),
(96, 175, 'NEX202608089112', 45.00, 10.00, 0.00, 55.00, 'cancelled', 'pending', 'Amur Hamja • 7364997817, 135, shoul dhukri, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-08-08 15:06:36', '2026-08-08 15:07:32', 'Order Placed', NULL, NULL),
(97, 176, 'NEX202608098931', 30.00, 10.00, 0.00, 40.00, 'cancelled', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-08-09 15:16:35', '2026-08-09 15:17:55', 'Order Placed', NULL, NULL),
(98, 152, 'NEX202608145800', 35.00, 10.00, 0.00, 45.00, 'pending', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-08-14 14:21:52', '2026-08-14 14:21:52', 'Order Placed', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(81, 72, 61, 2, 90.00),
(82, 73, 69, 33, 38.80),
(83, 73, 70, 15, 46.16),
(84, 73, 71, 4, 190.00),
(85, 74, 60, 2, 159.00),
(86, 75, 61, 2, 90.00),
(88, 77, 60, 18, 159.00),
(89, 77, 61, 8, 90.00),
(90, 77, 63, 2, 44.00),
(91, 77, 85, 1, 58.00),
(92, 78, 60, 1, 159.00),
(93, 79, 62, 1, 60.00),
(94, 80, 60, 1, 159.00),
(95, 81, 60, 1, 159.00),
(96, 82, 60, 3, 159.00),
(97, 83, 60, 2, 159.00),
(98, 84, 61, 3, 90.00),
(99, 84, 62, 1, 60.00),
(100, 84, 92, 1, 60.00),
(101, 85, 60, 1, 159.00),
(102, 86, 60, 1, 159.00),
(103, 87, 67, 69, 175.00),
(104, 88, 60, 16, 159.00),
(105, 88, 61, 1, 90.00),
(106, 89, 61, 1, 90.00),
(107, 90, 78, 1, 190.00),
(108, 90, 79, 5, 60.00),
(109, 90, 89, 2, 75.00),
(110, 90, 93, 2, 190.00),
(111, 91, 60, 2, 159.00),
(112, 92, 146, 2, 60.00),
(113, 92, 148, 2, 60.00),
(114, 93, 185, 1, 10.00),
(115, 93, 188, 1, 48.00),
(116, 93, 189, 1, 120.00),
(117, 94, 169, 1, 5.00),
(118, 95, 149, 2, 5.00),
(119, 95, 150, 2, 5.00),
(120, 95, 152, 2, 5.00),
(121, 96, 149, 4, 5.00),
(122, 96, 152, 3, 5.00),
(123, 96, 159, 1, 10.00),
(124, 97, 146, 1, 30.00),
(125, 98, 156, 1, 35.00);

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` enum('privacy_policy','terms_conditions','refund_policy','shipping_policy','cancellation_policy','about_us','contact_us','faq','custom') DEFAULT 'custom',
  `short_description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `visibility` enum('public','private') DEFAULT 'public',
  `is_featured` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(100) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `weight` decimal(8,3) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stock` int(11) DEFAULT 0,
  `attributes` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `discount_price`, `sku`, `stock_quantity`, `category_id`, `images`, `weight`, `is_active`, `created_at`, `updated_at`, `stock`, `attributes`) VALUES
(60, 'Medisalic Ointment (Clobetasol Propionate & Salicylic Acid) মেডিছিলিক ক্রিম', 'medisalic-ointment-clobetasol-propionate-salicylic-acid', 'Medisalic Ointment is a topical skin treatment containing Clobetasol Propionate and Salicylic Acid. It is commonly prescribed for certain skin conditions associated with inflammation, redness, itching, and scaling. The formulation helps soften thickened skin and supports relief from skin discomfort when used under medical supervision.\r\n\r\nKey Features:\r\n• Contains Clobetasol Propionate and Salicylic Acid\r\n• Helps manage inflammatory skin conditions\r\n• Easy-to-apply ointment formula\r\n• Fast absorption\r\n• Suitable for external use only\r\n\r\nDirections for Use:\r\nApply a thin layer to the affected area as directed by a healthcare professional.\r\n\r\nSafety Information:\r\n• For external use only\r\n• Avoid contact with eyes\r\n• Keep out of reach of children\r\n• Use only under medical supervision', 159.00, 65.00, 'MEDIS686', 20, 53, '[\"1781606073_8043_0.jpg\"]', 0.200, 1, '2026-06-16 10:34:33', '2026-06-29 03:14:42', 0, '{\"Brand\":\"Torque\",\"Expiry Date\":\"2027-06-01\"}'),
(61, 'Keo Karpin Light Hair Oil with Olive Oil & Vitamin E - 200ml কেউ কারপিন তেল', 'keo-karpin-light-hair-oil-with-olive-oil-vitamin-e-200ml', 'Keo Karpin Light Hair Oil is a non-sticky hair oil enriched with Olive Oil and Vitamin E that helps nourish the scalp and promote healthy, shiny hair. Its lightweight formula penetrates deeply into the scalp without leaving a greasy feel, making it suitable for daily use.\r\n\r\nKey Benefits:\r\n• Non-sticky formula\r\n• Enriched with Olive Oil & Vitamin E\r\n• Deep scalp nourishment\r\n• Helps maintain healthy and shiny hair\r\n• Suitable for daily use\r\n• Light and easy-to-apply texture\r\n\r\nDirections:\r\nApply an adequate amount to the scalp and hair. Massage gently and leave for a few hours or overnight before washing if desired.', 100.00, NULL, 'KEOKA378', 20, 53, '[\"1781683825_1323_0.png\",\"1781683825_6118_1.png\"]', 0.200, 1, '2026-06-17 08:10:25', '2026-08-14 12:21:35', 0, '{\"Brand\":\"Keo Karpin\",\"Size\":\"200ml\",\"Expiry Date\":\"2028-06-17\"}'),
(62, 'Keo Karpin Olivoyl Moisturizing Body Oil - 100ml', 'keo-karpin-olivoyl-moisturizing-body-oil-100ml', 'Keo Karpin Olivoyl Moisturizing Body Oil is a premium body oil enriched with imported Olive Oil that helps nourish, moisturize and soften the skin. Its lightweight non-sticky formula absorbs easily into the skin, helping maintain a healthy glow and smooth texture throughout the day.\r\n\r\nKey Benefits:\r\n• Enriched with imported Olive Oil\r\n• Deep moisturization for dry skin\r\n• Non-sticky formula\r\n• Helps improve skin softness\r\n• Supports healthy glowing skin\r\n• Suitable for daily body care\r\n\r\nHow to Use:\r\nApply generously on clean skin and massage gently until absorbed. Best used after bathing.', 60.00, NULL, 'KEOKA270', 20, 53, '[\"1781684134_9011_0.png\"]', 0.100, 1, '2026-06-17 08:15:34', '2026-06-17 08:15:34', 20, '{\"Brand\":\"Keo Karpin\",\"Size\":\"100ml\",\"Expiry Date\":\"2028-06-17\"}'),
(63, 'Navratna Ayurvedic Oil Cool 45ml নব রত্ন ঠান্ডা তেল', 'navratna-ayurvedic-oil-cool-45ml', 'Navratna Ayurvedic Oil Cool 45ml is a popular Ayurvedic hair oil enriched with 9 natural herbs. It provides a cooling sensation, helps relieve stress and fatigue, and promotes relaxation. Regular use helps nourish the scalp and supports healthy hair care. Suitable for daily use.', 44.00, NULL, 'NAVRA738', 20, 53, '[\"1781685590_6069_0.png\"]', NULL, 1, '2026-06-17 08:39:50', '2026-06-29 02:24:12', 0, '{\"Brand\":\"Navratna\",\"Size\":\"45ml\",\"Expiry Date\":\"2029-06-17\"}'),
(65, 'Vaseline Intensive Care Deep Moisture Body Lotion 85ml ভেসলিন বডি লোশন', 'vaseline-intensive-care-deep-moisture-body-lotion-85ml', 'Vaseline Intensive Care Deep Moisture Body Lotion provides long-lasting hydration for dry skin. Enriched with Ceramide and Hyaluron, it helps restore the skin\'s moisture barrier and keeps skin soft, smooth, and nourished for up to 48 hours. The non-sticky formula absorbs quickly and is suitable for daily use.', 90.00, NULL, 'VASEL969', 20, 53, '[\"1781686561_5597_0.png\"]', NULL, 1, '2026-06-17 08:56:01', '2026-06-29 02:23:17', 0, '{\"Brand\":\"Vaseline\",\"Size\":\"85ml\",\"Expiry Date\":\"2028-06-17\"}'),
(66, 'Him Shital Ayurvedic Sugandhi Thanda Tail 120ml হিম শীতল ঠান্ডা তেল', 'him-shital-ayurvedic-sugandhi-thanda-tail-120ml', 'Him Shital Ayurvedic Sugandhi Thanda Tail is a traditional Ayurvedic cooling hair oil enriched with natural herbal extracts. It helps provide a soothing and refreshing sensation, supports scalp care, and may help reduce headache, stress, and body fatigue. Suitable for regular scalp and hair massage.', 140.00, 100.00, 'HIMSH898', 20, 53, '[\"1781687618_6820_0.png\"]', NULL, 1, '2026-06-17 09:13:38', '2026-06-29 02:22:37', 0, '{\"Brand\":\"Sundarban Ayurved Bhawan\",\"Size\":\"120ml (100ml + 20ml Extra)\",\"Expiry Date\":\"2029-06-17\"}'),
(67, 'SESA Ayurvedic Hair Oil 100ml সিসা তেল', 'sesa-ayurvedic-hair-oil-100ml', 'SESA Ayurvedic Hair Oil is a traditional Ayurvedic formulation made with natural herbs and nourishing oils. It helps reduce hair fall, strengthens roots, promotes healthy hair growth and improves scalp health. Suitable for regular use.', 175.00, 170.00, 'SESAA852', 20, 54, '[\"1781687661_5243_0.png\"]', 0.100, 1, '2026-06-17 09:14:21', '2026-06-29 02:21:53', 0, '{\"Size\":\"100ml\",\"Expiry Date\":\"2029-06-17\"}'),
(68, 'Boroline Suthol Active Neem Body Hygiene Liquid 100ml সুথল', 'boroline-suthol-active-neem-body-hygiene-liquid-100ml', 'Boroline Suthol Active Neem Liquid is enriched with Neem, Aloe Vera and Turmeric. It helps maintain personal hygiene and provides relief from itching, rashes and prickly heat. Suitable for daily use.', 50.00, NULL, 'BOROL424', 20, 53, '[\"1781687835_9213_0.png\"]', 0.100, 1, '2026-06-17 09:17:15', '2026-06-29 02:20:26', 0, '{\"Brand\":\"Boroline\",\"Size\":\"100ml\",\"Expiry Date\":\"2029-06-17\"}'),
(69, 'Cosvate-GM Cream 20g কসভেট GM', 'cosvate-gm-cream-20g', 'Cosvate-GM Cream contains Clobetasol Propionate, Gentamicin and Miconazole Nitrate. It is commonly prescribed for certain inflammatory skin conditions with fungal or bacterial infections. Use only under medical supervision.', 38.80, 35.00, 'COSVA913', 20, 53, '[\"1781688097_7962_0.png\"]', 0.020, 1, '2026-06-17 09:21:37', '2026-06-29 02:19:42', 0, '{\"Brand\":\"Eris Oaknet\",\"Size\":\"20g\",\"Expiry Date\":\"2029-06-17\"}'),
(70, 'Clobetamil-G Skin Cream 30g কোলোবিটামিল  G', 'clobetamil-g-skin-cream-30g', 'Clobetamil-G Skin Cream is a topical corticosteroid and antibacterial cream used for the treatment of various skin conditions as directed by a physician.', 46.16, 45.00, 'CLOBE941', 20, 53, '[\"1781688238_8162_0.png\"]', 0.300, 1, '2026-06-17 09:23:58', '2026-06-29 02:18:27', 0, '{\"Brand\":\"Clobetamil\",\"Size\":\"30g\",\"Expiry Date\":\"2028-06-17\"}'),
(71, 'Kesh King Gold Ayurvedic Oil 100ml কেশ কিং তেল', 'kesh-king-gold-ayurvedic-oil-100ml', 'Kesh King Gold Ayurvedic Oil is an advanced Ayurvedic hair oil formulated with 21 herbs and Gro-Biotin technology. It helps reduce hair fall, promotes new hair growth, nourishes the scalp, and supports stronger, healthier hair. Suitable for moderate to severe hair fall and regular scalp care.', 190.00, NULL, 'KESHK844', 20, 54, '[\"1781688341_6447_0.png\"]', 0.100, 1, '2026-06-17 09:25:41', '2026-06-29 02:17:25', 0, '{\"Brand\":\"Kesh King\",\"Size\":\"100ml\",\"Expiry Date\":\"2029-06-17\"}'),
(72, 'Veet Pure Hair Removal Cream Sensitive Skin Aloe Vera 30g  ভীট', 'veet-pure-hair-removal-cream-sensitive-skin-aloe-vera-30g', 'Veet Pure Hair Removal Cream for Sensitive Skin with Aloe Vera Extract. Removes unwanted hair effectively while leaving skin soft, smooth and moisturized.', 99.00, NULL, 'VEETP732', 20, 53, '[\"1781688429_9504_0.png\",\"1781688429_3493_1.png\"]', 0.030, 1, '2026-06-17 09:27:09', '2026-06-29 02:16:46', 0, '{\"Brand\":\"Veet\",\"Size\":\"30g\",\"Expiry Date\":\"2028-06-17\"}'),
(73, 'Traditional Red Alta Liquid আলতা', 'traditional-red-alta-liquid', 'Traditional Red Alta is a classic cosmetic liquid used to decorate feet and hands, especially during weddings, festivals, religious ceremonies, and cultural celebrations. Its rich, vibrant red color enhances beauty and adds a traditional touch to every occasion. The formula is easy to apply, dries quickly, and provides long-lasting color. Suitable for regular and special-event use, this alta helps create an elegant and graceful appearance while preserving cultural heritage.', 35.00, 30.00, 'TRADI746', 20, 53, '[\"1781688894_6890_0.png\"]', 0.550, 1, '2026-06-17 09:34:54', '2026-06-28 12:21:23', 0, '{\"Brand\":\"Traditional Alta\",\"Size\":\"55ml\",\"Expiry Date\":\"2030-06-17\"}'),
(74, 'Fem Creme Bleach Saffron & Milk Healthy Glow ফেম', 'fem-creme-bleach-saffron-milk-healthy-glow', 'Fem Creme Bleach Saffron & Milk Healthy Glow is specially formulated to enhance your skin\'s natural radiance. Enriched with the goodness of saffron and milk, it helps lighten the appearance of facial hair, giving your skin a brighter, smoother, and more even-toned look. The gentle formula provides a healthy glow and leaves the skin looking fresh, soft, and revitalized after use.', 37.00, 35.00, 'FEMCR799', 20, 53, '[\"1781689213_3834_0.png\"]', 0.080, 1, '2026-06-17 09:40:13', '2026-06-28 12:21:49', 0, '{\"Brand\":\"Fem\",\"Size\":\"8g\",\"Expiry Date\":\"2028-06-17\"}'),
(75, 'PRODUCT NAME Super Vasmol 33 Kesh Kala Oil Based Hair Colour ভেসমোল চুল কালার', 'product-name-super-vasmol-33-kesh-kala-oil-based-hair-colour', 'Super Vasmol 33 Kesh Kala Oil Based Hair Colour is a premium hair colouring solution designed to provide rich, natural-looking black hair. Enriched with the goodness of Amla, Almond, and Hibiscus Oils, it helps nourish and condition the hair while delivering long-lasting colour coverage. Its oil-based formula is free from ammonia and peroxide, making it a convenient choice for covering grey hair and enhancing hair appearance. Suitable for regular use, it leaves hair looking shiny, smooth, and naturally black.', 35.00, NULL, 'PRODU367', 20, 53, '[\"1781696445_8222_0.png\"]', 0.050, 1, '2026-06-17 11:40:45', '2026-06-28 12:22:15', 0, '{\"Brand\":\"BRAND Vasmol\",\"Size\":\"50ml\",\"Expiry Date\":\"2028-06-17\"}'),
(77, 'PRODUCT NAME Super Vasmol 33 Kesh Kala Oil Based Hair Colour ভেসমোল চুল কালার', 'product-name-super-vasmol-33-kesh-kala-oil-based-hair-colour-2', 'Super Vasmol 33 Kesh Kala Oil Based Hair Colour is a premium hair colouring solution designed to provide rich, natural-looking black hair. Enriched with the goodness of Amla, Almond, and Hibiscus Oils, it helps nourish and condition the hair while delivering long-lasting colour coverage. Its oil-based formula is free from ammonia and peroxide, making it a convenient choice for covering grey hair and enhancing hair appearance. Suitable for regular use, it leaves hair looking shiny, smooth, and naturally black.', 35.00, NULL, 'PRODU282', 20, 53, '[\"1781698433_2451_0.png\"]', 0.050, 1, '2026-06-17 12:13:53', '2026-06-28 12:22:34', 0, '{\"Brand\":\"BRAND Vasmol\",\"Size\":\"50ml\",\"Expiry Date\":\"2028-06-17\"}'),
(78, 'Twinkle Cream 15g টুইনকেল ক্রিম', 'twinkle-cream-15g', 'Twinkle Cream is a topical prescription cream containing Hydroquinone, Tretinoin and Mometasone Furoate. It is commonly used under medical supervision for the treatment of melasma, hyperpigmentation, dark spots and uneven skin tone. For external use only. Use strictly as directed by a healthcare professional.', 190.00, 60.00, 'TWINK892', 20, 53, '[\"1781699929_1029_0.png\"]', NULL, 1, '2026-06-17 12:38:49', '2026-06-28 12:20:46', 0, '{\"Brand\":\"Twinkle \\/ Austro Labs\",\"Size\":\"15g\",\"Expiry Date\":\"2028-06-17\"}'),
(79, 'Black Nite Kesh Kala Tel Super ব্ল্যাক নাইট চুল কালা তেল', 'black-nite-kesh-kala-tel-super', 'Black Nite Kesh Kala Tel Super is a hair oil enriched with almond oil. It is marketed for helping grey hair appear naturally black and promoting healthy-looking hair. Suitable for regular external use on hair and scalp.', 60.00, NULL, 'BLACK241', 20, 54, '[\"1781700834_1036_0.png\"]', 0.120, 1, '2026-06-17 12:53:54', '2026-06-28 12:19:03', 0, '{\"Brand\":\"Black Nite\",\"Size\":\"100ml\",\"Expiry Date\":\"2029-06-17\"}'),
(80, 'WhiteTone Face Powder (Softshade Formula) 20g হোয়াইট টোন পাউডার', 'whitetone-face-powder-softshade-formula-20g', 'WhiteTone Face Powder with Softshade Formula delivers a smooth, fresh, and natural-looking finish. Its lightweight formula blends easily on the skin and helps maintain a soft, radiant appearance throughout the day. Suitable for daily use.\r\n\r\nNet Weight: 20g\r\nBrand: WhiteTone\r\nUsage: Apply evenly to the face as needed.\r\nStorage: Keep in a cool, dry place.\r\nFor external use only.', 65.00, NULL, 'WHITE985', 20, 53, '[\"1781702486_6771_0.png\"]', NULL, 1, '2026-06-17 13:21:26', '2026-06-28 12:17:34', 0, '{\"Brand\":\"WhiteTone\",\"Size\":\"30g\",\"Expiry Date\":\"2028-06-17\"}'),
(81, 'Super Vasmol 33 Kesh Kala Oil Based Hair colour ভেসমোল চুল কালার', 'super-vasmol-33-kesh-kala-oil-based-hair-colour', 'Super Vasmol 33 Kesh Kala is an oil-based hair colour enriched with natural oils. It provides rich black hair colour while helping maintain softness and shine. The formula uses natural oxygen technology and is free from ammonia and peroxide. Ideal for covering grey hair and achieving a natural-looking black finish.', 65.00, NULL, 'SUPER657', 20, 54, '[\"1781703074_2616_0.png\"]', 0.100, 1, '2026-06-17 13:31:14', '2026-06-28 12:15:49', 0, '{\"Brand\":\"Vasmol\",\"Size\":\"100ml\",\"Expiry Date\":\"2028-06-17\"}'),
(82, 'Padmini Kesh Kala Natural Black Hair Color (5ml) পদ্মনী চুল কালার', 'padmini-kesh-kala-natural-black-hair-color-5ml', 'Padmini Kesh Kala Natural Black Hair Color is a popular ammonia-free and peroxide-free hair color solution designed for both men and women. It gradually turns grey hair into a natural black shade, providing a simple and convenient coloring experience. Suitable for regular use and easy to apply.', 70.00, 50.00, 'PADMI145', 20, 54, '[\"1781705797_4983_0.png\"]', NULL, 1, '2026-06-17 14:16:37', '2026-06-28 12:14:35', 0, '{\"Brand\":\"Padmini\",\"Size\":\"100ml\",\"Expiry Date\":\"2028-06-17\"}'),
(83, 'Godrej Good Knight Flash Liquid Vaporiser Combo Pack (45ml) মশা নিয়ন্ত্রণের তেল', 'godrej-good-knight-flash-liquid-vaporiser-combo-pack-45ml', 'Godrej Good Knight Flash Liquid Vaporiser is an effective mosquito repellent solution designed to provide protection from mosquitoes and help prevent mosquito-borne diseases. The pack contains 1 vaporiser machine and 1 refill (45ml). Easy to use and suitable for homes, bedrooms, offices, and indoor spaces.', 105.00, NULL, 'GODRE799', 20, 55, '[\"1781707064_8128_0.png\"]', NULL, 1, '2026-06-17 14:37:44', '2026-06-28 12:05:18', 0, '{\"Brand\":\"Godrej Good Knight\",\"Size\":\"45ml (1 Machine + 1 Refill)\",\"Expiry Date\":\"2028-06-17\"}'),
(84, 'LuvLap Moisturising Wipes with Aloe Vera টিস্যু', 'luvlap-moisturising-wipes-with-aloe-vera', '88708.jpg\r\nProduct image and discription \r\n\r\nBased on the image, here is a product description:\r\n\r\nLuvLap Moisturising Wipes with Aloe Vera\r\n\r\nhttps://images.openai.com/static-rsc-4/kbWm5pFariAbDdR_i0TCrAcFZLI4XZ5vc9IAoHBO70bOxKJZlDB6urQACAYWZ-poZgAGAYzIV74C1emH_uYhMTp9uLp9c30nhdNfPYvj2_Kjzf8pI6Gm8V2VieslGQv0_veZOaUp5Ya768cMTrgfHQkB97beLU4534ynmT1MHZIv28x1cn1TCzFPxJ46mkEr?purpose=fullsize\r\n\r\nhttps://images.openai.com/static-rsc-4/Q5Um1ydQ1kqDaMcZWTwE835f1HW9_LZP_9Lzc3d_MCO08F6ds23md_1P2lWJU6rUb0WaXI4jdYJBU8MkXRhGA3uBJx0t1E1Pc1048Mtb3zWPac_E8Ytp9g1iksYxPqOgzIbnS596aNfvVP824u6cXK8q19A1N1QDaq8k6PufMlxwqL0QAptaistZkBGYZGkQ?purpose=fullsize\r\n\r\nhttps://images.openai.com/static-rsc-4/qIdTVmK01YsxwUoBIEuVe7nhD5L9gRuWjbcTW8xOtBzep8H_HwB4dfErxQ9QSdAQl-cgiMB9ncgSmW91fWkYviU0EoAyNp4Z0_RgUrC0QNa37pwY1U00ZEbCVXtwj76IzFKYTJuq6wLjTlGIaOC1hEPEYKANEyinHQUmAn86TfgIzMLc7f744N81T04dt7VC?purpose=fullsize\r\n\r\nhttps://images.openai.com/static-rsc-4/64yFTaet97-g--80ZdKxWYI_e9YAUv-xDeeEKebDeCMFLBun02UIZ-xdXNyY5IFJ2JlQmtqCItxOhgsTqRzT_ILhx7Hz0xX8poHDdSc5tLV-oIuFZTbGiZQNH3hCQugZKu4oFbzi4zO22eKc0OdQtkId1PfPYEJobfiemy5hZwTWGu2v-mrZ2HWGfE1WuuuE?purpose=fullsize\r\n\r\nhttps://images.openai.com/static-rsc-4/pzQB75VrIcmlNL769yWYqEC04wq3lTDaD5_cAD1UwUjwzRowcqV_uUDSSyDN99oicH0KAiIQCsemRm0ZhnGvcWB-82q0a0R8bZA2vgGI5Wem1fR9u-xHFK3QxNzBdUoUOHHkTa5S4qHokux-fcE8XQfh27UoczySg9oZxj98U9V03MFpkwdqmlu_3XondATY?purpose=fullsize\r\nProduct Name: LuvLap Moisturising Wipes with Aloe Vera\r\n\r\nBrand: LuvLap\r\n\r\nPack Size: 72 Wipes\r\n\r\nKey Features:\r\n\r\nEnriched with Aloe Vera to help soothe and moisturize the skin.\r\n\r\nContains Vitamin E, which helps nourish and protect delicate skin.\r\n\r\nSoft and gentle wipes suitable for babies and sensitive skin.\r\n\r\nConvenient resealable lid helps keep wipes fresh and moist.\r\n\r\nDesigned for everyday cleaning of hands, face, and diaper area.\r\n\r\nBenefits:\r\n\r\nCleans effectively while maintaining skin moisture.\r\n\r\nHelps prevent dryness and irritation.\r\n\r\nGentle enough for regular use on delicate baby skin.\r\n\r\nIdeal For:\r\n\r\nBabies and toddlers\r\n\r\nDiaper changes\r\n\r\nQuick cleanups at home or while traveling', 100.00, NULL, 'LUVLA572', 20, 53, '[\"1781711605_4579_0.jpg\",\"1781711605_8485_1.jpg\",\"1781711605_4708_2.jpg\"]', 0.000, 1, '2026-06-17 15:53:25', '2026-06-28 12:03:57', 0, '{\"Brand\":\"LuvLap\",\"Size\":\"72wipes\",\"Expiry Date\":\"2028-05-01\"}'),
(85, 'Comfort Fabric Conditioner – Lily Fresh (210 ml) কমফোর্ট', 'comfort-fabric-conditioner-lily-fresh-210-ml', 'Comfort Lily Fresh Fabric Conditioner keeps clothes soft, fresh, and pleasantly scented for a long time. It helps reduce fabric roughness, protects fibers, and makes ironing easier.\r\n\r\nKey Features:\r\n• Long-lasting Lily Fresh fragrance\r\n• Makes clothes soft and smooth\r\n• Suitable for everyday use\r\n• Helps maintain fabric quality\r\n• Easy to use with all types of washing methods\r\n\r\nBrand: Comfort\r\nVariant: Lily Fresh\r\nNet Volume: 210 ml', 58.00, NULL, 'COMFO175', 20, 55, '[\"1781754458_8619_0.png\"]', NULL, 1, '2026-06-18 03:47:38', '2026-06-28 12:02:47', 0, '{\"Brand\":\"Comfort\",\"Size\":\"210ml\",\"Expiry Date\":\"2027-09-30\"}'),
(86, 'Candid Clotrimazole Dusting Powder – Original (60 g) ঘামছি মারা পাউডার', 'candid-clotrimazole-dusting-powder-original-60-g', 'Candid Clotrimazole Dusting Powder is an antifungal powder formulated with Clotrimazole 1% w/w to help manage common fungal skin infections. It provides relief from itching, sweat rash, skin irritation, and discomfort caused by fungal infections.\r\n\r\nKey Features:\r\n• Contains Clotrimazole 1% w/w\r\n• Helps manage fungal skin infections\r\n• Provides relief from itching and sweat rash\r\n• Keeps affected areas dry and comfortable\r\n• Suitable for external use only\r\n• Clinically proven formula\r\n\r\nBrand: Candid\r\nVariant: Original\r\nNet Quantity: 60 g\r\n\r\nDirections for Use:\r\nClean and dry the affected area before application. Apply the powder as directed by a healthcare professional.\r\n\r\nWarning:\r\nFor external use only. Avoid contact with eyes. Keep out of reach of children.', 104.00, 100.00, 'CANDI850', 20, 53, '[\"1781754826_3686_0.png\"]', NULL, 1, '2026-06-18 03:53:46', '2026-06-28 11:13:36', 0, '{\"Brand\":\"Candid\",\"Size\":\"60g\",\"Expiry Date\":\"2028-06-18\"}'),
(87, 'Neha Fast Colour Mehendi Cone (25 g) হাত মেহেন্দি', 'neha-fast-colour-mehendi-cone-25-g', 'Neha Fast Colour Mehendi Cone is a ready-to-use herbal mehendi cone designed for creating beautiful, long-lasting designs on hands and feet. Its smooth texture ensures easy application and rich color development.\r\n\r\nKey Features:\r\n• Ready-to-use mehendi cone\r\n• Rich and long-lasting color\r\n• Smooth and easy application\r\n• Suitable for festive occasions, weddings, and celebrations\r\n• Herbal formulation\r\n\r\nBrand: Neha\r\nVariant: Fast Colour\r\nNet Quantity: 25 g\r\n\r\nDirections for Use:\r\nApply the mehendi evenly on clean and dry skin. Leave it on for several hours for a darker stain. Avoid washing the area immediately after removing the dried mehendi.\r\n\r\nStorage Instructions:\r\nStore in a cool and dry place away from direct sunlight. Keep out of reach of children.\r\n\r\nFor external use only.', 25.00, 15.00, 'NEHAF380', 30, 53, '[\"1781755089_9901_0.png\"]', NULL, 1, '2026-06-18 03:58:09', '2026-06-28 11:12:54', 0, '{\"Brand\":\"Neha\",\"Size\":\"25g\",\"Expiry Date\":\"2027-06-18\"}'),
(88, 'Johnson\'s Baby Oil with Vitamin E (50 ml) বেবী বডি অয়েল', 'johnsons-baby-oil-with-vitamin-e-50-ml', 'Johnson\'s Baby Oil with Vitamin E helps nourish and protect your baby\'s delicate skin by locking in moisture. Its gentle formula is dermatologist tested and clinically proven to be mild, making it suitable for daily use.\r\n\r\nKey Features:\r\n• Enriched with Vitamin E\r\n• Helps lock in moisture for soft and smooth skin\r\n• Dermatologist tested\r\n• Clinically proven mildness\r\n• Free from added parabens, dyes, and phthalates\r\n• Suitable for daily baby massage\r\n\r\nBrand: Johnson\'s\r\nVariant: Baby Oil with Vitamin E\r\nNet Quantity: 50 ml\r\n\r\nDirections for Use:\r\nApply gently all over the body after bathing or during massage for soft and healthy-looking skin.\r\n\r\nStorage Instructions:\r\nStore in a cool and dry place away from direct sunlight.\r\n\r\nFor external use only. Keep out of reach of children.', 75.00, NULL, 'JOHNS480', 20, 53, '[\"1781755391_5099_0.png\"]', 0.050, 1, '2026-06-18 04:03:11', '2026-06-28 11:12:26', 0, '{\"Brand\":\"Johnson\'s\",\"Size\":\"50ml\",\"Expiry Date\":\"2029-06-18\"}'),
(89, 'All Out Ultra Liquid Vaporiser Refill (45 ml) মশা তাড়ানোর তেল', 'all-out-ultra-liquid-vaporiser-refill-45-ml', 'All Out Ultra Liquid Vaporiser Refill is designed to provide effective protection against mosquitoes. Its advanced formula helps kill mosquitoes faster and is effective against mosquitoes that may spread dengue, malaria, and chikungunya.\r\n\r\nKey Features:\r\n• Kills mosquitoes up to 30% faster\r\n• Effective against dengue, malaria, and chikungunya mosquitoes\r\n• Fits all standard liquid vaporiser machines\r\n• Easy-to-use refill pack\r\n• Suitable for everyday indoor use\r\n\r\nBrand: All Out\r\nVariant: Ultra Liquid Vaporiser Refill\r\nNet Quantity: 45 ml\r\n\r\nDirections for Use:\r\nInsert the refill bottle into a compatible liquid vaporiser machine and plug it into an electrical socket. Use in a well-ventilated room.\r\n\r\nStorage Instructions:\r\nStore in a cool, dry place away from direct sunlight, heat, and open flames.\r\n\r\nWarning:\r\nKeep out of reach of children and pets. Avoid direct contact with skin and eyes. For household use only.', 75.00, NULL, 'ALLOU742', 20, 55, '[\"1781755579_7080_0.png\"]', 0.045, 1, '2026-06-18 04:06:19', '2026-06-28 11:11:30', 0, '{\"Brand\":\"All Out\",\"Size\":\"45ml\",\"Expiry Date\":\"2028-06-18\"}'),
(90, 'NIVEA Soft Light Moisturizing Cream (25 ml) নিভিয়া সফট ক্রিম', 'nivea-soft-light-moisturizing-cream-25-ml', 'NIVEA Soft Light Moisturizing Cream is a lightweight, fast-absorbing formula that provides long-lasting hydration for your skin. Enriched with Vitamin E and Jojoba Oil, it helps keep your skin soft, smooth, and refreshed.\r\n\r\nSuitable for use on the face, hands, and body, this non-greasy cream is ideal for daily skincare.\r\n\r\nKey Features:\r\n• Light and non-greasy formula\r\n• Provides long-lasting moisturization\r\n• Enriched with Vitamin E and Jojoba Oil\r\n• Fast-absorbing cream\r\n• Suitable for face, hands, and body\r\n• Ideal for everyday use\r\n\r\nBrand: NIVEA\r\nVariant: Soft\r\nNet Content: 25 ml', 55.00, NULL, 'NIVEA881', 20, 53, '[\"1781755788_2728_0.png\"]', 0.250, 1, '2026-06-18 04:09:48', '2026-06-28 09:18:41', 0, '{\"Brand\":\"NIVEA\",\"Size\":\"25ml\",\"Expiry Date\":\"2029-06-18\"}'),
(91, 'Johnson\'s Baby Face & Body Cream with Aloe & Vitamin B5 (30 g)জনসেন্স বেবী ক্রিম', 'johnsons-baby-face-body-cream-with-aloe-vitamin-b5-30-g', 'Johnson\'s Baby Face & Body Cream with Aloe & Vitamin B5 is specially formulated to nourish and protect your baby\'s delicate skin. Enriched with aloe vera, Vitamin B5, and coconut oil, it provides long-lasting moisturization and helps keep your baby\'s skin soft and smooth.\r\n\r\nKey Features:\r\n• Enriched with Aloe Vera and Vitamin B5\r\n• Helps protect skin from dryness\r\n• Provides up to 72 hours of moisture lock\r\n• Developed for newborn and delicate skin\r\n• Hypoallergenic and pH balanced\r\n• Tested with pediatricians and dermatologists\r\n• No added parabens, dyes, or phthalates\r\n\r\nBrand: Johnson\'s\r\nVariant: Face & Body Cream\r\nNet Quantity: 30 g\r\n\r\nDirections for Use:\r\nApply gently on clean and dry skin as often as needed, especially after bathing.\r\n\r\nStorage Instructions:\r\nStore in a cool and dry place away from direct sunlight.\r\n\r\nFor external use only. Keep out of reach of children.', 65.00, NULL, 'JOHNS200', 15, 53, '[\"1781756407_1950_0.png\"]', 0.300, 1, '2026-06-18 04:20:07', '2026-06-28 09:17:35', 0, '{\"Brand\":\"Johnson\'s\",\"Size\":\"30g\",\"Expiry Date\":\"2029-06-18\"}'),
(92, 'Product Name: Johnson\'s Milk + Rice Lotion 50ml  জনসেন্স বেবী লোশন', 'product-name-johnsons-milk-rice-lotion-50ml-category-baby-care-baby-lotion-sku-jmlr-50ml-product-description-johnsons-milk-rice-lotion-is-specially-formulated-to-nourish-and-moisturize-your-babys-delicate-skin-for-up-to-24-hours-enriched-with-milk-protein', 'Johnson\'s Milk + Rice Lotion is specially formulated to nourish and moisturize your baby\'s delicate skin for up to 24 hours. Enriched with milk proteins and rice extracts, this gentle lotion helps keep skin soft, smooth, and healthy. The formula is clinically proven mildness, pediatrician tested, and free from added parabens, dyes, and phthalates.', 60.00, NULL, 'PRODU478', 15, 53, '[\"1781757110_9867_0.png\"]', 0.080, 1, '2026-06-18 04:31:50', '2026-06-28 08:54:53', 0, '{\"Brand\":\"Johnson\'s Baby\",\"Size\":\"50ml\",\"Expiry Date\":\"2029-06-18\"}'),
(93, 'Skinsunrise Cream 20g স্কিনসানরাইস ক্রিম', 'skinsure-cream-20g', 'Skinsure Cream is a topical prescription cream containing Hydroquinone, Tretinoin and Mometasone Furoate. It is commonly used for the treatment of melasma, hyperpigmentation, dark spots and uneven skin tone. The cream helps reduce skin discoloration and promotes a clearer, brighter complexion when used as directed by a healthcare professional.\r\n\r\nKey Features:\r\n• Helps reduce dark spots and pigmentation\r\n• Supports skin tone improvement\r\n• Easy topical application\r\n• Suitable for prescribed dermatological use\r\n\r\nNet Weight: 20g\r\nUsage: Use only as directed by a physician.', 190.00, 60.00, 'SKINS779', 15, 53, '[\"1781782951_6033_0.png\"]', 0.020, 1, '2026-06-18 11:42:31', '2026-06-28 08:44:56', 0, '{\"Brand\":\"Ind Life\",\"Size\":\"20g\",\"Expiry Date\":\"2028-06-18\"}'),
(94, 'Pond\'s Bright Beauty Niasorcinol Spot Repair Formula Light Crème With UV Filter পন্ডস ব্রাইট বিউটি ক্রিম', 'ponds-bright-beauty-niasorcinol-spot-repair-formula-light-crme-with-uv-filter', 'Pond\'s Bright Beauty Spot Repair Formula Light Crème is enriched with Niasorcinol technology and UV filters to help reduce dark spots, improve skin radiance, and provide a brighter, even-looking complexion. Its lightweight, non-greasy formula absorbs quickly and is suitable for daily use.\r\n\r\nKey Features:\r\n• Helps reduce the appearance of dark spots\r\n• Brightens and evens skin tone\r\n• Lightweight cream with UV protection\r\n• Suitable for daily skincare routine\r\n• Quick-absorbing and non-sticky formula\r\n\r\nNet Weight: 23g', 105.00, NULL, 'PONDS276', 15, 53, '[\"1781783146_2162_0.png\"]', NULL, 1, '2026-06-18 11:45:46', '2026-06-28 08:36:40', 0, '{\"Brand\":\"Pond\'s\",\"Size\":\"23g\",\"Expiry Date\":\"2028-06-18\"}'),
(95, 'Dettol Antiseptic Liquid 100ml ডেটল লিকুইট', 'dettol-antiseptic-liquid-100ml', 'Dettol Antiseptic Liquid is a trusted antiseptic disinfectant that helps protect against germs and bacteria. It can be used for first aid, personal hygiene, and household disinfection. The formula kills 99.99% of illness-causing germs and provides effective protection for everyday use.\r\n\r\nKey Features:\r\n• Kills 99.99% of germs\r\n• Suitable for first aid antiseptic use\r\n• Helps protect against infection from cuts and wounds\r\n• Can be used for personal hygiene and household cleaning\r\n• Trusted germ protection formula\r\n\r\nNet Volume: 100ml', 40.00, NULL, 'DETTO157', 15, 53, '[\"1781783323_6088_0.png\"]', NULL, 1, '2026-06-18 11:48:43', '2026-06-28 08:20:49', 0, '{\"Brand\":\"Dettol\",\"Size\":\"100ml\",\"Expiry Date\":\"2029-06-18\"}'),
(96, 'Fair And Handsome Long Lasting Radiance Cream হ্যান্ডসাম ক্রিম', 'fair-and-handsome-long-lasting-radiance-cream', 'Fair And Handsome Long Lasting Radiance Cream is specially formulated for men\'s skin to provide a brighter and more radiant appearance. Enriched with advanced skin-brightening ingredients and sun protection, it helps reduce the appearance of dark spots and supports an even skin tone.\r\n\r\nKey Features:\r\n• Up to 8 hours brighter look\r\n• Helps reduce dark spots\r\n• Visible radiance in 1 week\r\n• Suitable for tough male skin\r\n• Includes sun protection\r\n• Lightweight and easy to apply\r\n\r\nIdeal For:\r\nDaily skincare and skin brightening for men.', 40.00, NULL, 'FAIRA231', 15, 53, '[\"1781783469_5551_0.png\"]', NULL, 1, '2026-06-18 11:51:09', '2026-06-28 08:20:08', 0, '{\"Brand\":\"Fair And Handsome\",\"Size\":\"15g\",\"Expiry Date\":\"2028-06-18\"}'),
(97, 'Himalaya Purifying Neem Face Wash 50ml হিমালয়া ফেসওয়াস', 'himalaya-purifying-neem-face-wash-50ml', 'Himalaya Purifying Neem Face Wash is a soap-free herbal face cleanser enriched with Neem and Turmeric. It helps cleanse the skin, remove excess oil, and prevent pimples while keeping the skin fresh and healthy. Suitable for daily use and all skin types, especially oily and acne-prone skin.\r\n\r\nKey Features:\r\n• Purifies and cleanses skin effectively\r\n• Helps prevent pimples and acne\r\n• Removes excess oil and impurities\r\n• Soap-free herbal formulation\r\n• Suitable for daily use\r\n• Skin-friendly and recyclable packaging\r\n\r\nNet Volume: 50ml', 90.00, NULL, 'HIMAL663', 15, 53, '[\"1781783607_1182_0.png\"]', NULL, 1, '2026-06-18 11:53:27', '2026-06-28 08:17:59', 0, '{\"Brand\":\"Himalaya\",\"Size\":\"50ml\",\"Expiry Date\":\"2028-06-18\"}'),
(98, 'Sunsilk Stunning Black Shine Shampoo সানসিল্ক শ্যাম্পু', 'sunsilk-stunning-black-shine-shampoo', 'Sunsilk Stunning Black Shine Shampoo is specially formulated with Amla, Pearl Protein and Vitamin E to help keep hair looking healthy, smooth and shiny. The nourishing formula gently cleanses the scalp and hair while enhancing natural black hair shine.\r\n\r\nKey Features:\r\n• Enhances black hair shine\r\n• Enriched with Amla, Pearl Protein and Vitamin E\r\n• Gently cleanses hair and scalp\r\n• Leaves hair soft and manageable\r\n• Pleasant long-lasting fragrance\r\n• Suitable for regular use\r\n\r\nNet Volume: 80ml', 68.00, NULL, 'SUNSI364', 15, 53, '[\"1781783749_7000_0.png\"]', NULL, 1, '2026-06-18 11:55:49', '2026-06-28 08:14:28', 0, '{\"Brand\":\"Sunsilk\",\"Size\":\"80ml\",\"Expiry Date\":\"2028-06-18\"}'),
(99, 'WhiteTone Soft & Smooth Face Cream 15g হোয়াইট টোন ক্রীম', 'whitetone-soft-smooth-face-cream-15g', 'WhiteTone Soft & Smooth Face Cream is a hydrating face cream with sun protection that helps keep skin soft, smooth and moisturized. Its lightweight formula nourishes the skin and supports a healthy, radiant appearance for everyday skincare.\r\n\r\nKey Features:\r\n• Hydrating and moisturizing formula\r\n• Helps keep skin soft and smooth\r\n• Includes sun protection\r\n• Lightweight and easy to apply\r\n• Suitable for daily use\r\n• Non-greasy texture\r\n\r\nNet Weight: 15g', 48.00, NULL, 'WHITE369', 15, 53, '[\"1781783899_6705_0.png\"]', NULL, 1, '2026-06-18 11:58:19', '2026-06-28 08:12:58', 0, '{\"Brand\":\"WhiteTone\",\"Size\":\"15g\",\"Expiry Date\":\"2028-06-18\"}'),
(100, 'Roopa Turmeric With Glycerine Skin Cream 20g রুপা', 'roopa-turmeric-with-glycerine-skin-cream-20g', 'Roopa Turmeric With Glycerine Skin Cream is a nourishing skincare cream enriched with turmeric and glycerine. It helps moisturize the skin, maintain softness, and support a healthy-looking complexion. The lightweight formula is suitable for everyday skincare and helps protect skin from dryness.\r\n\r\nKey Features:\r\n• Enriched with turmeric and glycerine\r\n• Helps keep skin soft and moisturized\r\n• Supports healthy-looking skin\r\n• Suitable for daily use\r\n• Smooth and easy-to-apply formula\r\n\r\nNet Weight: 20g', 13.00, 10.00, 'ROOPA499', 15, 53, '[\"1781784119_1020_0.png\"]', NULL, 1, '2026-06-18 12:01:59', '2026-06-28 08:11:38', 0, '{\"Brand\":\"Roopa\",\"Size\":\"20g\",\"Expiry Date\":\"2028-06-18\"}'),
(101, 'Glow & Lovely Re-New Bright Multi Vitamin Serum In Cream 25g ফেয়ার লাভলি', 'glow-lovely-re-new-bright-multi-vitamin-serum-in-cream-25g', 'Glow & Lovely Re-New Bright Multi Vitamin Serum In Cream is enriched with Vitamins B, C and E to help improve skin radiance and support a brighter, even-looking complexion. Its lightweight serum-cream formula moisturizes the skin while helping renew skin cells for a healthy glow.\r\n\r\nKey Features:\r\n• Multi Vitamin Serum In Cream\r\n• Enriched with Vitamins B, C & E\r\n• Helps improve skin brightness\r\n• Moisturizes and nourishes the skin\r\n• Lightweight and non-sticky formula\r\n• Suitable for daily use\r\n\r\nNet Weight: 25g', 72.00, 70.00, 'GLOWL552', 15, 53, '[\"1781784271_4514_0.png\"]', NULL, 1, '2026-06-18 12:04:31', '2026-06-28 08:11:09', 0, '{\"Brand\":\"Glow & Lovely\",\"Size\":\"25g\",\"Expiry Date\":\"2029-06-18\"}'),
(102, 'Betnovate-N Skin Cream 25g বেটনোভেট N', 'betnovate-n-skin-cream-25g', 'Betnovate-N Skin Cream contains Betamethasone Valerate and Neomycin. It is a prescription topical medication used for the treatment of certain inflammatory skin conditions associated with bacterial infections. The cream helps reduce redness, itching, swelling and discomfort while providing antibacterial action.\r\n\r\nKey Features:\r\n• Contains Betamethasone Valerate and Neomycin\r\n• Helps relieve redness, itching and inflammation\r\n• Antibacterial and anti-inflammatory action\r\n• Easy topical application\r\n• For external use only\r\n\r\nNet Weight: 25g\r\n\r\nNote: Use only under medical supervision and as prescribed by a healthcare professional.', 67.00, 65.00, 'BETNO392', 15, 53, '[\"1781784504_8488_0.png\"]', NULL, 1, '2026-06-18 12:08:24', '2026-06-28 08:09:02', 0, '{\"Brand\":\"Betnovate-N\",\"Size\":\"25g\",\"Expiry Date\":\"2026-12-18\"}'),
(103, 'Betnovate-C Skin Cream 30g বেটনভেট C', 'betnovate-c-skin-cream-30g', 'Betnovate-C Skin Cream is a topical medicated cream formulated with Betamethasone and Clioquinol. It is commonly used for the management of certain inflammatory skin conditions associated with bacterial or fungal infections. The cream helps reduce redness, itching, irritation, and discomfort while supporting skin recovery. Use only as directed by a healthcare professional.', 73.00, 70.00, 'BETNO104', 15, 53, '[\"1781784686_6118_0.png\"]', NULL, 1, '2026-06-18 12:11:26', '2026-06-28 06:15:33', 0, '{\"Brand\":\"GSK (GlaxoSmithKline)\",\"Size\":\"30g\",\"Expiry Date\":\"2028-06-18\"}'),
(104, 'A.D.S Precision Liquid Mascara 24H Waterproof মাসকারা', 'ads-precision-liquid-mascara-24h-waterproof', 'A.D.S Precision Liquid Mascara 24H Waterproof is designed to enhance eyelashes with a bold, defined look. Its precision formula helps provide long-lasting wear, giving lashes a fuller and more dramatic appearance. The waterproof formulation helps resist smudging and fading throughout the day, making it suitable for daily use and special occasions.', 72.00, 70.00, 'ADSPR870', 15, 53, '[\"1781784856_3281_0.png\"]', NULL, 1, '2026-06-18 12:14:16', '2026-06-28 03:46:05', 0, '{\"Brand\":\"A.D.S\",\"Size\":\"8ml\",\"Expiry Date\":\"2029-06-18\"}'),
(105, 'Garnier Men AcnoFight Pimple Clearing Brightening Moisturiser', 'garnier-men-acnofight-pimple-clearing-brightening-moisturiser', 'Garnier Men AcnoFight Pimple Clearing Brightening Moisturiser is specially formulated for men\'s skin to help combat pimples, reduce excess oil, and provide lasting hydration. Its lightweight, non-greasy formula helps keep pores clear while improving skin appearance and brightness. Suitable for daily use, it supports clearer, healthier-looking skin without clogging pores.', 109.00, NULL, 'GARNI399', 15, 53, '[\"1781785009_1659_0.png\"]', NULL, 1, '2026-06-18 12:16:49', '2026-06-18 12:16:49', 15, '{\"Brand\":\"Garnier Men\",\"Size\":\"20g\",\"Expiry Date\":\"2029-06-18\"}'),
(106, 'Jac Olivol Herbal Body Oil with Italian Olive Oil 100ml জ্যাক বডি অয়েল', 'jac-olivol-herbal-body-oil-with-italian-olive-oil-100ml', 'Jac Olivol Herbal Body Oil is a nourishing body oil enriched with Italian Olive Oil and herbal ingredients. It is formulated to help moisturize, soften, and smooth the skin while supporting a healthy, radiant appearance. The lightweight oil absorbs easily and is suitable for regular body massage and daily skincare routines.', 72.00, 70.00, 'JACOL111', 15, 53, '[\"1781785218_6349_0.png\"]', NULL, 1, '2026-06-18 12:20:18', '2026-06-28 03:44:36', 0, '{\"Brand\":\"Jac Olivol\",\"Size\":\"100ml\",\"Expiry Date\":\"2029-06-18\"}'),
(107, 'NIVEA Soft Light Moisturizing Cream - 25ml নিভিয়া সফট ক্রিম', 'nivea-soft-light-moisturizing-cream-25ml', 'NIVEA Soft Light Moisturizing Cream is a fast-absorbing, non-greasy formula enriched with Vitamin E and Jojoba Oil to provide long-lasting hydration.\r\n\r\nSuitable for the face, hands, and body, it leaves the skin soft, smooth, and refreshed throughout the day.\r\n\r\nKey Features:\r\n• Lightweight and non-greasy formula\r\n• Provides long-lasting moisture\r\n• Enriched with Vitamin E and Jojoba Oil\r\n• Suitable for face, hands, and body\r\n• Dermatologically tested\r\n• Ideal for daily use and all skin types', 55.00, NULL, 'NIVEA855', 15, 53, '[\"1781797693_4541_0.png\"]', NULL, 1, '2026-06-18 15:48:13', '2026-06-28 03:43:34', 0, '{\"Brand\":\"NIVEA\",\"Size\":\"25ml\",\"Expiry Date\":\"2029-06-18\"}'),
(108, 'Himalaya Baby Cream - Extra Soft & Gentle (30ml) হিমালয়া বেবী ক্রিম', 'himalaya-baby-cream-extra-soft-gentle-30ml', 'Himalaya Baby Cream Extra Soft & Gentle is a daily-use moisturizing cream specially formulated to nourish, soothe, and protect your baby\'s delicate skin.\r\n\r\nEnriched with Olive Oil and Country Mallow, this lightweight cream helps maintain skin softness while providing long-lasting hydration.\r\n\r\nKey Features:\r\n• Gentle daily-use baby cream\r\n• Moisturizes and soothes delicate skin\r\n• Enriched with Olive Oil and Country Mallow\r\n• pH 5.5 balanced formula\r\n• Free from synthetic colors, parabens, phthalates, silicones, and lanolin\r\n• Suitable for newborns and babies\r\n• Dermatologically tested', 65.00, NULL, 'HIMAL557', 15, 53, '[\"1781797973_9637_0.png\"]', NULL, 1, '2026-06-18 15:52:53', '2026-06-28 03:42:10', 0, '{\"Brand\":\"Himalaya\",\"Size\":\"30ml\",\"Expiry Date\":\"2029-06-18\"}'),
(109, 'Clinic Plus Health Shampoo ক্লিনিক প্লাস শ্যাম্পু', 'clinic-plus-health-shampoo-with-rice-water-protein-vitamin-e-strong-thick-80ml', 'Clinic Plus Health Shampoo with Rice Water, Protein, and Vitamin E is specially formulated to help strengthen hair and make it look thick, healthy, and manageable.\r\n\r\nThe nourishing formula gently cleanses the scalp while helping reduce hair breakage and providing essential nutrients for stronger-looking hair.\r\n\r\nKey Features:\r\n• Enriched with Rice Water, Protein, and Vitamin E\r\n• Helps strengthen hair from root to tip\r\n• Makes hair look thick and healthy\r\n• Gently cleanses the scalp and hair\r\n• Suitable for regular use\r\n• Leaves hair soft, smooth, and manageable', 57.00, NULL, 'CLINI886', 15, 54, '[\"1781798203_4472_0.png\"]', NULL, 1, '2026-06-18 15:56:43', '2026-06-28 03:38:58', 0, '{\"Brand\":\"Clinic Plus\",\"Size\":\"80ml\",\"Expiry Date\":\"2028-06-18\"}'),
(110, 'Nycil Germ Expert Cool Herbal Prickly Heat Powder - 50g নাইসিল পাউডার', 'nycil-germ-expert-cool-herbal-prickly-heat-powder-50g', 'Nycil Germ Expert Cool Herbal Prickly Heat Powder is specially formulated to provide cooling relief from prickly heat, sweat, and skin irritation.\r\n\r\nPowered by its Germ Expert formula and enriched with herbal ingredients, it helps absorb excess sweat, calm rashes, soothe itching, and keep the skin fresh and comfortable throughout the day.\r\n\r\nKey Features:\r\n• Helps protect against prickly heat\r\n• Absorbs excess sweat effectively\r\n• Calms rashes and soothes itching\r\n• Provides an instant cooling sensation\r\n• Enriched with herbal ingredients\r\n• Clinically proven formula\r\n• Suitable for daily use', 50.00, NULL, 'NYCIL579', 15, 53, '[\"1781798406_1082_0.png\"]', NULL, 1, '2026-06-18 16:00:06', '2026-06-28 03:37:26', 0, '{\"Brand\":\"Nycil\",\"Size\":\"50g.\",\"Expiry Date\":\"2029-06-18\"}'),
(111, 'Product Name: Pond\'s Dreamflower Pink Lily Perfume Body Powder - 19g পন্ডস পাউডার', 'product-name-ponds-dreamflower-pink-lily-perfume-body-powder-19g', 'Product Description:\r\nPond\'s Dreamflower Pink Lily Perfume Body Powder is a lightweight and refreshing body powder infused with a delicate pink lily fragrance. It helps absorb excess sweat, keeps skin feeling fresh and dry, and leaves a long-lasting floral scent throughout the day. The soft formula spreads easily on the skin, making it ideal for daily use after bathing or whenever you need instant freshness.\r\n\r\nKey Features:\r\n• Long-lasting pink lily fragrance\r\n• Absorbs sweat and controls body odor\r\n• Keeps skin fresh, soft, and dry\r\n• Lightweight and smooth texture\r\n• Suitable for everyday use\r\n• Convenient travel-friendly pack', 10.00, NULL, 'PRODU519', 20, 53, '[\"1781798634_9643_0.png\"]', NULL, 1, '2026-06-18 16:03:54', '2026-06-28 03:36:16', 0, '{\"Brand\":\"Brand: Pond\'s\",\"Size\":\"19g\",\"Expiry Date\":\"2028-06-18\"}'),
(112, 'Product Name: Sunsilk Lusciously Thick & Long Shampoo (80 ml)  সান সিলিক শ্যাম্পু', 'product-name-sunsilk-lusciously-thick-long-shampoo-80-ml-category-beauty-personal-care-hair-care-shampoo-sku-sun-tl-80ml-product-description-sunsilk-lusciously-thick-long-shampoo-is-specially-formulated-to-help-achieve-thicker-looking-longer-and-healthier', 'Sunsilk Lusciously Thick & Long Shampoo is specially formulated to help achieve thicker-looking, longer, and healthier hair. Enriched with Activ-Mix containing Keratin, Yoghurt Protein, and Macadamia Oil, it nourishes hair from root to tip while reducing breakage and improving manageability. Its gentle cleansing formula cleanses the scalp effectively while leaving hair soft, smooth, and visibly fuller with regular use.\r\n\r\nKey Benefits:\r\n• Helps strengthen hair and reduce breakage\r\n• Supports thicker and longer-looking hair\r\n• Nourishes and moisturizes hair from root to tip\r\n• Improves hair softness and manageability\r\n• Suitable for regular use\r\n\r\nHow to Use:\r\nApply an adequate amount to wet hair and gently massage the scalp to create a rich lather. Rinse thoroughly with water. Repeat if necessary. For best results, use regularly.', 60.00, NULL, 'PRODU876', 15, 54, '[\"1781798990_7985_0.png\"]', NULL, 1, '2026-06-18 16:09:50', '2026-06-28 03:35:39', 0, '{\"Brand\":\"Sunsilk\",\"Size\":\"80ml\",\"Expiry Date\":\"2028-06-18\"}'),
(113, 'Johnson\'s Baby Shampoo with Coconut-Based Cleansers – 50 ml বেবী শ্যাম্পু', 'johnsons-baby-shampoo-with-coconut-based-cleansers-50-ml', 'Johnson\'s Baby Shampoo is specially formulated to gently cleanse your baby\'s delicate hair and scalp. The mild, tear-free formula is enriched with coconut-based cleansers that effectively remove dirt while keeping hair soft, smooth, and healthy.\r\n\r\nThis pediatrician-tested shampoo is designed to be gentle on the eyes and suitable for daily use. It is free from added parabens, sulfates, dyes, and phthalates, making it a safe choice for your baby\'s sensitive skin and hair.\r\n\r\nKey Features:\r\n\r\n• No More Tears® formula – gentle on eyes\r\n• Enriched with coconut-based cleansers\r\n• Pediatrician tested and hypoallergenic\r\n• Free from added parabens, sulfates, dyes, and phthalates\r\n• Suitable for daily use\r\n• Leaves hair soft, clean, and healthy\r\n\r\nHow to Use:\r\n\r\nApply to wet hair, gently lather, and rinse thoroughly with water. For best results, use during your baby\'s regular bath routine.\r\n\r\nNet Quantity: 50 ml', 70.00, NULL, 'JOHNS426', 15, 53, '[\"1781800289_1104_0.png\"]', NULL, 1, '2026-06-18 16:31:29', '2026-06-28 03:32:56', 0, '{\"Brand\":\"Johnson\'s\",\"Size\":\"50ml\",\"Expiry Date\":\"2029-06-18\"}'),
(114, 'Product Name: Bajaj Almond Drops 6X Vitamin E Nourishment Non-Sticky Hair Oil - 85 ml বাজাজ তেল Category: Hair Ca', 'product-name-bajaj-almond-drops-6x-vitamin-e-nourishment-non-sticky-hair-oil-85-ml-category-hair-care-hair-oil-sku-bad-85ml-brand-bajaj-size-variant-85-ml-expiry-date-february-2026-weight-kg-0085-regular-price-85-discount-price-79-stock-quantity-50-stock', 'Bajaj Almond Drops 6X Vitamin E Nourishment Hair Oil is a lightweight, non-sticky hair oil enriched with the goodness of almond oil and Vitamin E. It helps nourish hair from root to tip, making it stronger, softer, and shinier without leaving a greasy feel.\r\n\r\nThe quick-absorbing formula is suitable for regular use and helps reduce dryness, improve manageability, and support healthy-looking hair. Its pleasant fragrance and non-sticky texture make it ideal for daily application.\r\n\r\nKey Features:\r\n\r\n• Enriched with almond oil and 6X Vitamin E nourishment\r\n• Non-sticky and lightweight formula\r\n• Helps nourish and strengthen hair\r\n• Adds softness and natural shine\r\n• Easy to apply and suitable for daily use\r\n• Pleasant fragrance with quick absorption\r\n• Suitable for all hair types\r\n\r\nHow to Use:\r\n\r\nApply a small amount of oil to your scalp and hair lengths. Massage gently using your fingertips and leave it on for a few hours or overnight before washing.\r\n\r\nNet Volume: 85 ml\r\nBrand: Bajaj\r\nCountry of Origin: India', 85.00, NULL, 'PRODU382', 15, 53, '[\"1781800568_1557_0.png\"]', NULL, 1, '2026-06-18 16:36:08', '2026-06-28 03:30:58', 0, '{\"Brand\":\"Bajaj\",\"Size\":\"110ml\",\"Expiry Date\":\"2029-06-18\"}'),
(115, 'Product Name: Pond\'s Bright Beauty Light Crème Body Lotion পন্ডস বডিলোশন', 'product-name-ponds-bright-beauty-light-crme-body-lotion-with-niacinamide-90-ml-category-skin-care-body-lotion-sku-pbbl-90ml-brand-ponds-size-variant-90-ml-expiry-date-check-packaging-before-listing-weight-kg-009-regular-price-95-discount-price-89-stock-qu', 'Pond\'s Bright Beauty Light Crème Body Lotion is a lightweight, fast-absorbing body lotion enriched with Active Niacinamide to help nourish and brighten your skin. Its non-greasy formula provides up to 72 hours of moisturization, leaving your skin feeling soft, smooth, and refreshed.\r\n\r\nThe light crème texture spreads easily and absorbs quickly, making it ideal for daily use in all seasons. Regular use helps improve skin texture and promotes a brighter, healthier-looking appearance.\r\n\r\nKey Features:\r\n\r\n• Enriched with Active Niacinamide\r\n• Provides up to 72 hours of moisturization\r\n• Helps achieve visibly brighter and smoother skin\r\n• Lightweight, non-sticky formula\r\n• Absorbs quickly without leaving a greasy residue\r\n• Suitable for everyday use\r\n• Ideal for all skin types\r\n\r\nHow to Use:\r\n\r\nApply an adequate amount of lotion to clean, dry skin and massage gently until fully absorbed. For best results, use daily, especially after bathing.\r\n\r\nNet Volume: 90 ml\r\nBrand: Pond\'s\r\nCountry of Origin: India', 120.00, NULL, 'PRODU357', 15, 53, '[\"1781800779_5555_0.png\"]', NULL, 1, '2026-06-18 16:39:39', '2026-06-28 03:29:46', 0, '{\"Brand\":\"Pond\'s\",\"Size\":\"90ml\",\"Expiry Date\":\"2028-06-18\"}');
INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `discount_price`, `sku`, `stock_quantity`, `category_id`, `images`, `weight`, `is_active`, `created_at`, `updated_at`, `stock`, `attributes`) VALUES
(116, 'Product Name: Johnson\'s Baby Powder  বেবী পাউডার', 'product-name-johnsons-baby-powder-natural-plant-based-50-g-category-baby-care-baby-powder-sku-jbp-nat-50g-brand-johnsons-size-variant-50-g-expiry-date-july-2027-weight-kg-005-regular-price-65-discount-price-59-stock-quantity-50-stock-50-status-active', 'Johnson\'s Baby Powder Natural Plant-Based is specially formulated with natural corn starch to help absorb excess moisture and keep your baby\'s skin soft, fresh, and comfortable throughout the day.\r\n\r\nIts gentle, plant-based formula is designed for delicate baby skin and is free from added parabens, dyes, sulphates, and phthalates. The lightweight powder helps reduce friction and provides a soothing feel after every diaper change or bath.\r\n\r\nKey Features:\r\n\r\n• Made with natural plant-based corn starch\r\n• Helps absorb excess moisture effectively\r\n• Keeps baby\'s skin soft, dry, and comfortable\r\n• Gentle and mild formula for delicate skin\r\n• No added parabens, dyes, sulphates, or phthalates\r\n• Suitable for everyday use\r\n• Dermatologically tested for baby skin\r\n\r\nHow to Use:\r\n\r\nSprinkle a small amount of powder onto your palms and apply gently to your baby\'s skin, especially around folds and areas prone to moisture. Avoid direct application near the nose and mouth.\r\n\r\nSafety Information:\r\n\r\nFor external use only. Keep powder away from your child\'s face to avoid accidental inhalation. Avoid contact with eyes. Discontinue use if irritation occurs and consult a physician.\r\n\r\nNet Weight: 50 g\r\nBrand: Johnson\'s\r\nCountry of Origin: Thailand\r\nMarketed in: India', 65.00, NULL, 'PRODU344', 15, 53, '[\"1781801006_8499_0.png\"]', NULL, 1, '2026-06-18 16:43:26', '2026-06-28 03:28:18', 0, '{\"Brand\":\"Johnson\'s\",\"Size\":\"50g.\",\"Expiry Date\":\"2029-06-18\"}'),
(117, 'Product Name: Johnson\'s Baby Soap  বেবী সাবান', 'product-name-johnsons-baby-soap-with-glycerin-75-g-category-baby-care-baby-soap-sku-jbs-gly-75g-brand-johnsons-size-variant-75-g-expiry-date-check-packaging-before-listing-weight-kg-0075-regular-price-45-discount-price-39-stock-quantity-50-stock-50-status', 'Johnson\'s Baby Soap with Glycerin is specially designed to gently cleanse your baby\'s delicate skin while helping retain its natural moisture. Enriched with glycerin, this mild soap creates a soft lather that effectively cleanses without causing dryness.\r\n\r\nThe dermatologist-tested formula is clinically proven for mildness and is suitable for daily use on delicate baby skin. It is free from added parabens, dyes, and phthalates, making it a gentle choice for your baby\'s everyday bathing routine.\r\n\r\nKey Features:\r\n\r\n• Enriched with moisturizing glycerin\r\n• Gently cleanses delicate baby skin\r\n• Helps retain natural skin moisture\r\n• Clinically proven mildness\r\n• Dermatologist tested\r\n• Free from added parabens, dyes, and phthalates\r\n• Suitable for daily use\r\n\r\nHow to Use:\r\n\r\nWet your baby\'s skin and the soap bar with water. Lather gently in your hands or on a soft washcloth and apply to the skin. Rinse thoroughly with clean water.\r\n\r\nSafety Information:\r\n\r\nFor external use only. Avoid contact with eyes. Discontinue use if irritation occurs and consult a physician if needed.\r\n\r\nNet Weight: 75 g\r\nBrand: Johnson\'s\r\nCountry of Origin: India', 20.00, NULL, 'PRODU891', 20, 53, '[\"1781801207_4795_0.png\"]', NULL, 1, '2026-06-18 16:46:47', '2026-06-28 03:26:52', 0, '{\"Brand\":\"Johnson\'s\",\"Size\":\"25g\",\"Expiry Date\":\"2029-06-18\"}'),
(118, 'Kisan Malam কিষাণ মলম', 'kisan-malam', 'Kisan Malam is a traditional agricultural brand featuring a farmer logo with a farming tool. The product is identified by Registration No. 205791 and represents quality and trust in the agricultural sector.\r\n\r\nBrand: Kisan Malam\r\nRegistration No.: 205791\r\nOrigin: India\r\nPackaging: Round container\r\nPrimary Color: Yellow and Red', 30.00, 20.00, 'KISAN563', 20, 53, '[\"1781841489_4857_0.png\"]', NULL, 1, '2026-06-19 03:58:09', '2026-06-28 03:23:44', 0, '{\"Brand\":\"Kisan Malam\",\"Size\":\"15g\",\"Expiry Date\":\"2029-06-19\"}'),
(119, 'Lakmé Perfecting Liquid Foundation ফাউন্ডেসন', 'lakm-perfecting-liquid-foundation', 'Lakmé Perfecting Liquid Foundation is a lightweight liquid foundation designed to provide smooth, even coverage for a natural-looking finish.\r\n\r\nKey Features:\r\n• Lightweight and easy-to-blend formula\r\n• Provides even skin tone coverage\r\n• Suitable for everyday use\r\n• Smooth and natural finish\r\n• Convenient glass bottle packaging\r\n\r\nBrand: Lakmé\r\nProduct Type: Liquid Foundation\r\nFinish: Natural\r\nSkin Type: Suitable for most skin types\r\nPackaging: Glass bottle with cap\r\nColor: Beige', 199.00, 89.00, 'LAKMP206', 15, 53, '[\"1781841838_1265_0.png\"]', NULL, 1, '2026-06-19 04:03:58', '2026-06-28 03:22:40', 0, '{\"Brand\":\"Lakm\\u00e9\",\"Size\":\"25ml\",\"Expiry Date\":\"2028-06-19\"}'),
(120, 'Godrej Expert Original Natural Black Powder Hair Colour চুল কালো করা কালার', 'godrej-expert-original-natural-black-powder-hair-colour', 'Godrej Expert Original Natural Black Powder Hair Colour is an ammonia-free hair colouring solution designed to provide 100% grey coverage with long-lasting results.\r\n\r\nKey Features:\r\n• Natural Black shade\r\n• Powder hair colour formula\r\n• No ammonia\r\n• Provides 100% grey coverage\r\n• Long-lasting colour\r\n• Easy-to-use single-use sachet packaging\r\n\r\nBrand: Godrej Expert\r\nVariant: Original\r\nShade: Natural Black\r\nProduct Type: Powder Hair Colour\r\nFormula: Ammonia-Free\r\nPackaging: Sachet\r\nSuitable For: Men and Women', 20.00, NULL, 'GODRE874', 20, 53, '[\"1781842418_8138_0.png\"]', NULL, 1, '2026-06-19 04:13:38', '2026-06-28 03:19:23', 0, '{\"Brand\":\"Godrej\",\"Size\":\"3g\",\"Expiry Date\":\"2028-06-19\"}'),
(121, 'Himalaya Gentle Baby Soap 75g হিমালয়া বেবী সাবান', 'himalaya-gentle-baby-soap-75g', 'Himalaya Gentle Baby Soap is specially formulated to gently cleanse and protect your baby\'s delicate skin. Enriched with the goodness of almond oil and olive oil, it helps keep the skin soft, moisturized, and healthy.\r\n\r\nKey Features:\r\n• Gently cleanses baby\'s delicate skin\r\n• Ideal for daily use\r\n• Clinically tested\r\n• Free from parabens, phthalates, and synthetic colors\r\n• Enriched with almond oil and olive oil\r\n• Dermatologically researched\r\n• Not tested on animals\r\n\r\nBrand: Himalaya\r\nProduct Type: Baby Soap\r\nNet Weight: 75 g\r\nTFM: 76% Grade I Soap\r\nSuitable For: Infants and babies\r\nPackaging: Box', 15.00, NULL, 'HIMAL159', 15, 53, '[\"1781842668_1644_0.png\"]', NULL, 1, '2026-06-19 04:17:48', '2026-06-28 03:17:57', 0, '{\"Brand\":\"Himalaya\",\"Size\":\"25g\",\"Expiry Date\":\"2029-06-19\"}'),
(122, 'Parachute Advansed Jasmine Coconut Hair Oil with Vitamin E 90ml জাসমিন তেল', 'parachute-advansed-jasmine-coconut-hair-oil-with-vitamin-e-90ml', 'Parachute Advansed Jasmine Coconut Hair Oil with Vitamin E is a non-sticky hair oil enriched with the goodness of coconut oil, jasmine fragrance, and Vitamin E to help nourish hair and keep it soft, smooth, and manageable.\r\n\r\nKey Features:\r\n• Non-sticky coconut hair oil\r\n• Enriched with Vitamin E\r\n• Pleasant jasmine fragrance\r\n• Helps nourish and condition hair\r\n• Lightweight formula for daily use\r\n• Suitable for all hair types\r\n\r\nBrand: Parachute Advansed\r\nVariant: Jasmine with Vitamin E\r\nProduct Type: Hair Oil\r\nNet Quantity: 90 ml\r\nPackaging: Plastic bottle\r\nSuitable For: Men and Women', 45.00, NULL, 'PARAC896', 20, 54, '[\"1781842835_7812_0.png\"]', NULL, 1, '2026-06-19 04:20:35', '2026-06-28 03:16:59', 0, '{\"Brand\":\"Parachute Advansed\",\"Size\":\"90ml\",\"Expiry Date\":\"2029-06-19\"}'),
(123, 'Himalaya Baby Powder Khus-Khus 30g হিমালয়া বেবী পাউডার', 'himalaya-baby-powder-khus-khus-30g', 'Himalaya Baby Powder with Khus-Khus helps keep your baby\'s skin fresh, cool, soft, and dry. Its gentle formulation is enriched with natural ingredients and is clinically tested for daily use.\r\n\r\nKey Features:\r\n• Refreshes and cools the skin\r\n• Helps keep baby\'s skin soft and dry\r\n• Enriched with Khus-Khus\r\n• Free from parabens, phthalates, and synthetic colors\r\n• Clinically tested\r\n• Gentle and safe for everyday use\r\n\r\nBrand: Himalaya\r\nProduct Type: Baby Powder\r\nVariant: Khus-Khus\r\nNet Weight: 30 g\r\nPackaging: Plastic bottle\r\nSuitable For: Infants and babies', 30.00, NULL, 'HIMAL108', 15, 53, '[\"1781842965_3681_0.png\"]', NULL, 1, '2026-06-19 04:22:45', '2026-07-07 14:29:31', 0, '{\"Brand\":\"Himalaya\",\"Size\":\"30g\",\"Expiry Date\":\"2029-06-19\"}'),
(124, 'Dabur Amla Hair Oil 110ml (90ml + 20ml Free) ডাবর আমলা তেল', 'dabur-amla-hair-oil-110ml-90ml-20ml-free', 'Dabur Amla Hair Oil is enriched with the goodness of amla extracts to help nourish hair from root to tip. Regular use helps promote stronger, longer, and thicker-looking hair.\r\n\r\nKey Features:\r\n• Enriched with amla extracts\r\n• Helps make hair strong, long, and thick\r\n• Nourishes the scalp and hair roots\r\n• Suitable for regular use\r\n• Lightweight and easy-to-apply formula\r\n• Promotional pack with 20 ml extra free\r\n\r\nBrand: Dabur\r\nProduct Type: Hair Oil\r\nVariant: Amla\r\nNet Quantity: 110 ml (90 ml + 20 ml Free)\r\nPackaging: Plastic bottle\r\nSuitable For: Men and Women', 50.00, NULL, 'DABUR310', 15, 54, '[\"1781843204_9978_0.png\"]', NULL, 1, '2026-06-19 04:26:44', '2026-06-28 03:14:54', 0, '{\"Brand\":\"Dabur\",\"Size\":\"110 ml (90 ml + 20 ml Free)\",\"Expiry Date\":\"2029-06-19\"}'),
(125, 'Garnier Bright Complete Vitamin C Face Wash 50g গার্নিযার ফেস ওয়াশ', 'garnier-bright-complete-vitamin-c-face-wash-50g', 'Garnier Bright Complete Vitamin C Face Wash is enriched with naturally sourced Vitamin C and superfruit lemon to help deeply cleanse the skin and reduce dullness.\r\n\r\nKey Features:\r\n• Enriched with Vitamin C and lemon extract\r\n• Helps clear skin dullness\r\n• Deep cleansing foam formula\r\n• Dermatologically tested\r\n• Suitable for all skin types\r\n• Refreshes and brightens the skin\r\n\r\nBrand: Garnier\r\nProduct Type: Face Wash\r\nVariant: Bright Complete Vitamin C+\r\nNet Quantity: 50 g\r\nSkin Type: Suitable for all skin types\r\nPackaging: Tube', 99.00, NULL, 'GARNI674', 15, 53, '[\"1781843444_8931_0.png\"]', NULL, 1, '2026-06-19 04:30:44', '2026-06-28 02:33:13', 0, '{\"Brand\":\"Garnier\",\"Size\":\"50g.\",\"Expiry Date\":\"2029-06-19\"}'),
(126, 'Padmini Kesh Kala Hair Colour চুল কালার', 'padmini-kesh-kala-hair-colour', 'Padmini Kesh Kala Hair Colour is specially formulated to turn grey hair into natural black within two days. Suitable for both men and women, this hair colour is free from ammonia and peroxide for a gentler colouring experience.\r\n\r\nKey Features:\r\n• Turns grey hair into natural black within two days\r\n• Suitable for men and women\r\n• No ammonia\r\n• No peroxide\r\n• Easy-to-use formula\r\n• Helps provide long-lasting black hair colour\r\n\r\nBrand: Padmini\r\nProduct Type: Hair Colour\r\nVariant: Kesh Kala\r\nColour: Natural Black\r\nPackaging: Box\r\nSuitable For: Men and Women', 70.00, 50.00, 'PADMI897', 15, 53, '[\"1781843684_9208_0.png\"]', NULL, 1, '2026-06-19 04:34:44', '2026-06-28 02:32:05', 0, '{\"Brand\":\"Padmini\",\"Size\":\"100ml\",\"Expiry Date\":\"2028-06-19\"}'),
(127, 'Godrej Good Knight Flash Liquid Vaporiser Machine + Refill (45ml) মশা তাড়ানোর জন্য', 'godrej-good-knight-flash-liquid-vaporiser-machine-refill-45ml', 'Godrej Good Knight Flash Liquid Vaporiser provides effective mosquito protection for your home. This pack contains 1 vaporiser machine and 1 refill bottle (45ml). Easy to use and suitable for daily indoor mosquito control.', 105.00, NULL, 'GODRE327', 15, 53, '[\"1781848252_6652_0.png\"]', NULL, 1, '2026-06-19 05:50:52', '2026-06-28 02:31:11', 0, '{\"Brand\":\"Godrej\",\"Size\":\"45ml (1 Machine + 1 Refill)\",\"Expiry Date\":\"2028-06-19\"}'),
(128, 'A.D.S Rose Flavour Nail Polish Cleanser Pads (Acetone Free) নেল পলিশ তোলা', 'ads-rose-flavour-nail-polish-cleanser-pads-acetone-free', 'A.D.S Rose Flavour Nail Polish Cleanser Pads are acetone-free nail cleaning wipes designed to remove nail polish gently while helping to keep nails clean and smooth. Enriched with a special formula, these pads help soften cuticles and maintain nail care. Compact and easy to carry, making them suitable for home and travel use.', 50.00, 30.00, 'ADSRO291', 15, 53, '[\"1781848442_1217_0.png\"]', NULL, 1, '2026-06-19 05:54:02', '2026-06-20 16:25:09', 0, '{\"Brand\":\"A.D.S\",\"Size\":\"Approx. 32 Wipes\",\"Expiry Date\":\"2029-06-19\"}'),
(129, 'Glucon-D Tangy Orange Instant Energy Drink Powder 125g', 'glucon-d-tangy-orange-instant-energy-drink-powder-125g', 'Glucon-D Tangy Orange Instant Energy Drink Powder provides quick energy and helps support recovery and immunity. Enriched with Vitamin C, Zinc, and Magnesium, this refreshing orange-flavoured glucose drink is ideal for daily energy replenishment. Easy to prepare and suitable for all age groups.', 55.00, NULL, 'GLUCO783', 15, 53, '[\"1781848604_2093_0.png\"]', NULL, 1, '2026-06-19 05:56:44', '2026-06-19 05:56:44', 15, '{\"Brand\":\"Glucon-D\",\"Size\":\"125g (75g + 50g Free)\",\"Expiry Date\":\"2027-11-30\"}'),
(130, 'Kaveri Mehndi Cone 25g  হাত মেহেন্দি', 'kaveri-mehndi-cone-25g', 'Kaveri Mehndi Cone is a ready-to-use henna cone designed for creating beautiful and intricate mehndi designs. Made with quality henna ingredients, it delivers a dark and long-lasting stain. Easy to apply and suitable for festivals, weddings, and special occasions.', 10.00, NULL, 'KAVER198', 20, 53, '[\"1781848748_1115_0.png\"]', NULL, 1, '2026-06-19 05:59:08', '2026-06-28 02:30:05', 0, '{\"Brand\":\"Kaveri\",\"Size\":\"25g\",\"Expiry Date\":\"2027-06-19\"}'),
(131, 'Manmohan Jadu Malam Ring Worm Ointment জাদু মলম', 'manmohan-jadu-malam-ring-worm-ointment', 'Manmohan Jadu Malam is a traditional ring worm ointment formulated for external use. Manufactured by Maniram Kirodimal, this ointment is commonly used for skin care applications and comes in a compact container for convenient use and storage.', 30.00, NULL, 'MANMO676', 20, 53, '[\"1781848890_7780_0.png\"]', NULL, 1, '2026-06-19 06:01:30', '2026-06-20 16:23:34', 0, '{\"Brand\":\"Manmohan\",\"Size\":\"11g\",\"Expiry Date\":\"2028-06-19\"}'),
(132, 'First Love Body Talc Perfumed Powder (Je powder) পাউডার', 'first-love-body-talc-perfumed-powder-jevton', 'First Love Body Talc Perfumed Powder is a refreshing body talcum powder enriched with herbs and a pleasant Jevton fragrance. It helps keep skin fresh, dry, and comfortable throughout the day while providing long-lasting fragrance. Suitable for daily use and all skin types.', 44.00, 40.00, 'FIRST584', 15, 53, '[\"1781849074_9157_0.png\"]', NULL, 1, '2026-06-19 06:04:34', '2026-06-20 16:22:24', 0, '{\"Brand\":\"First Love\",\"Size\":\"50g\",\"Expiry Date\":\"2031-06-19\"}'),
(134, 'Godrej Expert Rich Crème Hair Colour - Natural Black 1.00 (Mini Pack) কালো রঙের কালার', 'godrej-expert-rich-crme-hair-colour-natural-black-100-mini-pack', 'Godrej Expert Rich Crème Hair Colour in Natural Black 1.00 provides long-lasting colour with 100% grey coverage.\r\n\r\nEnriched with 10x more aloe vera, this no-ammonia formula leaves hair soft, shiny, and smooth after every use.\r\n\r\nKey Features:\r\n• Shade: Natural Black 1.00\r\n• Long-lasting colour\r\n• 100% grey coverage\r\n• No ammonia formula\r\n• Enriched with aloe vera\r\n• Ultra-soft finish with deep shine\r\n• Easy-to-use crème formula\r\n• Mini pack suitable for single use\r\n\r\nPack Contents:\r\n• 1 Sachet of Crème Colourant (11g)\r\n• 1 Sachet of Developer (11ml)\r\n• 1 Instruction Leaflet\r\n\r\nNet Content: 11g + 11ml\r\nMRP: ₹15\r\nBrand: Godrej Expert', 15.00, NULL, 'GODRE261', 20, 53, '[\"1782037724_9365_0.png\"]', NULL, 1, '2026-06-21 10:28:44', '2026-06-28 02:29:05', 0, '{\"Brand\":\"Godrej Expert\",\"Size\":\"11g + 11ml\",\"Expiry Date\":\"2028-06-21\"}'),
(135, 'Paree Heavy Flow Champion Sanitary Pads - Regular 230mm (6 Pads) প্যাডস্', 'paree-heavy-flow-champion-sanitary-pads-regular-230mm-9-pads', 'Paree Heavy Flow Champion Sanitary Pads are specially designed for heavy flow days, offering fast absorption and long-lasting dryness.\r\n\r\nThe dry feel cover helps prevent wetness, while the advanced absorbent core locks in fluid quickly for enhanced comfort and confidence.\r\n\r\nKey Features:\r\n• Regular size - 230mm\r\n• Pack contains 9 sanitary pads\r\n• Suitable for heavy flow days\r\n• 3-second fast absorption technology\r\n• Dry feel cover helps prevent wetness\r\n• Soft and comfortable material\r\n• Gentle fragrance\r\n• Individually packed for hygiene and convenience\r\n• BIS certified (IS 5405:2019)\r\n• Recyclable packaging\r\n\r\nBrand: Paree\r\nVariant: Heavy Flow Champion\r\nPad Length: 230mm\r\nQuantity: 9 Pads', 27.00, 25.00, 'PAREE274', 20, 53, '[\"1782037946_7545_0.png\"]', NULL, 1, '2026-06-21 10:32:26', '2026-06-28 02:28:00', 0, '{\"Brand\":\"Paree\",\"Size\":\"Regular 230mm | Pack of 9 Pads\",\"Expiry Date\":\"2029-06-21\"}'),
(136, 'Stayfree Secure XL Cottony Soft Cover Sanitary Pads - 274mm (6 Units) প্যাডস', 'stayfree-secure-xl-cottony-soft-cover-sanitary-pads-274mm-6-units', 'Stayfree Secure XL Sanitary Pads with Cottony Soft Cover are designed to provide superior comfort and long-lasting protection during heavy flow days.\r\n\r\nThe clinically tested non-irritant cottony soft cover feels gentle on the skin, while the advanced absorbent core offers protection for up to 12 hours.\r\n\r\nKey Features:\r\n• XL size with 274mm length\r\n• Pack contains 6 sanitary pads\r\n• Cottony soft cover for extra comfort\r\n• Clinically tested non-irritant cover\r\n• Up to 12 hours leakage protection\r\n• Designed for all-round night protection\r\n• Soft wings help keep the pad securely in place\r\n• Suitable for medium to heavy flow days\r\n\r\nBrand: Stayfree\r\nVariant: Secure XL\r\nPad Length: 274mm\r\nQuantity: 6 Units\r\nCover Type: Cottony Soft Cover\r\n\r\nStorage Instructions:\r\nStore in a cool and dry place away from direct sunlight.', 45.00, NULL, 'STAYF809', 20, 53, '[\"1782038216_4916_0.png\"]', NULL, 1, '2026-06-21 10:36:56', '2026-06-28 02:27:32', 0, '{\"Brand\":\"Stayfree\",\"Size\":\"XL | 274mm | Pack of 6 Pads\",\"Expiry Date\":\"2029-06-21\"}'),
(137, 'Amrutanjan Comfy Snug Fit Regular Dry Sanitary Pads (6 Units) প্যাডস', 'amrutanjan-comfy-snug-fit-regular-dry-sanitary-pads-6-units', 'Amrutanjan Comfy Snug Fit Regular Dry Sanitary Pads are designed to provide reliable protection and all-day comfort.\r\n\r\nFeaturing rapid suction funnels and an anti-leak system, these pads absorb fluid quickly and help prevent side leakage. The regular dry cover keeps you feeling fresh and comfortable throughout the day.\r\n\r\nKey Features:\r\n• Regular Dry variant\r\n• Pack contains 6 sanitary pads\r\n• Snug Fit design for better comfort\r\n• Up to 80% better absorption\r\n• Anti-leak system with side barriers\r\n• Rapid suction funnels for quick absorption\r\n• Rash control technology\r\n• Zero stains protection\r\n• Bigger wings for a secure fit\r\n• 20% extra pulp for heavy flow support\r\n• Imported pulp from North America\r\n\r\nBrand: Amrutanjan Comfy\r\nVariant: Regular Dry\r\nQuantity: 6 Units\r\nMade in India', 28.00, NULL, 'AMRUT531', 20, 53, '[\"1782038588_3705_0.png\"]', NULL, 1, '2026-06-21 10:43:08', '2026-06-28 02:26:38', 0, '{\"Brand\":\"Amrutanjan Comfy\",\"Size\":\"Regular Dry | Pack of 6 Pads\",\"Expiry Date\":\"2029-06-21\"}'),
(138, 'Huggies Comfy Pants Diaper Medium (M) 2 Pants Pack (হাগীজ M সাইজ', 'huggies-comfy-pants-diaper-medium-m-2-pants-pack', 'Huggies Comfy Pants Baby Diaper, Medium Size (M), suitable for babies weighing 7-12 kg. Features Dry Xpert Channel technology with 4X faster absorption for long-lasting dryness and comfort. Designed with a 360° flexible waistband for a snug fit and leakage protection. Soft, breathable, and comfortable for daily use.\r\n\r\nKey Features:\r\n• 4X Faster Absorption\r\n• Dry Xpert Channel Technology\r\n• Up to 12 Hours Absorption\r\n• 360° Flexible Waistband\r\n• Leakage Protection\r\n• Soft & Comfortable Fit\r\n• Suitable for 7-12 kg Babies\r\n\r\nPack Size: 2 Pants\r\nBrand: Huggies', 22.00, NULL, 'HUGGI675', 30, 53, '[\"1782198823_6856_0.png\"]', NULL, 1, '2026-06-23 07:13:43', '2026-06-28 02:25:26', 0, '{\"Brand\":\"Huggies\",\"Size\":\"Medium (M) | 7-12 kg | 2 Pants\",\"Expiry Date\":\"2029-06-23\"}'),
(139, 'Huggies Comfy Pants Diaper small (S) 2 Pants Pack (হাগিস S সাইজ)', 'huggies-comfy-pants-diaper-small-s-2-pants-pack', 'Huggies Comfy Pants Baby Diaper, Small Size (S), suitable for babies weighing  Features Dry Xpert Channel technology with 4X faster absorption for long-lasting dryness and comfort. Designed with a 360° flexible waistband for a snug fit and leakage protection. Soft, breathable, and comfortable for daily use.\r\n\r\nKey Features:\r\n• 4X Faster Absorption\r\n• Dry Xpert Channel Technology\r\n• Up to 12 Hours Absorption\r\n• 360° Flexible Waistband\r\n• Leakage Protection\r\n• Soft & Comfortable Fit\r\n• Suitable for 4-8 kg Babies\r\n\r\nPack Size: 2 Pants\r\nBrand: Huggies', 18.00, NULL, 'HUGGI690', 30, 53, '[\"1782199419_2086_0.png\"]', NULL, 1, '2026-06-23 07:23:39', '2026-06-26 17:24:42', 0, '{\"Brand\":\"Huggies\",\"Size\":\"Small (S) (4-8 kg ) 2 Pants\",\"Expiry Date\":\"2029-06-23\"}'),
(140, 'Huggies Comfy Pants Diaper Big(L) 2 Pants Pack (হ্যাগিস L সাইজ)', 'huggies-comfy-pants-diaper-bigl-2-pants-pack', 'Huggies Comfy Pants Baby Diaper, Big Size (L), suitable for babies weighing 9-14kg. Features Dry Xpert Channel technology with 4X faster absorption for long-lasting dryness and comfort. Designed with a 360° flexible waistband for a snug fit and leakage protection. Soft, breathable, and comfortable for daily use.\r\n\r\nKey Features:\r\n• 4X Faster Absorption\r\n• Dry Xpert Channel Technology\r\n• Up to 12 Hours Absorption\r\n• 360° Flexible Waistband\r\n• Leakage Protection\r\n• Soft & Comfortable Fit\r\n• Suitable for 9-14 kg Babies\r\n\r\nPack Size: 2 Pants\r\nBrand: Huggies', 28.00, NULL, 'HUGGI111', 30, 53, '[\"1782199772_6126_0.png\"]', NULL, 1, '2026-06-23 07:29:32', '2026-06-26 17:22:17', 0, '{\"Brand\":\"Huggies\",\"Size\":\"Big (L) | 9-14 kg | 2 Pants\",\"Expiry Date\":\"2029-06-23\"}'),
(141, 'Gillette Guard Razor (1 Razor Pack) (জিলেট গার্ড রেজার)', 'gillette-guard-razor-1-razor-pack', 'Gillette Guard Razor is designed for a smooth, comfortable, and safe shaving experience. It features a single-blade system with a safety comb that helps prevent cuts and nicks while providing a close shave. The lightweight ergonomic handle ensures better grip and control during use.\r\n\r\nKey Features:\r\n• Up to 7 Shaves\r\n• Single Blade Technology\r\n• Safety Comb Protection\r\n• Comfortable Grip Handle\r\n• Easy Rinse Design\r\n• Smooth and Safe Shaving\r\n• Suitable for Daily Grooming\r\n\r\nPack Contents: 1 Razor\r\nBrand: Gillette', 28.00, NULL, 'GILLE875', 15, 53, '[\"1782200056_8880_0.png\"]', NULL, 1, '2026-06-23 07:34:16', '2026-06-26 17:20:57', 0, '{\"Brand\":\"Gillette\",\"Size\":\"1 Razor\",\"Expiry Date\":\"2028-06-23\"}'),
(142, 'Dove Serum Bar Deep Nourish Soap 50g(ডাভ সাবান)', 'dove-serum-bar-deep-nourish-soap-50g', 'Dove Serum Bar Deep Nourish is a gentle cleansing bar enriched with nutrient serum and 1/4 moisturizing cream to help nourish and care for skin. Its pH-balanced formula cleanses effectively without leaving skin feeling dry. Dermatologically tested and suitable for daily use.\r\n\r\nKey Features:\r\n• Nutrient Serum Formula\r\n• Deep Nourishing Care\r\n• pH Balanced Formula\r\n• Contains 1/4 Moisturizing Cream\r\n• Plant-Based Cleansers\r\n• Dermatologically Tested\r\n• Suitable for Daily Use\r\n\r\nNet Weight: 50g\r\nBrand: Dove', 23.00, NULL, 'DOVES486', 20, 53, '[\"1782200260_7008_0.png\"]', NULL, 1, '2026-06-23 07:37:40', '2026-06-26 17:19:23', 0, '{\"Brand\":\"Dove\",\"Size\":\"50g\",\"Expiry Date\":\"2029-06-23\"}'),
(143, 'Medisalic Ointment (Clobetasol Propionate and Salicylic Acid Ointment) (মেডিছিলিক)', 'medisalic-ointment-clobetasol-propionate-and-salicylic-acid-ointment', 'Medisalic Ointment is a topical skin care ointment containing Clobetasol Propionate and Salicylic Acid. It is formulated for external use and helps manage skin conditions associated with scaling, itching, redness, and inflammation. Easy to apply and suitable for use as directed by a healthcare professional.\r\n\r\nKey Features:\r\n• Clobetasol Propionate & Salicylic Acid Formula\r\n• Helps Reduce Scaling and Dryness\r\n• Supports Relief from Itching and Redness\r\n• Easy Application\r\n• For External Use Only\r\n\r\nManufacturer: Torque Pharmaceuticals Pvt. Ltd.\r\nMRP: ₹159', 159.00, 65.00, 'MEDIS471', 20, 53, '[\"1782200486_1393_0.png\"]', NULL, 1, '2026-06-23 07:41:26', '2026-06-26 17:17:18', 0, '{\"Brand\":\"Medisalic\",\"Size\":\"15g\",\"Expiry Date\":\"2028-06-23\"}'),
(144, 'Lontha Footcare Cream - Herbal Cracked Heel Repair Cream(পায়ের গোড়ালি ফাটা ভালো হয়)', 'lontha-footcare-cream-herbal-cracked-heel-repair-cream', 'Lontha Footcare Cream is a herbal formulation specially designed to heal, hydrate, and soften rough, dry, and cracked feet. Enriched with natural herbal extracts, it helps repair cracked heels, moisturize damaged skin, and provide antiseptic protection. Regular use leaves feet smoother, healthier, and more comfortable.\r\n\r\nKey Benefits:\r\n• Helps heal cracked heels and rough feet\r\n• Deeply hydrates and softens dry skin\r\n• Moisturizes damaged skin\r\n• Provides antiseptic protection\r\n• Herbal formula with natural ingredients\r\n• Suitable for daily foot care\r\n\r\nDirections:\r\nClean and dry feet before application. Apply the cream generously on affected areas and massage gently until absorbed.', 72.00, 70.00, 'LONTH977', 4, 53, '[\"1782200884_9061_0.png\"]', NULL, 1, '2026-06-23 07:48:04', '2026-07-06 16:13:48', 0, '{\"Brand\":\"Lontha\",\"Size\":\"25g\",\"Expiry Date\":\"2028-06-23\"}'),
(145, 'Sugar (চিনি) 500g', 'sugar', '**Caption:**\r\nPure White Sugar – Premium Quality\r\n\r\n**Description:**\r\nOur premium white sugar is finely refined, clean, and perfect for everyday use. It dissolves quickly, making it ideal for tea, coffee, desserts, baking, and cooking. Carefully processed to ensure consistent quality, freshness, and a naturally sweet taste. Suitable for both home and commercial use.\r\n\r\n**Keywords:**\r\nWhite Sugar, Granulated Sugar, Premium Sugar, Refined Sugar, Natural Sweetener, Baking, Cooking, Tea, Coffee, Dessert', 30.00, NULL, 'SUGAR689', 50, 52, '[\"1783842231_7009_0.png\"]', NULL, 1, '2026-07-12 07:43:51', '2026-08-09 08:50:10', 0, '{\"Brand\":\"Sugar\",\"Size\":\"500g\"}'),
(146, 'Muri (মুরি) 500g', 'muri', 'Muri, also known as puffed rice, is a light, crispy, and healthy snack made by heating rice until it expands. It is widely enjoyed across South Asia and can be eaten on its own or mixed with spices, vegetables, peanuts, and mustard oil to make a flavorful snack. Muri is low in fat, easy to digest, and perfect for a quick, delicious bite at any time of the day.', 30.00, NULL, 'MURI641', 20, 52, '[\"1783842717_8466_0.png\"]', NULL, 1, '2026-07-12 07:51:57', '2026-08-09 08:48:40', 0, '{\"Brand\":\"Muri\",\"Size\":\"500g\"}'),
(147, 'Kaka Jira (জিরা) 100gram,s', 'kaka-jira', 'Cumin (Jira) is a popular aromatic spice made from the dried seeds of the Cuminum cyminum plant. It has a warm, earthy flavor and a distinctive aroma that enhances the taste of a wide variety of dishes. Rich in antioxidants and essential nutrients, cumin is widely used in cooking and traditional medicine. It is commonly added to curries, soups, rice dishes, spice blends, and snacks for its rich flavor and digestive benefits.', 35.00, NULL, 'KAKAJ394', 10, 52, '[\"1784293825_2387.png\"]', NULL, 1, '2026-07-12 07:59:23', '2026-07-17 13:10:25', 0, '{\"Brand\":\"Kaka\",\"Size\":\"100g\"}'),
(148, 'Chopped areca nut (সুপারি) 100gram,s', 'chopped-areca-nut', 'Chopped Areca Nut (Supari) is made from carefully selected, high-quality areca nuts that are cleaned and cut into small, uniform pieces. It features a natural color, firm texture, and authentic taste. Commonly used for chewing, traditional preparations, and cultural or religious ceremonies, this product is hygienically processed to preserve its freshness and quality. Suitable for both household and commercial use.', 60.00, NULL, 'CHOPP260', 10, 52, '[\"1784293779_7849.jpg\"]', NULL, 1, '2026-07-12 08:17:06', '2026-07-17 13:09:39', 0, '{\"Brand\":\"Supari\",\"Size\":\"100g\"}'),
(149, 'Shalimar\'s Chef Garam Masala Standard Grade', 'shalimars-chef-garam-masala-standard-grade', 'Shalimar\'s Chef Garam Masala is a premium blend of traditional Indian spices that enhances the flavor and aroma of curries, vegetables, meat, rice, and other dishes. Made from carefully selected spices, it delivers rich taste and authentic Indian flavor. Store in a cool, dry place and keep the packet tightly sealed after opening.', 5.00, NULL, 'SHALI384', 100, 52, '[\"1784293653_7548.jpg\"]', NULL, 1, '2026-07-15 07:44:35', '2026-07-17 13:07:33', 0, '{\"Brand\":\"Shalimar\'s Chef\",\"Size\":\"4g\",\"Expiry Date\":\"2027-07-15\"}'),
(150, 'Sunrise Pure Coriander (Dhania) Powder', 'sunrise-pure-coriander-dhania-powder', 'Sunrise Pure Coriander (Dhania) Powder is made from carefully selected premium coriander seeds, finely ground to deliver fresh aroma and authentic flavor. It enhances the taste of curries, vegetables, dals, gravies, marinades, and a variety of Indian dishes. Hygienically packed to preserve freshness and quality.', 5.00, NULL, 'SUNRI261', 100, 52, '[\"1784293626_3874.jpg\"]', NULL, 1, '2026-07-15 07:47:29', '2026-07-17 13:07:06', 0, '{\"Brand\":\"Sunrise\",\"Size\":\"10g\",\"Expiry Date\":\"2027-07-15\"}'),
(151, 'Shalimar\'s Chef Cumin Powder', 'shalimars-chef-cumin-powder', 'Shalimar\'s Chef Cumin Powder is made from premium quality cumin seeds, finely ground to provide a rich aroma and authentic taste. It is ideal for curries, dals, vegetables, rice dishes, snacks, marinades, and traditional Indian recipes. Hygienically packed to maintain freshness and quality.', 5.00, NULL, 'SHALI352', 100, 52, '[\"1784293595_2227.jpg\"]', NULL, 1, '2026-07-15 07:50:02', '2026-07-17 13:06:35', 0, '{\"Brand\":\"Shalimar\'s Chef\",\"Size\":\"5g\",\"Expiry Date\":\"2027-07-15\"}'),
(152, 'Sunrise Pure Meat Masala', 'sunrise-pure-meat-masala', 'Sunrise Pure Meat Masala is a premium blended spice mix specially crafted for preparing delicious meat dishes. It adds rich aroma, authentic flavor, and vibrant color to mutton, chicken, and other meat recipes. Made from carefully selected spices and hygienically packed to preserve freshness and quality.', 5.00, NULL, 'SUNRI280', 100, 52, '[\"1784293566_9431.jpg\"]', NULL, 1, '2026-07-15 07:52:09', '2026-07-17 13:06:06', 0, '{\"Brand\":\"Sunrise\",\"Size\":\"6g\",\"Expiry Date\":\"2027-07-15\"}'),
(153, 'Sparkle Long Lasting Thick Scrub Pad (50% Extra Thick)', 'sparkle-long-lasting-thick-scrub-pad-50-extra-thick', 'Sparkle Long Lasting Thick Scrub Pad is a durable and extra-thick kitchen cleaning pad designed to remove tough grease, stains, and food residue from utensils, cookware, and kitchen surfaces. Made with strong scrubbing fibers for long-lasting performance and everyday cleaning.', 15.00, 10.00, 'SPARK550', 20, 52, '[\"1784293536_2912.jpg\"]', NULL, 1, '2026-07-15 07:57:26', '2026-07-17 13:05:36', 0, '{\"Brand\":\"Sparkle\",\"Size\":\"7.5x9.5 cm\",\"Expiry Date\":\"2029-07-15\"}'),
(154, 'Shalimar\'s Chef Red Chilli Powder', 'shalimars-chef-red-chilli-powder', 'Shalimar\'s Chef Red Chilli Powder is made from carefully selected premium red chillies, finely ground to deliver rich color, authentic taste, and balanced pungency. Ideal for curries, vegetables, marinades, snacks, and a wide variety of Indian dishes. Hygienically packed to preserve freshness and quality.', 10.00, NULL, 'SHALI167', 50, 52, '[\"1784293497_6967.jpg\"]', NULL, 1, '2026-07-15 07:59:50', '2026-07-17 13:04:57', 0, '{\"Brand\":\"Shalimar\'s Chef\",\"Size\":\"15g\",\"Expiry Date\":\"2027-07-15\"}'),
(155, 'Shalimar\'s Chef Cumin Powder', 'shalimars-chef-cumin-powder-1', 'Shalimar\'s Chef Cumin Powder is made from premium quality cumin seeds, finely ground to provide a rich aroma and authentic flavor. It is perfect for curries, dals, vegetables, rice dishes, marinades, and a variety of Indian recipes. Hygienically packed to preserve freshness, natural taste, and quality.', 45.00, 35.00, 'SHALI684', 20, 52, '[\"1784293463_2968.jpg\"]', 0.500, 1, '2026-07-15 08:07:57', '2026-07-17 13:04:23', 0, '{\"Brand\":\"Shalimar\'s Chef\",\"Size\":\"50g.\",\"Expiry Date\":\"2027-07-15\"}'),
(156, 'Shalimar\'s Chef Red Chilli Powder', 'shalimars-chef-red-chilli-powder-1', 'Shalimar\'s Chef Red Chilli Powder is made from premium quality red chillies, finely ground to deliver rich color, authentic flavor, and balanced pungency. Ideal for curries, vegetables, marinades, snacks, and a wide range of Indian dishes. Hygienically packed to preserve freshness and quality.', 35.00, 25.00, 'SHALI930', 20, 52, '[\"1784293435_9515.jpg\"]', 20.000, 1, '2026-07-15 08:13:55', '2026-07-17 13:03:55', 0, '{\"Brand\":\"Shalimar\'s Chef\",\"Size\":\"50g.\",\"Expiry Date\":\"2027-07-15\"}'),
(157, 'Sunrise Pure Garam Masala', 'sunrise-pure-garam-masala', 'Sunrise Pure Garam Masala is a premium blend of carefully selected aromatic spices that adds rich flavor and authentic taste to Indian dishes. Ideal for curries, vegetables, meat, biryani, pulao, and gravies. Hygienically packed to preserve freshness, aroma, and quality.', 55.00, NULL, 'SUNRI438', 10, 52, '[\"1784293404_7935.jpg\"]', 0.500, 1, '2026-07-15 08:22:43', '2026-07-17 13:03:24', 0, '{\"Brand\":\"Sunrise\",\"Size\":\"50g.\",\"Expiry Date\":\"2027-07-15\"}'),
(158, 'Sunrise Pure Meat Masala', 'sunrise-pure-meat-masala-1', 'Sunrise Pure Meat Masala is a premium blend of aromatic spices specially formulated for preparing delicious meat dishes. It enhances the taste, aroma, and color of mutton, chicken, and other meat recipes. Hygienically packed to preserve freshness and ensure authentic flavor in every meal.', 49.00, NULL, 'SUNRI204', 10, 52, '[\"1784293362_3879.jpg\"]', 0.500, 1, '2026-07-15 08:25:25', '2026-07-17 13:02:42', 0, '{\"Brand\":\"Sunrise\",\"Size\":\"50g.\",\"Expiry Date\":\"2027-07-15\"}'),
(159, 'Anmol Marie Plus Biscuits 72g', 'anmol-marie-plus-biscuits-72g', 'Anmol Marie Plus is a light and crispy tea-time biscuit made from quality wheat flour. It is enriched with milk and offers a delicious taste that pairs perfectly with tea or coffee. Ideal for everyday snacking. Net Weight: 72g.', 10.00, NULL, 'ANMOL378', 50, 52, '[\"1784344139_6626_0.jpg\"]', 0.720, 1, '2026-07-18 03:08:59', '2026-07-18 03:08:59', 50, '{\"Brand\":\"Anmol\",\"Size\":\"72g\",\"Expiry Date\":\"2027-01-19\"}'),
(160, 'Anmol Marie Plus Biscuits 234g (184g + 50g Extra)', 'anmol-marie-plus-biscuits-234g-184g-50g-extra', 'Anmol Marie Plus Biscuits are light, crispy tea-time biscuits made from quality wheat flour. Enriched with milk, they contain zero cholesterol and no trans fat. This value pack includes 184g + 50g Extra, making it ideal for everyday family snacking with tea or coffee.', 30.00, NULL, 'ANMOL402', 30, 52, '[\"1784344539_9226_0.jpg\",\"1784344539_9518_1.jpg\"]', NULL, 1, '2026-07-18 03:15:39', '2026-07-18 03:15:39', 30, '{\"Brand\":\"Anmol\",\"Size\":\"234g (184g + 50g Extra)\",\"Expiry Date\":\"2027-07-18\"}'),
(162, 'Harpic Power Plus 10X Total Clean Original Fresh Disinfectant Toilet Cleaner', 'harpic-power-plus-10x-total-clean-original-fresh-disinfectant-toilet-cleaner', 'Harpic Power Plus 10X Total Clean Original Fresh is a powerful disinfectant toilet cleaner that removes tough stains, kills 99.99% of germs, and leaves your toilet fresh and hygienic. Its thick formula reaches under the rim for deep cleaning and provides long-lasting freshness. Visible cleaning action starts in just 5 minutes.', 46.00, NULL, 'HARPI861', 5, 52, '[\"1784888629_4639_0.jpg\"]', 0.200, 1, '2026-07-24 10:23:49', '2026-07-24 10:23:49', 5, '{\"Brand\":\"Harpic\",\"Size\":\"200ml\",\"Expiry Date\":\"2028-07-24\"}'),
(163, 'Anmol Butter Bake Rich Butter Cookies 20g', 'anmol-butter-bake-rich-butter-cookies-20g', 'Anmol Butter Bake Rich Butter Cookies are delicious, crispy, and buttery cookies made with quality ingredients for a rich taste. Perfect for tea-time, snacks, or anytime cravings. Convenient 20g pack for on-the-go enjoyment.', 20.00, NULL, 'ANMOL239', 30, 52, '[\"1784888921_8729_0.jpg\"]', NULL, 1, '2026-07-24 10:28:41', '2026-07-24 10:28:41', 30, '{\"Brand\":\"Anmol\",\"Size\":\"99gram+20gram=119gram\",\"Expiry Date\":\"2027-03-01\"}'),
(164, 'Parle-G Gluco Biscuits 12.5% Extra ₹10 Pack', 'parle-g-gluco-biscuits-125-extra-10-pack', 'Parle', 10.00, NULL, 'PARLE918', 20, 52, '[\"1784889399_3869_0.jpg\"]', 80.000, 1, '2026-07-24 10:36:39', '2026-07-24 10:36:39', 20, '{\"Brand\":\"Parle\",\"Size\":\"\\u20b910 Pack (12.5% Extra)\",\"Expiry Date\":\"2027-07-24\"}'),
(165, 'Super Hogla Washing Soap', 'super-hogla-washing-soap', 'Super Hogla Washing Soap is a high-quality laundry bar designed to remove tough stains and dirt from clothes. Its powerful cleansing formula helps keep fabrics clean and fresh, making it ideal for everyday hand washing and household laundry.', 15.00, NULL, 'SUPER917', 10, 52, '[\"1784890365_5806_0.jpg\"]', 20.000, 1, '2026-07-24 10:52:45', '2026-08-09 08:51:56', 0, '{\"Brand\":\"Hogla\",\"Size\":\"15grram\",\"Expiry Date\":\"2027-07-24\"}'),
(166, 'Premium Dry Red Chilli 100g', 'premium-dry-red-chilli', 'Premium quality dry red chilli, carefully selected and naturally dried to preserve its rich color, aroma, and spicy flavor. Ideal for cooking curries, stir-fries, pickles, and various traditional dishes. Hygienically packed to ensure freshness and long shelf life.', 35.00, NULL, 'PREMI354', 10, 52, '[\"1785142224_5110_0.png\"]', NULL, 1, '2026-07-27 08:50:24', '2026-08-09 08:53:30', 0, '{\"Brand\":\"Fresh Spice\",\"Size\":\"100g\",\"Expiry Date\":\"2027-07-27\"}'),
(167, 'Saffola Soya Chunks', 'saffola-soya-chunks', 'Saffola Soya Chunks are high in protein and perfect for healthy everyday meals. These tender and juicy soya chunks are ready in just 5 minutes and can be used in curries, pulao, fried rice, and many other delicious recipes. Hygienically packed to ensure freshness and quality.', 10.00, NULL, 'SAFFO328', 50, 52, '[\"1785142489_2698_0.jpg\"]', NULL, 1, '2026-07-27 08:54:49', '2026-07-27 08:54:49', 50, '{\"Brand\":\"Saffola\",\"Size\":\"40g\",\"Expiry Date\":\"2027-07-27\"}'),
(168, 'WAI WAI Masala Delight Instant Noodles', 'wai-wai-masala-delight-instant-noodles', 'WAI WAI Masala Delight Instant Noodles are delicious instant noodles made with the flavour of 10 spices. Ready in just a few minutes, they are perfect for a quick and tasty meal. Enjoy them as soup, stir-fried, or straight from the pack. Hygienically packed to ensure freshness and quality.', 5.00, NULL, 'WAIWA902', 50, 52, '[\"1785143059_3650_0.jpg\"]', NULL, 1, '2026-07-27 09:04:19', '2026-07-27 09:04:19', 50, '{\"Brand\":\"WAI WAI\",\"Size\":\"25g\",\"Expiry Date\":\"2027-04-27\"}'),
(169, 'WAI WAI High Five Chicken Flavoured Instant Noodles', 'wai-wai-high-five-chicken-flavoured-instant-noodles', 'WAI WAI High Five Chicken Flavoured Instant Noodles are delicious ready-to-eat pre-cooked noodles with a rich chicken flavour. Quick and easy to prepare, they are perfect for a tasty snack or meal anytime. Hygienically packed to ensure freshness and quality.', 5.00, NULL, 'WAIWA938', 50, 52, '[\"1785143430_1170_0.jpg\"]', NULL, 1, '2026-07-27 09:10:30', '2026-07-27 09:10:30', 50, '{\"Brand\":\"WAI WAI\",\"Size\":\"22g\",\"Expiry Date\":\"2027-04-27\"}'),
(170, 'Red Split Lentils 250g  (Masoor Dal)', 'red-split-lentils-masoor-dal', 'Premium quality Red Split Lentils (Masoor Dal), naturally rich in protein, dietary fiber, vitamins, and minerals. Carefully cleaned and hygienically packed to preserve freshness and taste. Ideal for preparing dal, soups, curries, khichdi, and other healthy recipes. Suitable for everyday home cooking.', 35.00, NULL, 'REDSP475', 10, 52, '[\"1785150173_8432_0.png\"]', NULL, 1, '2026-07-27 11:02:53', '2026-07-29 02:00:52', 0, '{\"Brand\":\"Dabang\",\"Size\":\"250g\"}'),
(171, 'Yellow Split Moong Dal250g', 'yellow-split-moong-dal', 'Premium quality Yellow Split Moong Dal, carefully cleaned and hygienically packed to ensure freshness and superior taste. Rich in protein, dietary fiber, vitamins, and essential minerals, making it an excellent choice for healthy everyday meals. Perfect for preparing dal, khichdi, soups, curries, and a variety of traditional recipes.', 30.00, NULL, 'YELLO641', 10, 52, '[\"1785150543_4127_0.png\"]', NULL, 1, '2026-07-27 11:09:03', '2026-07-29 01:59:31', 0, '{\"Size\":\"250g\"}'),
(172, 'White Urad Dal 250g  (Split & Skinned) ঠাকুর ডাল', 'white-urad-dal-split-skinned', 'Premium quality White Urad Dal (Split & Skinned), carefully processed and hygienically packed to ensure superior freshness and purity. Rich in protein, dietary fiber, calcium, and essential nutrients, it is ideal for preparing dal, dosa batter, idli, vada, papad, and various traditional recipes. A healthy choice for everyday cooking.', 30.00, NULL, 'WHITE327', 10, 52, '[\"1785150936_7144_0.png\"]', NULL, 1, '2026-07-27 11:15:36', '2026-07-29 01:58:47', 0, '{\"Size\":\"250g\"}'),
(173, 'Kaka Jira (কালো জিরা) 100gram,s', 'kaka-jira-100grams', 'Premium quality Black Sesame Seeds (Kala Til), carefully cleaned and hygienically packed to maintain freshness, purity, and natural flavor. Rich in protein, healthy fats, calcium, iron, fiber, and essential nutrients. Ideal for baking, cooking, sweets, chutneys, salads, seasoning, and traditional recipes. A nutritious choice for everyday use.', 40.00, NULL, 'KAKAJ842', 10, 52, '[\"1785151570_6916_0.png\"]', NULL, 1, '2026-07-27 11:26:10', '2026-07-29 01:56:37', 0, '{\"Size\":\"100g\"}'),
(174, 'Panch Phoron (Five Spice Mix)', 'panch-phoron-five-spice-mix', 'Panch Phoron is a traditional Bengali five-spice blend made from Fenugreek, Nigella, Cumin, Black Mustard, and Fennel seeds. It is widely used in Indian and Bangladeshi cooking to add a rich aroma and authentic flavor to vegetables, lentils, fish, and meat dishes. Fresh, natural, and free from artificial colors or preservatives.', 5.00, NULL, 'PANCH996', 50, 52, '[\"1785289734_1099_0.png\"]', NULL, 1, '2026-07-29 01:48:54', '2026-07-29 01:48:54', 50, '{\"Brand\":\"Debraj\",\"Size\":\"20g\",\"Expiry Date\":\"2027-07-29\"}'),
(175, 'Black Chickpeas (Kala Chana)250g', 'black-chickpeas-kala-chana250g', 'Premium quality Black Chickpeas (Kala Chana), carefully cleaned and naturally processed for freshness. Rich in protein, dietary fiber, vitamins, and minerals, making them an excellent choice for healthy meals. Perfect for curries, salads, soups, sprouts, and traditional Indian and Bangladeshi recipes. Hygienically packed with no artificial colors, flavors, or preservatives.', 20.00, NULL, 'BLACK231', 10, 52, '[\"1785290016_6646_0.png\"]', NULL, 1, '2026-07-29 01:53:36', '2026-07-29 01:53:36', 10, '{\"Size\":\"250g\"}'),
(176, 'White Peas 250g (Safed Matar)', 'white-peas-250g-safed-matar', 'Premium quality White Peas (Safed Matar), carefully selected and hygienically packed to ensure freshness and superior taste. Rich in protein, dietary fiber, and essential nutrients, these dried white peas are ideal for curries, soups, snacks, and traditional Indian and Bangladeshi recipes. 100% natural with no artificial colors, flavors, or preservatives.', 20.00, NULL, 'WHITE666', 10, 52, '[\"1785293302_8250_0.png\"]', NULL, 1, '2026-07-29 02:48:22', '2026-07-29 02:48:22', 10, '{\"Size\":\"250g\"}'),
(177, 'Premium Whole Garam Masala Mix (packet)', 'premium-whole-garam-masala-mix-packet', 'Premium Whole Garam Masala Mix is a carefully selected blend of high-quality whole spices including cinnamon, green cardamom, black pepper, mace, nutmeg, cloves, and other aromatic spices. Perfect for biryani, pulao, curry, korma, meat dishes, and traditional recipes. Freshly packed to preserve natural aroma, flavor, and freshness.', 10.00, NULL, 'PREMI833', 50, 52, '[\"1785297271_7943_0.png\"]', NULL, 1, '2026-07-29 03:54:31', '2026-07-29 03:54:31', 50, '{\"Size\":\"4g\"}'),
(178, 'পান Fresh Premium Betel Leaves (Paan Patta)best quality.', 'fresh-premium-betel-leaves-paan-pattabest-quality', 'Fresh Premium Betel Leaves (Paan Patta) are carefully handpicked to ensure superior quality, freshness, and rich natural flavor. These glossy green leaves are perfect for making sweet paan, meetha paan, traditional paan, religious rituals, and herbal uses. Hygienically packed to maintain freshness and delivered with the best quality.', 10.00, NULL, 'FRESH570', 100, 52, '[\"1785297710_1261_0.png\"]', NULL, 1, '2026-07-29 04:01:50', '2026-07-29 04:01:50', 100, '{\"Size\":\"25g\"}'),
(179, 'Chun (চুন)', 'chun', '**✨ Description**\r\n\r\n\"Chun is all about style, confidence, and creativity. Every moment tells a unique story, and every step reflects passion and personality. Stay inspired, stay authentic, and keep shining. ✨\"', 5.00, NULL, 'CHUN373', 50, 52, '[\"1785327140_2372_0.png\"]', NULL, 1, '2026-07-29 12:12:20', '2026-07-29 12:12:20', 50, '{\"Size\":\"25g\"}'),
(180, 'Gurudep dhupkati (ধূপকাটি)', 'gurudep-dhupkati', 'Khubbhalo sent dhupkati gurudeb 32grams', 25.00, 20.00, 'GURUD114', 30, 52, '[\"1785328816_8526_0.jpg\"]', NULL, 1, '2026-07-29 12:40:16', '2026-07-29 12:45:14', 0, '{\"Brand\":\"Parimal\",\"Size\":\"32g\",\"Expiry Date\":\"2028-07-29\"}'),
(181, 'Candel 7x16 (মম বাতি)', 'candel-7x16', 'Best prodact candel moon lite best wax candles 7x16', 5.00, NULL, 'CANDE170', 30, 52, '[\"1785329778_5493_0.jpg\"]', NULL, 1, '2026-07-29 12:56:18', '2026-07-29 12:56:18', 30, '{\"Brand\":\"Moon light\",\"Size\":\"5g\"}'),
(182, 'Moon lite candel 500x6 (মম)', 'moon-lite-candel-500x6', 'Best prodact candel moon lite candel 500x6', 30.00, NULL, 'MOONL553', 20, 52, '[\"1785330384_7381_0.jpg\"]', NULL, 1, '2026-07-29 13:06:24', '2026-07-29 13:06:24', 20, '{\"Brand\":\"Moon light\",\"Size\":\"80g\"}'),
(183, 'Beson (বেসন) 100g sourav', 'beson-100g-sourav', 'Best quality product beson 100g', 10.00, NULL, 'BESON932', 20, 52, '[\"1785407032_7446_0.jpg\"]', NULL, 1, '2026-07-30 10:23:52', '2026-07-30 10:23:52', 20, '{\"Brand\":\"Sourav\",\"Size\":\"100g\"}'),
(184, 'Lux Velvet Glow Soap (Jasmine & Vitamin E)', 'lux-velvet-glow-soap-jasmine-vitamin-e', 'Lux Velvet Glow Soap with Jasmine & Vitamin E is a premium beauty soap enriched with 7 beauty ingredients. It gently cleanses the skin while leaving it soft, smooth, and beautifully fragrant with a long-lasting jasmine scent. Suitable for everyday use.', 10.00, NULL, 'LUXVE140', 30, 52, '[\"1785431812_6584_0.png\"]', NULL, 1, '2026-07-30 17:16:52', '2026-07-30 17:46:54', 0, '{\"Brand\":\"Lux\",\"Size\":\"43g\",\"Expiry Date\":\"2028-07-30\"}'),
(185, 'Product Name: Vivel Aloe Vera Soap with Vitamin E', 'product-name-vivel-aloe-vera-soap-with-vitamin-e', 'Vivel Aloe Vera Soap with Vitamin E is enriched with the goodness of Aloe Vera and Vitamin E to gently cleanse and nourish your skin. Its moisturizing formula helps keep skin soft, smooth, and refreshed after every wash. Suitable for daily use and all skin types.', 10.00, NULL, 'PRODU342', 30, 52, '[\"1785432080_1942_0.png\"]', NULL, 1, '2026-07-30 17:21:20', '2026-07-30 17:21:20', 30, '{\"Brand\":\"Vivel\",\"Size\":\"43g\",\"Expiry Date\":\"2029-07-30\"}'),
(186, 'Lifebuoy Total 10 Soap (Silver Shield Formula)', 'lifebuoy-total-10-soap-silver-shield-formula', 'Lifebuoy Total 10 Soap with Silver Shield Formula helps protect against germs while keeping your skin clean and fresh. Its rich lather removes dirt and impurities, making it ideal for everyday family use. Suitable for all skin types.', 10.00, NULL, 'LIFEB287', 30, 52, '[\"1785432356_7825_0.png\"]', NULL, 1, '2026-07-30 17:25:56', '2026-07-30 17:25:56', 30, '{\"Brand\":\"Lifebuoy\",\"Size\":\"40g\",\"Expiry Date\":\"2029-07-30\"}'),
(187, 'Dettol Original Germ Defence Soap', 'dettol-original-germ-defence-soap', 'Dettol Original Germ Defence Soap is formulated to provide effective germ protection while gently cleansing the skin. It helps protect against illness-causing germs, leaving your skin clean, fresh, and hygienic after every wash. Suitable for daily use and all skin types.', 10.00, NULL, 'DETTO949', 30, 52, '[\"1785432645_3988_0.png\"]', NULL, 1, '2026-07-30 17:30:45', '2026-07-30 17:30:45', 30, '{\"Brand\":\"Detol\",\"Size\":\"45g\",\"Expiry Date\":\"2028-07-30\"}'),
(188, '(নিহার তেল 100ml)Nihar Naturals Coconut Hair Oil with Methi & Jasmine', 'nihar-naturals-coconut-hair-oil-with-methi-jasmine', 'Nihar Naturals Coconut Hair Oil with Methi & Jasmine is a nourishing hair oil enriched with coconut oil, methi, and jasmine extracts. It helps strengthen hair from the roots, nourishes the scalp, reduces dryness, and leaves hair soft, smooth, and healthy-looking. Suitable for regular use and all hair types.', 48.00, 45.00, 'NIHAR377', 10, 52, '[\"1785585765_3988.png\"]', NULL, 1, '2026-07-30 17:36:46', '2026-08-01 12:02:45', 0, '{\"Brand\":\"Nihar Naturals\",\"Size\":\"100ml\",\"Expiry Date\":\"2028-07-30\"}');
INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `discount_price`, `sku`, `stock_quantity`, `category_id`, `images`, `weight`, `is_active`, `created_at`, `updated_at`, `stock`, `attributes`) VALUES
(189, 'Shalimar\'s Coconut Oil', 'shalimars-coconut-oil', 'Shalimar\'s Coconut Oil is made from quality coconut oil to nourish and moisturize hair from root to tip. It helps reduce dryness, improves hair softness and shine, and supports strong, healthy-looking hair with regular use. Suitable for all hair types and everyday use.', 120.00, 115.00, 'SHALI398', 10, 52, '[\"1785433405_4052_0.png\"]', NULL, 1, '2026-07-30 17:43:25', '2026-07-30 17:43:25', 10, '{\"Brand\":\"Shalimar\'s\",\"Size\":\"160ml\",\"Expiry Date\":\"2028-07-30\"}'),
(190, 'Shalimar\'s Coconut Oil', 'shalimars-coconut-oil-1', 'Shalimar\'s Coconut Oil is made from quality coconut oil to nourish and moisturize hair from root to tip. It helps reduce dryness, improves hair softness and shine, and supports strong, healthy-looking hair with regular use. Suitable for all hair types and everyday use.', 94.00, 85.00, 'SHALI593', 10, 52, '[\"1785433554_3008_0.png\"]', NULL, 1, '2026-07-30 17:45:54', '2026-07-30 17:45:54', 10, '{\"Brand\":\"Shalimar\'s\",\"Size\":\"100ml\",\"Expiry Date\":\"2028-07-30\"}'),
(191, 'Chanadal  (বুট ডাল )250g', 'chanadal-250g', 'A close-up, top-view photograph of a bowl made from a dried leaf, filled with bright yellow split chickpeas (chana dal). The lentils appear fresh, clean, and evenly sized, creating a vibrant and natural look. The rustic leaf bowl enhances the traditional and organic presentation, making the image ideal for food, agriculture, grocery, or healthy eating content. The background is transparent, keeping the focus entirely on the golden-yellow split lentils.', 25.00, NULL, 'CHANA753', 10, 52, '[\"1785495467_8429_0.png\"]', NULL, 1, '2026-07-31 10:57:47', '2026-07-31 10:57:47', 10, '{\"Size\":\"250g\"}'),
(192, 'Sparkle (সাবান বাটি)200g', 'sparkle-200g', 'A high-quality product image of a bright yellow plastic container of Sparkle Lime & Orange Dishwash. The round tub features a secure matching yellow lid and a vibrant label with bold red \"Sparkle\" branding, accompanied by fresh lime and orange slice graphics. An illustration of clean kitchen utensils highlights its dishwashing purpose, while the colorful citrus-themed design emphasizes freshness, grease-cutting power, and effective cleaning. The product is isolated on a transparent background, making it ideal for e-commerce listings, product catalogs, advertisements, and promotional materials.', 20.00, NULL, 'SPARK645', 20, 52, '[\"1785495853_4553_0.png\"]', NULL, 1, '2026-07-31 11:04:13', '2026-07-31 11:04:13', 20, '{\"Brand\":\"Sparkle\",\"Size\":\"200g\",\"Expiry Date\":\"2028-07-31\"}'),
(193, 'Nima sandal (নিমা সাবান)100g', 'nima-sandal-100g', 'A high-quality product image featuring a pack of three Nima Sandal Soap bars with Turmeric. The soaps are neatly arranged in an overlapping stack, showcasing elegant beige packaging with the prominent \"Nima\" logo and \"Sandal with Turmeric\" branding. The design includes sandalwood sticks and turmeric illustrations, emphasizing the soap\'s natural ingredients and skincare benefits. The product is isolated on a clean white background, making it ideal for e-commerce listings, product catalogs, advertisements, branding, and promotional materials.', 25.00, NULL, 'NIMAS145', 10, 52, '[\"1785496428_4428_0.png\"]', NULL, 1, '2026-07-31 11:13:48', '2026-07-31 11:13:48', 10, '{\"Brand\":\"Nima sandal\",\"Size\":\"100g\",\"Expiry Date\":\"2028-07-31\"}'),
(194, 'Nima sandal soap 300g (নিমা সাবান)3পিচ সেট', 'nima-sandal-soap-300g-3', 'A high-quality product image featuring a pack of three Nima Sandal Soap bars with Turmeric. The soaps are neatly arranged in an overlapping stack, showcasing elegant beige packaging with the prominent \"Nima\" logo and \"Sandal with Turmeric\" branding. The design includes sandalwood sticks and turmeric illustrations, emphasizing the soap\'s natural ingredients and skincare benefits. The product is isolated on a clean white background, making it ideal for e-commerce listings, product catalogs, advertisements, branding, and promotional materials.', 73.00, 70.00, 'NIMAS896', 10, 52, '[\"1785496668_5660_0.png\"]', NULL, 1, '2026-07-31 11:17:48', '2026-07-31 11:17:48', 10, '{\"Brand\":\"Nima sandal\",\"Size\":\"300g\",\"Expiry Date\":\"2028-07-31\"}'),
(195, 'Batasha1kg বাতাসা', 'batasha1kg', 'A high-quality product image of white sugar candy (Mishri/Batasha) neatly placed in a square ceramic bowl. The smooth, round, white sugar pieces are clean, uniformly shaped, and arranged to highlight their purity and traditional appeal. A few pieces are scattered around the bowl, adding a natural presentation. The product is isolated on a clean white background, making it ideal for e-commerce listings, grocery catalogs, food packaging, advertisements, and promotional materials.', 75.00, NULL, 'BATAS562', 10, 52, '[\"1785497171_9510_0.png\"]', NULL, 1, '2026-07-31 11:26:11', '2026-07-31 11:26:11', 10, '{\"Size\":\"1kg\",\"Expiry Date\":\"2027-07-31\"}'),
(196, 'Semai (সেমাই)100g', 'semai-100g', 'A high-quality product image of Applenty Roasted Vermicelli – Premium Quality, packaged in a vibrant red and transparent plastic pouch. The transparent section clearly displays the fine, golden roasted vermicelli strands inside, emphasizing their freshness and quality. The front of the package features bold branding along with an appetizing serving suggestion of creamy vermicelli pudding (Sheer Khurma/Semiya Kheer) garnished with almonds, cashews, pistachios, and raisins. The product is isolated on a clean white background, making it ideal for e-commerce listings, grocery catalogs, food packaging, advertisements, and promotional materials.', 25.00, 15.00, 'SEMAI959', 50, 52, '[\"1785497656_6564_0.png\"]', NULL, 1, '2026-07-31 11:34:16', '2026-07-31 11:34:16', 50, '{\"Brand\":\"Captain vermicelli\",\"Size\":\"100g\",\"Expiry Date\":\"2027-07-31\"}'),
(197, 'Britannia marie gold biscuit 384g', 'britannia-marie-gold-biscuit', 'A high-quality product image of Britannia Marie Gold biscuits in a vibrant yellow and red retail pack. The packaging prominently features the iconic \"Marie Gold\" branding, along with illustrations of golden wheat grains that emphasize the biscuits\' wholesome goodness. Two crisp, golden-brown Marie biscuits are displayed on the front, highlighting their classic round shape and crunchy texture. The pack also showcases the \"10 Vitamins & Minerals\" benefit, reinforcing its nutritious appeal. The product is isolated on a clean white background, making it ideal for e-commerce listings, grocery catalogs, product packaging, advertisements, and promotional materials.', 50.00, NULL, 'BRITA287', 20, 52, '[\"1785498058_8084_0.png\"]', NULL, 1, '2026-07-31 11:40:58', '2026-08-05 07:14:44', 0, '{\"Brand\":\"Britannia\",\"Size\":\"384g\",\"Expiry Date\":\"2027-01-31\"}'),
(198, 'Sonali kachi ghani mastar oil 200ml', 'sonali-kachi-ghani-mastar-oil-200ml', '# Sonali Kachi Ghani Mustard Oil\r\n\r\n### Short Description\r\nEnhance the flavor of your everyday meals with **Sonali Kachi Ghani Mustard Oil**. Traditionally extracted from premium quality mustard seeds, this oil delivers a strong aroma, rich pungency, and authentic taste to all your culinary creations.\r\n\r\n---\r\n\r\n### Key Features\r\n* **100% Pure & Authentic:** Made from carefully selected, high-quality mustard seeds using traditional cold-pressed techniques.\r\n* **Pungent Aroma & Flavor:** Retains the natural pungency and characteristic aroma essential for authentic Indian cooking.\r\n* **Health Benefits:** Rich in Monounsaturated Fatty Acids (MUFA), Polyunsaturated Fatty Acids (PUFA), and natural antioxidants that support heart health and digestion.\r\n* **Versatile Cooking Companion:** Perfect for deep frying, sautéing, traditional curry preparation, and pickling.\r\n\r\n---\r\n\r\n### Product Specifications\r\n* **Brand:** Sonali Brand\r\n* **Product Type:** Mustard Oil (Kachi Ghani / Grade 1)\r\n* **Packaging Type:** PET Bottle\r\n* **Ideal For:** Daily cooking, frying, and pickles', 61.00, 40.00, 'SONAL426', 20, 52, '[\"1785499171_8274_0.png\"]', NULL, 1, '2026-07-31 11:59:31', '2026-07-31 11:59:31', 20, '{\"Brand\":\"Sonali\",\"Size\":\"200ml\",\"Expiry Date\":\"2027-07-31\"}'),
(199, '(কিসমিস 100g )Premium Golden Raisins (Kismis)', '100g-premium-golden-raisins-kismis', 'High-quality premium golden raisins (kismis), naturally dried, sweet, and rich in essential nutrients. Perfect for daily snacking, baking, and traditional desserts.', 50.00, NULL, '100GP914', 10, 52, '[\"1785506241_2793_0.png\"]', NULL, 1, '2026-07-31 13:57:21', '2026-07-31 13:57:21', 10, '{\"Size\":\"100g\",\"Expiry Date\":\"2027-07-31\"}'),
(200, '(ভাজা বাদাম 100g ) Peanuts / Groundnuts (Badam)', '100g-peanuts-groundnuts-badam', 'Fresh and high-quality raw peanuts (groundnuts), naturally packed with protein, healthy fats, and vitamins. Ideal for roasting, cooking, snacking, and making home-made peanut butter.', 20.00, NULL, '100GP478', 10, 52, '[\"1785507001_1325_0.png\"]', NULL, 1, '2026-07-31 14:10:01', '2026-07-31 14:10:01', 10, '{\"Size\":\"100g\",\"Expiry Date\":\"2027-07-31\"}'),
(201, '(খেসারি ডাল 250g ) Premium Khesari Dal (Grass Pea)', '250g-premium-khesari-dal-grass-pea', 'High-quality premium Khesari Dal (Grass Pea). Naturally processed, rich in plant-based protein and nutrients. Ideal for preparing traditional dal recipes, boras, and snacks.', 20.00, NULL, '250GP672', 10, 52, '[\"1785507540_1274_0.png\"]', NULL, 1, '2026-07-31 14:19:00', '2026-07-31 14:19:00', 10, '{\"Size\":\"250g\",\"Expiry Date\":\"2027-07-31\"}'),
(202, 'Amul Taaza Homogenised Toned Milk 200ml', 'amul-taaza-homogenised-toned-milk-200ml', 'Amul Taaza is a high-quality, long-life UHT homogenised toned milk. Rich in essential nutrients like calcium and protein, it requires no boiling and is ready to drink straight from the pack. Perfect for direct consumption, tea, coffee, and daily recipes.', 20.00, NULL, 'AMULT332', 10, 52, '[\"1785509901_5854_0.png\"]', NULL, 1, '2026-07-31 14:58:21', '2026-07-31 14:58:21', 10, '{\"Brand\":\"Amul\",\"Size\":\"200ml\",\"Expiry Date\":\"2027-01-31\"}'),
(203, 'Amul gold 500ml Homogenised Toned Milk 500ml', 'amul-gold-homogenised-toned-milk-500ml', 'Amul Taaza is a high-quality, long-life UHT homogenised toned milk. Rich in essential nutrients like calcium and protein, it requires no boiling and is ready to drink straight from the pack. Perfect for direct consumption, tea, coffee, and daily recipes.', 45.00, NULL, 'AMULT851', 10, 52, '[\"1785510410_9231_0.png\"]', NULL, 1, '2026-07-31 15:06:50', '2026-08-01 11:52:25', 0, '{\"Brand\":\"Amul\",\"Size\":\"500ml\",\"Expiry Date\":\"2027-01-31\"}'),
(204, '(সফেদ 1কেজি )Safed Detergent Powder', '1-safed-detergent-powder', 'Safed Detergent Powder with Advanced Formula and Power Bullets. Effective stain removal and long-lasting fresh fragrance for your clothes.', 77.00, 75.00, '1SAFE592', 20, 52, '[\"1785587409_9926_0.png\"]', NULL, 1, '2026-08-01 12:30:09', '2026-08-01 12:30:09', 20, '{\"Brand\":\"Safed\",\"Size\":\"1kg\",\"Expiry Date\":\"2027-08-01\"}'),
(205, '(সফেদ 500g )Safed Detergent Powder', '500g-safed-detergent-powder', 'Safed Detergent Powder with Advanced Formula and Power Bullets. Effective stain removal and long-lasting fresh fragrance for your clothes.', 37.00, 35.00, '500GS583', 20, 52, '[\"1785589003_3062_0.png\"]', NULL, 1, '2026-08-01 12:56:43', '2026-08-01 12:56:43', 20, '{\"Brand\":\"Safed\",\"Size\":\"500g\",\"Expiry Date\":\"2028-08-01\"}');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5 CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `title`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(65, 60, 156, 5, 'Review', '6f5f5f6f6g', 'approved', '2026-07-04 09:31:42', '2026-07-10 16:18:09');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin','delivery') DEFAULT 'customer',
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `otp` varchar(6) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_otp` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `address`, `city`, `state`, `country`, `profile_image`, `pincode`, `is_active`, `created_at`, `updated_at`, `otp`, `otp_expires_at`, `is_verified`, `email_verified_at`, `last_login`, `login_otp`, `otp_expiry`, `reset_token`, `reset_token_expires_at`) VALUES
(149, 'Ziaul Mandal', 'Mandalvaraity786786@gmail.com', '8967136033', '$2y$10$jipmpQRtDNuvGLTa.qmyZO2tg4dOP0Glywxq.vNpNVN9RIriEWmLW', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-09 15:17:12', '2026-06-09 15:31:15', NULL, NULL, 1, '2026-06-09 15:17:12', NULL, NULL, NULL, NULL, NULL),
(150, 'Sahin Mandal', 'sahinmanda3777@gmail.com', '9856556545', '$2y$10$0lj8eJ9mwT8znTkecfbpF.EnmXiRhUsi3wGaD9YHkVQCxEYrZklCG', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-09 15:18:23', '2026-06-09 15:53:36', NULL, NULL, 1, '2026-06-09 15:18:23', NULL, NULL, NULL, NULL, NULL),
(151, 'Raju Ali', 'akkasalisareyarpar@gmail.com', '7719297565', '$2y$10$7tpE6aOlGcRoHevCsyaZC.zi4VP.aAuDeTZqR4Hos4.rOgYgvbHbe', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-10 16:49:46', '2026-08-15 02:40:03', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 'sribash sarkar', 'sribashsarkarblp@gmail.com', '9083646603', '$2y$10$3sYaaZpJvvpvne.I3LLmw.bUasMH7W7xG50Q.K3mFqiZ0PTtZQb9S', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-12 07:18:31', '2026-08-14 14:17:11', NULL, NULL, 1, '2026-06-12 07:18:31', NULL, NULL, NULL, NULL, NULL),
(153, 'ram', 'sribash@gmail.com', '7887787667', '$2y$10$Us10teuCMcPoqC6wagUo5Ojs9aqg4V80sZ9jmnzS34/rFp93WzMmC', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-13 08:44:51', '2026-06-13 08:44:51', NULL, NULL, 1, '2026-06-13 08:44:51', NULL, NULL, NULL, NULL, NULL),
(154, 'Akku Mandal', 'rkmandal78666@gmail.com', '8967092471', '$2y$10$3VqdY6QbgbbrguLYC5YX3.3vuI2GrbZkKw2.UxxzUzjIrs0Tzsc9S', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-19 08:47:49', '2026-08-12 16:21:29', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 'Sayan Banik', 'sayanbanikcob@gmail.com', '8768412832', '$2y$10$i2.9UMBruwdxLbDhekRULufT.gviOg1oUCMAaQ0rUiUvTsqRNTDGO', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-21 17:44:01', '2026-06-21 17:45:12', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 'ram', 'roy338004@gmail.com', '9635160436', '$2y$10$Sp9OwHWH8Qhzt6qaf7nUru/VxySzEkFzVZ59aYTopU4KQKss2N3Ou', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-04 06:39:01', '2026-07-04 09:22:18', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(165, 'ajgat', 'ajgarali70009@gmail.com', '8967092471', '$2y$10$QpIc/8/p4r7edfyklx9RFeA7pgNnUZnBSNlM392Ci2Q2ZX47PMO4y', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-05 04:18:30', '2026-08-12 14:15:22', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(166, 'Rahul Ali', 'alirahul62891@gmail.com', '7768953079', '$2y$10$OFxN0RIsCELjdZ1vidhM6.cxvodERsf6t09NdNKsYvWEfrrkW8tiK', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-06 12:00:28', '2026-07-06 12:02:16', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'pabitra roy', 'pabitraroy182@gmail.com', '8348663686', '$2y$10$sBq6N2Rz/FHoN1CB/V7lSuKP97p4k/C0KN6iGEGQAEwwo/GJCQh7m', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-06 15:43:53', '2026-07-06 15:47:35', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(168, 'ishan mandal', 'imramanda884@gmail.com', '8001748622', '$2y$10$p3xLO3jAIwXufVuHwmCEvujOf6IGhtD6bn/cpdadN.GOfMPXBItce', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-08 17:34:33', '2026-07-10 17:12:56', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(169, 'majnu miya', 'anjumak928@gmail.com', '9734861724', '$2y$10$UNhK1i06n7KFKtgk5MXzLeLyZzZXuPB0Ng0G.gFK0n6u/3ioEidQ6', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-31 07:15:40', '2026-07-31 07:17:05', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(170, 'nimai sarkar', 'sbankim382@gmail.com', '7076453563', '$2y$10$yDd4oVeMwojM78IPG2HEruFjyksS9W6qloYSKgckWcuN1v7avQoPy', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-07 02:25:10', '2026-08-07 02:27:13', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(171, 'aminur ali', 'aminurali312@gmail.com', '8328744448', '$2y$10$uZDXSnCGRjauzlpmrYq2SeecK.IDj7SggKeDK4z6Pbqu6o/c7vw2W', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-07 06:57:35', '2026-08-07 07:01:05', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(172, 'khairul islam5867', 'khairulislam986709@gmail.com', '8597924233', '$2y$10$4z5noAzMPGmawaTh0AaskuNtfPPvJfVpCikiEFlsOc88EQuGaZ6pC', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-07 08:33:02', '2026-08-07 08:33:02', NULL, NULL, 0, NULL, NULL, '923555', '2026-08-07 08:43:01', NULL, NULL),
(173, 'khairul islam5867', 'khairulislam586709@gmail.com', '8597924233', '$2y$10$QMhHqlKObjoaPmMYdTbcces4uyEx3J95lQNhC7qV4hr37xxUqVF2K', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-07 08:34:45', '2026-08-07 08:34:45', NULL, NULL, 0, NULL, NULL, '048955', '2026-08-07 08:44:45', NULL, NULL),
(174, 'khairul islam5867', 'mampikh09@gmail.com', '8597924233', '$2y$10$/zQ.6eVJb4UMjluCuHuAP.XHauhpZ7zlXyahlarBZWWgcClRB8Hly', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-07 08:37:04', '2026-08-07 08:38:58', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(175, 'Amur hamja', 'mariyakhatunn177@gmail.com', '7364997817', '$2y$10$9myCZ7jHLhGX3tYaLeuqluCbD88lJgci0iH6N6DjEdegdULyxBDWu', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-08 14:55:47', '2026-08-08 14:59:25', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(176, 'manowar miah', 'miahmanowar22@gmail.com', '8626092879', '$2y$10$zIpcobYNgGHDliFtvZd6rueBB72gIRuVd2iUqO2Vle06cVVGwpxp.', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-09 15:02:14', '2026-08-09 15:12:11', NULL, NULL, 1, NULL, NULL, '196507', '2026-08-09 15:22:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auth_token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tokens`
--

INSERT INTO `user_tokens` (`id`, `user_id`, `auth_token`, `created_at`, `expires_at`) VALUES
(15, 151, '3e4ed3171331b1d69860136c287115cd221358beee96f9f32735213badde95a4', '2026-06-10 16:53:03', '2026-07-10 16:53:03'),
(16, 152, '5be42d03e20f98b35b5ed26fac11af170b206edd754fee30ec47650e46497f70', '2026-06-12 10:36:49', '2026-07-12 10:36:49'),
(17, 154, '16b6aa0cd2bf00c3ba9e85268d8bc22225b289ae32061d7396589333bcc1b20c', '2026-06-19 08:57:08', '2026-07-19 08:57:08'),
(18, 155, 'fa4a10561bf20e13ec4e0a12e116885bdfa19360894b9d6e1ed9616b2990bb6e', '2026-06-21 17:45:12', '2026-07-21 17:45:12'),
(19, 152, '1aa7b125ee6e791c5da4b195ee239b3f241cfaab387b3b54476f11a0345724e3', '2026-07-02 11:05:41', '2026-08-01 11:05:41'),
(20, 152, '38c841a954cf5f5c82110018ea26e839894f2675f86762e1177d1cf492306cf4', '2026-07-03 09:52:36', '2026-08-02 09:52:36'),
(21, 152, '940def5d239d75fc4cbd7b6e7f06fda8a6324ab28bacfb234478ac4c8f3a24ff', '2026-07-03 12:09:15', '2026-08-02 12:09:15'),
(22, 152, '9ca93c24cf40eba4f44ff3fc0dda6f42ade51cc2ff91c9f4d9844be2c70aa5e7', '2026-07-03 12:41:31', '2026-08-02 12:41:31'),
(23, 152, 'dc6036812535f0c7f43707ca11e5ffd6a00ed2905522bbecfbbc79f882ff7e18', '2026-07-03 13:58:30', '2026-08-02 13:58:30'),
(24, 152, '4d025d50838c94d514d21b76786aa35e4838c0258a5464225ba8a5144f931ed9', '2026-07-03 15:50:21', '2026-08-02 15:50:21'),
(25, 152, '8462106fa1845c9eb17e396364de05384d62bc55914dfb8555954ef4844a040c', '2026-07-03 18:12:02', '2026-08-02 18:12:02'),
(26, 152, 'd793953b0afe18fd0efcdbc6a8f89235ceccb35f67fefcbc270a620397e18880', '2026-07-03 18:23:50', '2026-08-02 18:23:50'),
(27, 152, '6251e64e8864e053cb30b34d0144c1e6cd43535c5b5aff25e78d071c27e0e28a', '2026-07-03 18:27:19', '2026-08-02 18:27:19'),
(28, 156, '2d888274541020330a7f4fbb56071978c27f9f54de8f937bf101b6d0133bfefd', '2026-07-04 06:42:21', '2026-08-03 06:42:21'),
(29, 152, '2ff58b6293d58989b2ea316762b8ac94ccc7c80fdab1da25e157482bf7873818', '2026-07-04 08:36:51', '2026-08-03 08:36:51'),
(30, 156, '9cba05e710acc6cffaee032d0f049d4c6a45ab3175bf5fa9621ffcdf5516cbb9', '2026-07-04 09:22:18', '2026-08-03 09:22:18'),
(31, 165, '8815b0bd1b564b230f86983f0ea2f7a697e5007652fdece02aea9ba80ca02d81', '2026-07-05 04:19:36', '2026-08-04 04:19:36'),
(32, 152, 'baa11c6e80777a801197a406bfe46bc87c95cf09e24494741a5bbd86dcda1319', '2026-07-05 05:12:35', '2026-08-04 05:12:35'),
(33, 166, 'ac8b2b119b0eb56454e7023a9bd4576b778bf54d3b2b516a31591be2b98c5716', '2026-07-06 12:02:16', '2026-08-05 12:02:16'),
(34, 165, '055c10d84e9b8b8f022652952e81fa6fbed8431222ba6f751636ec750d4902cf', '2026-07-06 12:06:26', '2026-08-05 12:06:26'),
(35, 167, '32117e43425a00edae25eb2052192cbdad5eda33de8b36e4a4be6540af6a7894', '2026-07-06 15:46:09', '2026-08-05 15:46:09'),
(36, 167, '9af63190280318273cdd4b0a526a1674be02f238bdd2f37d49292b58d321e85e', '2026-07-06 15:47:35', '2026-08-05 15:47:35'),
(37, 165, '8f2d2f5e9b8fff5a20c752c4e51da60fd5aa027dd58a4202f990cde538ae64a8', '2026-07-08 11:34:07', '2026-08-07 11:34:07'),
(38, 168, 'b1304298d9f993ea25a411c5e0580a0a3e9a905cf84de2631113cba7d9fad2d1', '2026-07-10 17:03:40', '2026-08-09 17:03:40'),
(39, 168, '3a786ee8b018763511c29ad5f4e200592c728586a71af365d284affdfb28fb58', '2026-07-10 17:12:56', '2026-08-09 17:12:56'),
(40, 165, 'c5b3098319099453e7e4596f1e06cacf4cc85bc89821a31846bbfca9ec3351e8', '2026-07-12 05:29:37', '2026-08-11 05:29:37'),
(41, 152, '45f5ac357a1cca2d5445c66a2b81971b71882c99682c7329d2f1bc923f7634fd', '2026-07-12 06:42:01', '2026-08-11 06:42:01'),
(42, 154, '92cad94b5a089638c2df237743ff24140dee2ab1c97acc0e678d2083c62f6f56', '2026-07-12 08:26:59', '2026-08-11 08:26:59'),
(43, 154, '0df9aff319c5c5c00e331bcb9505e914dd8066ca37ef9986c87e873b4f0ec352', '2026-07-12 08:33:03', '2026-08-11 08:33:03'),
(44, 169, '0645c2dc1c8b4f618571d914dcb3e096d78652dd2adf58a78f1f7686ee20af39', '2026-07-31 07:17:05', '2026-08-30 07:17:05'),
(45, 170, '6fce262565954c12f886729b908725f4f42112b0fced3a54206f5f043df6804e', '2026-08-07 02:27:13', '2026-09-06 02:27:13'),
(46, 171, 'db1473d4c76b64bb726cc4f07c84e4165484f54fb7c56292d2cd85033844f23f', '2026-08-07 07:01:05', '2026-09-06 07:01:05'),
(47, 174, '057c6cfb10aab40a9656c613b65fc7e20475e913c221a0354469e1bccc9f985d', '2026-08-07 08:38:58', '2026-09-06 08:38:58'),
(48, 175, '23659b3a58b887b0918f4c02a022e31950b508f4e61c07bc2a7f7703ec3ccf50', '2026-08-08 14:59:25', '2026-09-07 14:59:25'),
(49, 176, '68d1a9395e014d68daf3f55e3816e12d690f76a92d1600a465a593522443da2c', '2026-08-09 15:05:19', '2026-09-08 15:05:19'),
(50, 165, '8f4adb30d976a582a5f10a36a9e588ec1c219fde8e9c217a20088c1b1464b335', '2026-08-12 14:15:22', '2026-09-11 14:15:22'),
(51, 154, '2b993c2e1494e26684a5b466d70bacf0e46c28eacd5f103333c640de29c4b72f', '2026-08-12 16:21:29', '2026-09-11 16:21:29'),
(52, 152, '276497130ba6c07ed906946b328453eda31468d6fd6d795de99aabaff28dca4b', '2026-08-14 08:33:14', '2026-09-13 08:33:14'),
(53, 152, '3d01ef760878c753b5b5929a35df871e23707bdef2af57a7b418fca77ed76014', '2026-08-14 09:10:48', '2026-09-13 09:10:48'),
(54, 152, 'f7d601e5ade91a51f1fb952144ba7372015a7f79c1466ce186a63b607c9ca131', '2026-08-14 10:11:42', '2026-09-13 10:11:42'),
(55, 152, '7947626d9a684ef00eb7baeadd269b8bcc7930c55a17464068be7b3f973114c8', '2026-08-14 14:17:11', '2026-09-13 14:17:11'),
(56, 151, 'dabdf26746ea08d8c670f8ccc8c7a19b916810bc101bab03e48f90b79066c44d', '2026-08-15 02:40:03', '2026-09-14 02:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(70, 152, 79, '2026-06-19 13:48:58'),
(73, 152, 61, '2026-07-03 12:10:11'),
(74, 156, 60, '2026-07-04 09:43:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `age_verifications`
--
ALTER TABLE `age_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_cart` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_product` (`cart_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_boys`
--
ALTER TABLE `delivery_boys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `employee_categories`
--
ALTER TABLE `employee_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `employee_credit_payments`
--
ALTER TABLE `employee_credit_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_credit_payment_customer` (`customer_id`),
  ADD KEY `fk_credit_payment_employee` (`employee_id`);

--
-- Indexes for table `employee_customers`
--
ALTER TABLE `employee_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_customer_ledger`
--
ALTER TABLE `employee_customer_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ledger_customer` (`customer_id`),
  ADD KEY `fk_ledger_employee` (`employee_id`),
  ADD KEY `fk_ledger_sale` (`sale_id`);

--
-- Indexes for table `employee_expenses`
--
ALTER TABLE `employee_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expense_employee` (`created_by`);

--
-- Indexes for table `employee_notifications`
--
ALTER TABLE `employee_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_payments`
--
ALTER TABLE `employee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_sale` (`sale_id`);

--
-- Indexes for table `employee_products`
--
ALTER TABLE `employee_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_products_name` (`name`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_expiry` (`expiry_date`);

--
-- Indexes for table `employee_product_stock`
--
ALTER TABLE `employee_product_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_stock` (`product_id`);

--
-- Indexes for table `employee_purchases`
--
ALTER TABLE `employee_purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_number` (`purchase_number`),
  ADD KEY `fk_purchase_supplier` (`supplier_id`),
  ADD KEY `fk_purchase_employee` (`employee_id`);

--
-- Indexes for table `employee_purchase_items`
--
ALTER TABLE `employee_purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchase_item_purchase` (`purchase_id`),
  ADD KEY `fk_purchase_item_product` (`product_id`);

--
-- Indexes for table `employee_sales`
--
ALTER TABLE `employee_sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `fk_sale_customer` (`customer_id`),
  ADD KEY `idx_sales_date` (`created_at`),
  ADD KEY `idx_sales_employee` (`employee_id`);

--
-- Indexes for table `employee_sale_items`
--
ALTER TABLE `employee_sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sale_item_product` (`product_id`),
  ADD KEY `idx_sale_items_sale` (`sale_id`);

--
-- Indexes for table `employee_stock_movements`
--
ALTER TABLE `employee_stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_movement_employee` (`employee_id`),
  ADD KEY `idx_stock_movement_product` (`product_id`);

--
-- Indexes for table `employee_suppliers`
--
ALTER TABLE `employee_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_users`
--
ALTER TABLE `employee_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inventory_purchases`
--
ALTER TABLE `inventory_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_users`
--
ALTER TABLE `inventory_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_delivery_id` (`assigned_delivery_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reviews_product_id` (`product_id`),
  ADD KEY `idx_reviews_user_id` (`user_id`),
  ADD KEY `idx_reviews_status` (`status`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auth_token` (`auth_token`),
  ADD KEY `user_id_idx` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `age_verifications`
--
ALTER TABLE `age_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `delivery_boys`
--
ALTER TABLE `delivery_boys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employee_categories`
--
ALTER TABLE `employee_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employee_credit_payments`
--
ALTER TABLE `employee_credit_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `employee_customers`
--
ALTER TABLE `employee_customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_customer_ledger`
--
ALTER TABLE `employee_customer_ledger`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `employee_expenses`
--
ALTER TABLE `employee_expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_notifications`
--
ALTER TABLE `employee_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `employee_payments`
--
ALTER TABLE `employee_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `employee_products`
--
ALTER TABLE `employee_products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employee_product_stock`
--
ALTER TABLE `employee_product_stock`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_purchases`
--
ALTER TABLE `employee_purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_purchase_items`
--
ALTER TABLE `employee_purchase_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_sales`
--
ALTER TABLE `employee_sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `employee_sale_items`
--
ALTER TABLE `employee_sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `employee_stock_movements`
--
ALTER TABLE `employee_stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `employee_suppliers`
--
ALTER TABLE `employee_suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_users`
--
ALTER TABLE `employee_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory_purchases`
--
ALTER TABLE `inventory_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `inventory_users`
--
ALTER TABLE `inventory_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
