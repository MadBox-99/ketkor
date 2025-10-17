import "./../../vendor/power-components/livewire-powergrid/dist/tailwind.css";
import "./../../vendor/power-components/livewire-powergrid/dist/powergrid.js";

import "../css/app.css";
import.meta.glob(["../img/**"]);
import TomSelect from "tom-select";
window.TomSelect = TomSelect;

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

    const profileButton = document.getElementById("user-menu-button");
    const profileDropdown = document.getElementById("profile-dropdown");

    if (profileButton && profileDropdown) {
        // Remove existing listeners to prevent duplicates
        profileButton.replaceWith(profileButton.cloneNode(true));
        const newProfileButton = document.getElementById("user-menu-button");

        newProfileButton.addEventListener("click", () => {
            profileDropdown.classList.toggle("hidden");
            const isProfileExpanded = profileDropdown.classList.contains(
                "hidden"
            )
                ? "false"
                : "true";
            newProfileButton.setAttribute("aria-expanded", isProfileExpanded);
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function (event) {
            if (
                !newProfileButton.contains(event.target) &&
                !profileDropdown.contains(event.target)
            ) {
                profileDropdown.classList.add("hidden");
            }
        });
    }
}

// Initialize when Livewire loads (first time)
document.addEventListener("livewire:load", initializeNavigation);

// Reinitialize after Livewire navigation
document.addEventListener("livewire:navigated", initializeNavigation);
