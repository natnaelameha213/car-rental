-- Car Rental Management System Database
-- Import this file via phpMyAdmin or MySQL CLI
-- Database: car_rental_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `car_rental_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `car_rental_db`;

-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','manager') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'Ethiopia',
  `license_number` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `locations`
-- --------------------------------------------------------
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `cars`
-- --------------------------------------------------------
CREATE TABLE `cars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `category` enum('Sedan','SUV','Pickup','Luxury','Van','Hatchback','Crossover') NOT NULL,
  `daily_price` decimal(10,2) NOT NULL,
  `weekly_price` decimal(10,2) NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `transmission` enum('Automatic','Manual') NOT NULL DEFAULT 'Automatic',
  `fuel_type` enum('Petrol','Diesel','Hybrid','Electric') NOT NULL DEFAULT 'Petrol',
  `seats` int(2) NOT NULL DEFAULT 5,
  `doors` int(1) NOT NULL DEFAULT 4,
  `engine` varchar(50) DEFAULT NULL,
  `mileage` varchar(20) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `status` enum('Available','Reserved','Rented','Maintenance','Inactive') DEFAULT 'Available',
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_brand` (`brand`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_location` (`location_id`),
  KEY `idx_price` (`daily_price`),
  CONSTRAINT `fk_cars_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `car_images`
-- --------------------------------------------------------
CREATE TABLE `car_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(3) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_car_id` (`car_id`),
  CONSTRAINT `fk_car_images_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `bookings`
