<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$tableCheck = db()->query("SHOW TABLES LIKE 'users'");
if (!$tableCheck->fetch()) {
    flash_set('danger', t('users_table_missing'));
    redirect(url('admin/dashboard.php'));
}

$stmt = db()->query('SELECT id, username, email, language, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

$pageTitle = t('manage_users');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0"><?= e(t('manage_users')) ?></h1>
    <a class="btn btn-outline-secondary" href="<?= e(url('admin/dashboard.php')) ?>"><?= e(t('back')) ?></a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th><?= e(t('username')) ?></th>
                <th><?= e(t('email')) ?></th>
                <th><?= e(t('language')) ?></th>
                <th><?= e(t('admin')) ?></th>
                <th class="text-end"><?= e(t('actions')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$users): ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted"><?= e(t('no_users_found')) ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <?php $isAdminUser = user_has_admin_access((int) $u['id'], (string) $u['username']); ?>
                    <tr>
                        <td><?= (int) $u['id'] ?></td>
                        <td><?= e((string) $u['username']) ?></td>
                        <td><?= e((string) $u['email']) ?></td>
                        <td><?= e((string) $u['language']) ?></td>
                        <td>
                            <?php if ($isAdminUser): ?>
                                <span class="badge text-bg-success"><?= e(t('yes')) ?></span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary"><?= e(t('no')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <form method="post" action="<?= e(url('admin/user_role.php')) ?>" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                <input type="hidden" name="username" value="<?= e((string) $u['username']) ?>">
                                <?php if ($isAdminUser): ?>
                                    <input type="hidden" name="action" value="demote">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('<?= e(t('confirm_demote_admin')) ?>')">
                                        <?= e(t('remove_admin')) ?>
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="promote">
                                    <button class="btn btn-sm btn-outline-primary" type="submit">
                                        <?= e(t('make_admin')) ?>
                                    </button>
                                <?php endif; ?>
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
