<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../icons.php';

function app_config(): array
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/../config/nav.php';
    }

    return $config;
}

function current_user_initials(array $user): string
{
    $parts = preg_split(
        '/\s+/',
        trim((string) ($user['full_name'] ?? 'Usuario'))
    );

    $initials = '';

    foreach (array_slice($parts ?: ['U'], 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'U';
}

function short_text(string $value, int $limit = 85): string
{
    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, $limit - 3) . '...';
}

function pg_boolean(mixed $value): bool
{
    return $value === true
        || $value === 1
        || $value === '1'
        || $value === 't'
        || $value === 'true';
}

function environment_label(string $label = 'Entorno de soporte'): string
{
    return $label;
}
