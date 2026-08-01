<?php

declare(strict_types=1);

/*
 * Configuración deliberadamente débil para la V1:
 * - Cookie sin Secure porque trabaja mediante HTTP.
 * - HttpOnly desactivado para las futuras prácticas XSS.
 * - Sin regeneración automática de sesión.
 *
 * Estas configuraciones deberán corregirse en la V2.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('SECURITYLABSESSID');

    session_start([
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_secure' => false,
        'cookie_httponly' => false,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => false,
    ]);
}

function is_authenticated(): bool
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function require_login(): void
{
    if (!is_authenticated()) {
        header('Location: /login');
        exit;
    }
}

function current_user(): array
{
    return $_SESSION['user'] ?? [];
}