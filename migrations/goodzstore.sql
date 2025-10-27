-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2025 at 03:39 PM
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
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `size_id` int(11) DEFAULT NULL COMMENT 'Reference to product_sizes',
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(16, 7, 199000.00, 'completed', '123', 'cod', '2025-09-23 03:22:44', '2025-09-23 08:23:43'),
(17, 7, 419000.00, 'completed', '3C/40/4 tổ 6 ấp 3 xã phạm văn hai huyện bình chánh thành phố hồ chí minh', 'cod', '2025-09-23 03:30:54', '2025-09-23 08:39:31'),
(18, 7, 619000.00, 'completed', '3C/40/4 tổ 6 ấp 3 xã phạm văn hai huyện bình chánh thành phố hồ chí minh', 'cod', '2025-09-23 03:37:18', '2025-09-23 08:39:29');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_id` int(11) DEFAULT NULL COMMENT 'Tham chiếu product_sizes',
  `size_name` varchar(10) DEFAULT NULL COMMENT 'Snapshot size khi đặt hàng',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size_id`, `size_name`, `quantity`, `price`) VALUES
(11, 16, 1, NULL, NULL, 1, 199000.00),
(12, 17, 5, NULL, NULL, 1, 499000.00),
(13, 18, 7, NULL, NULL, 1, 699000.00);

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
(1, 'Áo Thun Basic Nam', 'ao-thun-basic-nam', 'Áo thun cotton 100% thoáng mát', 199000.00, 1, 49, 1, '2025-09-22 10:53:15', '2025-09-23 08:22:44'),
(2, 'Áo Sơ Mi Trắng Nữ', 'ao-so-mi-trang-nu', 'Áo sơ mi form slimfit công sở', 299000.00, 2, 40, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(3, 'Quần Jeans Xanh Nam', 'quan-jeans-xanh-nam', 'Quần jeans nam ống đứng', 399000.00, 3, 30, 0, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(4, 'Quần Short Kaki', 'quan-short-kaki', 'Quần short kaki trẻ trung', 249000.00, 4, 20, 0, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(5, 'Đầm Hoa Nữ', 'dam-hoa-nu', 'Đầm hoa nhí dạo phố', 499000.00, 5, 14, 1, '2025-09-22 10:53:15', '2025-09-23 08:30:54'),
(6, 'Áo Khoác Jean', 'ao-khoac-jean', 'Áo khoác jean cá tính', 599000.00, 6, 25, 1, '2025-09-22 10:53:15', '2025-09-22 10:53:15'),
(7, 'Giày Sneaker Trắng', 'giay-sneaker-trang', 'Giày sneaker trắng unisex', 699000.00, 7, 59, 1, '2025-09-22 10:53:15', '2025-09-23 08:37:18'),
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
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_name` varchar(10) NOT NULL COMMENT 'S, M, L, XL, XXL hoặc 38, 39, 40...',
  `stock_quantity` int(11) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00 COMMENT 'Điều chỉnh giá nếu size khác có giá khác',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size_name`, `stock_quantity`, `price_adjustment`, `created_at`) VALUES
(1, 1, 'S', 15, 0.00, '2025-10-01 13:38:53'),
(2, 1, 'M', 20, 0.00, '2025-10-01 13:38:53'),
(3, 1, 'L', 10, 0.00, '2025-10-01 13:38:53'),
(4, 1, 'XL', 5, 0.00, '2025-10-01 13:38:53'),
(5, 2, 'S', 12, 0.00, '2025-10-01 13:38:53'),
(6, 2, 'M', 15, 0.00, '2025-10-01 13:38:53'),
(7, 2, 'L', 10, 0.00, '2025-10-01 13:38:53'),
(8, 2, 'XL', 3, 0.00, '2025-10-01 13:38:53'),
(9, 3, '29', 5, 0.00, '2025-10-01 13:38:53'),
(10, 3, '30', 8, 0.00, '2025-10-01 13:38:53'),
(11, 3, '31', 7, 0.00, '2025-10-01 13:38:53'),
(12, 3, '32', 6, 0.00, '2025-10-01 13:38:53'),
(13, 3, '33', 4, 0.00, '2025-10-01 13:38:53'),
(14, 4, 'S', 8, 0.00, '2025-10-01 13:38:53'),
(15, 4, 'M', 7, 0.00, '2025-10-01 13:38:53'),
(16, 4, 'L', 5, 0.00, '2025-10-01 13:38:53'),
(17, 5, 'S', 5, 0.00, '2025-10-01 13:38:53'),
(18, 5, 'M', 7, 0.00, '2025-10-01 13:38:53'),
(19, 5, 'L', 3, 0.00, '2025-10-01 13:38:53'),
(20, 6, 'M', 10, 0.00, '2025-10-01 13:38:53'),
(21, 6, 'L', 10, 0.00, '2025-10-01 13:38:53'),
(22, 6, 'XL', 5, 0.00, '2025-10-01 13:38:53'),
(23, 7, '38', 8, 0.00, '2025-10-01 13:38:53'),
(24, 7, '39', 12, 0.00, '2025-10-01 13:38:53'),
(25, 7, '40', 15, 0.00, '2025-10-01 13:38:53'),
(26, 7, '41', 12, 0.00, '2025-10-01 13:38:53'),
(27, 7, '42', 10, 0.00, '2025-10-01 13:38:53'),
(28, 7, '43', 3, 0.00, '2025-10-01 13:38:53'),
(29, 9, 'M', 15, 0.00, '2025-10-01 13:38:53'),
(30, 9, 'L', 12, 0.00, '2025-10-01 13:38:53'),
(31, 9, 'XL', 8, 0.00, '2025-10-01 13:38:53');

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
(11, 1, 2, 5, 'Sản phẩm rất tốt, giao hàng nhanh.', '2025-10-01 00:58:25'),
(12, 2, 3, 4, 'Chất lượng ổn, nhưng đóng gói chưa đẹp.', '2025-10-01 00:58:25'),
(13, 3, 1, 3, 'Tạm ổn, giá hơi cao so với chất lượng.', '2025-10-01 00:58:25'),
(14, 4, 2, 5, 'Rất hài lòng, sẽ ủng hộ lần sau.', '2025-10-01 00:58:25'),
(15, 5, 4, 2, 'Không giống mô tả, hơi thất vọng.', '2025-10-01 00:58:25');

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
(1, 'Nguyễn Văn A', 'a@example.com', '123456', '0901234567', 'Hà Nội', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(2, 'Trần Thị B', 'b@example.com', '123456', '0902345678', 'TP.HCM', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(3, 'Lê Văn C', 'c@example.com', '123456', '0903456789', 'Đà Nẵng', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(4, 'Phạm Thị D', 'd@example.com', '123456', '0904567890', 'Cần Thơ', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(5, 'Hoàng Văn E', 'e@example.com', '123456', '0905678901', 'Hải Phòng', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(6, 'Đỗ Thị F', 'f@example.com', '$2y$10$3l2g83Xt4n8JBbkeH9V8rO6YRtRy//I7AojmchfFjYdT/C5AYZL3K', '0906789012', 'Huế', 'customer', '2025-09-22 03:53:15', '2025-10-01 01:15:21'),
(7, 'Ngô Văn Giáp', 'g@example.com', '$2y$10$5mHeq4RZMAqKR1gHATHQF.ppXVYN9gbiUN3rsGRPKDTljWplQDBb.', '0907890123', 'Bình Dương sài gòn', 'customer', '2025-09-22 03:53:15', '2025-10-01 01:05:10'),
(8, 'Vũ Thị H', 'h@example.com', '123456', '0908901234', 'Quảng Ninh', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(9, 'Admin 1', 'admin1@example.com', '$2y$10$sAdcZcNTlYxs6v/IYVYUwuRVh8ScLMojEMXckshkMYHAquZT28sry', '0909012345', 'Hà Nội', 'admin', '2025-09-22 03:53:15', '2025-10-01 01:11:03'),
(10, 'Admin 2', 'admin2@example.com', '$2y$10$jjLUIQOPT7ItvprB5oqxh.pQmhSk94Go9kMcjQAXb/.ikFDDiH0/q', '0910123456', 'TP.HCM', 'admin', '2025-09-22 03:53:15', '2025-10-01 00:48:04'),
(11, 'Lợi', 'ntloi1910@gmail.com', '$2y$10$rwpoyGk6tZQwWVsVms7EhuFG23SxM9n.QVrcsbDfPEGRbVFRDxVXS', '123', NULL, 'customer', '2025-09-23 08:52:36', '2025-09-23 08:52:36'),
(12, 'Bá tước', '123@gmail', '$2y$10$9xuUZM5IRYaU.EtQaPC0RuJs0LpiZR7xYLKuFkeyxDgoPSoYlvvdC', '123', NULL, 'customer', '2025-09-23 08:55:18', '2025-09-23 08:55:18'),
(13, 'Lợi', 'loi@gmail.com', '$2y$10$lcXFpRnWiuALvfSvH4j93OnPjOkDDoJPWeaKjQZ4ak9CMZGU6KC0C', '123', NULL, 'customer', '2025-09-23 09:00:11', '2025-09-23 09:00:11');

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
(2, 'SALE20', 'percentage', 20.00, 300000.00, 80000.00, '2025-09-01 00:00:00', '2025-09-30 00:00:00', 100, 2, '2025-09-22 17:53:15', '2025-09-23 15:37:18'),
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
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `size_id` (`size_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `fk_items_product` (`product_id`),
  ADD KEY `fk_orderitem_size` (`size_id`);

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
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_product_size` (`product_id`,`size_name`);

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
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_size` FOREIGN KEY (`size_id`) REFERENCES `product_sizes` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orderitem_size` FOREIGN KEY (`size_id`) REFERENCES `product_sizes` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `fk_product_sizes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

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
