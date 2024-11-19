'use strict';

// Debug flag
const DEBUG = true;

class CompanyManager {
    constructor() {
        if (DEBUG) console.log('Initializing CompanyManager');
        
        this.initializeElements();
        this.bindEvents();
        
        // Store original data
        this.companiesData = [];
        
        // Load initial data
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

        // Search and filter elements
        this.searchInput = document.getElementById('searchInput');
        this.searchField = document.getElementById('searchField');
        this.sortField = document.getElementById('sortField');
        this.sortOrder = document.getElementById('sortOrder');

        if (DEBUG) {
            console.log('DOM Elements initialized:', {
                cvrInput: this.cvrInput,
                addButton: this.addButton,
                companiesList: this.companiesList,
                loadingState: this.loadingState,
                messageArea: this.messageArea,
                cardTemplate: this.cardTemplate,
                searchInput: this.searchInput,
                searchField: this.searchField,
                sortField: this.sortField,
                sortOrder: this.sortOrder
            });
        }
    }

    bindEvents() {
        // Add company events
        if (this.addButton) {
            this.addButton.addEventListener('click', () => this.addCompany());
        }
        
        if (this.cvrInput) {
            this.cvrInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.addCompany();
            });
            
            this.cvrInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 8);
            });
        }

        // Search and filter events
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => this.filterCompanies());
        }
        if (this.searchField) {
            this.searchField.addEventListener('change', () => this.filterCompanies());
        }
        if (this.sortField) {
            this.sortField.addEventListener('change', () => this.filterCompanies());
        }
        if (this.sortOrder) {
            this.sortOrder.addEventListener('change', () => this.filterCompanies());
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
                this.companiesData = data.data;
                this.filterCompanies();
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

    filterCompanies() {
        if (!this.companiesData) return;

        const searchTerm = this.searchInput?.value.toLowerCase() || '';
        const searchField = this.searchField?.value || 'all';
        const sortField = this.sortField?.value || 'name';
        const sortOrder = this.sortOrder?.value || 'asc';

        if (DEBUG) {
            console.log('Filtering companies with:', {
                searchTerm,
                searchField,
                sortField,
                sortOrder
            });
        }

        // Filter
        let filteredCompanies = [...this.companiesData];
        
        if (searchTerm) {
            filteredCompanies = filteredCompanies.filter(company => {
                if (searchField === 'all') {
                    return Object.values(company).some(value => 
                        String(value).toLowerCase().includes(searchTerm)
                    );
                } else {
                    const value = company[searchField];
                    return value && String(value).toLowerCase().includes(searchTerm);
                }
            });
        }

        // Sort
        filteredCompanies.sort((a, b) => {
            let valueA = (a[sortField] || '').toString().toLowerCase();
            let valueB = (b[sortField] || '').toString().toLowerCase();

            if (sortField === 'created_at') {
                valueA = new Date(valueA);
                valueB = new Date(valueB);
            }

            if (sortOrder === 'asc') {
                return valueA < valueB ? -1 : valueA > valueB ? 1 : 0;
            } else {
                return valueA > valueB ? -1 : valueA < valueB ? 1 : 0;
            }
        });

        this.displayCompanies(filteredCompanies);
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