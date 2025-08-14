

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
    <style>
        .date-range-picker {
            background-image: url('data:image/svg+xml;utf8,<svg fill="%233b82f6" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>');
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1.5em;
        }
    </style>    
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Student Applicants Management</h1>
                        <nav class="flex space-x-4 mt-2">
                            <a href="#" class="text-gray-500 hover:text-gray-700 text-sm">Dashboard</a>
                            <span class="text-gray-300">/</span>
                            <a href="#" class="text-gray-500 hover:text-gray-700 text-sm">Student Applicants</a>
                            <span class="text-gray-300">/</span>
                            <a href="#" class="text-gray-800 font-medium text-sm">View All Leads</a>
                        </nav>
                    </div>
                    <button id="print_form" class="flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-file-excel mr-2"></i> Export to Excel
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
                        <select id="select-term-leads" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <?php foreach($sy as $s): ?>
                                <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status and Date Range Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Status Filter -->
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                            <select id="status_filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                                <option value="none" selected>All Statuses</option>
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
                            </select>
                        </div>

                        <!-- Date Type Selector -->
                        <div>
                            <label for="range-to-select" class="block text-sm font-medium text-gray-700 mb-1">Date Type</label>
                            <select id="range-to-select" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                                <option value="created_at">Date Applied</option>
                                <option value="date_interviewed">Date Interviewed</option>
                                <option value="date_reserved">Date Reserved</option>
                                <option value="date_enrolled">Date Enrolled</option>
                            </select>
                        </div>

                        <!-- Date Range Picker -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <button id="daterange-btn-users" class="w-full flex justify-between items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 date-range-picker">
                                <span>Select Date Range</span>
                                <i class="fas fa-caret-down ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Stats Button -->
                    <div class="pt-2">
                        <a href="<?php echo base_url(); ?>admissionsV1/admissions_report" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-success hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500">
                            Quick Stats
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alert Message (Hidden by default) -->
            <div id="alert-message" class="hidden bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-ban text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p id="alert-text" class="text-sm text-red-700">Sample error message goes here</p>
                    </div>
                </div>
            </div>

            <!-- Applicants Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Student Applicants</h3>
                    <div class="relative">
                        <input type="text" placeholder="Search applicants..." class="pl-8 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500">
                        <i class="fas fa-search absolute left-2 top-3 text-gray-400 text-sm"></i>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="subjects-table" class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>slug</th>
                                <th>Date</th>
                                <th>Date Interviewed</th>
                                <th>Date Reserved</th>
                                <th>Date Enrolled</th>
                                <th>Last Name</th>
                                <th>First Name</th> 
                                <th>ST</th>                           
                                <th>Program</th>                            
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>                                
            </div>
        </main>
    </div>
      

