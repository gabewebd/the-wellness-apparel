-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 09:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wellness_apparel`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `request_action` enum('ship','deliver','cancel') DEFAULT NULL,
  `status` enum('unread','read','archived') DEFAULT 'unread',
  `sender_type` enum('user','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderline`
--

CREATE TABLE `orderline` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderline`
--

INSERT INTO `orderline` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(5, 5, 9, 1, 59.99);

-- --------------------------------------------------------

--
-- Table structure for table `orderline_backup`
--

CREATE TABLE `orderline_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderline_backup`
--

INSERT INTO `orderline_backup` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(3, 5, 1, NULL, 1, 12.00),
(4, 19, NULL, NULL, 1, 1123.00),
(5, 20, NULL, NULL, 3, 1123.00),
(6, 21, NULL, NULL, 4, 479.00),
(7, 22, NULL, NULL, 4, 1123.00),
(8, 22, NULL, NULL, 3, 479.00),
(9, 23, NULL, NULL, 1, 479.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `user_address_id` varchar(500) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_option` enum('Standard','Express') NOT NULL DEFAULT 'Standard',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `user_address_id`, `payment_method`, `created_at`, `delivery_option`, `shipping_fee`) VALUES
(5, 15, 309.99, 'Delivered', '4', 'PayPal', '2025-03-28 01:04:20', 'Express', 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `images` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `images`, `created_at`) VALUES
(9, 'Motion Pro Track Jacket', 'A lightweight, breathable track jacket designed for both casual wear and active movement. Featuring a sleek white design with navy blue accents, this jacket brings a sporty yet stylish touch to your wardrobe.\r\n\r\nFeatures: Water-resistant fabric, elastic cuffs, zip-up front, and a comfortable relaxed fit.', 59.99, 5, '67e65d8f14517.jpg', '2025-03-28 08:01:09'),
(10, 'Preppy Striped Knit Sweater', 'A stylish and modern take on classic collegiate fashion, this preppy striped knit sweater features bold, vibrant colors and an embroidered crest detail for a sophisticated yet casual look. Layer it over a button-up shirt for a refined outfit or wear it solo for a relaxed, effortless vibe.\r\n\r\nMaterial: High-quality cotton blend with soft acrylic fibers\r\n\r\nFeatures:\r\nBold multicolor striped design\r\nRibbed cuffs and hem for a snug fit\r\nEmbroidered crest for a premium touch\r\nComfortable and breathable fabric', 390.00, 5, '67e65dbf3d453.jpg', '2025-03-28 08:24:21'),
(11, 'Cozy Cable Knit Sweater', 'This oversized cable knit sweater is the perfect blend of comfort and elegance. Made from ultra-soft, thick yarn, it provides warmth while maintaining a relaxed, stylish silhouette. The intricate cable patterns add a classic touch, making it a must-have for the fall and winter seasons.\r\n\r\nMaterial: Premium wool blend with soft acrylic fibers\r\n\r\nFeatures:\r\nChunky knit for maximum warmth\r\nOversized fit for a cozy and relaxed feel\r\nHigh neckline for added comfort\r\nLong sleeves with extended cuffs for a stylish touch', 590.00, 34, '67e65db78ba5d.jpg', '2025-03-28 08:24:50'),
(12, 'Cozy Flex Lounge Set', 'A relaxed yet stylish lounge set featuring a cropped tube top, oversized open-front cardigan, and high-waisted joggers. The perfect balance of comfort and effortless street style.\r\n\r\nFeatures:\r\nBreathable cotton blend\r\nsoft-touch fabric\r\nelastic waistband with adjustable drawstrings', 200.00, 45, '67e65dc899a15.jpg', '2025-03-28 08:25:40'),
(13, 'Cottagecore Chic Suspender Dress', 'A soft, elegant suspender dress featuring a delicate cream blouse layered under a deep navy pinafore. The outfit embodies a perfect blend of vintage charm and modern sophistication, ideal for casual meetups, work, or special occasions.\r\n\r\nFeatures:\r\nHigh-quality chiffon blouse with subtle frills\r\nAdjustable suspender straps for a perfect fit\r\nA-line silhouette for a flattering shape\r\nComfortable and breathable fabric', 456.00, 20, '67e65d96411b5.jpg', '2025-03-28 08:26:47'),
(14, 'Wool Overcoat Turtleneck Set', 'Elevate your winter wardrobe with this sophisticated wool overcoat and ribbed turtleneck set, inspired by classic Korean fashion. This ensemble exudes elegance and warmth, making it a perfect choice for formal events, casual strolls, or date nights in the colder seasons.\r\n\r\nIncluded Items:\r\nPremium Wool Overcoat (Double-breasted, tailored fit, soft neutral beige)\r\nRibbed Knit Turtleneck Sweater (Thick, warm, and stylish in ivory white)\r\n\r\nFeatures:\r\nHigh-quality wool blend for exceptional warmth\r\nDouble-breasted button closure for a timeless aesthetic\r\nWide lapels for a bold, structured look\r\nRibbed turtleneck for a cozy yet refined touch', 3400.00, 3, '67e65da917487.jpg', '2025-03-28 08:27:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `display_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `display_name`, `created_at`, `is_admin`) VALUES
(1, 'lakeqing', 'gabaivlsqz@gmail.com', '$2y$10$c7tY7ek0t1ROIQ3UkDPpQ.3n.hq4Pj9603Ykdc0yVsAs.uu0m1DVK', 'ainsh', '2025-03-24 06:07:44', 1),
(15, 'AglzJsh', 'shirley_aguiluz27@yahoo.com.ph', '$2y$10$7.oF2gbuJTUVI/oZ50b8deFPxE0H3BYJvf.1NGdvRW2HMtAgJKi9O', 'Dave', '2025-03-27 16:51:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `street_address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `full_name`, `street_address`, `city`, `province`, `zip_code`, `created_at`) VALUES
(4, 15, 'Shirley', 'Holy', 'Angeles', 'Pamp', '2009', '2025-03-28 08:04:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_recipient_status` (`recipient_id`,`status`),
  ADD KEY `idx_sender_type` (`sender_type`);

--
-- Indexes for table `orderline`
--
ALTER TABLE `orderline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orderline`
--
ALTER TABLE `orderline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orderline`
--
ALTER TABLE `orderline`
  ADD CONSTRAINT `orderline_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `orderline_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
