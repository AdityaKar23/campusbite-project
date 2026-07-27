-- CampusBite Database Setup
-- Import this file into phpMyAdmin to create the database and sample data

CREATE DATABASE IF NOT EXISTS campusbite;
USE campusbite;

-- Canteens table
CREATE TABLE IF NOT EXISTS canteens (
    canteen_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open'
);

-- Food items table
CREATE TABLE IF NOT EXISTS food (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    canteen_id INT NOT NULL,
    food_name VARCHAR(100) NOT NULL,
    price DECIMAL(8, 2) NOT NULL,
    available TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    canteen_id INT NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NOT NULL,
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE CASCADE
);

-- Complaints table
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    canteen_id INT NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE CASCADE
);

-- Sample canteens
INSERT INTO canteens (name, location, status) VALUES
('Central Campus Canteen', 'Main Academic Block, Ground Floor', 'open'),
('Engineering Food Court', 'Engineering Building, 2nd Floor', 'open'),
('Library Snack Corner', 'University Library, West Wing', 'closed');

-- Sample food items (5 per canteen = 15 total)
INSERT INTO food (canteen_id, food_name, price, available) VALUES
-- Central Campus Canteen
(1, 'Chicken Biryani', 120.00, 1),
(1, 'Beef Tehari', 100.00, 1),
(1, 'Vegetable Khichuri', 60.00, 1),
(1, 'Chicken Roll', 80.00, 0),
(1, 'Mango Lassi', 50.00, 1),
-- Engineering Food Court
(2, 'Beef Burger', 150.00, 1),
(2, 'Club Sandwich', 120.00, 1),
(2, 'French Fries', 80.00, 1),
(2, 'Chicken Shawarma', 130.00, 0),
(2, 'Cold Coffee', 70.00, 1),
-- Library Snack Corner
(3, 'Chicken Sandwich', 90.00, 1),
(3, 'Egg Paratha', 45.00, 1),
(3, 'Vegetable Soup', 55.00, 0),
(3, 'Samosa (2 pcs)', 30.00, 1),
(3, 'Green Tea', 25.00, 1);

-- Sample reviews
INSERT INTO reviews (canteen_id, student_name, rating, comment) VALUES
(1, 'Rahim Ahmed', 5, 'Best biryani on campus! Always fresh and generous portions.'),
(1, 'Sadia Khan', 4, 'Great variety and friendly staff. Gets crowded at lunch though.'),
(1, 'Karim Hassan', 3, 'Food is good but waiting time is long during peak hours.'),
(2, 'Nusrat Jahan', 5, 'Love the burgers here. Perfect spot after lab sessions.'),
(2, 'Tanvir Islam', 4, 'Clean and modern. Prices are reasonable for the quality.'),
(2, 'Farhana Akter', 4, 'Shawarma is amazing when available. Coffee is a must-try.'),
(3, 'Imran Chowdhury', 3, 'Convenient location for study breaks. Limited menu options.'),
(3, 'Maya Rahman', 4, 'Quiet and cozy. Perfect for a quick snack between classes.');

-- Sample complaints
INSERT INTO complaints (canteen_id, student_name, message) VALUES
(1, 'Arif Mahmud', 'Found a hair in my rice yesterday. Please improve kitchen hygiene.'),
(1, 'Lamia Sultana', 'Card payment machine was not working during lunch rush.'),
(2, 'Shuvo Das', 'Air conditioning was broken and the area was very hot.'),
(3, 'Priya Das', 'Opening hours on the website do not match actual schedule.');
