
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

--
-- Dumping data for table `age_verifications`
--

INSERT INTO `age_verifications` (`id`, `user_id`, `order_id`, `full_name`, `email`, `phone`, `date_of_birth`, `age_threshold`, `verified_age`, `method`, `document_type`, `document_number`, `document_front`, `document_back`, `selfie_image`, `status`, `confidence_score`, `review_notes`, `created_at`, `reviewed_at`) VALUES
(2, 9, '11', 'Rahul Sharma', 'rahul@example.com', '9876543210', '2000-05-15', 18, 24, 'document', 'Aadhar Card', 'XXXX-XXXX-1234', 'aadhaar_front.jpg', 'aadhaar_back.jpg', NULL, 'approved', 98.50, 'Verified successfully', '2026-04-26 17:09:34', '2026-04-26 17:09:34'),
(3, 9, '11', 'Rahul Sharma', 'rahul@example.com', '9876543210', '2008-03-10', 18, 16, 'document', 'PAN Card', 'ABCDE1234F', 'pan_front.jpg', NULL, NULL, 'rejected', 85.00, 'Underage user', '2026-04-26 17:09:34', '2026-04-26 17:09:34'),
(4, 9, '11', 'Rahul Sharma', 'rahul@example.com', '9876543210', '1998-11-22', 18, 26, 'self_declaration', NULL, NULL, NULL, NULL, NULL, 'approved', 75.00, 'Self declaration accepted', '2026-04-26 17:09:34', '2026-04-26 17:22:20'),
(5, 9, '11', 'Rahul Sharma', 'rahul@example.com', '9876543210', NULL, 18, NULL, 'facial_estimation', NULL, NULL, NULL, NULL, 'selfie1.jpg', 'under_review', 60.00, 'Waiting manual review', '2026-04-26 17:09:34', NULL),
(6, 9, '11', 'Rahul Sharma', 'rahul@example.com', '9876543210', '2005-07-19', 18, 19, 'document', 'Driving License', 'DL-123456789', 'dl_front.jpg', 'dl_back.jpg', NULL, 'approved', 92.00, 'Valid license verified', '2026-04-26 17:09:34', '2026-04-26 17:09:34');

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
(14, 9, '2026-04-26 15:16:20', '2026-04-26 15:16:20');

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
(20, 14, 13, 1, 500.00, '2026-04-26 15:26:54');

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
(1, 'Electronics', 'electronics', 'All electronic gadgets', 'cat-electronics.jpg', 1, '2026-03-08 02:38:45'),
(2, 'Clothing', 'clothing', 'Fashion and apparel', 'cat-clothing.jpg', 1, '2026-03-08 02:38:45'),
(3, 'Books', 'books', 'Educational books', 'cat-books.jpg', 1, '2026-03-08 02:38:45'),
(4, 'drink', '', NULL, NULL, 1, '2026-04-22 05:50:31'),
(6, 'Watches', 'watches', 'Wrist watches', NULL, 1, '2026-04-22 09:47:03'),
(7, 'Bags', 'bags', 'Travel bags', NULL, 1, '2026-04-22 09:47:03'),
(8, 'Beauty', 'beauty', 'Beauty products', NULL, 1, '2026-04-22 09:47:03'),
(10, 'Accessories', 'accessories', 'Extra accessories', NULL, 1, '2026-04-22 09:47:03');

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
(1, 7, NULL, '2JHELKJF', 'flat', 45.00, '2026-04-23', '2026-04-29', 'active', 2, '2026-04-26 06:14:13', '2026-04-26 06:14:13'),
(2, 8, NULL, '2JHELKJF', 'flat', 45.00, '2026-04-23', '2026-04-29', 'active', 2, '2026-04-26 06:22:31', '2026-04-26 06:22:31'),
(3, 9, NULL, '2JHELKJF', 'flat', 45.00, '2026-04-23', '2026-04-29', 'active', 2, '2026-04-26 06:29:34', '2026-04-26 06:29:34'),
(4, 10, NULL, 'kuhu', 'percent', 84.97, '2026-04-26', '2026-04-28', 'active', 1, '2026-04-26 06:35:12', '2026-04-26 06:35:12'),
(5, 11, NULL, 'kuhu', 'percent', 84.97, '2026-04-26', '2026-04-28', 'active', 1, '2026-04-26 06:40:40', '2026-04-26 06:40:40'),
(7, NULL, 8, '222', 'percent', 2.90, '2026-04-24', '2026-05-01', 'active', 0, '2026-04-26 06:50:50', '2026-04-26 06:50:50');

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
(1, 1, 'ORD001', 70000.00, 0.00, 0.00, 0.00, '', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(2, 2, 'ORD002', 100000.00, 0.00, 0.00, 0.00, 'pending', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(3, 3, 'ORD003', 60000.00, 0.00, 0.00, 0.00, '', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(4, 4, 'ORD004', 15000.00, 0.00, 0.00, 0.00, '', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(5, 5, 'ORD005', 1600.00, 0.00, 0.00, 0.00, 'pending', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(6, 6, 'ORD006', 2000.00, 0.00, 0.00, 0.00, '', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(7, 7, 'ORD007', 1500.00, 0.00, 0.00, 0.00, '', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(8, 8, 'ORD008', 2000.00, 0.00, 0.00, 0.00, 'pending', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(9, 9, 'ORD009', 600.00, 0.00, 0.00, 0.00, '', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(10, 10, 'ORD010', 2500.00, 0.00, 0.00, 0.00, '', 'pending', '', '', NULL, NULL, NULL, '2026-04-22 09:54:22', '2026-04-22 09:54:22', 'Order Placed', NULL),
(11, 9, 'ORD-1777189755', 4.99, 40.00, 10.00, 54.99, 'confirmed', 'paid', 'Kolkata, West Bengal, India', '700001', '13:19:15', NULL, 'Auto test order for user 9 and product 13', '2026-04-26 07:49:15', '2026-04-27 07:06:36', 'delivered', 'thank you so much sribash sarkar for order this product');

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
(6, 11, 13, 1, 4.99);

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
(2, 'Terms & Conditions', 'terms-conditions', 'terms_conditions', 'Rules and regulations for using our web', 'By using this website, you agree to our terms and conditions. Please read them carefully.', 'published', 'public', 1, 2, 'Terms & Conditions - My Store', 'terms,conditions,legal', 'Read our terms and conditions', '2026-04-26 16:32:04', '2026-04-26 16:49:57'),
(3, 'Refund Policy', 'refund-policy', 'refund_policy', 'Refund rules and eligibility', 'We offer refunds under certain conditions. Please read our refund policy for full details.', 'published', 'public', 0, 3, 'Refund Policy', 'refund,returns,money back', 'Refund policy details', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(4, 'Shipping Policy', 'shipping-policy', 'shipping_policy', 'Shipping times and charges', 'Orders are processed within 2-3 business days. Delivery times may vary depending on location.', 'published', 'public', 0, 4, 'Shipping Policy', 'shipping,delivery,orders', 'Shipping details', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(5, 'Cancellation Policy', 'cancellation-policy', 'cancellation_policy', 'Order cancellation rules', 'You can cancel your order within 24 hours of placing it.', 'published', 'public', 0, 5, 'Cancellation Policy', 'cancel,order,policy', 'Cancellation terms', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(6, 'About Us', 'about-us', 'about_us', 'Learn more about our company', 'We are a growing e-commerce platform providing quality products at affordable prices.', 'published', 'public', 1, 6, 'About Us', 'about,company,info', 'About our company', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(7, 'Contact Us', 'contact-us', 'contact_us', 'Get in touch with us', 'You can contact us via email or phone for any queries or support.', 'published', 'public', 0, 7, 'Contact Us', 'contact,support,email', 'Contact information', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(8, 'FAQ', 'faq', 'faq', 'Frequently asked questions', 'Find answers to common questions about orders, shipping, and returns.', 'published', 'public', 0, 8, 'FAQ', 'faq,questions,help', 'Frequently asked questions', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(9, 'Return Policy', 'return-policy', 'custom', 'Return product conditions', 'Products can be returned within 7 days if unused and in original packaging.', 'published', 'public', 0, 9, 'Return Policy', 'return,product,policy', 'Return rules', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(10, 'User Agreement', 'user-agreement', 'custom', 'Agreement between user and platform', 'This agreement governs your use of our platform and services.', 'draft', 'private', 0, 10, 'User Agreement', 'agreement,user,terms', 'User agreement details', '2026-04-26 16:32:04', '2026-04-26 16:32:04'),
(11, 'jfbbkj', 'jfbbkj', 'faq', 'dkjfj', 'fkjhfbwkj', 'archived', 'private', 0, 18, '', '', '', '2026-04-26 16:54:05', '2026-04-26 16:54:05'),
(12, 'hhh', 'hhh', 'faq', 'dkjfj', 'fkjhfbwkj', 'archived', 'private', 0, 18, 'Terms & Conditions - My Store', 'terms,conditions,legal', 'jnlj', '2026-04-26 16:55:05', '2026-04-26 16:55:05');

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
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `discount_price`, `sku`, `stock_quantity`, `category_id`, `images`, `weight`, `is_active`, `created_at`, `updated_at`, `stock`) VALUES
(1, 'iPhone 15 Pro', 'iphone-15-pro', 'Latest Apple smartphone', 999.00, 899.00, 'IPH15PRO001', 25, 1, '[\"iphone1.jpg\",\"iphone2.jpg\"]', NULL, 1, '2026-03-08 02:38:45', '2026-03-08 02:38:45', 0),
(2, 'Samsung Galaxy S24', 'samsung-galaxy-s24', 'Android flagship', 799.00, NULL, 'SGS24ULTRA001', 15, 1, '[\"samsung1.jpg\",\"samsung2.jpg\"]', NULL, 1, '2026-03-08 02:38:45', '2026-03-08 02:38:45', 0),
(3, 'T-Shirt Cotton', 't-shirt-cotton', 'Premium cotton t-shirt', 29.99, 19.99, 'TSHIRT001', 100, 2, '[\"tshirt1.jpg\",\"tshirt2.jpg\"]', NULL, 1, '2026-03-08 02:38:45', '2026-03-08 02:38:45', 0),
(4, 'Python Programming Book', 'python-programming-book', 'Complete Python guide', 49.99, NULL, 'BOOKPY001', 50, 3, '[\"1772057246_1.jpg\"]', NULL, 1, '2026-03-08 02:38:45', '2026-04-22 10:29:05', 0),
(6, 'milk shak', 'banana', 'best drink\n\n[offer_meta]{\"offer_name\":\"summ\",\"offer_type\":\"flat\",\"offer_value\":15,\"offer_start\":\"2026-04-22\",\"offer_end\":\"2026-08-15\",\"offer_status\":\"running\",\"offer_sort\":\"price_low\"}', 67.00, 52.00, 'SKU-1772070982', 0, 8, '[\"1776870789_45ae1036.png\",\"1776870789_5ba720be.png\"]', 0.000, 1, '2026-04-22 15:13:09', '2026-04-22 15:13:09', 50),
(7, 'kuk', 'fbjcjx', 'TRKERHGFEJHGRE', 123.00, 455.00, 'KUK936', 22, 1, '[\"1777184053_9745.png\"]', 2.000, 1, '2026-04-26 06:14:13', '2026-04-26 06:14:13', 22),
(8, 'kuk', 'fbjcjx-1', 'TRKERHGFEJHGRE', 123.00, 78.00, 'KUK530', 22, 1, '[\"1777184551_1256_0.png\"]', 2.000, 1, '2026-04-26 06:22:31', '2026-04-26 06:22:31', 22),
(9, 'kuk', 'fbjcjx-2', 'TRKERHGFEJHGRE', 123.00, 78.00, 'KUK941', 22, 1, '[\"1777184974_8875_0.png\"]', 2.000, 1, '2026-04-26 06:29:34', '2026-04-26 06:29:34', 22),
(10, 'dnj', 'dnj', 'igtyyfut', 77.98, 11.72, 'DNJ195', 55, 6, '[\"1777185312_8687_0.png\",\"1777185312_8318_1.png\",\"1777185312_4725_2.png\"]', 1.000, 1, '2026-04-26 06:35:12', '2026-04-26 06:35:12', 55),
(11, 'dnj', 'dnj-1', 'igtyyfut', 77.98, 11.72, 'DNJ877', 55, 6, '[\"1777185640_1501_0.png\",\"1777185640_7964_1.png\",\"1777185640_6805_2.png\"]', 1.000, 1, '2026-04-26 06:40:40', '2026-04-26 06:40:40', 55),
(13, 'hfhgg', 'hfhgg', 'gchgg', 4.99, 4.85, 'HFHGG324', 1, 8, '[\"1777186250_3177_0.png\",\"1777186250_1831_1.png\",\"1777186250_3586_2.png\"]', NULL, 1, '2026-04-26 06:50:50', '2026-04-26 06:50:50', 1);

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
(1, 13, 9, 5, 'Excellent Product', 'Very good quality product. Fast delivery and nice experience.', 'approved', '2026-04-26 16:16:39', '2026-04-26 16:16:39');

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
(1, 'site_name', 'Mandal variety', '2026-04-26 16:25:48', '2026-04-26 16:25:48'),
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
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `address`, `city`, `state`, `country`, `profile_image`, `pincode`, `is_active`, `created_at`, `updated_at`, `otp`, `otp_expires_at`, `is_verified`, `email_verified_at`, `last_login`) VALUES
(1, 'Bibek Sarkar', 'test@example.com', '9876543210', '$2y$10$Ow2svx9iKJnSLbratKX8R.59Xz1hJi6C4vfFhRwLaCV/G7UOCZLB.', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-07 10:09:56', '2026-03-07 10:09:56', '252217', NULL, 0, NULL, NULL),
(2, 'Admin User', 'admin@mondalvr.com', '9876543211', 'hashedpass2', 'admin', 'Admin Office, Siliguri', NULL, NULL, NULL, NULL, '734001', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', NULL, NULL, 1, '2026-04-22 04:30:00', NULL),
(3, 'Delivery Boy 1', 'delivery1@mondalvr.com', '9876543212', 'hashedpass3', 'delivery', 'Delivery Hub, Siliguri', NULL, NULL, NULL, NULL, '734001', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', NULL, NULL, 1, NULL, NULL),
(4, 'Ravi Kumar', 'ravi@example.com', '9876543213', 'hashedpass4', 'customer', 'Sevoke Road', NULL, NULL, NULL, NULL, '734005', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', NULL, NULL, 0, NULL, NULL),
(5, 'sribash Sarkar', 'sribashsarkarb', '9876543210', '$2y$10$9wxTrJf/JBKpDvCbQPeaOOozmitFI4RvSADHB/oiglLxDS0GUheEa', 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-03-07 11:16:23', '2026-04-26 13:23:11', NULL, NULL, 1, NULL, NULL),
(6, 'Priya Das', 'priya@example.com', '9876543215', 'hashedpass6', 'customer', 'Uttarayan', NULL, NULL, NULL, NULL, '734007', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', '123456', '2026-04-23 04:30:00', 0, NULL, NULL),
(7, 'Amit Roy', 'amit@example.com', '9876543216', 'hashedpass7', 'customer', 'Matigara', NULL, NULL, NULL, NULL, '734010', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', NULL, NULL, 1, '2026-04-22 05:30:00', NULL),
(8, 'Sita Mandal', 'sita@example.com', '9876543217', 'hashedpass8', 'customer', 'Bagdogra', NULL, NULL, NULL, NULL, '734014', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', NULL, NULL, 1, NULL, NULL),
(9, 'Rajesh Singh', 'sribashsarkarblp@gmail.com', '9876543218', 'hashedpass9', 'customer', 'Donbosco', 'fbfh', 'fbdfhd', 'ffbdnfbdh', NULL, '734003', 1, '2026-04-22 04:30:00', '2026-04-26 14:04:13', NULL, NULL, 0, NULL, NULL),
(10, 'Neha Bose', 'neha@example.com', '9876543219', 'hashedpass10', 'customer', 'Hill Cart Road', NULL, NULL, NULL, NULL, '734001', 1, '2026-04-22 04:30:00', '2026-04-22 04:30:00', NULL, NULL, 1, '2026-04-22 06:30:00', NULL),
(11, 'ramji', 'a@gmail.com', '9083646603', '$2y$10$JuEK4xtLnGVlrPg9oRCEiOs1gk9VLNTSv1iSOkw6VeBeBQFNXEn9e', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-24 07:19:19', '2026-04-24 07:19:19', NULL, NULL, 1, '2026-04-24 03:49:19', NULL),
(12, 'ramji', 'sribash@gmail.com', '9083646603', '$2y$10$BICjieESrRlBjrydH4gqW.LIfijmGhQscHBg.y1xX3u7h5.iIr0Am', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-24 07:19:57', '2026-04-24 07:19:57', NULL, NULL, 1, '2026-04-24 03:49:57', NULL);

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
(15, 9, 13, '2026-04-26 16:36:35');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
