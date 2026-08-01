<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();

$resource = trim(
    (string) ($_GET['page'] ?? '../pages/home.php')
);

$includeOutput = '';
$errorMessage = null;
$wasSuccessful = false;

$isRemote = preg_match(
    '#^https?://#i',
    $resource
) === 1;

$resourceType = $isRemote ? 'remoto' : 'local';

if (
    $isRemote
    && !str_starts_with(
        strtolower($resource),
        'http://rfi-source/'
    )
) {
    $errorMessage =
        'La demostración remota está limitada al servicio interno rfi-source.';
} else {
    ob_start();

    $includeResult = @include $resource;

    $includeOutput = (string) ob_get_clean();
    $wasSuccessful = $includeResult !== false;

    if (!$wasSuccessful) {
        $errorMessage = 'No fue posible incluir el recurso solicitado.';
    }
}

if (isset($_GET['page'])) {
    $statement = $pdo->prepare(
        'INSERT INTO file_include_attempts (
            user_id,
            resource_value,
            resource_type,
            was_successful,
            result_excerpt,
            ip_address,
            user_agent
        ) VALUES (
            :user_id,
            :resource_value,
            :resource_type,
            :was_successful,
            :result_excerpt,
            :ip_address,
            :user_agent
        )'
    );

    $statement->bindValue(
        ':user_id',
        (int) ($user['id'] ?? 0),
        PDO::PARAM_INT
    );

    $statement->bindValue(
        ':resource_value',
        $resource,
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':resource_type',
        $resourceType,
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':was_successful',
        $wasSuccessful,
        PDO::PARAM_BOOL
    );

    $statement->bindValue(
        ':result_excerpt',
        substr(strip_tags($includeOutput), 0, 500),
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':ip_address',
        (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        PDO::PARAM_STR
    );

    $statement->bindValue(
        ':user_agent',
        (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
        PDO::PARAM_STR
    );

    $statement->execute();
}

$recentAttempts = $pdo->query(
    'SELECT
        file_include_attempts.resource_value,
        file_include_attempts.resource_type,
        file_include_attempts.was_successful,
        file_include_attempts.ip_address,
        file_include_attempts.attempted_at,
        users.username
     FROM file_include_attempts
     LEFT JOIN users
        ON users.id = file_include_attempts.user_id
     ORDER BY file_include_attempts.attempted_at DESC
     LIMIT 20'
)->fetchAll();

$pageTitle = 'Documentación';
$activeUrl = '/soporte/documentacion';
$pageHeading = 'Centro de documentación';
$pageSubtitle = 'Consulta de recursos internos del soporte';
$environmentLabel = 'Mesa de servicio';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Documentación</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">DOCUMENTACIÓN</div>

    <h1>Visor de recursos internos</h1>

    <p>
        Permite cargar secciones de ayuda, manuales o fuentes internas
        del equipo de soporte.
    </p>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Seleccionar recurso</h2>

                <p>
                    Carga una sección local o una fuente interna.
                </p>
            </div>
        </div>

        <form method="get" action="/soporte/documentacion">
            <div class="form-group">
                <label for="page">
                    Ruta o recurso
                </label>

                <input
                    class="input-control"
                    type="text"
                    id="page"
                    name="page"
                    value="<?= htmlspecialchars($resource) ?>"
                    required
                >
            </div>

            <button type="submit" class="primary-button">
                Incluir recurso
                <span>→</span>
            </button>
        </form>

        <?php if ($errorMessage !== null): ?>
            <div
                class="alert alert-error"
                style="margin-top: 22px;"
            >
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($wasSuccessful): ?>
            <section class="include-result">
                <div class="include-result-header">
                    <strong>Contenido incluido</strong>

                    <span class="status-badge status-implemented">
                        <?= htmlspecialchars($resourceType) ?>
                    </span>
                </div>

                <div class="include-result-body">
                    <?= $includeOutput ?>
                </div>
            </section>
        <?php endif; ?>
    </article>

    <aside class="module-status-card">
        <h3>Recursos disponibles</h3>

        <div class="include-example-list">
            <a
                href="/soporte/documentacion?page=../pages/home.php"
                class="include-example"
            >
                <strong>Página principal</strong>
                <code>../pages/home.php</code>
            </a>

            <a
                href="/soporte/documentacion?page=../pages/help.php"
                class="include-example"
            >
                <strong>Centro de ayuda</strong>
                <code>../pages/help.php</code>
            </a>

            <a
                href="/soporte/documentacion?page=/etc/hostname"
                class="include-example"
            >
                <strong>Archivo del servidor</strong>
                <code>/etc/hostname</code>
            </a>

            <a
                href="/soporte/documentacion?page=http://rfi-source/remote-note.txt"
                class="include-example"
            >
                <strong>Fuente remota interna</strong>
                <code>http://rfi-source/remote-note.txt</code>
            </a>
        </div>

        <hr
            style="
                margin: 22px 0;
                border: 0;
                border-top: 1px solid var(--border);
            "
        >

        <table class="info-table">
            <tr>
                <th>Tipo detectado</th>
                <td><?= htmlspecialchars($resourceType) ?></td>
            </tr>

            <tr>
                <th>Resultado</th>
                <td>
                    <?= $wasSuccessful
                        ? 'Incluido'
                        : 'No incluido'
                    ?>
                </td>
            </tr>

            <tr>
                <th>Mecanismo</th>
                <td><code>include</code></td>
            </tr>
        </table>
    </aside>
</section>

<section class="panel-card" style="margin-top: 22px;">
    <div class="section-heading">
        <div>
            <h2>Historial reciente</h2>

            <p>
                Recursos solicitados recientemente.
            </p>
        </div>

        <a class="back-button" href="/soporte/documentacion">
            Actualizar
        </a>
    </div>

    <div class="project-module-table-wrapper">
        <table class="info-table">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Recurso</th>
                <th>Tipo</th>
                <th>Resultado</th>
                <th>IP</th>
            </tr>
            </thead>

            <tbody>
            <?php if ($recentAttempts === []): ?>
                <tr>
                    <td colspan="6">
                        No existen intentos registrados.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($recentAttempts as $attempt): ?>
                <?php
                $success = pg_boolean(
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
                        <?= htmlspecialchars(
                            (string) (
                                $attempt['username']
                                ?? 'Desconocido'
                            )
                        ) ?>
                    </td>

                    <td title="<?= htmlspecialchars(
                        (string) $attempt['resource_value']
                    ) ?>">
                        <code>
                            <?= htmlspecialchars(
                                short_text(
                                    (string) $attempt[
                                        'resource_value'
                                    ],
                                    65
                                )
                            ) ?>
                        </code>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            (string) $attempt['resource_type']
                        ) ?>
                    </td>

                    <td>
                        <span class="<?= $success
                            ? 'status-badge status-implemented'
                            : 'status-badge risk-critical'
                        ?>">
                            <?= $success
                                ? 'Correcto'
                                : 'Fallido'
                            ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            (string) $attempt['ip_address']
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
