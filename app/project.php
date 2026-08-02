<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/icons.php';

require_login();

$user = current_user();
$modules = require __DIR__ . '/config/modules.php';

$initial = strtoupper(
    substr((string) ($user['full_name'] ?? 'Usuario'), 0, 1)
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Información del proyecto — FIIS Security Lab</title>

    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>

<body data-page="project">
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">FI</div>

            <div>
                <strong>Service Desk FIIS</strong>
                <span>Security Lab · V2</span>
            </div>
        </div>

        <div class="sidebar-section-title">Proyecto</div>

        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/dashboard.php">
                <span class="sidebar-link-icon">
                    <?= icon('home', 18) ?>
                </span>

                Panel principal
            </a>

            <a class="sidebar-link active" href="/project.php">
                <span class="sidebar-link-icon">
                    <?= icon('shield', 18) ?>
                </span>

                Información
            </a>

            <a class="sidebar-link" href="/evidence.php">
                <span class="sidebar-link-icon">
                    <?= icon('activity', 18) ?>
                </span>

                Evidencias
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= htmlspecialchars($initial) ?>
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
                    <h1>Información del proyecto</h1>

                    <p>
                        Arquitectura, alcance y tecnologías del laboratorio
                    </p>
                </div>
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
        </header>

        <main class="content">
            <section class="project-cover">
                <img
                    src="/assets/logo.svg"
                    alt="FIIS Security Lab"
                    class="project-logo"
                >

                <div>
                    <span class="project-version">
                        Versión 1 · Aplicación vulnerable
                    </span>

                    <h1>
                        Laboratorio académico de seguridad web
                    </h1>

                    <p>
                        Aplicación construida para implementar, demostrar y
                        documentar escenarios de seguridad exclusivamente
                        dentro de un entorno local y autorizado.
                    </p>
                </div>
            </section>

            <section class="project-summary-grid">
                <article class="project-summary-card">
                    <span class="project-summary-icon">
                        <?= icon('code', 23) ?>
                    </span>

                    <strong>PHP 8.2</strong>
                    <p>Lógica del servidor y módulos web.</p>
                </article>

                <article class="project-summary-card">
                    <span class="project-summary-icon">
                        <?= icon('database', 23) ?>
                    </span>

                    <strong>PostgreSQL 16</strong>
                    <p>Persistencia de usuarios, tickets y evidencias.</p>
                </article>

                <article class="project-summary-card">
                    <span class="project-summary-icon">
                        <?= icon('terminal', 23) ?>
                    </span>

                    <strong>Docker Desktop</strong>
                    <p>Despliegue reproducible sobre Windows 11.</p>
                </article>

                <article class="project-summary-card">
                    <span class="project-summary-icon">
                        <?= icon('shield', 23) ?>
                    </span>

                    <strong><?= count($modules) ?> escenarios</strong>
                    <p>Módulos de prueba y documentación.</p>
                </article>
            </section>

            <section class="project-grid">
                <article class="panel-card">
                    <h2>Objetivo</h2>

                    <p>
                        Construir una aplicación web deliberadamente vulnerable
                        que permita ejecutar pruebas controladas, recopilar
                        evidencias y comprender el impacto de una implementación
                        insegura.
                    </p>

                    <h3>Alcance actual</h3>

                    <ul class="clean-list">
                        <li>Desarrollo de la versión V2 segura.</li>
                        <li>Ejecución únicamente en localhost.</li>
                        <li>Datos y credenciales completamente ficticios.</li>
                        <li>Documentación técnica y guía de pruebas.</li>
                        <li>Repositorio Git con historial de cambios.</li>
                    </ul>
                </article>

                <article class="panel-card">
                    <h2>Arquitectura de despliegue</h2>

                    <div class="architecture-flow">
                        <div class="architecture-node">
                            <span><?= icon('home', 22) ?></span>
                            <strong>Windows 11</strong>
                            <small>Navegador, Burp o ZAP</small>
                        </div>

                        <div class="architecture-arrow">→</div>

                        <div class="architecture-node">
                            <span><?= icon('code', 22) ?></span>
                            <strong>Apache + PHP 8.2</strong>
                            <small>Puerto local 8081</small>
                        </div>

                        <div class="architecture-arrow">→</div>

                        <div class="architecture-node">
                            <span><?= icon('database', 22) ?></span>
                            <strong>PostgreSQL 16</strong>
                            <small>Red interna de Docker</small>
                        </div>
                    </div>

                    <div class="project-address">
                        Dirección del laboratorio:
                        <code>http://localhost:8081</code>
                    </div>
                </article>
            </section>

            <section class="panel-card project-modules">
                <div class="section-heading">
                    <div>
                        <h2>Escenarios contemplados</h2>

                        <p>
                            Clasificación inicial de los módulos del proyecto.
                        </p>
                    </div>
                </div>

                <div class="project-module-table-wrapper">
                    <table class="info-table">
                        <thead>
                        <tr>
                            <th>N.º</th>
                            <th>Escenario</th>
                            <th>Riesgo</th>
                            <th>Referencia OWASP</th>
                            <th>Estado</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($modules as $module): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($module['number']) ?>
                                </td>

                                <td>
                                    <a href="<?= htmlspecialchars($module['url']) ?>">
                                        <?= htmlspecialchars($module['title']) ?>
                                    </a>
                                </td>

                                <td>
                                    <?= htmlspecialchars($module['risk']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($module['owasp']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($module['status']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <footer class="footer">
            FIIS Security Lab · Service Desk V1 · Entorno académico local
        </footer>
    </section>
</div>

<script src="/assets/app.js"></script>
</body>
</html>