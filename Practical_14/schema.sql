
CREATE DATABASE IF NOT EXISTS dashboard_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE dashboard_db;

-- User accounts and roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'editor', 'admin')
        NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive')
        NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student records
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    enrollment_no VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event registrations
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (student_id, event_id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Dynamic role-based menu
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50) NOT NULL,
    link VARCHAR(150) NOT NULL,
    min_role ENUM('user', 'editor', 'admin')
        NOT NULL DEFAULT 'user'
);

-- Audit trail of important actions
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
);

-- Migrate menu links from the earlier project version, if present.
UPDATE menu_items
SET title = 'Home', link = 'home.php', min_role = 'user'
WHERE title = 'Home' AND link = 'dashboard.php';

UPDATE menu_items
SET title = 'Events', link = 'events.php', min_role = 'editor'
WHERE title = 'Content Management' AND link = 'content.php';

-- Role-based navigation options; the NOT EXISTS guards make imports repeatable.
INSERT INTO menu_items (title, link, min_role)
SELECT 'Home', 'home.php', 'user'
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE link = 'home.php');

INSERT INTO menu_items (title, link, min_role)
SELECT 'Events', 'events.php', 'editor'
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE link = 'events.php');

INSERT INTO menu_items (title, link, min_role)
SELECT 'Admin Dashboard', 'dashboard.php', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE link = 'dashboard.php');

INSERT INTO menu_items (title, link, min_role)
SELECT 'User Management', 'users.php', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE link = 'users.php');

INSERT INTO menu_items (title, link, min_role)
SELECT 'Audit Logs', 'audit_logs.php', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE link = 'audit_logs.php');

-- Sample students
INSERT IGNORE INTO students (student_name, enrollment_no, email) VALUES
('Aarav Patel', 'STU001', 'aarav@example.com'),
('Diya Shah', 'STU002', 'diya@example.com'),
('Riya Desai', 'STU003', 'riya@example.com');

-- Sample events
INSERT INTO events (title, event_date)
SELECT 'Tech Fest', '2026-11-15'
WHERE NOT EXISTS (
    SELECT 1 FROM events WHERE title = 'Tech Fest' AND event_date = '2026-11-15'
);

INSERT INTO events (title, event_date)
SELECT 'Coding Competition', '2026-11-20'
WHERE NOT EXISTS (
    SELECT 1 FROM events
    WHERE title = 'Coding Competition' AND event_date = '2026-11-20'
);

INSERT INTO events (title, event_date)
SELECT 'Web Development Workshop', '2026-11-25'
WHERE NOT EXISTS (
    SELECT 1 FROM events
    WHERE title = 'Web Development Workshop' AND event_date = '2026-11-25'
);

-- Sample registrations keyed by natural values, not assumed auto-increment IDs.
INSERT IGNORE INTO registrations (student_id, event_id)
SELECT s.id, e.id
FROM students s
JOIN events e ON e.title = 'Tech Fest' AND e.event_date = '2026-11-15'
WHERE s.enrollment_no = 'STU001';

INSERT IGNORE INTO registrations (student_id, event_id)
SELECT s.id, e.id
FROM students s
JOIN events e ON e.title = 'Tech Fest' AND e.event_date = '2026-11-15'
WHERE s.enrollment_no = 'STU002';

INSERT IGNORE INTO registrations (student_id, event_id)
SELECT s.id, e.id
FROM students s
JOIN events e ON e.title = 'Coding Competition' AND e.event_date = '2026-11-20'
WHERE s.enrollment_no = 'STU002';

INSERT IGNORE INTO registrations (student_id, event_id)
SELECT s.id, e.id
FROM students s
JOIN events e ON e.title = 'Web Development Workshop'
    AND e.event_date = '2026-11-25'
WHERE s.enrollment_no = 'STU003';
