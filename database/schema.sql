-- Student Task Tracker Database Schema
-- Run this SQL to create the database and tables

-- Create database
CREATE DATABASE IF NOT EXISTS student_task_tracker;
USE student_task_tracker;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    due_date DATETIME NOT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample user for testing (password: password123)
INSERT IGNORE INTO users (name, email, password) VALUES 
('Test User', 'test@test.com', '$2y$10$YourHashedPasswordHere');

-- Insert sample tasks for testing
INSERT INTO tasks (user_id, title, description, due_date, status) VALUES
(1, 'Complete Assignment', 'Finish the mobile app project', '2026-02-15 10:00:00', 'pending'),
(1, 'Study for Exam', 'Database management exam preparation', '2026-02-20 14:00:00', 'in_progress'),
(1, 'Submit Report', 'Final project report submission', '2026-02-12 23:59:00', 'completed');
