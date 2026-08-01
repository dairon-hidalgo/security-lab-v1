<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

const MAX_UPLOAD_BYTES = 2097152;

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

function upload_storage_directory(): string
{
    return getenv('UPLOAD_STORAGE_PATH') ?: '/var/www/storage/uploads';
}

function upload_download_url(string $storedName): string
{
    return '/download.php?file=' . rawurlencode($storedName);
}

function allowed_upload_types(): array
{
    return [
        'pdf' => ['application/pdf'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'txt' => ['text/plain'],
    ];
}

function upload_pg_boolean(mixed $value): bool
{
    return $value === true
        || $value === 1
        || $value === '1'
        || $value === 't'
        || $value === 'true';
}

$userInitials = upload_user_initials($user);
$uploadDirectory = upload_storage_directory();
$successMessage = null;
$errorMessage = null;
$uploadedUrl = null;

if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true)) {
    throw new RuntimeException(
        'No fue posible preparar el almacenamiento seguro.'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['evidence'] ?? null;
    $submittedToken = isset($_POST['csrf_token'])
        ? (string) $_POST['csrf_token']
        : null;

    $originalName = 'archivo-sin-nombre';
    $storedName = '(rechazado)';
    $detectedMime = 'application/octet-stream';
    $fileSize = 0;
    $wasSuccessful = false;

    if (!csrf_token_is_valid($submittedToken)) {
        http_response_code(400);
        $errorMessage = 'La solicitud no es válida. Actualiza la página.';
    } elseif (!is_array($file)) {
        $errorMessage = 'No se recibió ningún archivo.';
    } elseif (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errorMessage = 'La carga no pudo ser procesada.';
    } else {
        $originalName = substr(
            basename((string) ($file['name'] ?? 'archivo-sin-nombre')),
            0,
            255
        );

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $fileSize = (int) ($file['size'] ?? 0);
        $extension = strtolower(
            pathinfo($originalName, PATHINFO_EXTENSION)
        );

        if ($fileSize <= 0 || $fileSize > MAX_UPLOAD_BYTES) {
            $errorMessage =
                'El archivo debe tener un tamaño mayor que 0 y no superar 2 MB.';
        } elseif (!array_key_exists($extension, allowed_upload_types())) {
            $errorMessage =
                'Extensión rechazada. Solo se permiten PDF, PNG, JPG y TXT.';
        } elseif (!is_uploaded_file($temporaryPath)) {
            $errorMessage = 'El origen temporal del archivo no es válido.';
        } else {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMime = (string) $fileInfo->file($temporaryPath);

            if (
                !in_array(
                    $detectedMime,
                    allowed_upload_types()[$extension],
                    true
                )
            ) {
                $errorMessage =
                    'El contenido real del archivo no coincide con su extensión.';
            } else {
                $normalizedExtension = $extension === 'jpeg'
                    ? 'jpg'
                    : $extension;

                $storedName =
                    bin2hex(random_bytes(16))
                    . '.'
                    . $normalizedExtension;

                $destination =
                    $uploadDirectory
                    . DIRECTORY_SEPARATOR
                    . $storedName;

                $wasSuccessful = move_uploaded_file(
                    $temporaryPath,
                    $destination
                );

                if ($wasSuccessful) {
                    chmod($destination, 0640);
                    $uploadedUrl = upload_download_url($storedName);
                    $successMessage =
                        'El archivo fue validado, renombrado y almacenado '
                        . 'fuera del directorio público.';
                } else {
                    $errorMessage =
                        'No fue posible almacenar el archivo de forma segura.';
                }
            }
        }
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
        ':reported_mime' => $detectedMime,
        ':file_size' => $fileSize,
        ':was_successful' => $wasSuccessful,
        ':public_url' => $wasSuccessful ? $uploadedUrl : null,
        ':ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        ':user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
    ]);
}

$uploadedFiles = [];

$storedRows = $pdo->query(
    'SELECT DISTINCT ON (stored_name)
        stored_name,
        original_name,
        reported_mime,
        file_size,
        uploaded_at
     FROM upload_attempts
     WHERE was_successful = TRUE
       AND stored_name <> \'\'
       AND stored_name <> \'(rechazado)\'
     ORDER BY stored_name, uploaded_at DESC'
)->fetchAll();

foreach ($storedRows as $row) {
    $storedName = (string) $row['stored_name'];
    $path = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;

    if (!is_file($path)) {
        continue;
    }

    $uploadedFiles[] = [
        'name' => (string) $row['original_name'],
        'size' => filesize($path) ?: (int) $row['file_size'],
        'url' => upload_download_url($storedName),
        'modified_at' => date(
            'Y-m-d H:i:s',
            filemtime($path) ?: time()
        ),
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
                <span>Security Lab · V2</span>
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
                    <p>Validación estricta y almacenamiento fuera del directorio público</p>
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
                <h1>Carga segura de evidencias</h1>
                <p>
                    La V2 valida extensión, tamaño y MIME real, genera un nombre
                    aleatorio y almacena el archivo fuera del webroot.
                </p>
            </section>

            <div class="alert alert-success">
                <strong>Controles aplicados:</strong>
                token CSRF, límite de 2 MB, lista de tipos permitidos,
                validación MIME, nombre aleatorio y descarga forzada.
            </div>

            <section class="module-layout">
                <article class="panel-card">
                    <div class="section-heading">
                        <div>
                            <h2>Subir evidencia</h2>
                            <p>Formatos permitidos: PDF, PNG, JPG y TXT; máximo 2 MB.</p>
                        </div>
                    </div>

                    <form
                        method="post"
                        action="/upload.php"
                        enctype="multipart/form-data"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <div class="form-group">
                            <label for="evidence">Seleccionar archivo</label>

                            <input
                                class="input-control"
                                id="evidence"
                                name="evidence"
                                type="file"
                                accept=".pdf,.png,.jpg,.jpeg,.txt"
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
                                Descarga segura:
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
                        <h3>Evidencias almacenadas de forma segura</h3>

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
                                                Descargar
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
                    <h3>Flujo seguro</h3>

                    <div class="status-list">
                        <div class="status-item">
                            <span class="status-circle">1</span>
                            Archivo recibido por POST
                        </div>

                        <div class="status-item">
                            <span class="status-circle">2</span>
                            Extensión y MIME validados
                        </div>

                        <div class="status-item">
                            <span class="status-circle">3</span>
                            Nombre aleatorio generado
                        </div>

                        <div class="status-item">
                            <span class="status-circle">4</span>
                            Almacenado fuera del webroot
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
                            <td><code>/var/www/storage/uploads</code></td>
                        </tr>
                        <tr>
                            <th>Ejecución PHP</th>
                            <td>Deshabilitada</td>
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
                            <th>MIME detectado</th>
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
            Service Desk FIIS V2 · Carga segura de archivos
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
</body>
</html>
