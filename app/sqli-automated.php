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
$activeUrl = '/sqli-automated.php';
$activeIcon = 'activity';
$activeLabel = 'SQLi automatizada';

$id = trim((string) ($_GET['id'] ?? '1'));
$validatedId = filter_var(
    $id,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$responseText = 'Sin resultados.';
$errorMessage = null;

if ($validatedId === false) {
    http_response_code(400);
    $errorMessage = 'El parametro id fue rechazado por la validacion.';
    $responseText = 'Solicitud no procesada.';
} else {
    try {
        $pdo = db();
        $statement = $pdo->prepare(
            'SELECT id, username, full_name, role FROM users WHERE id = :id ORDER BY id'
        );
        $statement->bindValue(':id', $validatedId, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll();

        if ($rows !== []) {
            $lines = [];

            foreach ($rows as $row) {
                $lines[] = implode(' | ', [
                    (string) $row['id'],
                    (string) $row['username'],
                    (string) $row['full_name'],
                    (string) $row['role'],
                ]);
            }

            $responseText = implode("\n", $lines);
        }
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(500);
        $errorMessage = 'No fue posible completar la solicitud.';
        $responseText = 'La consulta no pudo completarse.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection automatizada</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');
            if (savedTheme) document.documentElement.dataset.theme = savedTheme;
        })();
    </script>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>
<body data-page="module-sqli-auto">
<div class="app-shell">

    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">FI</div>
            <div>
                <strong>Service Desk FIIS</strong>
                <span>Security Lab · V2</span>
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
                    <h1>SQL Injection automatizada</h1>
                    <p>Endpoint reforzado para comprobar que la inyeccion automatizada queda bloqueada</p>
                </div>
            </div>
            <div class="header-actions">
                <div class="environment-badge"><span class="environment-dot"></span>Control activo</div>
                <button type="button" class="icon-button" data-theme-toggle aria-label="Cambiar tema">
                    <span data-theme-moon><?= icon('moon', 19) ?></span>
                    <span data-theme-sun hidden><?= icon('sun', 19) ?></span>
                </button>
            </div>
        </header>
        <main class="content">
            <div class="breadcrumb"><a href="/dashboard.php">Panel</a><span>/</span><span>Módulo 05</span></div>
            <section class="module-hero">
                <div class="module-hero-number">MÓDULO 05 · OWASP A03</div>
                <h1>Objetivo automatizado del laboratorio</h1>
                <p>La respuesta cambia según el parámetro ID y conserva errores observables del motor de base de datos.</p>
            </section>
            <div class="alert alert-success"><strong>Mitigacion activa:</strong> el endpoint valida el ID, usa PDO preparado y no expone errores del motor.</div>
            <section class="module-layout">
                <article class="panel-card">

                    <div class="section-heading">
                        <div><h2>Punto de prueba automatizada</h2><p>Endpoint autenticado con un parametro GET validado y parametrizado.</p></div>
                    </div>

                    <form method="get" action="/sqli-automated.php">
                        <div class="form-group">
                            <label for="id">ID consultado</label>
                            <input class="input-control" id="id" name="id" type="text" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" required>
                        </div>
                        <button class="primary-button" type="submit">Enviar solicitud <span>→</span></button>
                    </form>

                    <?php if ($errorMessage !== null): ?>
                        <div class="alert alert-error" style="margin-top: 20px;"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <div style="margin-top: 24px;">
                        <h3>Respuesta del endpoint</h3>
                        <pre class="terminal-output"><?= htmlspecialchars($responseText, ENT_QUOTES, 'UTF-8') ?></pre>
                    </div>

                </article>
                <aside class="module-status-card">

                    <h3>Datos del objetivo</h3>
                    <table class="info-table">
                        <tr><th>Ruta</th><td><code>/sqli-automated.php</code></td></tr>
                        <tr><th>Método</th><td>GET</td></tr>
                        <tr><th>Parámetro</th><td><code>id</code></td></tr>
                        <tr><th>Autenticación</th><td>Cookie de sesión</td></tr>
                    </table>
                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">
                    <h3>Verificacion defensiva</h3>
                    <div class="status-list">
                        <div class="status-item"><span class="status-circle">1</span>Enviar una entrada normal</div>
                        <div class="status-item"><span class="status-circle">2</span>Intentar una entrada manipulada</div>
                        <div class="status-item"><span class="status-circle">3</span>Confirmar que no hay enumeracion</div>
                        <div class="status-item"><span class="status-circle">4</span>Comparar V1 y V2</div>
                    </div>

                </aside>
            </section>
        </main>
        <footer class="footer">Service Desk FIIS · Laboratorio local y autorizado</footer>
    </section>
</div>
<script src="/assets/app.js"></script>
</body>
</html>
