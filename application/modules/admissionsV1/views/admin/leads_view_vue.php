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

<div id="leads-app" class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Student Applicants Management</h1>
                    <nav class="flex space-x-2 mt-2 text-sm">
                        <a href="#" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                        <span class="text-gray-300">/</span>
                        <a href="#" class="text-gray-500 hover:text-gray-700">Student Applicants</a>
                        <span class="text-gray-300">/</span>
                        <span class="text-gray-800 font-medium">View All Leads</span>
                    </nav>
                </div>
                <button @click="exportToExcel" :disabled="loading" class="flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-file-excel mr-2"></i> 
                    <span v-if="exporting">Exporting...</span>
                    <span v-else>Export to Excel</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filters Section -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="space-y-4">
                <!-- Academic Term Selector -->
                <div>
                    <label for="select-term-leads" class="block text-sm font-medium text-gray-700 mb-1">Academic Term</label>
                    <select id="select-term-leads" v-model="selectedTerm" @change="onTermChange" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md border">
                        <?php foreach($sy as $s): ?>
                            <option value="<?php echo $s['intID']; ?>" <?php echo ($current_sem == $s['intID']) ? 'selected' : ''; ?>>
                                <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status and Date Range Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select id="status_filter" v-model="statusFilter" @change="fetchLeads" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md border">
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
                        <label for="range-to-select" class="block text-sm font-medium text-gray-700 mb-1">Date Type</label>
                        <select id="range-to-select" v-model="dateType" @change="fetchLeads" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md border">
                            <option value="created_at">Date Applied</option>
                            <option value="date_interviewed">Date Interviewed</option>
                            <option value="date_reserved">Date Reserved</option>
                            <option value="date_enrolled">Date Enrolled</option>
                        </select>
                    </div>

                    <!-- Date Range Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <input 
                            type="text" 
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" 
                            v-model="dateRange" 
                            @change="fetchLeads" 
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md border"
                        >
                        <p class="text-xs text-gray-500 mt-1">Format: 2024-01-01 to 2024-12-31</p>
                    </div>
                </div>

                <!-- Quick Stats Button -->
                <div class="pt-2">
                    <a href="<?php echo base_url(); ?>admissionsV1/admissions_report" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-success hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Quick Stats
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert/Error Message -->
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ errorMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="errorMessage = ''" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ successMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="successMessage = ''" class="text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Applicants Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Student Applicants 
                    <span v-if="!loading && leads.length > 0" class="text-sm text-gray-500 font-normal">({{ leads.length }} records)</span>
                </h3>
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="Search applicants..." 
                        v-model="searchQuery" 
                        @input="debouncedSearch"
                        class="pl-8 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                    />
                    <i class="fas fa-search absolute left-2 top-3 text-gray-400 text-sm"></i>
                </div>
            </div>
            
            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center">
                <div class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin mr-2 text-primary"></i>
                    <span class="text-gray-600">Loading applicants...</span>
                </div>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('date')">
                                Date Applied
                                <i class="fas fa-sort ml-1" :class="getSortIcon('date')"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Interviewed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Reserved</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Enrolled</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('last_name')">
                                Last Name
                                <i class="fas fa-sort ml-1" :class="getSortIcon('last_name')"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('first_name')">
                                First Name
                                <i class="fas fa-sort ml-1" :class="getSortIcon('first_name')"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ST</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('status')">
                                Status
                                <i class="fas fa-sort ml-1" :class="getSortIcon('status')"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="lead in paginatedLeads" :key="lead.slug || lead.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date_interviewed) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date_reserved) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(lead.date_enrolled) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ lead.last_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.first_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.tos }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ lead.program }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(lead.status)">
                                    {{ lead.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a :href="`<?php echo base_url(); ?>admissionsV1/view_lead_new/${lead.slug}`" target="_blank" class="text-primary hover:text-blue-900 hover:underline">
                                        <i class="fas fa-eye mr-1"></i>View Details
                                    </a>
                                    <a :href="`<?php echo base_url(); ?>finance/manualPay/${lead.slug}`" target="_blank" class="text-success hover:text-green-900 hover:underline">
                                        <i class="fas fa-dollar-sign mr-1"></i>Finance
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!loading && filteredLeads.length === 0">
                            <td class="px-6 py-4 text-center text-gray-500" colspan="10">
                                <div class="py-8">
                                    <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg">No applicants found</p>
                                    <p class="text-sm">Try adjusting your search criteria</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="!loading && filteredLeads.length > itemsPerPage" class="px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-sm text-gray-700">
                        Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredLeads.length) }} of {{ filteredLeads.length }} results
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        Previous
                    </button>
                    <span class="px-3 py-1 text-sm">Page {{ currentPage }} of {{ totalPages }}</span>
                    <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        Next
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
window.api_url = "<?php echo isset($api_url) ? $api_url : 'https://smsapi.iacademy.edu.ph/api/v1/sms/'; ?>";
window.base_url = "<?php echo base_url(); ?>";
window.campus = "<?php echo isset($campus) ? $campus : ''; ?>";
window.current_sem = "<?php echo $current_sem; ?>";
</script>

<!-- Include the dedicated Vue.js application -->
<script src="<?php echo base_url(); ?>assets/themes/default/js/leads-vue.js"></script>
