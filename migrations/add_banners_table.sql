-- Tạo bảng contents để quản lý banners và promotional content
CREATE TABLE IF NOT EXISTS `contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('banner','promo','announcement') NOT NULL DEFAULT 'banner',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `position` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm dữ liệu mẫu cho banners
INSERT INTO `contents` (`type`, `title`, `description`, `image_url`, `link_url`, `button_text`, `position`, `is_active`) VALUES
('banner', 'Bộ sưu tập Thu Đông 2025', 'Xu hướng thời trang mới nhất', 'banner1.jpg', '/GoodZStore/Views/Users/products.php?category=1', 'Mua ngay', 1, 1),
('banner', 'Sale Up To 50%', 'Giảm giá sốc cuối tuần', 'banner2.jpg', '/GoodZStore/Views/Users/products.php?sort=price_asc', 'Khám phá', 2, 1),
('banner', 'Free Ship Toàn Quốc', 'Đơn hàng từ 500k', 'banner3.jpg', '/GoodZStore/Views/Users/products.php', 'Xem ngay', 3, 1);

-- Thêm dữ liệu mẫu cho promotional banners
INSERT INTO `contents` (`type`, `title`, `description`, `image_url`, `link_url`, `button_text`, `position`, `is_active`) VALUES
('promo', 'Giảm 20% cho đơn hàng đầu tiên!', 'Áp dụng cho tất cả sản phẩm', 'promo1.jpg', '/GoodZStore/Views/Users/products.php', 'Nhận ưu đãi', 1, 1),
('promo', 'Mua 2 Tặng 1', 'Áp dụng cho áo thun', 'promo2.jpg', '/GoodZStore/Views/Users/category.php?id=1', 'Mua ngay', 2, 1),
('promo', 'Flash Sale 12h - 14h', 'Giảm đến 70% các sản phẩm hot', 'promo3.jpg', '/GoodZStore/Views/Users/products.php?sort=price_desc', 'Xem ngay', 3, 1);

-- Cập nhật AUTO_INCREMENT
ALTER TABLE `contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
