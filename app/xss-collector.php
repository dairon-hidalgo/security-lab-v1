<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

require_login();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
http_response_code(410);

echo json_encode([
    'ok' => false,
    'message' => 'El colector de cookies está deshabilitado en la versión segura.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
