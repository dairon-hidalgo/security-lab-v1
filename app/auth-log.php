<?php

declare(strict_types=1);

function register_login_attempt(
    PDO $pdo,
    string $username,
    bool $wasSuccessful
): void {
    $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

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

    $statement->bindValue(
        ':username',
        $username !== '' ? $username : '(vacío)',
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':ip_address',
        $ipAddress,
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':user_agent',
        $userAgent,
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':was_successful',
        $wasSuccessful,
        PDO::PARAM_BOOL
    );

    $statement->execute();
}