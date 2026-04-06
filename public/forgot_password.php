<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    if (!password_resets_table_exists()) {
        flash_set('danger', t('password_reset_unavailable'));
        redirect(url('public/forgot_password.php'));
    }

    $email = post('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('danger', t('invalid_email'));
        redirect(url('public/forgot_password.php'));
    }

    $token = create_password_reset_token($email);
    if ($token) {
        $resetLink = url('public/reset_password.php?token=' . urlencode($token));
        flash_set('success', t('password_reset_link_ready') . ' ' . $resetLink);
    } else {
        flash_set('info', t('password_reset_if_exists'));
    }

    redirect(url('public/forgot_password.php'));
}

$pageTitle = t('forgot_password');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-2"><?= e(t('forgot_password')) ?></h1>
                <p class="text-muted mb-4"><?= e(t('forgot_password_intro')) ?></p>

                <form method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="col-12">
                        <label class="form-label"><?= e(t('email')) ?></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary" type="submit"><?= e(t('send_reset_link')) ?></button>
                    </div>
                </form>

                <p class="small text-muted mt-3 mb-0">
                    <a href="<?= e(url('public/login.php')) ?>"><?= e(t('back_to_login')) ?></a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
