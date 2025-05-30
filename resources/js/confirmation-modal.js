export function setupConfirmationModal(options = {}) {
    const defaultOptions = {
        formSelector: '.delete-form',
        title: 'Are you sure?',
        text: "This action cannot be undone.",
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
    };

    const finalOptions = { ...defaultOptions, ...options };

    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll(finalOptions.formSelector);
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: finalOptions.title,
                    text: finalOptions.text,
                    icon: finalOptions.icon,
                    showCancelButton: true,
                    confirmButtonColor: finalOptions.confirmButtonColor,
                    cancelButtonColor: finalOptions.cancelButtonColor,
                    confirmButtonText: finalOptions.confirmButtonText,
                    cancelButtonText: finalOptions.cancelButtonText,
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
}