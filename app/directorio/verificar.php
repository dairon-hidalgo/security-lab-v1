<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();

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

$pageTitle = 'Verificación de usuarios';
$activeUrl = '/directorio/verificar';
$pageHeading = 'Verificación de usuarios';
$pageSubtitle = 'Comprobación de disponibilidad de un registro';
$environmentLabel = 'Directorio';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Verificación de usuarios</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">DIRECTORIO</div>

    <h1>Comprobación de registros</h1>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Comprobar condición</h2>

            </div>
        </div>

        <form method="get" action="/directorio/verificar">
            <div class="form-group">
                <label for="id">Condición asociada al ID</label>

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
                Comprobar condición
                <span>→</span>
            </button>
        </form>

        <?php if ($consulted): ?>
            <div
                class="alert <?= $exists ? 'alert-success' : 'alert-error' ?>"
                style="margin-top: 22px;"
            >
                <?= $exists
                    ? 'Existe al menos un registro.'
                    : 'No se encontró ningún registro.'
                ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
