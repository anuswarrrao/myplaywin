-- Create the database 'playwin'
CREATE DATABASE IF NOT EXISTS playwin;
USE playwin;

-- Table: admin
CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
    `password` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: daily_games
CREATE TABLE IF NOT EXISTS `daily_games` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `game_name` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    `game_time` DATETIME NOT NULL,
    `coupon_number` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: weekly_jackpot
CREATE TABLE IF NOT EXISTS `weekly_jackpot` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `coupon_number` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    `second_winning_numbers` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    `next_draw_date` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure proper indexes for performance
CREATE INDEX idx_username ON `admin`(`username`);
CREATE INDEX idx_game_name ON `daily_games`(`game_name`);
CREATE INDEX idx_game_time ON `daily_games`(`game_time`);
CREATE INDEX idx_coupon_number ON `daily_games`(`coupon_number`);
CREATE INDEX idx_updated_at ON `weekly_jackpot`(`updated_at`);
CREATE INDEX idx_next_draw_date ON `weekly_jackpot`(`next_draw_date`);
