<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();
$config = app_config();

$ticketTotals = [
    'abierto' => 0,
    'en proceso' => 0,
    'resuelto' => 0,
];

foreach ($pdo->query(
    'SELECT status, COUNT(*) AS total
     FROM tickets
     GROUP BY status'
)->fetchAll() as $row) {
    $ticketTotals[(string) $row['status']] = (int) $row['total'];
}

$recentTickets = $pdo->query(
    'SELECT
        tickets.id,
        tickets.title,
        tickets.status,
        tickets.created_at,
        users.full_name AS requester
     FROM tickets
     LEFT JOIN users
        ON users.id = tickets.user_id
     ORDER BY tickets.created_at DESC, tickets.id DESC
     LIMIT 6'
)->fetchAll();

$ticketTotal = array_sum($ticketTotals);

$recentAccesses = $pdo->query(
    'SELECT
        login_attempts.username,
        login_attempts.was_successful,
        login_attempts.attempted_at
     FROM login_attempts
     ORDER BY login_attempts.attempted_at DESC, login_attempts.id DESC
     LIMIT 6'
)->fetchAll();

$pageTitle = 'Panel de control';
$activeUrl = '/panel';
$pageHeading = 'Panel de control';
$pageSubtitle = 'Mesa de servicio';
$environmentLabel = 'Entorno de soporte';

require __DIR__ . '/includes/header.php';
?>

<section class="welcome-banner">
    <div>
        <h2>
            Bienvenido,
            <?= htmlspecialchars(
                (string) ($user['full_name'] ?? 'Usuario')
            ) ?>
        </h2>

        <p>
            Resumen de la operación de la mesa de servicio.
        </p>
    </div>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <div class="stat-icon">
            <?= icon('activity', 21) ?>
        </div>

        <div class="stat-content">
            <strong><?= $ticketTotals['abierto'] ?></strong>
            <span>Tickets abiertos</span>
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">
            <?= icon('clock', 21) ?>
        </div>

        <div class="stat-content">
            <strong><?= $ticketTotals['en proceso'] ?></strong>
            <span>Tickets en proceso</span>
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">
            <?= icon('check', 21) ?>
        </div>

        <div class="stat-content">
            <strong><?= $ticketTotals['resuelto'] ?></strong>
            <span>Tickets resueltos</span>
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">
            <?= icon('folder', 21) ?>
        </div>

        <div class="stat-content">
            <strong><?= $ticketTotal ?></strong>
            <span>Total de tickets</span>
        </div>
    </article>
</section>

<section class="panel-card" style="margin-top: 26px;">
    <div class="section-heading">
        <div>
            <h2>Tickets recientes</h2>

            <p>
                Últimas solicitudes registradas en la cola.
            </p>
        </div>

        <a class="text-link" href="/tickets">
            Ver cola completa
            <span>→</span>
        </a>
    </div>

    <div class="project-module-table-wrapper">
        <table class="info-table">
            <thead>
            <tr>
                <th>N.º</th>
                <th>Título</th>
                <th>Solicitante</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
            </thead>

            <tbody>
            <?php if ($recentTickets === []): ?>
                <tr>
                    <td colspan="5">Aún no hay tickets registrados.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($recentTickets as $ticket): ?>
                <tr>
                    <td>
                        #<?= htmlspecialchars((string) $ticket['id']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars((string) $ticket['title']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            (string) ($ticket['requester'] ?? 'desconocido')
                        ) ?>
                    </td>

                    <td>
                        <?php
                        $status = (string) $ticket['status'];
                        $statusInfo = $config['ticket_statuses'][$status]
                            ?? ['label' => $status, 'class' => 'status-open'];
                        ?>
                        <span class="status-badge <?= htmlspecialchars((string) $statusInfo['class']) ?>">
                            <?= htmlspecialchars((string) $statusInfo['label']) ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars((string) $ticket['created_at']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel-card" style="margin-top: 26px;">
    <div class="section-heading">
        <div>
            <h2>Actividad de accesos</h2>

            <p>
                Últimos intentos de inicio de sesión registrados.
            </p>
        </div>

        <a class="text-link" href="/seguridad/accesos">
            Ver registro completo
            <span>→</span>
        </a>
    </div>

    <div class="project-module-table-wrapper">
        <table class="info-table">
            <thead>
            <tr>
                <th>Usuario</th>
                <th>Resultado</th>
                <th>Fecha</th>
            </tr>
            </thead>

            <tbody>
            <?php if ($recentAccesses === []): ?>
                <tr>
                    <td colspan="3">Aún no hay accesos registrados.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($recentAccesses as $access): ?>
                <?php
                $accessOk = pg_boolean($access['was_successful']);
                ?>
                <tr>
                    <td>
                        <code>
                            <?= htmlspecialchars((string) $access['username']) ?>
                        </code>
                    </td>

                    <td>
                        <span class="status-badge <?= $accessOk
                            ? 'status-implemented'
                            : 'badge-failed'
                        ?>">
                            <?= $accessOk ? 'Correcto' : 'Fallido' ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            (string) $access['attempted_at']
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
