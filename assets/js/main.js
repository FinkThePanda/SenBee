/**
 * Main JavaScript functionality for Company Manager
 * Handles all CRUD operations and UI interactions
 */

class CompanyManager {
    constructor() {
        // Cache DOM elements
        this.cvrInput = document.getElementById('cvrInput');
        this.addButton = document.getElementById('addButton');
        this.companiesList = document.getElementById('companiesList');
        this.loadingState = document.getElementById('loadingState');
        this.messageArea = document.getElementById('messageArea');
        this.cardTemplate = document.getElementById('companyCardTemplate');

        // Bind event listeners
        this.addButton.addEventListener('click', () => this.addCompany());
        this.cvrInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.addCompany();
        });

        // Input validation
        this.cvrInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 8);
        });

        // Initialize
        this.loadCompanies();

        // Development helper
        if (window.location.hostname === 'localhost') {
            console.log('Company Manager initialized in development mode');
        }
    }

    /**
     * Show/hide loading state
     * @param {boolean} show - Whether to show or hide loading state
     */
    showLoading(show = true) {
        this.loadingState.classList.toggle('hidden', !show);
        if (show) {
            this.companiesList.classList.add('hidden');
        } else {
            this.companiesList.classList.remove('hidden');
        }
    }

    /**
     * Display message to user
     * @param {string} message - Message to display
     * @param {boolean} isError - Whether this is an error message
     */
    showMessage(message, isError = false) {
        this.messageArea.textContent = message;
        this.messageArea.className = `message-area ${isError ? 'error-message' : 'success-message'}`;
        
        // Clear message after delay
        setTimeout(() => {
            this.messageArea.textContent = '';
            this.messageArea.className = 'message-area';
        }, 3000);
    }

    /**
     * Load all companies from the server
     */
    async loadCompanies() {
        try {
            this.showLoading(true);
            const response = await fetch('api/companies.php');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();

            if (data.success) {
                this.displayCompanies(data.data);
            } else {
                this.showMessage('Failed to load companies: ' + (data.error || 'Unknown error'), true);
            }
        } catch (error) {
            this.showMessage('Error connecting to server: ' + error.message, true);
            console.error('Load companies error:', error);
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * Display companies in the UI
     * @param {Array} companies - Array of company objects
     */
    displayCompanies(companies) {
        this.companiesList.innerHTML = '';
        
        if (!companies || companies.length === 0) {
            this.companiesList.innerHTML = '<div class="empty-state">No companies found. Add one above!</div>';
            return;
        }

        companies.forEach(company => {
            const card = this.cardTemplate.content.cloneNode(true);
            
            // Fill in company data
            card.querySelector('.company-name').textContent = company.name || 'Unnamed Company';
            card.querySelector('.cvr-number').textContent = `CVR: ${company.cvr_number}`;
            
            // Address handling
            const addressElem = card.querySelector('.company-address');
            addressElem.textContent = company.address || 'No address provided';
            addressElem.title = company.address || 'No address provided';

            // Contact info
            const phoneElem = card.querySelector('.company-phone');
            const emailElem = card.querySelector('.company-email');
            
            phoneElem.textContent = company.phone || 'No phone';
            emailElem.textContent = company.email ? ` | ${company.email}` : '';

            // Button event listeners
            const syncButton = card.querySelector('.sync-button');
            const deleteButton = card.querySelector('.delete-button');

            syncButton.addEventListener('click', () => this.syncCompany(company.id));
            deleteButton.addEventListener('click', () => this.deleteCompany(company.id));

            // Add the card to the list
            this.companiesList.appendChild(card);
        });
    }

    /**
     * Add a new company
     */
    async addCompany() {
        const cvr = this.cvrInput.value.trim();
        
        // Validate CVR
        if (!cvr) {
            this.showMessage('Please enter a CVR number', true);
            return;
        }

        if (!/^\d{8}$/.test(cvr)) {
            this.showMessage('CVR must be exactly 8 digits', true);
            return;
        }

        try {
            const response = await fetch('api/companies.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cvr_number: cvr })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.showMessage('Company added successfully');
                this.cvrInput.value = '';
                this.loadCompanies();
            } else {
                this.showMessage(data.error || 'Failed to add company', true);
            }
        } catch (error) {
            this.showMessage('Error connecting to server: ' + error.message, true);
            console.error('Add company error:', error);
        }
    }

    /**
     * Sync company data with CVR API
     * @param {number} id - Company ID
     */
    async syncCompany(id) {
        try {
            const response = await fetch(`api/sync.php?id=${id}`, {
                method: 'POST'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.showMessage('Company synced successfully');
                this.loadCompanies();
            } else {
                this.showMessage(data.error || 'Failed to sync company', true);
            }
        } catch (error) {
            this.showMessage('Error connecting to server: ' + error.message, true);
            console.error('Sync company error:', error);
        }
    }

    /**
     * Delete a company
     * @param {number} id - Company ID
     */
    async deleteCompany(id) {
        if (!confirm('Are you sure you want to delete this company?')) {
            return;
        }

        try {
            const response = await fetch(`api/companies.php?id=${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.showMessage('Company deleted successfully');
                this.loadCompanies();
            } else {
                this.showMessage(data.error || 'Failed to delete company', true);
            }
        } catch (error) {
            this.showMessage('Error connecting to server: ' + error.message, true);
            console.error('Delete company error:', error);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.companyManager = new CompanyManager();
});