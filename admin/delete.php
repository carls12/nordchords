<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/songs.php'));
}

verify_csrf_or_die($_POST['csrf_token'] ?? null);

$type = post('type');
$id = (int) ($_POST['id'] ?? 0);
$songId = (int) ($_POST['song_id'] ?? 0);

if ($id < 1) {
    flash_set('danger', t('invalid_delete_request'));
    redirect(url('admin/songs.php'));
}

if ($type === 'song') {
    $stmt = db()->prepare('DELETE FROM songs WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', t('song_deleted'));
    redirect(url('admin/songs.php'));
}

if ($type === 'version') {
    $stmt = db()->prepare('DELETE FROM chord_versions WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', t('version_deleted'));
    redirect(url('admin/versions.php?song_id=' . $songId));
}

flash_set('danger', t('unknown_delete_type'));
redirect(url('admin/songs.php'));
