CREATE TABLE IF NOT EXISTS `product_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `tag_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `search_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `query` varchar(255) NOT NULL,
  `normalized_query` varchar(255) NOT NULL,
  `result_count` int(11) DEFAULT 0,
  `search_type` enum('global','voice','products','categories') DEFAULT 'global',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `normalized_query` (`normalized_query`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Optional Full-text indexing for better performance on large datasets
-- Note: Requires MySQL/MariaDB version that supports InnoDB Full-Text
-- ALTER TABLE `products` ADD FULLTEXT INDEX `ft_name_desc` (`name`, `description`);
