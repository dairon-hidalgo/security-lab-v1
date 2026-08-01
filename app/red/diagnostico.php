<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();

$target = '';
$executedCommand = '';
$commandOutput = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = trim((string) ($_POST['target'] ?? ''));

    if ($target === '') {
        $errorMessage = 'Ingresa una dirección IP o nombre de host.';
    } else {
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

$pageTitle = 'Diagnóstico de red';
$activeUrl = '/red/diagnostico';
$pageHeading = 'Diagnóstico de conectividad';
$pageSubtitle = 'Herramienta de soporte para comprobar disponibilidad de equipos';
$environmentLabel = 'Red interna';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Diagnóstico de red</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">RED Y CONECTIVIDAD</div>

    <h1>Comprobación de disponibilidad</h1>

    <p>
        La aplicación ejecuta un ping sobre el destino indicado para
        verificar su disponibilidad en la red.
    </p>
</section>

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

        <form method="post" action="/red/diagnostico">
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
        <h3>Proceso ejecutado</h3>

        <div class="status-list">
            <div class="status-item">
                <span class="status-circle">1</span>

                Entrada recibida por POST
            </div>

            <div class="status-item">
                <span class="status-circle">2</span>

                Construcción del comando
            </div>

            <div class="status-item">
                <span class="status-circle">3</span>

                Ejecución del proceso
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
                <th>Usuario del sistema</th>
                <td><code>www-data</code></td>
            </tr>

            <tr>
                <th>Servidor</th>
                <td>Contenedor Linux</td>
            </tr>

            <tr>
                <th>Plataforma</th>
                <td>PHP 8.2</td>
            </tr>
        </table>
    </aside>
</section>

<section class="panel-card" style="margin-top: 22px;">
    <div class="section-heading">
        <div>
            <h2>Historial reciente</h2>

            <p>
                Últimas comprobaciones registradas.
            </p>
        </div>

        <a class="back-button" href="/red/diagnostico">
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
                        No existen comprobaciones registradas.
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

<?php require __DIR__ . '/../includes/footer.php'; ?>
