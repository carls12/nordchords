<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';
require_user(); // Require login for songbook

$user = current_user();

$search = trim((string) ($_GET['q'] ?? ''));

$sql = 'SELECT s.id, s.title, s.artist, COUNT(v.id) AS version_count
        FROM songs s
        LEFT JOIN chord_versions v ON v.song_id = s.id
        INNER JOIN user_songbooks usb ON usb.song_id = s.id
        WHERE usb.user_id = ? AND (? = "" OR s.title LIKE ? OR s.artist LIKE ?)
        GROUP BY s.id
        ORDER BY s.title ASC';

$like = '%' . $search . '%';
$stmt = db()->prepare($sql);
$stmt->execute([(int) $user['id'], $search, $like, $like]);
$songs = $stmt->fetchAll();

$pageTitle = t('my_songbook');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-bookmark-fill me-2"></i><?= e(t('my_songbook')) ?></h1>
    <a class="btn btn-outline-primary" href="<?= e(url('public/index.php')) ?>"><i class="bi bi-music-note-list me-1"></i><?= e(t('all_songs')) ?></a>
</div>

<form method="get" class="row g-2 mb-4">
    <div class="col-md-8">
        <input type="text" class="form-control" name="q" placeholder="<?= e(t('search_title_artist')) ?>" value="<?= e($search) ?>">
    </div>
    <div class="col-md-4 d-grid d-md-flex gap-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i><?= e(t('search')) ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('public/songbook.php')) ?>"><?= e(t('reset')) ?></a>
    </div>
</form>

<?php if (!$songs): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <?= e(t('no_songs_yet')) ?> 
        <a href="<?= e(url('public/index.php')) ?>" class="alert-link"><?= e(t('browse_library')) ?></a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($songs as $song): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card song-card song-theme h-100 border-0 shadow-sm" style="<?= e(song_theme_vars((string) $song['id'] . '|' . (string) $song['title'])) ?>">
                    <div class="card-body">
                        <h2 class="h5 mb-1"><?= e($song['title']) ?></h2>
                        <p class="text-muted mb-3"><?= e($song['artist'] ?: t('unknown_artist')) ?></p>
                        <p class="small mb-3"><i class="bi bi-collection-play me-1"></i><?= e(t('version_count', ['count' => (string) ((int) $song['version_count'])])) ?></p>
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('public/song.php?id=' . (int) $song['id'])) ?>">
                            <?= e(t('open_song')) ?> <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
