/**
 * assets/js/theme.js - CLRP Theme Controller
 * Handles global light/dark mode toggling and state persistence.
 */

(function () {
    'use strict';

    const STORAGE_KEY = 'clrp_theme';

    /**
     * Get currently stored theme or default to 'dark'
     */
    function getStoredTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'dark';
    }

    /**
     * Set theme on root element and update localStorage
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);
        updateToggleButtons(theme);
    }

    /**
     * Update all toggle buttons in the DOM to reflect current theme state
     */
    function updateToggleButtons(theme) {
        const buttons = document.querySelectorAll('.theme-toggle-btn');
        const isDark = theme === 'dark';

        buttons.forEach(btn => {
            const icon = btn.querySelector('.theme-icon') || btn.querySelector('i');
            const label = btn.querySelector('.theme-label');

            if (icon) {
                // In dark mode show sun icon (click for light mode)
                // In light mode show moon icon (click for dark mode)
                icon.className = isDark ? 'bi bi-sun-fill theme-icon' : 'bi bi-moon-fill theme-icon';
            }

            if (label) {
                label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            }

            const titleText = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
            btn.setAttribute('title', titleText);
            btn.setAttribute('aria-label', titleText);
        });
    }

    /**
     * Toggle between dark and light mode
     */
    window.toggleTheme = function () {
        const currentTheme = getStoredTheme();
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
    };

    // Apply saved theme immediately on DOM load to sync UI buttons
    document.addEventListener('DOMContentLoaded', function () {
        const activeTheme = getStoredTheme();
        applyTheme(activeTheme);

        // Delegate click handler for any theme toggle button
        document.body.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('.theme-toggle-btn');
            if (toggleBtn) {
                e.preventDefault();
                window.toggleTheme();
            }
        });
    });
})();
