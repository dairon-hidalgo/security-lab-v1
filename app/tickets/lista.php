<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$pdo = db();
$config = app_config();

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
$environmentLabel = 'Mesa de servicio';

require __DIR__ . '/../includes/header.php';
?>

<section class="panel-card">
    <div class="section-heading">
        <div>
            <h2>Todas las solicitudes</h2>
        </div>

        <span class="status-badge status-open">
            <?= count($tickets) ?> solicitudes
        </span>
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
