(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminSystemAlertsController', AdminSystemAlertsController);

  AdminSystemAlertsController.$inject = ['$http', '$q', 'APP_CONFIG', 'StorageService', 'RoleService', 'CampusService'];
  function AdminSystemAlertsController($http, $q, APP_CONFIG, StorageService, RoleService, CampusService) {
    var vm = this;

    vm.loading = false;
    vm.error = null;
    vm.notice = null;
    vm.rows = [];

    vm.form = {
      title: '',
      message: '',
      link: '',
      type: 'info', // 'success' | 'warning' | 'error' | 'info'
      target_all: true,
      role_codes: [],
      campus_ids: [],
      starts_at: '',
      ends_at: '',
      intActive: 1,
      system_generated: 0
    };

    vm.types = ['success', 'warning', 'error', 'info'];
    vm.availableRoles = [
      'admin', 'registrar', 'finance', 'admissions', 'scholarship',
      'campus_admin', 'faculty_admin', 'faculty', 'student_view', 'cashier_admin'
    ];

    vm.load = load;
    vm.submit = submit;
    vm.disable = disable;
    vm.clearForm = clearForm;
    vm.open = open;
    vm.toggleRole = toggleRole;
    vm.toggleCampus = toggleCampus;
    vm.isRoleSelected = isRoleSelected;
    vm.isCampusSelected = isCampusSelected;

    activate();

    function activate() {
      vm.campuses = [];
      // Prefetch campuses for campus_ids selection (best-effort)
      try {
        CampusService.init().then(function () {
          vm.campuses = CampusService.availableCampuses || [];
        });
      } catch (e) {}
      load();
    }

    function _headers() {
      var headers = {};
      try {
        var state = StorageService.getJSON('loginState') || {};
        // Populate role headers if available (helpful for Gate fallback)
        var roles = RoleService && RoleService.getRoles ? RoleService.getRoles() : [];
        if (Array.isArray(roles) && roles.length) headers['X-User-Roles'] = roles.join(',');
        // Identity
        if (state && state.username) headers['X-User-Name'] = state.username;
        if (state && state.loginType) headers['X-Login-Type'] = state.loginType;
        if (state && state.user_id != null) headers['X-User-ID'] = state.user_id;
        else if (state && state.faculty_id != null) headers['X-User-ID'] = state.faculty_id;
        // RequireRole middleware expects X-Faculty-ID for admin-guarded endpoints
        if (state && state.faculty_id != null) headers['X-Faculty-ID'] = state.faculty_id;
        // Campus (optional)
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id != null) headers['X-Campus-ID'] = campus.id;
      } catch (e) {}
      return headers;
    }

    function load() {
      vm.loading = true;
      vm.error = null; vm.notice = null;
      return $http.get(APP_CONFIG.API_BASE + '/system-alerts', {
        headers: _headers(),
        params: { per_page: 50, active: 1 }
      })
        .then(function (resp) {
          var payload = resp && resp.data ? resp.data : resp;
          vm.rows = (payload && Array.isArray(payload.data)) ? payload.data : (Array.isArray(payload) ? payload : []);
        })
        .catch(function (err) {
          vm.error = (err && err.message) ? err.message : 'Failed to load alerts';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function submit() {
      vm.error = null; vm.notice = null;
      var body = _normalizedForm();
      if (!body.message || !body.type) {
        vm.error = 'Message and type are required.';
        return $q.reject(new Error('validation'));
      }
      vm.loading = true;
      return $http.post(APP_CONFIG.API_BASE + '/system-alerts', body, { headers: _headers() })
        .then(function (resp) {
          vm.notice = 'Alert created';
          clearForm();
          return load();
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Create failed';
        })
        .finally(function () { vm.loading = false; });
    }

    function disable(row) {
      if (!row || row.id == null) return;
      vm.loading = true; vm.error = null; vm.notice = null;
      return $http.delete(APP_CONFIG.API_BASE + '/system-alerts/' + encodeURIComponent(row.id), {
        headers: _headers()
      })
        .then(function () {
          vm.notice = 'Alert disabled';
          return load();
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Disable failed';
        })
        .finally(function () { vm.loading = false; });
    }

    function clearForm() {
      vm.form = {
        title: '',
        message: '',
        link: '',
        type: 'info',
        target_all: true,
        role_codes: [],
        campus_ids: [],
        starts_at: '',
        ends_at: '',
        intActive: 1,
        system_generated: 0
      };
    }

    function toggleRole(code) {
      if (!code) return;
      var a = vm.form.role_codes || [];
      var i = a.indexOf(code);
      if (i === -1) a.push(code);
      else a.splice(i, 1);
      vm.form.role_codes = a;
    }
    function isRoleSelected(code) {
      return Array.isArray(vm.form.role_codes) && vm.form.role_codes.indexOf(code) !== -1;
    }

    function toggleCampus(id) {
      if (id == null) return;
      var a = vm.form.campus_ids || [];
      id = parseInt(id, 10);
      var idx = a.indexOf(id);
      if (idx === -1) a.push(id);
      else a.splice(idx, 1);
      vm.form.campus_ids = a;
    }
    function isCampusSelected(id) {
      id = parseInt(id, 10);
      return Array.isArray(vm.form.campus_ids) && vm.form.campus_ids.indexOf(id) !== -1;
    }

    function _normalizedForm() {
      var f = angular.copy(vm.form);
      // Normalize link (trim)
      if (typeof f.link === 'string') f.link = f.link.trim();
      // Coerce booleans/ints
      f.target_all = !!f.target_all;
      f.intActive = f.intActive ? 1 : 0;
      f.system_generated = f.system_generated ? 1 : 0;
      // Normalize role codes to lowercase no blanks
      if (Array.isArray(f.role_codes)) {
        f.role_codes = f.role_codes.map(function (r) { return (r || '').toLowerCase().trim(); })
          .filter(function (r) { return !!r; });
      }
      // Ensure campus ids are ints
      if (Array.isArray(f.campus_ids)) {
        f.campus_ids = f.campus_ids.map(function (n) { return parseInt(n, 10); })
          .filter(function (n) { return !isNaN(n); });
      }
      // Empty arrays allowed; if target_all is true, backend will ignore filters
      return f;
    }

    function open(row) {
      if (!row || !row.link) return;
      try {
        var l = (row.link + '').trim();
        if (!l) return;
        // For internal SPA paths beginning with '/': use hash routing
        if (/^\/[^/]/.test(l)) {
          window.location.hash = '#' + l;
          return;
        }
        // For hash paths beginning with '#': just navigate
        if (/^#/.test(l)) {
          window.location.href = l;
          return;
        }
        // Otherwise treat as external URL
        window.open(l, '_blank');
      } catch (e) {}
    }
  }

})();
