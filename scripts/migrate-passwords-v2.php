<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '5432';
$name = getenv('DB_NAME') ?: 'security_lab';
$user = getenv('DB_USER') ?: 'labuser';
$password = getenv('DB_PASSWORD') ?: '';

$pdo = new PDO(
    "pgsql:host={$host};port={$port};dbname={$name}",
    $user,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$pdo->beginTransaction();

try {
    $rows = $pdo
        ->query(
            'SELECT id, username, password
             FROM users
             ORDER BY id
             FOR UPDATE'
        )
        ->fetchAll();

    $update = $pdo->prepare(
        'UPDATE users
         SET password = :password
         WHERE id = :id'
    );

    $converted = 0;
    $alreadyProtected = 0;

    foreach ($rows as $row) {
        $currentPassword = (string) $row['password'];
        $information = password_get_info($currentPassword);

        if (($information['algoName'] ?? 'unknown') !== 'unknown') {
            $alreadyProtected++;
            continue;
        }

        $hash = password_hash(
            $currentPassword,
            PASSWORD_DEFAULT
        );

        if ($hash === false) {
            throw new RuntimeException(
                'No se pudo proteger la cuenta '
                . (string) $row['username']
            );
        }

        $update->execute([
            'password' => $hash,
            'id' => (int) $row['id'],
        ]);

        $converted++;
    }

    $pdo->commit();

    echo "Contraseñas convertidas: {$converted}" . PHP_EOL;
    echo "Contraseñas ya protegidas: {$alreadyProtected}" . PHP_EOL;
} catch (Throwable $exception) {
    $pdo->rollBack();

    fwrite(
        STDERR,
        $exception->getMessage() . PHP_EOL
    );

    exit(1);
}