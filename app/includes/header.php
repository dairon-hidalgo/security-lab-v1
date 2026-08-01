<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_login();

$pageTitle = $pageTitle ?? 'Service Desk FIIS';
$activeUrl = $activeUrl ?? '/panel';
$pageHeading = $pageHeading ?? 'Service Desk FIIS';
$pageSubtitle = $pageSubtitle ?? '';
//$environmentLabel = $environmentLabel ?? 'Entorno de soporte';
$pageBodyClass = $pageBodyClass ?? '';
$mainClass = $mainClass ?? 'content';

$layoutUser = current_user();
$layoutUserInitials = current_user_initials($layoutUser);
$layoutConfig = app_config();
$layoutSections = $layoutConfig['sections'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Service Desk FIIS</title>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>
<body data-page="app<?= $pageBodyClass !== '' ? ' ' . htmlspecialchars($pageBodyClass) : '' ?>">
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">FI</div>
            <div>
                <strong>Service Desk FIIS</strong>
                <span>Soporte y mesa de ayuda</span>
            </div>
        </div>

        <?php foreach ($layoutSections as $section): ?>
            <div class="sidebar-section-title">
                <?= htmlspecialchars($section['label']) ?>
            </div>

            <nav class="sidebar-nav">
                <?php foreach ($section['items'] as $item): ?>
                    <a
                        class="sidebar-link<?= $item['url'] === $activeUrl ? ' active' : '' ?>"
                        href="<?= htmlspecialchars($item['url']) ?>"
                    >
                        <span class="sidebar-link-icon">
                            <?= icon($item['icon'], 18) ?>
                        </span>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endforeach; ?>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= htmlspecialchars($layoutUserInitials) ?>
                </div>
                <div>
                    <strong>
                        <?= htmlspecialchars(
                            (string) ($layoutUser['full_name'] ?? 'Usuario')
                        ) ?>
                    </strong>
                    <span>
                        <?= htmlspecialchars(
                            (string) ($layoutUser['role'] ?? 'user')
                        ) ?>
                    </span>
                </div>
            </div>

            <a class="logout-link" href="/logout">
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
                    <h1><?= htmlspecialchars($pageHeading) ?></h1>
                    <?php if ($pageSubtitle !== ''): ?>
                        <p><?= htmlspecialchars($pageSubtitle) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="header-actions">
                <div class="environment-badge">
                    <span class="environment-dot"></span>
                    <?= htmlspecialchars($environmentLabel) ?>
                </div>
            </div>
        </header>

        <main class="<?= htmlspecialchars($mainClass) ?>">
