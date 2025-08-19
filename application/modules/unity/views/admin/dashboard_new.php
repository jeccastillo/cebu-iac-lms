<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iACADEMY SMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
        
        .fade-enter-active, .fade-leave-active {
            transition: opacity 0.5s ease;
        }
        
        .fade-enter-from, .fade-leave-to {
            opacity: 0;
        }
        
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .chart-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stats-card-2 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-card-3 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stats-card-4 {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .notification-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            position: absolute;
            top: -2px;
            right: -2px;
        }
        
        .menu-item {
            transition: all 0.2s ease;
        }
        
        .menu-item:hover {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
            padding-left: 20px;
        }
        
        .menu-item.active {
            background: rgba(59, 130, 246, 0.2);
            border-left: 4px solid #3b82f6;
            padding-left: 20px;
            color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="app">
        <!-- Main Container -->
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <div :class="{'w-64': sidebarOpen, 'w-16': !sidebarOpen}" 
                 class="bg-white shadow-lg sidebar-transition flex-shrink-0 border-r border-gray-200">
                
                <!-- Logo Section -->
                <div class="p-4 border-b border-gray-200">
                    <div v-if="sidebarOpen" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">iACADEMY</h1>
                            <p class="text-xs text-gray-500">School Management</p>
                        </div>
                    </div>
                    <div v-else class="flex justify-center">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="mt-6 px-2">
                    <div v-for="item in menuItems" :key="item.id" 
                         @click="activeSection = item.id"
                         :class="{'active': activeSection === item.id}"
                         class="menu-item flex items-center px-4 py-3 text-gray-700 hover:text-blue-600 cursor-pointer rounded-lg mb-1">
                        <i :class="item.icon" class="w-5 text-center"></i>
                        <span v-if="sidebarOpen" class="ml-3 font-medium">{{ item.name }}</span>
                        <span v-if="item.badge && sidebarOpen" 
                              class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ item.badge }}</span>
                    </div>
                </nav>
                
                <!-- User Profile in Sidebar -->
                <div v-if="sidebarOpen" class="absolute bottom-4 left-4 right-4">
                    <div class="bg-gray-100 rounded-lg p-3">
                        <div class="flex items-center space-x-3">
                            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/e9a10607-bb4c-484c-84c7-fdc15c336ef3.png" 
                                 alt="Professional headshot of school administrator with friendly smile wearing business attire"
                                 class="w-10 h-10 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ currentUser.name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ currentUser.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1 overflow-hidden">
                <!-- Top Header -->
                <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Left Side - Menu Toggle & Title -->
                        <div class="flex items-center space-x-4">
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                <i class="fas fa-bars text-lg"></i>
                            </button>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ getCurrentSectionTitle() }}</h2>
                                <p class="text-sm text-gray-500">{{ getCurrentDate() }}</p>
                            </div>
                        </div>
                        
                        <!-- Right Side - User Controls -->
                        <div class="flex items-center space-x-4">
                            <!-- Search -->
                            <div class="relative hidden md:block">
                                <input type="text" 
                                       placeholder="Search students, teachers..."
                                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            
                            <!-- Notifications -->
                            <div class="relative">
                                <button @click="showNotifications = !showNotifications"
                                        class="p-2 text-gray-500 hover:text-gray-700 relative">
                                    <i class="fas fa-bell text-lg"></i>
                                    <div v-if="notifications.length > 0" class="notification-dot"></div>
                                </button>
                                
                                <!-- Notifications Dropdown -->
                                <div v-if="showNotifications" 
                                     class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                    <div class="p-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                                    </div>
                                    <div class="max-h-64 overflow-y-auto">
                                        <div v-for="notification in notifications" :key="notification.id"
                                             class="p-4 border-b border-gray-100 hover:bg-gray-50">
                                            <div class="flex items-start space-x-3">
                                                <div :class="notification.iconClass" class="w-8 h-8 rounded-full flex items-center justify-center">
                                                    <i :class="notification.icon" class="text-xs"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-800">{{ notification.title }}</p>
                                                    <p class="text-xs text-gray-500">{{ notification.time }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Menu -->
                            <div class="relative">
                                <button @click="showUserMenu = !showUserMenu"
                                        class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100">
                                    <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/1ef2864e-c9e8-414c-b81d-9b929ed9c2fc.png" 
                                         alt="School administrator profile photo showing professional headshot with warm smile"
                                         class="w-8 h-8 rounded-full object-cover">
                                    <span class="hidden md:block text-sm font-medium text-gray-700">{{ currentUser.name }}</span>
                                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                </button>
                                
                                <!-- User Dropdown -->
                                <div v-if="showUserMenu" 
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                    <div class="py-2">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-user mr-2"></i>Profile
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-cog mr-2"></i>Settings
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-question-circle mr-2"></i>Help
                                        </a>
                                        <hr class="my-2">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                
                <!-- Main Dashboard Content -->
                <main class="flex-1 overflow-y-auto p-6">
                    <transition name="fade" mode="out-in">
                        <!-- Dashboard Overview -->
                        <div v-if="activeSection === 'dashboard'" key="dashboard">
                            <!-- Stats Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                <div class="stats-card rounded-xl p-6 text-white card-hover">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-white/80 text-sm">Total Students</p>
                                            <p class="text-3xl font-bold">{{ dashboardStats.totalStudents }}</p>
                                        </div>
                                        <i class="fas fa-user-graduate text-2xl opacity-80"></i>
                                    </div>
                                </div>
                                
                                <div class="stats-card-2 rounded-xl p-6 text-white card-hover">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-white/80 text-sm">Total Teachers</p>
                                            <p class="text-3xl font-bold">{{ dashboardStats.totalTeachers }}</p>
                                        </div>
                                        <i class="fas fa-chalkboard-teacher text-2xl opacity-80"></i>
                                    </div>
                                </div>
                                
                                <div class="stats-card-3 rounded-xl p-6 text-white card-hover">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-white/80 text-sm">Total Classes</p>
                                            <p class="text-3xl font-bold">{{ dashboardStats.totalClasses }}</p>
                                        </div>
                                        <i class="fas fa-door-open text-2xl opacity-80"></i>
                                    </div>
                                </div>
                                
                                <div class="stats-card-4 rounded-xl p-6 text-white card-hover">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-white/80 text-sm">Attendance Rate</p>
                                            <p class="text-3xl font-bold">{{ dashboardStats.attendanceRate }}%</p>
                                        </div>
                                        <i class="fas fa-chart-line text-2xl opacity-80"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Charts and Recent Activity -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                                <!-- Attendance Chart -->
                                <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm card-hover">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Attendance</h3>
                                    <div class="chart-container rounded-lg h-64 flex items-center justify-center text-white">
                                        <div class="text-center">
                                            <i class="fas fa-chart-bar text-4xl mb-2"></i>
                                            <p>Interactive Chart Placeholder</p>
                                            <p class="text-sm opacity-80">Weekly attendance visualization</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recent Activities -->
                                <div class="bg-white rounded-xl p-6 shadow-sm card-hover">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activities</h3>
                                    <div class="space-y-4">
                                        <div v-for="activity in recentActivities" :key="activity.id"
                                             class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <div :class="activity.iconClass" class="w-8 h-8 rounded-full flex items-center justify-center">
                                                <i :class="activity.icon" class="text-xs"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-800">{{ activity.title }}</p>
                                                <p class="text-xs text-gray-500">{{ activity.time }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="bg-white rounded-xl p-6 shadow-sm">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                                        <i class="fas fa-user-plus text-2xl text-blue-600 mb-2"></i>
                                        <p class="text-sm font-medium text-gray-700">Add Student</p>
                                    </button>
                                    <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                                        <i class="fas fa-calendar-plus text-2xl text-green-600 mb-2"></i>
                                        <p class="text-sm font-medium text-gray-700">Schedule Class</p>
                                    </button>
                                    <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
                                        <i class="fas fa-file-alt text-2xl text-purple-600 mb-2"></i>
                                        <p class="text-sm font-medium text-gray-700">Generate Report</p>
                                    </button>
                                    <button class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors">
                                        <i class="fas fa-bell text-2xl text-orange-600 mb-2"></i>
                                        <p class="text-sm font-medium text-gray-700">Send Notice</p>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Students Section -->
                        <div v-else-if="activeSection === 'students'" key="students">
                            <div class="bg-white rounded-xl shadow-sm">
                                <div class="p-6 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-800">Student Management</h3>
                                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-plus mr-2"></i>Add Student
                                        </button>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div v-for="student in students" :key="student.id"
                                             class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div class="flex items-center space-x-4">
                                                <img :src="student.avatar" 
                                                     :alt="'Student portrait of ' + student.name + ' wearing school uniform with friendly smile'"
                                                     class="w-12 h-12 rounded-full object-cover">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-800">{{ student.name }}</h4>
                                                    <p class="text-sm text-gray-500">{{ student.class }} - {{ student.rollNumber }}</p>
                                                    <p class="text-xs text-gray-400">{{ student.email }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Other Sections Placeholder -->
                        <div v-else :key="activeSection" class="bg-white rounded-xl p-8 shadow-sm text-center">
                            <i class="fas fa-construction text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ getCurrentSectionTitle() }}</h3>
                            <p class="text-gray-500">This section is under development. Coming soon with advanced features!</p>
                        </div>
                    </transition>
                </main>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    sidebarOpen: true,
                    activeSection: 'dashboard',
                    showNotifications: false,
                    showUserMenu: false,
                    currentUser: {
                        name: 'Dr. Sarah Johnson',
                        role: 'Principal',
                        avatar: 'https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/baacbc89-d020-4bd7-af2f-83a401af2c41.png'
                    },
                    menuItems: [
                        { id: 'dashboard', name: 'Dashboard', icon: 'fas fa-tachometer-alt' },
                        { id: 'students', name: 'Students', icon: 'fas fa-user-graduate', badge: '1,234' },
                        { id: 'teachers', name: 'Teachers', icon: 'fas fa-chalkboard-teacher' },
                        { id: 'classes', name: 'Classes', icon: 'fas fa-door-open' },
                        { id: 'attendance', name: 'Attendance', icon: 'fas fa-calendar-check' },
                        { id: 'grades', name: 'Grades', icon: 'fas fa-graduation-cap' },
                        { id: 'schedule', name: 'Schedule', icon: 'fas fa-calendar-alt' },
                        { id: 'fees', name: 'Fees', icon: 'fas fa-dollar-sign' },
                        { id: 'library', name: 'Library', icon: 'fas fa-book' },
                        { id: 'reports', name: 'Reports', icon: 'fas fa-chart-bar' },
                        { id: 'settings', name: 'Settings', icon: 'fas fa-cog' }
                    ],
                    dashboardStats: {
                        totalStudents: 1234,
                        totalTeachers: 89,
                        totalClasses: 45,
                        attendanceRate: 94
                    },
                    notifications: [
                        {
                            id: 1,
                            title: 'New student enrollment pending approval',
                            time: '2 minutes ago',
                            icon: 'fas fa-user-plus',
                            iconClass: 'bg-blue-100 text-blue-600'
                        },
                        {
                            id: 2,
                            title: 'Grade 10 exam results published',
                            time: '1 hour ago',
                            icon: 'fas fa-clipboard-check',
                            iconClass: 'bg-green-100 text-green-600'
                        },
                        {
                            id: 3,
                            title: 'Low attendance alert for Class 8-A',
                            time: '3 hours ago',
                            icon: 'fas fa-exclamation-triangle',
                            iconClass: 'bg-yellow-100 text-yellow-600'
                        }
                    ],
                    recentActivities: [
                        {
                            id: 1,
                            title: 'John Doe enrolled in Grade 9',
                            time: '10 min ago',
                            icon: 'fas fa-user-plus',
                            iconClass: 'bg-blue-100 text-blue-600'
                        },
                        {
                            id: 2,
                            title: 'Math test scheduled for Grade 8',
                            time: '30 min ago',
                            icon: 'fas fa-calendar-plus',
                            iconClass: 'bg-green-100 text-green-600'
                        },
                        {
                            id: 3,
                            title: 'Fee payment received from Sarah Wilson',
                            time: '1 hour ago',
                            icon: 'fas fa-dollar-sign',
                            iconClass: 'bg-yellow-100 text-yellow-600'
                        },
                        {
                            id: 4,
                            title: 'New teacher Mrs. Anderson joined',
                            time: '2 hours ago',
                            icon: 'fas fa-user-tie',
                            iconClass: 'bg-purple-100 text-purple-600'
                        }
                    ],
                    students: [
                        {
                            id: 1,
                            name: 'Emma Thompson',
                            class: 'Grade 10-A',
                            rollNumber: 'ST001',
                            email: 'emma.thompson@school.edu',
                            avatar: 'https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/4b52b874-e779-490c-bea1-68deb5c352e3.png'
                        },
                        {
                            id: 2,
                            name: 'Michael Chen',
                            class: 'Grade 9-B',
                            rollNumber: 'ST002',
                            email: 'michael.chen@school.edu',
                            avatar: 'https://placehold.co/48x48'
                        },
                        {
                            id: 3,
                            name: 'Sophie Williams',
                            class: 'Grade 11-A',
                            rollNumber: 'ST003',
                            email: 'sophie.williams@school.edu',
                            avatar: 'https://placehold.co/48x48'
                        },
                        {
                            id: 4,
                            name: 'David Rodriguez',
                            class: 'Grade 8-C',
                            rollNumber: 'ST004',
                            email: 'david.rodriguez@school.edu',
                            avatar: 'https://placehold.co/48x48'
                        },
                        {
                            id: 5,
                            name: 'Isabella Brown',
                            class: 'Grade 10-B',
                            rollNumber: 'ST005',
                            email: 'isabella.brown@school.edu',
                            avatar: 'https://placehold.co/48x48'
                        },
                        {
                            id: 6,
                            name: 'James Wilson',
                            class: 'Grade 9-A',
                            rollNumber: 'ST006',
                            email: 'james.wilson@school.edu',
                            avatar: 'https://placehold.co/48x48'
                        }
                    ]
                }
            },
            methods: {
                getCurrentSectionTitle() {
                    const section = this.menuItems.find(item => item.id === this.activeSection);
                    return section ? section.name : 'Dashboard';
                },
                getCurrentDate() {
                    const now = new Date();
                    return now.toLocaleDateString('en-US', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                }
            },
            mounted() {
                // Close dropdowns when clicking outside
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.relative')) {
                        this.showNotifications = false;
                        this.showUserMenu = false;
                    }
                });
            }
        }).mount('#app');
    </script>
</body>
</html>

