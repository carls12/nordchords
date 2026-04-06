<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$songId = (int) ($_GET['song_id'] ?? 0);
if ($songId < 1) {
    flash_set('danger', t('missing_song_id'));
    redirect(url('admin/songs.php'));
}

$songStmt = db()->prepare('SELECT id, title, artist FROM songs WHERE id = ? LIMIT 1');
$songStmt->execute([$songId]);
$song = $songStmt->fetch();

if (!$song) {
    flash_set('danger', t('song_not_found_flash'));
    redirect(url('admin/songs.php'));
}

$versionsStmt = db()->prepare('SELECT id, version_label, notes, updated_at FROM chord_versions WHERE song_id = ? ORDER BY version_label ASC, id ASC');
$versionsStmt->execute([$songId]);
$versions = $versionsStmt->fetchAll();

$pageTitle = t('manage_versions');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0"><?= e(t('versions')) ?></h1>
        <p class="text-muted mb-0"><?= e($song['title']) ?><?= $song['artist'] ? ' - ' . e($song['artist']) : '' ?></p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= e(url('admin/songs.php')) ?>"><?= e(t('back_to_song_list')) ?></a>
        <a class="btn btn-primary" href="<?= e(url('admin/version_edit.php?song_id=' . $songId)) ?>"><i class="bi bi-plus-lg me-1"></i><?= e(t('add_version')) ?></a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th><?= e(t('label')) ?></th>
                <th><?= e(t('notes')) ?></th>
                <th><?= e(t('updated')) ?></th>
                <th class="text-end"><?= e(t('actions')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$versions): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted"><?= e(t('no_versions_yet')) ?></td></tr>
            <?php else: ?>
                <?php foreach ($versions as $v): ?>
                    <tr>
                        <td><?= e($v['version_label']) ?></td>
                        <td><?= e($v['notes']) ?></td>
                        <td><?= e($v['updated_at']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/version_edit.php?song_id=' . $songId . '&id=' . (int) $v['id'])) ?>"><?= e(t('edit')) ?></a>
                            <form method="post" action="<?= e(url('admin/delete.php')) ?>" class="d-inline" onsubmit="return confirm('<?= e(t('confirm_delete_version')) ?>')">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="type" value="version">
                                <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                                <input type="hidden" name="song_id" value="<?= (int) $songId ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit"><?= e(t('delete')) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
