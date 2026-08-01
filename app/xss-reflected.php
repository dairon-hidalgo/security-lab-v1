<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$pdo = db();

function xss_reflected_initials(array $user): string
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
$userInitials = xss_reflected_initials($user);

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo 08 — XSS Reflected</title>

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
</head>

<body data-page="module-xss-reflected">
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

            <a class="sidebar-link active" href="/xss-reflected.php">
                <span class="sidebar-link-icon">
                    <?= icon('code', 18) ?>
                </span>
                XSS Reflected
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
                    <h1>XSS Reflected</h1>
                    <p>Entrada recibida por URL y devuelta sin codificación de salida</p>
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
                    <span data-theme-moon><?= icon('moon', 19) ?></span>
                    <span data-theme-sun hidden><?= icon('sun', 19) ?></span>
                </button>
            </div>
        </header>

        <main class="content">
            <div class="breadcrumb">
                <a href="/dashboard.php">Panel</a>
                <span>/</span>
                <span>Módulo 08</span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">MÓDULO 08 · OWASP A03</div>
                <h1>Contenido reflejado por el servidor</h1>
                <p>
                    El valor del parámetro <code>q</code> se inserta en el HTML de
                    respuesta sin utilizar codificación ni sanitización.
                </p>
            </section>

            <div class="warning-box">
                <strong>Vulnerabilidad intencional:</strong>
                el bloque de resultado imprime la entrada del usuario directamente.
                Este escenario debe permanecer únicamente en <code>localhost</code>.
            </div>

            <section class="module-layout">
                <article class="panel-card">
                    <div class="section-heading">
                        <div>
                            <h2>Probar contenido reflejado</h2>
                            <p>La entrada viaja mediante GET y aparece en la misma respuesta.</p>
                        </div>
                    </div>

                    <form method="get" action="/xss-reflected.php">
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
                                <code>GET /xss-reflected.php?q=...</code>
                            </div>

                            <div class="xss-result-body">
                                <?php
                                /*
                                 * Vulnerabilidad intencional del laboratorio:
                                 * la entrada se imprime sin htmlspecialchars().
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
                    <h3>Ejemplos del laboratorio</h3>

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

                    <h3>Punto de prueba</h3>

                    <table class="info-table">
                        <tr><th>Método</th><td>GET</td></tr>
                        <tr><th>Parámetro</th><td><code>q</code></td></tr>
                        <tr><th>Ruta</th><td><code>/xss-reflected.php</code></td></tr>
                        <tr><th>Salida</th><td>Sin codificación</td></tr>
                    </table>

                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Cookie visible desde JavaScript</h3>
                    <div class="xss-cookie-output" id="xss-cookie-output">
                        Cargando <code>document.cookie</code>...
                    </div>
                </aside>
            </section>
        </main>

        <footer class="footer">
            Service Desk FIIS · Laboratorio local y autorizado
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
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
</body>
</html>
