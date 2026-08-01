<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();

$requestHeaders = [
    'Host' => $_SERVER['HTTP_HOST'] ?? '',
    'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'Accept' => $_SERVER['HTTP_ACCEPT'] ?? '',
    'Cookie' => $_SERVER['HTTP_COOKIE'] ?? '',
    'Remote address' => $_SERVER['REMOTE_ADDR'] ?? '',
];

$pageTitle = 'Sesión actual';
$activeUrl = '/sistema/sesion';
$pageHeading = 'Sesión actual';
$pageSubtitle = 'Información visible de la sesión';
$environmentLabel = 'Administración';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Sesión actual</span>
</div>

<section class="module-hero">
    <div class="module-hero-number">INFORMACIÓN HTTP</div>

    <h1>Sesión y solicitud actual</h1>

    <p>
        Vista de la cookie de sesión y de las cabeceras recibidas por
        Apache/PHP para la solicitud actual.
    </p>
</section>

<section class="module-layout">
    <article class="panel-card">
        <div class="section-heading">
            <div>
                <h2>Cookies visibles</h2>
                <p>Valores enviados en la solicitud actual.</p>
            </div>
        </div>

        <div class="project-module-table-wrapper">
            <table class="info-table">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Valor</th>
                </tr>
                </thead>

                <tbody>
                <?php if ($_COOKIE === []): ?>
                    <tr>
                        <td colspan="2">No se recibieron cookies.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($_COOKIE as $name => $value): ?>
                    <tr>
                        <td><code><?= htmlspecialchars((string) $name) ?></code></td>
                        <td><code><?= htmlspecialchars((string) $value) ?></code></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <aside class="module-status-card">
        <h3>Sesión PHP</h3>

        <table class="info-table">
            <tr>
                <th>Nombre</th>
                <td><code><?= htmlspecialchars(session_name()) ?></code></td>
            </tr>

            <tr>
                <th>ID</th>
                <td><code><?= htmlspecialchars(session_id()) ?></code></td>
            </tr>

            <tr>
                <th>Usuario</th>
                <td><?= htmlspecialchars((string) ($user['username'] ?? '')) ?></td>
            </tr>
        </table>
    </aside>
</section>

<section class="panel-card" style="margin-top: 22px;">
    <div class="section-heading">
        <div>
            <h2>Cabeceras de solicitud</h2>

            <p>Datos expuestos por el servidor para esta petición.</p>
        </div>
    </div>

    <div class="project-module-table-wrapper">
        <table class="info-table">
            <tbody>
            <?php foreach ($requestHeaders as $name => $value): ?>
                <tr>
                    <th><?= htmlspecialchars($name) ?></th>
                    <td><code><?= htmlspecialchars((string) $value) ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
