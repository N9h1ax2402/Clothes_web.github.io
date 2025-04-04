-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 04, 2025 lúc 04:34 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `product`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product`
--

CREATE TABLE `product` (
  `id` int(6) UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL,
  `price` varchar(30) NOT NULL,
  `image` varchar(50) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product`
--

INSERT INTO `product` (`id`, `name`, `price`, `image`, `reg_date`) VALUES
(1, 'Textured Track jacket', '45.881.000 VNĐ', '../../public/images/p1.webp', '2025-04-02 09:37:44'),
(2, 'Classic Short', '17.595.000 VNĐ', '../../public/images/p2.webp', '2025-04-02 09:38:17'),
(3, 'External Knit Tee', '35.053.000 VNĐ', '../../public/images/p3.webp', '2025-04-02 09:39:01'),
(4, 'External Long Sleeve Tee', '7.986.000 VNĐ', '../../public/images/p4.webp', '2025-04-02 09:39:07'),
(5, 'External Fleece Full Zip Hoodi', '18.813.000 VNĐ', '../../public/images/p5.webp', '2025-04-02 09:39:13'),
(6, 'External jersey Tee', '6.767.000 VNĐ', '../../public/images/p6.webp', '2025-04-02 09:39:19'),
(7, 'External Hoodie', '14.888.000 VNĐ', '../../public/images/p7.webp', '2025-04-02 09:39:24'),
(8, 'External Sweatpant', '13.399.000 VNĐ', '../../public/images/p8.webp', '2025-04-02 09:39:29'),
(9, 'External Fleece Crewneck', '35.053.000 VNĐ', '../../public/images/p9.webp', '2025-04-02 09:39:33'),
(10, 'External jersey Tee', '6.767.000 VNĐ', '../../public/images/p10.webp', '2025-04-02 09:39:38'),
(11, 'External Sweatshort', '6.767.000 VNĐ', '../../public/images/p11.webp', '2025-04-02 09:39:42'),
(12, 'External Half Zip Hoodie', '17.595.000 VNĐ', '../../public/images/p12.webp', '2025-04-02 09:39:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `created_at`) VALUES
(1, 'paulaml117@gmail.com', '$2y$10$cvNY9cduV.75DRXaKH55xeiLVBr.ixvluETEthXKWJ63TehtvhnZW', 'nghia', '2025-04-03 09:43:09');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `product`
--
ALTER TABLE `product`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
