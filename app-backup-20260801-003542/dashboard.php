<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();

$nameParts = preg_split(
    '/\s+/',
    trim((string) ($user['full_name'] ?? 'Usuario'))
);

$userInitials = '';

foreach (array_slice($nameParts ?: ['U'], 0, 2) as $part) {
    $userInitials .= strtoupper(substr($part, 0, 1));
}

$modules = [
    [
        'number' => '01',
        'icon' => 'lock',
        'title' => 'Fuerza bruta',
        'description' => 'Autenticación sin bloqueo temporal, límite de intentos ni segundo factor.',
        'risk' => 'Alto',
        'risk_class' => 'risk-high',
        'status' => 'Implementado',
        'status_class' => 'status-implemented',
        'url' => '/login-security.php',
    ],
    [
        'number' => '02',
        'icon' => 'terminal',
        'title' => 'Ejecución de comandos',
        'description' => 'Estudio de entradas utilizadas por funciones del sistema operativo.',
        'risk' => 'Crítico',
        'risk_class' => 'risk-critical',
        'status' => 'Implementado',
        'status_class' => 'status-implemented',
        'url' => '/command.php',
    ],
    [
        'number' => '03',
        'icon' => 'folder',
        'title' => 'Inclusión de archivos',
        'description' => 'Evaluación de rutas, archivos locales y controles de inclusión.',
        'risk' => 'Alto',
        'risk_class' => 'risk-high',
        'status' => 'Implementado',
        'status_class' => 'status-implemented',
        'url' => '/file-include.php',
    ],
    [
        'number' => '04',
        'icon' => 'database',
        'title' => 'SQL Injection manual',
        'description' => 'Consulta vulnerable mediante un parámetro ID concatenado directamente.',
        'risk' => 'Crítico',
        'risk_class' => 'risk-critical',
        'status' => 'Implementado',
        'status_class' => 'status-implemented',
        'url' => '/sqli.php',
    ],
    [
        'number' => '05',
        'icon' => 'activity',
        'title' => 'SQL Injection automatizada',
        'description' => 'Punto de prueba autenticado preparado para evaluación automatizada.',
        'risk' => 'Crítico',
        'risk_class' => 'risk-critical',
        'status' => 'Implementado',
        'status_class' => 'status-implemented',
        'url' => '/sqli-automated.php',
    ],
    [
        'number' => '06',
        'icon' => 'eye',
        'title' => 'Blind SQL Injection',
        'description' => 'Inferencia mediante respuestas booleanas sin mostrar los registros.',
        'risk' => 'Crítico',
        'risk_class' => 'risk-critical',
        'status' => 'Implementado',
        'status_class' => 'status-implemented',
        'url' => '/blind-sqli.php',
    ],
    [
        'number' => '07',
        'icon' => 'upload',
        'title' => 'Carga de archivos',
        'description' => 'Carga de evidencias con validaciones deliberadamente incompletas.',
        'risk' => 'Crítico',
        'risk_class' => 'risk-critical',
        'status' => 'Pendiente',
        'status_class' => 'status-pending',
        'url' => '/upload.php',
    ],
    [
        'number' => '08',
        'icon' => 'code',
        'title' => 'XSS Reflected',
        'description' => 'Entrada recibida y reflejada directamente en la respuesta web.',
        'risk' => 'Medio',
        'risk_class' => 'risk-medium',
        'status' => 'Pendiente',
        'status_class' => 'status-pending',
        'url' => '/xss-reflected.php',
    ],
    [
        'number' => '09',
        'icon' => 'database',
        'title' => 'XSS Stored',
        'description' => 'Contenido almacenado y mostrado posteriormente a otros usuarios.',
        'risk' => 'Alto',
        'risk_class' => 'risk-high',
        'status' => 'Pendiente',
        'status_class' => 'status-pending',
        'url' => '/xss-stored.php',
    ],
    [
        'number' => '10',
        'icon' => 'code',
        'title' => 'XSS DOM',
        'description' => 'Manipulación insegura del contenido procesado por el navegador.',
        'risk' => 'Medio',
        'risk_class' => 'risk-medium',
        'status' => 'Pendiente',
        'status_class' => 'status-pending',
        'url' => '/xss-dom.php',
    ],
];

$statusTotals = [
    'Pendiente' => 0,
    'En desarrollo' => 0,
    'Implementado' => 0,
    'Probado' => 0,
];

$statusWeights = [
    'Pendiente' => 0,
    'En desarrollo' => 25,
    'Implementado' => 70,
    'Probado' => 100,
];

$totalProgress = 0;

foreach ($modules as $module) {
    $statusTotals[$module['status']]++;
    $totalProgress += $statusWeights[$module['status']];
}

$progress = (int) round($totalProgress / count($modules));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Panel principal — Service Desk FIIS</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem("securityLabTheme");

            if (savedTheme) {
                document.documentElement.dataset.theme = savedTheme;
            }
        })();
    </script>

    <link rel="stylesheet" href="/styles.css">
</head>

