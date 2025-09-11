-- omniSEO Database Schema
CREATE DATABASE IF NOT EXISTS omniseo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE omniseo;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    credits INT DEFAULT 100,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Generations table
CREATE TABLE generations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    prompt TEXT NOT NULL,
    content_type VARCHAR(50) DEFAULT 'blog_article',
    tone VARCHAR(50) DEFAULT 'informative',
    word_length VARCHAR(20) DEFAULT 'medium',
    output LONGTEXT NOT NULL,
    word_count INT NOT NULL,
    credits_used INT DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subscriptions table (for future use)
CREATE TABLE subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_name VARCHAR(50) NOT NULL,
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- WordPress accounts table (for future use)
CREATE TABLE wp_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    wp_site_url VARCHAR(255) NOT NULL,
    wp_username VARCHAR(100) NOT NULL,
    wp_app_password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample user for testing
INSERT INTO users (email, password_hash, credits) VALUES 
('nazmussakibsyam2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 100);
