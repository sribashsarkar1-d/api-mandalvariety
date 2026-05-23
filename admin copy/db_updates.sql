USE `mondal-vr`;
ALTER TABLE `products` ADD COLUMN `attributes` LONGTEXT DEFAULT NULL;
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`) VALUES 
('Shoes', 'shoes', 'Footwear and shoes'),
('Grocery', 'grocery', 'Daily groceries and snacks');
