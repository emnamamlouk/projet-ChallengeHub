-- ============================================================
-- SCRIPT SQL COMPLET - ChallengeHub
-- Base de données : challengehub

-- ============================================================
-- TABLE : users
-- ============================================================
CREATE TABLE `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `email`      VARCHAR(100) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `bio`        TEXT         DEFAULT NULL,
  `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : challenges
-- ============================================================
CREATE TABLE `challenges` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT          NOT NULL,
  `title`       VARCHAR(200) NOT NULL,
  `description` TEXT         NOT NULL,
  `category`    VARCHAR(100) NOT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `deadline`    DATETIME     DEFAULT NULL,
  `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : submissions (participations aux défis)
-- ============================================================
CREATE TABLE `submissions` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `challenge_id` INT          NOT NULL,
  `user_id`      INT          NOT NULL,
  `description`  TEXT         NOT NULL,
  `image`        VARCHAR(255) DEFAULT NULL,
  `link`         VARCHAR(500) DEFAULT NULL,
  `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_participation` (`challenge_id`, `user_id`),
  FOREIGN KEY (`challenge_id`) REFERENCES `challenges`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`)      REFERENCES `users`(`id`)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : votes (1 vote par user par participation)
-- ============================================================
CREATE TABLE `votes` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT       NOT NULL,
  `submission_id` INT       NOT NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_vote` (`user_id`, `submission_id`),
  FOREIGN KEY (`user_id`)       REFERENCES `users`(`id`)       ON DELETE CASCADE,
  FOREIGN KEY (`submission_id`) REFERENCES `submissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : comments (commentaires sur les participations)
-- ============================================================
CREATE TABLE `comments` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT       NOT NULL,
  `submission_id` INT       NOT NULL,
  `content`       TEXT      NOT NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)       REFERENCES `users`(`id`)       ON DELETE CASCADE,
  FOREIGN KEY (`submission_id`) REFERENCES `submissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : challenge_likes (likes sur les défis)
-- ============================================================
CREATE TABLE `challenge_likes` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT       NOT NULL,
  `challenge_id` INT       NOT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_challenge_like` (`user_id`, `challenge_id`),
  FOREIGN KEY (`user_id`)      REFERENCES `users`(`id`)      ON DELETE CASCADE,
  FOREIGN KEY (`challenge_id`) REFERENCES `challenges`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : challenge_comments (commentaires sur les défis)
-- ============================================================
CREATE TABLE `challenge_comments` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT       NOT NULL,
  `challenge_id` INT       NOT NULL,
  `content`      TEXT      NOT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)      REFERENCES `users`(`id`)      ON DELETE CASCADE,
  FOREIGN KEY (`challenge_id`) REFERENCES `challenges`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Ajouter parent_id pour les réponses aux commentaires sur participations
ALTER TABLE `comments` ADD COLUMN `parent_id` INT NULL DEFAULT NULL AFTER `content`;
ALTER TABLE `comments` ADD FOREIGN KEY (`parent_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE;

-- Ajouter parent_id pour les réponses aux commentaires sur défis
ALTER TABLE `challenge_comments` ADD COLUMN `parent_id` INT NULL DEFAULT NULL AFTER `content`;
ALTER TABLE `challenge_comments` ADD FOREIGN KEY (`parent_id`) REFERENCES `challenge_comments`(`id`) ON DELETE CASCADE;
ALTER TABLE `users` ADD COLUMN `is_admin` TINYINT(1) NOT NULL DEFAULT 0;

UPDATE `users` SET `is_admin` = 1 
WHERE `email` IN (
    'hassyeouiahmed@gmail.com',
    'sellamiabderrahmen2@gmail.com',
    'salmalabidi719@gmail.com',
    'ynes.manai@gmail.com',
    'emnamamlouk11@gmail.com'
);
-- ============================================================
-- FIN DU SCRIPT
-- ============================================================
-- ============================================================
-- SCRIPT SQL — Fonctionnalités Bonus ChallengeHub
-- À ajouter à la fin du fichier challengehub.sql existant
-- ============================================================

-- ============================================================
-- 1. SYSTÈME DE BADGES
-- ============================================================

-- Colonne role pour distinguer admin / user
ALTER TABLE `users`
  ADD COLUMN `role` ENUM('user','admin') NOT NULL DEFAULT 'user'
  AFTER `bio`;

-- Table de liaison utilisateur ↔ badge
CREATE TABLE IF NOT EXISTS `user_badges` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT          NOT NULL,
  `badge_key`   VARCHAR(50)  NOT NULL,
  `obtained_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_badge` (`user_id`, `badge_key`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. NOTIFICATIONS AJAX
-- ============================================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `recipient_id` INT          NOT NULL,
  `type`         VARCHAR(50)  NOT NULL,           -- new_vote, new_comment, new_submission, badge_earned
  `message`      TEXT         NOT NULL,
  `link_id`      INT          DEFAULT NULL,       -- ID de la ressource liée (submission, challenge…)
  `is_read`      TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_recipient_unread (`recipient_id`, `is_read`),
  FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTES D'UTILISATION
-- ============================================================
-- • La pagination n'a pas de table dédiée ; elle repose sur LIMIT/OFFSET
--   dans les requêtes existantes via le helper Pagination.php.
--
-- • L'API REST n'a pas de table dédiée ; elle utilise les tables existantes.
--
-- • Pour activer un compte admin :
--     UPDATE users SET role = 'admin' WHERE id = 1;
--
-- ============================================================
