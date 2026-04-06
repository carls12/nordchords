<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function users_table_exists(): bool
{
    static $exists = null;

    if (is_bool($exists)) {
        return $exists;
    }

    $stmt = db()->query("SHOW TABLES LIKE 'users'");
    $exists = (bool) $stmt->fetch();
    return $exists;
}

function users_has_language_column(): bool
{
    static $hasColumn = null;

    if (is_bool($hasColumn)) {
        return $hasColumn;
    }

    if (!users_table_exists()) {
        $hasColumn = false;
        return $hasColumn;
    }

    $stmt = db()->query("SHOW COLUMNS FROM users LIKE 'language'");
    $hasColumn = (bool) $stmt->fetch();
    return $hasColumn;
}

function is_user_logged_in(): bool
{
    return !empty($_SESSION[USER_SESSION_KEY]);
}

function current_user(): ?array
{
    if (!is_user_logged_in() || !users_table_exists()) {
        return null;
    }

    $sql = users_has_language_column()
        ? 'SELECT id, username, email, language FROM users WHERE id = ? LIMIT 1'
        : 'SELECT id, username, email FROM users WHERE id = ? LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([$_SESSION[USER_SESSION_KEY]]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function login_user(string $identity, string $password): bool
{
    if (!users_table_exists()) {
        return false;
    }

    $sql = users_has_language_column()
        ? 'SELECT id, username, email, password_hash, language FROM users WHERE username = ? OR email = ? LIMIT 1'
        : 'SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        return false;
    }

    $_SESSION[USER_SESSION_KEY] = (int) $user['id'];
    $userLang = (string) ($user['language'] ?? APP_DEFAULT_LANGUAGE);
    if (is_supported_language($userLang)) {
        set_language($userLang);
    }

    return true;
}

function register_user(string $username, string $email, string $password, string $language): bool
{
    if (!users_table_exists()) {
        return false;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        return false;
    }

    $lang = is_supported_language($language) ? $language : APP_DEFAULT_LANGUAGE;

    $sql = users_has_language_column()
        ? 'INSERT INTO users (username, email, password_hash, language) VALUES (?, ?, ?, ?)'
        : 'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)';
    $params = users_has_language_column()
        ? [$username, $email, $passwordHash, $lang]
        : [$username, $email, $passwordHash];

    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
    } catch (Throwable $e) {
        return false;
    }

    $_SESSION[USER_SESSION_KEY] = (int) db()->lastInsertId();
    set_language($lang);
    return true;
}

function logout_user(): void
{
    unset($_SESSION[USER_SESSION_KEY]);
}

function require_user(): void
{
    if (!is_user_logged_in()) {
        flash_set('warning', t('please_login_continue'));
        redirect(url('public/login.php'));
    }
}

function update_user_language(int $userId, string $language): void
{
    if (!users_has_language_column() || !is_supported_language($language)) {
        return;
    }

    $stmt = db()->prepare('UPDATE users SET language = ? WHERE id = ?');
    $stmt->execute([$language, $userId]);
}

function password_resets_table_exists(): bool
{
    static $exists = null;

    if (is_bool($exists)) {
        return $exists;
    }

    $stmt = db()->query("SHOW TABLES LIKE 'password_resets'");
    $exists = (bool) $stmt->fetch();
    return $exists;
}

function create_password_reset_token(string $email): ?string
{
    if (!users_table_exists() || !password_resets_table_exists()) {
        return null;
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) {
        return null;
    }

    $userId = (int) $user['id'];
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

    $cleanup = db()->prepare('DELETE FROM password_resets WHERE user_id = ? OR expires_at < NOW()');
    $cleanup->execute([$userId]);

    $insert = db()->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
    $insert->execute([$userId, $tokenHash, $expiresAt]);

    return $token;
}

function reset_user_password_with_token(string $token, string $newPassword): bool
{
    if (!password_resets_table_exists() || strlen($newPassword) < 6) {
        return false;
    }

    $tokenHash = hash('sha256', $token);

    $stmt = db()->prepare('
        SELECT id, user_id
        FROM password_resets
        WHERE token_hash = ?
          AND used_at IS NULL
          AND expires_at >= NOW()
        LIMIT 1
    ');
    $stmt->execute([$tokenHash]);
    $row = $stmt->fetch();
    if (!$row) {
        return false;
    }

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        return false;
    }

    $updUser = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $updUser->execute([$passwordHash, (int) $row['user_id']]);

    $updToken = db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
    $updToken->execute([(int) $row['id']]);

    return true;
}

function reset_user_password_without_link(string $email, string $username, string $newPassword): bool
{
    if (!users_table_exists() || strlen($newPassword) < 6) {
        return false;
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND username = ? LIMIT 1');
    $stmt->execute([$email, $username]);
    $user = $stmt->fetch();
    if (!$user) {
        return false;
    }

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        return false;
    }

    $upd = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $upd->execute([$passwordHash, (int) $user['id']]);

    return true;
}
