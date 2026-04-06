<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

function songs_has_audio_column_admin_audio_page(): bool
{
    static $hasColumn = null;

    if (is_bool($hasColumn)) {
        return $hasColumn;
    }

    $stmt = db()->query("SHOW COLUMNS FROM songs LIKE 'audio_url'");
    $hasColumn = (bool) $stmt->fetch();
    return $hasColumn;
}

function upload_song_audio_file(): ?string
{
    if (!isset($_FILES['audio_file']) || !is_array($_FILES['audio_file'])) {
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
    $name = (string) ($file['name'] ?? '');
    $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];

    if ($tmp === '' || !in_array($ext, $allowed, true)) {
        return '';
    }

    $dir = __DIR__ . '/../public/assets/audio';
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        return '';
    }

    $fileName = 'song_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $path = $dir . '/' . $fileName;

    if (!move_uploaded_file($tmp, $path)) {
        return '';
    }

    return url('public/assets/audio/' . $fileName);
}

$songId = (int) ($_GET['song_id'] ?? $_POST['song_id'] ?? 0);
if ($songId < 1) {
    flash_set('danger', t('missing_song_id'));
    redirect(url('admin/songs.php'));
}

$hasAudioColumn = songs_has_audio_column_admin_audio_page();
if (!$hasAudioColumn) {
    flash_set('danger', t('song_audio_column_missing'));
    redirect(url('admin/songs.php'));
}

$songStmt = db()->prepare('SELECT id, title, artist, audio_url FROM songs WHERE id = ? LIMIT 1');
$songStmt->execute([$songId]);
$song = $songStmt->fetch();
if (!$song) {
    flash_set('danger', t('song_not_found_flash'));
    redirect(url('admin/songs.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $audioUrl = post('audio_url');
    $uploaded = upload_song_audio_file();

    if ($uploaded === '') {
        flash_set('danger', t('audio_upload_failed'));
        redirect(url('admin/song_audio.php?song_id=' . $songId));
    }
    if (is_string($uploaded) && $uploaded !== null) {
        $audioUrl = $uploaded;
    }

    $upd = db()->prepare('UPDATE songs SET audio_url = ?, updated_at = NOW() WHERE id = ?');
    $upd->execute([$audioUrl, $songId]);

    flash_set('success', t('song_audio_saved'));
    redirect(url('admin/song_audio.php?song_id=' . $songId));
}

$pageTitle = t('song_audio');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0"><?= e(t('song_audio')) ?></h1>
        <p class="text-muted mb-0"><?= e((string) $song['title']) ?><?= !empty($song['artist']) ? ' - ' . e((string) $song['artist']) : '' ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('admin/songs.php')) ?>"><?= e(t('back')) ?></a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (!empty($song['audio_url'])): ?>
            <div class="mb-3">
                <label class="form-label mb-1"><?= e(t('current_audio')) ?></label>
                <audio controls class="w-100">
                    <source src="<?= e((string) $song['audio_url']) ?>">
                </audio>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="song_id" value="<?= (int) $songId ?>">

            <div class="col-12">
                <label class="form-label"><?= e(t('audio_file_upload')) ?></label>
                <input type="file" class="form-control" name="audio_file" accept=".mp3,.wav,.ogg,.m4a,.aac,audio/*">
                <div class="form-text"><?= e(t('audio_file_help')) ?></div>
            </div>
            <div class="col-12">
                <label class="form-label"><?= e(t('audio_url_label')) ?></label>
                <input type="url" class="form-control" name="audio_url" placeholder="<?= e(t('audio_url_placeholder')) ?>" value="<?= e((string) $song['audio_url']) ?>">
                <div class="form-text"><?= e(t('audio_or_upload_help')) ?></div>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit"><?= e(t('save_audio')) ?></button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
