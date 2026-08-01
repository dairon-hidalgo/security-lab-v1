<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

require_login();

$moduleTitle = $moduleTitle ?? 'Módulo del laboratorio';
$moduleDescription = $moduleDescription ?? 'Módulo pendiente de implementación.';
$moduleNumber = $moduleNumber ?? '00';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($moduleTitle) ?> — Service Desk FIIS</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body>
<header class="topbar">
    <div>
        <h1><?= htmlspecialchars($moduleTitle) ?></h1>
        <p>Service Desk FIIS — Laboratorio V1</p>
    </div>

    <a href="/dashboard.php">Volver al panel</a>
</header>

<main class="container">
    <section class="warning">
        <strong>Módulo <?= htmlspecialchars($moduleNumber) ?>:</strong>
        entorno académico local y deliberadamente vulnerable.
    </section>

    <section class="card">
        <span class="badge">En preparación</span>

        <h2><?= htmlspecialchars($moduleTitle) ?></h2>

        <p><?= htmlspecialchars($moduleDescription) ?></p>

        <p>
            Este espacio ya está integrado con la sesión del laboratorio.
            Su funcionalidad específica será implementada en la siguiente fase.
        </p>

        <a class="button button-secondary" href="/dashboard.php">
            Regresar
        </a>
    </section>
</main>
</body>
</html>