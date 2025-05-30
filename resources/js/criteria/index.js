import { setupConfirmationModal } from '../confirmation-modal';

setupConfirmationModal({
    formSelector: '.delete-form',
    text: "This will permanently delete this criteria and all its associated data."
});