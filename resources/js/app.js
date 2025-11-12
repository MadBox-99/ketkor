import "../css/app.css";
import.meta.glob(["../img/**"]);
import "./bootstrap";
// Dark mode functionality
function initializeDarkMode() {
    console.log("Initializing dark mode...");

    const themeToggleBtns = [
        document.getElementById("theme-toggle"),
        document.getElementById("theme-toggle-mobile"),
    ];
    const themeToggleDarkIcons = [
        document.getElementById("theme-toggle-dark-icon"),
        document.getElementById("theme-toggle-dark-icon-mobile"),
    ];
    const themeToggleLightIcons = [
        document.getElementById("theme-toggle-light-icon"),
        document.getElementById("theme-toggle-light-icon-mobile"),
    ];

    // Check for saved theme preference or default to light mode
    const theme = localStorage.getItem("color-theme");

    if (theme === "dark") {
        document.documentElement.classList.add("dark");
        themeToggleLightIcons.forEach((icon) =>
            icon?.classList.remove("hidden")
        );
        themeToggleDarkIcons.forEach((icon) => icon?.classList.add("hidden"));
    } else {
        document.documentElement.classList.remove("dark");
        themeToggleDarkIcons.forEach((icon) =>
            icon?.classList.remove("hidden")
        );
        themeToggleLightIcons.forEach((icon) => icon?.classList.add("hidden"));
    }

    // Toggle theme on button click
    themeToggleBtns.forEach((btn, index) => {
        if (btn) {
            // Remove any existing listeners by cloning the node
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            newBtn.addEventListener("click", function (e) {
                e.preventDefault();
                console.log("Theme toggle clicked!");

                // Re-query the icons after DOM manipulation
                const darkIcons = [
                    document.getElementById("theme-toggle-dark-icon"),
                    document.getElementById("theme-toggle-dark-icon-mobile"),
                ];
                const lightIcons = [
                    document.getElementById("theme-toggle-light-icon"),
                    document.getElementById("theme-toggle-light-icon-mobile"),
                ];

                // Toggle icons
                darkIcons.forEach((icon) => icon?.classList.toggle("hidden"));
                lightIcons.forEach((icon) => icon?.classList.toggle("hidden"));

                // Toggle dark class
                if (document.documentElement.classList.contains("dark")) {
                    console.log("Switching to light mode");
                    document.documentElement.classList.remove("dark");
                    localStorage.setItem("color-theme", "light");
                } else {
                    console.log("Switching to dark mode");
                    document.documentElement.classList.add("dark");
                    localStorage.setItem("color-theme", "dark");
                }
            });
        }
    });
}

// Function to initialize navigation
function initializeNavigation() {
    const menuButton = document.getElementById("menu-toggle");
    const mobileMenu = document.getElementById("mobile-menu");

    if (menuButton && mobileMenu) {
        // Remove existing listeners to prevent duplicates
        menuButton.replaceWith(menuButton.cloneNode(true));
        const newMenuButton = document.getElementById("menu-toggle");

        newMenuButton.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
    }
}

// Initialize immediately if DOM is already loaded
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
        initializeNavigation();
        initializeDarkMode();
    });
} else {
    // DOM already loaded
    initializeNavigation();
    initializeDarkMode();
}

// Initialize when Livewire loads (first time)
document.addEventListener("livewire:load", function () {
    initializeNavigation();
    initializeDarkMode();
});

// Reinitialize after Livewire navigation
document.addEventListener("livewire:navigated", function () {
    initializeNavigation();
    initializeDarkMode();
});
