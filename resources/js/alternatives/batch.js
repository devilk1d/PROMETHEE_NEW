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

    // Setup criteria toggle buttons for existing rows and headers
    setupCriteriaToggles();

    // Apply initial visibility based on header toggles
    applyInitialCriteriaVisibility();
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
        const isCriteriaSelected = document.getElementById(`criteriaToggle${criteria.id}`).checked;
        const displayStyle = isCriteriaSelected ? '' : 'display: none;';
        const selectedValue = isCriteriaSelected ? '1' : '0';
        criteriaInputs += `
            <td class="criteria-cell criteria-id-${criteria.id}">
                <div class="criteria-input-wrapper" style="${displayStyle}">
                    <input type="number" step="0.01" class="form-control table-input criteria-input" 
                           name="alternatives[${alternativeCounter}][criteria_values][${criteria.id}]" 
                           value="0" placeholder="0.00">
                    <input type="hidden" class="selected-criteria-input" name="alternatives[${alternativeCounter}][selected_criteria][${criteria.id}]" value="${selectedValue}">
                </div>
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

function setupCriteriaToggles() {
    document.querySelectorAll('.criteria-toggle input[type="checkbox"]').forEach(toggle => {
        toggle.removeEventListener('change', handleCriteriaToggle);
        toggle.addEventListener('change', handleCriteriaToggle);
    });
}

function handleCriteriaToggle(event) {
    const toggle = event.currentTarget;
    const criteriaId = toggle.dataset.criteriaId;
    const isChecked = toggle.checked;

    // Select all cells for this criteria across all rows
    document.querySelectorAll(`.criteria-cell.criteria-id-${criteriaId}`).forEach(cell => {
        const wrapper = cell.querySelector('.criteria-input-wrapper');
        const criteriaInput = cell.querySelector('.criteria-input');
        const selectedCriteriaInput = cell.querySelector('.selected-criteria-input');

        if (wrapper && selectedCriteriaInput) {
            wrapper.style.display = isChecked ? '' : 'none';
            selectedCriteriaInput.value = isChecked ? '1' : '0';
            
            // Clear value if unchecked to prevent sending data for unselected criteria
            if (!isChecked && criteriaInput) {
                criteriaInput.value = '0'; 
            }
        }
    });
}

function applyInitialCriteriaVisibility() {
    document.querySelectorAll('.criteria-toggle input[type="checkbox"]').forEach(toggle => {
        const criteriaId = toggle.dataset.criteriaId;
        const isChecked = toggle.checked;
        document.querySelectorAll(`.criteria-cell.criteria-id-${criteriaId}`).forEach(cell => {
            const wrapper = cell.querySelector('.criteria-input-wrapper');
            if (wrapper) {
                wrapper.style.display = isChecked ? '' : 'none';
            }
        });
    });
}

// Remove the global function as we're not using onclick anymore