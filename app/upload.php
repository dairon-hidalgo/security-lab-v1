<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$pdo = db();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS upload_attempts (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id),
        original_name VARCHAR(255) NOT NULL,
        stored_name VARCHAR(255) NOT NULL,
        reported_mime VARCHAR(150),
        file_size BIGINT NOT NULL DEFAULT 0,
        was_successful BOOLEAN NOT NULL DEFAULT FALSE,
        public_url VARCHAR(500),
        ip_address VARCHAR(64),
        user_agent TEXT,
        uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

function upload_user_initials(array $user): string
{
    $parts = preg_split(
        '/\s+/',
        trim((string) ($user['full_name'] ?? 'Usuario'))
    );

    $initials = '';

    foreach (array_slice($parts ?: ['U'], 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'U';
}

function upload_public_url(string $fileName): string
{
    return '/uploads/' . rawurlencode($fileName);
}

$userInitials = upload_user_initials($user);
$uploadDirectory = __DIR__ . '/uploads';
$successMessage = null;
$errorMessage = null;
$uploadedUrl = null;

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['evidence'] ?? null;

    if (!is_array($file)) {
        $errorMessage = 'No se recibió ningún archivo.';
    } elseif (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errorMessage = 'PHP rechazó la carga con el código ' .
            (string) ($file['error'] ?? UPLOAD_ERR_NO_FILE) . '.';
    } else {
        $originalName = (string) ($file['name'] ?? 'archivo-sin-nombre');

        /*
         * Vulnerabilidad intencional del laboratorio:
         * - Solo se elimina la ruta aportada por el cliente.
         * - No se valida la extensión.
         * - No se verifica el contenido real.
         * - Se conserva el nombre y la extensión original.
         * - El archivo se guarda dentro del webroot.
         * - Un archivo PHP puede ejecutarse al abrir su URL.
         */
        $storedName = basename($originalName);
        $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;
        $reportedMime = (string) ($file['type'] ?? 'application/octet-stream');
        $fileSize = (int) ($file['size'] ?? 0);
        $wasSuccessful = move_uploaded_file(
            (string) ($file['tmp_name'] ?? ''),
            $destination
        );

        if ($wasSuccessful) {
            @chmod($destination, 0644);
            $uploadedUrl = upload_public_url($storedName);
            $successMessage = 'El archivo fue almacenado dentro del directorio público.';
        } else {
            $errorMessage = 'No fue posible mover el archivo al directorio uploads.';
        }

        $statement = $pdo->prepare(
            'INSERT INTO upload_attempts (
                user_id,
                original_name,
                stored_name,
                reported_mime,
                file_size,
                was_successful,
                public_url,
                ip_address,
                user_agent
            ) VALUES (
                :user_id,
                :original_name,
                :stored_name,
                :reported_mime,
                :file_size,
                :was_successful,
                :public_url,
                :ip_address,
                :user_agent
            )'
        );

        $statement->execute([
            ':user_id' => (int) ($user['id'] ?? 0),
            ':original_name' => $originalName,
            ':stored_name' => $storedName,
            ':reported_mime' => $reportedMime,
            ':file_size' => $fileSize,
            ':was_successful' => $wasSuccessful,
            ':public_url' => $wasSuccessful ? $uploadedUrl : null,
            ':ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            ':user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
        ]);
    }
}

$uploadedFiles = [];

foreach (glob($uploadDirectory . '/*') ?: [] as $path) {
    if (!is_file($path)) {
        continue;
    }

    $fileName = basename($path);

    if ($fileName === '.gitkeep') {
        continue;
    }

    $uploadedFiles[] = [
        'name' => $fileName,
        'size' => filesize($path) ?: 0,
        'url' => upload_public_url($fileName),
        'modified_at' => date('Y-m-d H:i:s', filemtime($path) ?: time()),
    ];
}

usort(
    $uploadedFiles,
    static fn (array $left, array $right): int =>
        strcmp((string) $right['modified_at'], (string) $left['modified_at'])
);

$recentAttempts = $pdo->query(
    'SELECT
        upload_attempts.original_name,
        upload_attempts.reported_mime,
        upload_attempts.file_size,
        upload_attempts.was_successful,
        upload_attempts.public_url,
        upload_attempts.uploaded_at,
        users.username
     FROM upload_attempts
     LEFT JOIN users
        ON users.id = upload_attempts.user_id
     ORDER BY upload_attempts.uploaded_at DESC
     LIMIT 15'
)->fetchAll();

function upload_pg_boolean(mixed $value): bool
{
    return $value === true
        || $value === 1
        || $value === '1'
        || $value === 't'
        || $value === 'true';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo 07 — Carga de archivos</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');

            if (savedTheme) {
                document.documentElement.dataset.theme = savedTheme;
            }
        })();
    </script>

    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>

