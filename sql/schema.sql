-- Database schema voor MBO Soccer
-- Webdev 3 - OOP 1

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `lineups`;
DROP TABLE IF EXISTS `match_attendance`;
DROP TABLE IF EXISTS `trainings`;
DROP TABLE IF EXISTS `matches`;
DROP TABLE IF EXISTS `team_members`;
DROP TABLE IF EXISTS `teams`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Gebruikers / Leden
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('lid', 'trainer', 'medewerker') NOT NULL DEFAULT 'lid',
    `phone` VARCHAR(20) NULL,
    `birthdate` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Teams
CREATE TABLE `teams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `trainer_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_teams_trainer` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Teamleden koppeling
CREATE TABLE `team_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `team_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `position` VARCHAR(50) DEFAULT 'Speler',
    `shirt_number` INT NULL,
    CONSTRAINT `fk_tm_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_team_user` (`team_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Wedstrijden
CREATE TABLE `matches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `team_id` INT NOT NULL,
    `opponent` VARCHAR(100) NOT NULL,
    `match_date` DATETIME NOT NULL,
    `location` ENUM('Thuis', 'Uit') NOT NULL DEFAULT 'Thuis',
    `field_number` VARCHAR(20) DEFAULT 'Veld 1',
    `status` ENUM('Gepland', 'Gespeeld', 'Afgelast') NOT NULL DEFAULT 'Gepland',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_matches_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Trainingsschema
CREATE TABLE `trainings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `team_id` INT NOT NULL,
    `training_date` DATETIME NOT NULL,
    `field_number` VARCHAR(20) DEFAULT 'Veld 2',
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_trainings_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Aanwezigheid bij wedstrijden
CREATE TABLE `match_attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `match_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('aanwezig', 'afwezig', 'onbekend') NOT NULL DEFAULT 'onbekend',
    `reason` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_att_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_att_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_match_user` (`match_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Opstellingen per wedstrijd
CREATE TABLE `lineups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `match_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `position_on_field` VARCHAR(50) NOT NULL,
    `is_starter` TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT `fk_lineup_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lineup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_lineup_match_user` (`match_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
