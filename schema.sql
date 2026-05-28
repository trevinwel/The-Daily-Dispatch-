
CREATE DATABASE IF NOT EXISTS newssite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE newssite;


CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(20)  NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,         
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;


CREATE TABLE categories (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(60) NOT NULL UNIQUE,
    slug  VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categories (name, slug) VALUES
    ('Technology', 'technology'),
    ('Politics',   'politics'),
    ('Sports',     'sports'),
    ('Science',    'science'),
    ('Business',   'business'),
    ('Health',     'health');


CREATE TABLE articles (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED NOT NULL,
    author_id    INT UNSIGNED NOT NULL,
    headline     VARCHAR(100) NOT NULL,
    content      TEXT         NOT NULL,       
    image_path   VARCHAR(255) DEFAULT NULL,
    published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE CASCADE,
    FULLTEXT INDEX ft_headline_content (headline, content),
    INDEX idx_category (category_id),
    INDEX idx_published (published_at)
) ENGINE=InnoDB;


CREATE TABLE user_favorite_categories (
    user_id     INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, category_id),
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    article_id INT UNSIGNED NOT NULL,
    message    VARCHAR(255) NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB;