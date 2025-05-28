document.addEventListener('DOMContentLoaded', function() {
    // Form validation for criteria weight
    const weightInputs = document.querySelectorAll('input[name="weight"]');
    if (weightInputs) {
        weightInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 0) this.value = 0;
                if (this.value > 1) this.value = 1;
            });
        });
    }

    // Confirm before deleting
    const deleteForms = document.querySelectorAll('form[action*="destroy"]');
    if (deleteForms) {
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
        });
    }
});