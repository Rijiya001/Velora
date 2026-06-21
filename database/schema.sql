-- VELORA Luxury Showcase Platform - Database Schema (Separated Users and Admins)
-- Compatible with MySQL / MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Drop tables in order of foreign key dependencies to avoid constraints issues
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;

-- --------------------------------------------------------

--
-- Table structure for table `users` (Store only customer data)
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping default customer accounts (Password: 'user123')
--
INSERT INTO `users` (`id`, `fullname`, `email`, `phone`, `password`, `status`) VALUES
(1, 'Test Customer', 'customer@velora.com', '9865480192', '$2y$10$xwsVCH9QCn81cR/e9iUScOMzkeIFxOBEUxGPUXA1XGERhvYouqpiK', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `admins` (Store only administrator and staff logins)
--
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin', 'superadmin') NOT NULL DEFAULT 'admin',
  `status` enum('active', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping default admin accounts (Password: 'admin123')
--
INSERT INTO `admins` (`id`, `fullname`, `email`, `phone`, `password`, `role`, `status`) VALUES
(1, 'Super Admin', 'superadmin@velora.com', '9865480190', '$2y$10$uXipdbZnz6Csoa06zA4P..8XU1FPMVsfi4CHsjlfKGRmrw4mDxWRW', 'superadmin', 'active'),
(2, 'Concierge Admin', 'admin@velora.com', '9865480191', '$2y$10$uXipdbZnz6Csoa06zA4P..8XU1FPMVsfi4CHsjlfKGRmrw4mDxWRW', 'admin', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--
CREATE TABLE IF NOT EXISTS `collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping collections
--
INSERT INTO `collections` (`id`, `name`, `description`, `banner_image`) VALUES
(1, 'Imperial Gold Collection', 'Heirloom creations designed with pure 24K Nepalese gold, combining classical heritage with contemporary royalty.', 'designs/design3.jpg'),
(2, 'Celestial Silver Archive', 'Timeless sterling silver anklets, bands, and custom accessories inspired by celestial geometries.', 'designs/design4.jpg')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `material` varchar(100) NOT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `product_code` varchar(50) NOT NULL UNIQUE,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_trending` tinyint(1) NOT NULL DEFAULT '0',
  `is_new_arrival` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` int(11) NOT NULL DEFAULT '0',
  `status` enum('active', 'archived') NOT NULL DEFAULT 'active',
  `main_image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping catalog items
--
INSERT INTO `products` (`id`, `name`, `collection_id`, `category`, `description`, `material`, `weight`, `product_code`, `is_featured`, `is_trending`, `is_new_arrival`, `main_image`) VALUES
(1, 'Heirloom Necklace', 1, 'Necklace', 'Stunning 24K gold necklace with intricate hand-carved heritage details.', '24K Gold', '20gm', '#01', 1, 1, 1, 'designs/necklace1.jpg'),
(2, 'Elegance Ring Band', 1, 'Rings', 'Sleek luxury wedding ring band featuring diamond-cut gold facets.', '23K Gold', '20gm', '#02', 1, 0, 1, 'designs/rings1.jpg'),
(3, 'Classic Silver Anklet', 2, 'Anklets', 'Sterling silver anklet showcasing handcrafted traditional Nepali floral links.', 'Pure Silver', '20gm', '#03', 0, 1, 0, 'designs/anklet1.jpg'),
(4, 'Sovereign Mangalsutra', 1, 'Mangalsutra', 'Exquisite gold mangalsutra showcasing gold beadwork and signature designs.', '24K Gold', '20gm', '#04', 1, 0, 0, 'designs/mangalsutra1.jpg'),
(5, 'Traditional Naugedi', 1, 'Naugedi', 'Traditional Nepali Naugedi design crafted with red threads and nine gold beads.', '24K Gold', '11.11gm', '#05', 0, 0, 0, 'designs/naugedi1.jpg'),
(6, 'Lunar Chandrama', 1, 'Chandrama', 'Timeless crescent moon gold pendant showcasing heritage engraving.', '24K Gold', '15gm', '#06', 1, 1, 1, 'designs/chandrama1.jpg'),
(7, 'Sovereign Bangles', 1, 'Bangles', 'Solid gold bangles designed with floral textures for everyday luxury.', '24K Gold', '20gm', '#07', 0, 0, 0, 'designs/bangles1.jpg'),
(8, 'Classic Gold Bracelet', 1, 'Bracelets', 'Elegantly linked gold wristlet chain designed with custom security clasp.', '22K Gold', '22gm', '#08', 0, 1, 0, 'designs/bracelets1.jpg'),
(9, 'Elegance Stud Earrings', 1, 'Ear Rings', 'Minimal gold studs featuring delicate traditional engravings.', '24K Gold', '20gm', '#09', 1, 0, 0, 'designs/ear rings1.jpg')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread', 'read', 'replied') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_email` varchar(255) NOT NULL,
  `operator_role` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `website_settings`
--
CREATE TABLE IF NOT EXISTS `website_settings` (
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping settings (Editable from Admin dashboard)
--
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('brand_name', 'Velora', 'general'),
('hero_title', 'Give your looks\r\nA New Style', 'homepage'),
('hero_subtitle', '\"Make jewellery contact before eyes contact\"', 'homepage'),
('hero_image', 'designs/design3.jpg', 'homepage'),
('about_story', 'Velora is one of the oldest and most trusted jewelry houses located in Huprachaur, Hetauda. For over 20 years, our master artisans have blended classical Nepalese heritage with contemporary designs, offering an unmatched experience in bespoke gold and silver creations.', 'about'),
('about_mission', 'To craft timeless, ethical heirloom pieces that embody quiet luxury, unmatched Nepalese design craftsmanship, and structural integrity.', 'about'),
('about_vision', 'To establish Velora as the premier luxury showcase destination, globally representing authentic Nepalese jewelry heritage.', 'about'),
('contact_address', 'Huprachaur, Hetauda-4, Makwanpur, Nepal', 'contact'),
('contact_email', 'concierge@velorajewelry.com', 'contact'),
('contact_phone', '+977 057 91827', 'contact'),
('contact_hours', 'Sunday - Friday, 10:00 AM - 6:00 PM', 'contact'),
('promo_banner_text', 'Subscribe for Instant Updates on New Jewelry Launches & Private Showings', 'homepage')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping mock gallery files
--
INSERT INTO `gallery` (`id`, `image_path`, `title`, `description`) VALUES
(1, 'designs/necklace1.jpg', 'Imperial Golden Heirloom', 'Signature campaign gold close-up'),
(2, 'designs/rings1.jpg', 'Celestial Bands', 'Handmade diamond-cut textures'),
(3, 'designs/anklet1.jpg', 'Geometrical Silver Chains', 'Polished floral link patterns'),
(4, 'designs/design4.jpg', 'Artisan Atelier Work', 'A master goldsmith refining detail')
ON DUPLICATE KEY UPDATE `id`=`id`;

COMMIT;
