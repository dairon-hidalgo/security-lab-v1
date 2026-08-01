(() => {
    const root = document.documentElement;
    const savedTheme = localStorage.getItem("securityLabTheme");

    if (savedTheme === "dark" || savedTheme === "light") {
        root.dataset.theme = savedTheme;
    } else if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
        root.dataset.theme = "dark";
    }

    function currentTheme() {
        return root.dataset.theme === "dark" ? "dark" : "light";
    }

    function updateThemeButtons() {
        const dark = currentTheme() === "dark";

        document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
            button.setAttribute(
                "aria-label",
                dark ? "Cambiar a modo claro" : "Cambiar a modo oscuro"
            );

            button.querySelector("[data-theme-label]")?.replaceChildren(
                document.createTextNode(dark ? "Modo claro" : "Modo oscuro")
            );

            const moon = button.querySelector("[data-theme-moon]");
            const sun = button.querySelector("[data-theme-sun]");

            if (moon) {
                moon.hidden = dark;
            }

            if (sun) {
                sun.hidden = !dark;
            }
        });
    }

    function toggleTheme() {
        const nextTheme = currentTheme() === "dark" ? "light" : "dark";

        root.dataset.theme = nextTheme;
        localStorage.setItem("securityLabTheme", nextTheme);
        updateThemeButtons();

        showToast(
            nextTheme === "dark"
                ? "Modo oscuro activado"
                : "Modo claro activado",
            "success"
        );
    }

    document.addEventListener("click", (event) => {
        const themeButton = event.target.closest("[data-theme-toggle]");

        if (themeButton) {
            toggleTheme();
        }

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

    function ensureFloatingThemeButton() {
        if (
            !document.querySelector("[data-theme-toggle]") &&
            document.body.classList.contains("login-body")
        ) {
            const button = document.createElement("button");

            button.type = "button";
            button.className = "floating-theme-button";
            button.dataset.themeToggle = "";
            button.innerHTML = `
                <span data-theme-moon>☾</span>
                <span data-theme-sun hidden>☀</span>
            `;

            document.body.appendChild(button);
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
    ensureFloatingThemeButton();
    updateThemeButtons();

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