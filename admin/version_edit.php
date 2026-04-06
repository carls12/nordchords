<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$songId = (int) ($_GET['song_id'] ?? $_POST['song_id'] ?? 0);
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit = $id > 0;

if ($songId < 1) {
    flash_set('danger', t('missing_song_id'));
    redirect(url('admin/songs.php'));
}

$songStmt = db()->prepare('SELECT id, title FROM songs WHERE id = ? LIMIT 1');
$songStmt->execute([$songId]);
$song = $songStmt->fetch();
if (!$song) {
    flash_set('danger', t('song_not_found_flash'));
    redirect(url('admin/songs.php'));
}

$version = [
    'version_label' => '',
    'notes' => '',
    'content' => '',
];

if ($isEdit) {
    $stmt = db()->prepare('SELECT id, song_id, version_label, notes, content FROM chord_versions WHERE id = ? AND song_id = ? LIMIT 1');
    $stmt->execute([$id, $songId]);
    $existing = $stmt->fetch();

    if (!$existing) {
        flash_set('danger', t('version_not_found'));
        redirect(url('admin/versions.php?song_id=' . $songId));
    }

    $version = $existing;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $label = post('version_label');
    $notes = post('notes');
    $content = trim((string) ($_POST['content'] ?? ''));

    if ($label === '' || $content === '') {
        flash_set('danger', t('version_label_content_required'));
        redirect(url('admin/version_edit.php?song_id=' . $songId . ($isEdit ? '&id=' . $id : '')));
    }

    if ($isEdit) {
        $stmt = db()->prepare('UPDATE chord_versions SET version_label = ?, notes = ?, content = ?, updated_at = NOW() WHERE id = ? AND song_id = ?');
        $stmt->execute([$label, $notes, $content, $id, $songId]);
        flash_set('success', t('version_updated'));
    } else {
        $stmt = db()->prepare('INSERT INTO chord_versions (song_id, version_label, notes, content) VALUES (?, ?, ?, ?)');
        $stmt->execute([$songId, $label, $notes, $content]);
        flash_set('success', t('version_created'));
    }

    redirect(url('admin/versions.php?song_id=' . $songId));
}

$pageTitle = $isEdit ? t('edit_version') : t('add_version');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0"><?= e($pageTitle) ?></h1>
        <p class="text-muted mb-0"><?= e(t('song_prefix', ['title' => (string) $song['title']])) ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('admin/versions.php?song_id=' . $songId)) ?>"><?= e(t('back')) ?></a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="song_id" value="<?= (int) $songId ?>">
            <input type="hidden" name="id" value="<?= (int) $id ?>">

            <div class="col-md-6">
                <label class="form-label"><?= e(t('version_label_required')) ?></label>
                <input type="text" class="form-control" name="version_label" placeholder="<?= e(t('version_label_placeholder')) ?>" required value="<?= e($version['version_label']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= e(t('notes_label')) ?></label>
                <input type="text" class="form-control" name="notes" placeholder="<?= e(t('notes_placeholder')) ?>" value="<?= e($version['notes']) ?>">
            </div>
            <div class="col-12">
                <label class="form-label"><?= e(t('chord_content_required')) ?></label>
                <textarea class="form-control" name="content" rows="14" required placeholder="<?= e(t('content_placeholder')) ?>"><?= e($version['content']) ?></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit"><?= e($isEdit ? t('update_version') : t('create_version')) ?></button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
