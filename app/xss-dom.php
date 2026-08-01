<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$pdo = db();

$databaseError = null;
$recentCaptures = [];

$nameParts = preg_split(
    '/\s+/',
    trim((string) ($user['full_name'] ?? 'Usuario'))
);

$userInitials = '';

foreach (array_slice($nameParts ?: ['U'], 0, 2) as $part) {
    $userInitials .= strtoupper(substr($part, 0, 1));
}

function short_dom_value(string $value, int $limit = 78): string
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

    <title>Módulo 10 — XSS DOM</title>

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
</head>

<body data-page="module-xss-dom">
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

            <a class="sidebar-link active" href="/xss-dom.php">
                <span class="sidebar-link-icon">
                    <?= icon('code', 18) ?>
                </span>

                XSS DOM
            </a>

            <a class="sidebar-link" href="/security-info.php">
                <span class="sidebar-link-icon">
                    <?= icon('cookie', 18) ?>
                </span>

                Cabeceras y cookies
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
                    <h1>XSS DOM</h1>

                    <p>
                        Manipulación insegura del DOM mediante el fragmento URL
                    </p>
                </div>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    Riesgo medio
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
                <span>Módulo 10</span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">
                    MÓDULO 10 · DOM-BASED XSS
                </div>

                <h1>Visor seguro basado en el fragmento URL</h1>

                <p>
                    El navegador lee el contenido situado después del símbolo
                    <code>#</code> y lo inserta directamente mediante
                    <code>innerHTML</code>. El servidor no procesa ese valor.
                </p>
            </section>

            <div class="warning-box">
                <strong>Control aplicado:</strong>
                la fuente <code>location.hash</code> se conserva como texto y no se
                interpreta como marcado ejecutable.
            </div>

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
                                Texto o payload para comprobar la neutralización
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
                        El contenido se mostrará como texto, sin ejecutarse.
                    </div>

                    <div class="dom-sink" id="dom-sink">
                        El contenido procesado aparecerá aquí.
                    </div>

                    <div class="dom-cookie-box" id="dom-cookie-view">
                        Cookie de sesión protegida con HttpOnly.
                    </div>

                    <div
                        class="dom-capture-status"
                        id="dom-capture-status"
                        aria-live="polite"
                    ></div>
                </article>

                <aside class="module-status-card">
                    <h3>Ejemplos del laboratorio</h3>

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
                            <strong>Payload XSS neutralizado</strong>
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
                            Sink: <code>textContent</code>
                        </div>

                        <div class="status-item">
                            <span class="status-circle">3</span>
                            Colector: deshabilitado
                        </div>
                    </div>
                </aside>
            </section>

            <section class="panel-card" style="margin-top: 21px;">
                <div class="section-heading">
                    <div>
                        <h2>Colector de cookies deshabilitado</h2>

                        <p>
                            La V2 no recopila cookies ni contenido desde el DOM.
                        </p>
                    </div>
                </div>

                <?php if ($databaseError !== null): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($databaseError) ?>
                    </div>
                <?php elseif ($recentCaptures === []): ?>
                    <div class="alert alert-info">
                        No se registran capturas en la versión segura.
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
        </main>
    </section>
</div>

<div class="sidebar-overlay" data-sidebar-overlay></div>

<script src="/assets/app.js"></script>

<script>
    (() => {
        const sourceInput = document.getElementById('dom-source');
        const sink = document.getElementById('dom-sink');
        const proof = document.getElementById('dom-proof');
        const cookieView = document.getElementById('dom-cookie-view');
        const captureStatus = document.getElementById('dom-capture-status');

        const normalExample = 'Hola desde Service Desk FIIS';
        const htmlExample = '<strong>Este HTML se mostrará como texto</strong>';
        const xssExample = '<img src=x onerror="alert(1)">';

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
            const source = decodeFragment().slice(0, 1000);

            sourceInput.value = source;

            if (source === '') {
                sink.textContent = 'El contenido procesado aparecerá aquí.';
                proof.textContent = 'No se recibió contenido.';
                return;
            }

            sink.textContent = source;
            proof.textContent = 'Contenido neutralizado: se usó textContent.';
        }

        function setExample(value) {
            window.location.hash = encodeURIComponent(value);
            renderFromFragment();
        }

        document
            .getElementById('render-dom-source')
            .addEventListener('click', () => {
                setExample(sourceInput.value.slice(0, 1000));
            });

        sourceInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                setExample(sourceInput.value.slice(0, 1000));
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

        cookieView.textContent =
            'Cookie de sesión protegida con HttpOnly y SameSite=Strict.';
        captureStatus.textContent =
            'El colector de cookies está deshabilitado en la V2.';

        renderFromFragment();
    })();
</script>
</body>
</html>