<body data-page="module-upload">
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

            <a class="sidebar-link active" href="/upload.php">
                <span class="sidebar-link-icon">
                    <?= icon('upload', 18) ?>
                </span>
                Carga de archivos
            </a>

            <a class="sidebar-link" href="/evidence.php">
                <span class="sidebar-link-icon">
                    <?= icon('activity', 18) ?>
                </span>
                Registrar evidencia
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= htmlspecialchars($userInitials) ?>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars(
                            (string) ($user['full_name'] ?? 'Usuario')
                        ) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            (string) ($user['role'] ?? 'user')
                        ) ?>
                    </span>
                </div>
            </div>

            <a class="logout-link" href="/logout.php">Cerrar sesión</a>
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
                    <h1>Carga de archivos</h1>
                    <p>Validación incompleta y almacenamiento dentro del directorio público</p>
                </div>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    Riesgo crítico
                </div>

                <button
                    type="button"
                    class="icon-button"
                    data-theme-toggle
                    aria-label="Cambiar tema"
                >
                    <span data-theme-moon><?= icon('moon', 19) ?></span>
                    <span data-theme-sun hidden><?= icon('sun', 19) ?></span>
                </button>
            </div>
        </header>

        <main class="content">
            <div class="breadcrumb">
                <a href="/dashboard.php">Panel</a>
                <span>/</span>
                <span>Módulo 07</span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">MÓDULO 07 · OWASP A04</div>
                <h1>Carga insegura de evidencias</h1>
                <p>
                    La aplicación confía en el nombre y tipo reportados por el cliente,
                    conserva la extensión y almacena el archivo dentro del webroot.
                </p>
            </section>

            <div class="warning-box">
                <strong>Vulnerabilidad intencional:</strong>
                no existe lista permitida de extensiones, análisis de contenido,
                renombrado seguro ni aislamiento fuera del directorio público.
            </div>

            <section class="module-layout">
                <article class="panel-card">
                    <div class="section-heading">
                        <div>
                            <h2>Subir evidencia</h2>
                            <p>El archivo conservará su nombre y extensión originales.</p>
                        </div>
                    </div>

                    <form
                        method="post"
                        action="/upload.php"
                        enctype="multipart/form-data"
                    >
                        <div class="form-group">
                            <label for="evidence">Seleccionar archivo</label>

                            <input
                                class="input-control"
                                id="evidence"
                                name="evidence"
                                type="file"
                                required
                            >
                        </div>

                        <button class="primary-button" type="submit">
                            Cargar archivo <span>→</span>
                        </button>
                    </form>

                    <?php if ($successMessage !== null): ?>
                        <div class="alert alert-success" style="margin-top: 22px;">
                            <?= htmlspecialchars($successMessage) ?>

                            <?php if ($uploadedUrl !== null): ?>
                                <br>
                                URL pública:
                                <a
                                    href="<?= htmlspecialchars($uploadedUrl) ?>"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    <?= htmlspecialchars($uploadedUrl) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($errorMessage !== null): ?>
                        <div class="alert alert-error" style="margin-top: 22px;">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 26px;">
                        <h3>Archivos disponibles por URL</h3>

                        <div class="project-module-table-wrapper">
                            <table class="info-table">
                                <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Tamaño</th>
                                    <th>Última modificación</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php if ($uploadedFiles === []): ?>
                                    <tr>
                                        <td colspan="4">Aún no se han cargado archivos.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($uploadedFiles as $uploadedFile): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars((string) $uploadedFile['name']) ?>
                                        </td>
                                        <td>
                                            <?= number_format((int) $uploadedFile['size']) ?> bytes
                                        </td>
                                        <td>
                                            <?= htmlspecialchars((string) $uploadedFile['modified_at']) ?>
                                        </td>
                                        <td>
                                            <a
                                                href="<?= htmlspecialchars((string) $uploadedFile['url']) ?>"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                Abrir URL
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>

                <aside class="module-status-card">
                    <h3>Flujo vulnerable</h3>

                    <div class="status-list">
                        <div class="status-item">
                            <span class="status-circle">1</span>
                            Archivo recibido por POST
                        </div>

                        <div class="status-item">
                            <span class="status-circle">2</span>
                            Extensión sin validar
                        </div>

                        <div class="status-item">
                            <span class="status-circle">3</span>
                            Nombre original conservado
                        </div>

                        <div class="status-item">
                            <span class="status-circle">4</span>
                            Archivo servido desde webroot
                        </div>
                    </div>

                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Punto de prueba</h3>

                    <table class="info-table">
                        <tr>
                            <th>Ruta</th>
                            <td><code>/upload.php</code></td>
                        </tr>
                        <tr>
                            <th>Campo</th>
                            <td><code>evidence</code></td>
                        </tr>
                        <tr>
                            <th>Destino</th>
                            <td><code>/uploads/</code></td>
                        </tr>
                        <tr>
                            <th>Ejecución PHP</th>
                            <td>Habilitada</td>
                        </tr>
                    </table>
                </aside>
            </section>

            <section class="panel-card" style="margin-top: 22px;">
                <div class="section-heading">
                    <div>
                        <h2>Historial reciente</h2>
                        <p>Registro de las últimas cargas realizadas en el laboratorio.</p>
                    </div>
                </div>

                <div class="project-module-table-wrapper">
                    <table class="info-table">
                        <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Archivo</th>
                            <th>MIME reportado</th>
                            <th>Tamaño</th>
                            <th>Resultado</th>
                            <th>Fecha</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php if ($recentAttempts === []): ?>
                            <tr>
                                <td colspan="6">No hay intentos registrados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($recentAttempts as $attempt): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars((string) ($attempt['username'] ?? 'desconocido')) ?>
                                </td>
                                <td>
                                    <?php if (
                                        upload_pg_boolean($attempt['was_successful'])
                                        && !empty($attempt['public_url'])
                                    ): ?>
                                        <a
                                            href="<?= htmlspecialchars((string) $attempt['public_url']) ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <?= htmlspecialchars((string) $attempt['original_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars((string) $attempt['original_name']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) $attempt['reported_mime']) ?>
                                </td>
                                <td>
                                    <?= number_format((int) $attempt['file_size']) ?> bytes
                                </td>
                                <td>
                                    <?= upload_pg_boolean($attempt['was_successful'])
                                        ? 'Cargado'
                                        : 'Fallido' ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) $attempt['uploaded_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <footer class="footer">
            Service Desk FIIS · Laboratorio local y autorizado
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
</body>
</html>
