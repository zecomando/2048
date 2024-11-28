CREATE DATABASE IF NOT EXISTS game2048;
USE game2048;

-- Users table with enhanced fields
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    points INT DEFAULT 0,
    theme_preference VARCHAR(20) DEFAULT 'light',
    board_size INT DEFAULT 4,
    remember_token VARCHAR(255),
    email_verified_at TIMESTAMP NULL,
    verification_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expires TIMESTAMP NULL,
    oauth_provider VARCHAR(20),
    oauth_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Scores table with additional metadata
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    score INT NOT NULL,
    board_size INT DEFAULT 4,
    moves_count INT DEFAULT 0,
    time_spent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Saved games table
CREATE TABLE saved_games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    board_state JSON NOT NULL,
    current_score INT NOT NULL,
    board_size INT DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Achievements table
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    points_reward INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User achievements
CREATE TABLE user_achievements (
    user_id INT,
    achievement_id INT,
    achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, achievement_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (achievement_id) REFERENCES achievements(id)
);

-- Daily challenges
CREATE TABLE daily_challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenge_date DATE UNIQUE NOT NULL,
    initial_board JSON NOT NULL,
    target_score INT NOT NULL,
    board_size INT DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User challenge attempts
CREATE TABLE challenge_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    challenge_id INT,
    score INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (challenge_id) REFERENCES daily_challenges(id)
);

-- Insert default achievements
INSERT INTO achievements (name, description, points_reward) VALUES
('First Win', 'Reach the 2048 tile for the first time', 100),
('Speed Demon', 'Win a game in under 5 minutes', 200),
('Efficient Player', 'Win with less than 1000 moves', 150),
('Daily Champion', 'Complete 5 daily challenges', 300),
('Perfect Merger', 'Merge three pairs of tiles in one move', 50),
('High Scorer', 'Reach a score of 20,000', 250);
