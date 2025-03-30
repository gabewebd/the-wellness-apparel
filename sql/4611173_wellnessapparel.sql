-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: fdb1029.awardspace.net
-- Generation Time: Mar 30, 2025 at 09:19 AM
-- Server version: 8.0.32
-- PHP Version: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `4611173_wellnessapparel`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `selector` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `hashed_validator` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `recipient_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `request_action` enum('ship','deliver','cancel') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('unread','read','archived') COLLATE utf8mb4_general_ci DEFAULT 'unread',
  `sender_type` enum('user','admin') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `sender_id`, `recipient_id`, `order_id`, `message`, `request_action`, `status`, `sender_type`, `created_at`) VALUES
(6, 31, 1, NULL, 'This Website is so Cool', NULL, 'read', 'user', '2025-03-28 12:27:27'),
(7, 15, 1, 11, 'cancel it', 'cancel', 'read', 'user', '2025-03-28 12:39:10'),
(8, 15, 1, 10, 'cancel', 'cancel', 'read', 'user', '2025-03-28 12:43:02'),
(9, 15, 1, 9, 'dsadsa', 'ship', 'read', 'user', '2025-03-28 12:46:57'),
(10, 15, 1, 9, 'sdsad', 'ship', 'unread', 'user', '2025-03-28 12:49:44'),
(11, 15, 1, 9, 'sdsad', 'ship', 'unread', 'user', '2025-03-28 12:49:45'),
(12, 15, 1, 17, 'please deliver my order now', 'deliver', 'unread', 'user', '2025-03-28 15:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `orderline`
--

CREATE TABLE `orderline` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderline`
--

INSERT INTO `orderline` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(5, 5, 9, 1, 59.99),
(9, 9, 13, 3, 456.00),
(10, 10, 13, 2, 456.00),
(11, 11, 9, 2, 59.99),
(15, 15, 14, 1, 3400.00),
(16, 15, 13, 4, 456.00),
(17, 16, 15, 3, 230.00),
(18, 17, 15, 9, 230.00),
(19, 17, 14, 2, 3400.00),
(20, 18, 19, 1, 500.00),
(21, 19, 19, 1, 500.00),
(22, 20, 21, 5, 21.00),
(23, 21, 21, 16, 21.00),
(24, 22, 22, 4, 21.00),
(25, 23, 23, 5, 21.00),
(26, 24, 24, 1, 456.00),
(27, 25, 19, 2, 500.00),
(28, 26, 9, 3, 59.99);

-- --------------------------------------------------------

--
-- Table structure for table `orderline_backup`
--

