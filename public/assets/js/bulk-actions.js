// Bulk Actions Functionality
class BulkActionsManager {
    constructor(tableSelector = '.summary-table', checkboxSelector = '.summary-checkbox') {
        this.table = document.querySelector(tableSelector);
        this.checkboxes = document.querySelectorAll(checkboxSelector);
        this.selectAllCheckbox = this.table?.querySelector('thead ' + checkboxSelector);
        this.bulkActionsBtn = document.querySelector('[data-bulk-actions-toggle]');
        this.bulkActionsDropdown = document.querySelector('[data-bulk-actions-menu]');
        this.selectedLeadIds = [];

        this.init();
    }

    init() {
        if (!this.table) return;

        // Event listeners for individual checkboxes
        this.checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => this.handleCheckboxChange(e));
        });

        // Event listener for select all checkbox
        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.addEventListener('change', (e) => this.handleSelectAll(e));
        }

        // Event listener for bulk actions button
        if (this.bulkActionsBtn) {
            this.bulkActionsBtn.addEventListener('click', (e) => this.toggleDropdown(e));
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => this.handleClickOutside(e));

        // Bulk actions menu items
        const menuItems = this.bulkActionsDropdown?.querySelectorAll('[data-bulk-action]');
        menuItems?.forEach(item => {
            item.addEventListener('click', (e) => this.handleBulkAction(e));
        });
    }

    handleCheckboxChange(e) {
        const checkbox = e.target;
        const row = checkbox.closest('tr');
        const leadId = row?.getAttribute('data-lead-id');

        if (leadId) {
            if (checkbox.checked) {
                if (!this.selectedLeadIds.includes(leadId)) {
                    this.selectedLeadIds.push(leadId);
                }
            } else {
                this.selectedLeadIds = this.selectedLeadIds.filter(id => id !== leadId);
            }
        }

        this.updateUI();
    }

    handleSelectAll(e) {
        const isChecked = e.target.checked;
        const checkboxes = this.table?.querySelectorAll('tbody ' + this.checkboxSelector);

        checkboxes?.forEach(checkbox => {
            checkbox.checked = isChecked;
            const row = checkbox.closest('tr');
            const leadId = row?.getAttribute('data-lead-id');

            if (leadId) {
                if (isChecked && !this.selectedLeadIds.includes(leadId)) {
                    this.selectedLeadIds.push(leadId);
                } else if (!isChecked) {
                    this.selectedLeadIds = this.selectedLeadIds.filter(id => id !== leadId);
                }
            }
        });

        this.updateUI();
    }

    toggleDropdown(e) {
        e.stopPropagation();

        if (this.selectedLeadIds.length === 0) {
            this.showNotification('Please select at least one lead', 'error');
            return;
        }

        if (this.bulkActionsDropdown) {
            this.bulkActionsDropdown.classList.toggle('active');
        }
    }

    handleClickOutside(e) {
        const isClickInsideDropdown = this.bulkActionsDropdown?.contains(e.target);
        const isClickOnButton = this.bulkActionsBtn?.contains(e.target);

        if (!isClickInsideDropdown && !isClickOnButton && this.bulkActionsDropdown) {
            this.bulkActionsDropdown.classList.remove('active');
        }
    }

    handleBulkAction(e) {
        e.preventDefault();
        const action = e.target.closest('[data-bulk-action]')?.getAttribute('data-bulk-action');

        if (!action) return;

        switch (action) {
            case 'update':
                this.showUpdateModal();
                break;
            case 'delete':
                this.showDeleteConfirm();
                break;
            case 'move':
                this.showMoveModal();
                break;
            case 'copy':
                this.showCopyModal();
                break;
            case 'close':
                this.showCloseConfirm();
                break;
        }

        // Close dropdown
        if (this.bulkActionsDropdown) {
            this.bulkActionsDropdown.classList.remove('active');
        }
    }

    showUpdateModal() {
        console.log('Update modal for leads:', this.selectedLeadIds);
        // Dispatch event for Livewire component or custom modal
        window.dispatchEvent(new CustomEvent('openUpdateModal', {
            detail: { leadIds: this.selectedLeadIds }
        }));
    }

    showMoveModal() {
        console.log('Move modal for leads:', this.selectedLeadIds);
        window.dispatchEvent(new CustomEvent('openMoveModal', {
            detail: { leadIds: this.selectedLeadIds }
        }));
    }

    showCopyModal() {
        console.log('Copy modal for leads:', this.selectedLeadIds);
        window.dispatchEvent(new CustomEvent('openCopyModal', {
            detail: { leadIds: this.selectedLeadIds }
        }));
    }

    showDeleteConfirm() {
        if (confirm(`Are you sure you want to delete ${this.selectedLeadIds.length} lead(s)?`)) {
            console.log('Deleting leads:', this.selectedLeadIds);
            window.dispatchEvent(new CustomEvent('deleteLeads', {
                detail: { leadIds: this.selectedLeadIds }
            }));
        }
    }

    showCloseConfirm() {
        if (confirm(`Are you sure you want to close ${this.selectedLeadIds.length} lead(s)?`)) {
            console.log('Closing leads:', this.selectedLeadIds);
            window.dispatchEvent(new CustomEvent('closeLeads', {
                detail: { leadIds: this.selectedLeadIds }
            }));
        }
    }

    updateUI() {
        const selectedCount = this.selectedLeadIds.length;

        // Update button state
        if (this.bulkActionsBtn) {
            if (selectedCount > 0) {
                this.bulkActionsBtn.classList.remove('disabled');
                this.bulkActionsBtn.disabled = false;
            } else {
                this.bulkActionsBtn.classList.add('disabled');
                this.bulkActionsBtn.disabled = true;
            }
        }

        // Update select all checkbox state
        const totalCheckboxes = this.table?.querySelectorAll('tbody .summary-checkbox').length || 0;
        const checkedCheckboxes = this.table?.querySelectorAll('tbody .summary-checkbox:checked').length || 0;

        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.checked = checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0;
            this.selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
        }

        // Dispatch custom event with selected count
        window.dispatchEvent(new CustomEvent('bulkActionsUpdated', {
            detail: { selectedCount, selectedIds: this.selectedLeadIds }
        }));
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 16px;
            background: ${type === 'error' ? '#fee' : '#efe'};
            color: ${type === 'error' ? '#c33' : '#3c3'};
            border-radius: 4px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    getSelectedIds() {
        return this.selectedLeadIds;
    }

    clearSelection() {
        this.selectedLeadIds = [];
        this.checkboxes.forEach(checkbox => checkbox.checked = false);
        if (this.selectAllCheckbox) this.selectAllCheckbox.checked = false;
        this.updateUI();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.bulkActionsManager = new BulkActionsManager();
});
