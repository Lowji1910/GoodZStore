-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 01:55 PM
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
-- Table structure for table `ai_conversations`
--

CREATE TABLE `ai_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'Reference to users table, NULL for guest users',
  `session_id` varchar(100) NOT NULL COMMENT 'Unique session identifier for grouping conversations',
  `direction` enum('user','bot') NOT NULL COMMENT 'Message direction: user or bot',
  `intent` varchar(50) DEFAULT NULL COMMENT 'Detected intent (e.g., size_suggest, recommend, promo)',
  `message` text NOT NULL COMMENT 'The actual message content',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional data like product_id, measurements, etc.' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When the message was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores AI chatbot conversation history';

--
-- Dumping data for table `ai_conversations`
--

INSERT INTO `ai_conversations` (`id`, `user_id`, `session_id`, `direction`, `intent`, `message`, `metadata`, `created_at`) VALUES
(1, NULL, 'ses-sample-001', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}', '2025-10-27 08:40:33'),
(2, NULL, 'ses-sample-001', 'bot', 'size_suggest', 'Với chiều cao 170cm và cân nặng 65kg, mình gợi ý bạn nên chọn size M. Size này sẽ vừa vặn và thoải mái cho bạn.', '{\"size_suggestion\": \"M\", \"reason\": \"Based on height 170cm\"}', '2025-10-27 08:40:33'),
(3, NULL, 'ses-sample-002', 'user', NULL, 'Có mã giảm giá nào không?', '{\"product_id\": 2}', '2025-10-27 08:40:33'),
(4, NULL, 'ses-sample-002', 'bot', 'promo', 'Hiện tại shop đang có mã SUMMER2024 giảm 20% cho đơn hàng từ 500k. Bạn có thể áp dụng khi thanh toán nhé!', NULL, '2025-10-27 08:40:33'),
(5, NULL, 'test-1761554940', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}', '2025-10-27 08:49:00'),
(6, NULL, 'test-1761554941', 'user', NULL, 'Có mã giảm giá nào không?', '{\"product_id\": 2}', '2025-10-27 08:49:01'),
(7, NULL, 'test-1761554942', 'user', NULL, 'Áo này phối với quần gì đẹp?', '{\"product_id\": 3}', '2025-10-27 08:49:02'),
(8, NULL, 'test-1761554944', 'bot', 'size_suggest', 'Gợi ý size: M - Gợi ý dựa trên chiều cao 165cm', '{\"product_id\": 1, \"measurements\": {\"height_cm\": 165, \"weight_kg\": 60}}', '2025-10-27 08:49:04'),
(13, NULL, 'test-1761554996', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}', '2025-10-27 08:49:56'),
(14, NULL, 'test-1761554998', 'user', NULL, 'Có mã giảm giá nào không?', '{\"product_id\": 2}', '2025-10-27 08:49:58'),
(15, NULL, 'test-1761554999', 'user', NULL, 'Áo này phối với quần gì đẹp?', '{\"product_id\": 3}', '2025-10-27 08:49:59'),
(16, NULL, 'test-1761555000', 'bot', 'size_suggest', 'Gợi ý size: M - Gợi ý dựa trên chiều cao 165cm', '{\"product_id\": 1, \"measurements\": {\"height_cm\": 165, \"weight_kg\": 60}}', '2025-10-27 08:50:00'),
(21, NULL, 'test-1761555303', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}', '2025-10-27 08:55:03'),
(22, NULL, 'test-1761555303', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size L. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 08:55:03'),
(23, NULL, 'test-1761555304', 'user', NULL, 'Có mã giảm giá nào không?', '{\"product_id\": 2}', '2025-10-27 08:55:04'),
(24, NULL, 'test-1761555304', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size M. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 08:55:04'),
(25, NULL, 'test-1761555305', 'user', NULL, 'Áo này phối với quần gì đẹp?', '{\"product_id\": 3}', '2025-10-27 08:55:05'),
(26, NULL, 'test-1761555305', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size 30. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 08:55:05'),
(27, NULL, 'test-1761555306', 'bot', 'size_suggest', 'Gợi ý size: M - Gợi ý dựa trên chiều cao 165cm', '{\"product_id\": 1, \"measurements\": {\"height_cm\": 165, \"weight_kg\": 60}}', '2025-10-27 08:55:06'),
(32, NULL, 'test-1761555609', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}', '2025-10-27 09:00:09'),
(33, NULL, 'test-1761555609', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size L. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:00:11'),
(34, NULL, 'test-1761555612', 'user', NULL, 'Có mã giảm giá nào không?', '{\"product_id\": 2}', '2025-10-27 09:00:12'),
(35, NULL, 'test-1761555612', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size M. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:00:12'),
(36, NULL, 'test-1761555613', 'user', NULL, 'Áo này phối với quần gì đẹp?', '{\"product_id\": 3}', '2025-10-27 09:00:13'),
(37, NULL, 'test-1761555613', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size 30. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:00:13'),
(38, NULL, 'test-1761555614', 'bot', 'size_suggest', 'Gợi ý size: M - Gợi ý dựa trên chiều cao 165cm', '{\"product_id\": 1, \"measurements\": {\"height_cm\": 165, \"weight_kg\": 60}}', '2025-10-27 09:00:14'),
(43, NULL, 'test-1761555735', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}', '2025-10-27 09:02:15'),
(44, NULL, 'test-1761555735', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size L. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:02:16'),
(45, NULL, 'test-1761555737', 'user', NULL, 'Có mã giảm giá nào không?', '{\"product_id\": 2}', '2025-10-27 09:02:17'),
(46, NULL, 'test-1761555737', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size M. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:02:17'),
(47, NULL, 'test-1761555738', 'user', NULL, 'Áo này phối với quần gì đẹp?', '{\"product_id\": 3}', '2025-10-27 09:02:18'),
(48, NULL, 'test-1761555738', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size 30. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:02:19'),
(49, NULL, 'test-1761555740', 'bot', 'size_suggest', 'Gợi ý size: M - Gợi ý dựa trên chiều cao 165cm', '{\"product_id\": 1, \"measurements\": {\"height_cm\": 165, \"weight_kg\": 60}}', '2025-10-27 09:02:20'),
(54, NULL, 'test-1761555924', 'user', NULL, 'Tôi cao 170cm, nặng 65kg, nên mặc size nào?', '{\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}', '2025-10-27 09:05:24'),
(55, NULL, 'test-1761555924', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size L. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:05:25'),
(56, NULL, 'test-1761555926', 'user', NULL, 'Có mã giảm giá nào không?', '{\"product_id\": 2}', '2025-10-27 09:05:26'),
(57, NULL, 'test-1761555926', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size M. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:05:26'),
(58, NULL, 'test-1761555927', 'user', NULL, 'Áo này phối với quần gì đẹp?', '{\"product_id\": 3}', '2025-10-27 09:05:27'),
(59, NULL, 'test-1761555927', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size 30. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:05:28'),
(60, NULL, 'test-1761555929', 'bot', 'size_suggest', 'Gợi ý size: M - Gợi ý dựa trên chiều cao 165cm', '{\"product_id\": 1, \"measurements\": {\"height_cm\": 165, \"weight_kg\": 60}}', '2025-10-27 09:05:29'),
(61, NULL, 'test-flow-1761555931', 'user', NULL, 'Xin chào, tôi muốn mua áo', '{\"product_id\": 1, \"height_cm\": null}', '2025-10-27 09:05:31'),
(62, NULL, 'test-flow-1761555931', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size M. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:05:32'),
(63, NULL, 'test-flow-1761555931', 'user', NULL, 'Tôi cao 175cm', '{\"product_id\": 1, \"height_cm\": 175}', '2025-10-27 09:05:32'),
(64, NULL, 'test-flow-1761555931', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size L. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:05:32'),
(65, NULL, 'test-flow-1761555931', 'user', NULL, 'Size nào phù hợp?', '{\"product_id\": 1, \"height_cm\": 175}', '2025-10-27 09:05:33'),
(66, NULL, 'test-flow-1761555931', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size L. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:05:33'),
(67, NULL, 'test-flow-1761555931', 'user', NULL, 'Có khuyến mãi không?', '{\"product_id\": 1, \"height_cm\": 175}', '2025-10-27 09:05:33'),
(68, NULL, 'test-flow-1761555931', 'bot', NULL, 'Xin chào! Mình là trợ lý AI của GoodZStore. Dựa trên thông số của bạn, mình gợi ý size L. Hiện tại shop đang có các mã giảm giá: NEWUSER. Bạn có thể xem thêm các sản phẩm tương tự bên dưới nhé!', NULL, '2025-10-27 09:05:34'),
(69, 2, 'ses-1761561827', 'user', NULL, 'hi', NULL, '2025-10-27 10:43:47'),
(70, 2, 'ses-1761561827', 'bot', NULL, 'Chào bạn! Tôi có thể giúp gì cho bạn hôm nay?', NULL, '2025-10-27 10:43:49'),
(71, 2, 'ses-1761561827', 'user', NULL, 'tôi cao 1m7 nặng 100kg nên mặc size gì', NULL, '2025-10-27 10:44:10'),
(72, 2, 'ses-1761561827', 'bot', NULL, 'Chào bạn! Với chiều cao 1m70 và cân nặng 100kg, chúng tôi gợi ý bạn chọn size **2XL (XXL)** để đảm bảo sự thoải mái.\n\nTuy nhiên, bạn vui lòng kiểm tra thêm số đo vòng ngực và eo trên bảng size chi tiết của sản phẩm để có lựa chọn chính xác nhất nhé.\n\nĐừng quên sử dụng mã giảm giá **NEWUSER** khi thanh toán cho đơn hàng đầu tiên của bạn ạ!', NULL, '2025-10-27 10:44:14'),
(73, 2, 'ses-1761561871', 'user', NULL, 'hi bạn là gì', NULL, '2025-10-27 10:44:31'),
(74, 2, 'ses-1761561871', 'bot', NULL, 'Chào bạn! Mình là trợ lý tư vấn thời trang ảo, sẵn sàng giúp bạn tìm kiếm sản phẩm, tư vấn size, hoặc gợi ý các phong cách phù hợp.\n\nBạn đang quan tâm đến sản phẩm nào? Đừng quên sử dụng mã giảm giá **NEWUSER** cho đơn hàng đầu tiên của bạn nhé!', NULL, '2025-10-27 10:44:34'),
(75, 2, 'ses-1761561871', 'user', NULL, 'bạn có thể hỗ trợ tôi code không', NULL, '2025-10-27 10:44:51'),
(76, 2, 'ses-1761561871', 'bot', NULL, 'Chào bạn! Tôi rất sẵn lòng hỗ trợ bạn về các mã giảm giá nhé.\n\nHiện tại, mã voucher đang hoạt động là **NEWUSER**, áp dụng cho các đơn hàng đầu tiên. Bạn hãy nhập mã này khi thanh toán để được ưu đãi tốt nhất nhé!', NULL, '2025-10-27 10:44:54'),
(77, 2, 'ses-1761561871', 'user', NULL, 'tôi cao 1m6 nặng 70kg thì có size nào phù hợp', NULL, '2025-10-27 10:45:21'),
(78, 2, 'ses-1761561871', 'bot', NULL, 'Chào bạn! Dựa trên chiều cao 1m60 và cân nặng 70kg, bạn nên tham khảo size **XXL** hoặc **2XL** để có độ vừa vặn thoải mái.\n\nĐể đảm bảo chính xác nhất, bạn vui lòng kiểm tra thêm số đo vòng ngực và vòng eo trong bảng kích thước chi tiết của từng sản phẩm nhé. Đừng quên áp dụng mã **NEWUSER** cho đơn hàng đầu tiên của mình!', NULL, '2025-10-27 10:45:25'),
(79, 2, 'ses-1761561871', 'user', NULL, 'có sản phẩm nào phù hợp với tài chính 200k không', NULL, '2025-10-27 10:45:51'),
(80, 2, 'ses-1761561871', 'bot', NULL, 'Chào bạn! Có rất nhiều sản phẩm đẹp trong tầm giá 200k, như các loại phụ kiện, tất chân hoặc áo thun cơ bản đang có ưu đãi. Bạn hãy kiểm tra mục \"Giá Tốt\" trên website/app để dễ dàng lựa chọn nhé. Đừng quên áp dụng mã giảm giá **NEWUSER** nếu bạn là khách hàng mới để được giảm thêm!', NULL, '2025-10-27 10:45:55'),
(81, 2, 'ses-1761561871', 'user', NULL, 'Áo Thun Basic Nam giá bao nhiêu', NULL, '2025-10-27 10:46:14'),
(82, 2, 'ses-1761561871', 'bot', NULL, 'Chào bạn! Áo Thun Basic Nam hiện có giá rất hợp lý, bạn vui lòng kiểm tra trực tiếp trên trang sản phẩm để biết mức giá chính xác nhất. Đừng quên nhập mã **NEWUSER** khi thanh toán nếu đây là đơn hàng đầu tiên của bạn nhé!', NULL, '2025-10-27 10:46:17'),
(83, 2, 'ses-1761561871', 'user', NULL, 'ở tài chính 400k thì có sản phẩm nào phù hợp', NULL, '2025-10-27 10:47:29'),
(84, 2, 'ses-1761561871', 'bot', NULL, 'Chào bạn! Với ngân sách 400k VND, bạn hoàn toàn có thể tìm được những sản phẩm rất xinh như áo thun basic, áo croptop hoặc các phụ kiện thời trang.\n\nBạn hãy xem qua các mục sản phẩm đang được sale hoặc các mẫu áo phông mới về nhé. Đừng quên áp dụng mã giảm giá **NEWUSER** để tiết kiệm hơn khi thanh toán!', NULL, '2025-10-27 10:47:33'),
(85, 2, 'ses-1761562090', 'user', NULL, 'hi', NULL, '2025-10-27 10:48:10'),
(86, 2, 'ses-1761562090', 'bot', NULL, 'Chào bạn, tôi có thể giúp gì cho bạn hôm nay? Hãy chia sẻ nếu bạn cần tư vấn về thời trang hoặc sản phẩm nhé!', NULL, '2025-10-27 10:48:12'),
(87, 2, 'ses-1761562191', 'user', NULL, 'hi', NULL, '2025-10-27 10:49:51'),
(88, 2, 'ses-1761562191', 'bot', NULL, 'Chào bạn! Tôi có thể giúp gì cho bạn hôm nay?', NULL, '2025-10-27 10:49:52'),
(89, 2, 'ses-1761562191', 'user', NULL, 'tôi muốn tìm áo với giá 200k', NULL, '2025-10-27 10:50:05'),
(90, 2, 'ses-1761562191', 'bot', NULL, 'Chào bạn! Để tìm áo trong tầm giá 200k, bạn hãy sử dụng bộ lọc giá trên trang web để dễ dàng xem các mẫu áo thun hoặc áo kiểu đang được giảm giá nhé.\n\nHiện tại cửa hàng có rất nhiều lựa chọn phù hợp với mức giá này. Đừng quên áp dụng mã **NEWUSER** để nhận ưu đãi cho đơn hàng đầu tiên của bạn!', NULL, '2025-10-27 10:50:09'),
(91, 2, 'ses-1761562191', 'user', NULL, 'bạn hãy gợi ý 1 mẫu áo giá 200k cho tôi', NULL, '2025-10-27 10:50:25'),
(92, 2, 'ses-1761562191', 'bot', NULL, 'Chào bạn! Với ngân sách 200k, tôi gợi ý bạn tham khảo mẫu Áo Phông Cotton Cơ Bản, rất dễ mặc và phối đồ. Mẫu áo này có nhiều màu trung tính, chất liệu cotton thoáng mát, phù hợp cho mọi dịp. Bạn đừng quên sử dụng mã **NEWUSER** nếu đây là đơn hàng đầu tiên để tiết kiệm hơn nữa nhé!', NULL, '2025-10-27 10:50:29'),
(93, 2, 'ses-1761562262', 'user', NULL, 'gợi ý áo 200k cho tôi', NULL, '2025-10-27 10:51:02'),
(94, 2, 'ses-1761562262', 'bot', NULL, 'Chào bạn! Dưới 200k, bạn có thể tham khảo các mẫu áo đang được yêu thích như Áo Phông Basic (189k), Áo Croptop dệt kim (195k) hoặc Áo Hai Dây lụa (165k). Những mẫu này đều dễ phối đồ và rất hợp với ngân sách của bạn đấy. Bạn đừng quên sử dụng mã giảm giá NEWUSER khi thanh toán để nhận ưu đãi nhé!', NULL, '2025-10-27 10:51:07'),
(95, 2, 'ses-1761562427', 'user', NULL, 'tôi muốn mua 1 cái áo giá 199k hãy gokjiw ý cho tôi', NULL, '2025-10-27 10:53:47'),
(96, 2, 'ses-1761562427', 'bot', NULL, 'Chào bạn! Mình rất vui được hỗ trợ bạn tìm áo giá 199k.\n\nVới mức giá này, bạn có thể tham khảo các mẫu Áo Thun Basic (T-shirt), Áo Croptop hoặc Áo Tank Top hiện đang rất được ưa chuộng. Đừng quên áp dụng mã giảm giá **NEWUSER** khi thanh toán để nhận thêm ưu đãi nhé!', NULL, '2025-10-27 10:53:51'),
(97, 2, 'ses-1761562600', 'user', NULL, 'tôi muốn tìm áo 200k', NULL, '2025-10-27 10:56:40'),
(98, 2, 'ses-1761562600', 'bot', NULL, 'Chào bạn! Với ngân sách 200k, shop mình có rất nhiều mẫu áo phông cơ bản và áo ba lỗ hợp thời trang, rất dễ phối đồ. Bạn đừng quên áp dụng mã giảm giá **NEWUSER** khi thanh toán để được hưởng ưu đãi nhé!', NULL, '2025-10-27 10:56:44'),
(99, 2, 'ses-1761562600', 'user', NULL, 'cho tôi tên cụ thể của áo', NULL, '2025-10-27 10:57:02'),
(100, 2, 'ses-1761562600', 'bot', NULL, 'Chào bạn! Bạn vui lòng cho mình biết bạn đang quan tâm đến mẫu áo nào (mã sản phẩm hoặc mô tả) để mình cung cấp tên cụ thể nhé.\n\nRất sẵn lòng hỗ trợ bạn! Đừng quên sử dụng mã giảm giá **NEWUSER** cho đơn hàng đầu tiên của mình nha.', NULL, '2025-10-27 10:57:06'),
(101, 2, 'ses-1761562737', 'user', NULL, 'áo nào 200k', NULL, '2025-10-27 10:58:57'),
(102, 2, 'ses-1761562737', 'bot', NULL, 'Chào bạn! Hiện tại, cửa hàng có nhiều mẫu áo thun và áo phông cơ bản với giá chỉ từ 199.000 VNĐ.\n\nBạn có thể dễ dàng tìm thấy các sản phẩm này bằng cách sử dụng bộ lọc giá dưới 200k trong mục \"Áo\" nhé. Đừng quên áp dụng mã **NEWUSER** để được giảm thêm cho đơn hàng đầu tiên của mình!', NULL, '2025-10-27 10:59:01'),
(103, 2, 'ses-1761562737', 'user', NULL, 'giày có sản phẩm nào', NULL, '2025-10-27 10:59:15'),
(104, 2, 'ses-1761562737', 'bot', NULL, 'Chào bạn, hiện tại cửa hàng chúng tôi có rất nhiều mẫu giày đa dạng.\n\nBạn có thể tham khảo các dòng sản phẩm nổi bật như Giày sneakers năng động, Giày loafers lịch sự, hoặc Boots thời trang.\n\nĐừng quên áp dụng mã **NEWUSER** khi thanh toán để được ưu đãi nhé!', NULL, '2025-10-27 10:59:19'),
(105, 2, 'ses-1761568589', 'user', NULL, 'hello', NULL, '2025-10-27 12:36:29'),
(106, 2, 'ses-1761568589', 'bot', NULL, 'Chào bạn, tôi là trợ lý thời trang của bạn! Bạn cần tư vấn gì về phong cách hay sản phẩm nào không?', NULL, '2025-10-27 12:36:30');

-- --------------------------------------------------------

--
-- Table structure for table `ai_training_data`
--

CREATE TABLE `ai_training_data` (
  `id` int(11) NOT NULL,
  `source` varchar(50) NOT NULL COMMENT 'Source of data: conversation, size_tool, manual, etc.',
  `ref_id` int(11) DEFAULT NULL COMMENT 'Reference ID to source (e.g., conversation_id)',
  `text` text NOT NULL COMMENT 'The training text/data in JSON or plain text format',
  `label` varchar(50) DEFAULT NULL COMMENT 'Classification label: recommend, ask_size, promo, general, style_advice',
  `is_validated` tinyint(1) DEFAULT 0 COMMENT 'Whether this data has been validated by admin',
  `quality_score` tinyint(4) DEFAULT NULL COMMENT 'Quality score 1-5, set by admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When the data was added',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Curated training data for AI model improvement';

--
-- Dumping data for table `ai_training_data`
--

INSERT INTO `ai_training_data` (`id`, `source`, `ref_id`, `text`, `label`, `is_validated`, `quality_score`, `created_at`, `updated_at`) VALUES
(1, 'conversation', 1, '{\"user\": \"Tôi cao 170cm, nên mặc size nào?\", \"bot\": \"Với chiều cao 170cm, mình gợi ý size M\", \"metadata\": {\"height_cm\": 170}}', 'ask_size', 1, 5, '2025-10-27 08:40:33', '2025-10-27 08:40:33'),
(2, 'conversation', 3, '{\"user\": \"Có mã giảm giá nào không?\", \"bot\": \"Hiện tại shop đang có mã SUMMER2024 giảm 20%\"}', 'promo', 1, 5, '2025-10-27 08:40:33', '2025-10-27 08:40:33'),
(3, 'manual', NULL, '{\"user\": \"Áo này phối với quần gì đẹp?\", \"bot\": \"Áo này bạn có thể phối với quần jean hoặc quần kaki đều đẹp\"}', 'style_advice', 1, 4, '2025-10-27 08:40:33', '2025-10-27 08:40:33'),
(4, 'manual', NULL, '{\"user\": \"Sản phẩm này có màu nào khác không?\", \"bot\": \"Sản phẩm này hiện có 3 màu: đen, trắng và xám\"}', 'general', 1, 4, '2025-10-27 08:40:33', '2025-10-27 08:40:33'),
(5, 'size_tool', NULL, 'product:1 measurements:{\"height_cm\": 165, \"weight_kg\": 60} suggestion:M', 'size_suggest', 0, NULL, '2025-10-27 08:49:04', '2025-10-27 08:49:04'),
(6, 'size_tool', NULL, 'product:1 measurements:{\"height_cm\": 165, \"weight_kg\": 60} suggestion:M', 'size_suggest', 0, NULL, '2025-10-27 08:50:00', '2025-10-27 08:50:00'),
(7, 'conversation', NULL, '{\"user\": \"T\\u00f4i cao 170cm, n\\u1eb7ng 65kg, n\\u00ean m\\u1eb7c size n\\u00e0o?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size L. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}}', NULL, 0, NULL, '2025-10-27 08:55:03', '2025-10-27 08:55:03'),
(8, 'conversation', NULL, '{\"user\": \"C\\u00f3 m\\u00e3 gi\\u1ea3m gi\\u00e1 n\\u00e0o kh\\u00f4ng?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size M. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 2}}', NULL, 0, NULL, '2025-10-27 08:55:04', '2025-10-27 08:55:04'),
(9, 'conversation', NULL, '{\"user\": \"\\u00c1o n\\u00e0y ph\\u1ed1i v\\u1edbi qu\\u1ea7n g\\u00ec \\u0111\\u1eb9p?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size 30. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 3}}', NULL, 0, NULL, '2025-10-27 08:55:05', '2025-10-27 08:55:05'),
(10, 'size_tool', NULL, 'product:1 measurements:{\"height_cm\": 165, \"weight_kg\": 60} suggestion:M', 'size_suggest', 0, NULL, '2025-10-27 08:55:06', '2025-10-27 08:55:06'),
(11, 'conversation', NULL, '{\"user\": \"T\\u00f4i cao 170cm, n\\u1eb7ng 65kg, n\\u00ean m\\u1eb7c size n\\u00e0o?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size L. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}}', NULL, 0, NULL, '2025-10-27 09:00:11', '2025-10-27 09:00:11'),
(12, 'conversation', NULL, '{\"user\": \"C\\u00f3 m\\u00e3 gi\\u1ea3m gi\\u00e1 n\\u00e0o kh\\u00f4ng?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size M. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 2}}', NULL, 0, NULL, '2025-10-27 09:00:12', '2025-10-27 09:00:12'),
(13, 'conversation', NULL, '{\"user\": \"\\u00c1o n\\u00e0y ph\\u1ed1i v\\u1edbi qu\\u1ea7n g\\u00ec \\u0111\\u1eb9p?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size 30. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 3}}', NULL, 0, NULL, '2025-10-27 09:00:13', '2025-10-27 09:00:13'),
(14, 'size_tool', NULL, 'product:1 measurements:{\"height_cm\": 165, \"weight_kg\": 60} suggestion:M', 'size_suggest', 0, NULL, '2025-10-27 09:00:14', '2025-10-27 09:00:14'),
(15, 'conversation', NULL, '{\"user\": \"T\\u00f4i cao 170cm, n\\u1eb7ng 65kg, n\\u00ean m\\u1eb7c size n\\u00e0o?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size L. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}}', NULL, 0, NULL, '2025-10-27 09:02:16', '2025-10-27 09:02:16'),
(16, 'conversation', NULL, '{\"user\": \"C\\u00f3 m\\u00e3 gi\\u1ea3m gi\\u00e1 n\\u00e0o kh\\u00f4ng?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size M. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 2}}', NULL, 0, NULL, '2025-10-27 09:02:17', '2025-10-27 09:02:17'),
(17, 'conversation', NULL, '{\"user\": \"\\u00c1o n\\u00e0y ph\\u1ed1i v\\u1edbi qu\\u1ea7n g\\u00ec \\u0111\\u1eb9p?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size 30. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 3}}', NULL, 0, NULL, '2025-10-27 09:02:19', '2025-10-27 09:02:19'),
(18, 'size_tool', NULL, 'product:1 measurements:{\"height_cm\": 165, \"weight_kg\": 60} suggestion:M', 'size_suggest', 0, NULL, '2025-10-27 09:02:20', '2025-10-27 09:02:20'),
(19, 'conversation', NULL, '{\"user\": \"T\\u00f4i cao 170cm, n\\u1eb7ng 65kg, n\\u00ean m\\u1eb7c size n\\u00e0o?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size L. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": 170, \"weight_kg\": 65}}', NULL, 0, NULL, '2025-10-27 09:05:25', '2025-10-27 09:05:25'),
(20, 'conversation', NULL, '{\"user\": \"C\\u00f3 m\\u00e3 gi\\u1ea3m gi\\u00e1 n\\u00e0o kh\\u00f4ng?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size M. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 2}}', NULL, 0, NULL, '2025-10-27 09:05:26', '2025-10-27 09:05:26'),
(21, 'conversation', NULL, '{\"user\": \"\\u00c1o n\\u00e0y ph\\u1ed1i v\\u1edbi qu\\u1ea7n g\\u00ec \\u0111\\u1eb9p?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size 30. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 3}}', NULL, 0, NULL, '2025-10-27 09:05:28', '2025-10-27 09:05:28'),
(22, 'size_tool', NULL, 'product:1 measurements:{\"height_cm\": 165, \"weight_kg\": 60} suggestion:M', 'size_suggest', 0, NULL, '2025-10-27 09:05:29', '2025-10-27 09:05:29'),
(23, 'conversation', NULL, '{\"user\": \"Xin ch\\u00e0o, t\\u00f4i mu\\u1ed1n mua \\u00e1o\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size M. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": null}}', NULL, 0, NULL, '2025-10-27 09:05:32', '2025-10-27 09:05:32'),
(24, 'conversation', NULL, '{\"user\": \"T\\u00f4i cao 175cm\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size L. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": 175}}', NULL, 0, NULL, '2025-10-27 09:05:32', '2025-10-27 09:05:32'),
(25, 'conversation', NULL, '{\"user\": \"Size n\\u00e0o ph\\u00f9 h\\u1ee3p?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size L. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": 175}}', NULL, 0, NULL, '2025-10-27 09:05:33', '2025-10-27 09:05:33'),
(26, 'conversation', NULL, '{\"user\": \"C\\u00f3 khuy\\u1ebfn m\\u00e3i kh\\u00f4ng?\", \"bot\": \"Xin ch\\u00e0o! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd AI c\\u1ee7a GoodZStore. D\\u1ef1a tr\\u00ean th\\u00f4ng s\\u1ed1 c\\u1ee7a b\\u1ea1n, m\\u00ecnh g\\u1ee3i \\u00fd size L. Hi\\u1ec7n t\\u1ea1i shop \\u0111ang c\\u00f3 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1: NEWUSER. B\\u1ea1n c\\u00f3 th\\u1ec3 xem th\\u00eam c\\u00e1c s\\u1ea3n ph\\u1ea9m t\\u01b0\\u01a1ng t\\u1ef1 b\\u00ean d\\u01b0\\u1edbi nh\\u00e9!\", \"metadata\": {\"product_id\": 1, \"height_cm\": 175}}', NULL, 0, NULL, '2025-10-27 09:05:34', '2025-10-27 09:05:34'),
(27, 'conversation', NULL, '{\"user\": \"hi\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! T\\u00f4i c\\u00f3 th\\u1ec3 gi\\u00fap g\\u00ec cho b\\u1ea1n h\\u00f4m nay?\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:43:49', '2025-10-27 10:43:49'),
(28, 'conversation', NULL, '{\"user\": \"t\\u00f4i cao 1m7 n\\u1eb7ng 100kg n\\u00ean m\\u1eb7c size g\\u00ec\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! V\\u1edbi chi\\u1ec1u cao 1m70 v\\u00e0 c\\u00e2n n\\u1eb7ng 100kg, ch\\u00fang t\\u00f4i g\\u1ee3i \\u00fd b\\u1ea1n ch\\u1ecdn size **2XL (XXL)** \\u0111\\u1ec3 \\u0111\\u1ea3m b\\u1ea3o s\\u1ef1 tho\\u1ea3i m\\u00e1i.\\n\\nTuy nhi\\u00ean, b\\u1ea1n vui l\\u00f2ng ki\\u1ec3m tra th\\u00eam s\\u1ed1 \\u0111o v\\u00f2ng ng\\u1ef1c v\\u00e0 eo tr\\u00ean b\\u1ea3ng size chi ti\\u1ebft c\\u1ee7a s\\u1ea3n ph\\u1ea9m \\u0111\\u1ec3 c\\u00f3 l\\u1ef1a ch\\u1ecdn ch\\u00ednh x\\u00e1c nh\\u1ea5t nh\\u00e9.\\n\\n\\u0110\\u1eebng qu\\u00ean s\\u1eed d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 **NEWUSER** khi thanh to\\u00e1n cho \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean c\\u1ee7a b\\u1ea1n \\u1ea1!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:44:14', '2025-10-27 10:44:14'),
(29, 'conversation', NULL, '{\"user\": \"hi b\\u1ea1n l\\u00e0 g\\u00ec\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! M\\u00ecnh l\\u00e0 tr\\u1ee3 l\\u00fd t\\u01b0 v\\u1ea5n th\\u1eddi trang \\u1ea3o, s\\u1eb5n s\\u00e0ng gi\\u00fap b\\u1ea1n t\\u00ecm ki\\u1ebfm s\\u1ea3n ph\\u1ea9m, t\\u01b0 v\\u1ea5n size, ho\\u1eb7c g\\u1ee3i \\u00fd c\\u00e1c phong c\\u00e1ch ph\\u00f9 h\\u1ee3p.\\n\\nB\\u1ea1n \\u0111ang quan t\\u00e2m \\u0111\\u1ebfn s\\u1ea3n ph\\u1ea9m n\\u00e0o? \\u0110\\u1eebng qu\\u00ean s\\u1eed d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 **NEWUSER** cho \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean c\\u1ee7a b\\u1ea1n nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:44:34', '2025-10-27 10:44:34'),
(30, 'conversation', NULL, '{\"user\": \"b\\u1ea1n c\\u00f3 th\\u1ec3 h\\u1ed7 tr\\u1ee3 t\\u00f4i code kh\\u00f4ng\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! T\\u00f4i r\\u1ea5t s\\u1eb5n l\\u00f2ng h\\u1ed7 tr\\u1ee3 b\\u1ea1n v\\u1ec1 c\\u00e1c m\\u00e3 gi\\u1ea3m gi\\u00e1 nh\\u00e9.\\n\\nHi\\u1ec7n t\\u1ea1i, m\\u00e3 voucher \\u0111ang ho\\u1ea1t \\u0111\\u1ed9ng l\\u00e0 **NEWUSER**, \\u00e1p d\\u1ee5ng cho c\\u00e1c \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean. B\\u1ea1n h\\u00e3y nh\\u1eadp m\\u00e3 n\\u00e0y khi thanh to\\u00e1n \\u0111\\u1ec3 \\u0111\\u01b0\\u1ee3c \\u01b0u \\u0111\\u00e3i t\\u1ed1t nh\\u1ea5t nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:44:54', '2025-10-27 10:44:54'),
(31, 'conversation', NULL, '{\"user\": \"t\\u00f4i cao 1m6 n\\u1eb7ng 70kg th\\u00ec c\\u00f3 size n\\u00e0o ph\\u00f9 h\\u1ee3p\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! D\\u1ef1a tr\\u00ean chi\\u1ec1u cao 1m60 v\\u00e0 c\\u00e2n n\\u1eb7ng 70kg, b\\u1ea1n n\\u00ean tham kh\\u1ea3o size **XXL** ho\\u1eb7c **2XL** \\u0111\\u1ec3 c\\u00f3 \\u0111\\u1ed9 v\\u1eeba v\\u1eb7n tho\\u1ea3i m\\u00e1i.\\n\\n\\u0110\\u1ec3 \\u0111\\u1ea3m b\\u1ea3o ch\\u00ednh x\\u00e1c nh\\u1ea5t, b\\u1ea1n vui l\\u00f2ng ki\\u1ec3m tra th\\u00eam s\\u1ed1 \\u0111o v\\u00f2ng ng\\u1ef1c v\\u00e0 v\\u00f2ng eo trong b\\u1ea3ng k\\u00edch th\\u01b0\\u1edbc chi ti\\u1ebft c\\u1ee7a t\\u1eebng s\\u1ea3n ph\\u1ea9m nh\\u00e9. \\u0110\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 **NEWUSER** cho \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean c\\u1ee7a m\\u00ecnh!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:45:25', '2025-10-27 10:45:25'),
(32, 'conversation', NULL, '{\"user\": \"c\\u00f3 s\\u1ea3n ph\\u1ea9m n\\u00e0o ph\\u00f9 h\\u1ee3p v\\u1edbi t\\u00e0i ch\\u00ednh 200k kh\\u00f4ng\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! C\\u00f3 r\\u1ea5t nhi\\u1ec1u s\\u1ea3n ph\\u1ea9m \\u0111\\u1eb9p trong t\\u1ea7m gi\\u00e1 200k, nh\\u01b0 c\\u00e1c lo\\u1ea1i ph\\u1ee5 ki\\u1ec7n, t\\u1ea5t ch\\u00e2n ho\\u1eb7c \\u00e1o thun c\\u01a1 b\\u1ea3n \\u0111ang c\\u00f3 \\u01b0u \\u0111\\u00e3i. B\\u1ea1n h\\u00e3y ki\\u1ec3m tra m\\u1ee5c \\\"Gi\\u00e1 T\\u1ed1t\\\" tr\\u00ean website/app \\u0111\\u1ec3 d\\u1ec5 d\\u00e0ng l\\u1ef1a ch\\u1ecdn nh\\u00e9. \\u0110\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 **NEWUSER** n\\u1ebfu b\\u1ea1n l\\u00e0 kh\\u00e1ch h\\u00e0ng m\\u1edbi \\u0111\\u1ec3 \\u0111\\u01b0\\u1ee3c gi\\u1ea3m th\\u00eam!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:45:55', '2025-10-27 10:45:55'),
(33, 'conversation', NULL, '{\"user\": \"\\u00c1o Thun Basic Nam gi\\u00e1 bao nhi\\u00eau\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! \\u00c1o Thun Basic Nam hi\\u1ec7n c\\u00f3 gi\\u00e1 r\\u1ea5t h\\u1ee3p l\\u00fd, b\\u1ea1n vui l\\u00f2ng ki\\u1ec3m tra tr\\u1ef1c ti\\u1ebfp tr\\u00ean trang s\\u1ea3n ph\\u1ea9m \\u0111\\u1ec3 bi\\u1ebft m\\u1ee9c gi\\u00e1 ch\\u00ednh x\\u00e1c nh\\u1ea5t. \\u0110\\u1eebng qu\\u00ean nh\\u1eadp m\\u00e3 **NEWUSER** khi thanh to\\u00e1n n\\u1ebfu \\u0111\\u00e2y l\\u00e0 \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean c\\u1ee7a b\\u1ea1n nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:46:17', '2025-10-27 10:46:17'),
(34, 'conversation', NULL, '{\"user\": \"\\u1edf t\\u00e0i ch\\u00ednh 400k th\\u00ec c\\u00f3 s\\u1ea3n ph\\u1ea9m n\\u00e0o ph\\u00f9 h\\u1ee3p\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! V\\u1edbi ng\\u00e2n s\\u00e1ch 400k VND, b\\u1ea1n ho\\u00e0n to\\u00e0n c\\u00f3 th\\u1ec3 t\\u00ecm \\u0111\\u01b0\\u1ee3c nh\\u1eefng s\\u1ea3n ph\\u1ea9m r\\u1ea5t xinh nh\\u01b0 \\u00e1o thun basic, \\u00e1o croptop ho\\u1eb7c c\\u00e1c ph\\u1ee5 ki\\u1ec7n th\\u1eddi trang.\\n\\nB\\u1ea1n h\\u00e3y xem qua c\\u00e1c m\\u1ee5c s\\u1ea3n ph\\u1ea9m \\u0111ang \\u0111\\u01b0\\u1ee3c sale ho\\u1eb7c c\\u00e1c m\\u1eabu \\u00e1o ph\\u00f4ng m\\u1edbi v\\u1ec1 nh\\u00e9. \\u0110\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 **NEWUSER** \\u0111\\u1ec3 ti\\u1ebft ki\\u1ec7m h\\u01a1n khi thanh to\\u00e1n!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:47:33', '2025-10-27 10:47:33'),
(35, 'conversation', NULL, '{\"user\": \"hi\", \"bot\": \"Ch\\u00e0o b\\u1ea1n, t\\u00f4i c\\u00f3 th\\u1ec3 gi\\u00fap g\\u00ec cho b\\u1ea1n h\\u00f4m nay? H\\u00e3y chia s\\u1ebb n\\u1ebfu b\\u1ea1n c\\u1ea7n t\\u01b0 v\\u1ea5n v\\u1ec1 th\\u1eddi trang ho\\u1eb7c s\\u1ea3n ph\\u1ea9m nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:48:12', '2025-10-27 10:48:12'),
(36, 'conversation', NULL, '{\"user\": \"hi\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! T\\u00f4i c\\u00f3 th\\u1ec3 gi\\u00fap g\\u00ec cho b\\u1ea1n h\\u00f4m nay?\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:49:52', '2025-10-27 10:49:52'),
(37, 'conversation', NULL, '{\"user\": \"t\\u00f4i mu\\u1ed1n t\\u00ecm \\u00e1o v\\u1edbi gi\\u00e1 200k\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! \\u0110\\u1ec3 t\\u00ecm \\u00e1o trong t\\u1ea7m gi\\u00e1 200k, b\\u1ea1n h\\u00e3y s\\u1eed d\\u1ee5ng b\\u1ed9 l\\u1ecdc gi\\u00e1 tr\\u00ean trang web \\u0111\\u1ec3 d\\u1ec5 d\\u00e0ng xem c\\u00e1c m\\u1eabu \\u00e1o thun ho\\u1eb7c \\u00e1o ki\\u1ec3u \\u0111ang \\u0111\\u01b0\\u1ee3c gi\\u1ea3m gi\\u00e1 nh\\u00e9.\\n\\nHi\\u1ec7n t\\u1ea1i c\\u1eeda h\\u00e0ng c\\u00f3 r\\u1ea5t nhi\\u1ec1u l\\u1ef1a ch\\u1ecdn ph\\u00f9 h\\u1ee3p v\\u1edbi m\\u1ee9c gi\\u00e1 n\\u00e0y. \\u0110\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 **NEWUSER** \\u0111\\u1ec3 nh\\u1eadn \\u01b0u \\u0111\\u00e3i cho \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean c\\u1ee7a b\\u1ea1n!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:50:09', '2025-10-27 10:50:09'),
(38, 'conversation', NULL, '{\"user\": \"b\\u1ea1n h\\u00e3y g\\u1ee3i \\u00fd 1 m\\u1eabu \\u00e1o gi\\u00e1 200k cho t\\u00f4i\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! V\\u1edbi ng\\u00e2n s\\u00e1ch 200k, t\\u00f4i g\\u1ee3i \\u00fd b\\u1ea1n tham kh\\u1ea3o m\\u1eabu \\u00c1o Ph\\u00f4ng Cotton C\\u01a1 B\\u1ea3n, r\\u1ea5t d\\u1ec5 m\\u1eb7c v\\u00e0 ph\\u1ed1i \\u0111\\u1ed3. M\\u1eabu \\u00e1o n\\u00e0y c\\u00f3 nhi\\u1ec1u m\\u00e0u trung t\\u00ednh, ch\\u1ea5t li\\u1ec7u cotton tho\\u00e1ng m\\u00e1t, ph\\u00f9 h\\u1ee3p cho m\\u1ecdi d\\u1ecbp. B\\u1ea1n \\u0111\\u1eebng qu\\u00ean s\\u1eed d\\u1ee5ng m\\u00e3 **NEWUSER** n\\u1ebfu \\u0111\\u00e2y l\\u00e0 \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean \\u0111\\u1ec3 ti\\u1ebft ki\\u1ec7m h\\u01a1n n\\u1eefa nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:50:29', '2025-10-27 10:50:29'),
(39, 'conversation', NULL, '{\"user\": \"g\\u1ee3i \\u00fd \\u00e1o 200k cho t\\u00f4i\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! D\\u01b0\\u1edbi 200k, b\\u1ea1n c\\u00f3 th\\u1ec3 tham kh\\u1ea3o c\\u00e1c m\\u1eabu \\u00e1o \\u0111ang \\u0111\\u01b0\\u1ee3c y\\u00eau th\\u00edch nh\\u01b0 \\u00c1o Ph\\u00f4ng Basic (189k), \\u00c1o Croptop d\\u1ec7t kim (195k) ho\\u1eb7c \\u00c1o Hai D\\u00e2y l\\u1ee5a (165k). Nh\\u1eefng m\\u1eabu n\\u00e0y \\u0111\\u1ec1u d\\u1ec5 ph\\u1ed1i \\u0111\\u1ed3 v\\u00e0 r\\u1ea5t h\\u1ee3p v\\u1edbi ng\\u00e2n s\\u00e1ch c\\u1ee7a b\\u1ea1n \\u0111\\u1ea5y. B\\u1ea1n \\u0111\\u1eebng qu\\u00ean s\\u1eed d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 NEWUSER khi thanh to\\u00e1n \\u0111\\u1ec3 nh\\u1eadn \\u01b0u \\u0111\\u00e3i nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:51:07', '2025-10-27 10:51:07'),
(40, 'conversation', NULL, '{\"user\": \"t\\u00f4i mu\\u1ed1n mua 1 c\\u00e1i \\u00e1o gi\\u00e1 199k h\\u00e3y gokjiw \\u00fd cho t\\u00f4i\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! M\\u00ecnh r\\u1ea5t vui \\u0111\\u01b0\\u1ee3c h\\u1ed7 tr\\u1ee3 b\\u1ea1n t\\u00ecm \\u00e1o gi\\u00e1 199k.\\n\\nV\\u1edbi m\\u1ee9c gi\\u00e1 n\\u00e0y, b\\u1ea1n c\\u00f3 th\\u1ec3 tham kh\\u1ea3o c\\u00e1c m\\u1eabu \\u00c1o Thun Basic (T-shirt), \\u00c1o Croptop ho\\u1eb7c \\u00c1o Tank Top hi\\u1ec7n \\u0111ang r\\u1ea5t \\u0111\\u01b0\\u1ee3c \\u01b0a chu\\u1ed9ng. \\u0110\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 **NEWUSER** khi thanh to\\u00e1n \\u0111\\u1ec3 nh\\u1eadn th\\u00eam \\u01b0u \\u0111\\u00e3i nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:53:51', '2025-10-27 10:53:51'),
(41, 'conversation', NULL, '{\"user\": \"t\\u00f4i mu\\u1ed1n t\\u00ecm \\u00e1o 200k\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! V\\u1edbi ng\\u00e2n s\\u00e1ch 200k, shop m\\u00ecnh c\\u00f3 r\\u1ea5t nhi\\u1ec1u m\\u1eabu \\u00e1o ph\\u00f4ng c\\u01a1 b\\u1ea3n v\\u00e0 \\u00e1o ba l\\u1ed7 h\\u1ee3p th\\u1eddi trang, r\\u1ea5t d\\u1ec5 ph\\u1ed1i \\u0111\\u1ed3. B\\u1ea1n \\u0111\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 **NEWUSER** khi thanh to\\u00e1n \\u0111\\u1ec3 \\u0111\\u01b0\\u1ee3c h\\u01b0\\u1edfng \\u01b0u \\u0111\\u00e3i nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:56:44', '2025-10-27 10:56:44'),
(42, 'conversation', NULL, '{\"user\": \"cho t\\u00f4i t\\u00ean c\\u1ee5 th\\u1ec3 c\\u1ee7a \\u00e1o\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! B\\u1ea1n vui l\\u00f2ng cho m\\u00ecnh bi\\u1ebft b\\u1ea1n \\u0111ang quan t\\u00e2m \\u0111\\u1ebfn m\\u1eabu \\u00e1o n\\u00e0o (m\\u00e3 s\\u1ea3n ph\\u1ea9m ho\\u1eb7c m\\u00f4 t\\u1ea3) \\u0111\\u1ec3 m\\u00ecnh cung c\\u1ea5p t\\u00ean c\\u1ee5 th\\u1ec3 nh\\u00e9.\\n\\nR\\u1ea5t s\\u1eb5n l\\u00f2ng h\\u1ed7 tr\\u1ee3 b\\u1ea1n! \\u0110\\u1eebng qu\\u00ean s\\u1eed d\\u1ee5ng m\\u00e3 gi\\u1ea3m gi\\u00e1 **NEWUSER** cho \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean c\\u1ee7a m\\u00ecnh nha.\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:57:06', '2025-10-27 10:57:06'),
(43, 'conversation', NULL, '{\"user\": \"\\u00e1o n\\u00e0o 200k\", \"bot\": \"Ch\\u00e0o b\\u1ea1n! Hi\\u1ec7n t\\u1ea1i, c\\u1eeda h\\u00e0ng c\\u00f3 nhi\\u1ec1u m\\u1eabu \\u00e1o thun v\\u00e0 \\u00e1o ph\\u00f4ng c\\u01a1 b\\u1ea3n v\\u1edbi gi\\u00e1 ch\\u1ec9 t\\u1eeb 199.000 VN\\u0110.\\n\\nB\\u1ea1n c\\u00f3 th\\u1ec3 d\\u1ec5 d\\u00e0ng t\\u00ecm th\\u1ea5y c\\u00e1c s\\u1ea3n ph\\u1ea9m n\\u00e0y b\\u1eb1ng c\\u00e1ch s\\u1eed d\\u1ee5ng b\\u1ed9 l\\u1ecdc gi\\u00e1 d\\u01b0\\u1edbi 200k trong m\\u1ee5c \\\"\\u00c1o\\\" nh\\u00e9. \\u0110\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 **NEWUSER** \\u0111\\u1ec3 \\u0111\\u01b0\\u1ee3c gi\\u1ea3m th\\u00eam cho \\u0111\\u01a1n h\\u00e0ng \\u0111\\u1ea7u ti\\u00ean c\\u1ee7a m\\u00ecnh!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:59:01', '2025-10-27 10:59:01'),
(44, 'conversation', NULL, '{\"user\": \"gi\\u00e0y c\\u00f3 s\\u1ea3n ph\\u1ea9m n\\u00e0o\", \"bot\": \"Ch\\u00e0o b\\u1ea1n, hi\\u1ec7n t\\u1ea1i c\\u1eeda h\\u00e0ng ch\\u00fang t\\u00f4i c\\u00f3 r\\u1ea5t nhi\\u1ec1u m\\u1eabu gi\\u00e0y \\u0111a d\\u1ea1ng.\\n\\nB\\u1ea1n c\\u00f3 th\\u1ec3 tham kh\\u1ea3o c\\u00e1c d\\u00f2ng s\\u1ea3n ph\\u1ea9m n\\u1ed5i b\\u1eadt nh\\u01b0 Gi\\u00e0y sneakers n\\u0103ng \\u0111\\u1ed9ng, Gi\\u00e0y loafers l\\u1ecbch s\\u1ef1, ho\\u1eb7c Boots th\\u1eddi trang.\\n\\n\\u0110\\u1eebng qu\\u00ean \\u00e1p d\\u1ee5ng m\\u00e3 **NEWUSER** khi thanh to\\u00e1n \\u0111\\u1ec3 \\u0111\\u01b0\\u1ee3c \\u01b0u \\u0111\\u00e3i nh\\u00e9!\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 10:59:19', '2025-10-27 10:59:19'),
(45, 'conversation', NULL, '{\"user\": \"hello\", \"bot\": \"Ch\\u00e0o b\\u1ea1n, t\\u00f4i l\\u00e0 tr\\u1ee3 l\\u00fd th\\u1eddi trang c\\u1ee7a b\\u1ea1n! B\\u1ea1n c\\u1ea7n t\\u01b0 v\\u1ea5n g\\u00ec v\\u1ec1 phong c\\u00e1ch hay s\\u1ea3n ph\\u1ea9m n\\u00e0o kh\\u00f4ng?\", \"metadata\": {}}', NULL, 0, NULL, '2025-10-27 12:36:30', '2025-10-27 12:36:30'),
(46, 'conversation', 89, '{\"message\":\"t\\u00f4i mu\\u1ed1n t\\u00ecm \\u00e1o v\\u1edbi gi\\u00e1 200k\",\"metadata\":null}', 'recommend', 0, NULL, '2025-10-27 12:49:06', '2025-10-27 12:49:06'),
(47, 'conversation', 103, '{\"message\":\"gi\\u00e0y c\\u00f3 s\\u1ea3n ph\\u1ea9m n\\u00e0o\",\"metadata\":null}', 'recommend', 0, NULL, '2025-10-27 12:49:14', '2025-10-27 12:49:14'),
(48, 'conversation', 101, '{\"message\":\"\\u00e1o n\\u00e0o 200k\",\"metadata\":null}', 'recommend', 0, NULL, '2025-10-27 12:49:19', '2025-10-27 12:49:19'),
(49, 'conversation', 77, '{\"message\":\"t\\u00f4i cao 1m6 n\\u1eb7ng 70kg th\\u00ec c\\u00f3 size n\\u00e0o ph\\u00f9 h\\u1ee3p\",\"metadata\":null}', 'ask_size', 0, NULL, '2025-10-27 12:49:33', '2025-10-27 12:49:33'),
(50, 'conversation', 87, '{\"message\":\"hi\",\"metadata\":null}', 'general', 0, NULL, '2025-10-27 12:49:42', '2025-10-27 12:49:42');

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
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `id` int(11) NOT NULL,
  `type` enum('banner','promo','announcement') NOT NULL DEFAULT 'banner',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `position` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` datetime DEFAULT NULL COMMENT 'Ngày bắt đầu hiển thị',
  `end_date` datetime DEFAULT NULL COMMENT 'Ngày kết thúc hiển thị',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `type`, `title`, `description`, `image_url`, `link_url`, `button_text`, `position`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'banner', 'Bộ sưu tập Thu Đông 2025', 'Xu hướng thời trang mới nhất', 'banner1.jpg', '/GoodZStore/Views/Users/products.php?category=1', 'Mua ngay', 1, 1, '2025-09-01 00:00:00', '2025-12-31 23:59:59', '2025-10-01 13:53:02', '2025-10-01 13:53:02'),
