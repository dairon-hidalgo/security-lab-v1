<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$config = app_config();
$scenarioCount = count($config['scenarios']);

$pageTitle = 'Acerca del sistema';
$activeUrl = '/sistema/acerca';
$pageHeading = 'Acerca del sistema';
$pageSubtitle = 'Arquitectura, alcance y tecnologías';
$environmentLabel = 'Administración';

require __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Acerca del sistema</span>
</div>

<section class="project-cover">
    <img
        src="/assets/logo.svg"
        alt="Service Desk FIIS"
        class="project-logo"
    >

    <div>
        <span class="project-version">
            Service Desk FIIS · Versión 1
        </span>

        <h1>Mesa de ayuda interna</h1>

        <p>
            Aplicación web del área de soporte para la gestión de tickets,
            directorio de usuarios, documentación y registros de operación.
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
        <p>Persistencia de usuarios, tickets y registros.</p>
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

        <strong><?= $scenarioCount ?> áreas</strong>
        <p>Funcionalidades del servicio.</p>
    </article>
</section>

<section class="project-grid">
    <article class="panel-card">
        <h2>Objetivo</h2>

        <p>
            Proporcionar una plataforma interna de soporte que permita el
            registro de tickets, la consulta del directorio de usuarios y la
            atención de solicitudes del área técnica.
        </p>

        <h3>Alcance actual</h3>

        <ul class="clean-list">
            <li>Gestión de la cola de tickets.</li>
            <li>Directorio y consulta de usuarios.</li>
            <li>Documentación y recursos de soporte.</li>
            <li>Registro de accesos y operaciones.</li>
            <li>Repositorio Git con historial de cambios.</li>
        </ul>
    </article>

    <article class="panel-card">
        <h2>Arquitectura de despliegue</h2>

        <div class="architecture-flow">
            <div class="architecture-node">
                <span><?= icon('home', 22) ?></span>
                <strong>Windows 11</strong>
                <small>Navegador del área de TI</small>
            </div>

            <div class="architecture-arrow">→</div>

            <div class="architecture-node">
                <span><?= icon('code', 22) ?></span>
                <strong>Apache + PHP 8.2</strong>
                <small>Puerto local 8090</small>
            </div>

            <div class="architecture-arrow">→</div>

            <div class="architecture-node">
                <span><?= icon('database', 22) ?></span>
                <strong>PostgreSQL 16</strong>
                <small>Red interna de Docker</small>
            </div>
        </div>

        <div class="project-address">
            Dirección del servicio:
            <code>http://localhost:8090</code>
        </div>
    </article>
</section>

<section class="panel-card project-modules">
    <div class="section-heading">
        <div>
            <h2>Funcionalidades por área</h2>

            <p>Clasificación del servicio de soporte.</p>
        </div>
    </div>

    <div class="project-module-table-wrapper">
        <table class="info-table">
            <thead>
            <tr>
                <th>Área</th>
                <th>Descripción</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($config['scenarios'] as $area => $description): ?>
                <tr>
                    <td><?= htmlspecialchars($area) ?></td>
                    <td><?= htmlspecialchars($description) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
