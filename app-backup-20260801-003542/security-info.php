<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$parts = preg_split('/\s+/', trim((string) ($user['full_name'] ?? 'Usuario')));
$userInitials = '';
foreach (array_slice($parts ?: ['U'], 0, 2) as $part) {
    $userInitials .= strtoupper(substr($part, 0, 1));
}

$requestHeaders = [
    'Host' => $_SERVER['HTTP_HOST'] ?? '',
    'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'Accept' => $_SERVER['HTTP_ACCEPT'] ?? '',
    'Cookie' => $_SERVER['HTTP_COOKIE'] ?? '',
    'Remote address' => $_SERVER['REMOTE_ADDR'] ?? '',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cabeceras y cookies — Service Desk FIIS</title>
    <script>(()=>{const t=localStorage.getItem('securityLabTheme');if(t)document.documentElement.dataset.theme=t;})();</script>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/styles.css">
</head>
<body data-page="security-info">
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="sidebar-logo">FI</div><div><strong>Service Desk FIIS</strong><span>Security Lab · V1</span></div></div>
        <div class="sidebar-section-title">Navegación</div>
        <nav class="sidebar-nav"><a class="sidebar-link" href="/dashboard.php"><span class="sidebar-link-icon"><?= icon('home',18) ?></span>Panel principal</a><a class="sidebar-link active" href="/security-info.php"><span class="sidebar-link-icon"><?= icon('cookie',18) ?></span>Cabeceras y cookies</a></nav>
        <div class="sidebar-bottom"><div class="sidebar-user"><div class="sidebar-user-avatar"><?= htmlspecialchars($userInitials ?: 'U') ?></div><div><strong><?= htmlspecialchars((string)($user['full_name'] ?? 'Usuario')) ?></strong><span><?= htmlspecialchars((string)($user['role'] ?? 'user')) ?></span></div></div><a class="logout-link" href="/logout.php">Cerrar sesión</a></div>
    </aside>
    <section class="main-area">
        <header class="top-header"><div class="top-header-left"><button type="button" class="icon-button mobile-menu-button" data-sidebar-toggle aria-label="Abrir menú"><?= icon('menu',20) ?></button><div class="page-title"><h1>Cabeceras y cookies</h1><p>Información visible de la sesión del laboratorio</p></div></div><div class="header-actions"><div class="environment-badge"><span class="environment-dot"></span>localhost</div><button type="button" class="icon-button" data-theme-toggle aria-label="Cambiar tema"><span data-theme-moon><?= icon('moon',19) ?></span><span data-theme-sun hidden><?= icon('sun',19) ?></span></button></div></header>
        <main class="content">
            <div class="breadcrumb"><a href="/dashboard.php">Panel</a><span>/</span><span>Cabeceras y cookies</span></div>
            <section class="module-hero"><div class="module-hero-number">INFORMACIÓN HTTP</div><h1>Sesión y solicitud actual</h1><p>Vista académica de la cookie de sesión y de las cabeceras recibidas por Apache/PHP.</p></section>
            <section class="module-layout">
                <article class="panel-card"><div class="section-heading"><div><h2>Cookies visibles</h2><p>Valores enviados en la solicitud actual.</p></div></div><div class="project-module-table-wrapper"><table class="info-table"><thead><tr><th>Nombre</th><th>Valor</th></tr></thead><tbody><?php if ($_COOKIE === []): ?><tr><td colspan="2">No se recibieron cookies.</td></tr><?php endif; ?><?php foreach ($_COOKIE as $name => $value): ?><tr><td><code><?= htmlspecialchars((string)$name) ?></code></td><td><code><?= htmlspecialchars((string)$value) ?></code></td></tr><?php endforeach; ?></tbody></table></div></article>
                <aside class="module-status-card"><h3>Sesión PHP</h3><table class="info-table"><tr><th>Nombre</th><td><code><?= htmlspecialchars(session_name()) ?></code></td></tr><tr><th>ID</th><td><code><?= htmlspecialchars(session_id()) ?></code></td></tr><tr><th>Usuario</th><td><?= htmlspecialchars((string)($user['username'] ?? '')) ?></td></tr></table></aside>
            </section>
            <section class="panel-card" style="margin-top:22px;"><div class="section-heading"><div><h2>Cabeceras de solicitud</h2><p>Datos expuestos por el servidor para esta petición.</p></div></div><div class="project-module-table-wrapper"><table class="info-table"><tbody><?php foreach ($requestHeaders as $name => $value): ?><tr><th><?= htmlspecialchars($name) ?></th><td><code><?= htmlspecialchars((string)$value) ?></code></td></tr><?php endforeach; ?></tbody></table></div></section>
        </main>
        <footer class="footer">Service Desk FIIS · Laboratorio local y autorizado</footer>
    </section>
</div><script src="/assets/app.js"></script>
</body></html>
