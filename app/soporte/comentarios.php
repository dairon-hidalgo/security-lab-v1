<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$pdo = db();

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

            header('Location: /soporte/comentarios?saved=1');
            exit;
        }
    }

    if ($action === 'reset' && (string) ($user['role'] ?? '') === 'admin') {
        $pdo->exec('TRUNCATE TABLE xss_stored_comments RESTART IDENTITY');
        header('Location: /soporte/comentarios?reset=1');
        exit;
    }
}

if (isset($_GET['saved'])) {
    $message = 'Comentario almacenado correctamente. Se mostrará en cada visita.';
    $messageClass = 'status-note status-note-success';
}

if (isset($_GET['reset'])) {
    $message = 'Los comentarios registrados fueron eliminados.';
    $messageClass = 'status-note status-note-success';
}

$comments = $pdo->query(
    'SELECT id, author_name, content, ip_address, created_at
     FROM xss_stored_comments
     ORDER BY created_at DESC, id DESC
     LIMIT 20'
)->fetchAll();

$pageTitle = 'Comentarios';
$activeUrl = '/soporte/comentarios';
$pageHeading = 'Comentarios';
$pageSubtitle = 'Atención al usuario';
$environmentLabel = 'Mesa de servicio';

require __DIR__ . '/../includes/header.php';
?>

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

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Comentarios</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">ATENCIÓN AL USUARIO</div>

    <h1>Comentarios del servicio</h1>
</section>

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
            </div>
        </div>

        <form class="stored-form" method="post" action="/soporte/comentarios">
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
                        onclick="return confirm('¿Eliminar los comentarios registrados?')"
                    >
                        Limpiar registros
                    </button>
                <?php endif; ?>
            </div>
        </form>

        <div style="margin-top: 26px;">
            <div class="section-heading">
                <div>
                    <h2>Comentarios almacenados</h2>

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
                             * Renderizado del cuerpo del comentario.
                             */
                            echo (string) $comment['content'];
                            ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </article>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
