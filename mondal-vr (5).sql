-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 05, 2026 at 03:12 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u391326945_mandalvariety`
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
(1, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(2, 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(3, 3, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(4, 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(5, 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(6, 6, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(7, 7, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(8, 8, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(9, 9, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(10, 10, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(11, 11, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(12, 12, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(13, 13, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(14, 14, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(15, 15, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(16, 16, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(17, 17, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(18, 18, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(19, 19, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(20, 20, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(21, 21, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(22, 22, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(23, 23, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(24, 24, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(25, 25, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(26, 26, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(27, 27, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(28, 28, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(29, 29, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(30, 30, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(31, 31, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(32, 32, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(33, 33, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(34, 34, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(35, 35, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(36, 36, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(37, 37, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(38, 38, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(39, 39, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(40, 40, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(41, 41, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(42, 42, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(43, 43, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(44, 44, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(45, 45, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(46, 46, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(47, 47, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(48, 48, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(49, 49, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(50, 50, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(57, 144, '2026-05-24 08:08:01', '2026-05-24 08:08:01'),
(58, 145, '2026-05-24 11:30:46', '2026-05-24 11:30:46'),
(59, 146, '2026-06-02 07:49:26', '2026-06-02 07:49:26'),
(60, 147, '2026-06-03 14:34:40', '2026-06-03 14:34:40');

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
(1, 31, 33, 5, 100.00, '2026-05-24 07:59:49'),
(2, 4, 33, 1, 455.00, '2026-05-24 07:59:49'),
(3, 12, 5, 5, 54.00, '2026-05-24 07:59:49'),
(4, 44, 16, 4, 81.00, '2026-05-24 07:59:49'),
(5, 37, 16, 5, 324.00, '2026-05-24 07:59:49'),
(6, 3, 40, 1, 234.00, '2026-05-24 07:59:49'),
(7, 43, 38, 5, 287.00, '2026-05-24 07:59:49'),
(9, 46, 21, 2, 155.00, '2026-05-24 07:59:49'),
(10, 26, 9, 3, 254.00, '2026-05-24 07:59:49'),
(12, 30, 40, 5, 71.00, '2026-05-24 07:59:49'),
(13, 5, 35, 2, 279.00, '2026-05-24 07:59:49'),
(14, 17, 9, 3, 471.00, '2026-05-24 07:59:49'),
(15, 5, 16, 3, 165.00, '2026-05-24 07:59:49'),
(16, 11, 29, 5, 380.00, '2026-05-24 07:59:49'),
(17, 20, 40, 5, 24.00, '2026-05-24 07:59:49'),
(18, 43, 36, 3, 497.00, '2026-05-24 07:59:49'),
(19, 43, 7, 2, 155.00, '2026-05-24 07:59:49'),
(21, 18, 19, 5, 127.00, '2026-05-24 07:59:49'),
(22, 46, 22, 2, 371.00, '2026-05-24 07:59:49'),
(23, 41, 17, 5, 270.00, '2026-05-24 07:59:49'),
(24, 17, 4, 1, 344.00, '2026-05-24 07:59:49'),
(25, 28, 18, 1, 21.00, '2026-05-24 07:59:49'),
(26, 22, 50, 2, 346.00, '2026-05-24 07:59:49'),
(27, 17, 11, 4, 302.00, '2026-05-24 07:59:49'),
(28, 46, 28, 5, 24.00, '2026-05-24 07:59:49'),
(30, 3, 24, 5, 302.00, '2026-05-24 07:59:49'),
(31, 10, 28, 2, 41.00, '2026-05-24 07:59:49'),
(32, 20, 24, 1, 480.00, '2026-05-24 07:59:49'),
(33, 23, 14, 2, 361.00, '2026-05-24 07:59:49'),
(34, 7, 23, 5, 472.00, '2026-05-24 07:59:49'),
(35, 27, 40, 2, 493.00, '2026-05-24 07:59:49'),
(36, 16, 11, 2, 471.00, '2026-05-24 07:59:49'),
(37, 27, 2, 2, 397.00, '2026-05-24 07:59:49'),
(38, 22, 27, 2, 156.00, '2026-05-24 07:59:49'),
(39, 11, 45, 1, 215.00, '2026-05-24 07:59:49'),
(40, 3, 31, 2, 122.00, '2026-05-24 07:59:49'),
(41, 30, 23, 3, 440.00, '2026-05-24 07:59:49'),
(42, 15, 15, 1, 357.00, '2026-05-24 07:59:49'),
(43, 13, 26, 3, 162.00, '2026-05-24 07:59:49'),
(44, 5, 50, 3, 199.00, '2026-05-24 07:59:49'),
(45, 42, 33, 4, 367.00, '2026-05-24 07:59:49'),
(46, 35, 22, 1, 79.00, '2026-05-24 07:59:49'),
(47, 17, 12, 5, 155.00, '2026-05-24 07:59:49'),
(48, 3, 7, 5, 242.00, '2026-05-24 07:59:49'),
(49, 23, 47, 3, 243.00, '2026-05-24 07:59:49'),
(50, 39, 33, 1, 217.00, '2026-05-24 07:59:49'),
(64, 58, 51, 2, 999.00, '2026-05-28 18:47:37'),
(74, 60, 1, 1, 899.00, '2026-06-03 15:10:57'),
(77, 59, 1, 2, 899.00, '2026-06-04 17:57:47');

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
(1, 'Category 1', 'category-1', 'Quick commerce category 1', 'cat1.jpg', 1, '2026-05-24 07:55:03'),
(2, 'Category 2', 'category-2', 'Quick commerce category 2', 'cat2.jpg', 1, '2026-05-24 07:55:03'),
(3, 'Category 3', 'category-3', 'Quick commerce category 3', 'cat3.jpg', 1, '2026-05-24 07:55:03'),
(4, 'Category 4', 'category-4', 'Quick commerce category 4', 'cat4.jpg', 1, '2026-05-24 07:55:03'),
(5, 'Category 5', 'category-5', 'Quick commerce category 5', 'cat5.jpg', 1, '2026-05-24 07:55:03'),
(6, 'Category 6', 'category-6', 'Quick commerce category 6', 'cat6.jpg', 1, '2026-05-24 07:55:03'),
(7, 'Category 7', 'category-7', 'Quick commerce category 7', 'cat7.jpg', 1, '2026-05-24 07:55:03'),
(8, 'Category 8', 'category-8', 'Quick commerce category 8', 'cat8.jpg', 1, '2026-05-24 07:55:03'),
(9, 'Category 9', 'category-9', 'Quick commerce category 9', 'cat9.jpg', 1, '2026-05-24 07:55:03'),
(10, 'Category 10', 'category-10', 'Quick commerce category 10', 'cat10.jpg', 1, '2026-05-24 07:55:03'),
(11, 'Category 11', 'category-11', 'Quick commerce category 11', 'cat11.jpg', 1, '2026-05-24 07:55:03'),
(12, 'Category 12', 'category-12', 'Quick commerce category 12', 'cat12.jpg', 1, '2026-05-24 07:55:03'),
(13, 'Category 13', 'category-13', 'Quick commerce category 13', 'cat13.jpg', 1, '2026-05-24 07:55:03'),
(14, 'Category 14', 'category-14', 'Quick commerce category 14', 'cat14.jpg', 1, '2026-05-24 07:55:03'),
(15, 'Category 15', 'category-15', 'Quick commerce category 15', 'cat15.jpg', 1, '2026-05-24 07:55:03'),
(16, 'Category 16', 'category-16', 'Quick commerce category 16', 'cat16.jpg', 1, '2026-05-24 07:55:03'),
(17, 'Category 17', 'category-17', 'Quick commerce category 17', 'cat17.jpg', 1, '2026-05-24 07:55:03'),
(18, 'Category 18', 'category-18', 'Quick commerce category 18', 'cat18.jpg', 1, '2026-05-24 07:55:03'),
(19, 'Category 19', 'category-19', 'Quick commerce category 19', 'cat19.jpg', 1, '2026-05-24 07:55:03'),
(20, 'Category 20', 'category-20', 'Quick commerce category 20', 'cat20.jpg', 1, '2026-05-24 07:55:03'),
(21, 'Category 21', 'category-21', 'Quick commerce category 21', 'cat21.jpg', 1, '2026-05-24 07:55:03'),
(22, 'Category 22', 'category-22', 'Quick commerce category 22', 'cat22.jpg', 1, '2026-05-24 07:55:03'),
(23, 'Category 23', 'category-23', 'Quick commerce category 23', 'cat23.jpg', 1, '2026-05-24 07:55:03'),
(24, 'Category 24', 'category-24', 'Quick commerce category 24', 'cat24.jpg', 1, '2026-05-24 07:55:03'),
(25, 'Category 25', 'category-25', 'Quick commerce category 25', 'cat25.jpg', 1, '2026-05-24 07:55:03'),
(26, 'Category 26', 'category-26', 'Quick commerce category 26', 'cat26.jpg', 1, '2026-05-24 07:55:03'),
(27, 'Category 27', 'category-27', 'Quick commerce category 27', 'cat27.jpg', 1, '2026-05-24 07:55:03'),
(28, 'Category 28', 'category-28', 'Quick commerce category 28', 'cat28.jpg', 1, '2026-05-24 07:55:03'),
(29, 'Category 29', 'category-29', 'Quick commerce category 29', 'cat29.jpg', 1, '2026-05-24 07:55:03'),
(30, 'Category 30', 'category-30', 'Quick commerce category 30', 'cat30.jpg', 1, '2026-05-24 07:55:03'),
(31, 'Category 31', 'category-31', 'Quick commerce category 31', 'cat31.jpg', 1, '2026-05-24 07:55:03'),
(32, 'Category 32', 'category-32', 'Quick commerce category 32', 'cat32.jpg', 1, '2026-05-24 07:55:03'),
(33, 'Category 33', 'category-33', 'Quick commerce category 33', 'cat33.jpg', 1, '2026-05-24 07:55:03'),
(34, 'Category 34', 'category-34', 'Quick commerce category 34', 'cat34.jpg', 1, '2026-05-24 07:55:03'),
(35, 'Category 35', 'category-35', 'Quick commerce category 35', 'cat35.jpg', 1, '2026-05-24 07:55:03'),
(36, 'Category 36', 'category-36', 'Quick commerce category 36', 'cat36.jpg', 1, '2026-05-24 07:55:03'),
(37, 'Category 37', 'category-37', 'Quick commerce category 37', 'cat37.jpg', 1, '2026-05-24 07:55:03'),
(38, 'Category 38', 'category-38', 'Quick commerce category 38', 'cat38.jpg', 1, '2026-05-24 07:55:03'),
(39, 'Category 39', 'category-39', 'Quick commerce category 39', 'cat39.jpg', 1, '2026-05-24 07:55:03'),
(40, 'Category 40', 'category-40', 'Quick commerce category 40', 'cat40.jpg', 1, '2026-05-24 07:55:03'),
(41, 'Category 41', 'category-41', 'Quick commerce category 41', 'cat41.jpg', 1, '2026-05-24 07:55:03'),
(42, 'Category 42', 'category-42', 'Quick commerce category 42', 'cat42.jpg', 1, '2026-05-24 07:55:03'),
(43, 'Category 43', 'category-43', 'Quick commerce category 43', 'cat43.jpg', 1, '2026-05-24 07:55:03'),
(44, 'Category 44', 'category-44', 'Quick commerce category 44', 'cat44.jpg', 1, '2026-05-24 07:55:03'),
(45, 'Category 45', 'category-45', 'Quick commerce category 45', 'cat45.jpg', 1, '2026-05-24 07:55:03'),
(46, 'Category 46', 'category-46', 'Quick commerce category 46', 'cat46.jpg', 1, '2026-05-24 07:55:03'),
(47, 'Category 47', 'category-47', 'Quick commerce category 47', 'cat47.jpg', 1, '2026-05-24 07:55:03'),
(48, 'Category 48', 'category-48', 'Quick commerce category 48', 'cat48.jpg', 1, '2026-05-24 07:55:03'),
(49, 'Category 49', 'category-49', 'Quick commerce category 49', 'cat49.jpg', 1, '2026-05-24 07:55:03'),
(50, 'Category 50', 'category-50', 'Quick commerce category 50', 'cat50.jpg', 1, '2026-05-24 07:55:03');

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

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount`, `expiry`) VALUES
(2, 'SAVE2', 179, '2027-12-31'),
(3, 'SAVE3', 60, '2027-12-31'),
(4, 'SAVE4', 118, '2027-12-31'),
(5, 'SAVE5', 39, '2027-12-31'),
(6, 'SAVE6', 149, '2027-12-31'),
(7, 'SAVE7', 67, '2027-12-31'),
(8, 'SAVE8', 175, '2027-12-31'),
(9, 'SAVE9', 48, '2027-12-31'),
(10, 'SAVE10', 78, '2027-12-31'),
(11, 'SAVE11', 46, '2027-12-31'),
(12, 'SAVE12', 28, '2027-12-31'),
(13, 'SAVE13', 25, '2027-12-31'),
(14, 'SAVE14', 52, '2027-12-31'),
(15, 'SAVE15', 88, '2027-12-31'),
(16, 'SAVE16', 162, '2027-12-31'),
(17, 'SAVE17', 155, '2027-12-31'),
(18, 'SAVE18', 83, '2027-12-31'),
(19, 'SAVE19', 122, '2027-12-31'),
(20, 'SAVE20', 41, '2027-12-31'),
(21, 'SAVE21', 129, '2027-12-31'),
(22, 'SAVE22', 186, '2027-12-31'),
(23, 'SAVE23', 87, '2027-12-31'),
(24, 'SAVE24', 189, '2027-12-31'),
(25, 'SAVE25', 113, '2027-12-31'),
(26, 'SAVE26', 79, '2027-12-31'),
(27, 'SAVE27', 138, '2027-12-31'),
(28, 'SAVE28', 148, '2027-12-31'),
(29, 'SAVE29', 136, '2027-12-31'),
(30, 'SAVE30', 122, '2027-12-31'),
(31, 'SAVE31', 30, '2027-12-31'),
(32, 'SAVE32', 163, '2027-12-31'),
(33, 'SAVE33', 20, '2027-12-31'),
(34, 'SAVE34', 120, '2027-12-31'),
(35, 'SAVE35', 198, '2027-12-31'),
(36, 'SAVE36', 92, '2027-12-31'),
(37, 'SAVE37', 164, '2027-12-31'),
(38, 'SAVE38', 74, '2027-12-31'),
(39, 'SAVE39', 16, '2027-12-31'),
(40, 'SAVE40', 33, '2027-12-31'),
(41, 'SAVE41', 68, '2027-12-31'),
(42, 'SAVE42', 182, '2027-12-31'),
(43, 'SAVE43', 157, '2027-12-31'),
(44, 'SAVE44', 160, '2027-12-31'),
(45, 'SAVE45', 15, '2027-12-31'),
(46, 'SAVE46', 182, '2027-12-31'),
(47, 'SAVE47', 78, '2027-12-31'),
(48, 'SAVE48', 157, '2027-12-31'),
(49, 'SAVE49', 20, '2027-12-31'),
(50, 'SAVE50', 54, '2027-12-31'),
(51, 'DISCOUNT10', 0, NULL);

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
(1, 31, NULL, 'Offer 1', 'percent', 38.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(2, 18, NULL, 'Offer 2', 'percent', 16.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(3, 28, NULL, 'Offer 3', 'percent', 36.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(4, 31, NULL, 'Offer 4', 'percent', 27.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(5, 22, NULL, 'Offer 5', 'percent', 25.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(6, 11, NULL, 'Offer 6', 'percent', 26.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(7, 45, NULL, 'Offer 7', 'percent', 36.00, '2026-01-01', '2026-12-31', 'active', 3, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(8, 43, NULL, 'Offer 8', 'percent', 30.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(9, 3, NULL, 'Offer 9', 'percent', 34.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(10, 21, NULL, 'Offer 10', 'percent', 21.00, '2026-01-01', '2026-12-31', 'active', 3, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(11, 8, NULL, 'Offer 11', 'percent', 30.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(12, 1, NULL, 'Offer 12', 'percent', 39.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(13, 27, NULL, 'Offer 13', 'percent', 8.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(14, 34, NULL, 'Offer 14', 'percent', 28.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(17, 18, NULL, 'Offer 17', 'percent', 40.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(18, 19, NULL, 'Offer 18', 'percent', 33.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(19, 8, NULL, 'Offer 19', 'percent', 6.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(20, 16, NULL, 'Offer 20', 'percent', 15.00, '2026-01-01', '2026-12-31', 'active', 3, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(21, 36, NULL, 'Offer 21', 'percent', 5.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(22, 27, NULL, 'Offer 22', 'percent', 10.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(23, 8, NULL, 'Offer 23', 'percent', 34.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(24, 42, NULL, 'Offer 24', 'percent', 14.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(25, 46, NULL, 'Offer 25', 'percent', 23.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(26, 46, NULL, 'Offer 26', 'percent', 22.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(27, 31, NULL, 'Offer 27', 'percent', 35.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(28, 30, NULL, 'Offer 28', 'percent', 40.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(29, 25, NULL, 'Offer 29', 'percent', 17.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(30, 33, NULL, 'Offer 30', 'percent', 13.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(31, 18, NULL, 'Offer 31', 'percent', 31.00, '2026-01-01', '2026-12-31', 'active', 3, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(32, 33, NULL, 'Offer 32', 'percent', 22.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(33, 19, NULL, 'Offer 33', 'percent', 24.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(34, 38, NULL, 'Offer 34', 'percent', 36.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(35, 29, NULL, 'Offer 35', 'percent', 39.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(36, 23, NULL, 'Offer 36', 'percent', 26.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(38, 30, NULL, 'Offer 38', 'percent', 25.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(39, 45, NULL, 'Offer 39', 'percent', 20.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(40, 25, NULL, 'Offer 40', 'percent', 19.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(41, 3, NULL, 'Offer 41', 'percent', 25.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(42, 46, NULL, 'Offer 42', 'percent', 29.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(43, 43, NULL, 'Offer 43', 'percent', 14.00, '2026-01-01', '2026-12-31', 'active', 4, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(44, 3, NULL, 'Offer 44', 'percent', 13.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(45, 38, NULL, 'Offer 45', 'percent', 26.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(46, 29, NULL, 'Offer 46', 'percent', 11.00, '2026-01-01', '2026-12-31', 'active', 5, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(47, 30, NULL, 'Offer 47', 'percent', 5.00, '2026-01-01', '2026-12-31', 'active', 2, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(48, 27, NULL, 'Offer 48', 'percent', 14.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(49, 31, NULL, 'Offer 49', 'percent', 21.00, '2026-01-01', '2026-12-31', 'active', 3, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(50, 40, NULL, 'Offer 50', 'percent', 30.00, '2026-01-01', '2026-12-31', 'active', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(52, 49, NULL, 'Offer 37', 'percent', 39.00, NULL, NULL, 'active', 0, '2026-05-28 00:12:49', '2026-05-28 00:12:49');

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
  `admin_remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `delivery_charge`, `tax_amount`, `grand_total`, `status`, `payment_status`, `delivery_address`, `pincode`, `delivery_eta`, `assigned_delivery_id`, `notes`, `created_at`, `updated_at`, `tracking_status`, `admin_remark`) VALUES
(1, 13, 'ORD00001', 2461.00, 10.00, 123.00, 2594.00, 'delivered', 'paid', 'Delivery Address 1', '700011', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(2, 46, 'ORD00002', 281.00, 10.00, 14.00, 305.00, 'delivered', 'paid', 'Delivery Address 2', '700021', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(3, 34, 'ORD00003', 106.00, 10.00, 5.00, 121.00, 'out_for_delivery', 'paid', 'Delivery Address 3', '700031', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(4, 47, 'ORD00004', 2913.00, 10.00, 145.00, 3068.00, 'out_for_delivery', 'paid', 'Delivery Address 4', '700041', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(5, 13, 'ORD00005', 2846.00, 10.00, 142.00, 2998.00, 'delivered', 'paid', 'Delivery Address 5', '700051', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(6, 5, 'ORD00006', 1866.00, 10.00, 93.00, 1969.00, 'out_for_delivery', 'paid', 'Delivery Address 6', '700061', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(7, 40, 'ORD00007', 1452.00, 10.00, 72.00, 1534.00, 'delivered', 'paid', 'Delivery Address 7', '700071', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(8, 8, 'ORD00008', 2817.00, 10.00, 140.00, 2967.00, 'out_for_delivery', 'paid', 'Delivery Address 8', '700081', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(9, 33, 'ORD00009', 1330.00, 10.00, 66.00, 1406.00, 'delivered', 'paid', 'Delivery Address 9', '700091', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(10, 27, 'ORD00010', 2831.00, 10.00, 141.00, 2982.00, 'delivered', 'paid', 'Delivery Address 10', '700001', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(11, 45, 'ORD00011', 1748.00, 10.00, 87.00, 1845.00, 'delivered', 'paid', 'Delivery Address 11', '700011', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(12, 9, 'ORD00012', 2370.00, 10.00, 118.00, 2498.00, '', 'paid', 'Delivery Address 12', '700021', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(13, 43, 'ORD00013', 1822.00, 10.00, 91.00, 1923.00, 'delivered', 'paid', 'Delivery Address 13', '700031', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(14, 48, 'ORD00014', 2874.00, 10.00, 143.00, 3027.00, '', 'paid', 'Delivery Address 14', '700041', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(15, 37, 'ORD00015', 2621.00, 10.00, 131.00, 2762.00, 'delivered', 'paid', 'Delivery Address 15', '700051', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(16, 36, 'ORD00016', 1763.00, 10.00, 88.00, 1861.00, '', 'paid', 'Delivery Address 16', '700061', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(17, 19, 'ORD00017', 1344.00, 10.00, 67.00, 1421.00, '', 'paid', 'Delivery Address 17', '700071', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(18, 38, 'ORD00018', 1860.00, 10.00, 93.00, 1963.00, 'out_for_delivery', 'paid', 'Delivery Address 18', '700081', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(19, 21, 'ORD00019', 2781.00, 10.00, 139.00, 2930.00, 'delivered', 'paid', 'Delivery Address 19', '700091', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(20, 29, 'ORD00020', 1909.00, 10.00, 95.00, 2014.00, 'out_for_delivery', 'paid', 'Delivery Address 20', '700001', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(21, 33, 'ORD00021', 975.00, 10.00, 48.00, 1033.00, 'delivered', 'paid', 'Delivery Address 21', '700011', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(22, 43, 'ORD00022', 795.00, 10.00, 39.00, 844.00, '', 'paid', 'Delivery Address 22', '700021', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(23, 33, 'ORD00023', 1262.00, 10.00, 63.00, 1335.00, 'out_for_delivery', 'paid', 'Delivery Address 23', '700031', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(24, 40, 'ORD00024', 2692.00, 10.00, 134.00, 2836.00, 'delivered', 'paid', 'Delivery Address 24', '700041', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(25, 49, 'ORD00025', 482.00, 10.00, 24.00, 516.00, '', 'paid', 'Delivery Address 25', '700051', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(26, 20, 'ORD00026', 2855.00, 10.00, 142.00, 3007.00, '', 'paid', 'Delivery Address 26', '700061', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(27, 10, 'ORD00027', 915.00, 10.00, 45.00, 970.00, '', 'paid', 'Delivery Address 27', '700071', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(28, 16, 'ORD00028', 289.00, 10.00, 14.00, 313.00, 'delivered', 'paid', 'Delivery Address 28', '700081', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(29, 50, 'ORD00029', 2603.00, 10.00, 130.00, 2743.00, '', 'paid', 'Delivery Address 29', '700091', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(30, 27, 'ORD00030', 1965.00, 10.00, 98.00, 2073.00, 'out_for_delivery', 'paid', 'Delivery Address 30', '700001', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(31, 13, 'ORD00031', 2457.00, 10.00, 122.00, 2589.00, 'out_for_delivery', 'paid', 'Delivery Address 31', '700011', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(32, 25, 'ORD00032', 2952.00, 10.00, 147.00, 3109.00, 'delivered', 'paid', 'Delivery Address 32', '700021', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(33, 16, 'ORD00033', 1736.00, 10.00, 86.00, 1832.00, '', 'paid', 'Delivery Address 33', '700031', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(34, 45, 'ORD00034', 2787.00, 10.00, 139.00, 2936.00, '', 'paid', 'Delivery Address 34', '700041', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(35, 50, 'ORD00035', 536.00, 10.00, 26.00, 572.00, 'delivered', 'paid', 'Delivery Address 35', '700051', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(36, 12, 'ORD00036', 996.00, 10.00, 49.00, 1055.00, 'out_for_delivery', 'paid', 'Delivery Address 36', '700061', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(37, 30, 'ORD00037', 2221.00, 10.00, 111.00, 2342.00, '', 'paid', 'Delivery Address 37', '700071', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(38, 16, 'ORD00038', 2383.00, 10.00, 119.00, 2512.00, '', 'paid', 'Delivery Address 38', '700081', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(39, 9, 'ORD00039', 1969.00, 10.00, 98.00, 2077.00, 'delivered', 'paid', 'Delivery Address 39', '700091', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(40, 34, 'ORD00040', 2834.00, 10.00, 141.00, 2985.00, 'out_for_delivery', 'paid', 'Delivery Address 40', '700001', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(41, 21, 'ORD00041', 2538.00, 10.00, 126.00, 2674.00, 'delivered', 'paid', 'Delivery Address 41', '700011', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(42, 47, 'ORD00042', 2609.00, 10.00, 130.00, 2749.00, 'out_for_delivery', 'paid', 'Delivery Address 42', '700021', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(43, 36, 'ORD00043', 1847.00, 10.00, 92.00, 1949.00, 'delivered', 'paid', 'Delivery Address 43', '700031', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(44, 48, 'ORD00044', 751.00, 10.00, 37.00, 798.00, 'delivered', 'paid', 'Delivery Address 44', '700041', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(45, 17, 'ORD00045', 1943.00, 10.00, 97.00, 2050.00, '', 'paid', 'Delivery Address 45', '700051', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(46, 18, 'ORD00046', 2711.00, 10.00, 135.00, 2856.00, 'out_for_delivery', 'paid', 'Delivery Address 46', '700061', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(47, 41, 'ORD00047', 2084.00, 10.00, 104.00, 2198.00, '', 'paid', 'Delivery Address 47', '700071', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(48, 29, 'ORD00048', 1224.00, 10.00, 61.00, 1295.00, '', 'paid', 'Delivery Address 48', '700081', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(49, 16, 'ORD00049', 1270.00, 10.00, 63.00, 1343.00, 'delivered', 'paid', 'Delivery Address 49', '700091', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(50, 21, 'ORD00050', 1475.00, 10.00, 73.00, 1558.00, 'out_for_delivery', 'paid', 'Delivery Address 50', '700001', NULL, NULL, NULL, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 'Order Placed', NULL),
(51, 144, 'NEX202605247436', 1326.00, 50.00, 238.68, 1614.68, 'confirmed', 'pending', 'Sribash Sarkar • 9083646603, sareyar par manasa mandir, sareyar par, Landmark: temple, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-05-24 08:09:46', '2026-05-24 08:32:27', 'ordered', ''),
(52, 21, 'NEX202605246782', 748.00, 50.00, 134.64, 932.64, 'out_for_delivery', 'pending', 'Siliguri', '000000', NULL, NULL, NULL, '2026-05-24 08:25:57', '2026-05-24 08:50:45', 'Order Placed', NULL),
(53, 8, 'NEX202605241841', 1093.00, 50.00, 196.74, 1339.74, 'pending', 'pending', 'Siliguri', '000000', NULL, NULL, NULL, '2026-05-24 08:29:24', '2026-05-24 08:29:24', 'Order Placed', NULL),
(54, 144, 'NEX202605249584', 968.00, 50.00, 174.24, 1192.24, 'confirmed', 'pending', 'Sribash Sarkar • 9083646603, sareyar par manasa mandir, sareyar par, Landmark: temple, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-05-24 08:34:52', '2026-05-24 08:36:02', 'ordered', ''),
(55, 144, 'NEX202605241003', 968.00, 50.00, 174.24, 1192.24, 'pending', 'pending', 'Sribash Sarkar • 9083646603, sareyar par manasa mandir, sareyar par, Landmark: temple, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-05-24 08:37:01', '2026-05-24 08:37:01', 'Order Placed', NULL),
(56, 144, 'NEX202605243863', 968.00, 50.00, 174.24, 1192.24, 'out_for_delivery', 'pending', 'Sribash Sarkar • 9083646603, sareyar par manasa mandir, sareyar par, Landmark: temple, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-05-24 08:45:26', '2026-05-24 08:51:02', 'ordered', ''),
(57, 144, 'NEX202605248843', 1326.00, 50.00, 238.68, 1614.68, 'pending', 'pending', 'Sribash Sarkar • 9083646603, sareyar par manasa mandir, sareyar par, Landmark: temple, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-05-24 08:54:44', '2026-05-24 08:54:44', 'Order Placed', NULL),
(58, 144, 'NEX202605243462', 1326.00, 50.00, 238.68, 1614.68, 'pending', 'pending', 'Sribash Sarkar • 9083646603, sareyar par manasa mandir, sareyar par, Landmark: temple, West Bengal, 736134', '000000', NULL, NULL, NULL, '2026-05-24 08:55:59', '2026-05-24 08:55:59', 'Order Placed', NULL),
(59, 145, 'NEX202605266725', 1452.00, 50.00, 261.36, 1763.36, 'pending', 'pending', 'Union Square/Market Street, 1-99 Stockton St, Union Square, San Francisco, San Francisco County, CA, 94108, United States', '000000', NULL, NULL, NULL, '2026-05-26 16:16:45', '2026-05-26 16:16:45', 'Order Placed', NULL),
(60, 145, 'NEX202605282561', 1798.00, 50.00, 323.64, 2171.64, 'cancelled', 'pending', 'Union Square/Market Street, 1-99 Stockton St, Union Square, San Francisco, San Francisco County, CA, 94108, United States', '000000', NULL, NULL, NULL, '2026-05-28 15:01:33', '2026-05-28 15:01:48', 'Order Placed', NULL),
(61, 144, 'NEX202605293567', 4450.00, 50.00, 801.00, 5301.00, 'pending', 'pending', '1600, 1600 Amphitheatre Pkwy, Mountain View, Santa Clara County, California, 94043, United States', '0', '00:00:01', 6, NULL, '2026-05-29 15:52:22', '2026-05-29 16:09:20', 'Order Placed', 'L,L,L,L,L,L,L,L,L,L,L,L,LL,L,L,L,L,L,L,44'),
(62, 146, 'NEX202606026603', 1798.00, 50.00, 323.64, 2171.64, 'cancelled', 'pending', '8C4X+QX9, Cooch Behar, Jalpaiguri Division, West Bengal, 736101, India', '000000', NULL, NULL, NULL, '2026-06-02 07:49:53', '2026-06-04 17:57:05', 'Order Placed', NULL),
(63, 144, 'NEX202606024733', 5694.00, 50.00, 1024.92, 6768.92, 'pending', 'pending', 'Sayan Banik • 8768412832, BJ 214, Near BJ Ground, West Bengal, 736156', '000000', NULL, NULL, NULL, '2026-06-02 08:03:11', '2026-06-02 08:03:11', 'Order Placed', NULL),
(64, 147, 'NEX202606037488', 11599.00, 50.00, 2087.82, 13736.82, 'pending', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-06-03 14:57:53', '2026-06-03 15:34:23', 'ordered', 'gudurudwrsujdys'),
(65, 146, 'NEX202606033637', 2040.00, 50.00, 367.20, 2457.20, 'cancelled', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-06-03 15:22:25', '2026-06-04 17:57:11', 'Order Placed', NULL),
(66, 146, 'NEX202606045779', 26071.00, 50.00, 4692.78, 30813.78, 'cancelled', 'pending', '6JJ5+6M3, Balarampur, Jalpaiguri Division, West Bengal, 736134, India', '000000', NULL, NULL, NULL, '2026-06-04 17:56:41', '2026-06-04 17:56:59', 'Order Placed', NULL);

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
(1, 6, 9, 2, 256.00),
(2, 25, 45, 2, 743.00),
(3, 14, 5, 4, 437.00),
(4, 22, 35, 4, 445.00),
(5, 4, 14, 4, 418.00),
(6, 50, 38, 1, 609.00),
(7, 25, 31, 1, 380.00),
(8, 20, 49, 4, 449.00),
(9, 35, 48, 2, 519.00),
(10, 15, 18, 4, 517.00),
(11, 2, 25, 3, 704.00),
(12, 44, 26, 2, 498.00),
(13, 9, 40, 1, 423.00),
(14, 38, 37, 1, 105.00),
(15, 42, 28, 2, 492.00),
(16, 12, 4, 3, 408.00),
(17, 21, 14, 4, 354.00),
(18, 22, 49, 4, 304.00),
(19, 49, 27, 3, 103.00),
(20, 31, 2, 1, 378.00),
(21, 15, 42, 1, 687.00),
(22, 3, 49, 1, 273.00),
(23, 13, 2, 2, 264.00),
(24, 9, 31, 1, 597.00),
(25, 14, 30, 3, 397.00),
(26, 11, 39, 1, 187.00),
(27, 20, 7, 1, 339.00),
(28, 37, 44, 4, 426.00),
(29, 46, 13, 1, 626.00),
(30, 45, 41, 2, 124.00),
(31, 45, 50, 3, 720.00),
(32, 39, 8, 1, 375.00),
(33, 35, 28, 3, 90.00),
(34, 33, 42, 3, 32.00),
(35, 27, 32, 1, 463.00),
(36, 24, 41, 4, 744.00),
(37, 10, 28, 2, 771.00),
(38, 34, 42, 3, 650.00),
(39, 35, 50, 4, 496.00),
(40, 28, 47, 3, 350.00),
(41, 16, 6, 3, 481.00),
(42, 16, 49, 4, 603.00),
(43, 40, 43, 4, 364.00),
(44, 2, 32, 3, 206.00),
(45, 32, 14, 3, 284.00),
(46, 22, 18, 3, 589.00),
(47, 1, 34, 2, 107.00),
(48, 16, 47, 4, 520.00),
(49, 36, 49, 2, 727.00),
(50, 31, 42, 4, 478.00),
(51, 51, 1, 2, 663.00),
(52, 52, 17, 2, 362.00),
(53, 52, 49, 1, 24.00),
(54, 53, 5, 2, 299.00),
(55, 53, 7, 5, 99.00),
(56, 54, 4, 2, 484.00),
(57, 55, 4, 2, 484.00),
(58, 56, 4, 2, 484.00),
(59, 57, 1, 2, 663.00),
(60, 58, 1, 2, 663.00),
(61, 59, 4, 3, 484.00),
(62, 60, 1, 2, 899.00),
(63, 61, 1, 2, 899.00),
(64, 61, 2, 2, 741.00),
(65, 61, 3, 2, 101.00),
(66, 61, 4, 2, 484.00),
(67, 62, 1, 2, 899.00),
(68, 63, 1, 3, 899.00),
(69, 63, 51, 3, 999.00),
(70, 64, 2, 15, 741.00),
(71, 64, 49, 2, 242.00),
(72, 65, 1, 2, 899.00),
(73, 65, 49, 1, 242.00),
(74, 66, 1, 29, 899.00);

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

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `title`, `slug`, `type`, `short_description`, `content`, `status`, `visibility`, `is_featured`, `display_order`, `meta_title`, `meta_keywords`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 'Policy 1', 'policy-1', '', 'Short desc 1', 'Policy content 1', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(2, 'Policy 2', 'policy-2', '', 'Short desc 2', 'Policy content 2', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(3, 'Policy 3', 'policy-3', '', 'Short desc 3', 'Policy content 3', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(4, 'Policy 4', 'policy-4', '', 'Short desc 4', 'Policy content 4', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(5, 'Policy 5', 'policy-5', '', 'Short desc 5', 'Policy content 5', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(6, 'Policy 6', 'policy-6', '', 'Short desc 6', 'Policy content 6', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(7, 'Policy 7', 'policy-7', '', 'Short desc 7', 'Policy content 7', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(8, 'Policy 8', 'policy-8', '', 'Short desc 8', 'Policy content 8', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(9, 'Policy 9', 'policy-9', '', 'Short desc 9', 'Policy content 9', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(10, 'Policy 10', 'policy-10', '', 'Short desc 10', 'Policy content 10', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(11, 'Policy 11', 'policy-11', '', 'Short desc 11', 'Policy content 11', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(12, 'Policy 12', 'policy-12', '', 'Short desc 12', 'Policy content 12', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(13, 'Policy 13', 'policy-13', '', 'Short desc 13', 'Policy content 13', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(14, 'Policy 14', 'policy-14', '', 'Short desc 14', 'Policy content 14', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(15, 'Policy 15', 'policy-15', '', 'Short desc 15', 'Policy content 15', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(16, 'Policy 16', 'policy-16', '', 'Short desc 16', 'Policy content 16', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(17, 'Policy 17', 'policy-17', '', 'Short desc 17', 'Policy content 17', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(18, 'Policy 18', 'policy-18', '', 'Short desc 18', 'Policy content 18', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(19, 'Policy 19', 'policy-19', '', 'Short desc 19', 'Policy content 19', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(20, 'Policy 20', 'policy-20', '', 'Short desc 20', 'Policy content 20', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(21, 'Policy 21', 'policy-21', '', 'Short desc 21', 'Policy content 21', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(22, 'Policy 22', 'policy-22', '', 'Short desc 22', 'Policy content 22', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(23, 'Policy 23', 'policy-23', '', 'Short desc 23', 'Policy content 23', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(24, 'Policy 24', 'policy-24', '', 'Short desc 24', 'Policy content 24', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(25, 'Policy 25', 'policy-25', '', 'Short desc 25', 'Policy content 25', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(26, 'Policy 26', 'policy-26', '', 'Short desc 26', 'Policy content 26', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(27, 'Policy 27', 'policy-27', '', 'Short desc 27', 'Policy content 27', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(28, 'Policy 28', 'policy-28', '', 'Short desc 28', 'Policy content 28', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(29, 'Policy 29', 'policy-29', '', 'Short desc 29', 'Policy content 29', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(30, 'Policy 30', 'policy-30', '', 'Short desc 30', 'Policy content 30', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(31, 'Policy 31', 'policy-31', '', 'Short desc 31', 'Policy content 31', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(32, 'Policy 32', 'policy-32', '', 'Short desc 32', 'Policy content 32', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(33, 'Policy 33', 'policy-33', '', 'Short desc 33', 'Policy content 33', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(34, 'Policy 34', 'policy-34', '', 'Short desc 34', 'Policy content 34', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(35, 'Policy 35', 'policy-35', '', 'Short desc 35', 'Policy content 35', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(36, 'Policy 36', 'policy-36', '', 'Short desc 36', 'Policy content 36', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(37, 'Policy 37', 'policy-37', '', 'Short desc 37', 'Policy content 37', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(38, 'Policy 38', 'policy-38', '', 'Short desc 38', 'Policy content 38', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(39, 'Policy 39', 'policy-39', '', 'Short desc 39', 'Policy content 39', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(40, 'Policy 40', 'policy-40', '', 'Short desc 40', 'Policy content 40', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(41, 'Policy 41', 'policy-41', '', 'Short desc 41', 'Policy content 41', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(42, 'Policy 42', 'policy-42', '', 'Short desc 42', 'Policy content 42', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(43, 'Policy 43', 'policy-43', '', 'Short desc 43', 'Policy content 43', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(44, 'Policy 44', 'policy-44', '', 'Short desc 44', 'Policy content 44', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(45, 'Policy 45', 'policy-45', '', 'Short desc 45', 'Policy content 45', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(46, 'Policy 46', 'policy-46', '', 'Short desc 46', 'Policy content 46', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(47, 'Policy 47', 'policy-47', '', 'Short desc 47', 'Policy content 47', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(48, 'Policy 48', 'policy-48', '', 'Short desc 48', 'Policy content 48', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(49, 'Policy 49', 'policy-49', '', 'Short desc 49', 'Policy content 49', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(50, 'Policy 50', 'policy-50', '', 'Short desc 50', 'Policy content 50', 'published', 'public', 0, 0, NULL, NULL, NULL, '2026-05-24 08:01:58', '2026-05-24 08:01:58'),
(51, 'Privacy Policy', 'privacy-policy', '', 'Learn how we collect, use, store, and protect your personal information while using our platform and services.', '<h2>Privacy Policy</h2>\n\n<p>Welcome to our platform. Your privacy is important to us, and we are committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website, mobile application, and related services.</p>\n\n<h3>1. Information We Collect</h3>\n<p>We may collect personal information including your name, email address, phone number, billing address, payment information, and account credentials. We may also collect device information, IP address, browser type, and usage data.</p>\n\n<h3>2. How We Use Your Information</h3>\n<p>Your information may be used to:</p>\n<ul>\n<li>Create and manage your account</li>\n<li>Process orders and payments</li>\n<li>Provide customer support</li>\n<li>Improve our services and user experience</li>\n<li>Send important notifications and updates</li>\n<li>Prevent fraud and enhance security</li>\n</ul>\n\n<h3>3. Data Protection</h3>\n<p>We implement industry-standard security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>\n\n<h3>4. Cookies</h3>\n<p>We use cookies and similar technologies to improve functionality, analyze traffic, and personalize your experience.</p>\n\n<h3>5. Third-Party Services</h3>\n<p>We may share information with trusted third-party service providers such as payment gateways, analytics providers, and hosting partners for operational purposes.</p>\n\n<h3>6. User Rights</h3>\n<p>You have the right to access, update, or delete your personal information. You may also request limitation of processing or object to certain uses of your data.</p>\n\n<h3>7. Changes to This Policy</h3>\n<p>We reserve the right to update this Privacy Policy at any time. Changes will be posted on this page with an updated revision date.</p>\n\n<h3>8. Contact Us</h3>\n<p>If you have any questions regarding this Privacy Policy, please contact our support team.</p>', '', 'public', 1, 1, 'Privacy Policy | Secure User Data Protection', 'privacy policy, user data, data protection, security, cookies, personal information', 'Read our professional privacy policy to understand how user data is collected, stored, protected, and managed securely.', '2026-05-25 19:14:12', '2026-05-25 19:17:30');

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
(1, 'Nestle Product 1', 'nestle-product-1', 'Quick commerce grocery item 1', 899.00, 623.00, 'SKU0001', 473, 24, '[\"product1.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 11:43:15', 315, '{\"brand\":\"Nestle\"}'),
(2, 'Britannia Product 2', 'britannia-product-2', 'Quick commerce grocery item 2', 741.00, 736.00, 'SKU0002', 43, 43, '[\"product2.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 136, '{\"brand\":\"Britannia\"}'),
(3, 'Nestle Product 3', 'nestle-product-3', 'Quick commerce grocery item 3', 101.00, 86.00, 'SKU0003', 463, 7, '[\"product3.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 214, '{\"brand\":\"Nestle\"}'),
(4, 'Nestle Product 4', 'nestle-product-4', 'Quick commerce grocery item 4', 484.00, 443.00, 'SKU0004', 447, 24, '[\"product4.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 103, '{\"brand\":\"Nestle\"}'),
(5, 'Nestle Product 5', 'nestle-product-5', 'Quick commerce grocery item 5', 383.00, 369.00, 'SKU0005', 363, 18, '[\"product5.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 379, '{\"brand\":\"Nestle\"}'),
(6, 'Amul Product 6', 'amul-product-6', 'Quick commerce grocery item 6', 643.00, 602.00, 'SKU0006', 107, 35, '[\"product6.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 393, '{\"brand\":\"Amul\"}'),
(7, 'Britannia Product 7', 'britannia-product-7', 'Quick commerce grocery item 7', 187.00, 157.00, 'SKU0007', 214, 18, '[\"product7.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 493, '{\"brand\":\"Britannia\"}'),
(8, 'Coca Cola Product 8', 'coca cola-product-8', 'Quick commerce grocery item 8', 244.00, 200.00, 'SKU0008', 186, 50, '[\"product8.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 417, '{\"brand\":\"Coca Cola\"}'),
(9, 'Amul Product 9', 'amul-product-9', 'Quick commerce grocery item 9', 254.00, 251.00, 'SKU0009', 432, 21, '[\"product9.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 225, '{\"brand\":\"Amul\"}'),
(10, 'Nestle Product 10', 'nestle-product-10', 'Quick commerce grocery item 10', 87.00, 73.00, 'SKU0010', 487, 37, '[\"product10.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 468, '{\"brand\":\"Nestle\"}'),
(11, 'Nestle Product 11', 'nestle-product-11', 'Quick commerce grocery item 11', 237.00, 195.00, 'SKU0011', 275, 26, '[\"product11.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 472, '{\"brand\":\"Nestle\"}'),
(12, 'Pepsi Product 12', 'pepsi-product-12', 'Quick commerce grocery item 12', 166.00, 149.00, 'SKU0012', 91, 16, '[\"product12.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 401, '{\"brand\":\"Pepsi\"}'),
(13, 'Coca Cola Product 13', 'coca cola-product-13', 'Quick commerce grocery item 13', 571.00, 554.00, 'SKU0013', 402, 38, '[\"product13.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 239, '{\"brand\":\"Coca Cola\"}'),
(14, 'Coca Cola Product 14', 'coca cola-product-14', 'Quick commerce grocery item 14', 428.00, 404.00, 'SKU0014', 132, 9, '[\"product14.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 280, '{\"brand\":\"Coca Cola\"}'),
(15, 'Pepsi Product 15', 'pepsi-product-15', 'Quick commerce grocery item 15', 113.00, 64.00, 'SKU0015', 44, 8, '[\"product15.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 98, '{\"brand\":\"Pepsi\"}'),
(16, 'Britannia Product 16', 'britannia-product-16', 'Quick commerce grocery item 16', 716.00, 688.00, 'SKU0016', 325, 5, '[\"product16.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 217, '{\"brand\":\"Britannia\"}'),
(17, 'Pepsi Product 17', 'pepsi-product-17', 'Quick commerce grocery item 17', 630.00, 600.00, 'SKU0017', 290, 17, '[\"product17.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 303, '{\"brand\":\"Pepsi\"}'),
(18, 'Amul Product 18', 'amul-product-18', 'Quick commerce grocery item 18', 716.00, 669.00, 'SKU0018', 78, 44, '[\"product18.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 473, '{\"brand\":\"Amul\"}'),
(19, 'Coca Cola Product 19', 'coca cola-product-19', 'Quick commerce grocery item 19', 788.00, 770.00, 'SKU0019', 413, 42, '[\"product19.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 194, '{\"brand\":\"Coca Cola\"}'),
(20, 'Amul Product 20', 'amul-product-20', 'Quick commerce grocery item 20', 320.00, 292.00, 'SKU0020', 100, 30, '[\"product20.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 21, '{\"brand\":\"Amul\"}'),
(21, 'Nestle Product 21', 'nestle-product-21', 'Quick commerce grocery item 21', 532.00, 483.00, 'SKU0021', 111, 33, '[\"product21.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 487, '{\"brand\":\"Nestle\"}'),
(22, 'Amul Product 22', 'amul-product-22', 'Quick commerce grocery item 22', 660.00, 640.00, 'SKU0022', 450, 41, '[\"product22.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 279, '{\"brand\":\"Amul\"}'),
(23, 'Coca Cola Product 23', 'coca cola-product-23', 'Quick commerce grocery item 23', 223.00, 213.00, 'SKU0023', 211, 49, '[\"product23.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 102, '{\"brand\":\"Coca Cola\"}'),
(24, 'Coca Cola Product 24', 'coca cola-product-24', 'Quick commerce grocery item 24', 563.00, 562.00, 'SKU0024', 326, 21, '[\"product24.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 270, '{\"brand\":\"Coca Cola\"}'),
(25, 'Amul Product 25', 'amul-product-25', 'Quick commerce grocery item 25', 134.00, 110.00, 'SKU0025', 469, 20, '[\"product25.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 142, '{\"brand\":\"Amul\"}'),
(26, 'Amul Product 26', 'amul-product-26', 'Quick commerce grocery item 26', 266.00, 229.00, 'SKU0026', 60, 6, '[\"product26.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 394, '{\"brand\":\"Amul\"}'),
(27, 'Pepsi Product 27', 'pepsi-product-27', 'Quick commerce grocery item 27', 90.00, 41.00, 'SKU0027', 292, 50, '[\"product27.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 84, '{\"brand\":\"Pepsi\"}'),
(28, 'Britannia Product 28', 'britannia-product-28', 'Quick commerce grocery item 28', 695.00, 664.00, 'SKU0028', 301, 11, '[\"product28.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 155, '{\"brand\":\"Britannia\"}'),
(29, 'Coca Cola Product 29', 'coca cola-product-29', 'Quick commerce grocery item 29', 641.00, 613.00, 'SKU0029', 128, 35, '[\"product29.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 406, '{\"brand\":\"Coca Cola\"}'),
(30, 'Britannia Product 30', 'britannia-product-30', 'Quick commerce grocery item 30', 750.00, 730.00, 'SKU0030', 224, 43, '[\"product30.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 352, '{\"brand\":\"Britannia\"}'),
(31, 'Nestle Product 31', 'nestle-product-31', 'Quick commerce grocery item 31', 468.00, 434.00, 'SKU0031', 251, 8, '[\"product31.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 146, '{\"brand\":\"Nestle\"}'),
(32, 'Britannia Product 32', 'britannia-product-32', 'Quick commerce grocery item 32', 85.00, 63.00, 'SKU0032', 30, 38, '[\"product32.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 303, '{\"brand\":\"Britannia\"}'),
(33, 'Britannia Product 33', 'britannia-product-33', 'Quick commerce grocery item 33', 622.00, 607.00, 'SKU0033', 23, 5, '[\"product33.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 382, '{\"brand\":\"Britannia\"}'),
(34, 'Amul Product 34', 'amul-product-34', 'Quick commerce grocery item 34', 254.00, 249.00, 'SKU0034', 483, 3, '[\"product34.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 460, '{\"brand\":\"Amul\"}'),
(35, 'Nestle Product 35', 'nestle-product-35', 'Quick commerce grocery item 35', 92.00, 59.00, 'SKU0035', 141, 18, '[\"product35.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 362, '{\"brand\":\"Nestle\"}'),
(36, 'Pepsi Product 36', 'pepsi-product-36', 'Quick commerce grocery item 36', 239.00, 204.00, 'SKU0036', 87, 47, '[\"product36.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 498, '{\"brand\":\"Pepsi\"}'),
(37, 'Coca Cola Product 37', 'coca cola-product-37', 'Quick commerce grocery item 37', 610.00, 579.00, 'SKU0037', 144, 31, '[\"product37.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 433, '{\"brand\":\"Coca Cola\"}'),
(38, 'Pepsi Product 38', 'pepsi-product-38', 'Quick commerce grocery item 38', 214.00, 207.00, 'SKU0038', 69, 43, '[\"product38.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 240, '{\"brand\":\"Pepsi\"}'),
(39, 'Nestle Product 39', 'nestle-product-39', 'Quick commerce grocery item 39', 453.00, 426.00, 'SKU0039', 259, 47, '[\"product39.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 47, '{\"brand\":\"Nestle\"}'),
(40, 'Amul Product 40', 'amul-product-40', 'Quick commerce grocery item 40', 82.00, 56.00, 'SKU0040', 392, 22, '[\"product40.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 429, '{\"brand\":\"Amul\"}'),
(41, 'Amul Product 41', 'amul-product-41', 'Quick commerce grocery item 41', 274.00, 261.00, 'SKU0041', 117, 35, '[\"product41.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 249, '{\"brand\":\"Amul\"}'),
(42, 'Britannia Product 42', 'britannia-product-42', 'Quick commerce grocery item 42', 452.00, 440.00, 'SKU0042', 162, 30, '[\"product42.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 147, '{\"brand\":\"Britannia\"}'),
(43, 'Amul Product 43', 'amul-product-43', 'Quick commerce grocery item 43', 473.00, 437.00, 'SKU0043', 70, 4, '[\"product43.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 353, '{\"brand\":\"Amul\"}'),
(44, 'Coca Cola Product 44', 'coca cola-product-44', 'Quick commerce grocery item 44', 35.00, 29.00, 'SKU0044', 494, 49, '[\"product44.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 454, '{\"brand\":\"Coca Cola\"}'),
(45, 'Britannia Product 45', 'britannia-product-45', 'Quick commerce grocery item 45', 190.00, 163.00, 'SKU0045', 268, 31, '[\"product45.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 129, '{\"brand\":\"Britannia\"}'),
(46, 'Pepsi Product 46', 'pepsi-product-46', 'Quick commerce grocery item 46', 80.00, 69.00, 'SKU0046', 214, 1, '[\"product46.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 219, '{\"brand\":\"Pepsi\"}'),
(47, 'Nestle Product 47', 'nestle-product-47', 'Quick commerce grocery item 47', 485.00, 466.00, 'SKU0047', 236, 45, '[\"product47.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 394, '{\"brand\":\"Nestle\"}'),
(48, 'Coca Cola Product 48', 'coca cola-product-48', 'Quick commerce grocery item 48', 697.00, 651.00, 'SKU0048', 269, 10, '[\"product48.jpg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', 117, '{\"brand\":\"Coca Cola\"}'),
(49, 'sssssssss', 'nestle-product-49', 'Quick commerce grocery item 49', 242.00, 238.00, 'SKU0049', 316, 48, '[\"product49.jpg\",\"1779926975_6056.jpeg\",\"1779927169_1256.png\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-28 00:12:49', 0, NULL),
(50, 'sribash', 'amul-product-50', 'Quick commerce grocery item 50', 785.00, 764.00, 'SKU0050', 49, 4, '[\"product50.jpg\",\"1779926779_3507.jpg\",\"1779926779_8804.jpg\",\"1779926779_3858.jpeg\",\"1779926942_3535.jpg\",\"1779926942_3955.jpg\",\"1779926942_2305.jpeg\",\"1779926944_6743.jpg\",\"1779926944_3070.jpg\",\"1779926944_4966.jpeg\",\"1779926946_1919.jpg\",\"1779926946_6138.jpg\",\"1779926946_7836.jpeg\",\"1779926948_1926.jpg\",\"1779926948_2658.jpg\",\"1779926948_8189.jpeg\"]', 1.000, 1, '2026-05-24 07:59:49', '2026-05-28 00:09:08', 0, NULL),
(51, 'sayanp1', 'demo-product-1779623002', '', 999.00, NULL, 'SKU-1779623002', 10, 1, '[\"1779809466_2789.jpg\",\"1779809466_8297.jpg\",\"1779809466_4367.jpg\",\"1779809466_6831.webp\",\"1779809702_3099.jpg\",\"1779809808_1526.jpg\",\"1779926175_4895.jpeg\",\"1779926175_3252.jpg\",\"1779926175_2098.jpeg\",\"1779926175_9237.jpeg\",\"1779926175_5709.webp\",\"1779926233_3119.jpeg\",\"1779926233_4477.jpg\",\"1779926233_1994.jpeg\",\"1779926233_4484.jpeg\",\"1779926233_5285.webp\",\"1779926237_9316.jpeg\",\"1779926237_4555.jpg\",\"1779926237_7449.jpeg\",\"1779926237_7477.jpeg\",\"1779926237_9960.webp\",\"1779926276_6569.jpeg\",\"1779926276_1169.jpg\",\"1779926276_1813.jpeg\",\"1779926743_4977.jpeg\",\"1779926743_8976.jpg\",\"1779926743_8432.jpeg\"]', NULL, 1, '2026-05-24 11:43:22', '2026-05-28 00:05:43', 0, NULL);

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
(1, 2, 6, 4, 'Review 1', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(2, 15, 26, 5, 'Review 2', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(3, 16, 20, 5, 'Review 3', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(4, 38, 24, 4, 'Review 4', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(5, 36, 34, 4, 'Review 5', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(6, 28, 48, 5, 'Review 6', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(7, 22, 23, 5, 'Review 7', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(8, 30, 18, 4, 'Review 8', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(9, 17, 15, 3, 'Review 9', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(10, 47, 13, 4, 'Review 10', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(11, 8, 48, 5, 'Review 11', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(12, 49, 45, 3, 'Review 12', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(13, 13, 14, 5, 'Review 13', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(14, 31, 18, 5, 'Review 14', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(15, 38, 49, 5, 'Review 15', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(16, 39, 19, 3, 'Review 16', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(17, 13, 19, 3, 'Review 17', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(18, 24, 12, 4, 'Review 18', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(19, 1, 46, 5, 'Review 19', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(20, 9, 18, 3, 'Review 20', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(21, 4, 36, 4, 'Review 21', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(22, 45, 9, 5, 'Review 22', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(23, 49, 32, 3, 'Review 23', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(24, 1, 37, 4, 'Review 24', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(25, 31, 31, 4, 'Review 25', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(26, 22, 12, 3, 'Review 26', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(27, 17, 31, 3, 'Review 27', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(28, 5, 26, 4, 'Review 28', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(29, 5, 37, 5, 'Review 29', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(30, 44, 4, 3, 'Review 30', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(31, 10, 37, 4, 'Review 31', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(32, 6, 16, 3, 'Review 32', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(33, 36, 49, 4, 'Review 33', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(34, 39, 39, 5, 'Review 34', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(35, 15, 50, 5, 'Review 35', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(36, 25, 29, 4, 'Review 36', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(37, 20, 38, 4, 'Review 37', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(38, 20, 37, 5, 'Review 38', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(39, 4, 40, 5, 'Review 39', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(40, 7, 49, 3, 'Review 40', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(41, 41, 14, 4, 'Review 41', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(42, 43, 6, 3, 'Review 42', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(43, 16, 12, 5, 'Review 43', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(44, 5, 11, 3, 'Review 44', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(45, 27, 29, 5, 'Review 45', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(46, 39, 31, 4, 'Review 46', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(47, 3, 15, 4, 'Review 47', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(48, 46, 19, 5, 'Review 48', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(49, 30, 5, 5, 'Review 49', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(50, 15, 17, 5, 'Review 50', 'Fast delivery and fresh products', 'approved', '2026-05-24 07:59:49', '2026-05-24 07:59:49'),
(52, 6, 144, 4, 'Awesohbme', 'Grehat product!', 'pending', '2026-05-24 19:21:04', '2026-05-24 19:21:04'),
(53, 6, 26, 5, 'Awesohbme', 'Grehat prccscoduct!', 'pending', '2026-05-24 19:25:14', '2026-05-24 19:25:14'),
(54, 2, 145, 4, 'good', 'awesome', 'pending', '2026-05-24 19:32:22', '2026-05-24 19:32:22'),
(55, 3, 145, 4, 'wow', 'dvd da', 'pending', '2026-05-25 04:14:38', '2026-05-25 04:14:38'),
(57, 2, 144, 3, 'jjnj', 'jjbbbhb', 'pending', '2026-05-29 15:47:19', '2026-05-29 15:47:19'),
(58, 1, 147, 4, 'ddd', 'fxtdtxt', 'pending', '2026-06-03 14:44:49', '2026-06-03 14:44:49');

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

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'My New Store', '2026-04-26 16:25:48', '2026-05-23 08:59:22'),
(2, 'site_email', 'mandalvarietycustomerssupport@gmail.com', '2026-04-26 16:25:48', '2026-04-26 16:25:48'),
(3, 'currency', 'INR', '2026-04-26 16:25:48', '2026-04-26 16:25:48'),
(4, 'shipping_charge', '10', '2026-04-26 16:25:48', '2026-04-26 16:25:48'),
(5, 'tax_percentage', '0', '2026-04-26 16:25:48', '2026-04-26 16:25:48'),
(6, 'store_name', 'My E-commerce Store', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(7, 'store_email', 'admin@example.com', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(8, 'store_phone', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(9, 'store_whatsapp', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(10, 'store_address', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(11, 'store_city', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(12, 'store_state', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(13, 'store_country', 'India', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(14, 'store_pincode', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(15, 'store_currency', 'INR', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(16, 'currency_symbol', '₹', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(17, 'timezone', 'Asia/Kolkata', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(18, 'tax_percent', '0', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(20, 'free_shipping_min_amount', '0', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(21, 'razorpay_key_id', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(22, 'razorpay_key_secret', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(23, 'paypal_client_id', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(24, 'paypal_secret', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(25, 'low_stock_limit', '5', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(26, 'maintenance_message', 'We are improving our store. Please check back soon.', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(27, 'seo_meta_title', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(28, 'seo_meta_keywords', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(29, 'seo_meta_description', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(30, 'seo_og_image', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(31, 'smtp_host', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(32, 'smtp_port', '587', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(33, 'smtp_username', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(34, 'smtp_password', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(35, 'smtp_encryption', 'tls', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(36, 'invoice_prefix', 'INV', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(37, 'footer_text', '© All rights reserved.', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(38, 'facebook_url', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(39, 'instagram_url', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(40, 'twitter_url', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(41, 'youtube_url', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(42, 'admin_per_page', '10', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(43, 'admin_theme', 'light', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(44, 'cod_enabled', '1', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(45, 'razorpay_enabled', '0', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(46, 'paypal_enabled', '0', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(47, 'order_auto_confirm', '0', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(48, 'allow_guest_checkout', '1', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(49, 'maintenance_mode', '1', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(50, 'email_notifications', '1', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(51, 'order_notifications', '1', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(52, 'review_notifications', '1', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(53, 'new_user_notifications', '1', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(54, 'logo', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15'),
(55, 'favicon', '', '2026-04-26 16:27:15', '2026-04-26 16:27:15');

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
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `address`, `city`, `state`, `country`, `profile_image`, `pincode`, `is_active`, `created_at`, `updated_at`, `otp`, `otp_expires_at`, `is_verified`, `email_verified_at`, `last_login`, `login_otp`, `otp_expiry`) VALUES
(1, 'User 1', 'user1@gmail.com', '9876500001', '$2y$10$demo', 'customer', 'Address 1', 'Kolkata', 'West Bengal', 'India', 'user1.png', '700011', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(2, 'Admin User', 'admin@mondalvr.com', '9876543211', 'hashedpass2', 'admin', 'Admin Office, Siliguri', NULL, NULL, NULL, NULL, '734001', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', NULL, NULL, 1, '2026-04-22 04:30:00', NULL, NULL, NULL),
(3, 'User 3', 'user3@gmail.com', '9876500003', '$2y$10$demo', 'customer', 'Address 3', 'Howrah', 'West Bengal', 'India', 'user3.png', '700031', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(4, 'User 4', 'user4@gmail.com', '9876500004', '$2y$10$demo', 'customer', 'Address 4', 'Siliguri', 'West Bengal', 'India', 'user4.png', '700041', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(5, 'User 5', 'user5@gmail.com', '9876500005', '$2y$10$demo', 'customer', 'Address 5', 'Siliguri', 'West Bengal', 'India', 'user5.png', '700051', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(6, 'User 6', 'user6@gmail.com', '9876500006', '$2y$10$demo', 'customer', 'Address 6', 'Siliguri', 'West Bengal', 'India', 'user6.png', '700061', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(7, 'User 7', 'user7@gmail.com', '9876500007', '$2y$10$demo', 'customer', 'Address 7', 'Kolkata', 'West Bengal', 'India', 'user7.png', '700071', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(8, 'User 8', 'user8@gmail.com', '9876500008', '$2y$10$demo', 'customer', 'Address 8', 'Asansol', 'West Bengal', 'India', 'user8.png', '700081', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(9, 'User 9', 'user9@gmail.com', '9876500009', '$2y$10$demo', 'customer', 'Address 9', 'Kolkata', 'West Bengal', 'India', 'user9.png', '700091', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(10, 'User 10', 'user10@gmail.com', '9876500010', '$2y$10$demo', 'customer', 'Address 10', 'Asansol', 'West Bengal', 'India', 'user10.png', '700001', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(11, 'ramji', 'a@gmail.com', '9083646603', '$2y$10$JuEK4xtLnGVlrPg9oRCEiOs1gk9VLNTSv1iSOkw6VeBeBQFNXEn9e', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-24 07:19:19', '2026-04-24 07:19:19', NULL, NULL, 1, '2026-04-24 03:49:19', NULL, NULL, NULL),
(12, 'ramji', 'sribash@gmail.com', '9083646603', '$2y$10$BICjieESrRlBjrydH4gqW.LIfijmGhQscHBg.y1xX3u7h5.iIr0Am', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-24 07:19:57', '2026-04-24 07:19:57', NULL, NULL, 1, '2026-04-24 03:49:57', NULL, NULL, NULL),
(13, 'User 13', 'user13@gmail.com', '9876500013', '$2y$10$demo', 'customer', 'Address 13', 'Kolkata', 'West Bengal', 'India', 'user13.png', '700031', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(14, 'User 14', 'user14@gmail.com', '9876500014', '$2y$10$demo', 'customer', 'Address 14', 'Kolkata', 'West Bengal', 'India', 'user14.png', '700041', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(15, 'User 15', 'user15@gmail.com', '9876500015', '$2y$10$demo', 'customer', 'Address 15', 'Siliguri', 'West Bengal', 'India', 'user15.png', '700051', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(16, 'User 16', 'user16@gmail.com', '9876500016', '$2y$10$demo', 'customer', 'Address 16', 'Siliguri', 'West Bengal', 'India', 'user16.png', '700061', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(17, 'User 17', 'user17@gmail.com', '9876500017', '$2y$10$demo', 'customer', 'Address 17', 'Asansol', 'West Bengal', 'India', 'user17.png', '700071', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(18, 'User 18', 'user18@gmail.com', '9876500018', '$2y$10$demo', 'customer', 'Address 18', 'Asansol', 'West Bengal', 'India', 'user18.png', '700081', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(19, 'User 19', 'user19@gmail.com', '9876500019', '$2y$10$demo', 'customer', 'Address 19', 'Kolkata', 'West Bengal', 'India', 'user19.png', '700091', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(20, 'User 20', 'user20@gmail.com', '9876500020', '$2y$10$demo', 'customer', 'Address 20', 'Asansol', 'West Bengal', 'India', 'user20.png', '700001', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(21, 'User 21', 'user21@gmail.com', '9876500021', '$2y$10$demo', 'customer', 'Address 21', 'Siliguri', 'West Bengal', 'India', 'user21.png', '700011', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(22, 'User 22', 'user22@gmail.com', '9876500022', '$2y$10$demo', 'customer', 'Address 22', 'Asansol', 'West Bengal', 'India', 'user22.png', '700021', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(23, 'User 23', 'user23@gmail.com', '9876500023', '$2y$10$demo', 'customer', 'Address 23', 'Durgapur', 'West Bengal', 'India', 'user23.png', '700031', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(24, 'User 24', 'user24@gmail.com', '9876500024', '$2y$10$demo', 'customer', 'Address 24', 'Siliguri', 'West Bengal', 'India', 'user24.png', '700041', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(25, 'User 25', 'user25@gmail.com', '9876500025', '$2y$10$demo', 'customer', 'Address 25', 'Durgapur', 'West Bengal', 'India', 'user25.png', '700051', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(26, 'User 26', 'user26@gmail.com', '9876500026', '$2y$10$demo', 'customer', 'Address 26', 'Asansol', 'West Bengal', 'India', 'user26.png', '700061', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(27, 'User 27', 'user27@gmail.com', '9876500027', '$2y$10$demo', 'customer', 'Address 27', 'Howrah', 'West Bengal', 'India', 'user27.png', '700071', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(28, 'User 28', 'user28@gmail.com', '9876500028', '$2y$10$demo', 'customer', 'Address 28', 'Kolkata', 'West Bengal', 'India', 'user28.png', '700081', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(29, 'User 29', 'user29@gmail.com', '9876500029', '$2y$10$demo', 'customer', 'Address 29', 'Siliguri', 'West Bengal', 'India', 'user29.png', '700091', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(30, 'User 30', 'user30@gmail.com', '9876500030', '$2y$10$demo', 'customer', 'Address 30', 'Durgapur', 'West Bengal', 'India', 'user30.png', '700001', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(31, 'User 31', 'user31@gmail.com', '9876500031', '$2y$10$demo', 'customer', 'Address 31', 'Howrah', 'West Bengal', 'India', 'user31.png', '700011', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(32, 'User 32', 'user32@gmail.com', '9876500032', '$2y$10$demo', 'customer', 'Address 32', 'Howrah', 'West Bengal', 'India', 'user32.png', '700021', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(33, 'User 33', 'user33@gmail.com', '9876500033', '$2y$10$demo', 'customer', 'Address 33', 'Siliguri', 'West Bengal', 'India', 'user33.png', '700031', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(34, 'User 34', 'user34@gmail.com', '9876500034', '$2y$10$demo', 'customer', 'Address 34', 'Siliguri', 'West Bengal', 'India', 'user34.png', '700041', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(35, 'User 35', 'user35@gmail.com', '9876500035', '$2y$10$demo', 'customer', 'Address 35', 'Howrah', 'West Bengal', 'India', 'user35.png', '700051', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(36, 'User 36', 'user36@gmail.com', '9876500036', '$2y$10$demo', 'customer', 'Address 36', 'Kolkata', 'West Bengal', 'India', 'user36.png', '700061', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(37, 'User 37', 'user37@gmail.com', '9876500037', '$2y$10$demo', 'customer', 'Address 37', 'Kolkata', 'West Bengal', 'India', 'user37.png', '700071', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(38, 'User 38', 'user38@gmail.com', '9876500038', '$2y$10$demo', 'customer', 'Address 38', 'Durgapur', 'West Bengal', 'India', 'user38.png', '700081', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(39, 'User 39', 'user39@gmail.com', '9876500039', '$2y$10$demo', 'customer', 'Address 39', 'Kolkata', 'West Bengal', 'India', 'user39.png', '700091', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(40, 'User 40', 'user40@gmail.com', '9876500040', '$2y$10$demo', 'customer', 'Address 40', 'Howrah', 'West Bengal', 'India', 'user40.png', '700001', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(41, 'User 41', 'user41@gmail.com', '9876500041', '$2y$10$demo', 'customer', 'Address 41', 'Howrah', 'West Bengal', 'India', 'user41.png', '700011', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(42, 'User 42', 'user42@gmail.com', '9876500042', '$2y$10$demo', 'customer', 'Address 42', 'Asansol', 'West Bengal', 'India', 'user42.png', '700021', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(43, 'User 43', 'user43@gmail.com', '9876500043', '$2y$10$demo', 'customer', 'Address 43', 'Howrah', 'West Bengal', 'India', 'user43.png', '700031', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(44, 'User 44', 'user44@gmail.com', '9876500044', '$2y$10$demo', 'customer', 'Address 44', 'Kolkata', 'West Bengal', 'India', 'user44.png', '700041', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(45, 'User 45', 'user45@gmail.com', '9876500045', '$2y$10$demo', 'customer', 'Address 45', 'Durgapur', 'West Bengal', 'India', 'user45.png', '700051', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(46, 'User 46', 'user46@gmail.com', '9876500046', '$2y$10$demo', 'customer', 'Address 46', 'Asansol', 'West Bengal', 'India', 'user46.png', '700061', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(47, 'User 47', 'user47@gmail.com', '9876500047', '$2y$10$demo', 'customer', 'Address 47', 'Kolkata', 'West Bengal', 'India', 'user47.png', '700071', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(48, 'User 48', 'user48@gmail.com', '9876500048', '$2y$10$demo', 'customer', 'Address 48', 'Durgapur', 'West Bengal', 'India', 'user48.png', '700081', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(49, 'User 49', 'user49@gmail.com', '9876500049', '$2y$10$demo', 'customer', 'Address 49', 'Kolkata', 'West Bengal', 'India', 'user49.png', '700091', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(50, 'User 50', 'user50@gmail.com', '9876500050', '$2y$10$demo', 'customer', 'Address 50', 'Asansol', 'West Bengal', 'India', 'user50.png', '700001', 1, '2026-05-24 07:59:49', '2026-05-24 07:59:49', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(144, 'sayan', 'sayanbanikcob@gmail.com', '9099099090', '$2y$10$WBxp.w7fdvisQBTJYnzyYulLYQLQj/vmN8tphly6EC5UG7gMKaIFK', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-24 08:06:21', '2026-06-02 07:48:59', NULL, NULL, 1, NULL, NULL, NULL, NULL),
(145, 'Temp Mail', 'xawanop956@noyavip.com', '1234567890', '$2y$10$eEHEbYqCpoayg5j7Aw12GeXS813ftRGNISqE9ONHEcQ36Oz9yj9fa', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-24 11:27:45', '2026-05-24 11:29:27', NULL, NULL, 1, NULL, NULL, NULL, NULL),
(146, 'ram', 'sribashsarkarblp@gmail.com', '7074785899', '$2y$10$yyHNcHw9pmxdD7P0yEQLI.hzP9SBWqKyhY2wyLtIGYoot1par0HYy', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-02 07:45:00', '2026-06-02 07:48:49', NULL, NULL, 1, NULL, NULL, NULL, NULL),
(147, 'ziaul', 'imramanda884@gmail.com', '8001748622', '$2y$10$j32YwRgv7LlpxRFZxZPR4eXm4Sq7p/klp9Cc3Tj2chVd1EbLHWY0u', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-06-03 14:18:26', '2026-06-03 14:21:42', NULL, NULL, 1, NULL, NULL, NULL, NULL);

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
(1, 142, 'cb34cb324b3072c909000db4641b4f8807e2f9d9826f58d9fb352a13d429b28e', '2026-05-23 23:38:58', '2026-06-22 23:38:58'),
(2, 142, '655f4005e030cced2990199352ef4f8484a49685802b3d46697ee53bdccea09a', '2026-05-23 23:59:20', '2026-06-22 23:59:20'),
(3, 142, 'd8d3671dfef6427de31b5b021afa838219cdc0116a2b16e73a5e410078444bc4', '2026-05-24 00:13:29', '2026-06-23 00:13:29'),
(4, 143, '95d83d5d6a9d2d9e05b6e2040931160c22e510be263a89d89bf92a0a9a3b3d59', '2026-05-24 00:27:19', '2026-06-23 00:27:19'),
(5, 142, 'b6093b2922d0d3dc2d3c3191e8abab8be08d3c59aec8b504f1ff354846eb86a6', '2026-05-24 01:19:34', '2026-06-23 01:19:34'),
(6, 143, '23deda811419cdd85791b94ef460055cdcd672006883a12096ec736ef14c0bd3', '2026-05-24 04:18:53', '2026-06-23 04:18:53'),
(7, 144, '19c41d9581cb8df0e50f7d68c18e22d1bb4aa14aae1ad74eff4114728fe72f05', '2026-05-24 08:07:49', '2026-06-23 08:07:49'),
(8, 145, '2152733ebda84c2be7e145a675cdd2d68de9652e2771efb3f6065ce7ddd44615', '2026-05-24 11:29:27', '2026-06-23 11:29:27'),
(9, 144, 'bcf12b26f3d8c451bb62333c2b08c10f7b693a3b5179d28a14f97d23271e3e37', '2026-05-24 19:18:47', '2026-06-23 19:18:47'),
(10, 144, 'f562af1fbff24cff2f5895ea96f97391adf98b70786b04a19282a85b3d6c2f4d', '2026-05-28 15:04:46', '2026-06-27 15:04:46'),
(11, 144, 'cbe86d584a668771c6bed2249da30a0f06ce9b6fb2118c3477892784c403595f', '2026-05-29 15:38:24', '2026-06-28 15:38:24'),
(12, 146, '1efa455499005a98f5967157dfc87bdb8b5d48ff123489ef2e09de26db1f8250', '2026-06-02 07:48:49', '2026-07-02 07:48:49'),
(13, 144, '4d963f7084c6c5fcc365d1db8b65352d019d23d3c00fa9f551f6b6856c96e63a', '2026-06-02 07:48:59', '2026-07-02 07:48:59'),
(14, 147, '6a62c81679003170d98b7535832ee398b02a195b27cb8ac7384ec10dbf98e524', '2026-06-03 14:21:42', '2026-07-03 14:21:42');

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

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 22, 44, '2026-05-24 07:59:49'),
(2, 35, 25, '2026-05-24 07:59:49'),
(3, 21, 41, '2026-05-24 07:59:49'),
(4, 46, 49, '2026-05-24 07:59:49'),
(5, 32, 35, '2026-05-24 07:59:49'),
(6, 3, 40, '2026-05-24 07:59:49'),
(7, 5, 16, '2026-05-24 07:59:49'),
(8, 41, 44, '2026-05-24 07:59:49'),
(9, 19, 15, '2026-05-24 07:59:49'),
(10, 48, 6, '2026-05-24 07:59:49'),
(11, 28, 7, '2026-05-24 07:59:49'),
(12, 49, 41, '2026-05-24 07:59:49'),
(13, 46, 7, '2026-05-24 07:59:49'),
(14, 29, 11, '2026-05-24 07:59:49'),
(15, 45, 20, '2026-05-24 07:59:49'),
(16, 2, 3, '2026-05-24 07:59:49'),
(17, 21, 4, '2026-05-24 07:59:49'),
(18, 19, 23, '2026-05-24 07:59:49'),
(19, 24, 28, '2026-05-24 07:59:49'),
(20, 10, 16, '2026-05-24 07:59:49'),
(21, 34, 27, '2026-05-24 07:59:49'),
(22, 37, 44, '2026-05-24 07:59:49'),
(23, 12, 11, '2026-05-24 07:59:49'),
(24, 12, 6, '2026-05-24 07:59:49'),
(25, 40, 25, '2026-05-24 07:59:49'),
(26, 40, 44, '2026-05-24 07:59:49'),
(27, 16, 32, '2026-05-24 07:59:49'),
(28, 38, 10, '2026-05-24 07:59:49'),
(29, 15, 30, '2026-05-24 07:59:49'),
(30, 41, 17, '2026-05-24 07:59:49'),
(31, 30, 17, '2026-05-24 07:59:49'),
(32, 43, 1, '2026-05-24 07:59:49'),
(33, 30, 19, '2026-05-24 07:59:49'),
(34, 44, 35, '2026-05-24 07:59:49'),
(35, 11, 5, '2026-05-24 07:59:49'),
(36, 29, 23, '2026-05-24 07:59:49'),
(37, 38, 20, '2026-05-24 07:59:49'),
(38, 41, 28, '2026-05-24 07:59:49'),
(39, 45, 17, '2026-05-24 07:59:49'),
(40, 30, 20, '2026-05-24 07:59:49'),
(41, 13, 25, '2026-05-24 07:59:49'),
(42, 31, 7, '2026-05-24 07:59:49'),
(43, 16, 25, '2026-05-24 07:59:49'),
(44, 37, 23, '2026-05-24 07:59:49'),
(45, 37, 19, '2026-05-24 07:59:49'),
(46, 45, 19, '2026-05-24 07:59:49'),
(47, 2, 43, '2026-05-24 07:59:49'),
(48, 26, 18, '2026-05-24 07:59:49'),
(49, 1, 37, '2026-05-24 07:59:49'),
(50, 44, 50, '2026-05-24 07:59:49');

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
(1, 48, 4, '2026-05-24 07:59:49'),
(2, 39, 48, '2026-05-24 07:59:49'),
(3, 32, 19, '2026-05-24 07:59:49'),
(4, 50, 15, '2026-05-24 07:59:49'),
(5, 39, 23, '2026-05-24 07:59:49'),
(6, 15, 41, '2026-05-24 07:59:49'),
(7, 13, 40, '2026-05-24 07:59:49'),
(8, 17, 44, '2026-05-24 07:59:49'),
(9, 49, 47, '2026-05-24 07:59:49'),
(10, 50, 43, '2026-05-24 07:59:49'),
(11, 44, 9, '2026-05-24 07:59:49'),
(12, 41, 7, '2026-05-24 07:59:49'),
(13, 41, 42, '2026-05-24 07:59:49'),
(14, 3, 20, '2026-05-24 07:59:49'),
(15, 29, 3, '2026-05-24 07:59:49'),
(16, 38, 24, '2026-05-24 07:59:49'),
(17, 47, 9, '2026-05-24 07:59:49'),
(18, 6, 19, '2026-05-24 07:59:49'),
(19, 21, 48, '2026-05-24 07:59:49'),
(20, 27, 12, '2026-05-24 07:59:49'),
(21, 13, 9, '2026-05-24 07:59:49'),
(22, 35, 24, '2026-05-24 07:59:49'),
(23, 34, 33, '2026-05-24 07:59:49'),
(24, 18, 11, '2026-05-24 07:59:49'),
(25, 17, 31, '2026-05-24 07:59:49'),
(26, 19, 48, '2026-05-24 07:59:49'),
(27, 22, 8, '2026-05-24 07:59:49'),
(28, 30, 5, '2026-05-24 07:59:49'),
(29, 10, 49, '2026-05-24 07:59:49'),
(30, 15, 44, '2026-05-24 07:59:49'),
(31, 47, 44, '2026-05-24 07:59:49'),
(32, 26, 36, '2026-05-24 07:59:49'),
(33, 24, 6, '2026-05-24 07:59:49'),
(34, 26, 1, '2026-05-24 07:59:49'),
(35, 17, 35, '2026-05-24 07:59:49'),
(36, 8, 30, '2026-05-24 07:59:49'),
(37, 24, 44, '2026-05-24 07:59:49'),
(38, 48, 44, '2026-05-24 07:59:49'),
(39, 17, 38, '2026-05-24 07:59:49'),
(40, 25, 41, '2026-05-24 07:59:49'),
(41, 24, 7, '2026-05-24 07:59:49'),
(42, 44, 15, '2026-05-24 07:59:49'),
(43, 31, 2, '2026-05-24 07:59:49'),
(44, 40, 36, '2026-05-24 07:59:49'),
(45, 21, 40, '2026-05-24 07:59:49'),
(46, 15, 42, '2026-05-24 07:59:49'),
(47, 5, 41, '2026-05-24 07:59:49'),
(48, 30, 45, '2026-05-24 07:59:49'),
(49, 20, 42, '2026-05-24 07:59:49'),
(50, 27, 8, '2026-05-24 07:59:49'),
(51, 144, 1, '2026-05-25 21:15:14'),
(53, 145, 51, '2026-05-28 18:47:30'),
(54, 144, 51, '2026-05-29 15:45:21'),
(55, 146, 1, '2026-06-02 07:49:15'),
(57, 144, 2, '2026-06-02 07:51:48'),
(59, 147, 49, '2026-06-03 14:40:18'),
(60, 147, 1, '2026-06-03 15:10:48');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

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

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`assigned_delivery_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
