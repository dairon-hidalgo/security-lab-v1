(() => {
    document.addEventListener("click", (event) => {
        const sidebarButton = event.target.closest("[data-sidebar-toggle]");

        if (sidebarButton) {
            document.body.classList.toggle("sidebar-open");
        }

        if (
            document.body.classList.contains("sidebar-open") &&
            event.target.matches(".sidebar-overlay")
        ) {
            document.body.classList.remove("sidebar-open");
        }
    });

    function ensureSidebarOverlay() {
        if (
            document.querySelector(".sidebar") &&
            !document.querySelector(".sidebar-overlay")
        ) {
            const overlay = document.createElement("div");
            overlay.className = "sidebar-overlay";
            document.body.appendChild(overlay);
        }
    }

    function ensureToastContainer() {
        let container = document.querySelector(".toast-container");

        if (!container) {
            container = document.createElement("div");
            container.className = "toast-container";
            container.setAttribute("aria-live", "polite");
            document.body.appendChild(container);
        }

        return container;
    }

    window.showToast = function showToast(message, type = "info") {
        const container = ensureToastContainer();
        const toast = document.createElement("div");

        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-indicator"></span>
            <span>${message}</span>
            <button type="button" aria-label="Cerrar">×</button>
        `;

        toast.querySelector("button").addEventListener("click", () => {
            toast.remove();
        });

        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add("toast-visible");
        });

        window.setTimeout(() => {
            toast.classList.remove("toast-visible");

            window.setTimeout(() => {
                toast.remove();
            }, 250);
        }, 3500);
    };

    ensureSidebarOverlay();

    const firstDashboardVisit = sessionStorage.getItem(
        "securityLabDashboardVisited"
    );

    if (
        document.body.dataset.page === "dashboard" &&
        firstDashboardVisit !== "true"
    ) {
        window.setTimeout(() => {
            showToast("Laboratorio cargado correctamente", "success");
        }, 450);

        sessionStorage.setItem("securityLabDashboardVisited", "true");
    }
})();
