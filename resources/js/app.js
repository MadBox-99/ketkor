import './bootstrap';
import './../css/app.css';
import { Datepicker, Input, initTE } from "tw-elements";
initTE({ Datepicker, Input });
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import.meta.glob([
    '../img/**',
]);

const menuButton = document.getElementById('menu-toggle');
const mobileMenu = document.getElementById('mobile-menu');
// Add a click event listener to the button
menuButton.addEventListener('click', () => {
    // Toggle the 'hidden' class on the mobile menu
    mobileMenu.classList.toggle('hidden');
});


const profileButton = document.getElementById('user-menu-button');
const profileDropdown = document.getElementById('profile-dropdown');
profileButton.addEventListener('click', () => {
    // Toggle the 'hidden' class on the profile dropdown
    profileDropdown.classList.toggle('hidden');

    // Toggle the 'aria-expanded' attribute
    const isProfileExpanded = profileDropdown.classList.contains('hidden') ? 'false' : 'true';
    profileButton.setAttribute('aria-expanded', isProfileExpanded);
});

// Add a click event listener to the profile button
document.addEventListener('livewire:navigated', () => {
    const menuButton = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    // Add a click event listener to the button
    menuButton.addEventListener('click', () => {
        // Toggle the 'hidden' class on the mobile menu
        mobileMenu.classList.toggle('hidden');
    });


    const profileButton = document.getElementById('user-menu-button');
    const profileDropdown = document.getElementById('profile-dropdown');
    profileButton.addEventListener('click', () => {
        // Toggle the 'hidden' class on the profile dropdown
        profileDropdown.classList.toggle('hidden');

        // Toggle the 'aria-expanded' attribute
        const isProfileExpanded = profileDropdown.classList.contains('hidden') ? 'false' : 'true';
        profileButton.setAttribute('aria-expanded', isProfileExpanded);
    });
});
function toggle() {

}

Livewire.start();
