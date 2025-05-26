/**
 * MapIt - Admin JavaScript
 * Handles interactive functionality for the admin interface
 */

// Initialize when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize all popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-close alerts after 5 seconds
    const autoCloseAlerts = document.querySelectorAll('.alert-dismissible');
    if (autoCloseAlerts.length > 0) {
        autoCloseAlerts.forEach(alert => {
            setTimeout(() => {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 5000);
        });
    }

    // Data tables initialization
    initializeDataTables();

    // Admin charts
    initializeAdminCharts();

    // Confirm delete
    setupDeleteConfirmations();

    // Status toggle switches
    setupStatusToggles();

    // File uploads with preview
    setupFileUploads();

    // Filter forms
    setupFilterForms();

    // Bulk actions
    setupBulkActions();
});

/**
 * Initialize data tables
 */
function initializeDataTables() {
    const tables = document.querySelectorAll('.admin-table.data-table');
    if (tables.length === 0) return;

    tables.forEach(table => {
        // Add sorting functionality
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.addEventListener('click', function() {
                const sortField = this.getAttribute('data-sort');
                const currentSort = this.getAttribute('data-sort-direction') || 'asc';
                const newSort = currentSort === 'asc' ? 'desc' : 'asc';
                
                // Update sort direction for all headers
                headers.forEach(h => {
                    h.setAttribute('data-sort-direction', '');
                    h.querySelector('i.fas').className = 'fas fa-sort';
                });
                
                // Update this header
                this.setAttribute('data-sort-direction', newSort);
                this.querySelector('i.fas').className = `fas fa-sort-${newSort === 'asc' ? 'up' : 'down'}`;
                
                // Get current URL and update sort params
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sortField);
                url.searchParams.set('direction', newSort);
                
                // Navigate to the sorted page
                window.location.href = url.href;
            });
        });

        // Add search functionality
        const searchInput = document.querySelector('.table-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value;
                    const url = new URL(window.location.href);
                    
                    if (searchTerm) {
                        url.searchParams.set('search', searchTerm);
                    } else {
                        url.searchParams.delete('search');
                    }
                    
                    window.location.href = url.href;
                }
            });
        }

        // Add pagination functionality
        const pagination = document.querySelector('.admin-pagination');
        if (pagination) {
            const pageLinks = pagination.querySelectorAll('a.page-link');
            pageLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    const url = new URL(window.location.href);
                    url.searchParams.set('page', page);
                    window.location.href = url.href;
                });
            });
        }
    });
}

/**
 * Initialize admin dashboard charts
 */
function initializeAdminCharts() {
    // User registrations chart
    const userChartCanvas = document.getElementById('userRegistrationsChart');
    if (userChartCanvas) {
        // This is a placeholder - in a real app you'd load data via AJAX
        const userChartData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Users',
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderColor: '#3498db',
                borderWidth: 2,
                data: [18, 25, 30, 22, 28, 35]
            }]
        };

        new Chart(userChartCanvas, {
            type: 'line',
            data: userChartData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Destinations chart
    const destinationsChartCanvas = document.getElementById('destinationsChart');
    if (destinationsChartCanvas) {
        // This is a placeholder - in a real app you'd load data via AJAX
        const destinationsChartData = {
            labels: ['North America', 'South America', 'Europe', 'Asia', 'Africa', 'Oceania'],
            datasets: [{
                label: 'Destinations by Region',
                backgroundColor: [
                    '#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c'
                ],
                data: [120, 80, 150, 110, 70, 60]
            }]
        };

        new Chart(destinationsChartCanvas, {
            type: 'doughnut',
            data: destinationsChartData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Activity chart
    const activityChartCanvas = document.getElementById('activityChart');
    if (activityChartCanvas) {
        // This is a placeholder - in a real app you'd load data via AJAX
        const activityChartData = {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Logins',
                backgroundColor: 'rgba(52, 152, 219, 0.5)',
                data: [45, 52, 38, 40, 65, 30, 20]
            }, {
                label: 'New Destinations',
                backgroundColor: 'rgba(46, 204, 113, 0.5)',
                data: [15, 25, 20, 18, 28, 22, 10]
            }]
        };

        new Chart(activityChartCanvas, {
            type: 'bar',
            data: activityChartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true
                    }
                }
            }
        });
    }
}

