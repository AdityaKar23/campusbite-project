-- CampusBite Database Setup
-- Import this file into phpMyAdmin to create the database and sample data
-- This file preserves existing data when re-imported

CREATE DATABASE IF NOT EXISTS campusbite;
USE campusbite;

-- Canteens table
CREATE TABLE IF NOT EXISTS canteens (
    canteen_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    INDEX idx_canteens_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Food items table
CREATE TABLE IF NOT EXISTS food (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    canteen_id INT NOT NULL,
    food_name VARCHAR(100) NOT NULL,
    price DECIMAL(8, 2) NOT NULL,
    stock INT DEFAULT 0,
    available TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE CASCADE,
    INDEX idx_food_canteen (canteen_id),
    INDEX idx_food_available (available),
    INDEX idx_food_name (food_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    canteen_id INT NOT NULL,
    user_id INT NULL,
    student_name VARCHAR(100) NOT NULL,
    university_id VARCHAR(50) NULL,
    department VARCHAR(50) NULL,
    rating TINYINT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_reviews_canteen (canteen_id),
    INDEX idx_reviews_user (user_id),
    INDEX idx_reviews_created (created_at),
    CONSTRAINT chk_reviews_rating CHECK (rating >= 1 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complaints table
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    canteen_id INT NOT NULL,
    user_id INT NULL,
    student_name VARCHAR(100) NOT NULL,
    university_id VARCHAR(50) NULL,
    department VARCHAR(50) NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_complaints_canteen (canteen_id),
    INDEX idx_complaints_user (user_id),
    INDEX idx_complaints_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table (authentication)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    university_id VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'owner') NOT NULL,
    department VARCHAR(50) NULL,
    canteen_id INT NULL,
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE SET NULL,
    INDEX idx_users_university_id (university_id),
    INDEX idx_users_role (role),
    INDEX idx_users_canteen (canteen_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample canteens
INSERT INTO canteens (name, location, status) VALUES
('Central Campus Canteen', 'Main Academic Block, Ground Floor', 'open'),
('Engineering Food Court', 'Engineering Building, 2nd Floor', 'open'),
('Library Snack Corner', 'University Library, West Wing', 'closed'),
('Green Canteen', 'Science Building, 1st Floor', 'open');

-- Sample food items (5 per canteen = 20 total)
INSERT INTO food (canteen_id, food_name, price, stock, available) VALUES
-- Central Campus Canteen
(1, 'Chicken Biryani', 120.00, 50, 1),
(1, 'Beef Tehari', 100.00, 40, 1),
(1, 'Vegetable Khichuri', 60.00, 30, 1),
(1, 'Chicken Roll', 80.00, 0, 0),
(1, 'Mango Lassi', 50.00, 25, 1),
-- Engineering Food Court
(2, 'Beef Burger', 150.00, 35, 1),
(2, 'Club Sandwich', 120.00, 45, 1),
(2, 'French Fries', 80.00, 60, 1),
(2, 'Chicken Shawarma', 130.00, 0, 0),
(2, 'Cold Coffee', 70.00, 40, 1),
-- Library Snack Corner
(3, 'Chicken Sandwich', 90.00, 20, 1),
(3, 'Egg Paratha', 45.00, 50, 1),
(3, 'Vegetable Soup', 55.00, 0, 0),
(3, 'Samosa (2 pcs)', 30.00, 100, 1),
(3, 'Green Tea', 25.00, 80, 1),
-- Green Canteen
(4, 'Mixed Fried Rice', 110.00, 40, 1),
(4, 'Chicken Tikka', 140.00, 30, 1),
(4, 'Vegetable Curry', 70.00, 35, 1),
(4, 'Naan Bread', 20.00, 100, 1),
(4, 'Lassi', 40.00, 0, 0);

-- Sample reviews
INSERT INTO reviews (canteen_id, student_name, university_id, department, rating, comment) VALUES
(1, 'Rahim Ahmed', '2430901', 'CSE', 5, 'Best biryani on campus! Always fresh and generous portions.'),
(1, 'Sadia Khan', '2430109', 'CSE', 4, 'Great variety and friendly staff. Gets crowded at lunch though.'),
(1, 'Karim Hassan', '2340888', 'EEE', 3, 'Food is good but waiting time is long during peak hours.'),
(2, 'Nusrat Jahan', '2430876', 'BBA', 5, 'Love the burgers here. Perfect spot after lab sessions.'),
(2, 'Tanvir Islam', '2430901', 'CSE', 4, 'Clean and modern. Prices are reasonable for the quality.'),
(2, 'Farhana Akter', '2430109', 'CSE', 4, 'Shawarma is amazing when available. Coffee is a must-try.'),
(3, 'Imran Chowdhury', '2340888', 'EEE', 3, 'Convenient location for study breaks. Limited menu options.'),
(3, 'Maya Rahman', '2430876', 'BBA', 4, 'Quiet and cozy. Perfect for a quick snack between classes.'),
(4, 'Hasan Mahmud', '2430901', 'CSE', 5, 'Excellent fried rice and chicken tikka. Great value for money.'),
(4, 'Nadia Islam', '2430109', 'CSE', 4, 'Love the naan bread here. Very fresh and tasty.');

-- Sample complaints
INSERT INTO complaints (canteen_id, student_name, university_id, department, message) VALUES
(1, 'Arif Mahmud', '2340888', 'EEE', 'Found a hair in my rice yesterday. Please improve kitchen hygiene.'),
(1, 'Lamia Sultana', '2430876', 'BBA', 'Card payment machine was not working during lunch rush.'),
(2, 'Shuvo Das', '2430901', 'CSE', 'Air conditioning was broken and the area was very hot.'),
(3, 'Priya Das', '2430109', 'CSE', 'Opening hours on the website do not match actual schedule.'),
(4, 'Rafiq Ahmed', '2340888', 'EEE', 'Lassi was not available today despite being on the menu.');

-- After import, visit seed_users.php once to create demo accounts,
-- or register new accounts at register.php
