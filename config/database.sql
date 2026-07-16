CREATE DATABASE IF NOT EXISTS nova_news CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE nova_news;

-- ------------------------------------------------------------
-- 1. users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('admin','user') DEFAULT 'user',
    avatar        VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    slug       VARCHAR(110) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. posts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    slug        VARCHAR(270) NOT NULL UNIQUE,
    content     LONGTEXT     NOT NULL,
    excerpt     VARCHAR(500) DEFAULT NULL,
    image_url   VARCHAR(255) DEFAULT NULL,
    post_type   ENUM('free','premium') DEFAULT 'free',
    category_id INT          DEFAULT NULL,
    is_featured TINYINT(1)   DEFAULT 0,
    view_count  INT          DEFAULT 0,
    status      ENUM('published','draft') DEFAULT 'published',
    created_by  INT          NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_post_admin
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_post_category
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. subscription_plans
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscription_plans (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100)   NOT NULL,
    duration_months     INT            NOT NULL,
    price               DECIMAL(10,2)  NOT NULL,
    discount_percentage DECIMAL(5,2)   DEFAULT 0.00,
    final_price         DECIMAL(10,2)  NOT NULL,
    is_active           TINYINT(1)     DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. user_subscriptions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT  NOT NULL,
    plan_id        INT  NOT NULL,
    start_date     DATE NOT NULL,
    end_date       DATE NOT NULL,
    status         ENUM('active','expired','cancelled') DEFAULT 'active',
    payment_status ENUM('pending','paid','failed')      DEFAULT 'pending',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_subscription_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_subscription_plan
        FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. payments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT           NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    payment_method  VARCHAR(50)   NOT NULL, -- 'Kpay', 'Wave Pay', 'AYA Pay'
    account_name    VARCHAR(100)  NOT NULL, -- Customer ရဲ့ Account Name (မဖြစ်မနေ ထည့်ရမည်)
    account_phone   VARCHAR(20)   NOT NULL, -- Customer ရဲ့ ဖုန်းနံပါတ် (မဖြစ်မနေ ထည့်ရမည်)
    receipt_image   VARCHAR(255)  NOT NULL, -- E-Receipt photo ရဲ့ image path
    status          ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    paid_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payment_subscription
        FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Migration for existing databases (run these manually if columns missing):
-- ALTER TABLE posts ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER category_id;
-- ALTER TABLE posts ADD COLUMN view_count INT DEFAULT 0 AFTER is_featured;

-- ------------------------------------------------------------
-- 7. payment_services
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_services (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(50)  NOT NULL UNIQUE, -- 'kpay', 'wavepay', 'ayapay'
    display_name  VARCHAR(100) NOT NULL,
    phone_number  VARCHAR(20)  NOT NULL,
    logo_image    VARCHAR(255) NOT NULL, 
    account_name  VARCHAR(100) NOT NULL,
    qr_image      VARCHAR(255) DEFAULT NULL,
    is_active     TINYINT(1)   DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. comments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    post_id     INT  NOT NULL,
    user_id     INT  NOT NULL,
    parent_id   INT  DEFAULT NULL,
    content     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_comment_post
        FOREIGN KEY (post_id)   REFERENCES posts(id)    ON DELETE CASCADE,
    CONSTRAINT fk_comment_user
        FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_comment_parent
        FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. likes_dislikes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS likes_dislikes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    type       ENUM('like','dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_like_post
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_like_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post (user_id, post_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. notifications
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    type          VARCHAR(50)  NOT NULL,        -- 'new_subscription', 'payment_received', etc.
    title         VARCHAR(255) NOT NULL,
    message       TEXT         NOT NULL,
    reference_id  INT          DEFAULT NULL,    -- e.g. subscription_id
    reference_type VARCHAR(50) DEFAULT NULL,    -- e.g. 'user_subscriptions'
    is_read       TINYINT(1)   DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================================
--  SEED DATA
-- ============================================================

-- Default admin  (password: Admin@1234)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@gmail.com',
 'Admin@1234',
 'admin');

-- Categories
INSERT INTO categories (name, slug) VALUES
('World',      'world'),
('Politics',   'politics'),
('Business',   'business'),
('Technology', 'technology'),
('Sports',     'sports'),
('Health',     'health'),
('Science',    'science'),
('Entertainment','entertainment');

-- Subscription plans
INSERT INTO subscription_plans (name, duration_months, price, discount_percentage, final_price, is_active) VALUES
('Monthly',          1,  3000,  0.00,  3000,  1),
('Premium 3 Months', 3,  9000, 88.88, 8000, 1),
('Premium 6 Months', 6,  18000, 83.33, 15000, 1);

-----------------------------add to posts table-----------------------------
----------------------------------------------------------------------------
ALTER TABLE posts 
ADD COLUMN is_breaking TINYINT(1) DEFAULT 0 AFTER is_featured;

ALTER TABLE posts 
ADD COLUMN is_editors_pick TINYINT(1) DEFAULT 0 AFTER is_breaking;


