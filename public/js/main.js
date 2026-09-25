// public/js/main.js
document.addEventListener('DOMContentLoaded', () => {
    // Mobiele navigatie togglen
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('show');
        });
    }

    // Tabel filter functionaliteit
    const searchInputs = document.querySelectorAll('[data-table-filter]');
    searchInputs.forEach(input => {
        const targetTableSelector = input.getAttribute('data-table-filter');
        const targetTable = document.querySelector(targetTableSelector);
        if (!targetTable) return;

        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const rows = targetTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });
});
