-- ---------------------------------------------------------------------------
-- Bangla Shop (Laravel e-commerce assignment) - database export
-- Engine: MySQL 8.0 / utf8mb4_unicode_ci
-- Contains: schema + demo data (admin user, 3 categories, 8 subcategories,
-- 22 products, 6 orders with tracking tokens, e.g. DEMO-0001-TRK1).  Admin login: admin@example.com / password
-- Product images referenced here live in storage/app/public/products
-- (run `php artisan storage:link` once so /storage/... URLs work).
--
-- Import:  mysql -u root -p laravel_ecommerce < database/sql/ecommerce.sql
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `subcategories`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subcategories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subcategories_slug_unique` (`slug`),
  UNIQUE KEY `subcategories_category_id_name_unique` (`category_id`,`name`),
  CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `subcategory_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(170) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_price` decimal(10,2) NOT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_subcategory_id_created_at_index` (`subcategory_id`,`created_at`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tracking_token` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int unsigned NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `payment_method` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cod',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_tracking_token_unique` (`tracking_token`),
  KEY `orders_product_id_foreign` (`product_id`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  CONSTRAINT `orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_11_05_100000_create_categories_table', 1),
(2, '2026_11_05_100100_create_subcategories_table', 1),
(3, '2026_11_05_100200_create_products_table', 1),
(4, '2026_11_06_100000_create_users_table', 1),
(5, '2026_11_06_100100_create_orders_table', 1),
(6, '2026_11_07_100000_add_tracking_token_to_orders_table', 1);

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'electronics', 'Everything in Electronics.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(2, 'Fashion', 'fashion', 'Everything in Fashion.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(3, 'Home & Living', 'home-living', 'Everything in Home & Living.', '2026-11-06 10:00:00', '2026-11-06 10:00:00');

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mobile Phones', 'mobile-phones', 'Mobile Phones from our Electronics range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(2, 1, 'Laptops', 'laptops', 'Laptops from our Electronics range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(3, 1, 'Accessories', 'accessories', 'Accessories from our Electronics range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(4, 2, 'Men\'s Wear', 'mens-wear', 'Men\'s Wear from our Fashion range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(5, 2, 'Women\'s Wear', 'womens-wear', 'Women\'s Wear from our Fashion range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(6, 2, 'Footwear', 'footwear', 'Footwear from our Fashion range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(7, 3, 'Furniture', 'furniture', 'Furniture from our Home & Living range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(8, 3, 'Kitchen', 'kitchen', 'Kitchen from our Home & Living range.', '2026-11-06 10:00:00', '2026-11-06 10:00:00');

INSERT INTO `users` (`id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@example.com', '$2y$10$s.K2GCwARzX3XK6QPo1xvuokfaHi4xJxF7Xl1BpkHVcSHccx0lxeG', NULL, '2026-11-06 10:00:00', '2026-11-06 10:00:00');

INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `name`, `slug`, `description`, `image`, `old_price`, `new_price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Galaxy Nova 5G', 'galaxy-nova-5g', 'A 6.5 inch AMOLED smartphone with a 50MP triple camera, 8GB RAM and a two-day battery.', 'products/galaxy-nova-5g-seed1.jpg', 42990.00, 38990.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(2, 1, 1, 'Pixel Lite 128GB', 'pixel-lite-128gb', 'Compact Android phone with clean software, fast charging and a bright OLED screen.', 'products/pixel-lite-128gb-seed2.jpg', 31500.00, 28999.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(3, 1, 1, 'Redmi Note Max', 'redmi-note-max', 'Big-battery phone built for streaming and gaming with a 120Hz display.', 'products/redmi-note-max-seed3.jpg', 24999.00, 22499.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(4, 1, 2, 'UltraBook Air 14', 'ultrabook-air-14', 'Thin and light 14 inch laptop with Core i5, 16GB RAM and a 512GB NVMe SSD.', 'products/ultrabook-air-14-seed4.jpg', 89990.00, 82500.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(5, 1, 2, 'WorkStation Pro 15', 'workstation-pro-15', 'Powerful 15.6 inch laptop for developers with Ryzen 7, 32GB RAM and a 1TB SSD.', 'products/workstation-pro-15-seed5.jpg', 124000.00, 115000.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(6, 1, 2, 'Student Book 11', 'student-book-11', 'Affordable 11 inch laptop that is perfect for classes, notes and browsing.', 'products/student-book-11-seed6.jpg', 36500.00, 33900.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(7, 1, 3, 'Wireless Earbuds Z', 'wireless-earbuds-z', 'Bluetooth 5.3 earbuds with noise cancellation and a 24 hour charging case.', 'products/wireless-earbuds-z-seed7.jpg', 4500.00, 3499.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(8, 1, 3, 'Fast Charger 65W', 'fast-charger-65w', 'GaN USB-C wall charger that powers a phone and a laptop at the same time.', 'products/fast-charger-65w-seed8.jpg', 2200.00, 1799.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(9, 1, 3, 'Mechanical Keyboard K2', 'mechanical-keyboard-k2', 'Hot-swappable 75 percent mechanical keyboard with tactile switches.', 'products/mechanical-keyboard-k2-seed9.jpg', 7800.00, 6999.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(10, 2, 4, 'Classic Cotton Panjabi', 'classic-cotton-panjabi', 'Soft breathable cotton panjabi with fine embroidery, ideal for festive days.', 'products/classic-cotton-panjabi-seed10.jpg', 3200.00, 2699.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(11, 2, 4, 'Slim Fit Denim Jeans', 'slim-fit-denim-jeans', 'Stretch denim jeans with a modern slim fit and reinforced stitching.', 'products/slim-fit-denim-jeans-seed11.jpg', 2800.00, 2299.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(12, 2, 4, 'Polo T-Shirt Navy', 'polo-t-shirt-navy', 'Premium pique polo shirt that stays fresh all day.', 'products/polo-t-shirt-navy-seed12.jpg', 1500.00, 1199.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(13, 2, 5, 'Handloom Silk Saree', 'handloom-silk-saree', 'Elegant handloom silk saree with a rich zari border and matching blouse piece.', 'products/handloom-silk-saree-seed13.jpg', 9800.00, 8400.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(14, 2, 5, 'Printed Cotton Kurti', 'printed-cotton-kurti', 'Lightweight printed kurti, comfortable for work and casual outings.', 'products/printed-cotton-kurti-seed14.jpg', 1800.00, 1450.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(15, 2, 5, 'Winter Wool Shawl', 'winter-wool-shawl', 'Warm and soft wool shawl with a subtle geometric pattern.', 'products/winter-wool-shawl-seed15.jpg', 2600.00, 2199.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(16, 2, 6, 'Leather Formal Shoes', 'leather-formal-shoes', 'Handcrafted genuine leather shoes with a cushioned insole.', 'products/leather-formal-shoes-seed16.jpg', 4800.00, 4199.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(17, 2, 6, 'Running Sneakers X', 'running-sneakers-x', 'Breathable mesh running shoes with a responsive foam sole.', 'products/running-sneakers-x-seed17.jpg', 5200.00, 4499.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(18, 3, 7, 'Oak Study Table', 'oak-study-table', 'Solid wood study table with a cable hole and two side drawers.', 'products/oak-study-table-seed18.jpg', 18500.00, 16200.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(19, 3, 7, 'Ergonomic Office Chair', 'ergonomic-office-chair', 'Mesh back chair with adjustable lumbar support and 4D armrests.', 'products/ergonomic-office-chair-seed19.jpg', 14900.00, 12999.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(20, 3, 8, 'Non-stick Cookware Set', 'non-stick-cookware-set', 'Seven piece non-stick cookware set that works on gas and induction.', 'products/non-stick-cookware-set-seed20.jpg', 6900.00, 5799.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(21, 3, 8, 'Electric Rice Cooker 1.8L', 'electric-rice-cooker-18l', 'Automatic rice cooker with keep-warm function and a steaming tray.', 'products/electric-rice-cooker-18l-seed21.jpg', 3900.00, 3299.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00'),
(22, 3, 8, 'Stainless Water Bottle', 'stainless-water-bottle', 'Double wall insulated bottle that keeps drinks cold for 24 hours.', 'products/stainless-water-bottle-seed22.jpg', 1200.00, 899.00, '2026-11-06 10:00:00', '2026-11-06 10:00:00');


INSERT INTO `orders` (`id`, `order_number`, `tracking_token`, `product_id`, `product_name`, `unit_price`, `quantity`, `total`, `customer_name`, `phone`, `email`, `address`, `note`, `payment_method`, `status`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 'BS-DEMO-0001', 'DEMO-0001-TRK1', 1, 'Galaxy Nova 5G', 38990.00, 1, 38990.00, 'Rahim Uddin', '01712345678', NULL, 'House 12, Road 5, Dhanmondi, Dhaka', NULL, 'bkash', 'delivered', 'paid', '2026-11-01 11:30:00', '2026-11-01 11:30:00'),
(2, 'BS-DEMO-0002', 'DEMO-0002-TRK2', 7, 'Wireless Earbuds Z', 3499.00, 2, 6998.00, 'Nusrat Jahan', '01812345678', NULL, 'Flat 4B, Agrabad C/A, Chattogram', NULL, 'cod', 'shipped', 'unpaid', '2026-11-02 11:30:00', '2026-11-02 11:30:00'),
(3, 'BS-DEMO-0003', 'DEMO-0003-TRK3', 13, 'Handloom Silk Saree', 8400.00, 1, 8400.00, 'Farzana Akter', '01912345678', NULL, 'Zindabazar, Sylhet Sadar, Sylhet', NULL, 'nagad', 'packaging', 'paid', '2026-11-03 11:30:00', '2026-11-03 11:30:00'),
(4, 'BS-DEMO-0004', 'DEMO-0004-TRK4', 19, 'Ergonomic Office Chair', 12999.00, 1, 12999.00, 'Tanvir Hasan', '01612345678', NULL, 'Station Road, Rajshahi Sadar, Rajshahi', NULL, 'bank_transfer', 'confirmed', 'unpaid', '2026-11-04 11:30:00', '2026-11-04 11:30:00'),
(5, 'BS-DEMO-0005', 'DEMO-0005-TRK5', 9, 'Mechanical Keyboard K2', 6999.00, 3, 20997.00, 'Sabbir Ahmed', '01512345678', NULL, 'Sector 7, Uttara, Dhaka', NULL, 'cod', 'pending', 'unpaid', '2026-11-05 11:30:00', '2026-11-05 11:30:00'),
(6, 'BS-DEMO-0006', 'DEMO-0006-TRK6', 17, 'Running Sneakers X', 4499.00, 1, 4499.00, 'Mim Chowdhury', '01312345678', NULL, 'Boro Bazar, Khulna Sadar, Khulna', NULL, 'bkash', 'cancelled', 'refunded', '2026-11-06 11:30:00', '2026-11-06 11:30:00');

SET FOREIGN_KEY_CHECKS = 1;
