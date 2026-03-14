-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2026 at 08:40 AM
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
-- Database: `coreinventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Electronics'),
(2, 'Office Supplies'),
(3, 'Raw Materials'),
(4, 'Packaging'),
(5, 'Safety & PPE');

-- --------------------------------------------------------

--
-- Table structure for table `operations`
--

CREATE TABLE `operations` (
  `id` int(11) NOT NULL,
  `type` enum('receipt','delivery','transfer','adjustment') NOT NULL,
  `status` enum('draft','waiting','ready','done','canceled') DEFAULT 'draft',
  `reference` varchar(100) DEFAULT NULL,
  `supplier_customer` varchar(150) DEFAULT NULL,
  `from_warehouse_id` int(11) DEFAULT NULL,
  `to_warehouse_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `validated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operations`
--

INSERT INTO `operations` (`id`, `type`, `status`, `reference`, `supplier_customer`, `from_warehouse_id`, `to_warehouse_id`, `notes`, `created_by`, `created_at`, `validated_at`) VALUES
(1, 'receipt', 'done', 'REC-2025-001', 'TechSupplies Co.', 1, NULL, 'Initial stock intake Q1', 1, '2026-03-14 04:16:44', NULL),
(2, 'receipt', 'done', 'REC-2025-002', 'ElecWorld Ltd.', 1, NULL, 'Electronics restock', 2, '2026-03-14 04:16:44', NULL),
(3, 'receipt', 'done', 'REC-2025-003', 'Industrial Depot', 2, NULL, 'Secondary store restock', 2, '2026-03-14 04:16:44', NULL),
(4, 'receipt', 'waiting', 'REC-2025-004', 'SafetyGear Inc.', 1, NULL, 'Awaiting PPE restock', 2, '2026-03-14 04:16:44', NULL),
(5, 'receipt', 'waiting', 'REC-2025-005', 'Office Plus', 2, NULL, 'Office supplies incoming', 3, '2026-03-14 04:16:44', NULL),
(6, 'delivery', 'done', 'DEL-2025-001', 'Customer #C1042', 1, NULL, 'Customer order dispatch', 1, '2026-03-14 04:16:44', NULL),
(7, 'delivery', 'done', 'DEL-2025-002', 'Site B Operations', 1, NULL, 'Dispatch to site B', 2, '2026-03-14 04:16:44', NULL),
(8, 'delivery', 'waiting', 'DEL-2025-003', 'Urgent Client #C1055', 1, NULL, 'Urgent order awaiting dispatch', 3, '2026-03-14 04:16:44', NULL),
(9, 'transfer', 'done', 'TRF-2025-001', NULL, 1, 2, 'Rebalance raw materials', 2, '2026-03-14 04:16:44', NULL),
(10, 'adjustment', 'done', 'ADJ-2025-001', NULL, 1, NULL, 'Monthly physical count correction', 1, '2026-03-14 04:16:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `operation_items`
--

CREATE TABLE `operation_items` (
  `id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operation_items`
--

INSERT INTO `operation_items` (`id`, `operation_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 20.00),
(2, 1, 2, 50.00),
(3, 1, 3, 25.00),
(4, 1, 4, 80.00),
(5, 2, 5, 15.00),
(6, 2, 7, 30.00),
(7, 2, 8, 25.00),
(8, 3, 10, 100.00),
(9, 3, 11, 50.00),
(10, 3, 13, 150.00),
(11, 4, 15, 20.00),
(12, 4, 16, 30.00),
(13, 4, 17, 15.00),
(14, 5, 6, 50.00),
(15, 5, 7, 20.00),
(16, 6, 1, 2.00),
(17, 6, 2, 5.00),
(18, 6, 4, 10.00),
(19, 7, 6, 20.00),
(20, 7, 8, 10.00),
(21, 7, 13, 30.00),
(22, 8, 5, 3.00),
(23, 8, 3, 5.00),
(24, 9, 10, 50.00),
(25, 9, 11, 20.00),
(26, 10, 9, 3.00),
(27, 10, 17, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT 'pcs',
  `reorder_level` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `sku`, `category_id`, `unit`, `reorder_level`, `created_at`) VALUES
(1, 'Laptop Dell XPS 15', 'ELEC-DX15-001', 1, 'pcs', 5, '2026-03-14 04:16:44'),
(2, 'Wireless Mouse Logitech', 'ELEC-LM-002', 1, 'pcs', 10, '2026-03-14 04:16:44'),
(3, 'USB-C Hub 7-Port', 'ELEC-HUB-003', 1, 'pcs', 8, '2026-03-14 04:16:44'),
(4, 'HDMI Cable 2m', 'ELEC-HDM-004', 1, 'pcs', 15, '2026-03-14 04:16:44'),
(5, 'Mechanical Keyboard', 'ELEC-KB-005', 1, 'pcs', 6, '2026-03-14 04:16:44'),
(6, 'A4 Paper Ream', 'OFF-PPR-001', 2, 'ream', 20, '2026-03-14 04:16:44'),
(7, 'Ballpoint Pen Box', 'OFF-PEN-002', 2, 'box', 12, '2026-03-14 04:16:44'),
(8, 'Sticky Notes Pack', 'OFF-STK-003', 2, 'pack', 10, '2026-03-14 04:16:44'),
(9, 'Stapler Heavy Duty', 'OFF-STP-004', 2, 'pcs', 4, '2026-03-14 04:16:44'),
(10, 'Aluminium Sheet 1mm', 'RAW-ALU-001', 3, 'kg', 50, '2026-03-14 04:16:44'),
(11, 'PVC Pipe 20mm', 'RAW-PVC-002', 3, 'mtr', 30, '2026-03-14 04:16:44'),
(12, 'Copper Wire 1.5mm', 'RAW-COP-003', 3, 'kg', 25, '2026-03-14 04:16:44'),
(13, 'Cardboard Box Large', 'PKG-BOX-001', 4, 'pcs', 40, '2026-03-14 04:16:44'),
(14, 'Bubble Wrap Roll 50m', 'PKG-BWR-002', 4, 'roll', 5, '2026-03-14 04:16:44'),
(15, 'Safety Helmet', 'PPE-HLM-001', 5, 'pcs', 10, '2026-03-14 04:16:44'),
(16, 'Nitrile Gloves Box', 'PPE-GLV-002', 5, 'box', 15, '2026-03-14 04:16:44'),
(17, 'Hi-Vis Vest', 'PPE-VIS-003', 5, 'pcs', 8, '2026-03-14 04:16:44'),
(18, 'block chain in iot', 'WM0399C', 1, 'sadasd', 10, '2026-03-14 07:33:46');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `product_id`, `warehouse_id`, `quantity`) VALUES
(1, 1, 1, 18.00),
(2, 2, 1, 45.00),
(3, 3, 1, 22.00),
(4, 4, 1, 60.00),
(5, 5, 1, 14.00),
(6, 6, 1, 80.00),
(7, 7, 1, 30.00),
(8, 8, 1, 25.00),
(9, 9, 1, 3.00),
(10, 10, 1, 120.00),
(11, 11, 1, 75.00),
(12, 12, 1, 55.00),
(13, 13, 1, 200.00),
(14, 14, 1, 8.00),
(15, 15, 1, 20.00),
(16, 16, 1, 40.00),
(17, 17, 1, 5.00),
(18, 1, 2, 4.00),
(19, 2, 2, 20.00),
(20, 3, 2, 10.00),
(21, 5, 2, 3.00),
(22, 6, 2, 35.00),
(23, 10, 2, 80.00),
(24, 13, 2, 100.00),
(25, 14, 2, 4.00),
(26, 15, 2, 12.00),
(27, 16, 2, 18.00),
(28, 12, 3, 30.00),
(29, 11, 3, 20.00),
(30, 18, 1, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `stock_ledger`
--

CREATE TABLE `stock_ledger` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `operation_id` int(11) DEFAULT NULL,
  `change_qty` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_ledger`
--

INSERT INTO `stock_ledger` (`id`, `product_id`, `warehouse_id`, `operation_id`, `change_qty`, `balance_after`, `reason`, `created_by`, `created_at`) VALUES
(1, 1, 1, 1, 20.00, 20.00, 'REC-2025-001 intake', 1, '2026-03-14 04:18:48'),
(2, 2, 1, 1, 50.00, 50.00, 'REC-2025-001 intake', 1, '2026-03-14 04:18:48'),
(3, 3, 1, 1, 25.00, 25.00, 'REC-2025-001 intake', 1, '2026-03-14 04:18:48'),
(4, 4, 1, 1, 80.00, 80.00, 'REC-2025-001 intake', 1, '2026-03-14 04:18:48'),
(5, 5, 1, 2, 15.00, 15.00, 'REC-2025-002 electronics', 2, '2026-03-14 04:18:48'),
(6, 7, 1, 2, 30.00, 30.00, 'REC-2025-002 electronics', 2, '2026-03-14 04:18:48'),
(7, 8, 1, 2, 25.00, 25.00, 'REC-2025-002 electronics', 2, '2026-03-14 04:18:48'),
(8, 10, 2, 3, 100.00, 100.00, 'REC-2025-003 secondary store', 2, '2026-03-14 04:18:48'),
(9, 11, 2, 3, 50.00, 50.00, 'REC-2025-003 secondary store', 2, '2026-03-14 04:18:48'),
(10, 13, 2, 3, 150.00, 150.00, 'REC-2025-003 secondary store', 2, '2026-03-14 04:18:48'),
(11, 1, 1, 6, -2.00, 18.00, 'DEL-2025-001 dispatched', 1, '2026-03-14 04:18:48'),
(12, 2, 1, 6, -5.00, 45.00, 'DEL-2025-001 dispatched', 1, '2026-03-14 04:18:48'),
(13, 4, 1, 6, -10.00, 70.00, 'DEL-2025-001 dispatched', 1, '2026-03-14 04:18:48'),
(14, 6, 1, 7, -20.00, 80.00, 'DEL-2025-002 dispatched', 2, '2026-03-14 04:18:48'),
(15, 8, 1, 7, -10.00, 15.00, 'DEL-2025-002 dispatched', 2, '2026-03-14 04:18:48'),
(16, 13, 1, 7, -30.00, 200.00, 'DEL-2025-002 dispatched', 2, '2026-03-14 04:18:48'),
(17, 10, 1, 9, -50.00, 120.00, 'TRF-2025-001 out Main WH', 2, '2026-03-14 04:18:48'),
(18, 10, 2, 9, 50.00, 80.00, 'TRF-2025-001 in Secondary', 2, '2026-03-14 04:18:48'),
(19, 11, 1, 9, -20.00, 75.00, 'TRF-2025-001 out Main WH', 2, '2026-03-14 04:18:48'),
(20, 11, 2, 9, 20.00, 20.00, 'TRF-2025-001 in Secondary', 2, '2026-03-14 04:18:48'),
(21, 9, 1, 10, 3.00, 3.00, 'ADJ-2025-001 count correction', 1, '2026-03-14 04:18:48'),
(22, 17, 1, 10, 5.00, 5.00, 'ADJ-2025-001 count correction', 1, '2026-03-14 04:18:48'),
(23, 18, 1, NULL, 1.00, 1.00, 'Initial stock on product creation', 3, '2026-03-14 07:33:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@coreinventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-03-14 04:16:44'),
(2, 'Sarah Manager', 'manager@coreinventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '2026-03-14 04:16:44'),
(3, 'Jake Staff', 'staff@coreinventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', '2026-03-14 04:16:44');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `location`, `created_at`) VALUES
(1, 'Main Warehouse', 'Building A, Ground Floor', '2026-03-14 04:16:44'),
(2, 'Secondary Storage', 'Building B, Level 2', '2026-03-14 04:16:44'),
(3, 'Cold Storage', 'Annex Unit, Block C', '2026-03-14 04:16:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operations`
--
ALTER TABLE `operations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_warehouse_id` (`from_warehouse_id`),
  ADD KEY `to_warehouse_id` (`to_warehouse_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `operation_items`
--
ALTER TABLE `operation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operation_id` (`operation_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stock` (`product_id`,`warehouse_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `stock_ledger`
--
ALTER TABLE `stock_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `operation_id` (`operation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `operations`
--
ALTER TABLE `operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `operation_items`
--
ALTER TABLE `operation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `stock_ledger`
--
ALTER TABLE `stock_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `operations`
--
ALTER TABLE `operations`
  ADD CONSTRAINT `operations_ibfk_1` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operations_ibfk_2` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `operation_items`
--
ALTER TABLE `operation_items`
  ADD CONSTRAINT `operation_items_ibfk_1` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `operation_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_ledger`
--
ALTER TABLE `stock_ledger`
  ADD CONSTRAINT `stock_ledger_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ledger_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ledger_ibfk_3` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_ledger_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
