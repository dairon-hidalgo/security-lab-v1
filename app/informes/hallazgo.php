<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

require_login();

$user = current_user();
$config = app_config();

$pageTitle = 'Ficha de hallazgo';
$activeUrl = '/informes/hallazgo';
$pageHeading = 'Ficha de hallazgo';
$pageSubtitle = 'Registro técnico de cada hallazgo';
$environmentLabel = 'Administración';

require __DIR__ . '/../includes/header.php';
?>

<style>
    .evidence-content {
        max-width: 960px;
    }

    .evidence-document {
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--surface);
    }

    .evidence-document-header {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 26px 30px;
        border-bottom: 1px solid var(--border);
        background: var(--surface-soft);
    }

    .evidence-document-header img {
        width: 52px;
        height: 52px;
    }

    .evidence-document-header span {
        display: block;
        color: var(--text-soft);
        font-size: 13px;
    }

    .evidence-document-header h1 {
        margin: 2px 0 0;
        font-size: 24px;
    }

    .evidence-document-code {
        margin-left: auto;
        padding: 9px 14px;
        border: 1px dashed var(--accent-500);
        border-radius: 9px;
        color: var(--accent-700);
        font-weight: 700;
        letter-spacing: .05em;
    }

    .evidence-section {
        padding: 24px 30px;
        border-bottom: 1px solid var(--border);
    }

    .evidence-section h2 {
        margin: 0 0 18px;
        font-size: 16px;
        color: var(--text-soft);
    }

    .evidence-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .evidence-input {
        margin-top: 6px;
    }

    .evidence-section textarea {
        width: 100%;
        padding: 12px 14px;
        resize: vertical;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        color: var(--text);
        font: inherit;
    }

    .evidence-section textarea:focus {
        outline: 3px solid color-mix(in srgb, var(--accent-500) 20%, transparent);
        border-color: var(--accent-500);
    }

    .evidence-actions {
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
        .evidence-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="breadcrumb">
    <a href="/panel">Panel</a>
    <span>/</span>
    <span>Ficha de hallazgo</span>
</div>

<main class="content evidence-content">
    <section class="evidence-document">
        <header class="evidence-document-header">
            <img src="/assets/logo.svg" alt="Service Desk FIIS">

            <div>
                <span>Ficha técnica de prueba</span>
                <h1>Registro de hallazgo</h1>
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
                        <label for="module">Área evaluada</label>

                        <select
                            id="module"
                            name="module"
                            class="input-control evidence-input"
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
                            <option value="Hallazgo confirmado">
                                Hallazgo confirmado
                            </option>

                            <option value="Prueba inconclusa">
                                Prueba inconclusa
                            </option>

                            <option value="No reproducido">
                                No reproducido
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
                    Imprimir ficha
                </button>
            </div>
        </form>
    </section>
</main>

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
            '¿Deseas limpiar toda la ficha de hallazgo?'
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

<?php require __DIR__ . '/../includes/footer.php'; ?>
