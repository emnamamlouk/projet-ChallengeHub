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

-- ============================================================
-- FIN DU SCRIPT
-- ============================================================