<body data-page="dashboard">
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
                <span class="sidebar-link-icon">
                    <?= icon('home', 18) ?>
                </span>

                Panel principal
            </a>

            <a class="sidebar-link" href="#modules">
                <span class="sidebar-link-icon">
                    <?= icon('shield', 18) ?>
                </span>

                Módulos de prueba
            </a>

            <a class="sidebar-link" href="/security-info.php">
                <span class="sidebar-link-icon">
                    <?= icon('cookie', 18) ?>
                </span>

                Cabeceras y cookies
            </a>
        </nav>

        <div class="sidebar-section-title">Acceso rápido</div>

        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/sqli.php">
                <span class="sidebar-link-icon">
                    <?= icon('database', 18) ?>
                </span>

                Base de datos
            </a>

            <a class="sidebar-link" href="/upload.php">
                <span class="sidebar-link-icon">
                    <?= icon('upload', 18) ?>
                </span>

                Archivos
            </a>

            <a class="sidebar-link" href="/xss-reflected.php">
                <span class="sidebar-link-icon">
                    <?= icon('code', 18) ?>
                </span>

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
                        <?= htmlspecialchars(
                            (string) $user['full_name']
                        ) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            (string) $user['role']
                        ) ?>
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
            <div class="top-header-left">
                <button
                    type="button"
                    class="icon-button mobile-menu-button"
                    data-sidebar-toggle
                    aria-label="Abrir menú"
                >
                    <?= icon('menu', 20) ?>
                </button>

                <div class="page-title">
                    <h1>Panel de control</h1>

                    <p>
                        Administración del laboratorio académico
                    </p>
                </div>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    V1 vulnerable · localhost
                </div>

                <button
                    type="button"
                    class="icon-button"
                    data-theme-toggle
                    aria-label="Cambiar tema"
                >
                    <span data-theme-moon>
                        <?= icon('moon', 19) ?>
                    </span>

                    <span data-theme-sun hidden>
                        <?= icon('sun', 19) ?>
                    </span>
                </button>
            </div>
        </header>

        <main class="content">
            <section class="welcome-banner">
                <div>
                    <h2>
                        Bienvenido,
                        <?= htmlspecialchars(
                            (string) $user['full_name']
                        ) ?>
                    </h2>

                    <p>
                        El entorno está preparado para desarrollar,
                        comprobar y documentar los escenarios de seguridad
                        de la aplicación vulnerable.
                    </p>
                </div>

                <div class="banner-badge">
                    Sesión:
                    <?= htmlspecialchars(
                        (string) ($_SESSION['login_time'] ?? 'No disponible')
                    ) ?>
                </div>
            </section>

            <section class="stats-grid">
                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('shield', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong><?= count($modules) ?></strong>
                        <span>Módulos planificados</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('activity', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong>
                            <?= $statusTotals['En desarrollo'] ?>
                        </strong>

                        <span>En desarrollo</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('check', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong>
                            <?= $statusTotals['Implementado'] ?>
                        </strong>

                        <span>Implementados</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('clock', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong>
                            <?= $statusTotals['Pendiente'] ?>
                        </strong>

                        <span>Pendientes</span>
                    </div>
                </article>
            </section>

            <section class="progress-card">
                <div class="progress-header">
                    <div>
                        <h3>Progreso general del proyecto</h3>

                        <p>
                            Calculado según el estado actual de cada módulo.
                        </p>
                    </div>

                    <div class="progress-percentage">
                        <?= $progress ?>%
                    </div>
                </div>

                <div
                    class="progress-track"
                    role="progressbar"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="<?= $progress ?>"
                >
                    <div
                        class="progress-value"
                        style="width: <?= $progress ?>%;"
                    ></div>
                </div>

                <div class="progress-legend">
                    <span>
                        <i class="legend-dot dot-pending"></i>
                        <?= $statusTotals['Pendiente'] ?> pendientes
                    </span>

                    <span>
                        <i class="legend-dot dot-development"></i>
                        <?= $statusTotals['En desarrollo'] ?> en desarrollo
                    </span>

                    <span>
                        <i class="legend-dot dot-implemented"></i>
                        <?= $statusTotals['Implementado'] ?> implementados
                    </span>

                    <span>
                        <i class="legend-dot dot-tested"></i>
                        <?= $statusTotals['Probado'] ?> probados
                    </span>
                </div>
            </section>

            <section id="modules">
                <div class="section-heading">
                    <div>
                        <h2>Módulos del laboratorio</h2>

                        <p>
                            Selecciona un escenario para iniciar su
                            implementación o revisión.
                        </p>
                    </div>
                </div>

                <div class="module-grid">
                    <?php foreach ($modules as $module): ?>
                        <article class="module-card">
                            <div class="module-card-top">
                                <div class="module-icon">
                                    <?= icon($module['icon'], 22) ?>
                                </div>

                                <div class="module-number">
                                    <?= htmlspecialchars(
                                        $module['number']
                                    ) ?>
                                </div>
                            </div>

                            <div class="module-badges">
                                <span
                                    class="risk-badge <?= htmlspecialchars(
                                        $module['risk_class']
                                    ) ?>"
                                >
                                    <?= htmlspecialchars($module['risk']) ?>
                                </span>

                                <span
                                    class="status-badge <?= htmlspecialchars(
                                        $module['status_class']
                                    ) ?>"
                                >
                                    <?= htmlspecialchars(
                                        $module['status']
                                    ) ?>
                                </span>
                            </div>

                            <h3>
                                <?= htmlspecialchars($module['title']) ?>
                            </h3>

                            <p>
                                <?= htmlspecialchars(
                                    $module['description']
                                ) ?>
                            </p>

                            <a
                                class="module-button"
                                href="<?= htmlspecialchars(
                                    $module['url']
                                ) ?>"
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

<script src="/assets/app.js"></script>
</body>
</html>