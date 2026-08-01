<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$pdo = db();

function database_boolean(mixed $value): bool
{
    return $value === true
        || $value === 1
        || $value === '1'
        || $value === 't'
        || $value === 'true';
}

function short_user_agent(string $value, int $limit = 80): string
{
    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, $limit - 3) . '...';
}

$nameParts = preg_split(
    '/\s+/',
    trim((string) ($user['full_name'] ?? 'Usuario'))
);

$userInitials = '';

foreach (array_slice($nameParts ?: ['U'], 0, 2) as $part) {
    $userInitials .= strtoupper(substr($part, 0, 1));
}

$summary = $pdo->query(
    'SELECT
        COUNT(*) AS total,
        COUNT(*) FILTER (
            WHERE was_successful = TRUE
        ) AS successful,
        COUNT(*) FILTER (
            WHERE was_successful = FALSE
        ) AS failed,
        COUNT(DISTINCT username) AS unique_users
     FROM login_attempts'
)->fetch();

$recentAttempts = $pdo->query(
    'SELECT
        id,
        username,
        ip_address,
        user_agent,
        was_successful,
        attempted_at
     FROM login_attempts
     ORDER BY attempted_at DESC
     LIMIT 30'
)->fetchAll();

$topUsers = $pdo->query(
    'SELECT
        username,
        COUNT(*) AS attempts,
        COUNT(*) FILTER (
            WHERE was_successful = FALSE
        ) AS failed_attempts
     FROM login_attempts
     GROUP BY username
     ORDER BY attempts DESC, username ASC
     LIMIT 8'
)->fetchAll();

$total = (int) ($summary['total'] ?? 0);
$successful = (int) ($summary['successful'] ?? 0);
$failed = (int) ($summary['failed'] ?? 0);
$uniqueUsers = (int) ($summary['unique_users'] ?? 0);

$failureRate = $total > 0
    ? round(($failed / $total) * 100, 1)
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Módulo 01 — Autenticación</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');

            if (savedTheme) {
                document.documentElement.dataset.theme = savedTheme;
            }
        })();
    </script>

    <link
        rel="icon"
        href="/assets/favicon.svg"
        type="image/svg+xml"
    >

    <link rel="stylesheet" href="/styles.css">
</head>

<body data-page="module-authentication">
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
                <span class="sidebar-link-icon">
                    <?= icon('home', 18) ?>
                </span>

                Panel principal
            </a>

            <a class="sidebar-link active" href="/login-security.php">
                <span class="sidebar-link-icon">
                    <?= icon('lock', 18) ?>
                </span>

                Fuerza bruta
            </a>

            <a class="sidebar-link" href="/evidence.php">
                <span class="sidebar-link-icon">
                    <?= icon('activity', 18) ?>
                </span>

                Registrar evidencia
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
                    <h1>Autenticación y fuerza bruta</h1>

                    <p>
                        Seguimiento de intentos de acceso registrados
                    </p>
                </div>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    Riesgo alto
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
            <div class="breadcrumb">
                <a href="/dashboard.php">Panel</a>
                <span>/</span>
                <span>Módulo 01</span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">
                    MÓDULO 01 · OWASP A07
                </div>

                <h1>Autenticación sin límite de intentos</h1>

                <p>
                    El inicio de sesión permite realizar intentos sucesivos
                    sin bloqueo temporal, CAPTCHA, MFA ni límite por usuario
                    o dirección IP.
                </p>
            </section>

            <div class="warning-box">
                <strong>Debilidad intencional:</strong>
                cada intento queda registrado, pero la aplicación no impide
                que una misma cuenta sea probada repetidamente.
            </div>

            <section class="stats-grid">
                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('activity', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong><?= $total ?></strong>
                        <span>Intentos totales</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('check', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong><?= $successful ?></strong>
                        <span>Accesos correctos</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('lock', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong><?= $failed ?></strong>
                        <span>Intentos fallidos</span>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon">
                        <?= icon('eye', 21) ?>
                    </div>

                    <div class="stat-content">
                        <strong><?= $failureRate ?>%</strong>
                        <span>Tasa de fallos</span>
                    </div>
                </article>
            </section>

            <section class="project-grid">
                <article class="panel-card">
                    <div class="section-heading">
                        <div>
                            <h2>Últimos intentos</h2>

                            <p>
                                Registros más recientes del formulario de acceso.
                            </p>
                        </div>

                        <a class="back-button" href="/login-security.php">
                            Actualizar
                        </a>
                    </div>

                    <div class="project-module-table-wrapper">
                        <table class="info-table">
                            <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>IP</th>
                                <th>Resultado</th>
                                <th>Cliente</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php if ($recentAttempts === []): ?>
                                <tr>
                                    <td colspan="5">
                                        Todavía no hay intentos registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($recentAttempts as $attempt): ?>
                                <?php
                                $wasSuccessful = database_boolean(
                                    $attempt['was_successful']
                                );
                                ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $attempt['attempted_at']
                                        ) ?>
                                    </td>

                                    <td>
                                        <code>
                                            <?= htmlspecialchars(
                                                (string) $attempt['username']
                                            ) ?>
                                        </code>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $attempt['ip_address']
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="<?= $wasSuccessful
                                            ? 'status-badge status-implemented'
                                            : 'status-badge risk-critical'
                                        ?>">
                                            <?= $wasSuccessful
                                                ? 'Correcto'
                                                : 'Fallido'
                                            ?>
                                        </span>
                                    </td>

                                    <td title="<?= htmlspecialchars(
                                        (string) $attempt['user_agent']
                                    ) ?>">
                                        <?= htmlspecialchars(
                                            short_user_agent(
                                                (string) $attempt['user_agent']
                                            )
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <aside class="module-status-card">
                    <h3>Usuarios más probados</h3>

                    <?php if ($topUsers === []): ?>
                        <p>No existen registros todavía.</p>
                    <?php endif; ?>

                    <div class="status-list">
                        <?php foreach ($topUsers as $topUser): ?>
                            <div class="status-item">
                                <span class="status-circle">
                                    <?= (int) $topUser['attempts'] ?>
                                </span>

                                <div>
                                    <strong>
                                        <?= htmlspecialchars(
                                            (string) $topUser['username']
                                        ) ?>
                                    </strong>

                                    <div>
                                        <?= (int) $topUser['failed_attempts'] ?>
                                        fallidos
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Resumen técnico</h3>

                    <div class="status-list">
                        <div class="status-item">
                            <span class="status-circle">
                                <?= $uniqueUsers ?>
                            </span>

                            Usuarios distintos
                        </div>

                        <div class="status-item">
                            <span class="status-circle">∞</span>
                            Sin límite de intentos
                        </div>

                        <div class="status-item">
                            <span class="status-circle">0</span>
                            Sin bloqueo temporal
                        </div>

                        <div class="status-item">
                            <span class="status-circle">1</span>
                            Registro de auditoría activo
                        </div>
                    </div>
                </aside>
            </section>
        </main>

        <footer class="footer">
            FIIS Security Lab · Módulo 01 · Entorno local
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
</body>
</html>