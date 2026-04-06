<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';

$flowKey = 'reset_direct_flow';
$flow = $_SESSION[$flowKey] ?? null;

if (!is_array($flow) || !isset($flow['email'], $flow['token'], $flow['code'], $flow['expires_at']) || (int) $flow['expires_at'] < time()) {
    unset($_SESSION[$flowKey]);
    $flow = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $action = post('action');

    if ($action === 'request_code') {
        if (!password_resets_table_exists()) {
            flash_set('danger', t('password_reset_unavailable'));
            redirect(url('public/reset_password_direct.php'));
        }

        $email = post('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('danger', t('invalid_email'));
            redirect(url('public/reset_password_direct.php'));
        }

        $token = create_password_reset_token($email);
        if ($token === null) {
            flash_set('danger', t('reset_direct_failed'));
            redirect(url('public/reset_password_direct.php'));
        }

        $code = strtoupper(substr($token, 0, 6));
        $_SESSION[$flowKey] = [
            'email' => $email,
            'token' => $token,
            'code' => $code,
            'expires_at' => time() + (30 * 60),
        ];

        redirect(url('public/reset_password_direct.php'));
    }

    if ($action !== 'save_password') {
        redirect(url('public/reset_password_direct.php'));
    }

    if ($flow === null) {
        flash_set('danger', t('invalid_reset_token'));
        redirect(url('public/reset_password_direct.php'));
    }

    $code = strtoupper(post('reset_code'));
    if ($code === '' || !hash_equals((string) $flow['code'], $code)) {
        flash_set('danger', t('invalid_reset_code'));
        redirect(url('public/reset_password_direct.php'));
    }

    $password = post('password');
    $password2 = post('password_confirm');

    if (strlen($password) < 6) {
        flash_set('danger', t('password_too_short'));
        redirect(url('public/reset_password_direct.php'));
    }

    if ($password !== $password2) {
        flash_set('danger', t('password_mismatch'));
        redirect(url('public/reset_password_direct.php'));
    }

    if (!reset_user_password_with_token((string) $flow['token'], $password)) {
        unset($_SESSION[$flowKey]);
        flash_set('danger', t('reset_direct_failed'));
        redirect(url('public/reset_password_direct.php'));
    }

    unset($_SESSION[$flowKey]);
    flash_set('success', t('reset_direct_success'));
    redirect(url('public/login.php'));
}

$pageTitle = t('reset_password_no_link');
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-2"><?= e(t('reset_password_no_link')) ?></h1>
                <p class="text-muted mb-4"><?= e(t('reset_password_no_link_intro')) ?></p>

                <?php if ($flow === null): ?>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="request_code">
                        <div class="col-12">
                            <label class="form-label"><?= e(t('email')) ?></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-12 d-grid">
                            <button class="btn btn-primary" type="submit"><?= e(t('send_reset_code')) ?></button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info py-2" role="alert">
                        <?= e(t('reset_code_here')) ?>
                        <strong><?= e((string) $flow['code']) ?></strong>
                    </div>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="save_password">
                        <div class="col-12">
                            <label class="form-label"><?= e(t('reset_code')) ?></label>
                            <input type="text" class="form-control" name="reset_code" required>
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
                <?php endif; ?>

                <p class="small text-muted mt-3 mb-0">
                    <a href="<?= e(url('public/login.php')) ?>"><?= e(t('back_to_login')) ?></a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
