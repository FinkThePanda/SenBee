'use strict';

// Debug flag
const DEBUG = true;

class CompanyManager {
    constructor() {
        if (DEBUG) console.log('Initializing CompanyManager');
        
        // Initialize properties
        this.initializeElements();
        this.bindEvents();
        this.loadCompanies();
    }

    initializeElements() {
        // Cache DOM elements
        this.cvrInput = document.getElementById('cvrInput');
        this.addButton = document.getElementById('addButton');
        this.companiesList = document.getElementById('companiesList');
        this.loadingState = document.getElementById('loadingState');
        this.messageArea = document.getElementById('messageArea');
        this.cardTemplate = document.getElementById('companyCardTemplate');

        if (DEBUG) {
            console.log('DOM Elements initialized:', {
                cvrInput: this.cvrInput,
                addButton: this.addButton,
                companiesList: this.companiesList,
                loadingState: this.loadingState,
                messageArea: this.messageArea,
                cardTemplate: this.cardTemplate
            });
        }
    }

    bindEvents() {
        // Bind event listeners
        if (this.addButton) {
            this.addButton.addEventListener('click', () => this.addCompany());
        }
        
        if (this.cvrInput) {
            this.cvrInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.addCompany();
            });
            
            // Input validation
            this.cvrInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 8);
            });
        }
        
        if (DEBUG) console.log('Events bound successfully');
    }

    showLoading(show = true) {
        if (this.loadingState) {
            this.loadingState.classList.toggle('hidden', !show);
        }
        if (this.companiesList) {
            this.companiesList.classList.toggle('hidden', show);
        }
        if (DEBUG) console.log('Loading state:', show);
    }

    showMessage(message, isError = false) {
        if (this.messageArea) {
            this.messageArea.textContent = message;
            this.messageArea.className = `message-area ${isError ? 'error-message' : 'success-message'}`;
            
            if (DEBUG) console.log(`Showing message: ${message} (${isError ? 'error' : 'success'})`);

            setTimeout(() => {
                this.messageArea.textContent = '';
                this.messageArea.className = 'message-area';
            }, 3000);
        }
    }

    async loadCompanies() {
        try {
            if (DEBUG) console.log('Loading companies...');
            this.showLoading(true);
            
            const response = await fetch('api/companies.php');
            if (DEBUG) console.log('API response:', response);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (DEBUG) console.log('Companies data:', data);

            if (data.success) {
                this.displayCompanies(data.data);
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Load companies error:', error);
            this.showMessage('Error loading companies: ' + error.message, true);
        } finally {
            this.showLoading(false);
        }
    }

    displayCompanies(companies) {
        if (!this.companiesList || !this.cardTemplate) return;

        if (DEBUG) console.log('Displaying companies:', companies);

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
            card.querySelector('.company-address').textContent = company.address || 'No address provided';
            card.querySelector('.company-phone').textContent = company.phone || 'No phone';
            card.querySelector('.company-email').textContent = company.email ? ` | ${company.email}` : '';

            // Add event listeners
            const syncButton = card.querySelector('.sync-button');
            const deleteButton = card.querySelector('.delete-button');

            if (syncButton) {
                syncButton.addEventListener('click', () => this.syncCompany(company.id));
            }
            if (deleteButton) {
                deleteButton.addEventListener('click', () => this.deleteCompany(company.id));
            }

            this.companiesList.appendChild(card);
        });

        if (DEBUG) console.log('Companies display updated');
    }

    async addCompany() {
        if (!this.cvrInput) return;

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
            if (DEBUG) console.log('Adding company with CVR:', cvr);

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
            if (DEBUG) console.log('Add company response:', data);
            
            if (data.success) {
                this.showMessage('Company added successfully');
                this.cvrInput.value = '';
                await this.loadCompanies();
            } else {
                throw new Error(data.error || 'Failed to add company');
            }
        } catch (error) {
            console.error('Add company error:', error);
            this.showMessage('Error adding company: ' + error.message, true);
        }
    }

    async syncCompany(id) {
        try {
            if (DEBUG) console.log('Syncing company:', id);

            const response = await fetch(`api/sync.php?id=${id}`, {
                method: 'POST'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (DEBUG) console.log('Sync response:', data);
            
            if (data.success) {
                this.showMessage('Company synced successfully');
                await this.loadCompanies();
            } else {
                throw new Error(data.error || 'Failed to sync company');
            }
        } catch (error) {
            console.error('Sync company error:', error);
            this.showMessage('Error syncing company: ' + error.message, true);
        }
    }

    async deleteCompany(id) {
        if (!confirm('Are you sure you want to delete this company?')) {
            return;
        }

        try {
            if (DEBUG) console.log('Deleting company:', id);

            const response = await fetch(`api/companies.php?id=${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (DEBUG) console.log('Delete response:', data);
            
            if (data.success) {
                this.showMessage('Company deleted successfully');
                await this.loadCompanies();
            } else {
                throw new Error(data.error || 'Failed to delete company');
            }
        } catch (error) {
            console.error('Delete company error:', error);
            this.showMessage('Error deleting company: ' + error.message, true);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (DEBUG) console.log('DOM loaded, initializing application...');
    window.companyManager = new CompanyManager();
});

// Global error handlers
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
});