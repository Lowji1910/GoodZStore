-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 12:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goodzstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Áo Thun', 'Các loại áo thun nam nữ', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(2, 'Áo Sơ Mi', 'Áo sơ mi công sở và dạo phố', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(3, 'Quần Jeans', 'Quần jeans các loại', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(4, 'Quần Short', 'Quần short mùa hè', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(5, 'Váy Đầm', 'Các loại váy đầm thời trang', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(6, 'Áo Khoác', 'Áo khoác chống nắng, giữ ấm', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(7, 'Giày Dép', 'Giày sneaker, sandal, giày da', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(8, 'Phụ Kiện', 'Túi xách, mũ, kính mắt', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(9, 'Đồ Thể Thao', 'Quần áo thể thao nam nữ', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(10, 'Đồ Bộ', 'Bộ quần áo mặc nhà, pijama', '2025-09-22 10:53:15', '2025-09-22 10:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 1, 398000.00, 'completed', 'Hà Nội', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(2, 2, 299000.00, 'completed', 'TP.HCM', 'Bank Transfer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(3, 3, 499000.00, 'pending', 'Đà Nẵng', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(4, 4, 699000.00, 'shipped', 'Cần Thơ', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(5, 5, 359000.00, 'processing', 'Hải Phòng', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(6, 6, 459000.00, 'completed', 'Huế', 'Bank Transfer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(7, 7, 599000.00, 'pending', 'Bình Dương', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(8, 8, 899000.00, 'completed', 'Quảng Ninh', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(9, 1, 249000.00, 'cancelled', 'Hà Nội', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(10, 2, 199000.00, 'completed', 'TP.HCM', 'COD', '2025-09-22 10:53:15', '2025-09-22 10:53:15');

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
(1, 1, 1, 2, 199000.00),
(2, 2, 2, 1, 299000.00),
(3, 3, 5, 1, 499000.00),
(4, 4, 7, 1, 699000.00),
(5, 5, 10, 1, 359000.00),
(6, 6, 9, 1, 459000.00),
(7, 7, 6, 1, 599000.00),
(8, 8, 8, 1, 899000.00),
(9, 9, 4, 1, 249000.00),
(10, 10, 1, 1, 199000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `category_id`, `stock_quantity`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Áo Thun Basic Nam', 'ao-thun-basic-nam', 'Áo thun cotton 100% thoáng mát', 199000.00, 1, 50, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(2, 'Áo Sơ Mi Trắng Nữ', 'ao-so-mi-trang-nu', 'Áo sơ mi form slimfit công sở', 299000.00, 2, 40, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(3, 'Quần Jeans Xanh Nam', 'quan-jeans-xanh-nam', 'Quần jeans nam ống đứng', 399000.00, 3, 30, 0, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(4, 'Quần Short Kaki', 'quan-short-kaki', 'Quần short kaki trẻ trung', 249000.00, 4, 20, 0, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(5, 'Đầm Hoa Nữ', 'dam-hoa-nu', 'Đầm hoa nhí dạo phố', 499000.00, 5, 15, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(6, 'Áo Khoác Jean', 'ao-khoac-jean', 'Áo khoác jean cá tính', 599000.00, 6, 25, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(7, 'Giày Sneaker Trắng', 'giay-sneaker-trang', 'Giày sneaker trắng unisex', 699000.00, 7, 60, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(8, 'Túi Xách Da Nữ', 'tui-xach-da-nu', 'Túi xách da cao cấp', 899000.00, 8, 10, 0, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(9, 'Bộ Đồ Thể Thao Nam', 'bo-do-the-thao-nam', 'Đồ thể thao co giãn 4 chiều', 459000.00, 9, 35, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(10, 'Bộ Pijama Cotton', 'bo-pijama-cotton', 'Pijama cotton mềm mịn', 359000.00, 10, 20, 0, '2025-09-22 10:53:15', '2025-09-22 10:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_main`) VALUES
(1, 1, 'ao-thun-basic.jpg', 1),
(2, 2, 'ao-so-mi-trang.jpg', 1),
(3, 3, 'quan-jeans-xanh.jpg', 1),
(4, 4, 'quan-short-kaki.jpg', 1),
(5, 5, 'dam-hoa.jpg', 1),
(6, 6, 'ao-khoac-jean.jpg', 1),
(7, 7, 'giay-sneaker-trang.jpg', 1),
(8, 8, 'tui-xach-da.jpg', 1),
(9, 9, 'do-the-thao-nam.jpg', 1),
(10, 10, 'pijama-cotton.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 5, 'Áo rất đẹp, chất vải thoáng mát', '2025-09-22 10:53:15'),
(2, 2, 2, 4, 'Áo sơ mi hơi ôm nhưng vẫn ổn', '2025-09-22 10:53:15'),
(3, 3, 3, 5, 'Quần jeans chất lượng tốt', '2025-09-22 10:53:15'),
(4, 4, 5, 5, 'Đầm hoa rất xinh, phù hợp đi chơi', '2025-09-22 10:53:15'),
(5, 5, 7, 4, 'Giày đẹp nhưng hơi rộng', '2025-09-22 10:53:15'),
(6, 6, 8, 5, 'Túi xách sang trọng, rất thích', '2025-09-22 10:53:15'),
(7, 7, 9, 4, 'Đồ thể thao thoải mái', '2025-09-22 10:53:15'),
(8, 8, 10, 5, 'Pijama mềm mịn, dễ chịu', '2025-09-22 10:53:15'),
(9, 1, 6, 4, 'Áo khoác jean ngầu, chất ổn', '2025-09-22 10:53:15'),
(10, 2, 4, 3, 'Quần short hơi ngắn so với mong đợi', '2025-09-22 10:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone_number`, `address`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn A', 'a@example.com', '123456', '0901234567', 'Hà Nội', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(2, 'Trần Thị B', 'b@example.com', '123456', '0902345678', 'TP.HCM', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(3, 'Lê Văn C', 'c@example.com', '123456', '0903456789', 'Đà Nẵng', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(4, 'Phạm Thị D', 'd@example.com', '123456', '0904567890', 'Cần Thơ', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(5, 'Hoàng Văn E', 'e@example.com', '123456', '0905678901', 'Hải Phòng', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(6, 'Đỗ Thị F', 'f@example.com', '123456', '0906789012', 'Huế', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(7, 'Ngô Văn G', 'g@example.com', '123456', '0907890123', 'Bình Dương', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(8, 'Vũ Thị H', 'h@example.com', '123456', '0908901234', 'Quảng Ninh', 'customer', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(9, 'Admin 1', 'admin1@example.com', 'admin123', '0909012345', 'Hà Nội', 'admin', '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(10, 'Admin 2', 'admin2@example.com', 'admin123', '0910123456', 'TP.HCM', 'admin', '2025-09-22 10:53:15', '2025-09-22 10:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `usage_limit` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `start_date`, `end_date`, `usage_limit`, `used_count`, `created_at`, `updated_at`) VALUES
(1, 'SALE10', 'percentage', 10.00, 200000.00, 50000.00, '2025-09-01 00:00:00', '2025-09-30 00:00:00', 100, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(2, 'SALE20', 'percentage', 20.00, 300000.00, 80000.00, '2025-09-01 00:00:00', '2025-09-30 00:00:00', 100, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(3, 'SALE50K', 'fixed', 50000.00, 250000.00, NULL, '2025-09-01 00:00:00', '2025-09-30 00:00:00', 50, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(4, 'SALE100K', 'fixed', 100000.00, 500000.00, NULL, '2025-09-01 00:00:00', '2025-09-30 00:00:00', 50, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(5, 'FREESHIP', 'fixed', 30000.00, 150000.00, NULL, '2025-09-01 00:00:00', '2025-09-30 00:00:00', 200, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(6, 'NEWUSER', 'percentage', 15.00, 100000.00, 50000.00, '2025-09-01 00:00:00', '2025-12-31 00:00:00', 500, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(7, 'BLACKFRIDAY', 'percentage', 50.00, 500000.00, 200000.00, '2025-11-25 00:00:00', '2025-11-30 00:00:00', 300, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(8, 'XMAS2025', 'percentage', 25.00, 400000.00, 100000.00, '2025-12-01 00:00:00', '2025-12-31 00:00:00', 200, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(9, 'SUMMER25', 'percentage', 25.00, 300000.00, 70000.00, '2025-06-01 00:00:00', '2025-06-30 00:00:00', 100, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15'),
(10, 'FLASHSALE', 'fixed', 150000.00, 600000.00, NULL, '2025-09-15 00:00:00', '2025-09-25 00:00:00', 100, 0, '2025-09-22 17:53:15', '2025-09-22 17:53:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_items_order` (`order_id`),
  ADD KEY `fk_items_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_images_product` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_user` (`user_id`),
  ADD KEY `fk_reviews_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
