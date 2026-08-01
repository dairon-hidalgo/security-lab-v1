<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();

function current_user_initials(array $user): string
{
    $parts = preg_split('/\s+/', trim((string) ($user['full_name'] ?? 'Usuario')));
    $initials = '';

    foreach (array_slice($parts ?: ['U'], 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'U';
}

$userInitials = current_user_initials($user);
$activeUrl = '/sqli.php';
$activeIcon = 'database';
$activeLabel = 'SQL Injection manual';

$id = (string) ($_GET['id'] ?? '1');
$results = [];
$sqlExecuted = '';
$errorMessage = null;

try {
    $pdo = db();
    $sqlExecuted = "SELECT id, username, full_name, role FROM users WHERE id = $id ORDER BY id";
    $results = $pdo->query($sqlExecuted)->fetchAll();
} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection manual</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');
            if (savedTheme) document.documentElement.dataset.theme = savedTheme;
        })();
    </script>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>
<body data-page="module-sqli">
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
                <span class="sidebar-link-icon"><?= icon('home', 18) ?></span>
                Panel principal
            </a>

            <a class="sidebar-link active" href="<?= htmlspecialchars($activeUrl) ?>">
                <span class="sidebar-link-icon"><?= icon($activeIcon, 18) ?></span>
                <?= htmlspecialchars($activeLabel) ?>
            </a>

            <a class="sidebar-link" href="/evidence.php">
                <span class="sidebar-link-icon"><?= icon('activity', 18) ?></span>
                Registrar evidencia
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><?= htmlspecialchars($userInitials) ?></div>
                <div>
                    <strong><?= htmlspecialchars((string) ($user['full_name'] ?? 'Usuario')) ?></strong>
                    <span><?= htmlspecialchars((string) ($user['role'] ?? 'user')) ?></span>
                </div>
            </div>
            <a class="logout-link" href="/logout.php">Cerrar sesión</a>
        </div>
    </aside>

    <section class="main-area">
        <header class="top-header">
            <div class="top-header-left">
                <button type="button" class="icon-button mobile-menu-button" data-sidebar-toggle aria-label="Abrir menú">
                    <?= icon('menu', 20) ?>
                </button>
                <div class="page-title">
                    <h1>SQL Injection manual</h1>
                    <p>Consulta vulnerable de usuarios mediante un parámetro controlado</p>
                </div>
            </div>
            <div class="header-actions">
                <div class="environment-badge"><span class="environment-dot"></span>Riesgo crítico</div>
                <button type="button" class="icon-button" data-theme-toggle aria-label="Cambiar tema">
                    <span data-theme-moon><?= icon('moon', 19) ?></span>
                    <span data-theme-sun hidden><?= icon('sun', 19) ?></span>
                </button>
            </div>
        </header>
        <main class="content">
            <div class="breadcrumb"><a href="/dashboard.php">Panel</a><span>/</span><span>Módulo 04</span></div>
            <section class="module-hero">
                <div class="module-hero-number">MÓDULO 04 · OWASP A03</div>
                <h1>Consulta directa de usuarios</h1>
                <p>El identificador proporcionado se concatena dentro de una consulta SQL sin parametrización.</p>
            </section>
            <div class="warning-box"><strong>Vulnerabilidad intencional:</strong> la aplicación no valida ni parametriza el identificador antes de enviarlo a PostgreSQL.</div>
            <section class="module-layout">
                <article class="panel-card">

                    <div class="section-heading">
                        <div><h2>Consulta manual por ID</h2><p>El valor se inserta directamente en la sentencia SQL.</p></div>
                    </div>

                    <form method="get" action="/sqli.php">
                        <div class="form-group">
                            <label for="id">Identificador del usuario</label>
                            <input class="input-control" id="id" name="id" type="text" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" required>
                        </div>
                        <button class="primary-button" type="submit">Ejecutar consulta <span>→</span></button>
                    </form>

                    <?php if ($sqlExecuted !== ''): ?>
                        <div style="margin-top: 24px;">
                            <h3>Consulta construida</h3>
                            <pre class="terminal-output"><code><?= htmlspecialchars($sqlExecuted, ENT_QUOTES, 'UTF-8') ?></code></pre>
                        </div>
                    <?php endif; ?>

                    <?php if ($errorMessage !== null): ?>
                        <div class="alert alert-error" style="margin-top: 20px;"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <div style="margin-top: 24px;">
                        <h3>Resultados: <?= count($results) ?></h3>
                        <div class="project-module-table-wrapper">
                            <table class="info-table">
                                <thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th></tr></thead>
                                <tbody>
                                <?php if ($results === []): ?>
                                    <tr><td colspan="4">No se encontraron registros.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) $row['id']) ?></td>
                                        <td><?= htmlspecialchars((string) $row['username']) ?></td>
                                        <td><?= htmlspecialchars((string) $row['full_name']) ?></td>
                                        <td><?= htmlspecialchars((string) $row['role']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </article>
                <aside class="module-status-card">

                    <h3>Flujo vulnerable</h3>
                    <div class="status-list">
                        <div class="status-item"><span class="status-circle">1</span>Parámetro recibido por GET</div>
                        <div class="status-item"><span class="status-circle">2</span>Concatenación directa</div>
                        <div class="status-item"><span class="status-circle">3</span>Consulta enviada a PostgreSQL</div>
                        <div class="status-item"><span class="status-circle">4</span>Resultados visibles</div>
                    </div>
                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">
                    <h3>Punto de prueba</h3>
                    <table class="info-table">
                        <tr><th>Método</th><td>GET</td></tr>
                        <tr><th>Parámetro</th><td><code>id</code></td></tr>
                        <tr><th>Ruta</th><td><code>/sqli.php</code></td></tr>
                        <tr><th>Motor</th><td>PostgreSQL 16</td></tr>
                    </table>

                </aside>
            </section>
        </main>
        <footer class="footer">Service Desk FIIS · Laboratorio local y autorizado</footer>
    </section>
</div>
<script src="/assets/app.js"></script>
</body>
</html>
