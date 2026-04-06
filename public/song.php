<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';
// require_user(); // Removed to allow viewing songs without login
require_once __DIR__ . '/../includes/chord_render.php';

function songs_has_audio_column_public(): bool
{
    static $hasColumn = null;

    if (is_bool($hasColumn)) {
        return $hasColumn;
    }

    $stmt = db()->query("SHOW COLUMNS FROM songs LIKE 'audio_url'");
    $hasColumn = (bool) $stmt->fetch();
    return $hasColumn;
}

$songId = (int) ($_GET['id'] ?? 0);
$versionId = (int) ($_GET['v'] ?? 0);

if ($songId < 1) {
    http_response_code(404);
    exit(t('song_not_found'));
}

$hasSongAudioColumn = songs_has_audio_column_public();
$songSql = $hasSongAudioColumn
    ? 'SELECT id, title, artist, audio_url FROM songs WHERE id = ? LIMIT 1'
    : "SELECT id, title, artist, '' AS audio_url FROM songs WHERE id = ? LIMIT 1";
$stmt = db()->prepare($songSql);
$stmt->execute([$songId]);
$song = $stmt->fetch();

if (!$song) {
    http_response_code(404);
    exit(t('song_not_found'));
}

$versionsSql = 'SELECT id, version_label, content, notes FROM chord_versions WHERE song_id = ? ORDER BY version_label ASC, id ASC';
$versionsStmt = db()->prepare($versionsSql);
$versionsStmt->execute([$songId]);
$versions = $versionsStmt->fetchAll();

$currentVersion = null;
if ($versions) {
    if ($versionId > 0) {
        foreach ($versions as $v) {
            if ((int) $v['id'] === $versionId) {
                $currentVersion = $v;
                break;
            }
        }
    }

    if (!$currentVersion) {
        $currentVersion = $versions[0];
    }
}

$pageTitle = $song['title'];
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="mb-3">
    <a class="text-decoration-none" href="<?= e(url('public/index.php')) ?>"><i class="bi bi-arrow-left me-1"></i><?= e(t('back_to_songs')) ?></a>
</div>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div class="song-hero w-100" style="<?= e(song_theme_vars((string) $song['id'] . '|' . (string) $song['title'])) ?>">
        <h1 class="h3 mb-1"><?= e($song['title']) ?></h1>
        <p class="text-muted mb-0"><?= e($song['artist'] ?: t('unknown_artist')) ?></p>
    </div>
</div>

<?php if (!empty($song['audio_url'])): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label mb-1"><?= e(t('audio_track')) ?></label>
            <audio controls class="w-100">
                <source src="<?= e((string) $song['audio_url']) ?>">
            </audio>
        </div>
    </div>
<?php endif; ?>

<?php if (!$versions): ?>
    <div class="alert alert-warning"><?= e(t('no_versions_for_song')) ?></div>
<?php else: ?>
    <form method="get" class="row g-2 mb-3">
        <input type="hidden" name="id" value="<?= (int) $songId ?>">
        <div class="col-md-6">
            <label class="form-label"><?= e(t('chord_version')) ?></label>
            <select name="v" class="form-select" onchange="this.form.submit()">
                <?php foreach ($versions as $v): ?>
                    <option value="<?= (int) $v['id'] ?>" <?= ((int) $currentVersion['id'] === (int) $v['id']) ? 'selected' : '' ?>>
                        <?= e($v['version_label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div class="lyrics-box">
        <?php if (!empty($currentVersion['notes'])): ?>
            <p class="small text-muted mb-3"><i class="bi bi-info-circle me-1"></i><?= e($currentVersion['notes']) ?></p>
        <?php endif; ?>

        <?= render_chord_text((string) $currentVersion['content']) ?>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
