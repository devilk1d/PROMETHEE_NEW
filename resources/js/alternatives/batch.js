// Variables to store configuration
let alternativeCounter = 0;
let criterias = [];

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadConfiguration();
    initializeBatchAlternatives();
});

function loadConfiguration() {
    const configElement = document.getElementById('js-config');
    if (configElement) {
        alternativeCounter = parseInt(configElement.dataset.initialCount) || 0;
        try {
            criterias = JSON.parse(configElement.dataset.criterias) || [];
        } catch (e) {
            console.error('Error parsing criterias data:', e);
            criterias = [];
        }
    }
}

function initializeBatchAlternatives() {
    // Add event listener for add row button
    const addButton = document.getElementById('addAlternativeRow');
    if (addButton) {
        addButton.addEventListener('click', addAlternativeRow);
    }
    
    // Add event listeners for existing delete buttons
    setupDeleteButtons();
}

function addAlternativeRow() {
    const tbody = document.querySelector('#alternativesTable tbody');
    if (!tbody) return;
    
    const newRow = document.createElement('tr');
    newRow.className = 'alternative-row new-row';
    alternativeCounter++;
    
    // Generate criteria inputs
    let criteriaInputs = '';
    criterias.forEach(criteria => {
        criteriaInputs += `
            <td>
                <input type="number" step="0.01" class="form-control table-input criteria-input" 
                       name="alternatives[${alternativeCounter}][criteria_values][${criteria.id}]" 
                       value="0" placeholder="0.00">
                <input type="hidden" name="alternatives[${alternativeCounter}][selected_criteria][${criteria.id}]" value="1">
            </td>
        `;
    });
    
    newRow.innerHTML = `
        <td>
            <input type="text" class="form-control table-input" name="alternatives[${alternativeCounter}][name]" placeholder="Alternative name" required>
        </td>
        <td>
            <textarea class="form-control table-textarea" name="alternatives[${alternativeCounter}][description]" rows="2" placeholder="Description"></textarea>
        </td>
        ${criteriaInputs}
        <td>
            <button type="button" class="btn-icon btn-icon-danger delete-row" title="Delete Row">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    // Setup delete button for the new row
    setupDeleteButtons(newRow);
}

function setupDeleteButtons(container = document) {
    const deleteButtons = container.querySelectorAll('.delete-row');
    
    deleteButtons.forEach(button => {
        // Remove existing listener to prevent duplicates
        button.removeEventListener('click', handleDeleteRow);
        // Add new listener
        button.addEventListener('click', handleDeleteRow);
    });
}

function handleDeleteRow(event) {
    const button = event.currentTarget;
    const row = button.closest('tr');
    const idInput = row.querySelector('input[name*="[id]"]');
    
    // If it's an existing alternative (has ID), mark for deletion
    if (idInput && idInput.value) {
        const form = document.getElementById('alternativesBatchForm');
        if (form) {
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_alternatives[]';
            deleteInput.value = idInput.value;
            form.appendChild(deleteInput);
        }
    }
    
    // Remove the row
    row.remove();
}

// Remove the global function as we're not using onclick anymore