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
        sortField: 'date',
        sortDirection: 'desc',
        currentPage: 1,
        itemsPerPage: 20,
        searchTimeout: null
    },
    computed: {
        filteredLeads() {
            let filtered = this.leads;
            
            // Client-side search filtering
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(lead =>
                    (lead.first_name && lead.first_name.toLowerCase().includes(query)) ||
                    (lead.last_name && lead.last_name.toLowerCase().includes(query)) ||
                    (lead.program && lead.program.toLowerCase().includes(query)) ||
                    (lead.status && lead.status.toLowerCase().includes(query)) ||
                    (lead.slug && lead.slug.toLowerCase().includes(query))
                );
            }
            
            // Client-side sorting
            filtered.sort((a, b) => {
                let aVal = a[this.sortField] || '';
                let bVal = b[this.sortField] || '';
                
                // Handle date sorting
                if (this.sortField.includes('date')) {
                    aVal = new Date(aVal || '1900-01-01');
                    bVal = new Date(bVal || '1900-01-01');
                }
                
                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            
            return filtered;
        },
        paginatedLeads() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredLeads.slice(start, end);
        },
        totalPages() {
            return Math.ceil(this.filteredLeads.length / this.itemsPerPage);
        }
    },
    methods: {
        fetchLeads() {
            this.loading = true;
            this.errorMessage = '';
            
            // Prepare parameters for API call
            let params = {
                current_sem: this.selectedTerm,
                campus: window.campus || '',
                limit: 2000, // Get more records for client-side filtering
                page: 1,
                count_content: 10,
                search_data:'',
                search_field: 'first_name',
                sort_field: this.sortField,
                order_by: this.sortDirection
            };
            
            // Add filters if they're not default values
            if (this.statusFilter !== 'none') {
                params.filter = this.statusFilter;
            }
            
            if (this.dateRange) {
                // Parse date range and add to params
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
                        this.currentPage = 1; // Reset to first page
                        this.successMessage = `Loaded ${this.leads.length} applicant records`;
                        setTimeout(() => { this.successMessage = ''; }, 3000);
                    } else if (response.data && Array.isArray(response.data)) {
                        // Handle case where data is directly an array
                        this.leads = response.data;
                        this.currentPage = 1;
                        this.successMessage = `Loaded ${this.leads.length} applicant records`;
                        setTimeout(() => { this.successMessage = ''; }, 3000);
                    } else {
                        this.leads = [];
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
                    } else if (error.request) {
                        errorMsg += "Network error. Please check your connection.";
                    } else {
                        errorMsg += "An unexpected error occurred.";
                    }
                    
                    this.errorMessage = errorMsg;
                    this.loading = false;
                    this.leads = [];
                });
        },
        
        onTermChange() {
            // Redirect to new URL with selected term (maintaining CodeIgniter routing)
            const baseUrl = window.base_url || '';
            window.location.href = baseUrl + "admissionsV1/view_all_leads/" + this.selectedTerm;
        },
        
        exportToExcel() {
            if (this.leads.length === 0) {
                this.errorMessage = "No data to export";
                return;
            }
            
            this.exporting = true;
            
            // First get fresh data from API for export
            axios.get('https://smsapi.iacademy.edu.ph/api/v1/sms/admissions/student-info/view-applicants/' + this.selectedTerm + '/' + (window.campus || ''))
                .then(response => {
                    const exportData = response.data.data || this.filteredLeads;
                    
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
                this.currentPage = 1; // Reset to first page when searching
            }, 300);
        },
        
        clearFilters() {
            this.statusFilter = 'none';
            this.dateRange = '';
            this.searchQuery = '';
            this.dateType = 'created_at';
            this.currentPage = 1;
            this.successMessage = 'Filters cleared successfully';
            setTimeout(() => { this.successMessage = ''; }, 2000);
            this.fetchLeads();
        },
        
        refreshData() {
            this.fetchLeads();
        }
    },
    
    watch: {
        // Watch for changes in filters and refetch data
        statusFilter() {
            this.currentPage = 1;
        },
        dateType() {
            if (this.dateRange) {
                this.fetchLeads();
            }
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