/**
 * Setup delete confirmation dialogs
 */
function setupDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('[data-delete-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemName = this.getAttribute('data-delete-confirm');
            const url = this.getAttribute('href');
            
            // Create modal
            const modalId = 'deleteConfirmModal';
            let modal = document.getElementById(modalId);
            
            if (!modal) {
                const modalHTML = `
                    <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="${modalId}Label">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this ${itemName}? This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <a href="#" class="btn btn-danger" id="confirmDeleteButton">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Append modal to body
                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = modalHTML;
                document.body.appendChild(modalContainer);
                
                modal = document.getElementById(modalId);
            }
            
            // Set the correct delete URL
            const confirmButton = modal.querySelector('#confirmDeleteButton');
            confirmButton.setAttribute('href', url);
            
            // Show the modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        });
    });
}

/**
 * Setup status toggle switches
 */
function setupStatusToggles() {
    const statusToggles = document.querySelectorAll('.status-toggle');
    statusToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const itemId = this.getAttribute('data-id');
            const itemType = this.getAttribute('data-type');
            const isActive = this.checked;
            
            // Send AJAX request to update status
            const xhr = new XMLHttpRequest();
            xhr.open('POST', `/admin/${itemType}/toggle-status`, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status !== 200) {
                    alert('Error updating status. Please try again.');
                    toggle.checked = !isActive; // Revert toggle state
                }
            };
            xhr.send(`id=${itemId}&active=${isActive ? 1 : 0}`);
        });
    });
}

/**
 * Setup file uploads with preview
 */
function setupFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        const previewId = input.getAttribute('data-preview');
        const preview = document.getElementById(previewId);
        
        if (preview) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
}

/**
 * Setup filter forms
 */
function setupFilterForms() {
    const filterForm = document.getElementById('adminFilterForm');
    if (!filterForm) return;
    
    // Auto-submit on select change
    const selectFilters = filterForm.querySelectorAll('select[data-autosubmit]');
    selectFilters.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
    
    // Date range pickers
    const dateRanges = filterForm.querySelectorAll('.date-range');
    dateRanges.forEach(dateRange => {
        const startInput = dateRange.querySelector('input[name*="start"]');
        const endInput = dateRange.querySelector('input[name*="end"]');
        
        if (startInput && endInput) {
            // When both dates are selected, submit the form
            endInput.addEventListener('change', function() {
                if (startInput.value && endInput.value) {
                    filterForm.submit();
                }
            });
        }
    });
    
    // Reset filters
    const resetButton = filterForm.querySelector('.reset-filters');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear all inputs
            const inputs = filterForm.querySelectorAll('input:not([type="submit"]), select');
            inputs.forEach(input => {
                if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                }
            });
            
            // Submit the form
            filterForm.submit();
        });
    }
}

/**
 * Setup bulk actions
 */
function setupBulkActions() {
    const bulkForm = document.getElementById('bulkActionForm');
    if (!bulkForm) return;
    
    const checkAll = document.getElementById('checkAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionSelect = document.getElementById('bulkAction');
    const applyBulkAction = document.getElementById('applyBulkAction');
    
    // Check all functionality
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const isChecked = this.checked;
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBulkActionState();
        });
    }
    
    // Update bulk action button state when individual checkboxes change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionState);
    });
    
    // Apply bulk action
    if (applyBulkAction) {
        applyBulkAction.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate at least one checkbox is selected
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (checkedItems.length === 0) {
                alert('Please select at least one item.');
                return;
            }
            
            // Validate an action is selected
            const action = bulkActionSelect.value;
            if (!action) {
                alert('Please select an action.');
                return;
            }
            
            // Confirm before proceeding
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete the selected items? This action cannot be undone.')) {
                    return;
                }
            }
            
            // Submit the form
            bulkForm.submit();
        });
    }
    
    /**
     * Update bulk action button state
     */
    function updateBulkActionState() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        
        if (applyBulkAction) {
            applyBulkAction.disabled = checkedItems.length === 0;
        }
        
        // Update "Check All" state
        if (checkAll && itemCheckboxes.length > 0) {
            checkAll.checked = checkedItems.length === itemCheckboxes.length;
        }
    }
}
