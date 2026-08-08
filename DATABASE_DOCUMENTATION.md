# CampusBite Database Documentation

## Database Overview

**Database Name:** campusbite  
**Engine:** InnoDB  
**Charset:** utf8mb4  
**Collation:** utf8mb4_unicode_ci  

---

## Table Structure

### 1. canteens

**Purpose:** Stores information about campus canteens/restaurants

**Columns:**
- `canteen_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier for each canteen
- `name` (VARCHAR(100), NOT NULL) - Name of the canteen
- `location` (VARCHAR(150), NOT NULL) - Physical location of the canteen
- `status` (ENUM('open', 'closed'), NOT NULL, DEFAULT 'open') - Current operational status

**Indexes:**
- PRIMARY KEY on `canteen_id`
- `idx_canteens_status` on `status` - For filtering open/closed canteens

**Relationships:**
- One-to-many with food (one canteen has many food items)
- One-to-many with reviews (one canteen has many reviews)
- One-to-many with complaints (one canteen has many complaints)
- One-to-many with users (one canteen can have multiple owners)

**Sample Data:** 4 canteens (Central Campus Canteen, Engineering Food Court, Library Snack Corner, Green Canteen)

---

### 2. food

**Purpose:** Stores menu items for each canteen

**Columns:**
- `food_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier for each food item
- `canteen_id` (INT, NOT NULL, FOREIGN KEY) - Reference to canteen table
- `food_name` (VARCHAR(100), NOT NULL) - Name of the food item
- `price` (DECIMAL(8,2), NOT NULL) - Price in local currency
- `stock` (INT, DEFAULT 0) - Current stock quantity
- `available` (TINYINT(1), NOT NULL, DEFAULT 1) - Availability status (1=available, 0=out of stock)

**Indexes:**
- PRIMARY KEY on `food_id`
- `idx_food_canteen` on `canteen_id` - For fetching food by canteen
- `idx_food_available` on `available` - For filtering available items
- `idx_food_name` on `food_name` - For searching food items

**Foreign Keys:**
- `canteen_id` REFERENCES canteens(canteen_id) ON DELETE CASCADE

**Relationships:**
- Many-to-one with canteens (many food items belong to one canteen)

**Sample Data:** 20 food items (5 per canteen)

---

### 3. reviews

**Purpose:** Stores student reviews for canteens

**Columns:**
- `review_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier for each review
- `canteen_id` (INT, NOT NULL, FOREIGN KEY) - Reference to canteen table
- `user_id` (INT, NULL, FOREIGN KEY) - Reference to users table (for logged-in students)
- `student_name` (VARCHAR(100), NOT NULL) - Student's full name
- `university_id` (VARCHAR(50), NULL) - Student's University ID
- `department` (VARCHAR(50), NULL) - Student's department (CSE, EEE, BBA, etc.)
- `rating` (TINYINT, NOT NULL) - Star rating (1-5)
- `comment` (TEXT, NOT NULL) - Review message
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP) - When review was created
- `updated_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) - When review was last modified

**Indexes:**
- PRIMARY KEY on `review_id`
- `idx_reviews_canteen` on `canteen_id` - For fetching reviews by canteen
- `idx_reviews_user` on `user_id` - For fetching reviews by user
- `idx_reviews_created` on `created_at` - For sorting by date

**Foreign Keys:**
- `canteen_id` REFERENCES canteens(canteen_id) ON DELETE CASCADE
- `user_id` REFERENCES users(user_id) ON DELETE SET NULL

**Constraints:**
- `chk_reviews_rating` CHECK (rating >= 1 AND rating <= 5) - Ensures valid rating range

**Relationships:**
- Many-to-one with canteens (many reviews belong to one canteen)
- Many-to-one with users (many reviews can be written by one user)

**Sample Data:** 10 sample reviews with university_id and department

---

### 4. complaints

**Purpose:** Stores student complaints about canteens

**Columns:**
- `complaint_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier for each complaint
- `canteen_id` (INT, NOT NULL, FOREIGN KEY) - Reference to canteen table
- `user_id` (INT, NULL, FOREIGN KEY) - Reference to users table (for logged-in students)
- `student_name` (VARCHAR(100), NOT NULL) - Student's full name
- `university_id` (VARCHAR(50), NULL) - Student's University ID
- `department` (VARCHAR(50), NULL) - Student's department
- `message` (TEXT, NOT NULL) - Complaint message
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP) - When complaint was created
- `updated_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) - When complaint was last modified

