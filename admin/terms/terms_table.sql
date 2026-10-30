-- ==============================================================
-- Table structure for `terms_and_conditions`
-- ==============================================================

CREATE TABLE IF NOT EXISTS `terms_and_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `section_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_terms_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Seed Data
INSERT INTO `terms_and_conditions` (`title`, `slug`, `content`, `section_order`, `status`, `is_active`) VALUES
('General Terms of Service', 'general-terms-of-service', 'Welcome to Mandal Variety. By downloading, accessing or using our mobile application and web platform, you agree to be bound by these Terms and Conditions. If you do not agree to all terms, please refrain from using our services.\n\nAll content, products, and services provided are subject to acceptance of these rules.', 1, 'published', 1),
('Account Registration & Security', 'account-registration-security', 'Users are required to register using a valid phone number and OTP verification. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.\n\nWe reserve the right to suspend or terminate accounts that provide false information or violate our policies.', 2, 'published', 1),
('Orders, Pricing & Payment', 'orders-pricing-payment', 'All orders placed through the app are subject to stock availability and confirmation. Prices of items are subject to change without prior notice. Payment can be made via Cash on Delivery (COD) or online payment methods where available.\n\nIn case an item becomes out of stock after order placement, our team will promptly notify you.', 3, 'published', 1),
('Delivery & Shipping Policy', 'delivery-shipping-policy', 'We aim to deliver items within the specified delivery timeframe. Deliveries are made by assigned delivery partners to the shipping address and pincode provided by the customer during checkout.\n\nPlease ensure someone is available at the delivery location to receive and inspect the order.', 4, 'published', 1),
('Cancellation & Returns', 'cancellation-returns', 'Customers can cancel pending orders before they are dispatched for delivery. For damaged or defective products, complaints must be registered within 24 hours of delivery with photographic evidence.\n\nRefunds for approved cancellations will be processed according to our standard refund schedule.', 5, 'published', 1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

