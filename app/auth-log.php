<?php

declare(strict_types=1);

const MAX_LOGIN_FAILURES = 5;
const LOGIN_FAILURE_WINDOW_MINUTES = 15;

function client_ip_address(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function register_login_attempt(
    PDO $pdo,
    string $username,
    bool $wasSuccessful
): void {
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
        client_ip_address(),
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':user_agent',
        (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':was_successful',
        $wasSuccessful,
        PDO::PARAM_BOOL
    );

    $statement->execute();
}

function recent_failed_login_count(
    PDO $pdo,
    string $username
): int {
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM login_attempts
         WHERE was_successful = FALSE
           AND attempted_at >= CURRENT_TIMESTAMP - INTERVAL '15 minutes'
           AND (
               username = :username
               OR ip_address = :ip_address
           )
           AND attempted_at > COALESCE(
               (
                   SELECT MAX(success.attempted_at)
                   FROM login_attempts AS success
                   WHERE success.was_successful = TRUE
                     AND success.username = :success_username
               ),
               TIMESTAMP '1970-01-01 00:00:00'
           )"
    );

    $statement->execute([
        'username' => $username,
        'ip_address' => client_ip_address(),
        'success_username' => $username,
    ]);

    return (int) $statement->fetchColumn();
}

function login_is_temporarily_locked(
    PDO $pdo,
    string $username
): bool {
    return recent_failed_login_count($pdo, $username)
        >= MAX_LOGIN_FAILURES;
}