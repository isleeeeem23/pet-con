-- PetConnect - Unified Database Schema & Seed Data
-- This file combines schema, updates, and initial seed data.

CREATE DATABASE IF NOT EXISTS petconnect;
USE petconnect;

-- ==========================================
-- 1. TABLES STRUCTURE
-- ==========================================

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    bio TEXT DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT 'default_user.png',
    location VARCHAR(100) DEFAULT NULL,
    language_pref ENUM('en', 'fr', 'ar') DEFAULT 'en',
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cities Table
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Posts Table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_name VARCHAR(50) NOT NULL,
    pet_type VARCHAR(20) DEFAULT 'other',
    breed VARCHAR(50) DEFAULT NULL,
    age VARCHAR(20) DEFAULT NULL,
    health_status TEXT DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    lat DECIMAL(10, 8) DEFAULT NULL,
    lng DECIMAL(11, 8) DEFAULT NULL,
    status ENUM('under_review', 'available', 'pending', 'adopted', 'expired', 'archived', 'rejected') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Post Images Table (Gallery)
CREATE TABLE IF NOT EXISTS post_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Likes Table
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages Table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    post_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adoption Requests Table
CREATE TABLE IF NOT EXISTS adoption_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    requester_id INT NOT NULL,
    owner_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'finalized') DEFAULT 'pending',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Logs Table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    target_type VARCHAR(50) DEFAULT NULL, -- 'user', 'post', 'category', etc.
    target_id INT DEFAULT NULL,
    details TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Follows Table
CREATE TABLE IF NOT EXISTS follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User Preferences Table (for PetMatch)
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_type VARCHAR(50) NOT NULL,
    interest_level INT DEFAULT 1, -- Incremented on likes/views
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_pref (user_id, pet_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 2. SEED DATA
-- ==========================================

-- Categories
INSERT IGNORE INTO categories (name) VALUES ('Dog'), ('Cat'), ('Bird'), ('Rabbit'), ('Other');

-- Cities
INSERT IGNORE INTO cities (name) VALUES ('Casablanca'), ('Rabat'), ('Marrakech'), ('Fes'), ('Tangier'), ('Agadir');

-- Users (All passwords are: password123)
INSERT INTO users (username, email, password, bio, avatar, location, language_pref, role) VALUES 
('AliceAdopt', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Animal lover looking to adopt a furry friend 🐾', 'default_user.png', 'Casablanca, Morocco', 'en', 'admin'),
('HappyShelter', 'shelter@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Local animal shelter dedicated to finding forever homes 🏠', 'default_user.png', 'Rabat, Morocco', 'en', 'user'),
('MonaFR', 'mona@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bénévole passionnée par les animaux', 'default_user.png', 'Paris, France', 'fr', 'user');

-- Posts
INSERT INTO posts (user_id, pet_name, pet_type, breed, age, health_status, image_path, lat, lng, status) VALUES 
(2, 'Buddy', 'dog', 'Golden Retriever', '2 years', 'Healthy, vaccinated, neutered', 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80', 33.5731, -7.5898, 'available'),
(2, 'Luna', 'dog', 'Siberian Husky', '1 year', 'Energetic and friendly, all shots up to date', 'https://images.unsplash.com/photo-1560743641-3914f2c45636?auto=format&fit=crop&w=800&q=80', 33.5891, -7.6038, 'available'),
(2, 'Milo', 'cat', 'Siamese Cat', '6 months', 'Very playful, litter trained', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80', 34.0209, -6.8416, 'available'),
(3, 'Oscar', 'cat', 'Tabby Cat', '3 years', 'Calm, good with children', 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=800&q=80', 48.8566, 2.3522, 'available'),
(3, 'Rex', 'dog', 'German Shepherd', '4 years', 'Trained, vaccinated, loyal companion', 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?auto=format&fit=crop&w=800&q=80', 48.8606, 2.3376, 'available');

-- Likes
INSERT INTO likes (post_id, user_id) VALUES (1, 1), (2, 1), (3, 1), (1, 3), (4, 1);

-- Comments
INSERT INTO comments (post_id, user_id, comment_text) VALUES 
(1, 1, 'He is so cute! I want to adopt him 🐶'),
(2, 1, 'My kids would love this husky!'),
(1, 3, 'Magnifique chien! Il est toujours disponible?'),
(3, 1, 'What a beautiful cat! Is Milo friendly with other cats?');

-- Messages
INSERT INTO messages (sender_id, receiver_id, message_content) VALUES 
(1, 2, 'Hello, I am interested in Buddy! Is he still available?'),
(2, 1, 'Yes, Buddy is still available! Would you like to come visit us?'),
(1, 2, 'That would be great! What are your visiting hours?'),
(3, 2, 'Bonjour, je suis intéressée par Luna. Est-elle toujours disponible?');
