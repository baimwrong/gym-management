/**
 * Gym Management System - Offline Client Application Logic
 */

document.addEventListener('DOMContentLoaded', function () {
    // Auto focus search / scan inputs on attendance kiosk or member scan pages
    const scanInput = document.getElementById('kiosk-scan-input');
    if (scanInput) {
        scanInput.focus();
        // Keep focus on window click for continuous rapid scanning
        document.addEventListener('click', function(e) {
            if (!['INPUT', 'BUTTON', 'A', 'SELECT'].includes(e.target.tagName)) {
                scanInput.focus();
            }
        });
    }

    // Auto dismiss standard alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // Tooltips activation
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

/**
 * Trigger print dialog
 */
function printPage() {
    window.print();
}
