<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function admins_has_language_column(): bool
{
    static $hasColumn = null;

    if (is_bool($hasColumn)) {
        return $hasColumn;
    }

    $stmt = db()->query("SHOW COLUMNS FROM admins LIKE 'language'");
    $hasColumn = (bool) $stmt->fetch();
    return $hasColumn;
}

function admins_has_user_id_column(): bool
{
    static $hasColumn = null;

    if (is_bool($hasColumn)) {
        return $hasColumn;
    }

    $stmt = db()->query("SHOW COLUMNS FROM admins LIKE 'user_id'");
    $hasColumn = (bool) $stmt->fetch();
    return $hasColumn;
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function current_admin(): ?array
{
    if (!is_admin_logged_in()) {
        return null;
    }

    $hasLang = admins_has_language_column();
    $hasUserId = admins_has_user_id_column();
    $sql = 'SELECT id, username'
        . ($hasLang ? ', language' : '')
        . ($hasUserId ? ', user_id' : '')
        . ' FROM admins WHERE id = ? LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([$_SESSION[ADMIN_SESSION_KEY]]);

    $admin = $stmt->fetch();

    return $admin ?: null;
}

function login_admin(string $username, string $password): bool
{
    $hasLang = admins_has_language_column();
    $hasUserId = admins_has_user_id_column();
    $sql = 'SELECT id, username, password_hash'
        . ($hasLang ? ', language' : '')
        . ($hasUserId ? ', user_id' : '')
        . ' FROM admins WHERE username = ? LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return false;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }

    $_SESSION[ADMIN_SESSION_KEY] = (int) $admin['id'];
    $adminLang = (string) ($admin['language'] ?? APP_DEFAULT_LANGUAGE);
    if (is_supported_language($adminLang)) {
        set_language($adminLang);
    }

    return true;
}

function logout_admin(): void
{
    unset($_SESSION[ADMIN_SESSION_KEY]);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        flash_set('warning', t('admin_auth_required'));
        redirect(url('admin/login.php'));
    }
}

function update_admin_language(int $adminId, string $language): void
{
    if (!is_supported_language($language) || !admins_has_language_column()) {
        return;
    }

    $stmt = db()->prepare('UPDATE admins SET language = ? WHERE id = ?');
    $stmt->execute([$language, $adminId]);
}

function user_has_admin_access(int $userId, string $username): bool
{
    if ($userId < 1 || $username === '') {
        return false;
    }

    if (admins_has_user_id_column()) {
        $stmt = db()->prepare('SELECT id FROM admins WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        if ($stmt->fetch()) {
            return true;
        }
    }

    $stmt = db()->prepare('SELECT id FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    return (bool) $stmt->fetch();
}

function promote_user_to_admin(int $userId): bool
{
    if ($userId < 1) {
        return false;
    }

    $userStmt = db()->prepare('SELECT id, username, password_hash, email, language FROM users WHERE id = ? LIMIT 1');
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();
    if (!$user) {
        return false;
    }

    $username = (string) $user['username'];
    $passwordHash = (string) $user['password_hash'];
    $language = is_supported_language((string) ($user['language'] ?? '')) ? (string) $user['language'] : APP_DEFAULT_LANGUAGE;

    if (admins_has_user_id_column()) {
        $find = db()->prepare('SELECT id FROM admins WHERE user_id = ? OR username = ? LIMIT 1');
        $find->execute([$userId, $username]);
        $admin = $find->fetch();

        if ($admin) {
            $upd = db()->prepare('UPDATE admins SET user_id = ?, username = ?, password_hash = ?, language = ? WHERE id = ?');
            $upd->execute([$userId, $username, $passwordHash, $language, (int) $admin['id']]);
            return true;
        }

        $ins = db()->prepare('INSERT INTO admins (user_id, username, password_hash, language) VALUES (?, ?, ?, ?)');
        $ins->execute([$userId, $username, $passwordHash, $language]);
        return true;
    }

    $find = db()->prepare('SELECT id FROM admins WHERE username = ? LIMIT 1');
    $find->execute([$username]);
    $admin = $find->fetch();

    if ($admin) {
        $upd = db()->prepare('UPDATE admins SET password_hash = ?, language = ? WHERE id = ?');
        $upd->execute([$passwordHash, $language, (int) $admin['id']]);
        return true;
    }

    $ins = db()->prepare('INSERT INTO admins (username, password_hash, language) VALUES (?, ?, ?)');
    $ins->execute([$username, $passwordHash, $language]);
    return true;
}

function demote_user_from_admin(int $userId, string $username): bool
{
    if ($userId < 1 || $username === '') {
        return false;
    }

    if (admins_has_user_id_column()) {
        $stmt = db()->prepare('DELETE FROM admins WHERE user_id = ?');
        $stmt->execute([$userId]);
        return true;
    }

    // Fallback mode without user_id relation. Keep the built-in admin account safe.
    if (strtolower($username) === 'admin') {
        return false;
    }

    $stmt = db()->prepare('DELETE FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    return true;
}
