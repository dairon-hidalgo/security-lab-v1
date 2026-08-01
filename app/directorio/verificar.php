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

    <p>
        Permite confirmar si existe un registro en el directorio. La
        interfaz solo informa el resultado lógico de la verificación.
    </p>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Comprobar condición</h2>

                <p>
                    La respuesta solo informa verdadero o falso.
                </p>
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
                    ? 'Condición verdadera: existe al menos un registro.'
                    : 'Condición falsa: no se encontró ningún registro.'
                ?>
            </div>
        <?php endif; ?>
    </article>

    <aside class="module-status-card">
        <h3>Comportamiento</h3>

        <div class="status-list">
            <div class="status-item">
                <span class="status-circle">1</span>
                Entrada por parámetro GET
            </div>

            <div class="status-item">
                <span class="status-circle">2</span>
                Respuesta booleana
            </div>

            <div class="status-item">
                <span class="status-circle">3</span>
                Respuesta binaria
            </div>

            <div class="status-item">
                <span class="status-circle">4</span>
                Sin datos visibles
            </div>
        </div>

        <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

        <table class="info-table">
            <tr>
                <th>Parámetro</th>
                <td><code>id</code></td>
            </tr>
        </table>
    </aside>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