**Indexes:**
- PRIMARY KEY on `complaint_id`
- `idx_complaints_canteen` on `canteen_id` - For fetching complaints by canteen
- `idx_complaints_user` on `user_id` - For fetching complaints by user
- `idx_complaints_created` on `created_at` - For sorting by date

**Foreign Keys:**
- `canteen_id` REFERENCES canteens(canteen_id) ON DELETE CASCADE
- `user_id` REFERENCES users(user_id) ON DELETE SET NULL

**Relationships:**
- Many-to-one with canteens (many complaints belong to one canteen)
- Many-to-one with users (many complaints can be submitted by one user)

**Sample Data:** 5 sample complaints with university_id and department

---

### 5. users

**Purpose:** Stores user accounts for authentication and authorization

**Columns:**
- `user_id` (INT, AUTO_INCREMENT, PRIMARY KEY) - Unique identifier for each user
- `university_id` (VARCHAR(50), NOT NULL, UNIQUE) - Student's University ID or Owner ID
- `password` (VARCHAR(255), NOT NULL) - Hashed password (bcrypt)
- `full_name` (VARCHAR(100), NOT NULL) - User's full name
- `role` (ENUM('student', 'owner'), NOT NULL) - User role (student or restaurant owner)
- `department` (VARCHAR(50), NULL) - Student's department (NULL for owners)
- `canteen_id` (INT, NULL, FOREIGN KEY) - Assigned canteen for owners (NULL for students)

**Indexes:**
- PRIMARY KEY on `user_id`
- UNIQUE on `university_id` - Ensures no duplicate University IDs
- `idx_users_university_id` on `university_id` - For fast University ID lookup
- `idx_users_role` on `role` - For filtering by role
- `idx_users_canteen` on `canteen_id` - For fetching owners by canteen

**Foreign Keys:**
- `canteen_id` REFERENCES canteens(canteen_id) ON DELETE SET NULL

**Relationships:**
- One-to-many with reviews (one user can write many reviews)
- One-to-many with complaints (one user can submit many complaints)
- Many-to-one with canteens (multiple owners can be assigned to one canteen)

**Sample Data:** Created via seed_users.php

---

## Database Relationships Diagram

```
canteens (1) ----< (many) food
    |
    +----< (many) reviews
    |
    +----< (many) complaints
    |
    +----< (many) users (owners only)

users (1) ----< (many) reviews
    |
    +----< (many) complaints
```

---

## Indexes Summary

### Performance Indexes

| Table | Index Name | Columns | Purpose |
|-------|-----------|---------|---------|
| canteens | idx_canteens_status | status | Filter open/closed canteens |
| food | idx_food_canteen | canteen_id | Fetch food by canteen |
| food | idx_food_available | available | Filter available items |
| food | idx_food_name | food_name | Search food items |
| reviews | idx_reviews_canteen | canteen_id | Fetch reviews by canteen |
| reviews | idx_reviews_user | user_id | Fetch reviews by user |
| reviews | idx_reviews_created | created_at | Sort by date |
| complaints | idx_complaints_canteen | canteen_id | Fetch complaints by canteen |
| complaints | idx_complaints_user | user_id | Fetch complaints by user |
| complaints | idx_complaints_created | created_at | Sort by date |
| users | idx_users_university_id | university_id | Fast ID lookup |
| users | idx_users_role | role | Filter by role |
| users | idx_users_canteen | canteen_id | Fetch owners by canteen |

---

## Foreign Keys Summary

| Table | Column | References | On Delete |
|-------|--------|------------|-----------|
| food | canteen_id | canteens(canteen_id) | CASCADE |
| reviews | canteen_id | canteens(canteen_id) | CASCADE |
| reviews | user_id | users(user_id) | SET NULL |
| complaints | canteen_id | canteens(canteen_id) | CASCADE |
| complaints | user_id | users(user_id) | SET NULL |
| users | canteen_id | canteens(canteen_id) | SET NULL |

