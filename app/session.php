<?php

declare(strict_types=1);

const SESSION_IDLE_TIMEOUT = 1800;
const SESSION_ABSOLUTE_TIMEOUT = 28800;

function request_is_https(): bool
{
    $httpsHeader = (string) ($_SERVER['HTTPS'] ?? '');

    return ($httpsHeader !== '' && $httpsHeader !== 'off')
        || getenv('SESSION_COOKIE_SECURE') === '1';
}

function request_fingerprint(): string
{
    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

    return hash('sha256', $userAgent);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    session_name('SECURITYLABV2SESSID');

    session_start([
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_secure' => request_is_https(),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
    ]);
}

if (!isset($_SESSION['_created_at'])) {
    $_SESSION['_created_at'] = time();
}

if (!isset($_SESSION['_last_activity'])) {
    $_SESSION['_last_activity'] = time();
}

if (!isset($_SESSION['_fingerprint'])) {
    $_SESSION['_fingerprint'] = request_fingerprint();
}

function session_security_is_valid(): bool
{
    $now = time();

    $createdAt = (int) ($_SESSION['_created_at'] ?? 0);
    $lastActivity = (int) ($_SESSION['_last_activity'] ?? 0);
    $fingerprint = (string) ($_SESSION['_fingerprint'] ?? '');

    if ($createdAt === 0 || $lastActivity === 0) {
        return false;
    }

    if (($now - $lastActivity) > SESSION_IDLE_TIMEOUT) {
        return false;
    }

    if (($now - $createdAt) > SESSION_ABSOLUTE_TIMEOUT) {
        return false;
    }

    return hash_equals($fingerprint, request_fingerprint());
}

function is_authenticated(): bool
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return false;
    }

    return session_security_is_valid();
}

function establish_authenticated_session(array $user): void
{
    session_regenerate_id(true);

    $_SESSION = [
        'user' => [
            'id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'full_name' => (string) $user['full_name'],
            'role' => (string) $user['role'],
        ],
        'login_time' => date('Y-m-d H:i:s'),
        '_created_at' => time(),
        '_last_activity' => time(),
        '_fingerprint' => request_fingerprint(),
    ];
}

function destroy_current_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parameters = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $parameters['path'] ?: '/',
                'domain' => $parameters['domain'] ?? '',
                'secure' => (bool) $parameters['secure'],
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );
    }

    session_destroy();
}

function require_login(): void
{
    if (!is_authenticated()) {
        destroy_current_session();

        header('Location: /login.php');
        exit;
    }

    $_SESSION['_last_activity'] = time();
}

function current_user(): array
{
    return is_authenticated()
        ? (array) $_SESSION['user']
        : [];
}

function csrf_token(): string
{
    if (
        !isset($_SESSION['_csrf_token'])
        || !is_string($_SESSION['_csrf_token'])
    ) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_token_is_valid(?string $token): bool
{
    if (
        $token === null
        || !isset($_SESSION['_csrf_token'])
        || !is_string($_SESSION['_csrf_token'])
    ) {
        return false;
    }

    return hash_equals($_SESSION['_csrf_token'], $token);
}