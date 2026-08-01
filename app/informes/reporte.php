<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$config = app_config();

$pageTitle = 'Reporte de incidencia';
$activeUrl = '/informes/reporte';
$pageHeading = 'Reporte de incidencia';
$pageSubtitle = 'Registro oficial de incidencias del servicio';
$environmentLabel = 'Administración';

require __DIR__ . '/../includes/header.php';
?>

<style>
    .report-content {
        max-width: 960px;
    }

    .report-document {
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--surface);
    }

    .report-document-header {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 26px 30px;
        border-bottom: 1px solid var(--border);
        background: var(--surface-soft);
    }

    .report-document-header img {
        width: 52px;
        height: 52px;
    }

    .report-document-header span {
        display: block;
        color: var(--text-soft);
        font-size: 13px;
    }

    .report-document-header h1 {
        margin: 2px 0 0;
        font-size: 24px;
    }

    .report-document-code {
        margin-left: auto;
        padding: 9px 14px;
        border: 1px dashed var(--accent-500);
        border-radius: 9px;
        color: var(--accent-700);
        font-weight: 700;
        letter-spacing: .05em;
    }

    .report-section {
        padding: 24px 30px;
        border-bottom: 1px solid var(--border);
    }

    .report-section h2 {
        margin: 0 0 18px;
        font-size: 16px;
        color: var(--text-soft);
    }

    .report-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .report-input {
        margin-top: 6px;
    }

    .report-section textarea {
        width: 100%;
        padding: 12px 14px;
        resize: vertical;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        color: var(--text);
        font: inherit;
    }

    .report-section textarea:focus {
        outline: 3px solid color-mix(in srgb, var(--accent-500) 20%, transparent);
        border-color: var(--accent-500);
    }

    .report-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 30px;
        background: var(--surface-soft);
    }

    .back-button {
        padding: 11px 16px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        color: var(--text);
        font: inherit;
        cursor: pointer;
    }

    @media (max-width: 640px) {
        .report-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Reporte de incidencia</span>
</div>

<main class="content report-content">
    <section class="report-document">
        <header class="report-document-header">
            <img src="/assets/logo.svg" alt="Service Desk FIIS">

            <div>
                <span>Mesa de servicio FIIS</span>
                <h1>Informe de incidencia</h1>
            </div>

            <div class="report-document-code">
                REP-FIIS
            </div>
        </header>

        <form id="reportForm">
            <section class="report-section">
                <h2>1. Identificación</h2>

                <div class="report-grid">
                    <div class="form-group">
                        <label for="module">Área del servicio</label>

                        <select
                            id="module"
                            name="module"
                            class="input-control report-input"
                        >
                            <?php foreach ($config['scenarios'] as $area => $description): ?>
                                <option value="<?= htmlspecialchars($area . ' - ' . $description) ?>">
                                    <?= htmlspecialchars($area . ' - ' . $description) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Fecha y hora</label>

                        <input
                            class="input-control report-input"
                            type="datetime-local"
                            id="date"
                            name="date"
                        >
                    </div>

                    <div class="form-group">
                        <label for="responsible">Solicitante</label>

                        <input
                            class="input-control report-input"
                            type="text"
                            id="responsible"
                            name="responsible"
                            value="<?= htmlspecialchars(
                                (string) ($user['full_name'] ?? '')
                            ) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="tool">Canal de reporte</label>

                        <input
                            class="input-control report-input"
                            type="text"
                            id="tool"
                            name="tool"
                            placeholder="Ejemplo: teléfono, correo o portal"
                        >
                    </div>
                </div>
            </section>

            <section class="report-section">
                <h2>2. Descripción</h2>

                <div class="form-group">
                    <label for="objective">Asunto</label>

                    <textarea
                        id="objective"
                        name="objective"
                        rows="3"
                        placeholder="Resumen breve de la incidencia."
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="procedure">Descripción del problema</label>

                    <textarea
                        id="procedure"
                        name="procedure"
                        rows="5"
                        placeholder="Detalla los pasos y el comportamiento observado."
                    ></textarea>
                </div>

                <div class="report-grid">
                    <div class="form-group">
                        <label for="expected">Resultado esperado</label>

                        <textarea
                            id="expected"
                            name="expected"
                            rows="4"
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label for="obtained">Resultado observado</label>

                        <textarea
                            id="obtained"
                            name="obtained"
                            rows="4"
                        ></textarea>
                    </div>
                </div>
            </section>

            <section class="report-section">
                <h2>3. Resolución</h2>

                <div class="form-group">
                    <label for="screenshots">
                        Archivos adjuntos o capturas
                    </label>

                    <textarea
                        id="screenshots"
                        name="screenshots"
                        rows="3"
                        placeholder="Ejemplo: captura-pantalla.png"
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

                <div class="report-grid">
                    <div class="form-group">
                        <label for="result">Estado final</label>

                        <select
                            id="result"
                            name="result"
                            class="input-control report-input"
                        >
                            <option value="Resuelto">Resuelto</option>

                            <option value="En proceso">En proceso</option>

                            <option value="No aplica">No aplica</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="severity">Prioridad</label>

                        <select
                            id="severity"
                            name="severity"
                            class="input-control report-input"
                        >
                            <option value="Alta">Alta</option>
                            <option value="Media">Media</option>
                            <option value="Baja">Baja</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="report-actions no-print">
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
                    class="primary-button report-print-button"
                    id="printReport"
                >
                    Imprimir informe
                </button>
            </div>
        </form>
    </section>
</main>

<script>
    const reportForm = document.getElementById('reportForm');
    const storageKey = 'fiisIncidentReportDraft';

    function getFormData() {
        return Object.fromEntries(new FormData(reportForm).entries());
    }

    function applyFormData(data) {
        Object.entries(data).forEach(([name, value]) => {
            const field = reportForm.elements.namedItem(name);

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
            '¿Deseas limpiar el informe?'
        );

        if (!confirmation) {
            return;
        }

        reportForm.reset();
        localStorage.removeItem(storageKey);
        showToast('Informe limpiado', 'info');
    });

    document.getElementById('printReport').addEventListener('click', () => {
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

<?php require __DIR__ . '/../includes/footer.php'; ?>
