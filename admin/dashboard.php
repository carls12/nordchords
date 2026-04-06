<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$stats = [
    'songs' => (int) db()->query('SELECT COUNT(*) FROM songs')->fetchColumn(),
    'versions' => (int) db()->query('SELECT COUNT(*) FROM chord_versions')->fetchColumn(),
];

$pageTitle = t('dashboard');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0"><?= e(t('dashboard')) ?></h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1"><?= e(t('songs')) ?></p>
                <p class="display-6 mb-0"><?= $stats['songs'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1"><?= e(t('chord_versions')) ?></p>
                <p class="display-6 mb-0"><?= $stats['versions'] ?></p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <a class="btn btn-primary" href="<?= e(url('admin/songs.php')) ?>"><i class="bi bi-journal-text me-1"></i><?= e(t('manage_songs')) ?></a>
    <a class="btn btn-outline-primary" href="<?= e(url('admin/users.php')) ?>"><i class="bi bi-people me-1"></i><?= e(t('manage_users')) ?></a>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
