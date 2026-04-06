<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function translations(): array
{
    static $map = null;

    if (is_array($map)) {
        return $map;
    }

    $map = require __DIR__ . '/i18n.php';
    return $map;
}

function supported_languages(): array
{
    return SUPPORTED_LANGUAGES;
}

function is_supported_language(string $lang): bool
{
    return isset(SUPPORTED_LANGUAGES[$lang]);
}

function set_language(string $lang): void
{
    if (!is_supported_language($lang)) {
        return;
    }

    $_SESSION['lang'] = $lang;

    setcookie(LANGUAGE_COOKIE_KEY, $lang, [
        'expires' => time() + (60 * 60 * 24 * 365),
        'path' => rtrim(BASE_URL, '/') . '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
}

function current_language(): string
{
    $lang = $_SESSION['lang'] ?? APP_DEFAULT_LANGUAGE;
    return is_supported_language((string) $lang) ? (string) $lang : APP_DEFAULT_LANGUAGE;
}

function init_language(): void
{
    if (isset($_GET['lang']) && is_string($_GET['lang']) && is_supported_language($_GET['lang'])) {
        set_language($_GET['lang']);
        return;
    }

    if (!empty($_SESSION['lang']) && is_supported_language((string) $_SESSION['lang'])) {
        return;
    }

    $cookieLang = $_COOKIE[LANGUAGE_COOKIE_KEY] ?? '';
    if (is_string($cookieLang) && is_supported_language($cookieLang)) {
        $_SESSION['lang'] = $cookieLang;
        return;
    }

    $_SESSION['lang'] = APP_DEFAULT_LANGUAGE;
}

function t(string $key, array $replace = []): string
{
    $map = translations();
    $lang = current_language();
    $line = $map[$lang][$key] ?? $map[APP_DEFAULT_LANGUAGE][$key] ?? $key;

    foreach ($replace as $placeholder => $value) {
        $line = str_replace('{' . $placeholder . '}', (string) $value, $line);
    }

    return $line;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_or_die(?string $token): void
{
    $valid = isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);

    if (!$valid) {
        http_response_code(400);
        exit(t('invalid_csrf'));
    }
}

function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

function song_theme_vars(string $seed): string
{
    $hash = hash('sha256', $seed);
    $base = hexdec(substr($hash, 0, 8));

    $h1 = $base % 360;
    $h2 = ($h1 + 48) % 360;
    $h3 = ($h1 + 96) % 360;

    return '--song-a:hsl(' . $h1 . ' 78% 92%);'
        . '--song-b:hsl(' . $h2 . ' 84% 88%);'
        . '--song-c:hsl(' . $h3 . ' 76% 84%);'
        . '--song-ink:hsl(' . $h1 . ' 40% 24%);';
}

init_language();
