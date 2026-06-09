USE `mondal-vr`;
ALTER TABLE `products` ADD COLUMN `attributes` LONGTEXT DEFAULT NULL;
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`) VALUES 
('Shoes', 'shoes', 'Footwear and shoes'),
('Grocery', 'grocery', 'Daily groceries and snacks');

-- For Forgot Password Feature
ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `reset_token_expires_at` DATETIME DEFAULT NULL;
