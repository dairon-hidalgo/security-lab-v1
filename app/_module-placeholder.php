<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

require_login();

$user = current_user();

$moduleTitle = $moduleTitle ?? 'Módulo del laboratorio';
$moduleDescription = $moduleDescription ?? 'Módulo pendiente de implementación.';
$moduleNumber = $moduleNumber ?? '00';

$userInitial = strtoupper(
    substr((string) ($user['full_name'] ?? 'U'), 0, 1)
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($moduleTitle) ?> — Service Desk FIIS
    </title>

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

        <div class="sidebar-section-title">Navegación</div>

        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/dashboard.php">
                <span class="sidebar-link-icon">⌂</span>
                Panel principal
            </a>

            <a class="sidebar-link active" href="#">
                <span class="sidebar-link-icon">
                    <?= htmlspecialchars($moduleNumber) ?>
                </span>

                <?= htmlspecialchars($moduleTitle) ?>
            </a>

            <a class="sidebar-link" href="/security-info.php">
                <span class="sidebar-link-icon">◎</span>
                Cabeceras y cookies
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= htmlspecialchars($userInitial) ?>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars(
                            (string) ($user['full_name'] ?? 'Usuario')
                        ) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            (string) ($user['role'] ?? 'user')
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
            <div class="page-title">
                <h1><?= htmlspecialchars($moduleTitle) ?></h1>

                <p>
                    Escenario académico de la versión vulnerable
                </p>
            </div>

            <div class="environment-badge">
                <span class="environment-dot"></span>
                Entorno controlado
            </div>
        </header>

        <main class="content">
            <div class="breadcrumb">
                <a href="/dashboard.php">Panel</a>
                <span>/</span>
                <span>Módulo <?= htmlspecialchars($moduleNumber) ?></span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">
                    ESCENARIO <?= htmlspecialchars($moduleNumber) ?>
                </div>

                <h1><?= htmlspecialchars($moduleTitle) ?></h1>

                <p><?= htmlspecialchars($moduleDescription) ?></p>
            </section>

            <div class="warning-box">
                <strong>Uso académico:</strong>
                este escenario debe utilizarse exclusivamente sobre
                <code>http://localhost:8081</code>.
            </div>

            <section class="module-layout">
                <article class="panel-card">
                    <h2>Área de trabajo</h2>

                    <p>
                        La interfaz visual y la integración con el sistema de
                        sesiones ya están listas. En la siguiente fase se
                        incorporará la funcionalidad específica de este módulo.
                    </p>

                    <table class="info-table">
                        <tr>
                            <th>Número del módulo</th>
                            <td><?= htmlspecialchars($moduleNumber) ?></td>
                        </tr>

                        <tr>
                            <th>Nombre</th>
                            <td><?= htmlspecialchars($moduleTitle) ?></td>
                        </tr>

                        <tr>
                            <th>Estado</th>
                            <td>Preparado para implementación</td>
                        </tr>

                        <tr>
                            <th>Entorno</th>
                            <td>Docker Desktop sobre Windows 11</td>
                        </tr>

                        <tr>
                            <th>Sesión</th>
                            <td>
                                <code>
                                    <?= htmlspecialchars(session_name()) ?>
                                </code>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 22px;">
                        <a class="back-button" href="/dashboard.php">
                            ← Regresar al panel
                        </a>
                    </div>
                </article>

                <aside class="module-status-card">
                    <h3>Estado del escenario</h3>

                    <div class="status-list">
                        <div class="status-item">
                            <span class="status-circle">1</span>
                            Interfaz creada
                        </div>

                        <div class="status-item">
                            <span class="status-circle">2</span>
                            Sesión integrada
                        </div>

                        <div class="status-item">
                            <span class="status-circle">3</span>
                            Docker operativo
                        </div>

                        <div class="status-item">
                            <span class="status-circle">4</span>
                            Funcionalidad pendiente
                        </div>
                    </div>
                </aside>
            </section>
        </main>

        <footer class="footer">
            Service Desk FIIS · Laboratorio académico de seguridad
        </footer>
    </section>
</div>
</body>
</html>