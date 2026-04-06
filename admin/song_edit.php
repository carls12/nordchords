<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

function songs_has_audio_column_admin(): bool
{
    static $hasColumn = null;

    if (is_bool($hasColumn)) {
        return $hasColumn;
    }

    $stmt = db()->query("SHOW COLUMNS FROM songs LIKE 'audio_url'");
    $hasColumn = (bool) $stmt->fetch();
    return $hasColumn;
}

function process_song_audio_upload_if_any(bool $enabled): ?string
{
    if (!$enabled || !isset($_FILES['audio_file']) || !is_array($_FILES['audio_file'])) {
        return null;
    }

    $file = $_FILES['audio_file'];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        return '';
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? '');
    $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];

    if ($tmp === '' || !in_array($ext, $allowed, true)) {
        return '';
    }

    $targetDir = __DIR__ . '/../public/assets/audio';
    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
        return '';
    }

    $newName = 'song_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $targetDir . '/' . $newName;

    if (!move_uploaded_file($tmp, $targetPath)) {
        return '';
    }

    return url('public/assets/audio/' . $newName);
}

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit = $id > 0;
$hasAudioColumn = songs_has_audio_column_admin();

$song = [
    'title' => '',
    'artist' => '',
    'audio_url' => '',
];

if ($isEdit) {
    $sql = $hasAudioColumn
        ? 'SELECT id, title, artist, audio_url FROM songs WHERE id = ? LIMIT 1'
        : "SELECT id, title, artist, '' AS audio_url FROM songs WHERE id = ? LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        flash_set('danger', t('song_not_found_flash'));
        redirect(url('admin/songs.php'));
    }

    $song = $existing;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $title = post('title');
    $artist = post('artist');
    $audioUrl = $hasAudioColumn ? post('audio_url') : '';
    $uploadedAudioUrl = process_song_audio_upload_if_any($hasAudioColumn);

    if ($uploadedAudioUrl === '') {
        flash_set('danger', t('audio_upload_failed'));
        redirect(url('admin/song_edit.php' . ($isEdit ? '?id=' . $id : '')));
    }

    if (is_string($uploadedAudioUrl) && $uploadedAudioUrl !== null) {
        $audioUrl = $uploadedAudioUrl;
    }

    if ($title === '') {
        flash_set('danger', t('title_required'));
        redirect(url('admin/song_edit.php' . ($isEdit ? '?id=' . $id : '')));
    }

    if ($isEdit) {
        $sql = $hasAudioColumn
            ? 'UPDATE songs SET title = ?, artist = ?, audio_url = ?, updated_at = NOW() WHERE id = ?'
            : 'UPDATE songs SET title = ?, artist = ?, updated_at = NOW() WHERE id = ?';
        $stmt = db()->prepare($sql);
        $params = $hasAudioColumn
            ? [$title, $artist, $audioUrl, $id]
            : [$title, $artist, $id];
        $stmt->execute($params);
        flash_set('success', t('song_updated'));
    } else {
        $sql = $hasAudioColumn
            ? 'INSERT INTO songs (title, artist, audio_url) VALUES (?, ?, ?)'
            : 'INSERT INTO songs (title, artist) VALUES (?, ?)';
        $stmt = db()->prepare($sql);
        $params = $hasAudioColumn
            ? [$title, $artist, $audioUrl]
            : [$title, $artist];
        $stmt->execute($params);
        $id = (int) db()->lastInsertId();
        flash_set('success', t('song_created'));
    }

    redirect(url('admin/versions.php?song_id=' . $id));
}

$pageTitle = $isEdit ? t('edit_song') : t('add_song');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0"><?= e($pageTitle) ?></h1>
    <a class="btn btn-outline-secondary" href="<?= e(url('admin/songs.php')) ?>"><?= e(t('back')) ?></a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $id ?>">

            <div class="col-md-8">
                <label class="form-label"><?= e(t('title_required_star')) ?></label>
                <input type="text" class="form-control" name="title" required value="<?= e($song['title']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= e(t('artist')) ?></label>
                <input type="text" class="form-control" name="artist" value="<?= e($song['artist']) ?>">
            </div>
            <?php if ($hasAudioColumn): ?>
                <div class="col-12">
                    <label class="form-label"><?= e(t('audio_url_label')) ?></label>
                    <input type="url" class="form-control" name="audio_url" placeholder="<?= e(t('audio_url_placeholder')) ?>" value="<?= e($song['audio_url']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label"><?= e(t('audio_file_upload')) ?></label>
                    <input type="file" class="form-control" name="audio_file" accept=".mp3,.wav,.ogg,.m4a,.aac,audio/*">
                    <div class="form-text"><?= e(t('audio_file_help')) ?></div>
                </div>
            <?php endif; ?>
            <div class="col-12">
                <button class="btn btn-primary" type="submit"><?= e($isEdit ? t('update_song') : t('create_song')) ?></button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
