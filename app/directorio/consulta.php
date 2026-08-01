<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();

$id = (string) ($_GET['id'] ?? '1');
$responseText = 'Sin resultados.';
$errorMessage = null;

try {
    $pdo = db();
    $sql = "SELECT id, username, full_name, role FROM users WHERE id = $id ORDER BY id";
    $rows = $pdo->query($sql)->fetchAll();

    if ($rows !== []) {
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = implode(' | ', [
                (string) $row['id'],
                (string) $row['username'],
                (string) $row['full_name'],
                (string) $row['role'],
            ]);
        }
        $responseText = implode("\n", $lines);
    }
} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
    $responseText = 'La consulta no pudo completarse.';
}

$pageTitle = 'Consulta de datos';
$activeUrl = '/directorio/consulta';
$pageHeading = 'Consulta de datos';
$pageSubtitle = 'Endpoint de consulta del directorio';
$environmentLabel = 'Directorio';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Consulta de datos</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">DIRECTORIO</div>

    <h1>Consulta técnica del directorio</h1>

    <p>
        Endpoint orientado a procesos automatizados. La respuesta cambia
        según el identificador consultado.
    </p>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Punto de consulta</h2>

                <p>
                    Servicio interno de consulta con un parámetro GET.
                </p>
            </div>
        </div>

        <form method="get" action="/directorio/consulta">
            <div class="form-group">
                <label for="id">ID consultado</label>

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
                Enviar solicitud
                <span>→</span>
            </button>
        </form>

        <?php if ($errorMessage !== null): ?>
            <div class="alert alert-error" style="margin-top: 20px;">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 24px;">
            <h3>Respuesta del servicio</h3>

            <pre class="terminal-output"><?= htmlspecialchars(
                $responseText,
                ENT_QUOTES,
                'UTF-8'
            ) ?></pre>
        </div>
    </article>

    <aside class="module-status-card">
        <h3>Datos del endpoint</h3>

        <table class="info-table">
            <tr>
                <th>Método</th>
                <td>GET</td>
            </tr>

            <tr>
                <th>Parámetro</th>
                <td><code>id</code></td>
            </tr>

            <tr>
                <th>Autenticación</th>
                <td>Cookie de sesión</td>
            </tr>
        </table>

        <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

        <h3>Proceso de consulta</h3>

        <div class="status-list">
            <div class="status-item">
                <span class="status-circle">1</span>
                Recibir el parámetro
            </div>

            <div class="status-item">
                <span class="status-circle">2</span>
                Construir la consulta
            </div>

            <div class="status-item">
                <span class="status-circle">3</span>
                Consultar el motor
            </div>

            <div class="status-item">
                <span class="status-circle">4</span>
                Devolver la respuesta
            </div>
        </div>
    </aside>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
