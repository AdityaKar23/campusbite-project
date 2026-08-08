-- Run this in phpMyAdmin if you already imported campusbite.sql before auth was added
-- This migration file preserves existing data while adding new columns and constraints

USE campusbite;

-- Users table updates
ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(50) NULL;
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_role (role);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_canteen (canteen_id);

-- Food table updates
ALTER TABLE food ADD COLUMN IF NOT EXISTS stock INT DEFAULT 0;
ALTER TABLE food ADD INDEX IF NOT EXISTS idx_food_canteen (canteen_id);
ALTER TABLE food ADD INDEX IF NOT EXISTS idx_food_available (available);
ALTER TABLE food ADD INDEX IF NOT EXISTS idx_food_name (food_name);

-- Update existing food items with default stock values if stock is NULL
UPDATE food SET stock = 50 WHERE stock IS NULL OR stock = 0;

-- Reviews table updates
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS user_id INT NULL;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS university_id VARCHAR(50) NULL;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS department VARCHAR(50) NULL;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add indexes to reviews table
ALTER TABLE reviews ADD INDEX IF NOT EXISTS idx_reviews_canteen (canteen_id);
ALTER TABLE reviews ADD INDEX IF NOT EXISTS idx_reviews_user (user_id);
ALTER TABLE reviews ADD INDEX IF NOT EXISTS idx_reviews_created (created_at);

-- Add foreign key for reviews.user_id
ALTER TABLE reviews ADD CONSTRAINT fk_reviews_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- Add check constraint for rating
ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating CHECK (rating >= 1 AND rating <= 5);

-- Complaints table updates
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS user_id INT NULL;
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS university_id VARCHAR(50) NULL;
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS department VARCHAR(50) NULL;
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add indexes to complaints table
ALTER TABLE complaints ADD INDEX IF NOT EXISTS idx_complaints_canteen (canteen_id);
ALTER TABLE complaints ADD INDEX IF NOT EXISTS idx_complaints_user (user_id);
ALTER TABLE complaints ADD INDEX IF NOT EXISTS idx_complaints_created (created_at);

-- Add foreign key for complaints.user_id
ALTER TABLE complaints ADD CONSTRAINT fk_complaints_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- Canteens table updates
ALTER TABLE canteens ADD INDEX IF NOT EXISTS idx_canteens_status (status);

-- Add Green Canteen if it doesn't exist
INSERT IGNORE INTO canteens (canteen_id, name, location, status) VALUES
(4, 'Green Canteen', 'Science Building, 1st Floor', 'open');

-- Add food items for Green Canteen
INSERT IGNORE INTO food (canteen_id, food_name, price, stock, available) VALUES
(4, 'Mixed Fried Rice', 110.00, 40, 1),
(4, 'Chicken Tikka', 140.00, 30, 1),
(4, 'Vegetable Curry', 70.00, 35, 1),
(4, 'Naan Bread', 20.00, 100, 1),
(4, 'Lassi', 40.00, 0, 0);
