<?php

/**
 * app/models/PostModel.php
 *
 * Handles ALL database operations related to the `posts` table.
 *
 * Responsibilities (Step 10 — Newsfeed Improvements):
 *   - getAllPosts() — Retrieve all posts from all users, ordered newest first (READ)
 *   - getById()     — Retrieve a single post with author details (READ)
 *   - getByUserId() — Retrieve all posts by a specific user (READ)
 *   - create()      — Insert a new post (CREATE)
 *   - update()      — Update post content with ownership verification (UPDATE)
 *   - delete()      — Delete a post with ownership verification (DELETE)
 *   - countAll()    — Total count of posts in system
 *
 * Database Relationships:
 *   - users.id → posts.user_id (one user has many posts)
 *   - posts.id → comments.post_id (one post has many comments)
 *   - posts.id → likes.post_id (one post has many likes)
 *
 * Rules:
 *   - Pure database access: no HTML output, no validation logic, no session logic.
 *   - Every query uses PDO prepared statements to protect against SQL injection.
 *   - Table: posts (id, user_id, content, image, created_at)
 */

class PostModel
{
    private PDO $db;

    /**
     * Constructor — receives the active PDO connection.
     *
     * @param PDO $db Active PDO connection from getDBConnection()
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieve all posts from all users for the newsfeed.
     * Ordered by created_at DESC (newest first), with p.id DESC as secondary sort.
     *
     * Joined with the `users` table to fetch the author's username, full_name, and profile_image.
     *
     * @param int|null $limit Optional maximum number of posts to retrieve (for pagination/infinite scroll)
     * @param int $offset Optional offset
     * @return array Array of associative arrays representing posts
     */
    public function getAllPosts(?int $limit = null, int $offset = 0): array
    {
        $sql = '
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC, p.id DESC
        ';

        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve a single post by its ID with author details.
     *
     * @param int $id Post ID
     * @return array|false Associative array of post data or false if not found
     */
    public function getById(int $id): array|false
    {
        $sql = '
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            WHERE p.id = :id
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all posts created by a specific user.
     *
     * @param int $userId User ID
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        $sql = '
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC, p.id DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new post.
     *
     * @param int $userId The author's user ID
     * @param string $content Text content of the post
     * @param string|null $image Optional image filename
     * @return int|false The newly created post ID, or false on failure
     */
    public function create(int $userId, string $content, ?string $image = null): int|false
    {
        $sql = 'INSERT INTO posts (user_id, content, image) VALUES (:user_id, :content, :image)';
        $stmt = $this->db->prepare($sql);

        $success = $stmt->execute([
            ':user_id' => $userId,
            ':content' => $content,
            ':image'   => $image,
        ]);

        if ($success) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing post's content.
     * Includes strict ownership verification in the WHERE clause.
     *
     * @param int $id Post ID
     * @param int $userId Owner's user ID
     * @param string $content Updated content
     * @return bool True if updated, false if not found or unauthorized
     */
    public function update(int $id, int $userId, string $content): bool
    {
        $sql = 'UPDATE posts SET content = :content WHERE id = :id AND user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':content' => $content,
            ':id'      => $id,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete an existing post.
     * Includes strict ownership verification in the WHERE clause.
     * Cascading deletes on foreign keys in MySQL handle removing comments and likes.
     *
     * @param int $id Post ID
     * @param int $userId Owner's user ID
     * @return bool True if deleted, false if not found or unauthorized
     */
    public function delete(int $id, int $userId): bool
    {
        $sql = 'DELETE FROM posts WHERE id = :id AND user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'      => $id,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Count the total number of posts in the application.
     *
     * @return int
     */
    public function countAll(): int
    {
        $sql = 'SELECT COUNT(*) FROM posts';
        $stmt = $this->db->query($sql);

        return (int) $stmt->fetchColumn();
    }

    // ─── Step 11 & Step 12: Search, Filtering & Reporting Operations ──────────

    /**
     * Retrieve all distinct authors who have created at least one post.
     * Used to populate the Author filter dropdown.
     *
     * @return array Array of associative arrays with id, username, full_name
     */
    public function getDistinctAuthors(): array
    {
        $sql = '
            SELECT DISTINCT u.id, u.username, u.full_name
            FROM users u
            INNER JOIN posts p ON u.id = p.user_id
            ORDER BY u.full_name ASC, u.username ASC
        ';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search and filter posts with exact comment and like counts.
     *
     * Combines Step 11 (Search & Filters) and Step 12 (Detailed Reporting):
     *   - Filters by keyword (posts.content LIKE)
     *   - Filters by author (posts.user_id = :author_id)
     *   - Filters by date range (created_at >= :date_from, created_at <= :date_to)
     *   - Sorts by newest, oldest, most_liked, or most_commented
     *
     * SQL Cartesian Prevention:
     *   Uses derived table subqueries for comments and likes grouped by post_id
     *   before joining to posts. This guarantees comment_count and like_count
     *   are exact and never multiplied.
     *
     * @param array $filters Associative array: ['keyword', 'author_id', 'date_from', 'date_to', 'sort']
     * @param int|null $limit Optional max results
     * @param int $offset Offset
     * @return array Array of matching posts with author info and counts
     */
    public function searchPostsAdvanced(array $filters = [], ?int $limit = 100, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        $keyword  = trim($filters['keyword'] ?? '');
        $authorId = isset($filters['author_id']) && $filters['author_id'] !== '' ? (int) $filters['author_id'] : null;
        $dateFrom = trim($filters['date_from'] ?? '');
        $dateTo   = trim($filters['date_to'] ?? '');
        $sort     = strtolower(trim($filters['sort'] ?? 'newest'));

        if ($keyword !== '') {
            $where[] = 'p.content LIKE :keyword';
            $params[':keyword'] = '%' . $keyword . '%';
        }

        if ($authorId !== null && $authorId > 0) {
            $where[] = 'p.user_id = :author_id';
            $params[':author_id'] = $authorId;
        }

        if ($dateFrom !== '') {
            $where[] = 'p.created_at >= :date_from';
            $params[':date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== '') {
            $where[] = 'p.created_at <= :date_to';
            $params[':date_to'] = $dateTo . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Safe sort mapping
        $orderBy = match ($sort) {
            'oldest'         => 'ORDER BY p.created_at ASC, p.id ASC',
            'most_liked'     => 'ORDER BY like_count DESC, p.created_at DESC, p.id DESC',
            'most_commented' => 'ORDER BY comment_count DESC, p.created_at DESC, p.id DESC',
            default          => 'ORDER BY p.created_at DESC, p.id DESC',
        };

        $sql = "
            SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                   u.username, u.full_name, u.profile_image,
                   COALESCE(c.comment_count, 0) AS comment_count,
                   COALESCE(l.like_count, 0) AS like_count
            FROM posts p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS comment_count
                FROM comments
                GROUP BY post_id
            ) c ON p.id = c.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS like_count
                FROM likes
                GROUP BY post_id
            ) l ON p.id = l.post_id
            {$whereClause}
            {$orderBy}
        ";

        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compute summary statistics for posts matching the given search and filter criteria.
     *
     * Responsibilities (Step 12 — Summary Statistics):
     *   - Total matching posts
     *   - Number of distinct authors for matching posts
     *   - Total comments associated with matching posts
     *   - Total likes associated with matching posts
     *
     * SQL Cartesian Prevention:
     *   Uses pre-aggregated derived tables for comments and likes joined with posts.
     *   Zero inflation occurs even if posts have multiple comments and likes.
     *
     * @param array $filters Associative array: ['keyword', 'author_id', 'date_from', 'date_to']
     * @return array ['total_posts' => int, 'total_authors' => int, 'total_comments' => int, 'total_likes' => int]
     */
    public function getSearchSummaryStats(array $filters = []): array
    {
        $where  = [];
        $params = [];

        $keyword  = trim($filters['keyword'] ?? '');
        $authorId = isset($filters['author_id']) && $filters['author_id'] !== '' ? (int) $filters['author_id'] : null;
        $dateFrom = trim($filters['date_from'] ?? '');
        $dateTo   = trim($filters['date_to'] ?? '');

        if ($keyword !== '') {
            $where[] = 'p.content LIKE :keyword';
            $params[':keyword'] = '%' . $keyword . '%';
        }

        if ($authorId !== null && $authorId > 0) {
            $where[] = 'p.user_id = :author_id';
            $params[':author_id'] = $authorId;
        }

        if ($dateFrom !== '') {
            $where[] = 'p.created_at >= :date_from';
            $params[':date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== '') {
            $where[] = 'p.created_at <= :date_to';
            $params[':date_to'] = $dateTo . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT 
                COUNT(p.id) AS total_posts,
                COUNT(DISTINCT p.user_id) AS total_authors,
                COALESCE(SUM(c.comment_count), 0) AS total_comments,
                COALESCE(SUM(l.like_count), 0) AS total_likes
            FROM posts p
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS comment_count
                FROM comments
                GROUP BY post_id
            ) c ON p.id = c.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) AS like_count
                FROM likes
                GROUP BY post_id
            ) l ON p.id = l.post_id
            {$whereClause}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_posts'    => (int) ($row['total_posts'] ?? 0),
            'total_authors'  => (int) ($row['total_authors'] ?? 0),
            'total_comments' => (int) ($row['total_comments'] ?? 0),
            'total_likes'    => (int) ($row['total_likes'] ?? 0),
        ];
    }

    /**
     * Search posts by content keyword (legacy wrapper delegating to searchPostsAdvanced).
     *
     * @param string $keyword Search keyword
     * @param int|null $limit Optional max results
     * @return array Array of matching posts
     */
    public function searchPosts(string $keyword, ?int $limit = 50): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        return $this->searchPostsAdvanced(['keyword' => $keyword], $limit);
    }
}

