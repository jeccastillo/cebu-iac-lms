<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#3b82f6',
                    secondary: '#64748b',
                    success: '#22c55e',
                    warning: '#f59e0b',
                    danger: '#ef4444',
                    info: '#0ea5e9',
                    dark: '#1e293b',
                    light: '#f8fafc'
                }
            }
        }
    }
</script>

<div id="leads-app" class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 content-wrapper">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b border-gray-200">
        <div class="mx-auto px-6 py-6 sm:px-8 lg:px-10">
            <div class="flex justify-between items-center">
                <div class="space-y-3">
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Student Applicants Management</h1>
                    <nav class="flex items-center space-x-3 text-sm">
                        <a href="#" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">Dashboard</a>
                        <span class="text-gray-300">•</span>
                        <a href="#" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">Student Applicants</a>
                        <span class="text-gray-300">•</span>
                        <span class="text-gray-800 font-semibold">View All Leads</span>
                    </nav>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="refreshData" :disabled="loading" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <span v-if="loading">Refreshing...</span>
                        <span v-else>Refresh Data</span>
                    </button>
                    <button @click="exportToExcel" :disabled="loading || exporting" class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <span v-if="exporting">Exporting...</span>
                        <span v-else>📊 Export to Excel</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="mx-auto px-6 sm:px-8 lg:px-10 py-8">
        <!-- Filters Section -->
        <div class="bg-white shadow-lg rounded-xl p-8 mb-8 border border-gray-200">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Filter Options</h2>
                    <button @click="clearFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        🗑️ Clear Filters
                    </button>
                </div>
                
                <!-- Academic Term Selector -->
                <div>
                    <label for="select-term-leads" class="block text-sm font-semibold text-gray-700 mb-2">Academic Term</label>
                    <select id="select-term-leads" v-model="selectedTerm" @change="onTermChange" class="block w-full pl-4 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary rounded-lg border transition-all duration-200">
                        <?php foreach($sy as $s): ?>
                            <option value="<?php echo $s['intID']; ?>" <?php echo ($current_sem == $s['intID']) ? 'selected' : ''; ?>>
                                <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status and Date Range Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Status Filter -->
                    <div>
                        <label for="status_filter" class="block text-sm font-semibold text-gray-700 mb-2">Filter by Status</label>
                        <select id="status_filter" v-model="statusFilter" @change="fetchLeads" class="block w-full pl-4 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary rounded-lg border transition-all duration-200">
                            <option value="none">All Statuses</option>
                            <option value="New">New Applicant</option>
                            <option value="Waiting for Interview">Waiting for Interview</option>
                            <option value="For Interview">For Interview</option>
                            <option value="For Reservation">For Reservation</option>
                            <option value="Reserved">Reserved</option>
                            <option value="Floating">Floating</option>
                            <option value="For Enrollment">For Enrollment</option>
                            <option value="Enrolled">Enrolled</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Will Not Proceed">Will Not Proceed</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Disqualified">Disqualified</option>
                            <option value="Not Answering">Not Answering</option>
                        </select>
                    </div>

                    <!-- Date Type Selector -->
                    <div>
                        <label for="range-to-select" class="block text-sm font-semibold text-gray-700 mb-2">Date Type</label>
                        <select id="range-to-select" v-model="dateType" @change="fetchLeads" class="block w-full pl-4 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary rounded-lg border transition-all duration-200">
                            <option value="created_at">Date Applied</option>
                            <option value="date_interviewed">Date Interviewed</option>
                            <option value="date_reserved">Date Reserved</option>
                            <option value="date_enrolled">Date Enrolled</option>
                        </select>
                    </div>

                    <!-- Date Range Input -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                        <input 
                            type="text" 
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" 
                            v-model="dateRange" 
                            @change="fetchLeads" 
                            class="block w-full pl-4 pr-4 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary rounded-lg border transition-all duration-200"
                        >
                        <p class="text-xs text-gray-500 mt-2">Format: 2024-01-01 to 2024-12-31</p>
                    </div>
                </div>

                <!-- Quick Stats Button -->
                <div class="pt-4 border-t border-gray-200">
                    <a href="<?php echo base_url(); ?>admissionsV1/admissions_report" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-success hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success transition-all duration-200">
                        📊 Quick Stats
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert/Error Message -->
        <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-lg shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <span class="text-red-400 text-lg">⚠️</span>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="errorMessage = ''" class="text-red-400 hover:text-red-600 transition-colors duration-200">
                        <span class="text-lg">×</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <span class="text-green-400 text-lg">✅</span>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-green-700 font-medium">{{ successMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="successMessage = ''" class="text-green-400 hover:text-green-600 transition-colors duration-200">
                        <span class="text-lg">×</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Applicants Table -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">
                        Student Applicants 
                    </h3>
                    <p v-if="!loading && leads.length > 0" class="text-sm text-gray-600 mt-1">{{ leads.length }} total records found</p>
                </div>
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="🔍 Search applicants..." 
                        v-model="searchQuery" 
                        @input="debouncedSearch"
                        class="pl-4 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary w-64 transition-all duration-200"
                    />
                </div>
            </div>
            
            <!-- Loading State -->
            <div v-if="loading" class="p-12 text-center">
                <div class="inline-flex flex-col items-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-4"></div>
                    <span class="text-gray-600 font-medium">Loading applicants...</span>
                    <span class="text-gray-500 text-sm mt-1">Please wait while we fetch the data</span>
                </div>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors duration-200" @click="sortBy('date')">
                                Date Applied
                                <span class="ml-2 text-primary">{{ getSortIcon('date') }}</span>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date Interviewed</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date Reserved</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date Enrolled</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors duration-200" @click="sortBy('last_name')">
                                Last Name
                                <span class="ml-2 text-primary">{{ getSortIcon('last_name') }}</span>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors duration-200" @click="sortBy('first_name')">
                                First Name
                                <span class="ml-2 text-primary">{{ getSortIcon('first_name') }}</span>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ST</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Program</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors duration-200" @click="sortBy('status')">
                                Status
                                <span class="ml-2 text-primary">{{ getSortIcon('status') }}</span>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="lead in paginatedLeads" :key="lead.slug || lead.id" class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date_interviewed) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date_reserved) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date_enrolled) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ lead.last_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.first_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.tos }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.program }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(lead.status)">
                                    {{ lead.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-1">
                                    <a :href="`<?php echo base_url(); ?>admissionsV1/view_lead_new/${lead.slug}`" target="_blank" class="text-primary hover:text-blue-900 hover:underline transition-colors duration-200">
                                        👁️ View Details
                                    </a>
                                    <a :href="`<?php echo base_url(); ?>finance/manualPay/${lead.slug}`" target="_blank" class="text-success hover:text-green-900 hover:underline transition-colors duration-200">
                                        💰 Finance
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!loading && filteredLeads.length === 0">
                            <td class="px-6 py-4 text-center text-gray-500" colspan="10">
                                <div class="py-12">
                                    <div class="text-6xl text-gray-300 mb-4">🔍</div>
                                    <p class="text-lg font-medium text-gray-900 mb-2">No applicants found</p>
                                    <p class="text-sm text-gray-500">Try adjusting your search criteria or filters</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="!loading && filteredLeads.length > itemsPerPage" class="px-8 py-4 border-t border-gray-200 flex items-center justify-between bg-gray-50">
                <div class="flex items-center">
                    <span class="text-sm text-gray-700 font-medium">
                        Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredLeads.length) }} of {{ filteredLeads.length }} results
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        ← Previous
                    </button>
                    <span class="px-4 py-2 text-sm font-medium text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
                    <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        Next →
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Include Vue and Axios -->
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
// Global variables for Vue.js application
window.api_url = 'https://cebuapi.iacademy.edu.ph/api/v1/sms/';
window.base_url = "<?php echo base_url(); ?>";
window.campus = "<?php echo isset($campus) ? $campus : ''; ?>";
window.current_sem = "<?php echo $current_sem; ?>";
</script>

<!-- Include the dedicated Vue.js application -->
<script src="<?php echo base_url(); ?>assets/themes/default/js/leads-vue.js"></script>
