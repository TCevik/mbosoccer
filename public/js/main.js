// public/js/main.js
// Algemene interactie: mobiel menu, zoekfilter in tabellen en localStorage.

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobiel navigatiemenu in- en uitklappen
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('show');
        });
    }

    // 2. Zoeken en filteren in tabellen (bijv. op wedstrijden of leden)
    const searchInputs = document.querySelectorAll('[data-table-filter]');
    searchInputs.forEach(input => {
        const table = document.querySelector(input.getAttribute('data-table-filter'));
        if (!table) return;

        input.addEventListener('input', () => {
            const query = input.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            // Verberg rijen die de zoekterm niet bevatten
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // 3. LocalStorage: bewaar het e-mailadres bij inloggen zodat je het niet steeds opnieuw hoeft te typen
    const loginEmailInput = document.getElementById('email');
    if (loginEmailInput) {
        // Als er eerder een e-mail is opgeslagen, vul deze automatisch in
        const savedEmail = localStorage.getItem('mbo_saved_email');
        if (savedEmail && !loginEmailInput.value) {
            loginEmailInput.value = savedEmail;
        }

        // Zodra de gebruiker zijn e-mail wijzigt, bewaren we die in localStorage
        loginEmailInput.addEventListener('change', () => {
            localStorage.setItem('mbo_saved_email', loginEmailInput.value);
        });
    }
});
