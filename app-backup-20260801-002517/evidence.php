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

    <title>Registro de evidencias — FIIS Security Lab</title>

    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/styles.css">
</head>

<body data-page="evidence">
<div class="app-shell">
    <aside class="sidebar no-print">
        <div class="sidebar-brand">
            <div class="sidebar-logo">FI</div>

            <div>
                <strong>Service Desk FIIS</strong>
                <span>Security Lab · V1</span>
            </div>
        </div>

        <div class="sidebar-section-title">Documentación</div>

        <nav class="sidebar-nav">
            <a class="sidebar-link" href="/dashboard.php">
                <span class="sidebar-link-icon">
                    <?= icon('home', 18) ?>
                </span>

                Panel principal
            </a>

            <a class="sidebar-link" href="/project.php">
                <span class="sidebar-link-icon">
                    <?= icon('shield', 18) ?>
                </span>

                Información
            </a>

            <a class="sidebar-link active" href="/evidence.php">
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
        </div>
    </aside>

    <section class="main-area">
        <header class="top-header no-print">
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
                    <h1>Registro de evidencias</h1>

                    <p>
                        Ficha técnica para documentar cada prueba
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

        <main class="content evidence-content">
            <section class="evidence-document">
                <header class="evidence-document-header">
                    <img
                        src="/assets/logo.svg"
                        alt="FIIS Security Lab"
                    >

                    <div>
                        <span>Ficha técnica de prueba</span>
                        <h1>Registro de evidencia</h1>
                    </div>

                    <div class="evidence-document-code">
                        V1-SEC
                    </div>
                </header>

                <form id="evidenceForm">
                    <section class="evidence-section">
                        <h2>1. Identificación</h2>

                        <div class="evidence-grid">
                            <div class="form-group">
                                <label for="module">Módulo evaluado</label>

                                <select
                                    id="module"
                                    name="module"
                                    class="input-control evidence-input"
                                >
                                    <?php foreach ($modules as $module): ?>
                                        <option
                                            value="<?= htmlspecialchars(
                                                $module['number'] . ' - ' .
                                                $module['title']
                                            ) ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $module['number'] . ' - ' .
                                                $module['title']
                                            ) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="date">Fecha y hora</label>

                                <input
                                    class="input-control evidence-input"
                                    type="datetime-local"
                                    id="date"
                                    name="date"
                                >
                            </div>

                            <div class="form-group">
                                <label for="responsible">Responsable</label>

                                <input
                                    class="input-control evidence-input"
                                    type="text"
                                    id="responsible"
                                    name="responsible"
                                    value="<?= htmlspecialchars(
                                        (string) ($user['full_name'] ?? '')
                                    ) ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="tool">Herramienta utilizada</label>

                                <input
                                    class="input-control evidence-input"
                                    type="text"
                                    id="tool"
                                    name="tool"
                                    placeholder="Ejemplo: navegador, Burp Suite o ZAP"
                                >
                            </div>
                        </div>
                    </section>

                    <section class="evidence-section">
                        <h2>2. Datos de la prueba</h2>

                        <div class="form-group">
                            <label for="objective">Objetivo</label>

                            <textarea
                                id="objective"
                                name="objective"
                                rows="3"
                                placeholder="Describe qué se desea comprobar."
                            ></textarea>
                        </div>

                        <div class="form-group">
                            <label for="procedure">Procedimiento ejecutado</label>

                            <textarea
                                id="procedure"
                                name="procedure"
                                rows="5"
                                placeholder="Registra de manera ordenada los pasos realizados."
                            ></textarea>
                        </div>

                        <div class="evidence-grid">
                            <div class="form-group">
                                <label for="expected">Resultado esperado</label>

                                <textarea
                                    id="expected"
                                    name="expected"
                                    rows="4"
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <label for="obtained">Resultado obtenido</label>

                                <textarea
                                    id="obtained"
                                    name="obtained"
                                    rows="4"
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="evidence-section">
                        <h2>3. Evidencias y conclusión</h2>

                        <div class="form-group">
                            <label for="screenshots">
                                Capturas o archivos relacionados
                            </label>

                            <textarea
                                id="screenshots"
                                name="screenshots"
                                rows="3"
                                placeholder="Ejemplo: evidencia-01-login.png"
                            ></textarea>
                        </div>

                        <div class="form-group">
                            <label for="observations">Observaciones</label>

                            <textarea
                                id="observations"
                                name="observations"
                                rows="4"
                            ></textarea>
                        </div>

                        <div class="evidence-grid">
                            <div class="form-group">
                                <label for="result">Resultado final</label>

                                <select
                                    id="result"
                                    name="result"
                                    class="input-control evidence-input"
                                >
                                    <option value="Vulnerabilidad confirmada">
                                        Vulnerabilidad confirmada
                                    </option>

                                    <option value="Prueba inconclusa">
                                        Prueba inconclusa
                                    </option>

                                    <option value="No reproducida">
                                        No reproducida
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="severity">Severidad observada</label>

                                <select
                                    id="severity"
                                    name="severity"
                                    class="input-control evidence-input"
                                >
                                    <option value="Crítica">Crítica</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Media">Media</option>
                                    <option value="Baja">Baja</option>
                                    <option value="Informativa">Informativa</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <div class="evidence-actions no-print">
                        <button
                            type="button"
                            class="back-button"
                            id="saveDraft"
                        >
                            Guardar borrador
                        </button>

                        <button
                            type="button"
                            class="back-button"
                            id="clearDraft"
                        >
                            Limpiar
                        </button>

                        <button
                            type="button"
                            class="primary-button evidence-print-button"
                            id="printEvidence"
                        >
                            Imprimir evidencia
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </section>
</div>

<script src="/assets/app.js"></script>

<script>
const evidenceForm = document.getElementById('evidenceForm');
const storageKey = 'fiisSecurityEvidenceDraft';

function getFormData() {
    return Object.fromEntries(new FormData(evidenceForm).entries());
}

function applyFormData(data) {
    Object.entries(data).forEach(([name, value]) => {
        const field = evidenceForm.elements.namedItem(name);

        if (field) {
            field.value = value;
        }
    });
}

document.getElementById('saveDraft').addEventListener('click', () => {
    localStorage.setItem(
        storageKey,
        JSON.stringify(getFormData())
    );

    showToast('Borrador guardado en este navegador', 'success');
});

document.getElementById('clearDraft').addEventListener('click', () => {
    const confirmation = window.confirm(
        '¿Deseas limpiar toda la ficha de evidencia?'
    );

    if (!confirmation) {
        return;
    }

    evidenceForm.reset();
    localStorage.removeItem(storageKey);
    showToast('Ficha limpiada', 'info');
});

document.getElementById('printEvidence').addEventListener('click', () => {
    window.print();
});

const savedDraft = localStorage.getItem(storageKey);

if (savedDraft) {
    try {
        applyFormData(JSON.parse(savedDraft));
    } catch (error) {
        localStorage.removeItem(storageKey);
    }
}

const dateInput = document.getElementById('date');

if (!dateInput.value) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    dateInput.value = now.toISOString().slice(0, 16);
}
</script>
</body>
</html>