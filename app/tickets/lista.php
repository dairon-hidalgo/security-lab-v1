<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

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

$tickets = $pdo->query(
    'SELECT
        tickets.id,
        tickets.title,
        tickets.description,
        tickets.status,
        tickets.created_at,
        users.full_name AS requester,
        users.username AS username
     FROM tickets
     LEFT JOIN users
        ON users.id = tickets.user_id
     ORDER BY tickets.created_at DESC, tickets.id DESC'
)->fetchAll();

$pageTitle = 'Cola de tickets';
$activeUrl = '/tickets';
$pageHeading = 'Cola de tickets';
$pageSubtitle = 'Solicitudes de la mesa de servicio';
$environmentLabel = 'Mesa de servicio';

require __DIR__ . '/../includes/header.php';
?>

<section class="stats-grid">
    <?php foreach ($config['ticket_statuses'] as $key => $statusInfo): ?>
        <article class="stat-card">
            <div class="stat-icon">
                <?= icon($key === 'resuelto' ? 'check' : 'activity', 21) ?>
            </div>

            <div class="stat-content">
                <strong><?= $ticketTotals[$key] ?></strong>
                <span><?= htmlspecialchars((string) $statusInfo['label']) ?></span>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel-card" style="margin-top: 26px;">
    <div class="section-heading">
        <div>
            <h2>Todas las solicitudes</h2>

            <p>
                Registro completo de la cola de tickets.
            </p>
        </div>
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
            <?php if ($tickets === []): ?>
                <tr>
                    <td colspan="5">Aún no hay tickets registrados.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td>
                        #<?= htmlspecialchars((string) $ticket['id']) ?>
                    </td>

                    <td>
                        <strong>
                            <?= htmlspecialchars((string) $ticket['title']) ?>
                        </strong>

                        <br>

                        <span class="ticket-description">
                            <?= htmlspecialchars(
                                short_text((string) $ticket['description'], 90)
                            ) ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            (string) ($ticket['requester'] ?? $ticket['username'] ?? 'desconocido')
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

<?php require __DIR__ . '/../includes/footer.php'; ?>
