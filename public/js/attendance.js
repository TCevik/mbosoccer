// public/js/attendance.js - AJAX aanwezigheid direct bijwerken zonder herladen
document.addEventListener('DOMContentLoaded', () => {
    const attendanceButtons = document.querySelectorAll('.btn-attendance');

    attendanceButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const matchId = btn.getAttribute('data-match-id');
            const status = btn.getAttribute('data-status');
            const parentContainer = btn.closest('.attendance-actions');

            try {
                btn.disabled = true;
                const formData = new FormData();
                formData.append('match_id', matchId);
                formData.append('status', status);

                const res = await fetch('/api/set_attendance.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    // Update active button state
                    const allInGroup = parentContainer.querySelectorAll('.btn-attendance');
                    allInGroup.forEach(b => {
                        b.classList.remove('btn-primary', 'btn-danger');
                        b.classList.add('btn-outline');
                    });

                    if (status === 'aanwezig') {
                        btn.classList.remove('btn-outline');
                        btn.classList.add('btn-primary');
                    } else if (status === 'afwezig') {
                        btn.classList.remove('btn-outline');
                        btn.classList.add('btn-danger');
                    }

                    const badge = parentContainer.querySelector('.status-indicator');
                    if (badge) {
                        badge.textContent = status === 'aanwezig' ? '✓ Aanwezig' : '✕ Afwezig';
                        badge.className = `status-indicator badge ${status === 'aanwezig' ? 'badge-success' : 'badge-danger'}`;
                    }
                } else {
                    alert(data.error || 'Er is een fout opgetreden.');
                }
            } catch (err) {
                console.error(err);
                alert('Kon status niet opslaan. Probeer opnieuw.');
            } finally {
                btn.disabled = false;
            }
        });
    });
});
