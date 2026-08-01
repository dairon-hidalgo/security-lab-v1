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

function allowed_diagnostic_targets(): array
{
    return [
        '127.0.0.1' => 'Interfaz local del contenedor',
        'localhost' => 'Nombre local del contenedor',
        'db' => 'Servicio PostgreSQL interno',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = trim((string) ($_POST['target'] ?? ''));
    $submittedToken = isset($_POST['csrf_token'])
        ? (string) $_POST['csrf_token']
        : null;

    if (!csrf_token_is_valid($submittedToken)) {
        http_response_code(400);
        $errorMessage = 'La solicitud no es válida. Actualiza la página.';
    } elseif ($target === '') {
        $errorMessage = 'Selecciona un destino permitido.';
    } elseif (!array_key_exists($target, allowed_diagnostic_targets())) {
        http_response_code(422);
        $errorMessage =
            'El destino fue rechazado. La V2 utiliza una lista cerrada '
            . 'y no ejecuta entradas arbitrarias.';
    } else {
        /*
         * V2 segura:
         * - No se construye ningún comando del sistema.
         * - No se utiliza shell_exec(), exec(), system() ni passthru().
         * - El destino pertenece a una lista cerrada.
         * - La resolución se realiza con funciones nativas de PHP.
         */
        $executedCommand = 'Resolución DNS nativa de PHP (sin shell)';

        if (filter_var($target, FILTER_VALIDATE_IP) !== false) {
            $resolvedAddress = $target;
        } else {
            $resolvedAddress = gethostbyname($target);
        }

        $description = allowed_diagnostic_targets()[$target];

        $commandOutput = implode(
            PHP_EOL,
            [
                'Entrada validada: ' . $target,
                'Destino autorizado: ' . $description,
                'Dirección resuelta: ' . $resolvedAddress,
                'Shell del sistema: no utilizado',
                'Resultado: diagnóstico procesado de forma segura',
            ]
        );

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
                <span>Security Lab · V2</span>
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
                    La V2 valida el destino contra una lista cerrada y utiliza
                    funciones nativas de PHP, sin invocar el shell.
                </p>
            </section>

            <div class="alert alert-success">
                <strong>Control aplicado:</strong>
                lista cerrada de destinos, token CSRF y diagnóstico nativo
                de PHP sin ejecución de comandos del sistema.
            </div>

            <section class="module-layout">
                <article class="panel-card">
                    <div class="section-heading">
                        <div>
                            <h2>Comprobar conectividad</h2>

                            <p>
                                Selecciona un destino interno autorizado.
                            </p>
                        </div>
                    </div>

                    <?php if ($errorMessage !== null): ?>
                        <div class="alert alert-error">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/command.php">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <div class="form-group">
                            <label for="target">
                                Dirección IP o nombre de host
                            </label>

                            <select
                                class="input-control"
                                id="target"
                                name="target"
                                required
                            >
                                <option value="">Selecciona un destino</option>
                                <?php foreach (allowed_diagnostic_targets() as $value => $label): ?>
                                    <option
                                        value="<?= htmlspecialchars($value) ?>"
                                        <?= $target === $value ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($value . ' — ' . $label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="primary-button">
                            Ejecutar diagnóstico
                            <span>→</span>
                        </button>
                    </form>

                    <?php if ($executedCommand !== ''): ?>
                        <div style="margin-top: 25px;">
                            <h3>Operación aplicada</h3>

                            <pre class="terminal-output"><code><?= htmlspecialchars(
                                $executedCommand
                            ) ?></code></pre>
                        </div>
                    <?php endif; ?>

                    <?php if ($commandOutput !== null): ?>
                        <div style="margin-top: 20px;">
                            <h3>Resultado del diagnóstico</h3>

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

                            Validación mediante lista cerrada
                        </div>

                        <div class="status-item">
                            <span class="status-circle">3</span>

                            Resolución nativa de PHP
                        </div>

                        <div class="status-item">
                            <span class="status-circle">4</span>

                            Shell deshabilitado
                        </div>
                    </div>

                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Contexto</h3>

                    <table class="info-table">
                        <tr>
                            <th>Ejecución de shell</th>
                            <td>Deshabilitada</td>
                        </tr>

                        <tr>
                            <th>Destinos</th>
                            <td>localhost, 127.0.0.1 y db</td>
                        </tr>

                        <tr>
                            <th>Aplicación</th>
                            <td>PHP 8.2</td>
                        </tr>

                        <tr>
                            <th>Exposición</th>
                            <td>localhost:8082</td>
                        </tr>
                    </table>
                </aside>
            </section>

            <section class="panel-card" style="margin-top: 22px;">
                <div class="section-heading">
                    <div>
                        <h2>Historial reciente</h2>

                        <p>
                            Últimos diagnósticos seguros registrados.
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
                            <th>Operación</th>
                            <th>IP</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php if ($recentExecutions === []): ?>
                            <tr>
                                <td colspan="5">
                                    No existen diagnósticos registrados.
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
            FIIS Security Lab V2 · Módulo 02 · Entorno seguro
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
</body>
</html>