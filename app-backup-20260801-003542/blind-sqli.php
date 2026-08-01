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
$activeUrl = '/blind-sqli.php';
$activeIcon = 'eye';
$activeLabel = 'Blind SQL Injection';

$id = (string) ($_GET['id'] ?? '1');
$consulted = array_key_exists('id', $_GET);
$exists = false;

if ($consulted) {
    try {
        $pdo = db();
        $sql = "SELECT id FROM users WHERE id = $id LIMIT 1";
        $exists = $pdo->query($sql)->fetchColumn() !== false;
    } catch (Throwable $exception) {
        $exists = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blind SQL Injection</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');
            if (savedTheme) document.documentElement.dataset.theme = savedTheme;
        })();
    </script>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>
<body data-page="module-blind-sqli">
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
                    <h1>Blind SQL Injection</h1>
                    <p>Inferencia de condiciones mediante respuestas verdaderas o falsas</p>
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
            <div class="breadcrumb"><a href="/dashboard.php">Panel</a><span>/</span><span>Módulo 06</span></div>
            <section class="module-hero">
                <div class="module-hero-number">MÓDULO 06 · OWASP A03</div>
                <h1>Consulta booleana de usuarios</h1>
                <p>La interfaz no muestra registros ni mensajes del motor, únicamente el resultado lógico de la condición.</p>
            </section>
            <div class="warning-box"><strong>Vulnerabilidad intencional:</strong> la condición recibida se concatena directamente dentro de la sentencia SQL.</div>
            <section class="module-layout">
                <article class="panel-card">

                    <div class="section-heading">
                        <div><h2>Comprobar condición</h2><p>La respuesta solo informa verdadero o falso.</p></div>
                    </div>

                    <form method="get" action="/blind-sqli.php">
                        <div class="form-group">
                            <label for="id">Condición asociada al ID</label>
                            <input class="input-control" id="id" name="id" type="text" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" required>
                        </div>
                        <button class="primary-button" type="submit">Comprobar condición <span>→</span></button>
                    </form>

                    <?php if ($consulted): ?>
                        <div class="alert <?= $exists ? 'alert-success' : 'alert-error' ?>" style="margin-top: 22px;">
                            <?= $exists ? 'Condición verdadera: existe al menos un registro.' : 'Condición falsa: no se encontró ningún registro.' ?>
                        </div>
                    <?php endif; ?>

                </article>
                <aside class="module-status-card">

                    <h3>Comportamiento</h3>
                    <div class="status-list">
                        <div class="status-item"><span class="status-circle">1</span>Entrada por parámetro GET</div>
                        <div class="status-item"><span class="status-circle">2</span>Respuesta booleana</div>
                        <div class="status-item"><span class="status-circle">3</span>Errores ocultos</div>
                        <div class="status-item"><span class="status-circle">4</span>Sin datos visibles</div>
                    </div>
                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">
                    <table class="info-table">
                        <tr><th>Ruta</th><td><code>/blind-sqli.php</code></td></tr>
                        <tr><th>Parámetro</th><td><code>id</code></td></tr>
                        <tr><th>Tipo</th><td>Boolean-based blind</td></tr>
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
