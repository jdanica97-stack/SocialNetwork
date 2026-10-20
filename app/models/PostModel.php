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
        try {
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

            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->attachImagesToPosts($posts);
        } catch (PDOException $e) {
            error_log('PostModel::getAllPosts database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve a single post by its ID with author details.
     *
     * @param int $id Post ID
     * @return array|false Associative array of post data or false if not found
     */
    public function getById(int $id): array|false
    {
        try {
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

            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($post) {
                $images = $this->getImagesByPostId((int) $post['id']);
                if (!empty($images)) {
                    $post['images'] = $images;
                } elseif (!empty($post['image'])) {
                    $post['images'] = [$post['image']];
                } else {
                    $post['images'] = [];
                }
            }

            return $post;
        } catch (PDOException $e) {
            error_log('PostModel::getById database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve all posts created by a specific user.
     *
     * @param int $userId User ID
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        try {
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

            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->attachImagesToPosts($posts);
        } catch (PDOException $e) {
            error_log('PostModel::getByUserId database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new post with one or multiple images.
     *
     * @param int $userId The author's user ID
     * @param string $content Text content of the post
     * @param array|string|null $images Optional image filename or array of filenames
     * @return int|false The newly created post ID, or false on failure
     */
    public function create(int $userId, string $content, array|string|null $images = null): int|false
    {
        $imageList = [];
        if (is_array($images)) {
            $imageList = array_values(array_filter($images));
        } elseif (!empty($images)) {
            $imageList = [$images];
        }

        $primaryImage = !empty($imageList) ? $imageList[0] : null;

        try {
            $sql = 'INSERT INTO posts (user_id, content, image) VALUES (:user_id, :content, :image)';
            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':user_id' => $userId,
                ':content' => $content,
                ':image'   => $primaryImage,
            ]);

            if ($success) {
                $newPostId = (int) $this->db->lastInsertId();
                if (!empty($imageList)) {
                    $this->addImages($newPostId, $imageList);
                }
                return $newPostId;
            }

            return false;
        } catch (PDOException $e) {
            error_log('PostModel::create database error: ' . $e->getMessage());
            return false;
        }
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
        try {
            $sql = 'UPDATE posts SET content = :content WHERE id = :id AND user_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':content' => $content,
                ':id'      => $id,
                ':user_id' => $userId,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('PostModel::update database error: ' . $e->getMessage());
            return false;
        }
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
        try {
            $images = $this->getImagesByPostId($id);

            $sql = 'DELETE FROM posts WHERE id = :id AND user_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'      => $id,
                ':user_id' => $userId,
            ]);

            $deleted = $stmt->rowCount() > 0;
            if ($deleted) {
                foreach ($images as $imgPath) {
                    $fullPath = BASE_PATH . '/public/assets/images/' . $imgPath;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }

            return $deleted;
        } catch (PDOException $e) {
            error_log('PostModel::delete database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add multiple images to a post in post_images.
     *
     * @param int $postId
     * @param array $imagePaths
     * @return bool
     */
    public function addImages(int $postId, array $imagePaths): bool
    {
        if (empty($imagePaths)) {
            return true;
        }

        try {
            $sql = 'INSERT INTO post_images (post_id, image_path, sort_order) VALUES (:post_id, :image_path, :sort_order)';
            $stmt = $this->db->prepare($sql);

            foreach ($imagePaths as $order => $path) {
                if (!empty($path)) {
                    $stmt->execute([
                        ':post_id'    => $postId,
                        ':image_path' => $path,
                        ':sort_order' => (int) $order,
                    ]);
                }
            }

            return true;
        } catch (PDOException $e) {
            error_log('PostModel::addImages database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all image paths for a specific post.
     *
     * @param int $postId
     * @return array Array of image paths
     */
    public function getImagesByPostId(int $postId): array
    {
        try {
            $sql = 'SELECT image_path FROM post_images WHERE post_id = :post_id ORDER BY sort_order ASC, id ASC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':post_id' => $postId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_column($rows, 'image_path');
        } catch (PDOException $e) {
            error_log('PostModel::getImagesByPostId database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get image paths for multiple posts in a single query.
     *
     * @param array $postIds
     * @return array [post_id => [image1, image2, ...]]
     */
    public function getImagesByPostIds(array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        try {
            $inPlaceholders = implode(',', array_fill(0, count($postIds), '?'));
            $sql = "SELECT post_id, image_path FROM post_images WHERE post_id IN ($inPlaceholders) ORDER BY sort_order ASC, id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($postIds));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $row) {
                $pid = (int) $row['post_id'];
                if (!isset($result[$pid])) {
                    $result[$pid] = [];
                }
                $result[$pid][] = $row['image_path'];
            }

            return $result;
        } catch (PDOException $e) {
            error_log('PostModel::getImagesByPostIds database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Attach 'images' array to a collection of post rows.
     *
     * @param array $posts
     * @return array
     */
    private function attachImagesToPosts(array $posts): array
    {
        if (empty($posts)) {
            return [];
        }

        $postIds = array_column($posts, 'id');
        $imagesByPost = $this->getImagesByPostIds($postIds);

        foreach ($posts as &$p) {
            $pid = (int) $p['id'];
            if (isset($imagesByPost[$pid]) && !empty($imagesByPost[$pid])) {
                $p['images'] = $imagesByPost[$pid];
            } elseif (!empty($p['image'])) {
                $p['images'] = [$p['image']];
            } else {
                $p['images'] = [];
            }
        }
        unset($p);

        return $posts;
    }

    /**
     * Update post content and its associated images.
     *
     * @param int $postId
     * @param int $userId
     * @param string $content
     * @param array $keepImages Image paths to keep
     * @param array $newImages Newly uploaded image paths
     * @return bool
     */
    public function updatePostWithImages(int $postId, int $userId, string $content, array $keepImages = [], array $newImages = []): bool
    {
        try {
            // Verify ownership
            $existing = $this->getById($postId);
            if (!$existing || (int)$existing['user_id'] !== $userId) {
                return false;
            }

            // Remove unkept images from post_images & disk
            $currentImages = $this->getImagesByPostId($postId);
            foreach ($currentImages as $img) {
                if (!in_array($img, $keepImages, true)) {
                    $delStmt = $this->db->prepare('DELETE FROM post_images WHERE post_id = :post_id AND image_path = :image_path');
                    $delStmt->execute([':post_id' => $postId, ':image_path' => $img]);
                    $fullPath = BASE_PATH . '/public/assets/images/' . $img;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }

            // Add new images
            if (!empty($newImages)) {
                $this->addImages($postId, $newImages);
            }

            // Determine primary image
            $finalImages = array_merge($keepImages, $newImages);
            $primaryImage = !empty($finalImages) ? $finalImages[0] : null;

            // Update posts table
            $sql = 'UPDATE posts SET content = :content, image = :image WHERE id = :id AND user_id = :user_id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':content' => $content,
                ':image'   => $primaryImage,
                ':id'      => $postId,
                ':user_id' => $userId,
            ]);

            return true;
        } catch (PDOException $e) {
            error_log('PostModel::updatePostWithImages database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Count the total number of posts in the application.
     *
     * @return int
     */
    public function countAll(): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM posts';
            $stmt = $this->db->query($sql);

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('PostModel::countAll database error: ' . $e->getMessage());
            return 0;
        }
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
        try {
            $sql = '
                SELECT DISTINCT u.id, u.username, u.full_name
                FROM users u
                INNER JOIN posts p ON u.id = p.user_id
                ORDER BY u.full_name ASC, u.username ASC
            ';

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('PostModel::getDistinctAuthors database error: ' . $e->getMessage());
            return [];
        }
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
        try {
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

            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->attachImagesToPosts($posts);
        } catch (PDOException $e) {
            error_log('PostModel::searchPostsAdvanced database error: ' . $e->getMessage());
            return [];
        }
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
        try {
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
        } catch (PDOException $e) {
            error_log('PostModel::getSearchSummaryStats database error: ' . $e->getMessage());
            return [
                'total_posts'    => 0,
                'total_authors'  => 0,
                'total_comments' => 0,
                'total_likes'    => 0,
            ];
        }
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

