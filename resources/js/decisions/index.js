import { setupConfirmationModal } from '../confirmation-modal';

// Untuk semua form delete
document.addEventListener('DOMContentLoaded', function() {
    // Form delete biasa
    setupConfirmationModal({
        formSelector: '.delete-form',
        text: "This will permanently delete this decision analysis and all its results."
    });

    // Form delete di dropdown menu
    document.querySelectorAll('.dropdown-menu form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Delete Decision Analysis',
                text: "This will permanently delete this decision analysis and all its results.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary me-2',
                    cancelButton: 'btn btn-gray'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});