CREATE TABLE `orderline_backup` (
  `id` int NOT NULL DEFAULT '0',
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int NOT NULL,
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
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Shipped','Delivered','Cancelled') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `user_address_id` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivery_option` enum('Standard','Express') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Standard',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `user_address_id`, `payment_method`, `created_at`, `delivery_option`, `shipping_fee`) VALUES
(5, 15, 309.99, 'Delivered', '4', 'PayPal', '2025-03-28 01:04:20', 'Express', 250.00),
(9, 15, 1468.00, 'Delivered', '7', 'GCash', '2025-03-28 12:35:32', 'Standard', 100.00),
(10, 15, 1012.00, 'Cancelled', '4', 'Credit/Debit Card', '2025-03-28 12:36:24', 'Standard', 100.00),
(11, 15, 219.98, 'Cancelled', '4', 'Credit/Debit Card', '2025-03-28 12:38:37', 'Standard', 100.00),
(15, 15, 5324.00, 'Delivered', '7', 'Credit/Debit Card', '2025-03-28 13:21:31', 'Standard', 100.00),
(16, 36, 940.00, 'Cancelled', '10', 'Credit/Debit Card', '2025-03-28 14:52:57', 'Express', 250.00),
(17, 15, 9120.00, 'Delivered', '11', 'GCash', '2025-03-28 15:37:38', 'Express', 250.00),
(18, 15, 600.00, 'Cancelled', '4', 'Credit/Debit Card', '2025-03-28 15:41:00', 'Standard', 100.00),
(19, 15, 600.00, 'Delivered', '4', 'Credit/Debit Card', '2025-03-28 15:43:16', 'Standard', 100.00),
(20, 15, 205.00, 'Delivered', '11', 'Credit/Debit Card', '2025-03-28 15:53:12', 'Standard', 100.00),
(21, 15, 586.00, 'Delivered', '11', 'GCash', '2025-03-28 16:05:54', 'Express', 250.00),
(22, 15, 184.00, 'Delivered', '7', 'PayPal', '2025-03-28 16:09:53', 'Standard', 100.00),
(23, 15, 205.00, 'Delivered', '7', 'GCash', '2025-03-28 16:13:31', 'Standard', 100.00),
(24, 15, 556.00, 'Cancelled', '4', 'Credit/Debit Card', '2025-03-28 16:17:34', 'Standard', 100.00),
(25, 15, 1100.00, 'Delivered', '4', 'Credit/Debit Card', '2025-03-28 16:21:16', 'Standard', 100.00),
(26, 15, 429.97, 'Delivered', '4', 'Credit/Debit Card', '2025-03-29 05:06:47', 'Express', 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `images` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `images`, `created_at`, `is_active`) VALUES
(9, 'Motion Pro Track Jacket', 'A lightweight, breathable track jacket designed for both casual wear and active movement. Featuring a sleek white design with navy blue accents, this jacket brings a sporty yet stylish touch to your wardrobe.\r\n\r\nFeatures: Water-resistant fabric, elastic cuffs, zip-up front, and a comfortable relaxed fit.', 650.00, 3, '67e65d8f14517.jpg', '2025-03-28 08:01:09', 1),
(10, 'Preppy Striped Knit Sweater', 'A stylish and modern take on classic collegiate fashion, this preppy striped knit sweater features bold, vibrant colors and an embroidered crest detail for a sophisticated yet casual look. Layer it over a button-up shirt for a refined outfit or wear it solo for a relaxed, effortless vibe.\r\n\r\nMaterial: High-quality cotton blend with soft acrylic fibers\r\n\r\nFeatures:\r\nBold multicolor striped design\r\nRibbed cuffs and hem for a snug fit\r\nEmbroidered crest for a premium touch\r\nComfortable and breathable fabric', 390.00, 0, '67e65dbf3d453.jpg', '2025-03-28 08:24:21', 1),
(11, 'Cozy Cable Knit Sweater', 'This oversized cable knit sweater is the perfect blend of comfort and elegance. Made from ultra-soft, thick yarn, it provides warmth while maintaining a relaxed, stylish silhouette. The intricate cable patterns add a classic touch, making it a must-have for the fall and winter seasons.\r\n\r\nMaterial: Premium wool blend with soft acrylic fibers\r\n\r\nFeatures:\r\nChunky knit for maximum warmth\r\nOversized fit for a cozy and relaxed feel\r\nHigh neckline for added comfort\r\nLong sleeves with extended cuffs for a stylish touch', 590.00, 0, '67e65db78ba5d.jpg', '2025-03-28 08:24:50', 1),
(12, 'Cozy Flex Lounge Set', 'A relaxed yet stylish lounge set featuring a cropped tube top, oversized open-front cardigan, and high-waisted joggers. The perfect balance of comfort and effortless street style.\r\n\r\nFeatures:\r\nBreathable cotton blend\r\nsoft-touch fabric\r\nelastic waistband with adjustable drawstrings', 200.00, 3, '67e65dc899a15.jpg', '2025-03-28 08:25:40', 1),
(13, 'Cottagecore Chic Suspender Dress', 'A soft, elegant suspender dress featuring a delicate cream blouse layered under a deep navy pinafore. The outfit embodies a perfect blend of vintage charm and modern sophistication, ideal for casual meetups, work, or special occasions.\r\n\r\nFeatures:\r\nHigh-quality chiffon blouse with subtle frills\r\nAdjustable suspender straps for a perfect fit\r\nA-line silhouette for a flattering shape\r\nComfortable and breathable fabric', 460.00, 0, '67e65d96411b5.jpg', '2025-03-28 08:26:47', 1),
(14, 'Wool Overcoat Turtleneck Set', 'Elevate your winter wardrobe with this sophisticated wool overcoat and ribbed turtleneck set, inspired by classic Korean fashion. This ensemble exudes elegance and warmth, making it a perfect choice for formal events, casual strolls, or date nights in the colder seasons.\r\n\r\nIncluded Items:\r\nPremium Wool Overcoat (Double-breasted, tailored fit, soft neutral beige)\r\nRibbed Knit Turtleneck Sweater (Thick, warm, and stylish in ivory white)\r\n\r\nFeatures:\r\nHigh-quality wool blend for exceptional warmth\r\nDouble-breasted button closure for a timeless aesthetic\r\nWide lapels for a bold, structured look\r\nRibbed turtleneck for a cozy yet refined touch', 3400.00, 3, '67e65da917487.jpg', '2025-03-28 08:27:29', 1),
(15, 'Everyday Essential Oversized Tee', 'Description: A relaxed-fit oversized t-shirt made from organic cotton for everyday wear. Pairs well with joggers, jeans, or leggings for a cool and effortless look.\r\n\r\nFeatures: 100% cotton, ultra-soft touch, unisex design.', 230.00, 12, '67e69cc324aa1.jpg', '2025-03-28 12:57:39', 1),
(19, 'Urban Edge Leather Jacket Outfit', 'This stylish streetwear ensemble combines bold, edgy elements with layered fashion for a modern, effortlessly cool look. Perfect for casual outings, travel, or fashion-forward styling.\r\n\r\nIncluded Items:\r\nJacket: Black leather biker jacket with silver zipper details.\r\nShirt: Yellow-and-black plaid flannel shirt for a layered, warm touch.\r\nInnerwear: Classic white T-shirt for a clean, versatile base.\r\n\r\nAccessories:\r\nNavy blue beanie with crisscross stitch design.\r\nBlack sunglasses for a sleek, mysterious vibe.\r\nSilver rings for added style.\r\nBlack shoulder bag for a practical yet stylish touch.\r\nBottoms: Black fitted pants with a silver belt buckle to complete the look.', 500.00, 3, 'prod_67e6c2edc5f54.jpg', '2025-03-28 15:40:29', 0),
(21, 'dsad', 'adsad', 21.00, 0, NULL, '2025-03-28 15:51:49', 0),
(22, 'dsad', 'dasd', 21.00, 17, NULL, '2025-03-28 16:09:34', 0),
(23, 'ainsh', 'dasd', 21.00, 16, NULL, '2025-03-28 16:13:16', 0),
(24, 'eff', 'effeef', 456.00, 44, NULL, '2025-03-28 16:17:22', 0),
(25, 'drgr', 'rdgdr', 45.00, 30, NULL, '2025-03-28 16:17:43', 0),
(26, 'Luxe Activewear Set – Black Edition', 'This sleek and stylish activewear set is designed for both performance and fashion, ensuring maximum comfort and a flattering fit. Ideal for workouts, yoga, or casual wear.\r\n\r\nProduct Details:\r\nTop: Black cropped sports tank with a high-neck design, providing support and a chic, minimalistic look.\r\n\r\nLeggings: High-waisted black leggings with a smooth, stretchy fit for ultimate comfort and flexibility. \r\n\r\nFootwear: White and pink athletic sneakers with cushioned soles for optimal support, perfect for both training and casual outings.', 1500.00, 10, 'prod_67e8fefd9cbad.png', '2025-03-30 08:21:17', 1),
(27, 'Comfort Oversized Hoodie', 'Stay effortlessly stylish and comfortable with our Urban Comfort Oversized Hoodie. Designed for everyday wear, this ultra-cozy hoodie features a relaxed fit, soft fleece fabric, and an adjustable drawstring hood. Whether you\'re traveling, lounging, or running errands, this hoodie is your go-to for casual chic.\r\n\r\nFeatures:\r\n\r\nPremium cotton-blend fabric for ultimate comfort\r\n\r\nOversized fit for a relaxed and trendy look\r\n\r\nSoft inner fleece lining for extra warmth\r\n\r\nFunctional front pockets for convenience\r\n\r\nUnisex design, perfect for layering', 430.00, 45, 'prod_67e901a5009f5.jpg', '2025-03-30 08:31:53', 1),
(28, 'Vintage Sport Knit Polo', 'Channel effortless retro vibes with our Vintage Sport Knit Polo. This eye-catching piece features a bold contrast of deep blue with striped accents and embroidered detailing for a nostalgic yet modern feel. Designed for both comfort and statement-making style, it’s perfect for casual outings, layering, or expressing your unique fashion sense.\r\n\r\nFeatures:\r\nSoft, breathable knit fabric for all-day comfort\r\nVintage-inspired striped cuffs and collar for a sporty touch\r\nRelaxed fit with a slightly oversized silhouette\r\nEmbroidered lettering for a premium finish\r\nVersatile unisex design', 1500.00, 4, 'prod_67e902c7c71ce.jpg', '2025-03-30 08:36:37', 1),
(29, 'Edgy Denim Cropped Jacket', 'Make a bold statement with our Edgy Denim Cropped Jacket, a modern twist on classic denim. Featuring a structured fit with raw, frayed edges and metal button detailing, this jacket adds an effortlessly cool vibe to any look. Pair it with high-waisted skirts, cargo pants, or layered pieces for an ultra-stylish ensemble.\r\n\r\nFeatures:\r\nPremium denim fabric with a structured silhouette\r\nCropped length for a trendy, modern edge\r\nFrayed hems for a distressed, lived-in feel\r\nButton-up closure and functional chest pockets\r\nVersatile styling for casual or high-fashion looks', 2990.00, 30, 'prod_67e9034a62283.jpeg', '2025-03-30 08:39:38', 1),
(30, 'Minimalist Relaxed Polo Shirt', 'Effortless style meets comfort with our Minimalist Relaxed Polo Shirt. Designed with a loose, breathable fit and a soft, flowy drape, this shirt is perfect for casual days or dressed-up evenings. The subtle open collar adds a touch of sophistication while maintaining a laid-back vibe. Pair it with wide-leg trousers, jeans, or shorts for a timeless and polished look.\r\n\r\nFeatures:\r\nLightweight, breathable fabric for all-day comfort\r\nRelaxed, oversized fit for effortless styling\r\nOpen collar design for a modern touch\r\nVersatile and easy to pair with any outfit\r\nAvailable in multiple neutral colors', 1900.00, 45, 'prod_67e9048d69fd1.jpg', '2025-03-30 08:44:14', 1),
(31, 'Classic Varsity Bomber Jacket', 'Add a touch of vintage charm to your wardrobe with our Classic Varsity Bomber Jacket. Designed with a sleek, structured fit and contrasting striped details, this jacket embodies timeless collegiate style. Featuring a soft wool-blend fabric and a snap-button closure, it pairs perfectly with sweaters, jeans, or tailored pants for a smart-casual look.\r\n\r\nFeatures:\r\nPremium wool-blend fabric for warmth and durability\r\nRibbed cuffs, collar, and hem for a snug fit\r\nSnap-button front closure for easy wear\r\nEmbroidered varsity logo detail\r\nVersatile layering piece for any season', 2800.00, 4, 'prod_67e9056873cb6.jpg', '2025-03-30 08:48:40', 1),
(32, 'Effortless Preppy Chic', 'This look is a perfect blend of preppy chic and elegant simplicity, featuring a white collared shirt layered under a camel-colored knit vest with subtle embossed details. The pleated white mini skirt adds a fresh and youthful touch, complementing the overall sophisticated vibe.\r\n\r\nAccessories & Styling:\r\nA black structured handbag with gold hardware enhances the polished look.\r\nMinimalistic black flats keep it effortlessly chic and comfortable.\r\nLoose, wavy hair and soft makeup maintain a natural and refined aesthetic.', 560.00, 23, 'prod_67e907bd5adc5.jpg', '2025-03-30 08:55:16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `display_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `display_name`, `created_at`, `is_admin`) VALUES
(1, 'lakeqing', 'gabaivlsqz@gmail.com', '$2y$10$c7tY7ek0t1ROIQ3UkDPpQ.3n.hq4Pj9603Ykdc0yVsAs.uu0m1DVK', 'ainsh', '2025-03-24 06:07:44', 1),
(15, 'AglzJsh', 'shirley_aguiluz27@yahoo.com.ph', '$2y$10$7.oF2gbuJTUVI/oZ50b8deFPxE0H3BYJvf.1NGdvRW2HMtAgJKi9O', 'daveee', '2025-03-27 16:51:26', 0),
(29, '1234', '100089075411075@gmail.com', '$2y$10$TYv5.7g9sPo7h3MUfSWJs.8xolzWMdAsSDQUPQ0TMJe4MPFvV5kk2', '123', '2025-03-28 12:22:34', 0),
(31, '1234567', '1000890754110751@gmail.com', '$2y$10$zD67MxP3Th0M9Ei0TXB9M.LbBHi387IEmUrNSIlFtDgj/HIdxNMAS', '12345', '2025-03-28 12:24:20', 0),
(33, 'customer23', 'customer2@gmail.com', '$2y$10$ESgssR4UKx8kRot0nMWshu9JKMGobjZR.g0PEkhm5hqhur4gh6Qtu', 'i am a customer23', '2025-03-28 12:54:42', 0),
(36, 'aunds123', 'ellieseaver123@gmail.com', '$2y$10$sGEOAfFK/Lljn3soSRoomeA8dudG3MjkMIk7ixwNqGLEbqap5TrqO', 'aundrea', '2025-03-28 14:45:30', 0),
(37, 'customerako', 'hachuchoi@gmail.com', '$2y$10$NrcC81NSzQrSq4axjzK2V.Dh6OfpETNMBhU7ONbwd.oMCiPpRDEEO', 'i am a customer', '2025-03-29 06:02:49', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `street_address` text COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `province` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `zip_code` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `full_name`, `street_address`, `city`, `province`, `zip_code`, `created_at`) VALUES
(4, 15, 'Shirley', 'Holy', 'Angeles', 'Pamp', '2009', '2025-03-28 08:04:20'),
(7, 15, 'Josh Andrei Aguiluz', '106 San Pedro 2 Magalang Pampanga', 'Magalang', 'Pampanga', '2011', '2025-03-28 12:35:32'),
(10, 36, 'aundrea m.', 'orinj haws avenue', 'biñan', 'laguna', '0712', '2025-03-28 14:52:57'),
(11, 15, 'Josh Andrei Aguiluz', '23 Paseo Lazatin street L&S Subdivision Santo Domingo', 'Angeles City', 'Pampanga', '2009', '2025-03-28 15:37:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orderline`
--
ALTER TABLE `orderline`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `auth_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
