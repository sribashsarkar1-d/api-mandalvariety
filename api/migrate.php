<?php
require 'config/database.php';

try {
    // 1. Coupons Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `coupons` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `code` varchar(50) NOT NULL UNIQUE,
      `type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
      `value` decimal(10,2) NOT NULL,
      `min_cart_value` decimal(10,2) DEFAULT '0.00',
      `max_discount` decimal(10,2) DEFAULT NULL,
      `start_date` datetime DEFAULT NULL,
      `end_date` datetime DEFAULT NULL,
      `usage_limit` int(11) DEFAULT NULL,
      `used_count` int(11) DEFAULT '0',
      `is_active` tinyint(1) DEFAULT '1',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 2. Offers Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `offers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `offer_name` varchar(255) NOT NULL,
      `description` text DEFAULT NULL,
      `offer_type` enum('product','category','cart') NOT NULL DEFAULT 'product',
      `offer_value` decimal(10,2) NOT NULL,
      `start_date` datetime DEFAULT NULL,
      `end_date` datetime DEFAULT NULL,
      `is_active` tinyint(1) DEFAULT '1',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 3. Settings Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `setting_key` varchar(100) NOT NULL UNIQUE,
      `setting_value` text DEFAULT NULL,
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 4. Policies Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `policies` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL UNIQUE,
      `type` varchar(50) DEFAULT 'custom',
      `short_description` text DEFAULT NULL,
      `content` longtext DEFAULT NULL,
      `status` enum('draft','published') DEFAULT 'draft',
      `visibility` enum('public','private') DEFAULT 'public',
      `is_featured` tinyint(1) DEFAULT '0',
      `display_order` int(11) DEFAULT '0',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 5. Age Verifications Table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `age_verifications` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `document_type` varchar(50) NOT NULL,
      `document_number` varchar(100) NOT NULL,
      `document_front_url` varchar(255) NOT NULL,
      `document_back_url` varchar(255) DEFAULT NULL,
      `status` enum('pending','approved','rejected') DEFAULT 'pending',
      `review_notes` text DEFAULT NULL,
      `reviewed_by` int(11) DEFAULT NULL,
      `reviewed_at` datetime DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    echo "<h1>Migration Successful!</h1>";
    echo "<p>All 5 required database tables (coupons, offers, settings, policies, age_verifications) have been created successfully on your database.</p>";
    echo "<p>You can now test the API endpoints.</p>";

} catch (Exception $e) {
    echo "<h1>Migration Failed!</h1>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
