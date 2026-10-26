// public/js/attendance.js
// Regelt dat je op 'Wel' of 'Niet' aanwezig kunt klikken,
// waarna fetch() dit direct naar de server stuurt zonder de pagina te herladen.

document.addEventListener('DOMContentLoaded', () => {
    // Zoek alle aanwezigheidsknoppen op de pagina
    const buttons = document.querySelectorAll('.btn-attendance');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Haal het wedstrijd-ID en de gekozen status ('aanwezig' of 'afwezig') uit de knop
            const matchId = btn.getAttribute('data-match-id');
            const status = btn.getAttribute('data-status');
            const form = btn.closest('.attendance-actions');

            // Formulierdata klaarzetten om te versturen
            const data = new FormData();
            data.append('match_id', matchId);
            data.append('status', status);

            // POST-verzoek sturen naar het PHP-endpoint
            fetch('/api/set_attendance.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Stap 1: Knoppen resetten naar normale omranding
                    form.querySelectorAll('.btn-attendance').forEach(b => {
                        b.classList.remove('btn-primary', 'btn-danger');
                        b.classList.add('btn-outline');
                    });

                    // Stap 2: De aangeklikte knop actief kleuren (groen voor aanwezig, rood voor afwezig)
                    if (status === 'aanwezig') {
                        btn.classList.remove('btn-outline');
                        btn.classList.add('btn-primary');
                    } else {
                        btn.classList.remove('btn-outline');
                        btn.classList.add('btn-danger');
                    }

                    // Stap 3: Het tekst-labeltje bijwerken
                    const badge = form.querySelector('.status-indicator');
                    if (badge) {
                        badge.textContent = (status === 'aanwezig') ? '✓ Aanwezig' : '✕ Afwezig';
                        badge.className = 'status-indicator badge ' + (status === 'aanwezig' ? 'badge-success' : 'badge-danger');
                    }
                } else {
                    alert(result.error || 'Er ging iets fout.');
                }
            })
            .catch(() => {
                alert('Kon verbinding met de server niet tot stand brengen.');
            });
        });
    });
});
