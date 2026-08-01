<?php

declare(strict_types=1);

$modules = [
    [
        'number' => '1',
        'title' => 'Fuerza bruta',
        'file' => 'login.php',
        'description' => 'Inicio de sesión sin controles de intentos.'
    ],
    [
        'number' => '2',
        'title' => 'Ejecución de comandos',
        'file' => 'command.php',
        'description' => 'Módulo académico de ejecución controlada.'
    ],
    [
        'number' => '3',
        'title' => 'Inclusión de archivos',
        'file' => 'file-include.php',
        'description' => 'Prueba de inclusión local y remota.'
    ],
    [
        'number' => '4 y 5',
        'title' => 'SQL Injection',
        'file' => 'sqli.php?id=1',
        'checkFile' => 'sqli.php',
        'description' => 'Inyección SQL manual y automatizada.'
    ],
    [
        'number' => '6',
        'title' => 'Blind SQL Injection',
        'file' => 'blind-sqli.php?id=1',
        'checkFile' => 'blind-sqli.php',
        'description' => 'Inyección ciega mediante respuestas booleanas.'
    ],
    [
        'number' => '7',
        'title' => 'File Upload',
        'file' => 'upload.php',
        'description' => 'Pendiente de implementación.'
    ],
    [
        'number' => '8',
        'title' => 'XSS Reflected',
        'file' => 'xss-reflected.php',
        'description' => 'Pendiente de implementación.'
    ],
    [
        'number' => '9',
        'title' => 'XSS Stored',
        'file' => 'xss-stored.php',
        'description' => 'Pendiente de implementación.'
    ],
    [
        'number' => '10',
        'title' => 'XSS DOM',
        'file' => 'xss-dom.php',
        'description' => 'Pendiente de implementación.'
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulos — Service Desk FIIS</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #eef2f7;
            color: #172033;
        }

        header {
            padding: 28px;
            color: white;
            background: #172033;
        }

        main {
            width: min(1100px, 92%);
            margin: 30px auto;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 18px;
        }

        .card {
            padding: 22px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
        }

        .number {
            display: inline-block;
            padding: 6px 10px;
            color: white;
            font-weight: bold;
            background: #172033;
            border-radius: 20px;
        }

        .button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 17px;
            color: white;
            text-decoration: none;
            background: #2356a8;
            border-radius: 6px;
        }

        .pending {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 17px;
            color: #6a4b00;
            background: #fff3cd;
            border-radius: 6px;
        }
    </style>
</head>

<body>
<header>
    <h1>Service Desk FIIS</h1>
    <p>Laboratorio V1 — Módulos de seguridad</p>
</header>

<main>
    <div class="grid">
        <?php foreach ($modules as $module): ?>
            <?php
            $checkFile = $module['checkFile'] ?? $module['file'];
            $checkFile = explode('?', $checkFile)[0];
            $implemented = file_exists(__DIR__ . '/' . $checkFile);
            ?>

            <article class="card">
                <span class="number">Módulo <?= htmlspecialchars($module['number']) ?></span>

                <h2><?= htmlspecialchars($module['title']) ?></h2>

                <p><?= htmlspecialchars($module['description']) ?></p>

                <?php if ($implemented): ?>
                    <a class="button" href="/<?= htmlspecialchars($module['file']) ?>">
                        Abrir módulo
                    </a>
                <?php else: ?>
                    <span class="pending">Pendiente</span>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>