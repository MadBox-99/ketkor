import "../css/app.css";
import.meta.glob(["../img/**"], { eager: true });
import "./bootstrap";

const THEME_TOGGLE_IDS = ["theme-toggle", "theme-toggle-mobile"];
const DARK_ICON_IDS = ["theme-toggle-dark-icon", "theme-toggle-dark-icon-mobile"];
const LIGHT_ICON_IDS = ["theme-toggle-light-icon", "theme-toggle-light-icon-mobile"];

function setHidden(ids, hidden) {
    ids.forEach((id) => document.getElementById(id)?.classList.toggle("hidden", hidden));
}

/**
 * Paints the current theme onto the document. Safe to call repeatedly: it derives
 * everything from localStorage rather than from whatever the DOM currently shows.
 */
function applyTheme() {
    const isDark = localStorage.getItem("color-theme") === "dark";

    document.documentElement.classList.toggle("dark", isDark);
    setHidden(DARK_ICON_IDS, isDark);
    setHidden(LIGHT_ICON_IDS, !isDark);
}

function toggleTheme() {
    const isDark = document.documentElement.classList.contains("dark");

    localStorage.setItem("color-theme", isDark ? "light" : "dark");
    applyTheme();
}

/*
| Listeners live on `document`, so they survive Livewire's wire:navigate DOM
| swaps and never need re-binding. That removes the whole class of duplicate
| listener bugs the previous cloneNode/replaceChild dance worked around.
*/
document.addEventListener("click", function (event) {
    const themeToggle = event.target.closest?.(
        THEME_TOGGLE_IDS.map((id) => `#${id}`).join(", ")
    );

    if (themeToggle) {
        event.preventDefault();
        toggleTheme();

        return;
    }

    if (event.target.closest?.("#menu-toggle")) {
        document.getElementById("mobile-menu")?.classList.toggle("hidden");
    }
});

// The theme must be re-painted after every navigation, because wire:navigate
// replaces the elements that carry the icon state.
document.addEventListener("livewire:navigated", applyTheme);

applyTheme();
