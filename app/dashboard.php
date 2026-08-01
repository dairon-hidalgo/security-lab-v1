<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

require_login();

$user = current_user();

$modules = [
    [
        'number' => '01',
        'title' => 'Fuerza bruta',
        'description' => 'Autenticación sin límite de intentos, bloqueo temporal ni segundo factor.',
        'url' => '/login-security.php',
    ],
    [
        'number' => '02',
        'title' => 'Ejecución de comandos',
        'description' => 'Módulo académico para estudiar la manipulación insegura de entradas del sistema.',
        'url' => '/command.php',
    ],
    [
        'number' => '03',
        'title' => 'Inclusión de archivos',
        'description' => 'Práctica de rutas, inclusión local y controles de acceso a archivos.',
        'url' => '/file-include.php',
    ],
    [
        'number' => '04',
        'title' => 'SQL Injection',
        'description' => 'Consulta de usuarios preparada para pruebas manuales y automatizadas.',
        'url' => '/sqli.php',
    ],
    [
        'number' => '05',
        'title' => 'Blind SQL Injection',
        'description' => 'Consultas con respuestas booleanas para estudiar inferencia de información.',
        'url' => '/blind-sqli.php',
    ],
    [
        'number' => '06',
        'title' => 'Carga de archivos',
        'description' => 'Carga de evidencias con validaciones deliberadamente incompletas.',
        'url' => '/upload.php',
    ],
    [
        'number' => '07',
        'title' => 'XSS Reflected',
        'description' => 'Entrada reflejada en la respuesta generada por la aplicación.',
        'url' => '/xss-reflected.php',
    ],
    [
        'number' => '08',
        'title' => 'XSS Stored',
        'description' => 'Comentarios almacenados y mostrados posteriormente a los usuarios.',
        'url' => '/xss-stored.php',
    ],
    [
        'number' => '09',
        'title' => 'XSS DOM',
        'description' => 'Manipulación del contenido del navegador mediante JavaScript.',
        'url' => '/xss-dom.php',
    ],
    [
        'number' => '10',
        'title' => 'Cabeceras y cookies',
        'description' => 'Revisión de sesión, cookies y cabeceras HTTP de la versión vulnerable.',
        'url' => '/security-info.php',
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal — Service Desk FIIS</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body>
<header class="topbar">
    <div>
        <h1>Service Desk FIIS — V1</h1>
        <p>Panel del laboratorio académico de seguridad</p>
    </div>

    <div>
        <?= htmlspecialchars((string) $user['full_name']) ?>
        ·
        <a href="/logout.php">Cerrar sesión</a>
    </div>
</header>

<main class="container">
    <section class="warning">
        <strong>Entorno vulnerable:</strong>
        utiliza estos módulos exclusivamente en el laboratorio local
        <code>http://localhost:8081</code>.
    </section>

    <section class="card">
        <h2>Sesión actual</h2>

        <table>
            <tr>
                <th>Usuario</th>
                <td><?= htmlspecialchars((string) $user['username']) ?></td>
            </tr>

            <tr>
                <th>Nombre</th>
                <td><?= htmlspecialchars((string) $user['full_name']) ?></td>
            </tr>

            <tr>
                <th>Rol</th>
                <td><?= htmlspecialchars((string) $user['role']) ?></td>
            </tr>

            <tr>
                <th>Cookie de sesión</th>
                <td>
                    <code><?= htmlspecialchars(session_name()) ?></code>
                </td>
            </tr>

            <tr>
                <th>Identificador</th>
                <td>
                    <code><?= htmlspecialchars(session_id()) ?></code>
                </td>
            </tr>
        </table>
    </section>

    <h2>Módulos disponibles</h2>

    <section class="grid">
        <?php foreach ($modules as $module): ?>
            <article class="module-card">
                <span class="badge">
                    Módulo <?= htmlspecialchars($module['number']) ?>
                </span>

                <h3><?= htmlspecialchars($module['title']) ?></h3>

                <p><?= htmlspecialchars($module['description']) ?></p>

                <a class="button" href="<?= htmlspecialchars($module['url']) ?>">
                    Abrir módulo
                </a>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<footer>
    Laboratorio local · PHP 8.2 · Apache · PostgreSQL 16
</footer>
</body>
</html>