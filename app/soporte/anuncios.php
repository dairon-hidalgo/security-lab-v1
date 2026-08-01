<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();

/*
 * Cookie ficticia del sistema.
 * Se deja accesible desde JavaScript de forma intencional para demostrar
 * el impacto de una vulnerabilidad XSS DOM sin utilizar datos reales.
 */
if (!isset($_COOKIE['LAB_XSS_DEMO'])) {
    $demoCookieValue = sprintf(
        'dom-user-%d-%s',
        (int) ($user['id'] ?? 0),
        bin2hex(random_bytes(4))
    );

    setcookie('LAB_XSS_DEMO', $demoCookieValue, [
        'expires' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => false,
        'samesite' => 'Lax',
    ]);

    $_COOKIE['LAB_XSS_DEMO'] = $demoCookieValue;
}

$databaseError = null;
$recentCaptures = [];

try {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS xss_dom_captures (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
            cookie_name VARCHAR(80) NOT NULL,
            cookie_value VARCHAR(255) NOT NULL,
            source_hash TEXT NULL,
            page_url TEXT NULL,
            ip_address VARCHAR(64) NULL,
            user_agent TEXT NULL,
            captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $recentCaptures = $pdo->query(
        'SELECT
            xss_dom_captures.id,
            xss_dom_captures.cookie_name,
            xss_dom_captures.cookie_value,
            xss_dom_captures.source_hash,
            xss_dom_captures.page_url,
            xss_dom_captures.ip_address,
            xss_dom_captures.captured_at,
            users.username
         FROM xss_dom_captures
         LEFT JOIN users ON users.id = xss_dom_captures.user_id
         ORDER BY xss_dom_captures.captured_at DESC
         LIMIT 15'
    )->fetchAll();
} catch (Throwable $exception) {
    $databaseError = $exception->getMessage();
}

function short_dom_value(string $value, int $limit = 78): string
{
    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, $limit - 3) . '...';
}

$pageTitle = 'Anuncios';
$activeUrl = '/soporte/anuncios';
$pageHeading = 'Anuncios';
$pageSubtitle = 'Atención al usuario';
$environmentLabel = 'Mesa de servicio';

require __DIR__ . '/../includes/header.php';
?>

