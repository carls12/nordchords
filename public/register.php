<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/user_auth.php';

if (is_user_logged_in()) {
    redirect(url('public/index.php'));
}

// Registration is disabled for normal users
flash_set('info', t('registration_disabled'));
redirect(url('public/index.php'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $username = post('username');
    $email = post('email');
    $password = post('password');
    $password2 = post('password_confirm');
    $language = post('language', APP_DEFAULT_LANGUAGE);

    if ($username === '' || $email === '' || $password === '') {
        flash_set('danger', t('register_required_fields'));
        redirect(url('public/register.php'));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('danger', t('invalid_email'));
        redirect(url('public/register.php'));
    }

    if (strlen($password) < 6) {
        flash_set('danger', t('password_too_short'));
        redirect(url('public/register.php'));
    }

    if ($password !== $password2) {
        flash_set('danger', t('password_mismatch'));
        redirect(url('public/register.php'));
    }

    if (!register_user($username, $email, $password, $language)) {
        flash_set('danger', t('register_failed'));
        redirect(url('public/register.php'));
    }

    flash_set('success', t('register_success'));
    redirect(url('public/index.php'));
}

$pageTitle = t('register');
$languages = supported_languages();
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-2"><?= e(t('register')) ?></h1>
                <p class="text-muted mb-4"><?= e(t('register_intro')) ?></p>

                <form method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="col-md-6">
                        <label class="form-label"><?= e(t('username')) ?></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(t('email')) ?></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(t('password')) ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" data-toggle-password required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= e(t('confirm_password')) ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password_confirm" data-toggle-password required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" aria-label="toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= e(t('language')) ?></label>
                        <select class="form-select" name="language" required>
                            <?php foreach ($languages as $code => $label): ?>
                                <option value="<?= e($code) ?>" <?= current_language() === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-primary" type="submit"><?= e(t('register')) ?></button>
                    </div>
                </form>

                <p class="small text-muted mt-3 mb-0">
                    <?= e(t('already_have_account')) ?>
                    <a href="<?= e(url('public/login.php')) ?>"><?= e(t('login')) ?></a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
