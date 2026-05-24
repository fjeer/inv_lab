import './bootstrap';

// Standard CSRF for jQuery AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
    }
});

// Global Function for AJAX Alerts
window.showAlert = (title, text, icon = 'success') => {
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonColor: '#3b82f6'
    });
};
