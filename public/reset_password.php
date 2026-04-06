<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
if ($token === '') {
    flash_set('danger', t('invalid_reset_token'));
    redirect(url('public/login.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $password = post('password');
    $password2 = post('password_confirm');

    if (strlen($password) < 6) {
        flash_set('danger', t('password_too_short'));
        redirect(url('public/reset_password.php?token=' . urlencode($token)));
    }

    if ($password !== $password2) {
        flash_set('danger', t('password_mismatch'));
        redirect(url('public/reset_password.php?token=' . urlencode($token)));
    }

    if (!reset_user_password_with_token($token, $password)) {
        flash_set('danger', t('invalid_reset_token'));
        redirect(url('public/login.php'));
    }

    flash_set('success', t('password_reset_success'));
    redirect(url('public/login.php'));
}

$pageTitle = t('reset_password');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-2"><?= e(t('reset_password')) ?></h1>
                <p class="text-muted mb-4"><?= e(t('reset_password_intro')) ?></p>

                <form method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="col-12">
                        <label class="form-label"><?= e(t('password')) ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" data-toggle-password required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= e(t('confirm_password')) ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password_confirm" data-toggle-password required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary" type="submit"><?= e(t('save_new_password')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
