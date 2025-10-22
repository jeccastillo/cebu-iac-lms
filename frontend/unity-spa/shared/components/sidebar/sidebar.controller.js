(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('SidebarController', SidebarController);

  SidebarController.$inject = ['$scope', '$location', '$document', 'StorageService', 'TermService', 'CampusService', 'RoleService', 'LinkService'];
  function SidebarController($scope, $location, $document, StorageService, TermService, CampusService, RoleService, LinkService) {
    var vm = this;

    // Sidebar state
    vm.isCollapsed = false;
    vm.isVisible = true;

    // User state
    vm.loginState = null;

    // Public methods
    vm.toggleCollapse = toggleCollapse;
    vm.isCurrentPath = isCurrentPath;

    // RBAC helpers exposed to template
    vm.canAccess = RoleService.canAccess;
    vm.hasRole = RoleService.hasRole;
    vm.roles = [];
    // Dashboard target path (student -> /student/dashboard, else /dashboard)
    vm.dashboardPath = '/dashboard';
    // External system links (e.g., CI endpoints)
    vm.links = LinkService.buildLinks();

    // Group open state (persisted) and hierarchical menu
    vm.groupOpen = {};
    vm.popoutTop = {};
    vm.menu = [
      {
        key: 'faculty',
        label: 'Faculty',
        children: [
          { label: 'Profile', path: '/faculty/profile' },
          { label: 'My Classes', path: '/faculty/classes' }
        ]
      },
      {
        key: 'registrar',
        label: 'Registrar',
        children: [
          { label: 'Reports', path: '/registrar/reports' },
          { label: 'Transcripts / Copy of Grades', path: '/registrar/transcripts' },
          { label: 'Enlistment', path: '/registrar/enlistment' },
          { label: 'Shifting', path: '/registrar/shifting' },
          { label: 'Enlistment Applicants', path: '/registrar/enlistment-applicants' },
          { label: 'Slot Monitoring', path: '/registrar/sections-slots' },
          { label: 'Credit Subjects', path: '/registrar/credit-subjects' },
          { label: 'Change Student Password', path: '/registrar/change-password' },
          { label: 'Classlists', path: '/classlists' },          
        ]
      },
      {
        key: 'admissions',
        label: 'Admissions',
        children: [
          { label: 'Requirements', path: '/admissions/requirements' },
          { label: 'Previous Schools', path: '/admissions/previous-schools' },
          { label: 'Applicant Types', path: '/admissions/applicant-types' },
          { label: 'Applicants', path: '/admissions/applicants' },
          { label: 'Applicants Analytics', path: '/admissions/applicants/analytics' }
        ]
      },
      {
        key: 'scholarship',
        label: 'Scholarship',
        children: [
          { label: 'Assign Scholarships', path: '/scholarship/assignments' },
          { label: 'Students', path: '/scholarship/students' },
          { label: 'Catalog', path: '/scholarship/scholarships' }
        ]
      },
      {
        key: 'finance',
        label: 'Finance',
        children: [
          { label: 'Ledger', path: '/finance/ledger' },
          { label: 'Tuition Setup', path: '/finance/tuition-years' },
          { label: 'Payment Descriptions', path: '/finance/payment-descriptions' },
          { label: 'Payment Modes', path: '/finance/payment-modes' },
          { label: 'Student Billing', path: '/finance/student-billing' },
          { label: 'Payment Actions', path: '/finance/payment-actions' },
          { label: 'Official Receipt Reports', path: '/finance/or-reports' },
          { label: 'Invoice Reports', path: '/finance/invoice-reports' },
          { label: 'Non-Student Payments', path: '/finance/non-student-payments' },
          { label: 'Cashier Admin', path: '/cashier-admin' },
          { label: 'Credit/Debit', path: '/finance/debit-credit' },                    
        ]
      },
      {
        key: 'department',
        label: 'Department',
        children: [
          { label: 'Deficiency Tagging', path: '/department/deficiencies' }
        ]
      },
      {
        key: 'clinic',
        label: 'Clinic',
        children: [
          { label: 'Health Records', path: '/clinic' }
        ]
      },
      {
        key: 'help',
        label: 'Help / Docs',
        children: [
          { label: 'Documentation', path: '/docs' }
        ]
      },
      {
        key: 'academics',
        label: 'Academics',
        children: [
          { label: 'Programs', path: '/programs' },
          { label: 'Subjects', path: '/subjects' },
          { label: 'Curricula', path: '/curricula' },
          { label: 'School Terms', path: '/school-years' },
          { label: 'Classrooms', path: '/classrooms' },
          { label: 'Schedules', path: '/schedules' },
          // { label: 'Faculty Loading', path: '/faculty-loading' },
          { label: 'Faculty Loading', path: '/faculty-loading/by-faculty' },
          { label: 'Grading Systems', path: '/grading-systems' },
          { label: 'Advisors', path: '/advisors' },
          { label: 'Advising List', path: '/advisors/quick-view' },
          { label: 'Grading Sheet', path: '/academics/grading-sheet' }
        ]
      },
      {
        key: 'student',
        label: 'Student',
        children: [
          { label: 'Finances', path: '/student/finances' },
          { label: 'Request Program Change', path: '/student/change-program-request' }
        ]
      },
      {
        key: 'admin',
        label: 'Admin',
        children: [
          { label: 'Student Editor', path: '/admin/students/prompt' },
          { label: 'Invoices', path: '/admin/invoices' },
          { label: 'Payment Details', path: '/admin/payment-details' },
          { label: 'Payment Details Import', path: '/admin/payment-details/import' },
          { label: 'Users Import', path: '/admin/users-import' },
          { label: 'Subjects Import', path: '/admin/subjects-import' },
          { label: 'Programs Import', path: '/admin/programs-import' },
          { label: 'Curricula Import', path: '/admin/curricula-import' },
          { label: 'Faculty', path: '/faculty' },
          { label: 'Roles', path: '/roles' },
          { label: 'System Alerts', path: '/admin/system-alerts' },
          { label: 'Logs', path: '/logs' }
        ]
      }
    ];

    // Helpers for hierarchical menu
    vm.toggleGroup = toggleGroup;
    vm.openGroup = openGroup;
    vm.closeAllGroups = closeAllGroups;
    vm.togglePopout = togglePopout;
    vm.isAnyPopoutOpen = isAnyPopoutOpen;
    vm.isActivePrefix = isActivePrefix;
    vm.canShowGroup = canShowGroup;

    // Initialize
    activate();

    function activate() {
      // Get login state
      vm.loginState = StorageService.getJSON('loginState');
      RoleService.normalizeState(vm.loginState);
      vm.roles = RoleService.getRoles();
      vm.dashboardPath = vm.hasRole('student_view') ? '/student/dashboard' : '/dashboard';
      
      // Initialize services
      TermService.init();
      CampusService.init();

      // Restore group open state (default open if not yet stored)
      try {
        (vm.menu || []).forEach(function(g){
          var k = 'sidebarOpen.' + g.key;
          var v = StorageService.get(k);
          vm.groupOpen[g.key] = (v === 'true' || v === null || typeof v === 'undefined');
        });
      } catch (e) {}

      // Watch for login state changes
      $scope.$watch(function() {
        return StorageService.getJSON('loginState');
      }, function(newState) {
        vm.loginState = newState;
        if (newState && newState.loggedIn) {
          RoleService.normalizeState(newState);
          vm.roles = RoleService.getRoles();
        } else {
          vm.roles = [];
        }
        // Update dashboard target whenever roles/state change
        vm.dashboardPath = vm.hasRole('student_view') ? '/student/dashboard' : '/dashboard';
        vm.isVisible = !!(newState && newState.loggedIn);
      }, true);

      // Watch route changes to determine sidebar visibility
      $scope.$on('$routeChangeSuccess', function() {
        var path = $location.path();
        // Hide sidebar on login page
        vm.isVisible = path !== '/login' && !!(vm.loginState && vm.loginState.loggedIn);
        // Update dynamic Admin link for editing current student (if applicable)
        updateAdminEditStudentLink();
      });

      // Load collapsed state from storage
      var savedCollapsed = StorageService.get('sidebarCollapsed');
      if (savedCollapsed === 'true') {
        vm.isCollapsed = true;
      }
      // Initialize dynamic Admin link for "Edit Current Student" (if on a student viewer route)
      updateAdminEditStudentLink();

      // Close popouts on ESC key
      var keydownHandler = function (e) {
        if (e && (e.key === 'Escape' || e.keyCode === 27)) {
          $scope.$applyAsync(closeAllGroups);
        }
      };
      $document.on('keydown', keydownHandler);
      $scope.$on('$destroy', function () {
        try { $document.off('keydown', keydownHandler); } catch (e) {}
      });
    }

    function toggleCollapse() {
      vm.isCollapsed = !vm.isCollapsed;
      StorageService.set('sidebarCollapsed', vm.isCollapsed.toString());
    }

    function isCurrentPath(path) {
      return $location.path() === path;
    }

    function isActivePrefix(path) {
      try {
        var cur = $location.path() || '';
        return cur === path || cur.indexOf(path + '/') === 0;
      } catch (e) {
        return false;
      }
    }

    function toggleGroup(key) {
      if (!key) return;
      // Accordion behavior: when opening one group, close all others
      var willOpen = !vm.groupOpen[key];

      try {
        // Close all groups first
        Object.keys(vm.groupOpen).forEach(function (k) {
          vm.groupOpen[k] = false;
          StorageService.set('sidebarOpen.' + k, 'false');
        });
      } catch (e) {}

      // Apply final state to the requested group (open if it was previously closed)
      vm.groupOpen[key] = willOpen;
      try {
        StorageService.set('sidebarOpen.' + key, willOpen ? 'true' : 'false');
      } catch (e) {}
    }

    // Explicitly open a group (used for popout on hover/click)
    function openGroup(key) {
      if (!key) return;
      closeAllGroups();
      vm.groupOpen[key] = true;
      try {
        StorageService.set('sidebarOpen.' + key, 'true');
      } catch (e) {}
    }

    // Close all groups helper
    function closeAllGroups() {
      try {
        Object.keys(vm.groupOpen).forEach(function (k) {
          vm.groupOpen[k] = false;
          StorageService.set('sidebarOpen.' + k, 'false');
        });
      } catch (e) {}
    }

    function togglePopout(key, $event) {
      if (!key) return;

      // Toggle behavior
      if (vm.groupOpen[key]) {
        closeAllGroups();
        return;
      }

      // Open requested group
      openGroup(key);

      // Compute top so the popout aligns with the clicked menu item
      try {
        var header = 56; // keep in sync with CSS --header-height
        var pad = 8;
        var rect = ($event && ($event.currentTarget || $event.target)) ? ($event.currentTarget || $event.target).getBoundingClientRect() : null;

        // Popout height mirrors CSS rule (50% of visible area below header)
        var popH = Math.floor((window.innerHeight - header) / 2);

        // Desired top equals the clicked row's viewport top
        var desiredTop = rect ? rect.top : (header + pad);

        // Clamp so it does not exceed viewport bottom
        var maxTop = window.innerHeight - popH - pad;
        var clampedTop = Math.max(header + pad, Math.min(maxTop, desiredTop));

        vm.popoutTop[key] = clampedTop + 'px';
      } catch (e) {
        // Fallback to a sensible default if any error occurs
        vm.popoutTop[key] = (56 + 8) + 'px';
      }
    }

    function isAnyPopoutOpen() {
      try {
        return Object.keys(vm.groupOpen || {}).some(function (k) { return !!vm.groupOpen[k]; });
      } catch (e) { return false; }
    }
 
    function canShowGroup(group) {
      if (!group || !group.children || !group.children.length) return false;

      try {
        var gkey = (group.key || '').toLowerCase();

        // Hide the entire Student group for non-student logins
        if (gkey === 'student') {
          if (!(vm && typeof vm.hasRole === 'function' && vm.hasRole('student_view'))) {
            return false;
          }
        }

        // Hide specific groups for student users
        if (vm && typeof vm.hasRole === 'function' && vm.hasRole('student_view')) {
          if (gkey === 'department' || gkey === 'clinic') {
            return false;
          }
        }
      } catch (e) {}

      for (var i = 0; i < group.children.length; i++) {
        var c = group.children[i];
        var testPath = c.path || '';
        if (testPath && vm.canAccess(testPath)) {
          return true;
        }
      }
      return false;
    }

    // ---------------- Dynamic Admin Link: Edit Current Student ----------------

    // Extracts a numeric student id from routes like /students/:id
    function extractCurrentStudentId() {
      try {
        var p = $location.path() || '';
        var m = p.match(/^\/students\/(\d+)/);
        return m && m[1] ? m[1] : null;
      } catch (e) { return null; }
    }

    // Ensures an "Edit Current Student" entry exists under Admin when viewing /students/:id.
    // Uses a dynamic href so the link points to /admin/students/:id/edit.
    function updateAdminEditStudentLink() {
      var sid = extractCurrentStudentId();

      // Locate Admin group
      var adminGroup = null;
      try {
        for (var gi = 0; gi < (vm.menu || []).length; gi++) {
          if (vm.menu[gi] && vm.menu[gi].key === 'admin') {
            adminGroup = vm.menu[gi];
            break;
          }
        }
      } catch (e) { adminGroup = null; }

      if (!adminGroup || !Array.isArray(adminGroup.children)) return;

      // Find existing dynamic child (by special key)
      var idx = -1;
      for (var ci = 0; ci < adminGroup.children.length; ci++) {
        var ch = adminGroup.children[ci] || {};
        if (ch._key === 'admin_edit_current_student') { idx = ci; break; }
      }

      if (sid) {
        var href = '/admin/students/' + sid + '/edit';
        var item = {
          _key: 'admin_edit_current_student',
          label: 'Edit Current Student',
          // path used for RBAC check; keep under /admin to match Access Matrix/route guards
          path: '/admin/students',
          // href used for actual navigation to include the id parameter
          href: href
        };

        if (idx >= 0) {
          adminGroup.children[idx] = item;
        } else {
          // Prefer to insert near the top of Admin tools
          adminGroup.children.unshift(item);
        }
      } else {
        // Not in a student viewer route; remove dynamic item if present
        if (idx >= 0) {
          adminGroup.children.splice(idx, 1);
        }
      }
    }
  }

})();
