<?php
declare(strict_types=1);
?>
        </div>
    </div>
</main>
<?php
$path = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
$isUser = function_exists('is_user_logged_in') && is_user_logged_in();
$isAdmin = function_exists('current_admin') && current_admin() !== null;
?>
<nav class="mobile-bottom-nav">
    <?php if ($isAdmin): ?>
        <a class="mobile-nav-item <?= str_contains($path, '/admin/dashboard.php') ? 'is-active' : '' ?>" href="<?= e(url('admin/dashboard.php')) ?>">
            <i class="bi bi-grid"></i><span><?= e(t('dashboard')) ?></span>
        </a>
        <a class="mobile-nav-item <?= str_contains($path, '/admin/songs.php') ? 'is-active' : '' ?>" href="<?= e(url('admin/songs.php')) ?>">
            <i class="bi bi-music-note-list"></i><span><?= e(t('songs')) ?></span>
        </a>
        <a class="mobile-nav-item <?= str_contains($path, '/admin/users.php') ? 'is-active' : '' ?>" href="<?= e(url('admin/users.php')) ?>">
            <i class="bi bi-people"></i><span><?= e(t('manage_users')) ?></span>
        </a>
        <a class="mobile-nav-item <?= str_contains($path, '/admin/profile.php') ? 'is-active' : '' ?>" href="<?= e(url('admin/profile.php')) ?>">
            <i class="bi bi-person"></i><span><?= e(t('profile')) ?></span>
        </a>
        <a class="mobile-nav-item" href="<?= e(url('admin/logout.php')) ?>">
            <i class="bi bi-box-arrow-right"></i><span><?= e(t('logout')) ?></span>
        </a>
    <?php elseif ($isUser): ?>
        <a class="mobile-nav-item <?= str_contains($path, '/public/index.php') ? 'is-active' : '' ?>" href="<?= e(url('public/index.php')) ?>">
            <i class="bi bi-house"></i><span><?= e(t('songs')) ?></span>
        </a>
        <a class="mobile-nav-item <?= str_contains($path, '/public/settings.php') ? 'is-active' : '' ?>" href="<?= e(url('public/settings.php')) ?>">
            <i class="bi bi-translate"></i><span><?= e(t('settings')) ?></span>
        </a>
        <a class="mobile-nav-item <?= str_contains($path, '/public/profile.php') ? 'is-active' : '' ?>" href="<?= e(url('public/profile.php')) ?>">
            <i class="bi bi-person"></i><span><?= e(t('profile')) ?></span>
        </a>
        <a class="mobile-nav-item" href="<?= e(url('public/logout.php')) ?>">
            <i class="bi bi-box-arrow-right"></i><span><?= e(t('logout')) ?></span>
        </a>
    <?php else: ?>
        <a class="mobile-nav-item <?= str_contains($path, '/public/login.php') ? 'is-active' : '' ?>" href="<?= e(url('public/login.php')) ?>">
            <i class="bi bi-box-arrow-in-right"></i><span><?= e(t('login')) ?></span>
        </a>
        <a class="mobile-nav-item <?= str_contains($path, '/admin/login.php') ? 'is-active' : '' ?>" href="<?= e(url('admin/login.php')) ?>">
            <i class="bi bi-shield-lock"></i><span><?= e(t('admin')) ?></span>
        </a>
    <?php endif; ?>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = btn.closest('.input-group')?.querySelector('input[data-toggle-password]');
        if (!input) return;
        var isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');
        var icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);
        }
    });
});
</script>
</body>
</html>
