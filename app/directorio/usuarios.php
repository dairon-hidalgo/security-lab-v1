<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();

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

$pageTitle = 'Directorio de usuarios';
$activeUrl = '/directorio/usuarios';
$pageHeading = 'Directorio de usuarios';
$environmentLabel = 'Directorio';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Directorio de usuarios</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">DIRECTORIO</div>
    <h1>Consulta de usuarios</h1>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Consulta por identificador</h2>
            </div>
        </div>

        <form method="get" action="/directorio/usuarios">
            <div class="form-group">
                <label for="id">Identificador del usuario</label>

                <input
                    class="input-control"
                    id="id"
                    name="id"
                    type="text"
                    value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                    required
                >
            </div>

            <button class="primary-button" type="submit">
                Consultar
            </button>
        </form>

        <?php if ($errorMessage !== null): ?>
            <div class="alert alert-error" style="margin-top: 20px;">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 24px;">
            <h3>Resultados: <?= count($results) ?></h3>

            <div class="project-module-table-wrapper">
                <table class="info-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if ($results === []): ?>
                        <tr>
                            <td colspan="4">
                                No se encontraron registros.
                            </td>
                        </tr>
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
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
