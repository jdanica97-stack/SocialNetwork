<?php
/**
 * app/views/posts/_gallery.php
 *
 * Responsive multi-image gallery partial for posts.
 * Expects $post to be defined with an 'images' array or 'image' field.
 */

$postImages = !empty($post['images']) && is_array($post['images'])
    ? array_values(array_filter($post['images']))
    : (!empty($post['image']) ? [$post['image']] : []);

$imgCount = count($postImages);
if ($imgCount === 0) {
    return;
}

$galleryJson = htmlspecialchars(json_encode(array_map(function($path) {
    return '/SocialNetwork/public/assets/images/' . ltrim($path, '/');
}, $postImages)), ENT_QUOTES, 'UTF-8');
?>

<div class="post-gallery-container mb-3" data-gallery-count="<?= $imgCount ?>" data-gallery-images="<?= $galleryJson ?>">
    <?php if ($imgCount === 1): ?>
        <div class="post-gallery-single">
            <img
                src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($postImages[0], ENT_QUOTES, 'UTF-8') ?>"
                alt="Post photo"
                class="img-fluid rounded post-gallery-img"
                loading="lazy"
                data-gallery-index="0"
            >
        </div>
    <?php elseif ($imgCount === 2): ?>
        <div class="post-gallery-grid post-gallery-grid-2">
            <?php foreach ($postImages as $idx => $imgPath): ?>
                <div class="post-gallery-item" data-gallery-index="<?= $idx ?>">
                    <img
                        src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Post photo <?= $idx + 1 ?>"
                        class="post-gallery-img"
                        loading="lazy"
                        data-gallery-index="<?= $idx ?>"
                    >
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($imgCount === 3): ?>
        <div class="post-gallery-grid post-gallery-grid-3">
            <div class="post-gallery-item post-gallery-lead" data-gallery-index="0">
                <img
                    src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($postImages[0], ENT_QUOTES, 'UTF-8') ?>"
                    alt="Post photo 1"
                    class="post-gallery-img"
                    loading="lazy"
                    data-gallery-index="0"
                >
            </div>
            <div class="post-gallery-sub">
                <div class="post-gallery-item" data-gallery-index="1">
                    <img
                        src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($postImages[1], ENT_QUOTES, 'UTF-8') ?>"
                        alt="Post photo 2"
                        class="post-gallery-img"
                        loading="lazy"
                        data-gallery-index="1"
                    >
                </div>
                <div class="post-gallery-item" data-gallery-index="2">
                    <img
                        src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($postImages[2], ENT_QUOTES, 'UTF-8') ?>"
                        alt="Post photo 3"
                        class="post-gallery-img"
                        loading="lazy"
                        data-gallery-index="2"
                    >
                </div>
            </div>
        </div>
    <?php elseif ($imgCount === 4): ?>
        <div class="post-gallery-grid post-gallery-grid-4">
            <?php foreach ($postImages as $idx => $imgPath): ?>
                <div class="post-gallery-item" data-gallery-index="<?= $idx ?>">
                    <img
                        src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Post photo <?= $idx + 1 ?>"
                        class="post-gallery-img"
                        loading="lazy"
                        data-gallery-index="<?= $idx ?>"
                    >
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php // 5 or more images: Show 4 in grid, 4th item has +N overlay ?>
        <div class="post-gallery-grid post-gallery-grid-4 post-gallery-more">
            <?php for ($idx = 0; $idx < 4; $idx++): ?>
                <div class="post-gallery-item position-relative" data-gallery-index="<?= $idx ?>">
                    <img
                        src="/SocialNetwork/public/assets/images/<?= htmlspecialchars($postImages[$idx], ENT_QUOTES, 'UTF-8') ?>"
                        alt="Post photo <?= $idx + 1 ?>"
                        class="post-gallery-img"
                        loading="lazy"
                        data-gallery-index="<?= $idx ?>"
                    >
                    <?php if ($idx === 3 && $imgCount > 4): ?>
                        <div class="post-gallery-overlay" data-gallery-index="3">
                            <span class="post-gallery-overlay-badge">+<?= ($imgCount - 4) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
