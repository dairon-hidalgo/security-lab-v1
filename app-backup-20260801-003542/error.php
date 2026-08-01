<?php

declare(strict_types=1);

$requestedCode = (int) ($_GET['code'] ?? 404);

$errors = [
    403 => [
        'title' => 'Acceso restringido',
        'message' => 'No tienes permisos para acceder a este recurso.',
    ],
    404 => [
        'title' => 'Página no encontrada',
        'message' => 'La dirección solicitada no existe dentro del laboratorio.',
    ],
    500 => [
        'title' => 'Error interno',
        'message' => 'La aplicación no pudo completar la solicitud.',
    ],
];

$code = array_key_exists($requestedCode, $errors)
    ? $requestedCode
    : 404;

$error = $errors[$code];

http_response_code($code);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= $code ?> — <?= htmlspecialchars($error['title']) ?></title>

    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>

<body class="error-page">
<main class="error-card">
    <img
        src="/assets/logo.svg"
        alt="FIIS Security Lab"
        class="error-logo"
    >

    <div class="error-number"><?= $code ?></div>

    <h1><?= htmlspecialchars($error['title']) ?></h1>

    <p><?= htmlspecialchars($error['message']) ?></p>

    <div class="error-actions">
        <a class="primary-button error-button" href="/">
            Ir al inicio
        </a>

        <button
            type="button"
            class="back-button"
            onclick="history.back()"
        >
            Regresar
        </button>
    </div>
</main>
</body>
</html>