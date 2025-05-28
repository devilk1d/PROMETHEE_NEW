// resources/js/criteria/batch.js

// Variables to store configuration
let criteriaCounter = 0;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadConfiguration();
    initializeBatchCriteria();
});

function loadConfiguration() {
    const configElement = document.getElementById('js-config');
    if (configElement) {
        criteriaCounter = parseInt(configElement.dataset.initialCount) || 0;
    }
}

function initializeBatchCriteria() {
    // Add event listener for add row button
    const addButton = document.getElementById('addCriteriaRow');
    if (addButton) {
        addButton.addEventListener('click', addCriteriaRow);
    }
    
    // Setup event listeners for existing elements
    setupEventListeners();
}

function addCriteriaRow() {
    const tbody = document.querySelector('#criteriaTable tbody');
    if (!tbody) return;
    
    const newRow = document.createElement('tr');
    newRow.className = 'criteria-row new-row';
    criteriaCounter++;
    
    newRow.innerHTML = `
        <td>
            <input type="text" class="form-control table-input" name="criteria[${criteriaCounter}][name]" placeholder="Criteria name" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0" max="1" class="form-control table-input" name="criteria[${criteriaCounter}][weight]" value="1" placeholder="0.00" required>
        </td>
        <td>
            <select class="form-control table-select" name="criteria[${criteriaCounter}][type]" required>
                <option value="benefit">Benefit</option>
                <option value="cost">Cost</option>
            </select>
        </td>
        <td>
            <select class="form-control table-select preference-function" name="criteria[${criteriaCounter}][preference_function]" required>
                ${getPreferenceFunctionOptions()}
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control table-input p-field" name="criteria[${criteriaCounter}][p]" placeholder="0.00" disabled>
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control table-input q-field" name="criteria[${criteriaCounter}][q]" placeholder="0.00" disabled>
        </td>
        <td>
            <div class="description-container">
                <textarea class="form-control table-textarea d-none" name="criteria[${criteriaCounter}][description]" rows="2"></textarea>
                <button type="button" class="btn-icon btn-icon-info edit-description" data-bs-toggle="modal" data-bs-target="#descriptionModal" data-index="${criteriaCounter}" title="Edit Description">
                    <i class="bi bi-pencil"></i>
                </button>
            </div>
        </td>
        <td>
            <button type="button" class="btn-icon btn-icon-danger delete-row" title="Delete Row">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    setupEventListeners(newRow);
}

function getPreferenceFunctionOptions() {
    // This should match the server-side preference functions
    const functions = {
        'usual': 'Usual',
        'quasi': 'Quasi',
        'linear': 'Linear',
        'level': 'Level',
        'linear_quasi': 'Linear Quasi',
        'gaussian': 'Gaussian'
    };
    
    let options = '';
    for (const [value, label] of Object.entries(functions)) {
        const needsP = ['linear', 'level', 'linear_quasi', 'gaussian'].includes(value) ? '1' : '0';
        const needsQ = ['quasi', 'level', 'linear_quasi'].includes(value) ? '1' : '0';
        
        options += `<option value="${value}" data-needs-p="${needsP}" data-needs-q="${needsQ}">${label}</option>`;
    }
    
    return options;
}

function setupEventListeners(container = document) {
    // Preference function changes
    const prefFunctions = container.querySelectorAll('.preference-function');
    prefFunctions.forEach(function(select) {
        select.addEventListener('change', function() {
            const row = this.closest('tr');
            const option = this.options[this.selectedIndex];
            const pField = row.querySelector('.p-field');
            const qField = row.querySelector('.q-field');
            
            pField.disabled = option.dataset.needsP !== '1';
            qField.disabled = option.dataset.needsQ !== '1';
            
            if (pField.disabled) pField.value = '';
            if (qField.disabled) qField.value = '';
        });
    });
    
    // Delete buttons
    const deleteButtons = container.querySelectorAll('.delete-row');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            
            // If it has an ID, mark for deletion
            const idInput = row.querySelector('input[name*="[id]"]');
            if (idInput) {
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_criteria[]';
                deleteInput.value = idInput.value;
                document.getElementById('criteriaBatchForm').appendChild(deleteInput);
            }
            
            row.remove();
        });
    });
    
    // Description edit buttons
    const editButtons = container.querySelectorAll('.edit-description');
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            const textarea = this.parentElement.querySelector('textarea');
            const modalTextarea = document.getElementById('criteriaDescription');
            
            modalTextarea.value = textarea.value;
            document.getElementById('saveDescription').setAttribute('data-index', index);
        });
    });
    
    // Save description button
    const saveDescriptionBtn = document.getElementById('saveDescription');
    if (saveDescriptionBtn) {
        saveDescriptionBtn.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            const modalTextarea = document.getElementById('criteriaDescription');
            const textarea = document.querySelector(`textarea[name="criteria[${index}][description]"]`);
            
            if (textarea) {
                textarea.value = modalTextarea.value;
                const modal = bootstrap.Modal.getInstance(document.getElementById('descriptionModal'));
                if (modal) modal.hide();
            }
        });
    }
}