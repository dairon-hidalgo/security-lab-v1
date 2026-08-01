<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$pdo = db();

$resource = trim(
    (string) ($_GET['page'] ?? 'pages/home.php')
);

$includeOutput = '';
$errorMessage = null;
$wasSuccessful = false;

$isRemote = preg_match(
    '#^https?://#i',
    $resource
) === 1;

$resourceType = $isRemote ? 'remoto' : 'local';

/*
 * La inclusión remota se limita al servicio interno de Docker.
 * No se permiten direcciones remotas de Internet.
 */
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

function pg_boolean(mixed $value): bool
{
    return $value === true
        || $value === 1
        || $value === '1'
        || $value === 't'
        || $value === 'true';
}

function short_resource(string $value, int $limit = 65): string
{
    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, $limit - 3) . '...';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Módulo 03 — Inclusión de archivos</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');

            if (savedTheme) {
                document.documentElement.dataset.theme = savedTheme;
            }
        })();
    </script>

    <link
        rel="icon"
        href="/assets/favicon.svg"
        type="image/svg+xml"
    >

    <link rel="stylesheet" href="/styles.css">
</head>

<body data-page="module-file-include">
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">FI</div>

            <div>
                <strong>Service Desk FIIS</strong>
                <span>Security Lab · V1</span>
            </div>
        </div>

        <div class="sidebar-section-title">Navegación</div>

        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/dashboard.php">
                <span class="sidebar-link-icon">
                    <?= icon('home', 18) ?>
                </span>

                Panel principal
            </a>

            <a class="sidebar-link active" href="/file-include.php">
                <span class="sidebar-link-icon">
                    <?= icon('folder', 18) ?>
                </span>

                Inclusión de archivos
            </a>

            <a class="sidebar-link" href="/evidence.php">
                <span class="sidebar-link-icon">
                    <?= icon('activity', 18) ?>
                </span>

                Registrar evidencia
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a class="logout-link" href="/logout.php">
                Cerrar sesión
            </a>
        </div>
    </aside>

    <section class="main-area">
        <header class="top-header">
            <div class="top-header-left">
                <button
                    type="button"
                    class="icon-button mobile-menu-button"
                    data-sidebar-toggle
                    aria-label="Abrir menú"
                >
                    <?= icon('menu', 20) ?>
                </button>

                <div class="page-title">
                    <h1>Inclusión de archivos</h1>

                    <p>
                        Laboratorio de inclusión local y remota
                    </p>
                </div>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    Riesgo alto
                </div>

                <button
                    type="button"
                    class="icon-button"
                    data-theme-toggle
                    aria-label="Cambiar tema"
                >
                    <span data-theme-moon>
                        <?= icon('moon', 19) ?>
                    </span>

                    <span data-theme-sun hidden>
                        <?= icon('sun', 19) ?>
                    </span>
                </button>
            </div>
        </header>

        <main class="content">
            <div class="breadcrumb">
                <a href="/dashboard.php">Panel</a>
                <span>/</span>
                <span>Módulo 03</span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">
                    MÓDULO 03 · FILE INCLUSION
                </div>

                <h1>Visor vulnerable de documentación</h1>

                <p>
                    El valor recibido en el parámetro
                    <code>page</code> se utiliza directamente en una
                    instrucción de inclusión de PHP.
                </p>
            </section>

            <div class="warning-box">
                <strong>Debilidad intencional:</strong>
                no existe normalización de rutas ni una lista cerrada de
                archivos permitidos.
            </div>

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

                    <form method="get" action="/file-include.php">
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
                    <h3>Recursos del laboratorio</h3>

                    <div class="include-example-list">
                        <a
                            href="/file-include.php?page=pages/home.php"
                            class="include-example"
                        >
                            <strong>Página principal</strong>
                            <code>pages/home.php</code>
                        </a>

                        <a
                            href="/file-include.php?page=pages/help.php"
                            class="include-example"
                        >
                            <strong>Centro de ayuda</strong>
                            <code>pages/help.php</code>
                        </a>

                        <a
                            href="/file-include.php?page=/etc/hostname"
                            class="include-example"
                        >
                            <strong>Archivo local del contenedor</strong>
                            <code>/etc/hostname</code>
                        </a>

                        <a
                            href="/file-include.php?page=http://rfi-source/remote-note.txt"
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
                            <th>Función</th>
                            <td><code>include</code></td>
                        </tr>

                        <tr>
                            <th>RFI externa</th>
                            <td>Bloqueada</td>
                        </tr>
                    </table>
                </aside>
            </section>

            <section class="panel-card" style="margin-top: 22px;">
                <div class="section-heading">
                    <div>
                        <h2>Historial reciente</h2>

                        <p>
                            Recursos solicitados en el laboratorio.
                        </p>
                    </div>

                    <a class="back-button" href="/file-include.php">
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
                                            short_resource(
                                                (string) $attempt[
                                                    'resource_value'
                                                ]
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
        </main>

        <footer class="footer">
            FIIS Security Lab · Módulo 03 · Entorno local
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
</body>
</html>