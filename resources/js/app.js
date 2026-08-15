import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '@fullcalendar/core';
import '@fullcalendar/daygrid';
import 'daterangepicker/daterangepicker.js';

// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});