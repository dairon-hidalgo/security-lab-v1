<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

require_login();

$storedName = (string) ($_GET['file'] ?? '');

if (
    preg_match(
        '/\A[a-f0-9]{32}\.(?:pdf|png|jpg|txt)\z/',
        $storedName
    ) !== 1
) {
    http_response_code(400);
    exit('Solicitud de descarga no válida.');
}

$pdo = db();

$statement = $pdo->prepare(
    'SELECT original_name, reported_mime
     FROM upload_attempts
     WHERE stored_name = :stored_name
       AND was_successful = TRUE
     ORDER BY uploaded_at DESC
     LIMIT 1'
);

$statement->execute([
    'stored_name' => $storedName,
]);

$fileRecord = $statement->fetch();

if ($fileRecord === false) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

$storageRoot = getenv('UPLOAD_STORAGE_PATH')
    ?: '/var/www/storage/uploads';

$realRoot = realpath($storageRoot);
$realFile = realpath(
    $storageRoot . DIRECTORY_SEPARATOR . $storedName
);

if (
    $realRoot === false
    || $realFile === false
    || !str_starts_with(
        $realFile,
        $realRoot . DIRECTORY_SEPARATOR
    )
    || !is_file($realFile)
) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

$originalName = basename(
    (string) $fileRecord['original_name']
);

$finfo = new finfo(FILEINFO_MIME_TYPE);
$detectedMime = (string) $finfo->file($realFile);

header('Content-Type: ' . $detectedMime);
header('Content-Length: ' . (string) filesize($realFile));
header('Content-Disposition: attachment; filename="' .
    rawurlencode($originalName) . '"; filename*=UTF-8\'\'' .
    rawurlencode($originalName));
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'none\'');
header('Cache-Control: private, no-store');

readfile($realFile);
exit;
