CREATE DATABASE IF NOT EXISTS sipsip;
USE sipsip;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS water_intake_records (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount_ml INT NOT NULL,
    recorded_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT PRIMARY KEY,
    daily_goal_ml INT NOT NULL DEFAULT 2000,
    button_1_ml INT NOT NULL DEFAULT 250,
    button_2_ml INT NOT NULL DEFAULT 500,
    reset_time TIME NOT NULL DEFAULT '00:00:00',
    notifications_enabled BOOLEAN NOT NULL DEFAULT 1,
    tips_enabled BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
