-- ============================================================
--  social_app.sql
--  Mini Social Networking Web Application — Full Database Schema
--  Character Set : utf8mb4
--  Engine        : InnoDB (required for foreign keys)
-- ============================================================

-- ─── Create & Select Database ────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS social_app
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE social_app;

-- ─── 1. users ────────────────────────────────────────────────────────────────
-- Stores registered user accounts and profile information.
-- Passwords are stored as bcrypt hashes produced by PHP password_hash().

CREATE TABLE IF NOT EXISTS users (
    id            INT          NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    password      VARCHAR(255) NOT NULL,
    full_name     VARCHAR(100) NOT NULL,
    bio           TEXT,
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── 2. posts ────────────────────────────────────────────────────────────────
-- Stores posts created by users.
-- Relationship: one user → many posts  (users.id → posts.user_id)

CREATE TABLE IF NOT EXISTS posts (
    id         INT       NOT NULL AUTO_INCREMENT,
    user_id    INT       NOT NULL,
    content    TEXT      NOT NULL,
    image      VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_posts_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── 3. comments ─────────────────────────────────────────────────────────────
-- Stores comments made by users on posts.
-- Relationships:
--   One post → many comments  (posts.id  → comments.post_id)
--   One user → many comments  (users.id  → comments.user_id)

CREATE TABLE IF NOT EXISTS comments (
    id         INT       NOT NULL AUTO_INCREMENT,
    post_id    INT       NOT NULL,
    user_id    INT       NOT NULL,
    content    TEXT      NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_comments_post
        FOREIGN KEY (post_id) REFERENCES posts (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ─── 4. likes ────────────────────────────────────────────────────────────────
-- Records which users liked which posts.
-- Relationships:
--   One post → many likes  (posts.id → likes.post_id)
--   One user → many likes  (users.id → likes.user_id)
-- UNIQUE (post_id, user_id) prevents a user from liking the same post twice.

CREATE TABLE IF NOT EXISTS likes (
    id      INT NOT NULL AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_like (post_id, user_id),
    CONSTRAINT fk_likes_post
        FOREIGN KEY (post_id) REFERENCES posts (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_likes_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  SAMPLE DATA (Development / Testing Only)
--  Passwords (plaintext → hash):
--    john_doe  → password123  → $2y$10$CnuTyGZkK0zm0gBwtonXkuJ8fRmwes1eGqGoUOo38wXgT9OzJcwb.
--    jane_doe  → mypassword   → $2y$10$BwdcvTd6EdqOgPbZM4eZDO8je1LVhGqIcGZwsYsspiHFaOBf9WZ/i
--    mark_lee  → securepass   → $2y$10$AUHPS6gUN32gK846xi6JnubZ34pdhv4GkaGNaXz07xUwWEsgp/T/.
-- ============================================================

-- ─── Sample Users ────────────────────────────────────────────────────────────

INSERT INTO users (username, password, full_name, bio, profile_image) VALUES
(
    'john_doe',
    '$2y$10$CnuTyGZkK0zm0gBwtonXkuJ8fRmwes1eGqGoUOo38wXgT9OzJcwb.',
    'John Doe',
    'Software developer who loves open source.',
    NULL
),
(
    'jane_doe',
    '$2y$10$BwdcvTd6EdqOgPbZM4eZDO8je1LVhGqIcGZwsYsspiHFaOBf9WZ/i',
    'Jane Doe',
    'UI/UX designer and coffee enthusiast.',
    NULL
),
(
    'mark_lee',
    '$2y$10$AUHPS6gUN32gK846xi6JnubZ34pdhv4GkaGNaXz07xUwWEsgp/T/.',
    'Mark Lee',
    'Student learning web development.',
    NULL
);

-- ─── Sample Posts ─────────────────────────────────────────────────────────────

INSERT INTO posts (user_id, content, image) VALUES
(1, 'Just launched my first open-source project! Really excited to share it with everyone.', NULL),
(2, 'Finished a new UI design concept for a mobile app. What do you think?', NULL),
(3, 'Learning PHP MVC architecture this week. It is challenging but rewarding!', NULL),
(1, 'Reading about PDO and prepared statements. Security first!', NULL);

-- ─── Sample Comments ──────────────────────────────────────────────────────────

INSERT INTO comments (post_id, user_id, content) VALUES
(1, 2, 'Congratulations John! That is awesome.'),
(1, 3, 'Great work! Would love to check it out.'),
(2, 1, 'Looks really clean, Jane. Nice job!'),
(3, 2, 'Keep it up Mark, MVC is very useful once you get the hang of it.'),
(4, 3, 'PDO is the way to go. Good choice!');

-- ─── Sample Likes ─────────────────────────────────────────────────────────────

INSERT INTO likes (post_id, user_id) VALUES
(1, 2),  -- Jane liked John's first post
(1, 3),  -- Mark liked John's first post
(2, 1),  -- John liked Jane's post
(2, 3),  -- Mark liked Jane's post
(3, 1),  -- John liked Mark's post
(4, 2);  -- Jane liked John's second post
