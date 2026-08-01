<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();

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

$pageTitle = 'Registro de accesos';
$activeUrl = '/seguridad/accesos';
$pageHeading = 'Registro de accesos';
$pageSubtitle = 'Seguimiento de los intentos de inicio de sesión';
$environmentLabel = 'Seguridad';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Registro de accesos</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">AUDITORÍA DE ACCESO</div>

    <h1>Control de intentos de inicio de sesión</h1>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <div class="stat-content">
            <strong><?= $total ?></strong>
            <span>Intentos totales</span>
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-content">
            <strong><?= $successful ?></strong>
            <span>Accesos correctos</span>
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-content">
            <strong><?= $failed ?></strong>
            <span>Intentos fallidos</span>
        </div>
    </article>

    <article class="stat-card">
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

            <a class="back-button" href="/seguridad/accesos">
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
                    $wasSuccessful = pg_boolean(
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
                                : 'status-badge badge-failed'
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
                                short_text(
                                    (string) $attempt['user_agent'],
                                    80
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
        <h3>Actividad por usuario</h3>

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

        <h3>Resumen</h3>

        <div class="status-list">
            <div class="status-item">
                <span class="status-circle">
                    <?= $uniqueUsers ?>
                </span>

                Usuarios distintos
            </div>

            <div class="status-item">
                <span class="status-circle">1</span>

                Registro de auditoría activo
            </div>
        </div>
    </aside>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