(2, 'banner', 'Sale Up To 50%', 'Giảm giá sốc cuối tuần', 'banner2.jpg', '/GoodZStore/Views/Users/products.php?sort=price_asc', 'Khám phá', 2, 1, '2025-09-01 00:00:00', '2025-09-30 23:59:59', '2025-10-01 13:53:02', '2025-10-01 13:53:02'),
(3, 'banner', 'Free Ship Toàn Quốc', 'Đơn hàng từ 500k', 'banner3.jpg', '/GoodZStore/Views/Users/products.php', 'Xem ngay', 3, 1, '2025-09-01 00:00:00', '2025-12-31 23:59:59', '2025-10-01 13:53:02', '2025-10-01 13:53:02'),
(4, 'promo', 'Giảm 20% cho đơn hàng đầu tiên!', 'Áp dụng cho tất cả sản phẩm', 'promo1.jpg', '/GoodZStore/Views/Users/products.php', 'Nhận ưu đãi', 1, 1, '2025-09-01 00:00:00', '2025-12-31 23:59:59', '2025-10-01 13:53:02', '2025-10-01 13:53:02'),
(5, 'promo', 'Mua 2 Tặng 1', 'Áp dụng cho áo thun', 'promo2.jpg', '/GoodZStore/Views/Users/category.php?id=1', 'Mua ngay', 2, 1, '2025-09-01 00:00:00', '2025-10-31 23:59:59', '2025-10-01 13:53:02', '2025-10-01 13:53:02'),
(6, 'promo', 'Flash Sale 12h - 14h', 'Giảm đến 70% các sản phẩm hot', 'promo3.jpg', '/GoodZStore/Views/Users/products.php?sort=price_desc', 'Xem ngay', 3, 1, '2025-09-01 00:00:00', '2025-09-30 23:59:59', '2025-10-01 13:53:02', '2025-10-01 13:53:02');

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
(2, 'Trần Thị B', 'b@example.com', '$2y$10$DsHteq5IdIQxwaKg90fB1O6yYBY2s.g1.E7T4I/I4qH/L2.8YNaQ.', '0902345678', 'TP.HCM', 'customer', '2025-09-22 03:53:15', '2025-10-27 08:42:32'),
(3, 'Lê Văn C', 'c@example.com', '123456', '0903456789', 'Đà Nẵng', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(4, 'Phạm Thị D', 'd@example.com', '123456', '0904567890', 'Cần Thơ', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(5, 'Hoàng Văn E', 'e@example.com', '123456', '0905678901', 'Hải Phòng', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(6, 'Đỗ Thị F', 'f@example.com', '$2y$10$3l2g83Xt4n8JBbkeH9V8rO6YRtRy//I7AojmchfFjYdT/C5AYZL3K', '0906789012', 'Huế', 'customer', '2025-09-22 03:53:15', '2025-10-01 01:15:21'),
(7, 'Ngô Văn Giáp', 'g@example.com', '$2y$10$5mHeq4RZMAqKR1gHATHQF.ppXVYN9gbiUN3rsGRPKDTljWplQDBb.', '0907890123', 'Bình Dương sài gòn', 'customer', '2025-09-22 03:53:15', '2025-10-01 01:05:10'),
(8, 'Vũ Thị H', 'h@example.com', '123456', '0908901234', 'Quảng Ninh', 'customer', '2025-09-22 03:53:15', '2025-09-22 13:34:07'),
(9, 'Admin 1', 'admin1@example.com', '$2y$10$Njjd9FxHQJTQ2X764wP1ROImaoInh34Mjww4Zvlqb2GFnCM9pqBhq', '0909012345', 'Hà Nội', 'admin', '2025-09-22 03:53:15', '2025-10-03 13:28:29'),
(10, 'Admin 2', 'admin2@example.com', '$2y$10$Z6YbQy6BcywPbrsaIj4DKuF6x7xS7bXfi52eCsto1.xA4O1V/0HzO', '0910123456', 'TP.HCM', 'admin', '2025-09-22 03:53:15', '2025-10-27 08:44:05'),
(11, 'Lợi', 'ntloi1910@gmail.com', '$2y$10$rwpoyGk6tZQwWVsVms7EhuFG23SxM9n.QVrcsbDfPEGRbVFRDxVXS', '123', NULL, 'customer', '2025-09-23 08:52:36', '2025-09-23 08:52:36'),
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

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_conversation_threads`
-- (See below for the actual view)
--
CREATE TABLE `v_conversation_threads` (
`session_id` varchar(100)
,`user_id` int(11)
,`user_name` varchar(255)
,`message_count` bigint(21)
,`started_at` timestamp
,`last_message_at` timestamp
,`conversation_preview` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_training_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_training_stats` (
`label` varchar(50)
,`total_count` bigint(21)
,`validated_count` decimal(22,0)
,`avg_quality` decimal(7,4)
,`first_added` timestamp
,`last_added` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `v_conversation_threads`
--
DROP TABLE IF EXISTS `v_conversation_threads`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_conversation_threads`  AS SELECT `c`.`session_id` AS `session_id`, `c`.`user_id` AS `user_id`, `u`.`full_name` AS `user_name`, count(0) AS `message_count`, min(`c`.`created_at`) AS `started_at`, max(`c`.`created_at`) AS `last_message_at`, group_concat(concat(`c`.`direction`,': ',substr(`c`.`message`,1,50)) order by `c`.`created_at` ASC separator ' | ') AS `conversation_preview` FROM (`ai_conversations` `c` left join `users` `u` on(`c`.`user_id` = `u`.`id`)) GROUP BY `c`.`session_id`, `c`.`user_id`, `u`.`full_name` ;

-- --------------------------------------------------------

--
-- Structure for view `v_training_stats`
--
DROP TABLE IF EXISTS `v_training_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_training_stats`  AS SELECT `ai_training_data`.`label` AS `label`, count(0) AS `total_count`, sum(case when `ai_training_data`.`is_validated` = 1 then 1 else 0 end) AS `validated_count`, avg(`ai_training_data`.`quality_score`) AS `avg_quality`, min(`ai_training_data`.`created_at`) AS `first_added`, max(`ai_training_data`.`created_at`) AS `last_added` FROM `ai_training_data` GROUP BY `ai_training_data`.`label` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_direction` (`direction`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_intent` (`intent`);

--
-- Indexes for table `ai_training_data`
--
ALTER TABLE `ai_training_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_source` (`source`),
  ADD KEY `idx_label` (`label`),
  ADD KEY `idx_validated` (`is_validated`),
  ADD KEY `idx_created` (`created_at`);
ALTER TABLE `ai_training_data` ADD FULLTEXT KEY `ft_text` (`text`);

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
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_type_position` (`type`,`position`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_position` (`position`);

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
-- AUTO_INCREMENT for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `ai_training_data`
--
ALTER TABLE `ai_training_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

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
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD CONSTRAINT `ai_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
