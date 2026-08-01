<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

$user = current_user();
$pdo = db();

$rawCookie = trim((string) ($_POST['cookie'] ?? ''));
$pageUrl = substr(trim((string) ($_POST['page'] ?? '')), 0, 1000);

/*
 * El punto de captura acepta exclusivamente la cookie ficticia LAB_XSS_DEMO.
 */
if (!preg_match('/^LAB_XSS_DEMO=([A-Za-z0-9._-]{1,200})$/', $rawCookie, $matches)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'Solo se acepta la cookie ficticia LAB_XSS_DEMO',
    ]);
    exit;
}

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS xss_cookie_captures (
        id SERIAL PRIMARY KEY,
        captured_by_user_id INTEGER REFERENCES users(id),
        cookie_name VARCHAR(100) NOT NULL,
        cookie_value TEXT NOT NULL,
        page_url TEXT,
        ip_address VARCHAR(64),
        captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

$statement = $pdo->prepare(
    'INSERT INTO xss_cookie_captures (
        captured_by_user_id,
        cookie_name,
        cookie_value,
        page_url,
        ip_address
    ) VALUES (
        :captured_by_user_id,
        :cookie_name,
        :cookie_value,
        :page_url,
        :ip_address
    )'
);

$statement->execute([
    ':captured_by_user_id' => (int) ($user['id'] ?? 0),
    ':cookie_name' => 'LAB_XSS_DEMO',
    ':cookie_value' => (string) $matches[1],
    ':page_url' => $pageUrl,
    ':ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
]);

echo json_encode([
    'ok' => true,
    'message' => 'Cookie ficticia registrada localmente',
]);
