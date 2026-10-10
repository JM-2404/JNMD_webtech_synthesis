-- QuizUp Campus - database structure
-- Safe to run on a database that already has data: every table uses
-- CREATE TABLE IF NOT EXISTS, so nothing is dropped or overwritten.
-- Import with phpMyAdmin (Import tab) or:  mysql -u root -p quizup_campus < schema.sql

CREATE TABLE IF NOT EXISTS users (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username         VARCHAR(20)  NOT NULL UNIQUE,
    email            VARCHAR(120) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    xp               INT UNSIGNED NOT NULL DEFAULT 0,
    streak           INT UNSIGNED NOT NULL DEFAULT 0,
    best_streak      INT UNSIGNED NOT NULL DEFAULT 0,
    last_active_date DATE NULL,
    last_seen        DATETIME NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(40)  NOT NULL UNIQUE,
    name        VARCHAR(60)  NOT NULL,
    description VARCHAR(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS questions (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    question    VARCHAR(255) NOT NULL,
    option_a    VARCHAR(160) NOT NULL,
    option_b    VARCHAR(160) NOT NULL,
    option_c    VARCHAR(160) NOT NULL,
    option_d    VARCHAR(160) NOT NULL,
    correct     TINYINT UNSIGNED NOT NULL COMMENT '0=A, 1=B, 2=C, 3=D',
    hint        VARCHAR(200) NULL,
    KEY idx_q_cat (category_id),
    CONSTRAINT fk_q_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS flashcards (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    front       VARCHAR(255) NOT NULL,
    back        VARCHAR(400) NOT NULL,
    KEY idx_f_cat (category_id),
    CONSTRAINT fk_f_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_attempts (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    score       INT UNSIGNED NOT NULL,
    total       INT UNSIGNED NOT NULL,
    xp_earned   INT UNSIGNED NOT NULL DEFAULT 0,
    time_taken  INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_a_user (user_id),
    KEY idx_a_cat (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS community_messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    message    VARCHAR(300) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_m_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
