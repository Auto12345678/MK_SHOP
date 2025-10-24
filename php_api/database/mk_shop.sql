-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 07:21 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mk_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '1234');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `username`, `password`, `profile_picture`) VALUES
(3, 'Michael', 'Johnson', 'mikejohnson', 'mypassword789', 'michael_johnson.jpg'),
(4, 'EmilyMonkey', 'Davis1112', 'emilydavis22', 'emily123433', '1760726888_Macaca_nigra_self-portrait_large.jpg'),
(5, 'Autoๅๅๅ', 'Auto', 'Auto', '1234', '1760726853_10.webp'),
(6, '112', '22', '11', '22', '1760726746_images.jfif');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `table_no` varchar(10) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'รอดำเนินการ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `table_no`, `total_price`, `order_date`, `status`) VALUES
(7, '4', '898.00', '2025-10-12 21:47:29', 'เสร็จแล้ว'),
(10, '2', '0.00', '2025-10-15 21:58:39', 'เสร็จแล้ว'),
(11, '1', '0.00', '2025-10-15 22:17:19', 'เสร็จแล้ว'),
(12, '1', '1098.00', '2025-10-19 22:18:37', 'เสร็จแล้ว'),
(14, '2', '1098.00', '2025-10-16 11:37:40', 'เสร็จแล้ว'),
(16, '3', '29737.00', '2025-10-16 15:39:20', 'เสร็จแล้ว'),
(17, '5', '2347.00', '2025-10-16 15:52:12', 'เสร็จแล้ว'),
(18, '2', '3633.00', '2025-10-16 16:01:49', 'เสร็จแล้ว'),
(19, '1', '1098.00', '2025-10-22 17:23:16', 'เสร็จแล้ว'),
(20, '2', '1746.00', '2025-10-22 17:30:05', 'เสร็จแล้ว'),
(21, '5', '0.00', '2025-10-22 21:40:06', 'เสร็จแล้ว'),
(22, '1', '0.00', '2025-10-22 22:28:16', 'เสร็จแล้ว'),
(26, '2', '0.00', '2025-10-22 23:33:51', 'เสร็จแล้ว'),
(27, '4', '0.00', '2025-10-22 23:53:24', 'เสร็จแล้ว'),
(28, '2', '0.00', '2025-10-23 08:52:04', 'เสร็จแล้ว'),
(29, '1', '0.00', '2025-10-23 09:35:12', 'เสร็จแล้ว'),
(30, '4', '0.00', '2025-10-23 10:07:16', 'เสร็จแล้ว');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'รอดำเนินการ' COMMENT 'status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `status`) VALUES
(3, 7, 10, 2, '199.00', '398.00', 'เสร็จแล้ว'),
(4, 7, 13, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(11, 10, 9, 1, '250.00', '250.00', 'เสร็จแล้ว'),
(13, 11, 9, 1, '250.00', '250.00', 'ยกเลิก'),
(14, 11, 10, 2, '199.00', '398.00', 'เสร็จแล้ว'),
(15, 11, 17, 1, '299.00', '299.00', 'เสร็จแล้ว'),
(16, 12, 14, 2, '299.00', '598.00', 'เสร็จแล้ว'),
(17, 12, 9, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(20, 14, 14, 2, '299.00', '598.00', 'เสร็จแล้ว'),
(21, 14, 13, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(27, 16, 8, 3, '280.00', '840.00', 'เสร็จแล้ว'),
(28, 16, 17, 3, '299.00', '897.00', 'เสร็จแล้ว'),
(29, 17, 9, 4, '250.00', '1000.00', 'เสร็จแล้ว'),
(30, 17, 10, 3, '199.00', '597.00', 'เสร็จแล้ว'),
(31, 17, 13, 3, '250.00', '750.00', 'เสร็จแล้ว'),
(32, 18, 8, 3, '280.00', '840.00', 'เสร็จแล้ว'),
(33, 18, 9, 4, '250.00', '1000.00', 'เสร็จแล้ว'),
(34, 18, 10, 3, '199.00', '597.00', 'เสร็จแล้ว'),
(35, 18, 14, 4, '299.00', '1196.00', 'เสร็จแล้ว'),
(36, 19, 13, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(37, 19, 14, 2, '299.00', '598.00', 'เสร็จแล้ว'),
(38, 20, 10, 2, '199.00', '398.00', 'เสร็จแล้ว'),
(39, 20, 9, 3, '250.00', '750.00', 'เสร็จแล้ว'),
(40, 20, 14, 2, '299.00', '598.00', 'เสร็จแล้ว'),
(41, 21, 9, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(42, 21, 10, 3, '199.00', '597.00', 'เสร็จแล้ว'),
(43, 21, 13, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(44, 22, 9, 1, '250.00', '250.00', 'เสร็จแล้ว'),
(45, 22, 10, 1, '199.00', '199.00', 'เสร็จแล้ว'),
(56, 26, 9, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(60, 27, 13, 3, '250.00', '750.00', 'เสร็จแล้ว'),
(61, 27, 14, 2, '299.00', '598.00', 'เสร็จแล้ว'),
(62, 28, 9, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(63, 28, 10, 2, '199.00', '398.00', 'เสร็จแล้ว'),
(64, 29, 9, 2, '250.00', '500.00', 'เสร็จแล้ว'),
(66, 29, 14, 2, '299.00', '598.00', 'เสร็จแล้ว'),
(67, 30, 8, 2, '280.00', '560.00', 'เสร็จแล้ว'),
(68, 30, 9, 2, '250.00', '500.00', 'เสร็จแล้ว');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` text DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `image`, `stock`, `created_at`) VALUES
(8, 'ชุดผักรวม ชื่นใจ', 'ชุดผักรวมและหมู ชื่นใจ', '280.00', '1761182155_1761180238_3.jfif', 20, '2025-09-11 03:36:39'),
(9, 'หมูเนื้อแดงรวมผัก', 'ชุด 1 หมูเนื้อแดงรวมผักสด', '250.00', '1761182114_1758077855_p5.jpg', 50, '2025-09-17 02:57:35'),
(10, 'ชุด 2 ลูกชิ้น + หมูสามชั้น', 'ชุด 2 ลูกชิ้น + หมูสามชั้น', '199.00', '1761182102_1760498908_p6.jpg', 20, '2025-09-17 02:58:35'),
(11, 'สันคอหมูรวมผัก', 'ชุด 3 สันคอหมูรวมผัก', '220.00', '1761182089_1760589547_p1.jpg', 20, '2025-09-17 02:59:44'),
(12, 'ชุดรวมมิตร MK', 'ชุดรวมมิตร MK ฉ่ำ ๆ', '299.00', '1761182063_3.jfif', 20, '2025-09-17 04:08:58'),
(13, 'ชาบูน้ำดำ หมู ผักสด', 'ชุด 5 ชาบูน้ำดำ หมู ผักสด', '250.00', '1761182047_1761180305_2.jfif', 20, '2025-09-17 04:15:07'),
(14, 'ชุดรวมมิตรชาบู', 'รวมมิตร หมู ลูกชิ้น ผักสด', '299.00', '1761182034_1758077915_p6.jpg', 20, '2025-09-18 07:33:32'),
(15, 'สันคอหมูล้วน', 'สันคอหมูล้วน เกาหลี', '220.00', '1761182020_7.jfif', 20, '2025-09-18 07:42:25'),
(17, 'ชุดรวมหมู่ชาบู', 'ชุดรวมหมู่ชาบู หมูเนื้อแดง ผักรวม', '299.00', '1761182011_1760511137_p7.jpg', 30, '2025-10-15 03:18:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
