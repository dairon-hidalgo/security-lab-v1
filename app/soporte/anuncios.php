<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();

/*
 * Preferencias del usuario almacenadas en el navegador.
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
} catch (Throwable $exception) {
    $databaseError = $exception->getMessage();
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
    <h1>Visor de anuncios</h1>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Vista previa del anuncio</h2>
            </div>
        </div>

        <div class="dom-source-row">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="dom-source">
                    Contenido del anuncio
                </label>

                <input
                    class="input-control"
                    type="text"
                    id="dom-source"
                    autocomplete="off"
                    placeholder="Ejemplo: Recordatorio de mantenimiento programado"
                >
            </div>

            <button
                type="button"
                class="primary-button"
                id="render-dom-source"
            >
                Vista previa
                <span>→</span>
            </button>
        </div>

        <div class="dom-sink" id="dom-sink">
            La vista previa aparecerá aquí.
        </div>

        <div
            class="dom-capture-status"
            id="dom-capture-status"
            aria-live="polite"
        ></div>
    </article>
</section>

<script>
    (() => {
        const sourceInput = document.getElementById('dom-source');
        const sink = document.getElementById('dom-sink');
        const captureStatus = document.getElementById('dom-capture-status');

        const textExample = 'Recordatorio de mantenimiento este fin de semana';
        const htmlExample = '<h3>Aviso</h3><p>Detalle del aviso para todos los usuarios.</p>';

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
                sink.textContent = 'La vista previa aparecerá aquí.';
                return;
            }

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
                    'No se encontraron preferencias para registrar.';
                return;
            }

            captureStatus.textContent = 'Registrando preferencias…';

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
                    throw new Error(data.message || 'No fue posible registrar las preferencias.');
                }

                captureStatus.textContent =
                    'Preferencias registradas correctamente.';
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
            .addEventListener('click', () => setExample(textExample));

        document
            .getElementById('dom-example-html')
            .addEventListener('click', () => setExample(htmlExample));

        window.addEventListener('hashchange', renderFromFragment);
        renderFromFragment();
    })();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
