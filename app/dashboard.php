<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

require_login();

$user = current_user();

$userInitials = strtoupper(
    substr((string) $user['full_name'], 0, 1) .
    substr(
        (string) $user['full_name'],
        (int) strpos((string) $user['full_name'], ' ') + 1,
        1
    )
);

$modules = [
    [
        'number' => '01',
        'icon' => 'BF',
        'title' => 'Fuerza bruta',
        'description' => 'Autenticación sin bloqueo temporal, límite de intentos ni segundo factor.',
        'url' => '/login-security.php',
    ],
    [
        'number' => '02',
        'icon' => 'RC',
        'title' => 'Ejecución de comandos',
        'description' => 'Estudio de entradas utilizadas por funciones del sistema operativo.',
        'url' => '/command.php',
    ],
    [
        'number' => '03',
        'icon' => 'FI',
        'title' => 'Inclusión de archivos',
        'description' => 'Evaluación de rutas, archivos locales y controles de inclusión.',
        'url' => '/file-include.php',
    ],
    [
        'number' => '04',
        'icon' => 'SQ',
        'title' => 'SQL Injection',
        'description' => 'Consulta de información mediante parámetros controlados por el usuario.',
        'url' => '/sqli.php',
    ],
    [
        'number' => '05',
        'icon' => 'BL',
        'title' => 'Blind SQL Injection',
        'description' => 'Inferencia de información mediante respuestas verdaderas o falsas.',
        'url' => '/blind-sqli.php',
    ],
    [
        'number' => '06',
        'icon' => 'UP',
        'title' => 'Carga de archivos',
        'description' => 'Carga de evidencias con validaciones deliberadamente incompletas.',
        'url' => '/upload.php',
    ],
    [
        'number' => '07',
        'icon' => 'XR',
        'title' => 'XSS Reflected',
        'description' => 'Entrada recibida y reflejada directamente en la respuesta web.',
        'url' => '/xss-reflected.php',
    ],
    [
        'number' => '08',
        'icon' => 'XS',
        'title' => 'XSS Stored',
        'description' => 'Contenido almacenado y mostrado posteriormente a otros usuarios.',
        'url' => '/xss-stored.php',
    ],
    [
        'number' => '09',
        'icon' => 'XD',
        'title' => 'XSS DOM',
        'description' => 'Manipulación insegura del contenido procesado por el navegador.',
        'url' => '/xss-dom.php',
    ],
    [
        'number' => '10',
        'icon' => 'HC',
        'title' => 'Cabeceras y cookies',
        'description' => 'Inspección de la sesión, cookies y cabeceras HTTP de la aplicación.',
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
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">FI</div>

            <div>
                <strong>Service Desk FIIS</strong>
                <span>Security Lab · V1</span>
            </div>
        </div>

        <div class="sidebar-section-title">Principal</div>

        <nav class="sidebar-nav">
            <a class="sidebar-link active" href="/dashboard.php">
                <span class="sidebar-link-icon">⌂</span>
                Panel principal
            </a>

            <a class="sidebar-link" href="#modules">
                <span class="sidebar-link-icon">▦</span>
                Módulos de prueba
            </a>

            <a class="sidebar-link" href="/security-info.php">
                <span class="sidebar-link-icon">◎</span>
                Información HTTP
            </a>
        </nav>

        <div class="sidebar-section-title">Laboratorio</div>

        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/sqli.php">
                <span class="sidebar-link-icon">DB</span>
                Base de datos
            </a>

            <a class="sidebar-link" href="/upload.php">
                <span class="sidebar-link-icon">UP</span>
                Archivos
            </a>

            <a class="sidebar-link" href="/xss-reflected.php">
                <span class="sidebar-link-icon">JS</span>
                Navegador
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= htmlspecialchars($userInitials) ?>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars((string) $user['full_name']) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars((string) $user['role']) ?>
                    </span>
                </div>
            </div>

            <a class="logout-link" href="/logout.php">
                Cerrar sesión
            </a>
        </div>
    </aside>

    <section class="main-area">
        <header class="top-header">
            <div class="page-title">
                <h1>Panel de control</h1>

                <p>
                    Administración del laboratorio académico de seguridad
                </p>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    V1 vulnerable · localhost
                </div>
            </div>
        </header>

        <main class="content">
            <section class="welcome-banner">
                <div>
                    <h2>
                        Bienvenido,
                        <?= htmlspecialchars((string) $user['full_name']) ?>
                    </h2>

                    <p>
                        El entorno está preparado para desarrollar y documentar
                        pruebas controladas sobre PHP 8.2, Apache y PostgreSQL 16.
                    </p>
                </div>

                <div class="banner-badge">
                    Sesión iniciada:
                    <?= htmlspecialchars(
                        (string) ($_SESSION['login_time'] ?? 'No disponible')
                    ) ?>
                </div>
            </section>

            <section class="stats-grid">
                <article class="stat-card">
                    <div class="stat-icon">10</div>

                    <div class="stat-content">
                        <strong>10</strong>
                        <span>Módulos planificados</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">PHP</div>

                    <div class="stat-content">
                        <strong><?= htmlspecialchars(PHP_VERSION) ?></strong>
                        <span>Versión de PHP</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">DB</div>

                    <div class="stat-content">
                        <strong>16</strong>
                        <span>PostgreSQL</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">ID</div>

                    <div class="stat-content">
                        <strong>
                            <?= htmlspecialchars(
                                substr(session_id(), 0, 8)
                            ) ?>
                        </strong>
                        <span>Identificador de sesión</span>
                    </div>
                </article>
            </section>

            <section id="modules">
                <div class="section-heading">
                    <div>
                        <h2>Módulos del laboratorio</h2>

                        <p>
                            Selecciona el escenario que deseas implementar o revisar.
                        </p>
                    </div>
                </div>

                <div class="module-grid">
                    <?php foreach ($modules as $module): ?>
                        <article class="module-card">
                            <div class="module-card-top">
                                <div class="module-icon">
                                    <?= htmlspecialchars($module['icon']) ?>
                                </div>

                                <div class="module-number">
                                    <?= htmlspecialchars($module['number']) ?>
                                </div>
                            </div>

                            <div class="module-tag">
                                Riesgo de seguridad
                            </div>

                            <h3>
                                <?= htmlspecialchars($module['title']) ?>
                            </h3>

                            <p>
                                <?= htmlspecialchars($module['description']) ?>
                            </p>

                            <a
                                class="module-button"
                                href="<?= htmlspecialchars($module['url']) ?>"
                            >
                                Abrir escenario
                                <span>→</span>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>

        <footer class="footer">
            Service Desk FIIS · Laboratorio local ·
            PHP 8.2 · Apache · PostgreSQL 16
        </footer>
    </section>
</div>
</body>
</html>