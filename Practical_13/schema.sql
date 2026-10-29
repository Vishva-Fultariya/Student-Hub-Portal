
-- ============================================
-- WDF Practical 13
-- PHP JSON API and Fetch-Based CRUD
-- Database Schema: Student Management System
-- ============================================

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS wdf_practical_13
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- 2. Select the database
USE wdf_practical_13;

-- 3. Create the students table
CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_student_semester
        CHECK (semester BETWEEN 1 AND 8)
);

-- 4. Insert sample records for testing
INSERT IGNORE INTO students (name, email, course, semester)
VALUES
    ('Aarav Patel', 'aarav@example.com', 'B.Tech CSE', 3),
    ('Diya Shah', 'diya@example.com', 'B.Tech IT', 2),
    ('Rohan Mehta', 'rohan@example.com', 'B.Tech CSE', 4);

-- 5. Display student records (for manual testing)
SELECT * FROM students;
