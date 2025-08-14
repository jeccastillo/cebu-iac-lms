// Leads Vue.js Application
// Global variables should be defined before this script is loaded
// Expected: api_url, base_url, campus

const LeadsApp = new Vue({
    el: '#leads-app',
    data: {
        leads: [],
        selectedTerm: '',
        statusFilter: 'none',
        dateType: 'created_at',
        dateRange: '',
        searchQuery: '',
        errorMessage: '',
        successMessage: '',
        loading: false,
        exporting: false,
        sortField: 'created_at',
        sortDirection: 'asc',
        currentPage: 1,
        itemsPerPage: 20,
        totalRecords: 0,
        totalPages: 0,
        jumpToPage: 1,
        searchTimeout: null
    },
    computed: {
        // Server-side pagination - leads are already paginated from API
        paginatedLeads() {
            return this.leads;
        },
        
        // Calculate pagination info
        startRecord() {
            return ((this.currentPage - 1) * this.itemsPerPage) + 1;
        },
        
        endRecord() {
            return Math.min(this.currentPage * this.itemsPerPage, this.totalRecords);
        }
    },
    methods: {
        fetchLeads(resetPage = false) {
            this.loading = true;
            this.errorMessage = '';
            
            // Reset to first page if requested (e.g., when filters change)
            if (resetPage) {
                this.currentPage = 1;
            }
            
            // Prepare parameters for API call with server-side pagination
            let params = {
                current_sem: this.selectedTerm,
                campus: window.campus || '',
                limit: this.itemsPerPage,
                page: this.currentPage,
                count_content: this.itemsPerPage,
                search_data: this.searchQuery || '',
                filter: this.statusFilter !== 'none' ? this.statusFilter : '',
                search_field: 'first_name',
                sort_field: this.sortField,
                order_by: this.sortDirection
            };
            
            // Add date range filters if specified
            if (this.dateRange) {
                const dates = this.dateRange.split(' to ');
                if (dates.length === 2) {
                    const startDate = dates[0].trim();
                    const endDate = dates[1].trim();
                    
                    // Validate date format (YYYY-MM-DD)
                    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (dateRegex.test(startDate) && dateRegex.test(endDate)) {
                        params.start = startDate;
                        params.end = endDate;
                        params.range_field = this.dateType;
                    } else {
                        this.errorMessage = "Invalid date format. Please use YYYY-MM-DD to YYYY-MM-DD";
                        this.loading = false;
                        return;
                    }
                }
            }
            
            // Make API call
            const apiUrl = window.api_url || 'https://smsapi.iacademy.edu.ph/api/v1/sms/';
            axios.get(apiUrl + "admissions/applications", { 
                params,
                timeout: 30000 // 30 second timeout
            })
                .then(response => {
                    if (response.data && response.data.data) {
                        this.leads = response.data.data;
                        
                        // Handle pagination metadata
                        if (response.data.pagination) {
                            this.totalRecords = response.data.pagination.total || response.data.data.length;
                            this.totalPages = response.data.pagination.total_pages || Math.ceil(this.totalRecords / this.itemsPerPage);
                            this.currentPage = response.data.pagination.current_page || this.currentPage;
                        } else {
                            // Fallback if no pagination metadata
                            this.totalRecords = response.data.total || response.data.data.length;
                            this.totalPages = Math.ceil(this.totalRecords / this.itemsPerPage);
                        }
                        
                        this.successMessage = `Loaded ${this.leads.length} of ${this.totalRecords} applicant records`;
                        setTimeout(() => { this.successMessage = ''; }, 3000);
                    } else if (response.data && Array.isArray(response.data)) {
                        // Handle case where data is directly an array
                        this.leads = response.data;
                        this.totalRecords = response.data.length;
                        this.totalPages = Math.ceil(this.totalRecords / this.itemsPerPage);
                        this.successMessage = `Loaded ${this.leads.length} applicant records`;
                        setTimeout(() => { this.successMessage = ''; }, 3000);
                    } else {
                        this.leads = [];
                        this.totalRecords = 0;
                        this.totalPages = 0;
                        this.errorMessage = "No data received from server";
                    }
                    this.loading = false;
                })
                .catch(error => {
                    console.error("API Error:", error);
                    let errorMsg = "Failed to fetch applicant data. ";
                    
                    if (error.code === 'ECONNABORTED') {
                        errorMsg += "Request timed out. Please try again.";
                    } else if (error.response) {
                        errorMsg += `Server error: ${error.response.status}`;
                        if (error.response.data && error.response.data.message) {
                            errorMsg += ` - ${error.response.data.message}`;
                        }
                    } else if (error.request) {
                        errorMsg += "Network error. Please check your connection.";
                    } else {
                        errorMsg += "An unexpected error occurred.";
                    }
                    
                    this.errorMessage = errorMsg;
                    this.loading = false;
                    this.leads = [];
                    this.totalRecords = 0;
                    this.totalPages = 0;
                });
        },
        
        onTermChange() {
            // Redirect to new URL with selected term (maintaining CodeIgniter routing)
            const baseUrl = window.base_url || '';
            window.location.href = baseUrl + "admissionsV1/view_all_leads/" + this.selectedTerm;
        },
        
        exportToExcel() {
            if (this.totalRecords === 0) {
                this.errorMessage = "No data to export";
                return;
            }
            
            this.exporting = true;
            
            // First get fresh data from API for export (all records)
            let exportParams = {
                current_sem: this.selectedTerm,
                campus: window.campus || '',
                limit: 10000, // Get all records for export
                page: 1,
                search_data: this.searchQuery || '',
                filter: this.statusFilter !== 'none' ? this.statusFilter : '',
                search_field: 'first_name',
                sort_field: this.sortField,
                order_by: this.sortDirection
            };
            
            // Add date range filters if specified
            if (this.dateRange) {
                const dates = this.dateRange.split(' to ');
                if (dates.length === 2) {
                    const startDate = dates[0].trim();
                    const endDate = dates[1].trim();
                    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (dateRegex.test(startDate) && dateRegex.test(endDate)) {
                        exportParams.start = startDate;
                        exportParams.end = endDate;
                        exportParams.range_field = this.dateType;
                    }
                }
            }
            
            const apiUrl = window.api_url || 'https://smsapi.iacademy.edu.ph/api/v1/sms/';
            axios.get(apiUrl + "admissions/applications", { params: exportParams })
                .then(response => {
                    const exportData = response.data.data || response.data || [];
                    
                    // Create form for export
                    const baseUrl = window.base_url || '';
                    const exportUrl = baseUrl + "excel/export_leads";
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = exportUrl;
                    form.target = "_blank";
                    form.style.display = "none";
                    
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "applicants";
                    input.value = JSON.stringify(exportData);
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                    
                    this.successMessage = `Exported ${exportData.length} records to Excel`;
                    this.exporting = false;
                    setTimeout(() => { this.successMessage = ''; }, 3000);
                })
                .catch(error => {
                    console.error("Export Error:", error);
                    this.errorMessage = "Export failed. Please try again.";
                    this.exporting = false;
                });
        },
        
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            // Fetch data with new sorting
            this.fetchLeads(true);
        },
        
        getSortIcon(field) {
            if (this.sortField !== field) return '';
            return this.sortDirection === 'asc' ? '▲' : '▼';
        },
        
        getStatusClass(status) {
            const statusClasses = {
                'New': 'bg-blue-100 text-blue-800',
                'Waiting for Interview': 'bg-yellow-100 text-yellow-800',
                'For Interview': 'bg-orange-100 text-orange-800',
                'For Reservation': 'bg-purple-100 text-purple-800',
                'Reserved': 'bg-indigo-100 text-indigo-800',
                'Floating': 'bg-gray-100 text-gray-800',
                'For Enrollment': 'bg-green-100 text-green-800',
                'Enrolled': 'bg-green-100 text-green-800',
                'Confirmed': 'bg-green-100 text-green-800',
                'Enlisted': 'bg-green-100 text-green-800',
                'Cancelled': 'bg-red-100 text-red-800',
                'Will Not Proceed': 'bg-red-100 text-red-800',
                'Did Not Reserve': 'bg-red-100 text-red-800',
                'Rejected': 'bg-red-100 text-red-800',
                'Disqualified': 'bg-red-100 text-red-800',
                'Not Answering': 'bg-gray-100 text-gray-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        
        formatDate(dateString) {
            if (!dateString || dateString === '0000-00-00' || dateString === '0000-00-00 00:00:00') return '-';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return '-';
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                return dateString;
            }
        },
        
        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.fetchLeads(true); // Reset to first page and fetch with search
            }, 500);
        },
        
        clearFilters() {
            this.statusFilter = 'none';
            this.dateRange = '';
            this.searchQuery = '';
            this.dateType = 'created_at';
            this.currentPage = 1;
            this.successMessage = 'Filters cleared successfully';
            setTimeout(() => { this.successMessage = ''; }, 2000);
            this.fetchLeads(true);
        },
        
        // Pagination methods
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
                this.currentPage = page;
                this.jumpToPage = page; // Update jump input
                this.fetchLeads();
            }
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.jumpToPage = this.currentPage;
                this.fetchLeads();
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.jumpToPage = this.currentPage;
                this.fetchLeads();
            }
        },
        
        changeItemsPerPage(newSize) {
            this.itemsPerPage = parseInt(newSize);
            this.currentPage = 1;
            this.jumpToPage = 1;
            this.fetchLeads(true);
        },
        
        refreshData() {
            this.fetchLeads();
        }
    },
    
    watch: {
        // Watch for changes in filters and refetch data
        statusFilter() {
            this.fetchLeads(true);
        },
        dateType() {
            if (this.dateRange) {
                this.fetchLeads(true);
            }
        },
        // Update jumpToPage when currentPage changes
        currentPage(newPage) {
            this.jumpToPage = newPage;
        }
    },
    
    mounted() {
        // Initialize selected term from PHP data
        this.selectedTerm = window.current_sem || '';
        
        // Also sync with the select element
        const termSelect = document.getElementById('select-term-leads');
        if (termSelect && this.selectedTerm) {
            termSelect.value = this.selectedTerm;
        }
        
        // Fetch leads on initial load
        this.fetchLeads();
        
        // Set up periodic refresh (every 5 minutes)
        setInterval(() => {
            if (!this.loading) {
                this.fetchLeads();
            }
        }, 300000); // 5 minutes
    }
});

// Export for global access if needed
window.LeadsApp = LeadsApp;
