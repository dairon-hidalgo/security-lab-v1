<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS xss_reflected_attempts (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id),
        payload TEXT NOT NULL,
        ip_address VARCHAR(64),
        user_agent TEXT,
        requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

if (!isset($_COOKIE['LAB_XSS_DEMO'])) {
    setcookie(
        'LAB_XSS_DEMO',
        'fiis-user-' . (string) ($user['id'] ?? '0'),
        [
            'expires' => 0,
            'path' => '/',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax',
        ]
    );
}

$payload = (string) ($_GET['q'] ?? 'Hola, Service Desk FIIS');
$wasSubmitted = array_key_exists('q', $_GET);

if ($wasSubmitted) {
    $statement = $pdo->prepare(
        'INSERT INTO xss_reflected_attempts (
            user_id,
            payload,
            ip_address,
            user_agent
        ) VALUES (
            :user_id,
            :payload,
            :ip_address,
            :user_agent
        )'
    );

    $statement->execute([
        ':user_id' => (int) ($user['id'] ?? 0),
        ':payload' => $payload,
        ':ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        ':user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
    ]);
}

$recentAttempts = $pdo->query(
    'SELECT
        xss_reflected_attempts.payload,
        xss_reflected_attempts.ip_address,
        xss_reflected_attempts.requested_at,
        users.username
     FROM xss_reflected_attempts
     LEFT JOIN users
        ON users.id = xss_reflected_attempts.user_id
     ORDER BY xss_reflected_attempts.requested_at DESC
     LIMIT 8'
)->fetchAll();

$pageTitle = 'Búsqueda de ayuda';
$activeUrl = '/soporte/buscar';
$pageHeading = 'Búsqueda de ayuda';
$pageSubtitle = 'Buscador del centro de soporte';
$environmentLabel = 'Mesa de servicio';

require __DIR__ . '/../includes/header.php';
?>

<style>
    .search-result {
        overflow: hidden;
        margin-top: 24px;
        border: 1px solid var(--border);
        border-radius: 13px;
        background: var(--surface-soft);
    }

    .search-result-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 14px 17px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }

    .search-result-body {
        min-height: 100px;
        padding: 22px;
        overflow-wrap: anywhere;
    }

    .search-history-term {
        max-width: 430px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Búsqueda de ayuda</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">ATENCIÓN AL USUARIO</div>

    <h1>Búsqueda en el centro de soporte</h1>

    <p>
        Busca entre las guías, manuales y publicaciones del área de soporte.
    </p>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Buscar contenido</h2>

                <p>
                    Escribe el término que deseas localizar.
                </p>
            </div>
        </div>

        <form method="get" action="/soporte/buscar">
            <div class="form-group">
                <label for="q">Término de búsqueda</label>

                <input
                    class="input-control"
                    id="q"
                    name="q"
                    type="text"
                    value="<?= htmlspecialchars($payload, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Ejemplo: reseteo de contraseña"
                    autocomplete="off"
                    required
                >
            </div>

            <button class="primary-button" type="submit">
                Buscar
                <span>→</span>
            </button>
        </form>

        <?php if ($wasSubmitted): ?>
            <div class="search-result">
                <div class="search-result-header">
                    <strong>Resultado de la búsqueda</strong>
                    <code>GET /soporte/buscar?q=...</code>
                </div>

                <div class="search-result-body">
                    <?= $payload ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top: 25px;">
            <h3>Búsquedas recientes</h3>

            <div class="project-module-table-wrapper">
                <table class="info-table">
                    <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Término</th>
                        <th>IP</th>
                        <th>Fecha</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if ($recentAttempts === []): ?>
                        <tr>
                            <td colspan="4">Aún no se registraron búsquedas.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($recentAttempts as $attempt): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($attempt['username'] ?? 'desconocido')) ?></td>

                            <td
                                class="search-history-term"
                                title="<?= htmlspecialchars((string) $attempt['payload'], ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <code><?= htmlspecialchars((string) $attempt['payload'], ENT_QUOTES, 'UTF-8') ?></code>
                            </td>

                            <td><?= htmlspecialchars((string) $attempt['ip_address']) ?></td>

                            <td><?= htmlspecialchars((string) $attempt['requested_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </article>

    <aside class="module-status-card">
        <h3>Información</h3>

        <div class="status-list">
            <div class="status-item">
                <span class="status-circle">1</span>
                Búsqueda sobre el índice de ayuda
            </div>

            <div class="status-item">
                <span class="status-circle">2</span>
                Resultado mostrado en la página
            </div>

            <div class="status-item">
                <span class="status-circle">3</span>
                Historial registrado por consulta
            </div>
        </div>

        <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

        <table class="info-table">
            <tr>
                <th>Método</th>
                <td>GET</td>
            </tr>

            <tr>
                <th>Parámetro</th>
                <td><code>q</code></td>
            </tr>

            <tr>
                <th>Almacenamiento</th>
                <td>PostgreSQL</td>
            </tr>
        </table>
    </aside>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
