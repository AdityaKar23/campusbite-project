-- Migration script to split users table into students and canteen_owners tables
-- This preserves all existing data while maintaining functionality

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    student_id INT(11) NOT NULL AUTO_INCREMENT,
    university_id VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (student_id),
    INDEX idx_university_id (university_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create canteen_owners table
CREATE TABLE IF NOT EXISTS canteen_owners (
    owner_id INT(11) NOT NULL AUTO_INCREMENT,
    university_id VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    canteen_id INT(11) DEFAULT NULL,
    PRIMARY KEY (owner_id),
    INDEX idx_university_id (university_id),
    INDEX idx_canteen_id (canteen_id),
    FOREIGN KEY (canteen_id) REFERENCES canteens(canteen_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrate student data from users table
INSERT INTO students (student_id, university_id, password, full_name, department)
SELECT user_id, university_id, password, full_name, department
FROM users
WHERE role = 'student';

-- Migrate canteen owner data from users table
INSERT INTO canteen_owners (owner_id, university_id, password, full_name, canteen_id)
SELECT user_id, university_id, password, full_name, canteen_id
FROM users
WHERE role = 'owner';

-- Update reviews table to reference students instead of users
-- Add student_id column first
ALTER TABLE reviews ADD COLUMN student_id INT(11) DEFAULT NULL AFTER user_id;

-- Populate student_id using join syntax
UPDATE reviews, students 
SET reviews.student_id = students.student_id 
WHERE reviews.university_id = students.university_id;

-- Add index
ALTER TABLE reviews ADD INDEX idx_student_id (student_id);

-- Update complaints table to reference students instead of users
-- Add student_id column first
ALTER TABLE complaints ADD COLUMN student_id INT(11) DEFAULT NULL AFTER user_id;

-- Populate student_id using join syntax
UPDATE complaints, students 
SET complaints.student_id = students.student_id 
WHERE complaints.university_id = students.university_id;

-- Add index
ALTER TABLE complaints ADD INDEX idx_student_id (student_id);

-- Drop the old users table (disable foreign key checks temporarily)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE users;
SET FOREIGN_KEY_CHECKS = 1;
