<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

header('Content-Type: application/json; charset=UTF-8');

if (!is_authenticated()) {
    http_response_code(401);

    echo json_encode([
        'ok' => false,
        'message' => 'La sesión no está autenticada.',
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'ok' => false,
        'message' => 'Método no permitido.',
    ]);

    exit;
}

$payload = json_decode(
    (string) file_get_contents('php://input'),
    true
);

if (!is_array($payload)) {
    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'message' => 'El cuerpo JSON no es válido.',
    ]);

    exit;
}

$cookie = trim((string) ($payload['cookie'] ?? ''));
$sourceHash = substr((string) ($payload['sourceHash'] ?? ''), 0, 3000);
$pageUrl = substr((string) ($payload['pageUrl'] ?? ''), 0, 1500);

/*
 * El colector acepta exclusivamente la cookie ficticia del laboratorio.
 * No almacena SECURITYLABSESSID ni cualquier otro valor de document.cookie.
 */
if (
    preg_match(
        '/^LAB_XSS_DEMO=([A-Za-z0-9._-]{1,180})$/',
        $cookie,
        $matches
    ) !== 1
) {
    http_response_code(422);

    echo json_encode([
        'ok' => false,
        'message' => 'Solo se admite la cookie ficticia LAB_XSS_DEMO.',
    ]);

    exit;
}

$user = current_user();
$pdo = db();

try {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS xss_dom_captures (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
            cookie_name VARCHAR(80) NOT NULL,
            cookie_value VARCHAR(255) NOT NULL,
            source_hash TEXT NULL,
            page_url TEXT NULL,
            ip_address VARCHAR(64) NULL,
            user_agent TEXT NULL,
            captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $statement = $pdo->prepare(
        'INSERT INTO xss_dom_captures (
            user_id,
            cookie_name,
            cookie_value,
            source_hash,
            page_url,
            ip_address,
            user_agent
        ) VALUES (
            :user_id,
            :cookie_name,
            :cookie_value,
            :source_hash,
            :page_url,
            :ip_address,
            :user_agent
        )
        RETURNING id'
    );

    $statement->execute([
        'user_id' => (int) ($user['id'] ?? 0),
        'cookie_name' => 'LAB_XSS_DEMO',
        'cookie_value' => $matches[1],
        'source_hash' => $sourceHash,
        'page_url' => $pageUrl,
        'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
    ]);

    echo json_encode([
        'ok' => true,
        'captureId' => (int) $statement->fetchColumn(),
    ]);
} catch (Throwable $exception) {
    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'message' => 'No fue posible registrar la evidencia local.',
    ]);
}
