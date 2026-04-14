<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/user_auth.php';

$pageTitle = $pageTitle ?? APP_NAME;
$flash = flash_get();
$admin = current_admin();
$user = current_user();
$isAdminLoggedIn = $admin !== null;
$isUserLoggedIn = is_user_logged_in();
$path = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

function nav_active(string $needle, string $path): string
{
    return str_contains($path, $needle) ? 'is-active' : '';
}
?>
<!doctype html>
<html lang="<?= e(current_language()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('public/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<header class="top-shell">
    <div class="container top-shell-inner">
        <a class="brand-pill" href="<?= e(url($isAdminLoggedIn ? 'admin/dashboard.php' : ($isUserLoggedIn ? 'public/index.php' : 'public/index.php'))) ?>">
            <i class="bi bi-music-note-beamed"></i>
            <span><?= e(APP_NAME) ?></span>
        </a>
        <nav class="desktop-nav">
            <?php if ($isAdminLoggedIn): ?>
                <a class="nav-pill <?= nav_active('/admin/dashboard.php', $path) ?>" href="<?= e(url('admin/dashboard.php')) ?>"><?= e(t('dashboard')) ?></a>
                <a class="nav-pill <?= nav_active('/admin/songs.php', $path) ?>" href="<?= e(url('admin/songs.php')) ?>"><?= e(t('manage_songs')) ?></a>
                <a class="nav-pill <?= nav_active('/admin/users.php', $path) ?>" href="<?= e(url('admin/users.php')) ?>"><?= e(t('manage_users')) ?></a>
                <a class="nav-pill <?= nav_active('/admin/profile.php', $path) ?>" href="<?= e(url('admin/profile.php')) ?>"><?= e(t('profile')) ?></a>
                <a class="nav-pill" href="<?= e(url('admin/logout.php')) ?>"><?= e(t('logout')) ?></a>
            <?php elseif ($isUserLoggedIn): ?>
                <a class="nav-pill <?= nav_active('/public/index.php', $path) ?>" href="<?= e(url('public/index.php')) ?>"><?= e(t('songs')) ?></a>
                <a class="nav-pill <?= nav_active('/public/songbook.php', $path) ?>" href="<?= e(url('public/songbook.php')) ?>"><i class="bi bi-bookmark-fill me-1"></i>My Songbook</a>
                <a class="nav-pill <?= nav_active('/public/settings.php', $path) ?>" href="<?= e(url('public/settings.php')) ?>"><?= e(t('settings')) ?></a>
                <a class="nav-pill <?= nav_active('/public/profile.php', $path) ?>" href="<?= e(url('public/profile.php')) ?>"><?= e(t('profile')) ?></a>
                <a class="nav-pill" href="<?= e(url('public/logout.php')) ?>"><?= e(t('logout')) ?></a>
            <?php else: ?>
                <?php if (!str_contains($path, '/public/index.php')): ?>
                    <a class="nav-pill <?= nav_active('/public/login.php', $path) ?>" href="<?= e(url('public/login.php')) ?>"><?= e(t('login')) ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="py-4 main-shell">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="app-panel">
