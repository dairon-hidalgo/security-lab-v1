<?php

declare(strict_types=1);

function register_login_attempt(
    PDO $pdo,
    string $username,
    bool $wasSuccessful
): void {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $statement = $pdo->prepare(
        'INSERT INTO login_attempts (
            username,
            ip_address,
            user_agent,
            was_successful
        ) VALUES (
            :username,
            :ip_address,
            :user_agent,
            :was_successful
        )'
    );

    $statement->execute([
        'username' => $username !== '' ? $username : '(vacío)',
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'was_successful' => $wasSuccessful,
    ]);
}