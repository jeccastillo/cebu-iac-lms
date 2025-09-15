(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminStudentPromptController', AdminStudentPromptController);

  AdminStudentPromptController.$inject = ['$location', '$http', 'APP_CONFIG', 'RoleService', 'StorageService'];
  function AdminStudentPromptController($location, $http, APP_CONFIG, RoleService, StorageService) {
    var vm = this;

    vm.title = 'Student Editor';
    vm.notice = '';
    vm.error = '';
    vm.studentId = '';     // manual numeric input (fallback)
    vm.selectedId = null;  // autocomplete ng-model (holds id)
    vm.items = [];         // autocomplete items
    vm.canEdit = RoleService.hasRole('admin');

    // Ensure admin
    if (!vm.canEdit) {
      vm.error = 'Access denied. Admin only.';
    }

    // Build admin headers to propagate user context to API
    function _adminHeaders(extra) {
      var headers = {};
      try {
        var state = StorageService.getJSON('loginState') || {};
        if (state && state.faculty_id != null) headers['X-Faculty-ID'] = state.faculty_id;
        if (Array.isArray(state.roles)) headers['X-User-Roles'] = state.roles.join(',');
        if (state && state.campus_id != null) headers['X-Campus-ID'] = state.campus_id;
      } catch (e) {}
      if (extra && typeof extra === 'object') {
        for (var k in extra) if (Object.prototype.hasOwnProperty.call(extra, k)) headers[k] = extra[k];
      }
      return { headers: headers, params: {} };
    }

    // Remote query for students (by student number or name)
    vm.onStudentQuery = function (q) {
      if (!vm.canEdit) return;
      var cfg = _adminHeaders();
      cfg.params = {
        q: q || '',
        page: 1,
        per_page: 20
      };
      return $http.get(APP_CONFIG.API_BASE + '/students', cfg)
        .then(function (resp) {
          var rows = (resp && resp.data && resp.data.data) ? resp.data.data : [];
          // Map to a normalized shape the autocomplete expects
          vm.items = (rows || []).map(function (r) {
            return {
              id: r.id,
              student_number: r.student_number || '',
              first_name: r.first_name || '',
              middle_name: r.middle_name || '',
              last_name: r.last_name || '',
              program: r.program || '',
              program_description: r.program_description || ''
            };
          });
        })
        .catch(function () {
          vm.items = [];
        });
    };

    // Navigate when a student is selected from autocomplete
    vm.goFromSelect = function () {
      if (vm.selectedId && !isNaN(parseInt(vm.selectedId, 10))) {
        $location.path('/admin/students/' + parseInt(vm.selectedId, 10) + '/edit');
      }
    };

    // Manual Go (fallback by numeric input)
    vm.go = function () {
      vm.error = '';
      vm.notice = '';

      // Prefer autocomplete selection if present
      if (vm.selectedId && !isNaN(parseInt(vm.selectedId, 10))) {
        $location.path('/admin/students/' + parseInt(vm.selectedId, 10) + '/edit');
        return;
      }

      var raw = (vm.studentId || '').toString().trim();
      if (!raw) {
        vm.error = 'Enter a student ID or use the autocomplete above.';
        return;
      }
      var n = parseInt(raw, 10);
      if (isNaN(n) || n <= 0) {
        vm.error = 'Student ID must be a positive integer (tb_mas_users.intID).';
        return;
      }
      $location.path('/admin/students/' + n + '/edit');
    };

    vm.back = function () {
      // If previously viewed a student, go back there; else to Students list
      try {
        var state = StorageService.getJSON('lastStudentId');
        if (state && state.id) {
          $location.path('/students/' + state.id);
          return;
        }
      } catch (e) {}
      $location.path('/students');
    };
  }
})();
