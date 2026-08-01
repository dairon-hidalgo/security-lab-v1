<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$pdo = db();

$target = '';
$executedCommand = '';
$commandOutput = null;
$errorMessage = null;

function short_text(string $value, int $limit = 85): string
{
    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, $limit - 3) . '...';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = trim((string) ($_POST['target'] ?? ''));

    if ($target === '') {
        $errorMessage = 'Ingresa una dirección IP o nombre de host.';
    } else {
        /*
         * Vulnerabilidad intencional de la V1:
         *
         * La entrada se concatena directamente dentro de un comando
         * interpretado por el shell del sistema.
         *
         * En una versión segura se debería validar la dirección,
         * evitar el shell y emplear funciones con argumentos separados.
         */
        $executedCommand = 'ping -c 1 ' . $target;

        $rawOutput = shell_exec(
            $executedCommand . ' 2>&1'
        );

        $commandOutput = $rawOutput !== null
            ? substr($rawOutput, 0, 12000)
            : 'El proceso no devolvió información.';

        $statement = $pdo->prepare(
            'INSERT INTO command_attempts (
                user_id,
                input_value,
                executed_command,
                command_output,
                ip_address,
                user_agent
            ) VALUES (
                :user_id,
                :input_value,
                :executed_command,
                :command_output,
                :ip_address,
                :user_agent
            )'
        );

        $statement->execute([
            'user_id' => (int) ($user['id'] ?? 0),
            'input_value' => $target,
            'executed_command' => $executedCommand,
            'command_output' => $commandOutput,
            'ip_address' => (string) (
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ),
            'user_agent' => (string) (
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ),
        ]);
    }
}

$recentExecutions = $pdo->query(
    'SELECT
        command_attempts.id,
        command_attempts.input_value,
        command_attempts.executed_command,
        command_attempts.command_output,
        command_attempts.ip_address,
        command_attempts.executed_at,
        users.username
     FROM command_attempts
     LEFT JOIN users
        ON users.id = command_attempts.user_id
     ORDER BY command_attempts.executed_at DESC
     LIMIT 15'
)->fetchAll();

$nameParts = preg_split(
    '/\s+/',
    trim((string) ($user['full_name'] ?? 'Usuario'))
);

$userInitials = '';

foreach (array_slice($nameParts ?: ['U'], 0, 2) as $part) {
    $userInitials .= strtoupper(substr($part, 0, 1));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Módulo 02 — Ejecución de comandos</title>

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

<body data-page="module-command">
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

            <a class="sidebar-link active" href="/command.php">
                <span class="sidebar-link-icon">
                    <?= icon('terminal', 18) ?>
                </span>

                Ejecución de comandos
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
                    <h1>Ejecución de comandos</h1>

                    <p>
                        Herramienta vulnerable de diagnóstico de red
                    </p>
                </div>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    Riesgo crítico
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
                <span>Módulo 02</span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">
                    MÓDULO 02 · OWASP A03
                </div>

                <h1>Diagnóstico de conectividad</h1>

                <p>
                    La aplicación construye un comando del sistema utilizando
                    directamente el valor recibido desde el formulario.
                </p>
            </section>

            <div class="warning-box">
                <strong>Vulnerabilidad intencional:</strong>
                la entrada no se valida ni se separa del comando que será
                interpretado por el shell del contenedor.
            </div>

            <section class="module-layout">
                <article class="panel-card">
                    <div class="section-heading">
                        <div>
                            <h2>Comprobar conectividad</h2>

                            <p>
                                Ingresa una IP o nombre de host del entorno local.
                            </p>
                        </div>
                    </div>

                    <?php if ($errorMessage !== null): ?>
                        <div class="alert alert-error">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/command.php">
                        <div class="form-group">
                            <label for="target">
                                Dirección IP o nombre de host
                            </label>

                            <input
                                class="input-control"
                                type="text"
                                id="target"
                                name="target"
                                value="<?= htmlspecialchars($target) ?>"
                                placeholder="Ejemplo: 127.0.0.1"
                                required
                            >
                        </div>

                        <button type="submit" class="primary-button">
                            Ejecutar diagnóstico
                            <span>→</span>
                        </button>
                    </form>

                    <?php if ($executedCommand !== ''): ?>
                        <div style="margin-top: 25px;">
                            <h3>Comando construido</h3>

                            <pre class="terminal-output"><code><?= htmlspecialchars(
                                $executedCommand
                            ) ?></code></pre>
                        </div>
                    <?php endif; ?>

                    <?php if ($commandOutput !== null): ?>
                        <div style="margin-top: 20px;">
                            <h3>Resultado del proceso</h3>

                            <pre class="terminal-output"><?= htmlspecialchars(
                                $commandOutput
                            ) ?></pre>
                        </div>
                    <?php endif; ?>
                </article>

                <aside class="module-status-card">
                    <h3>Información técnica</h3>

                    <div class="status-list">
                        <div class="status-item">
                            <span class="status-circle">1</span>

                            Entrada recibida por POST
                        </div>

                        <div class="status-item">
                            <span class="status-circle">2</span>

                            Concatenación directa
                        </div>

                        <div class="status-item">
                            <span class="status-circle">3</span>

                            Ejecución mediante shell
                        </div>

                        <div class="status-item">
                            <span class="status-circle">4</span>

                            Resultado visible
                        </div>
                    </div>

                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Contexto</h3>

                    <table class="info-table">
                        <tr>
                            <th>Usuario web</th>
                            <td><code>www-data</code></td>
                        </tr>

                        <tr>
                            <th>Sistema</th>
                            <td>Contenedor Linux</td>
                        </tr>

                        <tr>
                            <th>Aplicación</th>
                            <td>PHP 8.2</td>
                        </tr>

                        <tr>
                            <th>Exposición</th>
                            <td>localhost:8081</td>
                        </tr>
                    </table>
                </aside>
            </section>

            <section class="panel-card" style="margin-top: 22px;">
                <div class="section-heading">
                    <div>
                        <h2>Historial reciente</h2>

                        <p>
                            Últimas ejecuciones registradas por el laboratorio.
                        </p>
                    </div>

                    <a class="back-button" href="/command.php">
                        Actualizar
                    </a>
                </div>

                <div class="project-module-table-wrapper">
                    <table class="info-table">
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Entrada</th>
                            <th>Comando</th>
                            <th>IP</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php if ($recentExecutions === []): ?>
                            <tr>
                                <td colspan="5">
                                    No existen ejecuciones registradas.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($recentExecutions as $execution): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        (string) $execution['executed_at']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        (string) (
                                            $execution['username'] ?? 'Desconocido'
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    <code title="<?= htmlspecialchars(
                                        (string) $execution['input_value']
                                    ) ?>">
                                        <?= htmlspecialchars(
                                            short_text(
                                                (string) $execution['input_value'],
                                                35
                                            )
                                        ) ?>
                                    </code>
                                </td>

                                <td title="<?= htmlspecialchars(
                                    (string) $execution['executed_command']
                                ) ?>">
                                    <?= htmlspecialchars(
                                        short_text(
                                            (string) $execution['executed_command'],
                                            50
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        (string) $execution['ip_address']
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <footer class="footer">
            FIIS Security Lab · Módulo 02 · Entorno controlado
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
</body>
</html>