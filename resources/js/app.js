import './bootstrap';

// Alpine.js
import Alpine from 'alpinejs';

Alpine.data('searchableCustomerSelect', (config = {}) => ({
    customers: Array.isArray(config.customers) ? config.customers : [],
    placeholder: config.placeholder ?? 'Pilih pelanggan',
    selectedId: config.selectedId ? String(config.selectedId) : '',
    selectedLabel: config.selectedLabel ?? '',
    query: config.selectedLabel ?? '',
    open: false,

    init() {
        if (this.selectedId && !this.selectedLabel) {
            const selectedCustomer = this.customers.find(
                (customer) => String(customer.id) === this.selectedId,
            );

            if (selectedCustomer) {
                this.selectedLabel = this.formatLabel(selectedCustomer);
            }
        }

        this.restoreSelectedLabel();
    },

    get filteredCustomers() {
        const normalizedQuery = this.query.trim().toLowerCase();

        if (!normalizedQuery) {
            return this.customers;
        }

        return this.customers.filter((customer) => {
            const name = (customer.name ?? '').toLowerCase();
            const phone = customer.phone ?? '';

            return name.includes(normalizedQuery) || phone.includes(this.query.trim());
        });
    },

    formatLabel(customer) {
        return `${customer.name} - ${customer.phone}`;
    },

    openList() {
        this.open = true;

        if (this.query === this.selectedLabel) {
            this.query = '';
        }
    },

    closeList(restoreSelection = false) {
        this.open = false;

        if (restoreSelection) {
            this.restoreSelectedLabel();
        }
    },

    restoreSelectedLabel() {
        this.query = this.selectedLabel || '';
    },

    handleInput(value) {
        this.query = value;
        this.open = true;

        if (value.trim() && value !== this.selectedLabel) {
            this.selectedId = '';
            this.selectedLabel = '';
        }
    },

    selectCustomer(customer) {
        this.selectedId = String(customer.id);
        this.selectedLabel = this.formatLabel(customer);
        this.query = this.selectedLabel;
        this.open = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();
