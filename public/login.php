<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_user_logged_in()) {
    redirect(url('public/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $identity = post('identity');
    $password = post('password');

    if (login_user($identity, $password)) {
        flash_set('success', t('welcome_back'));
        redirect(url('public/index.php'));
    }

    // Allow admins to sign in from the same form.
    if (login_admin($identity, $password)) {
        flash_set('success', t('welcome_back'));
        redirect(url('admin/dashboard.php'));
    }

    flash_set('danger', t('invalid_credentials'));
    redirect(url('public/login.php'));
}

$pageTitle = t('login');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-2"><?= e(t('login')) ?></h1>
                <p class="text-muted mb-4"><?= e(t('login_intro')) ?></p>

                <form method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="col-12">
                        <label class="form-label"><?= e(t('email_or_username')) ?></label>
                        <input type="text" class="form-control" name="identity" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= e(t('password')) ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" data-toggle-password required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary" type="submit"><?= e(t('login')) ?></button>
                    </div>
                </form>

                <p class="small text-muted mt-3 mb-0">
                    <a href="<?= e(url('public/reset_password_direct.php')) ?>"><?= e(t('reset_password_no_link')) ?></a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
