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

$moduleIcon = $moduleIcon ?? 'shield';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo <?= htmlspecialchars($moduleNumber) ?> — <?= htmlspecialchars($moduleTitle) ?></title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');
            if (savedTheme) document.documentElement.dataset.theme = savedTheme;
        })();
    </script>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>
<body data-page="module-pending">
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="sidebar-logo">FI</div><div><strong>Service Desk FIIS</strong><span>Security Lab · V1</span></div></div>
        <div class="sidebar-section-title">Navegación</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/dashboard.php"><span class="sidebar-link-icon"><?= icon('home', 18) ?></span>Panel principal</a>
            <a class="sidebar-link active" href="<?= htmlspecialchars((string) ($_SERVER['REQUEST_URI'] ?? '#')) ?>"><span class="sidebar-link-icon"><?= icon($moduleIcon, 18) ?></span><?= htmlspecialchars($moduleTitle) ?></a>
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-user"><div class="sidebar-user-avatar"><?= htmlspecialchars($userInitials ?: 'U') ?></div><div><strong><?= htmlspecialchars((string) ($user['full_name'] ?? 'Usuario')) ?></strong><span><?= htmlspecialchars((string) ($user['role'] ?? 'user')) ?></span></div></div>
            <a class="logout-link" href="/logout.php">Cerrar sesión</a>
        </div>
    </aside>
    <section class="main-area">
        <header class="top-header">
            <div class="top-header-left"><button type="button" class="icon-button mobile-menu-button" data-sidebar-toggle aria-label="Abrir menú"><?= icon('menu', 20) ?></button><div class="page-title"><h1><?= htmlspecialchars($moduleTitle) ?></h1><p><?= htmlspecialchars($moduleDescription) ?></p></div></div>
            <div class="header-actions"><div class="environment-badge"><span class="environment-dot"></span>Pendiente</div><button type="button" class="icon-button" data-theme-toggle aria-label="Cambiar tema"><span data-theme-moon><?= icon('moon', 19) ?></span><span data-theme-sun hidden><?= icon('sun', 19) ?></span></button></div>
        </header>
        <main class="content">
            <div class="breadcrumb"><a href="/dashboard.php">Panel</a><span>/</span><span>Módulo <?= htmlspecialchars($moduleNumber) ?></span></div>
            <section class="module-hero"><div class="module-hero-number">MÓDULO <?= htmlspecialchars($moduleNumber) ?></div><h1><?= htmlspecialchars($moduleTitle) ?></h1><p><?= htmlspecialchars($moduleDescription) ?></p></section>
            <section class="panel-card">
                <div class="section-heading"><div><h2>Módulo pendiente de implementación</h2><p>La página mantiene el diseño del proyecto y será completada en el siguiente paso.</p></div></div>
                <div class="alert alert-success">Los módulos 01 al 06 permanecen disponibles y no fueron reemplazados por esta página.</div>
                <a class="back-button" href="/dashboard.php">Volver al panel principal</a>
            </section>
        </main>
        <footer class="footer">Service Desk FIIS · Laboratorio local y autorizado</footer>
    </section>
</div>
<script src="/assets/app.js"></script>
</body>
</html>
