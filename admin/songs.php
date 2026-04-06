<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$search = trim((string) ($_GET['q'] ?? ''));

$sql = 'SELECT s.id, s.title, s.artist, COUNT(v.id) AS version_count
        FROM songs s
        LEFT JOIN chord_versions v ON v.song_id = s.id
        WHERE (? = "" OR s.title LIKE ? OR s.artist LIKE ?)
        GROUP BY s.id
        ORDER BY s.created_at DESC';

$like = '%' . $search . '%';
$stmt = db()->prepare($sql);
$stmt->execute([$search, $like, $like]);
$songs = $stmt->fetchAll();

$pageTitle = t('manage_songs');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0"><?= e(t('songs')) ?></h1>
    <a class="btn btn-primary" href="<?= e(url('admin/song_edit.php')) ?>"><i class="bi bi-plus-lg me-1"></i><?= e(t('add_song')) ?></a>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-8"><input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="<?= e(t('search_songs')) ?>"></div>
    <div class="col-md-4 d-grid d-md-flex gap-2">
        <button class="btn btn-outline-primary" type="submit"><?= e(t('search')) ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('admin/songs.php')) ?>"><?= e(t('reset')) ?></a>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th><?= e(t('title')) ?></th>
                <th><?= e(t('artist')) ?></th>
                <th><?= e(t('versions')) ?></th>
                <th class="text-end"><?= e(t('actions')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$songs): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted"><?= e(t('no_songs_found')) ?></td></tr>
            <?php else: ?>
                <?php foreach ($songs as $song): ?>
                    <tr>
                        <td><?= e($song['title']) ?></td>
                        <td><?= e($song['artist']) ?></td>
                        <td><?= (int) $song['version_count'] ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/song_edit.php?id=' . (int) $song['id'])) ?>"><?= e(t('edit')) ?></a>
                            <a class="btn btn-sm btn-outline-info" href="<?= e(url('admin/song_audio.php?song_id=' . (int) $song['id'])) ?>"><?= e(t('audio')) ?></a>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/versions.php?song_id=' . (int) $song['id'])) ?>"><?= e(t('versions')) ?></a>
                            <form method="post" action="<?= e(url('admin/delete.php')) ?>" class="d-inline" onsubmit="return confirm('<?= e(t('confirm_delete_song')) ?>')">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="type" value="song">
                                <input type="hidden" name="id" value="<?= (int) $song['id'] ?>">
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