---

## Constraints Summary

| Table | Constraint | Type | Description |
|-------|-----------|------|-------------|
| reviews | chk_reviews_rating | CHECK | Rating must be between 1 and 5 |
| users | university_id | UNIQUE | No duplicate University IDs |

---

## Database Changes Made

### Version 1.0 (Initial Setup)
- Created all 5 tables with basic structure
- Added basic foreign keys and indexes
- Added sample data for canteens, food, reviews, complaints

### Version 2.0 (Authentication Enhancement)
- Added users table for authentication
- Added foreign key from users to canteens
- Added indexes for performance

### Version 3.0 (Food Management Enhancement)
- Added stock column to food table
- Added indexes for food queries
- Updated sample data with stock values

### Version 4.0 (Review/Complaint Enhancement)
- Added user_id, university_id, department to reviews table
- Added user_id, university_id, department to complaints table
- Added created_at and updated_at timestamps
- Added foreign keys from reviews/complaints to users
- Added indexes for user-based queries
- Added CHECK constraint for rating validation
- Updated sample data with university_id and department

### Version 5.0 (Performance Optimization)
- Added comprehensive indexes for all frequently queried columns
- Added engine and charset specifications
- Added collation for proper Unicode support
- Optimized foreign key relationships

---

## Data Preservation

### Migration Strategy

**For existing databases:**
1. Use `auth_migration.sql` to add new columns and indexes
2. All ALTER TABLE statements use `IF NOT EXISTS` to prevent errors
3. Existing data is preserved during migration
4. Default values are set for new columns
5. Foreign keys are added only if they don't exist

**For new installations:**
1. Import `campusbite.sql` directly
2. All tables created with optimized structure
3. Sample data included for testing

---

## Current Database State

### Total Records:
- **Canteens:** 4
- **Food Items:** 20
- **Reviews:** 10
- **Complaints:** 5
- **Users:** Created via seed_users.php (7 demo users)

### Official IUB Records:
- **Students:** 4 (2430901, 2430109, 2340888, 2430876)
- **Owners:** 3 (4006, 5678, 7865)

---

## Security Considerations

1. **Password Storage:** All passwords are hashed using bcrypt (PASSWORD_DEFAULT)
2. **Foreign Key Cascades:** Reviews and complaints are deleted when canteens are deleted
3. **User Data:** User data is preserved when users are deleted (SET NULL)
4. **Rating Validation:** CHECK constraint ensures ratings are between 1-5
5. **Unique IDs:** University IDs are unique to prevent duplicate accounts

---

## Performance Optimizations

1. **Indexes on Foreign Keys:** All foreign key columns are indexed for faster joins
2. **Indexes on Search Columns:** Food name, status, and other frequently queried columns
3. **Indexes on Date Columns:** created_at columns indexed for sorting
4. **InnoDB Engine:** Supports transactions and row-level locking
5. **Proper Data Types:** Using appropriate data types for storage efficiency

---

## Backup and Restore

### Backup Command:
```bash
mysqldump -u root -p campusbite > campusbite_backup.sql
```

### Restore Command:
```bash
mysql -u root -p campusbite < campusbite_backup.sql
```

---

## Maintenance Notes

1. **Regular Backups:** Perform regular database backups
2. **Index Maintenance:** Rebuild indexes periodically for optimal performance
3. **Data Cleanup:** Consider archiving old reviews/complaints periodically
4. **User Cleanup:** Remove inactive user accounts periodically
5. **Schema Updates:** Use migration files for schema changes

---

## Troubleshooting

### Common Issues:

1. **Foreign Key Errors:** Ensure data consistency before adding foreign keys
2. **Index Conflicts:** Drop existing indexes before recreating
3. **Migration Failures:** Check if columns already exist before adding
4. **Character Encoding:** Ensure utf8mb4 charset for proper Unicode support

---

## Future Enhancements

### Potential Additions:
1. **Order System:** Add orders table for food ordering
2. **Favorites:** Add favorites table for student preferences
3. **Notifications:** Add notifications table for alerts
4. **Analytics:** Add analytics tables for reporting
5. **Menu Categories:** Add categories table for food classification

---

## Contact Information

For database-related issues or questions, please contact the database administrator.
