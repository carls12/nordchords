<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin = current_admin();
if (!$admin) {
    flash_set('warning', t('please_login_continue'));
    redirect(url('admin/login.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? null);

    $language = post('language', APP_DEFAULT_LANGUAGE);
    update_admin_language((int) $admin['id'], $language);
    set_language($language);

    flash_set('success', t('profile_saved'));
    redirect(url('admin/profile.php'));
}

$pageTitle = t('profile');
$languages = supported_languages();
require_once __DIR__ . '/../includes/layout_top.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-2"><?= e(t('profile')) ?></h1>
                <p class="text-muted mb-4"><?= e(t('admin_profile_help')) ?></p>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= e(t('username')) ?></label>
                        <input type="text" class="form-control" value="<?= e((string) $admin['username']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= e(t('language')) ?></label>
                        <select class="form-select" name="language" required>
                            <?php foreach ($languages as $code => $label): ?>
                                <option value="<?= e($code) ?>" <?= current_language() === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit"><?= e(t('save_settings')) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/layout_bottom.php'; ?>