-- --------------------------------------------------------
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) NOT NULL,
  `pickup_location_id` int(11) NOT NULL,
  `return_location_id` int(11) NOT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `pickup_time` time DEFAULT '10:00:00',
  `return_time` time DEFAULT '10:00:00',
  `rental_days` int(5) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `deposit` decimal(10,2) DEFAULT 0.00,
  `additional_fees` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('Pending','Confirmed','Active','Completed','Cancelled') DEFAULT 'Pending',
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text DEFAULT NULL,
  `driver_license` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_car` (`car_id`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`pickup_date`,`return_date`),
  CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`),
  CONSTRAINT `fk_bookings_pickup` FOREIGN KEY (`pickup_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `fk_bookings_return` FOREIGN KEY (`return_location_id`) REFERENCES `locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `payment_number` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Bank Transfer','Card','Mobile Money','Other') NOT NULL,
  `payment_type` enum('Deposit','Rental','Additional','Refund') DEFAULT 'Rental',
  `status` enum('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_number` (`payment_number`),
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `invoices`
-- --------------------------------------------------------
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(20) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Draft','Sent','Paid','Overdue','Cancelled') DEFAULT 'Draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `fk_invoices_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `reviews`
-- --------------------------------------------------------
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `title` varchar(100) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_car` (`car_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reviews_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `contact_messages`
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('New','Read','Replied','Archived') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert default admin (password: password - change immediately)
-- --------------------------------------------------------
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@carrental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');
-- Note: the demo password hash above is for 'password'. Change it after the first login (Admin > Settings).

-- --------------------------------------------------------
-- Insert locations
-- --------------------------------------------------------
INSERT INTO `locations` (`name`, `address`, `city`, `phone`, `email`) VALUES
('Addis Ababa Airport', 'Bole International Airport', 'Addis Ababa', '+251 11 665 0220', 'airport@carrental.et'),
('Bole Branch', 'Bole Road, near Edna Mall', 'Addis Ababa', '+251 11 661 2345', 'bole@carrental.et'),
('Kazanchis Branch', 'Kazanchis Business District', 'Addis Ababa', '+251 11 551 7890', 'kazanchis@carrental.et'),
('Meskel Square', 'Meskel Square Area', 'Addis Ababa', '+251 11 552 3456', 'meskel@carrental.et'),
('Hawassa Branch', 'Main Street, Hawassa', 'Hawassa', '+251 46 220 1122', 'hawassa@carrental.et'),
('Bahir Dar Branch', 'Lake Tana Road', 'Bahir Dar', '+251 58 220 3344', 'bahirdar@carrental.et');

-- --------------------------------------------------------
-- Insert 50 cars with realistic data (USD currency)
-- Note: main_image uses local paths. Download real photos to assets/images/cars/
-- For demo, some Unsplash free images are referenced where available.
-- --------------------------------------------------------
INSERT INTO `cars` (`brand`, `model`, `year`, `category`, `daily_price`, `weekly_price`, `monthly_price`, `transmission`, `fuel_type`, `seats`, `doors`, `engine`, `mileage`, `color`, `location_id`, `status`, `deposit_amount`, `description`, `features`, `main_image`) VALUES
('Toyota', 'Corolla', 2023, 'Sedan', 50.00, 300.00, 1100.00, 'Automatic', 'Petrol', 5, 4, '1.8L 4-Cylinder', '15,000 km', 'White', 1, 'Available', 200.00, 'Reliable and fuel-efficient sedan perfect for city driving and long trips. Comfortable interior with modern features.', 'Bluetooth, Backup Camera, Cruise Control, Apple CarPlay, Android Auto, Keyless Entry', 'toyota-corolla-2023.jpg'),
('Toyota', 'RAV4', 2024, 'SUV', 90.00, 560.00, 2000.00, 'Automatic', 'Hybrid', 5, 5, '2.5L Hybrid', '8,000 km', 'Silver', 1, 'Available', 300.00, 'Popular compact SUV with excellent hybrid efficiency and spacious cargo area. Ideal for families and adventures.', 'AWD, Panoramic Sunroof, Adaptive Cruise, Lane Assist, Wireless Charging, Premium Audio', 'toyota-rav4-2024.jpg'),
('Toyota', 'Land Cruiser', 2023, 'SUV', 240.00, 1500.00, 5600.00, 'Automatic', 'Diesel', 7, 5, '3.3L V6 Turbo Diesel', '12,000 km', 'Black', 2, 'Available', 1000.00, 'Legendary off-road capability with unmatched reliability. The ultimate luxury adventure vehicle.', '4WD, Multi-Terrain Select, Leather Seats, Premium Sound, Dual Zone Climate, Tow Package', 'toyota-landcruiser-2023.jpg'),
('Toyota', 'Prado', 2022, 'SUV', 180.00, 1100.00, 4000.00, 'Automatic', 'Diesel', 7, 5, '2.8L Turbo Diesel', '25,000 km', 'White', 2, 'Available', 700.00, 'Mid-size luxury SUV combining comfort, capability and prestige. Perfect for business and family use.', 'Leather Interior, Sunroof, Navigation, 360 Camera, Adaptive Suspension, 7 Seats', 'toyota-prado-2022.jpg'),
('Toyota', 'Hilux', 2023, 'Pickup', 100.00, 600.00, 2200.00, 'Manual', 'Diesel', 5, 4, '2.8L Turbo Diesel', '18,000 km', 'Grey', 3, 'Available', 400.00, 'Tough and dependable pickup truck built for work and adventure. Excellent towing capacity.', '4WD, Bed Liner, Tow Hitch, Diff Lock, Bluetooth, Air Conditioning', 'toyota-hilux-2023.jpg'),
('Hyundai', 'Tucson', 2024, 'SUV', 84.00, 520.00, 1900.00, 'Automatic', 'Petrol', 5, 5, '2.0L Smartstream', '5,000 km', 'Blue', 1, 'Available', 280.00, 'Stylish modern SUV with bold design and advanced technology. Great value for money.', 'Smart Sense Safety, Digital Cluster, Wireless CarPlay, Heated Seats, Power Tailgate', 'hyundai-tucson-2024.jpg'),
('Hyundai', 'Santa Fe', 2023, 'SUV', 110.00, 680.00, 2500.00, 'Automatic', 'Diesel', 7, 5, '2.2L CRDi', '14,000 km', 'Black', 2, 'Available', 360.00, 'Spacious 7-seater SUV ideal for large families. Comfortable ride with premium features.', '7 Seats, Panoramic Roof, Bose Audio, Smart Cruise, Blind Spot Monitor, Hands-free Liftgate', 'hyundai-santafe-2023.jpg'),
('Hyundai', 'Elantra', 2023, 'Sedan', 56.00, 340.00, 1240.00, 'Automatic', 'Petrol', 5, 4, '2.0L MPI', '10,000 km', 'Red', 3, 'Available', 200.00, 'Sporty and elegant sedan with sharp styling and efficient performance.', 'Sport Mode, Digital Key, Wireless Charging, Dual Climate, Rear Camera', 'hyundai-elantra-2023.jpg'),
('Hyundai', 'Sonata', 2022, 'Sedan', 64.00, 390.00, 1440.00, 'Automatic', 'Petrol', 5, 4, '2.5L GDI', '22,000 km', 'Silver', 4, 'Available', 240.00, 'Mid-size sedan offering premium comfort and advanced driver assistance systems.', 'SmartSense, Leather, Sunroof, Navigation, Adaptive Cruise, Heated/Ventilated Seats', 'hyundai-sonata-2022.jpg'),
('Hyundai', 'Palisade', 2023, 'SUV', 150.00, 920.00, 3400.00, 'Automatic', 'Petrol', 8, 5, '3.8L V6', '9,000 km', 'White', 1, 'Available', 500.00, 'Full-size luxury SUV with 8 seats. Ultimate comfort for large groups and long journeys.', '8 Seats, Premium Nappa Leather, Dual Sunroof, Rear Entertainment, Highway Driving Assist', 'hyundai-palisade-2023.jpg'),
('Kia', 'Sportage', 2024, 'SUV', 80.00, 490.00, 1800.00, 'Automatic', 'Petrol', 5, 5, '2.0L', '6,000 km', 'Green', 2, 'Available', 260.00, 'Bold design and practical features make this SUV stand out. Excellent warranty coverage.', 'Drive Wise Safety, Dual Screens, Wireless Charging, Terrain Modes, Power Seats', 'kia-sportage-2024.jpg'),
('Kia', 'Sorento', 2023, 'SUV', 104.00, 640.00, 2360.00, 'Automatic', 'Diesel', 7, 5, '2.2L CRDi', '15,000 km', 'Grey', 3, 'Available', 340.00, 'Versatile 7-seater with strong diesel performance and refined interior.', '7 Seats, Panoramic Sunroof, Harman Kardon, Smart Cruise, 360 View Monitor', 'kia-sorento-2023.jpg'),
('Kia', 'Carnival', 2023, 'Van', 120.00, 740.00, 2700.00, 'Automatic', 'Diesel', 8, 5, '2.2L CRDi', '11,000 km', 'Black', 1, 'Available', 400.00, 'Premium MPV with luxurious seating and advanced features. Perfect for group travel.', '8 Seats, VIP Lounge Seating, Dual Sunroof, Rear Climate, Power Sliding Doors', 'kia-carnival-2023.jpg'),
('Nissan', 'X-Trail', 2023, 'SUV', 86.00, 530.00, 1960.00, 'Automatic', 'Petrol', 7, 5, '2.5L', '13,000 km', 'Blue', 4, 'Available', 280.00, 'Capable crossover SUV with available 7 seats and ProPILOT assist technology.', 'ProPILOT, 7 Seats Option, Around View Monitor, Intelligent 4WD, Dual Zone AC', 'nissan-xtrail-2023.jpg'),
('Nissan', 'Patrol', 2022, 'SUV', 220.00, 1360.00, 5000.00, 'Automatic', 'Petrol', 8, 5, '5.6L V8', '20,000 km', 'Black', 2, 'Available', 900.00, 'Full-size luxury SUV with powerful V8 and exceptional off-road ability.', '4WD, Hydraulic Body Control, Leather, Bose Audio, Rear Entertainment, Tow Package', 'nissan-patrol-2022.jpg'),
('Nissan', 'Altima', 2023, 'Sedan', 60.00, 370.00, 1360.00, 'Automatic', 'Petrol', 5, 4, '2.5L', '12,000 km', 'White', 3, 'Available', 220.00, 'Comfortable mid-size sedan with smooth ride and modern tech features.', 'ProPILOT Assist, Zero Gravity Seats, Apple CarPlay, Android Auto, Blind Spot Warning', 'nissan-altima-2023.jpg'),
('Nissan', 'Sunny', 2022, 'Sedan', 44.00, 270.00, 1000.00, 'Automatic', 'Petrol', 5, 4, '1.5L', '28,000 km', 'Silver', 5, 'Available', 160.00, 'Affordable and reliable compact sedan. Great for everyday commuting.', 'Bluetooth, Rear Camera, USB Ports, Air Conditioning, Power Windows', 'nissan-sunny-2022.jpg'),
('Honda', 'CR-V', 2024, 'SUV', 92.00, 570.00, 2100.00, 'Automatic', 'Hybrid', 5, 5, '2.0L Hybrid', '4,000 km', 'White', 1, 'Available', 300.00, 'Best-selling compact SUV with excellent hybrid efficiency and spacious cabin.', 'Honda Sensing, Hands-Free Power Tailgate, Wireless CarPlay, Dual Zone Climate', 'honda-crv-2024.jpg'),
('Honda', 'Civic', 2023, 'Sedan', 58.00, 350.00, 1300.00, 'Automatic', 'Petrol', 5, 4, '1.5L Turbo', '16,000 km', 'Red', 2, 'Available', 200.00, 'Sporty compact sedan with fun-to-drive dynamics and advanced safety.', 'Honda Sensing, Turbo Engine, Digital Cluster, Sport Seats, Wireless Charging', 'honda-civic-2023.jpg'),
('Honda', 'Accord', 2023, 'Sedan', 70.00, 430.00, 1600.00, 'Automatic', 'Hybrid', 5, 4, '2.0L Hybrid', '9,000 km', 'Grey', 3, 'Available', 260.00, 'Premium mid-size sedan combining luxury, efficiency and performance.', 'Honda Sensing, Leather, Head-Up Display, Bose Audio, Adaptive Cruise', 'honda-accord-2023.jpg'),
('Ford', 'Explorer', 2023, 'SUV', 130.00, 800.00, 2960.00, 'Automatic', 'Petrol', 7, 5, '2.3L EcoBoost', '17,000 km', 'Blue', 1, 'Available', 440.00, 'Spacious American SUV with powerful engine and advanced 4WD system.', 'Terrain Management, 7 Seats, SYNC 4, B&O Audio, Adaptive Cruise, Tow Package', 'ford-explorer-2023.jpg'),
('Ford', 'Everest', 2023, 'SUV', 116.00, 710.00, 2600.00, 'Automatic', 'Diesel', 7, 5, '2.0L Bi-Turbo Diesel', '14,000 km', 'White', 4, 'Available', 400.00, 'Rugged 7-seater SUV built on Ranger platform. Excellent off-road capability.', '4WD, Terrain Modes, Leather, 360 Camera, Adaptive Cruise, Diff Lock', 'ford-everest-2023.jpg'),
('Ford', 'Ranger', 2024, 'Pickup', 96.00, 590.00, 2160.00, 'Automatic', 'Diesel', 5, 4, '2.0L Bi-Turbo', '7,000 km', 'Orange', 3, 'Available', 360.00, 'Modern pickup with car-like comfort and serious work capability.', '4WD, Sync 4A, Adaptive Cruise, Tow Haul Mode, Bed Power Outlet', 'ford-ranger-2024.jpg'),
('Mercedes-Benz', 'C-Class', 2023, 'Luxury', 170.00, 1040.00, 3800.00, 'Automatic', 'Petrol', 5, 4, '2.0L Turbo', '11,000 km', 'Black', 1, 'Available', 600.00, 'Elegant luxury sedan with refined ride and cutting-edge technology.', 'MBUX, Ambient Lighting, Burmester Sound, Adaptive Suspension, Driver Assistance', 'mercedes-cclass-2023.jpg'),
('Mercedes-Benz', 'E-Class', 2022, 'Luxury', 220.00, 1360.00, 5000.00, 'Automatic', 'Petrol', 5, 4, '2.0L Turbo', '19,000 km', 'Silver', 2, 'Available', 800.00, 'Executive luxury sedan offering supreme comfort and advanced features.', 'MBUX Hyperscreen, Air Body Control, Premium Audio, Rear Seat Entertainment', 'mercedes-eclass-2022.jpg'),
('Mercedes-Benz', 'GLE', 2023, 'Luxury', 280.00, 1720.00, 6400.00, 'Automatic', 'Diesel', 5, 5, '2.9L Inline-6 Diesel', '8,000 km', 'White', 1, 'Available', 1000.00, 'Luxury mid-size SUV combining performance, comfort and prestige.', '4MATIC, Air Suspension, MBUX, Burmester 3D, Off-Road Package, Panoramic Roof', 'mercedes-gle-2023.jpg'),
('BMW', '3 Series', 2023, 'Luxury', 160.00, 980.00, 3600.00, 'Automatic', 'Petrol', 5, 4, '2.0L TwinPower Turbo', '10,000 km', 'Blue', 2, 'Available', 560.00, 'Iconic sports sedan delivering dynamic driving pleasure and premium luxury.', 'iDrive 8, Live Cockpit, Harman Kardon, Adaptive M Suspension, Parking Assistant', 'bmw-3series-2023.jpg'),
('BMW', '5 Series', 2022, 'Luxury', 210.00, 1280.00, 4700.00, 'Automatic', 'Petrol', 5, 4, '2.0L TwinPower', '16,000 km', 'Black', 1, 'Available', 760.00, 'Executive sports sedan with perfect balance of performance and comfort.', 'Gesture Control, Bowers & Wilkins, Adaptive Air Suspension, Executive Lounge', 'bmw-5series-2022.jpg'),
('BMW', 'X3', 2023, 'Luxury', 180.00, 1100.00, 4000.00, 'Automatic', 'Petrol', 5, 5, '2.0L TwinPower', '12,000 km', 'Grey', 3, 'Available', 640.00, 'Sporty compact luxury SUV with agile handling and premium interior.', 'xDrive, M Sport Package, Panoramic Roof, Harman Kardon, Parking Assistant Plus', 'bmw-x3-2023.jpg'),
('BMW', 'X5', 2023, 'Luxury', 260.00, 1600.00, 5900.00, 'Automatic', 'Diesel', 5, 5, '3.0L TwinPower Diesel', '9,000 km', 'White', 1, 'Available', 900.00, 'Flagship mid-size luxury SUV offering unmatched versatility and performance.', 'xDrive, Air Suspension, Bowers & Wilkins, Gesture Control, Off-Road Package', 'bmw-x5-2023.jpg'),
('Audi', 'A4', 2023, 'Luxury', 156.00, 950.00, 3500.00, 'Automatic', 'Petrol', 5, 4, '2.0L TFSI', '13,000 km', 'Black', 2, 'Available', 540.00, 'Sophisticated luxury sedan with quattro all-wheel drive and virtual cockpit.', 'Virtual Cockpit, MMI Touch, Bang & Olufsen, Adaptive Cruise, Matrix LED', 'audi-a4-2023.jpg'),
('Audi', 'A6', 2022, 'Luxury', 200.00, 1220.00, 4500.00, 'Automatic', 'Petrol', 5, 4, '2.0L TFSI', '18,000 km', 'Silver', 1, 'Available', 700.00, 'Elegant executive sedan with advanced technology and refined comfort.', 'Virtual Cockpit Plus, MMI Navigation, Adaptive Air Suspension, Bang & Olufsen', 'audi-a6-2022.jpg'),
('Audi', 'Q5', 2023, 'Luxury', 176.00, 1080.00, 3960.00, 'Automatic', 'Petrol', 5, 5, '2.0L TFSI', '11,000 km', 'Blue', 3, 'Available', 600.00, 'Premium compact SUV with quattro and advanced driver assistance.', 'quattro, Virtual Cockpit, Panoramic Roof, Adaptive Cruise, Matrix LED', 'audi-q5-2023.jpg'),
('Audi', 'Q7', 2023, 'Luxury', 250.00, 1540.00, 5700.00, 'Automatic', 'Diesel', 7, 5, '3.0L TDI', '7,000 km', 'Black', 1, 'Available', 840.00, 'Spacious luxury 7-seater SUV with exceptional comfort and technology.', 'quattro, Air Suspension, Virtual Cockpit, Bang & Olufsen, All-Wheel Steering', 'audi-q7-2023.jpg'),
('Lexus', 'ES', 2023, 'Luxury', 150.00, 920.00, 3400.00, 'Automatic', 'Hybrid', 5, 4, '2.5L Hybrid', '8,000 km', 'White', 2, 'Available', 500.00, 'Quiet and refined luxury hybrid sedan with legendary reliability.', 'Mark Levinson Audio, Safety System+, Panoramic View, Wireless Charging', 'lexus-es-2023.jpg'),
('Lexus', 'RX', 2023, 'Luxury', 190.00, 1160.00, 4300.00, 'Automatic', 'Hybrid', 5, 5, '2.5L Hybrid', '6,000 km', 'Silver', 1, 'Available', 660.00, 'Premium luxury crossover with hybrid efficiency and exceptional comfort.', 'Mark Levinson, Safety System+, Panoramic Roof, Power Tailgate, 360 Camera', 'lexus-rx-2023.jpg'),
('Lexus', 'LX', 2022, 'Luxury', 300.00, 1840.00, 6800.00, 'Automatic', 'Petrol', 7, 5, '3.5L Twin-Turbo V6', '15,000 km', 'Black', 1, 'Available', 1100.00, 'Ultimate luxury full-size SUV with unmatched off-road capability and opulence.', '4WD, Multi-Terrain Select, Mark Levinson, Rear Entertainment, Air Suspension', 'lexus-lx-2022.jpg'),
('Range Rover', 'Evoque', 2023, 'Luxury', 200.00, 1220.00, 4500.00, 'Automatic', 'Petrol', 5, 5, '2.0L Turbo', '10,000 km', 'White', 2, 'Available', 700.00, 'Stylish compact luxury SUV with distinctive design and capable off-road ability.', 'Terrain Response, Meridian Audio, ClearSight Mirror, Adaptive Dynamics', 'rangerover-evoque-2023.jpg'),
('Range Rover', 'Sport', 2023, 'Luxury', 320.00, 1960.00, 7200.00, 'Automatic', 'Diesel', 5, 5, '3.0L Inline-6 Diesel', '5,000 km', 'Black', 1, 'Available', 1200.00, 'Performance-oriented luxury SUV combining sportiness with Range Rover refinement.', 'Terrain Response 2, Meridian Signature, Air Suspension, Cabin Air Purification', 'rangerover-sport-2023.jpg'),
('Jeep', 'Wrangler', 2023, 'SUV', 140.00, 860.00, 3160.00, 'Automatic', 'Petrol', 5, 4, '2.0L Turbo', '12,000 km', 'Green', 3, 'Available', 500.00, 'Iconic off-road vehicle with removable top and doors. Adventure ready.', '4WD, Trail Rated, Removable Top, Uconnect, Off-Road Camera, Selec-Trac', 'jeep-wrangler-2023.jpg'),
('Jeep', 'Grand Cherokee', 2023, 'SUV', 170.00, 1040.00, 3800.00, 'Automatic', 'Petrol', 5, 5, '3.6L V6', '9,000 km', 'Grey', 2, 'Available', 600.00, 'Premium mid-size SUV with advanced 4WD and luxurious interior.', 'Quadra-Trac, McIntosh Audio, Uconnect 5, Adaptive Cruise, Air Suspension Option', 'jeep-grandcherokee-2023.jpg'),
('Volkswagen', 'Tiguan', 2023, 'SUV', 82.00, 500.00, 1840.00, 'Automatic', 'Petrol', 5, 5, '2.0L TSI', '14,000 km', 'Blue', 4, 'Available', 280.00, 'Practical European SUV with refined ride and high-quality interior.', 'Digital Cockpit, Adaptive Cruise, App-Connect, Park Assist, All-Wheel Drive Option', 'vw-tiguan-2023.jpg'),
('Volkswagen', 'Passat', 2022, 'Sedan', 68.00, 420.00, 1560.00, 'Automatic', 'Diesel', 5, 4, '2.0L TDI', '21,000 km', 'Silver', 3, 'Available', 240.00, 'Spacious and comfortable mid-size sedan with excellent long-distance capability.', 'Digital Cockpit, Adaptive Chassis, Discover Pro Nav, Park Assist', 'vw-passat-2022.jpg'),
('Chevrolet', 'Tahoe', 2023, 'SUV', 180.00, 1100.00, 4000.00, 'Automatic', 'Petrol', 8, 5, '5.3L V8', '11,000 km', 'Black', 1, 'Available', 600.00, 'Full-size American SUV with powerful V8 and massive cargo capacity.', '4WD, Magnetic Ride, Bose Audio, Rear Entertainment, Super Cruise Ready', 'chevrolet-tahoe-2023.jpg'),
('Chevrolet', 'Suburban', 2022, 'SUV', 200.00, 1220.00, 4500.00, 'Automatic', 'Petrol', 8, 5, '5.3L V8', '18,000 km', 'White', 2, 'Available', 700.00, 'The ultimate full-size family hauler with unmatched space and capability.', '4WD, Max Trailering, Bose, Rear Media, Adaptive Cruise, Power Running Boards', 'chevrolet-suburban-2022.jpg'),
('Mitsubishi', 'Outlander', 2023, 'SUV', 76.00, 460.00, 1700.00, 'Automatic', 'Petrol', 7, 5, '2.5L', '13,000 km', 'Red', 5, 'Available', 240.00, 'Practical 7-seater SUV with S-AWC all-wheel control system.', 'S-AWC, 7 Seats, Smartphone Link, Adaptive Cruise, Multi Around Monitor', 'mitsubishi-outlander-2023.jpg'),
('Mitsubishi', 'Pajero', 2021, 'SUV', 110.00, 680.00, 2500.00, 'Automatic', 'Diesel', 7, 5, '3.2L Diesel', '35,000 km', 'White', 4, 'Available', 360.00, 'Legendary off-roader known for reliability and toughness in harsh conditions.', 'Super Select 4WD, Leather, Rockford Fosgate, Rear Diff Lock, Tow Package', 'mitsubishi-pajero-2021.jpg'),
('Suzuki', 'Vitara', 2023, 'SUV', 64.00, 390.00, 1440.00, 'Automatic', 'Petrol', 5, 5, '1.4L Boosterjet', '10,000 km', 'Yellow', 5, 'Available', 200.00, 'Compact and agile SUV with distinctive styling and efficient turbo engine.', 'AllGrip 4WD, Panoramic Roof, Adaptive Cruise, Smartphone Linkage, LED Lights', 'suzuki-vitara-2023.jpg'),
('Mazda', 'CX-5', 2023, 'SUV', 86.00, 530.00, 1960.00, 'Automatic', 'Petrol', 5, 5, '2.5L Skyactiv', '9,000 km', 'Red', 1, 'Available', 280.00, 'Driver-focused compact SUV with premium interior and engaging handling.', 'i-Activ AWD, Bose Audio, Mazda Connect, Adaptive Cruise, 360 View Monitor', 'mazda-cx5-2023.jpg'),
('Subaru', 'Forester', 2023, 'SUV', 90.00, 550.00, 2040.00, 'Automatic', 'Petrol', 5, 5, '2.5L Boxer', '8,000 km', 'Green', 2, 'Available', 300.00, 'Practical SUV with standard Symmetrical AWD and excellent safety ratings.', 'Symmetrical AWD, EyeSight, X-Mode, Dual Zone Climate, Power Rear Gate', 'subaru-forester-2023.jpg');

-- Insert sample car images (using main images as primary; add more as needed)
INSERT INTO `car_images` (`car_id`, `image_path`, `is_primary`, `sort_order`)
SELECT id, main_image, 1, 0 FROM cars;

-- Sample additional images (same as main for structure; replace with real additional photos)
INSERT INTO `car_images` (`car_id`, `image_path`, `is_primary`, `sort_order`)
SELECT id, CONCAT(SUBSTRING_INDEX(main_image, '.', 1), '-2.jpg'), 0, 1 FROM cars;

INSERT INTO `car_images` (`car_id`, `image_path`, `is_primary`, `sort_order`)
SELECT id, CONCAT(SUBSTRING_INDEX(main_image, '.', 1), '-3.jpg'), 0, 2 FROM cars;

-- --------------------------------------------------------
-- Sample users (password: password123 for all)
-- --------------------------------------------------------
INSERT INTO `users` (`first_name`, `last_name`, `email`, `phone`, `password`, `address`, `city`, `license_number`, `status`) VALUES
('Abebe', 'Kebede', 'abebe@email.com', '+251911234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bole, Addis Ababa', 'Addis Ababa', 'DL-ET-12345', 'active'),
('Sara', 'Tesfaye', 'sara@email.com', '+251922345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kazanchis, Addis Ababa', 'Addis Ababa', 'DL-ET-23456', 'active'),
('Daniel', 'Haile', 'daniel@email.com', '+251933456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Piassa, Addis Ababa', 'Addis Ababa', 'DL-ET-34567', 'active'),
('Meron', 'Assefa', 'meron@email.com', '+251944567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hawassa Town', 'Hawassa', 'DL-ET-45678', 'active');

-- --------------------------------------------------------
-- Sample bookings
-- --------------------------------------------------------
INSERT INTO `bookings` (`booking_number`, `user_id`, `car_id`, `pickup_location_id`, `return_location_id`, `pickup_date`, `return_date`, `rental_days`, `daily_rate`, `subtotal`, `deposit`, `total_amount`, `paid_amount`, `status`, `customer_name`, `customer_email`, `customer_phone`, `driver_license`) VALUES
('BK202409001', 1, 1, 1, 1, '2024-09-15', '2024-09-20', 5, 50.00, 250.00, 200.00, 450.00, 450.00, 'Completed', 'Abebe Kebede', 'abebe@email.com', '+251911234567', 'DL-ET-12345'),
('BK202409002', 2, 6, 2, 2, '2024-09-18', '2024-09-25', 7, 84.00, 588.00, 280.00, 868.00, 280.00, 'Confirmed', 'Sara Tesfaye', 'sara@email.com', '+251922345678', 'DL-ET-23456'),
('BK202409003', 3, 3, 1, 2, '2024-09-22', '2024-09-28', 6, 240.00, 1440.00, 1000.00, 2440.00, 1000.00, 'Active', 'Daniel Haile', 'daniel@email.com', '+251933456789', 'DL-ET-34567'),
('BK202410001', 1, 2, 1, 1, '2024-10-05', '2024-10-10', 5, 90.00, 450.00, 300.00, 750.00, 0.00, 'Pending', 'Abebe Kebede', 'abebe@email.com', '+251911234567', 'DL-ET-12345');

-- --------------------------------------------------------
-- Sample payments
-- --------------------------------------------------------
INSERT INTO `payments` (`booking_id`, `payment_number`, `amount`, `payment_method`, `payment_type`, `status`, `paid_at`) VALUES
(1, 'PAY202409001', 200.00, 'Bank Transfer', 'Deposit', 'Completed', '2024-09-14 10:00:00'),
(1, 'PAY202409002', 250.00, 'Cash', 'Rental', 'Completed', '2024-09-15 09:30:00'),
(2, 'PAY202409003', 280.00, 'Card', 'Deposit', 'Completed', '2024-09-17 14:00:00'),
(3, 'PAY202409004', 1000.00, 'Bank Transfer', 'Deposit', 'Completed', '2024-09-21 11:00:00');

-- --------------------------------------------------------
-- Sample reviews
-- --------------------------------------------------------
INSERT INTO `reviews` (`user_id`, `car_id`, `booking_id`, `rating`, `title`, `comment`, `status`) VALUES
(1, 1, 1, 5, 'Excellent car and service', 'The Corolla was in perfect condition. Smooth booking process and friendly staff. Highly recommended!', 'Approved'),
(2, 6, 2, 4, 'Great SUV for the family', 'Tucson was spacious and comfortable. Good value for money. Only minor delay at pickup.', 'Approved'),
(3, 24, 3, 5, 'Luxury at its best', 'Mercedes C-Class exceeded expectations. Clean, powerful, and feature-packed. Will rent again.', 'Approved');

-- --------------------------------------------------------
-- Settings
-- --------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'EliteDrive Car Rental'),
('company_email', 'info@elitedrive.com'),
('company_phone', '+251 11 665 0220'),
('company_address', 'Bole International Airport Road, Addis Ababa, Ethiopia'),
('currency', 'USD'),
('currency_symbol', '$'),
('tax_rate', '0'),
('booking_deposit_percent', '30'),
('min_rental_days', '1'),
('max_rental_days', '90'),
('cancellation_hours', '24');

COMMIT;

-- --------------------------------------------------------
-- Update cars with free Unsplash image URLs (no local download needed)
-- --------------------------------------------------------
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800&h=500&fit=crop&q=80' WHERE id = 1;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&h=500&fit=crop&q=80' WHERE id = 2;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1609521263047-f8f205293f24?w=800&h=500&fit=crop&q=80' WHERE id = 3;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1621135802920-133df287f89c?w=800&h=500&fit=crop&q=80' WHERE id = 4;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?w=800&h=500&fit=crop&q=80' WHERE id = 5;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&h=500&fit=crop&q=80' WHERE id = 6;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?w=800&h=500&fit=crop&q=80' WHERE id = 7;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1563720223185-11003d516935?w=800&h=500&fit=crop&q=80' WHERE id = 8;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=800&h=500&fit=crop&q=80' WHERE id = 9;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=800&h=500&fit=crop&q=80' WHERE id = 10;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1631295868223-63265b40d9e4?w=800&h=500&fit=crop&q=80' WHERE id = 11;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=800&h=500&fit=crop&q=80' WHERE id = 12;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&h=500&fit=crop&q=80' WHERE id = 13;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=800&h=500&fit=crop&q=80' WHERE id = 14;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800&h=500&fit=crop&q=80' WHERE id = 15;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&h=500&fit=crop&q=80' WHERE id = 16;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1609521263047-f8f205293f24?w=800&h=500&fit=crop&q=80' WHERE id = 17;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1621135802920-133df287f89c?w=800&h=500&fit=crop&q=80' WHERE id = 18;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?w=800&h=500&fit=crop&q=80' WHERE id = 19;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&h=500&fit=crop&q=80' WHERE id = 20;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?w=800&h=500&fit=crop&q=80' WHERE id = 21;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1563720223185-11003d516935?w=800&h=500&fit=crop&q=80' WHERE id = 22;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=800&h=500&fit=crop&q=80' WHERE id = 23;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=800&h=500&fit=crop&q=80' WHERE id = 24;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1631295868223-63265b40d9e4?w=800&h=500&fit=crop&q=80' WHERE id = 25;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=800&h=500&fit=crop&q=80' WHERE id = 26;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&h=500&fit=crop&q=80' WHERE id = 27;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=800&h=500&fit=crop&q=80' WHERE id = 28;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800&h=500&fit=crop&q=80' WHERE id = 29;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&h=500&fit=crop&q=80' WHERE id = 30;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1609521263047-f8f205293f24?w=800&h=500&fit=crop&q=80' WHERE id = 31;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1621135802920-133df287f89c?w=800&h=500&fit=crop&q=80' WHERE id = 32;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?w=800&h=500&fit=crop&q=80' WHERE id = 33;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&h=500&fit=crop&q=80' WHERE id = 34;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?w=800&h=500&fit=crop&q=80' WHERE id = 35;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1563720223185-11003d516935?w=800&h=500&fit=crop&q=80' WHERE id = 36;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=800&h=500&fit=crop&q=80' WHERE id = 37;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=800&h=500&fit=crop&q=80' WHERE id = 38;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1631295868223-63265b40d9e4?w=800&h=500&fit=crop&q=80' WHERE id = 39;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=800&h=500&fit=crop&q=80' WHERE id = 40;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&h=500&fit=crop&q=80' WHERE id = 41;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=800&h=500&fit=crop&q=80' WHERE id = 42;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800&h=500&fit=crop&q=80' WHERE id = 43;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&h=500&fit=crop&q=80' WHERE id = 44;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1609521263047-f8f205293f24?w=800&h=500&fit=crop&q=80' WHERE id = 45;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1621135802920-133df287f89c?w=800&h=500&fit=crop&q=80' WHERE id = 46;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?w=800&h=500&fit=crop&q=80' WHERE id = 47;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&h=500&fit=crop&q=80' WHERE id = 48;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?w=800&h=500&fit=crop&q=80' WHERE id = 49;
UPDATE cars SET main_image = 'https://images.unsplash.com/photo-1563720223185-11003d516935?w=800&h=500&fit=crop&q=80' WHERE id = 50;

-- Also update car_images table
UPDATE car_images ci JOIN cars c ON ci.car_id = c.id SET ci.image_path = c.main_image;
