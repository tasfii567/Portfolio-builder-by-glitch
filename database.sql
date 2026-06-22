-- ============================================================
--  Portfolio Builder (by glitch) — Database Schema
--  Import this ONCE in phpMyAdmin (select the DB first, then Import).
-- ============================================================

CREATE DATABASE IF NOT EXISTS portfolio_builder
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_builder;

-- 1. Users (register / login)
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       VARCHAR(100) NOT NULL DEFAULT 'Member',
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Portfolios (one per user)
CREATE TABLE IF NOT EXISTS portfolios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT          NOT NULL,
  slug       VARCHAR(100) NOT NULL UNIQUE,
  title      VARCHAR(200) NOT NULL DEFAULT '',
  owner_name VARCHAR(150) NOT NULL DEFAULT '',
  owner_role VARCHAR(150) NOT NULL DEFAULT '',
  template   VARCHAR(50)  NOT NULL DEFAULT 'midnight',
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_portfolio_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Projects
CREATE TABLE IF NOT EXISTS projects (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT          NOT NULL,
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  github_link VARCHAR(255) NOT NULL DEFAULT '',
  live_link   VARCHAR(255) NOT NULL DEFAULT '',
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_project_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Project images (many per project)
CREATE TABLE IF NOT EXISTS project_images (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT          NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  CONSTRAINT fk_image_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Visitors (counts portfolio views)
CREATE TABLE IF NOT EXISTS visitors (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  portfolio_slug VARCHAR(100) NOT NULL,
  ip             VARCHAR(45),
  visited_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_slug (portfolio_slug)
) ENGINE=InnoDB;

-- 6. Profiles (photo, github, bio, skills, experience, education)
CREATE TABLE IF NOT EXISTS profiles (
  user_id    INT PRIMARY KEY,
  image      VARCHAR(255) NOT NULL DEFAULT '',
  phone      VARCHAR(50)  NOT NULL DEFAULT '',
  location   VARCHAR(120) NOT NULL DEFAULT '',
  website    VARCHAR(200) NOT NULL DEFAULT '',
  github     VARCHAR(255) NOT NULL DEFAULT '',
  summary    TEXT,
  skills     TEXT,
  experience TEXT,
  education  TEXT,
  CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  No demo user is inserted — passwords must be hashed by PHP.
--  Open register.php in your browser and create your account.
--  (The app also self-creates/updates the profiles table, so even
--   if you skip this file the pages still work.)
-- ============================================================
