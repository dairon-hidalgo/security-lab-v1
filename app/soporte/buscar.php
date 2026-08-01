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
    .xss-example-list {
        display: grid;
        gap: 10px;
    }

    .xss-example-button {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid var(--border);
        border-radius: 11px;
        background: var(--surface-soft);
        color: var(--text);
        font: inherit;
        text-align: left;
        cursor: pointer;
        transition: border-color .18s ease, transform .18s ease;
    }

    .xss-example-button:hover {
        border-color: var(--accent-500);
        transform: translateY(-1px);
    }

    .xss-example-button strong,
    .xss-example-button code {
        display: block;
    }

    .xss-example-button strong {
        margin-bottom: 5px;
    }

    .xss-result {
        overflow: hidden;
        margin-top: 24px;
        border: 1px solid var(--border);
        border-radius: 13px;
        background: var(--surface-soft);
    }

    .xss-result-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 14px 17px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }

    .xss-result-body {
        min-height: 100px;
        padding: 22px;
        overflow-wrap: anywhere;
    }

    .xss-proof {
        margin-top: 16px;
        padding: 13px 15px;
        border: 1px dashed var(--accent-500);
        border-radius: 10px;
        background: var(--surface-soft);
        color: var(--text-soft);
        font-size: 13px;
    }

    .xss-cookie-output {
        min-height: 58px;
        padding: 13px;
        overflow-wrap: anywhere;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface-soft);
        color: var(--text);
        font-family: Consolas, "Courier New", monospace;
        font-size: 12px;
    }

    .xss-history-payload {
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
        El valor del parámetro <code>q</code> se inserta en la respuesta
        sin codificación de salida.
    </p>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Probar contenido reflejado</h2>

                <p>
                    La entrada viaja mediante GET y aparece en la misma respuesta.
                </p>
            </div>
        </div>

        <form method="get" action="/soporte/buscar">
            <div class="form-group">
                <label for="q">Texto o fragmento HTML</label>

                <input
                    class="input-control"
                    id="q"
                    name="q"
                    type="text"
                    value="<?= htmlspecialchars($payload, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                    required
                >
            </div>

            <button class="primary-button" type="submit">
                Reflejar contenido
                <span>→</span>
            </button>
        </form>

        <?php if ($wasSubmitted): ?>
            <div class="xss-result">
                <div class="xss-result-header">
                    <strong>Respuesta reflejada</strong>
                    <code>GET /soporte/buscar?q=...</code>
                </div>

                <div class="xss-result-body">
                    <?php
                    /*
                     * La entrada se imprime sin htmlspecialchars().
                     */
                    echo $payload;
                    ?>
                </div>
            </div>

            <div class="xss-proof" id="xss-proof">
                Estado de comprobación: el navegador todavía no ha modificado este texto.
            </div>
        <?php endif; ?>

        <div style="margin-top: 25px;">
            <h3>Intentos recientes</h3>

            <div class="project-module-table-wrapper">
                <table class="info-table">
                    <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Contenido recibido</th>
                        <th>IP</th>
                        <th>Fecha</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if ($recentAttempts === []): ?>
                        <tr>
                            <td colspan="4">Aún no se registraron intentos.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($recentAttempts as $attempt): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($attempt['username'] ?? 'desconocido')) ?></td>
                            <td class="xss-history-payload" title="<?= htmlspecialchars((string) $attempt['payload'], ENT_QUOTES, 'UTF-8') ?>">
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
        <h3>Ejemplos de consulta</h3>

        <div class="xss-example-list">
            <button
                class="xss-example-button"
                type="button"
                data-xss-example="Hola, Service Desk FIIS"
            >
                <strong>Texto normal</strong>
                <code>Hola, Service Desk FIIS</code>
            </button>

            <button
                class="xss-example-button"
                type="button"
                data-xss-example="&lt;strong&gt;HTML interpretado&lt;/strong&gt;"
            >
                <strong>HTML básico</strong>
                <code>&lt;strong&gt;HTML interpretado&lt;/strong&gt;</code>
            </button>

            <button
                class="xss-example-button"
                type="button"
                data-xss-example="&lt;img src=x onerror=&quot;setTimeout(()=>{document.getElementById('xss-proof').textContent='XSS Reflected ejecutado'},0);this.remove()&quot;&gt;"
            >
                <strong>Comprobación con evento</strong>
                <code>&lt;img src=x onerror=...&gt;</code>
            </button>

            <button
                class="xss-example-button"
                type="button"
                data-xss-example="&lt;script&gt;setTimeout(()=>{document.getElementById('xss-proof').textContent='JavaScript reflejado ejecutado'},0);&lt;/script&gt;"
            >
                <strong>Comprobación con script</strong>
                <code>&lt;script&gt;...&lt;/script&gt;</code>
            </button>
        </div>

        <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

        <h3>Punto de consulta</h3>

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
                <th>Salida</th>
                <td>Sin codificación</td>
            </tr>
        </table>

        <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

        <h3>Cookie visible desde JavaScript</h3>

        <div class="xss-cookie-output" id="xss-cookie-output">
            Cargando <code>document.cookie</code>...
        </div>
    </aside>
</section>

<script>
    const payloadInput = document.getElementById('q');

    document.querySelectorAll('[data-xss-example]').forEach((button) => {
        button.addEventListener('click', () => {
            payloadInput.value = button.dataset.xssExample || '';
            payloadInput.focus();
        });
    });

    const cookieOutput = document.getElementById('xss-cookie-output');

    if (cookieOutput) {
        cookieOutput.textContent = document.cookie || '(sin cookies accesibles)';
    }
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
