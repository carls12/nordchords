<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/users.php'));
}

verify_csrf_or_die($_POST['csrf_token'] ?? null);

$action = post('action');
$userId = (int) ($_POST['user_id'] ?? 0);
$username = post('username');

if ($userId < 1 || $username === '') {
    flash_set('danger', t('invalid_user_action'));
    redirect(url('admin/users.php'));
}

if ($action === 'promote') {
    if (promote_user_to_admin($userId)) {
        flash_set('success', t('user_promoted_admin'));
    } else {
        flash_set('danger', t('user_promote_failed'));
    }
    redirect(url('admin/users.php'));
}

if ($action === 'demote') {
    if (demote_user_from_admin($userId, $username)) {
        flash_set('success', t('user_demoted_admin'));
    } else {
        flash_set('danger', t('user_demote_failed'));
    }
    redirect(url('admin/users.php'));
}

flash_set('danger', t('invalid_user_action'));
redirect(url('admin/users.php'));