<style>
    .dom-source-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 11px;
        align-items: end;
    }

    .dom-example-list {
        display: grid;
        gap: 10px;
    }

    .dom-example-button {
        width: 100%;
        padding: 13px 14px;
        border: 1px solid var(--border);
        border-radius: 11px;
        background: var(--surface-soft);
        color: var(--text);
        text-align: left;
        cursor: pointer;
    }

    .dom-example-button:hover {
        border-color: var(--accent-500);
    }

    .dom-example-button strong {
        display: block;
        margin-bottom: 5px;
    }

    .dom-example-button code {
        display: block;
        overflow: hidden;
        color: var(--text-muted);
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dom-sink {
        min-height: 105px;
        padding: 18px;
        margin-top: 15px;
        border: 1px dashed var(--border);
        border-radius: 12px;
        background: var(--surface-soft);
        overflow-wrap: anywhere;
    }

    .dom-proof {
        padding: 15px;
        margin-top: 15px;
        border-left: 4px solid var(--accent-600);
        border-radius: 10px;
        background: rgba(37, 99, 235, 0.08);
    }

    .dom-cookie-box {
        padding: 14px;
        margin-top: 12px;
        border-radius: 10px;
        background: #07111f;
        color: #b9f6ca;
        font-family: Consolas, "Courier New", monospace;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .dom-capture-status {
        margin-top: 12px;
        color: var(--text-soft);
        font-size: 13px;
    }

    @media (max-width: 760px) {
        .dom-source-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Anuncios</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">ATENCIÓN AL USUARIO</div>

    <h1>Visor de anuncios basado en el fragmento URL</h1>

    <p>
        El navegador lee el contenido situado después del símbolo
        <code>#</code> y lo inserta directamente mediante
        <code>innerHTML</code>. El servidor no procesa ese valor.
    </p>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Contenido del fragmento</h2>

                <p>
                    El valor se procesa únicamente en el navegador.
                </p>
            </div>
        </div>

        <div class="dom-source-row">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="dom-source">
                    Texto, HTML o demostración controlada
                </label>

                <input
                    class="input-control"
                    type="text"
                    id="dom-source"
                    autocomplete="off"
                    placeholder="Ejemplo: Hola desde el fragmento"
                >
            </div>

            <button
                type="button"
                class="primary-button"
                id="render-dom-source"
            >
                Procesar contenido
                <span>→</span>
            </button>
        </div>

        <div class="dom-proof" id="dom-proof">
            Aún no se ha ejecutado una demostración.
        </div>

        <div class="dom-sink" id="dom-sink">
            El contenido procesado aparecerá aquí.
        </div>

        <div class="dom-cookie-box" id="dom-cookie-view">
            document.cookie: cargando…
        </div>

        <div
            class="dom-capture-status"
            id="dom-capture-status"
            aria-live="polite"
        ></div>
    </article>

    <aside class="module-status-card">
        <h3>Ejemplos de contenido</h3>

        <div class="dom-example-list">
            <button
                type="button"
                class="dom-example-button"
                id="dom-example-text"
            >
                <strong>Texto normal</strong>
                <code>Hola desde Service Desk FIIS</code>
            </button>

            <button
                type="button"
                class="dom-example-button"
                id="dom-example-html"
            >
                <strong>HTML interpretado</strong>
                <code>&lt;strong&gt;Contenido HTML&lt;/strong&gt;</code>
            </button>

            <button
                type="button"
                class="dom-example-button"
                id="dom-example-xss"
            >
                <strong>Captura local de cookie ficticia</strong>
                <code>&lt;img onerror="captureDemoCookie()"&gt;</code>
            </button>
        </div>

        <div class="status-list" style="margin-top: 18px;">
            <div class="status-item">
                <span class="status-circle">1</span>
                Fuente: <code>location.hash</code>
            </div>

            <div class="status-item">
                <span class="status-circle">2</span>
                Sink: <code>innerHTML</code>
            </div>

            <div class="status-item">
                <span class="status-circle">3</span>
                Destino: punto de captura local
            </div>
        </div>
    </aside>
</section>

<section class="panel-card" style="margin-top: 21px;">
    <div class="section-heading">
        <div>
            <h2>Capturas locales recientes</h2>

            <p>
                Solamente se admite la cookie ficticia
                <code>LAB_XSS_DEMO</code>.
            </p>
        </div>
    </div>

    <?php if ($databaseError !== null): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($databaseError) ?>
        </div>
    <?php elseif ($recentCaptures === []): ?>
        <div class="alert alert-info">
            Todavía no se registraron capturas.
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="info-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Cookie ficticia</th>
                    <th>Fragmento</th>
                    <th>Fecha</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($recentCaptures as $capture): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars((string) $capture['id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                (string) ($capture['username'] ?? 'desconocido')
                            ) ?>
                        </td>

                        <td>
                            <code>
                                <?= htmlspecialchars(
                                    (string) $capture['cookie_name']
                                    . '='
                                    . (string) $capture['cookie_value']
                                ) ?>
                            </code>
                        </td>

                        <td title="<?= htmlspecialchars((string) ($capture['source_hash'] ?? '')) ?>">
                            <?= htmlspecialchars(
                                short_dom_value(
                                    (string) ($capture['source_hash'] ?? '')
                                )
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars((string) $capture['captured_at']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script>
    (() => {
        const sourceInput = document.getElementById('dom-source');
        const sink = document.getElementById('dom-sink');
        const proof = document.getElementById('dom-proof');
        const cookieView = document.getElementById('dom-cookie-view');
        const captureStatus = document.getElementById('dom-capture-status');

        const normalExample = 'Hola desde Service Desk FIIS';
        const htmlExample = '<strong>HTML interpretado por innerHTML</strong>';
        const xssExample = '<img src=x onerror="captureDemoCookie();document.getElementById(\'dom-proof\').textContent=\'XSS DOM ejecutado y cookie ficticia enviada al punto de captura local\';this.remove()">';

        function decodeFragment() {
            const raw = window.location.hash.slice(1);

            if (!raw) {
                return '';
            }

            try {
                return decodeURIComponent(raw);
            } catch (error) {
                return raw;
            }
        }

        function renderFromFragment() {
            const source = decodeFragment();

            sourceInput.value = source;

            if (source === '') {
                sink.textContent = 'El contenido procesado aparecerá aquí.';
                return;
            }

            /*
             * La fuente location.hash se inserta sin sanitización.
             */
            sink.innerHTML = source;
        }

        function setExample(value) {
            window.location.hash = encodeURIComponent(value);
            renderFromFragment();
        }

        function readDemoCookie() {
            const match = document.cookie.match(
                /(?:^|;\s*)LAB_XSS_DEMO=([^;]*)/
            );

            return match ? decodeURIComponent(match[1]) : '';
        }

        window.captureDemoCookie = async function captureDemoCookie() {
            const demoValue = readDemoCookie();

            if (!demoValue) {
                captureStatus.textContent =
                    'No se encontró la cookie ficticia LAB_XSS_DEMO.';
                return;
            }

            captureStatus.textContent = 'Registrando captura local…';

            try {
                const response = await fetch('/api/capturar-cookie-dom', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cookie: `LAB_XSS_DEMO=${demoValue}`,
                        sourceHash: decodeFragment(),
                        pageUrl: window.location.href
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No fue posible registrar la captura.');
                }

                captureStatus.textContent =
                    'Cookie ficticia capturada localmente. Recarga la página para verla en la tabla.';
            } catch (error) {
                captureStatus.textContent = `Error: ${error.message}`;
            }
        };

        document
            .getElementById('render-dom-source')
            .addEventListener('click', () => {
                setExample(sourceInput.value);
            });

        sourceInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                setExample(sourceInput.value);
            }
        });

        document
            .getElementById('dom-example-text')
            .addEventListener('click', () => setExample(normalExample));

        document
            .getElementById('dom-example-html')
            .addEventListener('click', () => setExample(htmlExample));

        document
            .getElementById('dom-example-xss')
            .addEventListener('click', () => setExample(xssExample));

        window.addEventListener('hashchange', renderFromFragment);

        cookieView.textContent = `document.cookie: ${document.cookie || '(vacío)'}`;
        renderFromFragment();
    })();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
