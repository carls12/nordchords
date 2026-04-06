<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    redirect(url('admin/dashboard.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $username = post('username');
    $password = post('password');

    if (login_admin($username, $password)) {
        flash_set('success', t('welcome_back'));
        redirect(url('admin/dashboard.php'));
    }

    flash_set('danger', t('invalid_credentials'));
    redirect(url('admin/login.php'));
}

$pageTitle = t('admin_login');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-3"><?= e(t('admin_login')) ?></h1>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3">
                           <label class="form-label"><?= e(t('username')) ?></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= e(t('password')) ?></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" data-toggle-password required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100" type="submit"><?= e(t('login')) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
