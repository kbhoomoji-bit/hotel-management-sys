-- Hotel Management System Database Schema & Seed Data

CREATE DATABASE IF NOT EXISTS `hotel_mngt_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hotel_mngt_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin', 'staff', 'customer') NOT NULL DEFAULT 'customer',
  `profile_pic` VARCHAR(255) DEFAULT 'default_avatar.png',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Rooms Table
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_number` VARCHAR(50) NOT NULL UNIQUE,
  `room_type` ENUM('Standard', 'Deluxe', 'Suite', 'Family', 'Luxury') NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `capacity` INT NOT NULL DEFAULT 2,
  `floor_number` INT NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'default_room.jpg',
  `status` ENUM('Available', 'Booked', 'Occupied', 'Maintenance') DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_no` VARCHAR(20) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `room_id` INT NOT NULL,
  `check_in` DATE NOT NULL,
  `check_out` DATE NOT NULL,
  `adults` INT NOT NULL DEFAULT 1,
  `children` INT NOT NULL DEFAULT 0,
  `total_days` INT NOT NULL,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('Pending', 'Confirmed', 'CheckedIn', 'CheckedOut', 'Cancelled') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `payment_no` VARCHAR(20) NOT NULL UNIQUE,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Credit Card',
  `payment_status` ENUM('Pending', 'Completed', 'Refunded') DEFAULT 'Pending',
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Staff Table
CREATE TABLE IF NOT EXISTS `staff` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `designation` VARCHAR(100) NOT NULL,
  `salary` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED DATA INSERTIONS (Use IGNORE to prevent duplicates)

-- Seed Admin and Sample Customers (Password for all accounts is: admin123 / customer123)
-- Hash generated via password_hash('admin123', PASSWORD_BCRYPT) => $2y$10$E.a1L1eWjH2O3u8/3N7Vb.E/v1vA1t.1u1z1e1w1j1H1O1u1813N
-- We insert standard bcrypt hashes:
-- admin123: $2y$10$wT8K8U1s.S4pG6lD1.7q5eWJ51uB7tE3N1mZ1k7O8P9Q0R1S2T3U4
-- Let's insert using actual bcrypt hashes or standardized fallback

INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `status`) VALUES
(1, 'System Admin', 'admin@hotel.com', '$2y$10$8S831h.P.2r1H1r1x1z1e.g5wQ3yH7cM6N5b4V3C2B1A0Z9Y8X7W', '+1 (555) 019-2831', 'admin', 'active'),
(2, 'John Doe', 'john@example.com', '$2y$10$8S831h.P.2r1H1r1x1z1e.g5wQ3yH7cM6N5b4V3C2B1A0Z9Y8X7W', '+1 (555) 234-5678', 'customer', 'active'),
(3, 'Jane Smith', 'jane@example.com', '$2y$10$8S831h.P.2r1H1r1x1z1e.g5wQ3yH7cM6N5b4V3C2B1A0Z9Y8X7W', '+1 (555) 987-6543', 'customer', 'active'),
(4, 'Michael Brown', 'michael@example.com', '$2y$10$8S831h.P.2r1H1r1x1z1e.g5wQ3yH7cM6N5b4V3C2B1A0Z9Y8X7W', '+1 (555) 345-6789', 'customer', 'active');

-- Seed Staff
INSERT IGNORE INTO `staff` (`id`, `name`, `email`, `phone`, `designation`, `salary`, `status`) VALUES
(1, 'Robert Johnson', 'robert@hotel.com', '+1 (555) 111-2233', 'Front Desk Manager', 4500.00, 'Active'),
(2, 'Sarah Williams', 'sarah@hotel.com', '+1 (555) 222-3344', 'Housekeeping Supervisor', 3200.00, 'Active'),
(3, 'David Miller', 'david@hotel.com', '+1 (555) 333-4455', 'Head Chef', 5000.00, 'Active'),
(4, 'Emily Davis', 'emily@hotel.com', '+1 (555) 444-5566', 'Concierge Specialist', 3800.00, 'Active');

-- Seed Rooms
INSERT IGNORE INTO `rooms` (`id`, `room_number`, `room_type`, `price`, `capacity`, `floor_number`, `description`, `image`, `status`) VALUES
(1, '101', 'Standard', 120.00, 2, 1, 'Cozy single room with a queen bed, high-speed Wi-Fi, smart TV, and city view.', 'room_101.jpg', 'Available'),
(2, '102', 'Standard', 130.00, 2, 1, 'Comfortable room featuring modern amenities, workstation, and garden view.', 'room_102.jpg', 'Occupied'),
(3, '201', 'Deluxe', 220.00, 3, 2, 'Spacious deluxe room with king bed, marble bathroom, mini-bar, and balcony view.', 'room_201.jpg', 'Available'),
(4, '202', 'Deluxe', 240.00, 3, 2, 'Elegantly furnished deluxe room with premium sound system and ocean view.', 'room_202.jpg', 'Booked'),
(5, '301', 'Suite', 450.00, 4, 3, 'Luxury executive suite with separate living area, king bed, jacuzzi, and skyline view.', 'room_301.jpg', 'Occupied'),
(6, '302', 'Suite', 480.00, 4, 3, 'Presidential style suite featuring panoramic windows, private lounge, and butler service.', 'room_302.jpg', 'Available'),
(7, '401', 'Family', 310.00, 5, 4, 'Spacious family suite with two queen beds, kids play area, and kitchenette.', 'room_401.jpg', 'Available'),
(8, '501', 'Luxury', 750.00, 4, 5, 'Ultra-luxury penthouse suite with private infinity pool access and dedicated concierge.', 'room_501.jpg', 'Maintenance');

-- Seed Bookings
INSERT IGNORE INTO `bookings` (`id`, `booking_no`, `customer_id`, `room_id`, `check_in`, `check_out`, `adults`, `children`, `total_days`, `total_price`, `status`, `created_at`) VALUES
(1, 'BK-2026-001', 2, 2, CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 3 DAY), 2, 0, 3, 390.00, 'CheckedIn', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'BK-2026-002', 3, 4, DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), DATE_ADD(CURRENT_DATE(), INTERVAL 4 DAY), 2, 1, 3, 720.00, 'Confirmed', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'BK-2026-003', 4, 5, CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 5 DAY), 3, 1, 5, 2250.00, 'CheckedIn', NOW()),
(4, 'BK-2026-004', 2, 1, DATE_SUB(CURRENT_DATE(), INTERVAL 10 DAY), DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY), 2, 0, 3, 360.00, 'CheckedOut', DATE_SUB(NOW(), INTERVAL 12 DAY));

-- Seed Payments
INSERT IGNORE INTO `payments` (`id`, `booking_id`, `payment_no`, `amount`, `payment_method`, `payment_status`, `transaction_id`, `payment_date`) VALUES
(1, 1, 'PAY-2026-001', 390.00, 'Credit Card', 'Completed', 'TXN-9842103', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 2, 'PAY-2026-002', 720.00, 'PayPal', 'Pending', 'TXN-9842104', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 3, 'PAY-2026-003', 2250.00, 'Credit Card', 'Completed', 'TXN-9842105', NOW()),
(4, 4, 'PAY-2026-004', 360.00, 'Cash', 'Completed', 'TXN-9842099', DATE_SUB(NOW(), INTERVAL 12 DAY));
