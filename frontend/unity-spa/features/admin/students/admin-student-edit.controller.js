(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminStudentEditController', AdminStudentEditController);

  AdminStudentEditController.$inject = ['$routeParams', '$http', '$location', '$scope', 'APP_CONFIG', 'StorageService'];
  function AdminStudentEditController($routeParams, $http, $location, $scope, APP_CONFIG, StorageService) {
    var vm = this;

    // State
    vm.state = StorageService.getJSON('loginState');
    vm.id = $routeParams.id;
    vm.loading = { init: false, save: false };
    vm.error = { init: null, save: null };
    vm.success = { save: null };
    vm.model = {};              // editable model (key-value; keys match tb_mas_users columns)
    vm.original = {};           // snapshot for diff and reset
    vm.advancedJson = '';       // free-form JSON editor for rare columns
    vm.programs = [];
    vm.campuses = [];
    vm.dynamicKeys = [];        // keys not covered by common fields

    // Admin guard (route already requires admin; this is double-check)
    vm.hasAdmin = function () {
      try {
        var s = vm.state || {};
        if (!s || !Array.isArray(s.roles)) return false;
        var roles = s.roles.map(function (r) { return ('' + r).toLowerCase().trim(); });
        return roles.indexOf('admin') !== -1;
      } catch (e) {
        return false;
      }
    };
    if (!vm.state || !vm.state.loggedIn || !vm.hasAdmin()) {
      $location.path('/login');
      return;
    }

    // Headers for admin endpoints to propagate user context for SystemLogService
    vm._adminHeaders = function (extra) {
      var headers = {};
      try {
        if (extra && typeof extra === 'object') {
          for (var k in extra) { if (Object.prototype.hasOwnProperty.call(extra, k)) headers[k] = extra[k]; }
        }
        var s = vm.state || {};
        if (s && s.faculty_id != null) headers['X-Faculty-ID'] = s.faculty_id;
        if (s && Array.isArray(s.roles)) headers['X-User-Roles'] = s.roles.join(',');
        if (s && s.campus_id != null) headers['X-Campus-ID'] = s.campus_id;
      } catch (e) {}
      return { headers: headers };
    };

    // API endpoints
    vm.api = {
      raw: APP_CONFIG.API_BASE + '/students/' + vm.id + '/raw',
      update: APP_CONFIG.API_BASE + '/students/' + vm.id,
      programs: APP_CONFIG.API_BASE + '/programs',
      campuses: APP_CONFIG.API_BASE + '/campuses',
      viewer: '/#/students/' + vm.id
    };

    // Common keys we render specific inputs for; others go to dynamic section
    var COMMON_KEYS = [
      'strStudentNumber', 'strFirstname', 'strMiddlename', 'strLastname',
      'strEmail', 'strGSuiteEmail', 'strMobileNumber',
      'enumGender', 'student_status', 'student_type', 'level',
      'intProgramID', 'campus_id', 'dteBirthDate'
    ];
    var IMMUTABLE_KEYS = ['intID']; // do not allow editing PK

    vm.isCommonKey = function (k) {
      return COMMON_KEYS.indexOf(k) !== -1;
    };
    vm.isMutableKey = function (k) {
      return IMMUTABLE_KEYS.indexOf(k) === -1;
    };

    // Build a human-friendly program label with fallbacks
    vm.programLabel = function (p) {
      if (!p) return '';
      var id = (p.intProgramID != null) ? p.intProgramID : (p.id != null ? p.id : '');
      var code = p.strProgramCode || p.code || '';
      var desc = p.strProgramDescription || p.title || p.description || '';
      if (code && desc) return code + ' — ' + desc;
      if (desc) return desc;
      if (code) return code;
      return 'Program ' + id;
    };

    vm.computeDynamicKeys = function () {
      var keys = [];
      try {
        Object.keys(vm.model || {}).forEach(function (k) {
          if (!vm.isCommonKey(k) && vm.isMutableKey(k)) {
            keys.push(k);
          }
        });
        keys.sort();
      } catch (e) {}
      vm.dynamicKeys = keys;
    };

    vm.loadPrograms = function () {
      // Include disabled programs as well so the student's current program can still be displayed/selected
      return $http.get(vm.api.programs + '?enabledOnly=false')
        .then(function (resp) {
          var data = resp && resp.data ? (resp.data.data || resp.data) : [];
          var rows = Array.isArray(data) ? data : [];
          // Normalize for consistent ng-options binding/labeling
          vm.programs = rows.map(function (p) {
            var id = (p.intProgramID != null) ? p.intProgramID : (p.id != null ? p.id : null);
            var code = p.strProgramCode || p.code || '';
            var desc = p.strProgramDescription || p.title || p.description || '';
            return Object.assign({}, p, {
              intProgramID: id,
              strProgramCode: code,
              strProgramDescription: desc
            });
          }).filter(function (p) { return p.intProgramID != null; });
        })
        .catch(function () { vm.programs = []; });
    };

    vm.loadCampuses = function () {
      return $http.get(vm.api.campuses)
        .then(function (resp) {
          var data = resp && resp.data ? (resp.data.data || resp.data) : [];
          vm.campuses = Array.isArray(data) ? data : [];
        })
        .catch(function () { vm.campuses = []; });
    };

    // Ensure the current student's program exists in vm.programs; if not, fetch and append.
    function ensureSelectedProgramPresent() {
      try {
        var sid = vm.model && vm.model.intProgramID;
        if (sid == null || sid === '') return;
        var found = (vm.programs || []).some(function (p) {
          return (p && p.intProgramID != null && parseInt(p.intProgramID, 10) === parseInt(sid, 10));
        });
        if (!found) {
          // Fetch single program and append
          return $http.get(APP_CONFIG.API_BASE + '/programs/' + parseInt(sid, 10))
            .then(function (r) {
              var prog = (r && r.data && r.data.data) ? r.data.data : null;
              if (!prog) return;
              var id = (prog.intProgramID != null) ? prog.intProgramID : (prog.id != null ? prog.id : null);
              var code = prog.strProgramCode || prog.code || '';
              var desc = prog.strProgramDescription || prog.title || prog.description || '';
              if (id != null) {
                vm.programs = (vm.programs || []).concat([{
                  intProgramID: parseInt(id, 10),
                  strProgramCode: code,
                  strProgramDescription: desc
                }]);
              }
            })
            .catch(function () { /* ignore */ });
        }
      } catch (e) { /* ignore */ }
    }

    vm.fetchRaw = function () {
      vm.error.init = null;
      var cfg = vm._adminHeaders();
      return $http.get(vm.api.raw, cfg)
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            var raw = resp.data.data || resp.data;
            vm.model = angular.copy(raw) || {};
            vm.original = angular.copy(vm.model);

            // Coerce FK fields to STRINGS for stable ng-options matching
            try {
              if (vm.model.intProgramID !== undefined && vm.model.intProgramID !== null && vm.model.intProgramID !== '') {
                var p = parseInt(vm.model.intProgramID, 10);
                vm.model.intProgramID = isNaN(p) ? null : String(p);
              } else {
                vm.model.intProgramID = null;
              }
              if (vm.model.campus_id !== undefined && vm.model.campus_id !== null && vm.model.campus_id !== '') {
                var c = parseInt(vm.model.campus_id, 10);
                vm.model.campus_id = isNaN(c) ? null : String(c);
              } else {
                vm.model.campus_id = null;
              }
            } catch (e) {}

            // Make sure selected program is present in the options
            Promise.resolve().then(ensureSelectedProgramPresent);

            // Pre-fill advanced JSON with a pretty-printed snapshot for editing
            try { vm.advancedJson = JSON.stringify(vm.model, null, 2); } catch (e) { vm.advancedJson = ''; }
            vm.computeDynamicKeys();
          } else {
            vm.error.init = 'Failed to load student.';
          }
        })
        .catch(function () {
          vm.error.init = 'Failed to load student.';
        });
    };

    vm.reset = function () {
      vm.model = angular.copy(vm.original);
      try { vm.advancedJson = JSON.stringify(vm.model, null, 2); } catch (e) { vm.advancedJson = ''; }
      vm.success.save = null;
      vm.error.save = null;
      vm.computeDynamicKeys();
    };

    vm.mergeAdvancedJson = function (base) {
      // Parse advanced JSON; merge into base (overwriting)
      try {
        if (vm.advancedJson && ('' + vm.advancedJson).trim() !== '') {
          var parsed = JSON.parse(vm.advancedJson);
          if (parsed && typeof parsed === 'object') {
            Object.keys(parsed).forEach(function (k) {
              // Respect immutables
              if (vm.isMutableKey(k)) {
                base[k] = parsed[k];
              }
            });
          }
        }
      } catch (e) {
        // Keep silent; surface validation error only on save
      }
      return base;
    };

    vm.save = function () {
      vm.loading.save = true;
      vm.error.save = null;
      vm.success.save = null;

      // Build payload from model and merged advanced JSON
      var payload = angular.copy(vm.model) || {};
      // Ensure primary key not submitted
      if (payload.hasOwnProperty('intID')) {
        delete payload.intID;
      }

      // Merge advanced JSON; if parse fails, abort with error
      try {
        if (vm.advancedJson && ('' + vm.advancedJson).trim() !== '') {
          var parsed = JSON.parse(vm.advancedJson);
          if (parsed && typeof parsed === 'object') {
            Object.keys(parsed).forEach(function (k) {
              if (vm.isMutableKey(k)) payload[k] = parsed[k];
            });
          }
        }
      } catch (e) {
        vm.loading.save = false;
        vm.error.save = 'Advanced JSON is invalid: ' + e.message;
        return;
      }

      // Normalize empty strings to null (consistent with backend logic)
      Object.keys(payload).forEach(function (k) {
        if (payload[k] === '') payload[k] = null;
      });
      // Coerce FK fields to integers for API payload
      try {
        if (payload.intProgramID !== null && payload.intProgramID !== undefined) {
          var _p = parseInt(payload.intProgramID, 10);
          payload.intProgramID = isNaN(_p) ? null : _p;
        }
        if (payload.campus_id !== null && payload.campus_id !== undefined) {
          var _c = parseInt(payload.campus_id, 10);
          payload.campus_id = isNaN(_c) ? null : _c;
        }
      } catch (e) {}

      var cfg = vm._adminHeaders({ 'Content-Type': 'application/json' });
      return $http.put(vm.api.update, payload, cfg)
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            var updated = resp.data.data || resp.data;
            vm.original = angular.copy(updated);
            vm.model = angular.copy(updated);

            // Re-coerce after save so select boxes show labels
            try {
              if (vm.model.intProgramID !== undefined && vm.model.intProgramID !== null && vm.model.intProgramID !== '') {
                var sp = parseInt(vm.model.intProgramID, 10);
                vm.model.intProgramID = isNaN(sp) ? null : String(sp);
              } else {
                vm.model.intProgramID = null;
              }
              if (vm.model.campus_id !== undefined && vm.model.campus_id !== null && vm.model.campus_id !== '') {
                var sc = parseInt(vm.model.campus_id, 10);
                vm.model.campus_id = isNaN(sc) ? null : String(sc);
              } else {
                vm.model.campus_id = null;
              }
            } catch (e) {}

            try { vm.advancedJson = JSON.stringify(vm.model, null, 2); } catch (e) {}
            vm.computeDynamicKeys();
            vm.success.save = 'Saved successfully.';
          } else {
            vm.error.save = (resp && resp.data && resp.data.message) ? resp.data.message : 'Save failed.';
          }
        })
        .catch(function (err) {
          var msg = 'Save failed.';
          try {
            if (err && err.data && err.data.message) msg = err.data.message;
          } catch (e) {}
          vm.error.save = msg;
        })
        .finally(function () {
          vm.loading.save = false;
        });
    };

    vm.backToViewer = function () {
      $location.url(vm.api.viewer);
    };

    // Listen for external changes if necessary (not strictly needed)
    $scope.$on('studentUpdatedExternally', function () {
      vm.fetchRaw();
    });

    // init
    vm.init = function () {
      vm.loading.init = true;
      vm.error.init = null;
      Promise.resolve()
        .then(function () { return vm.loadPrograms(); })
        .then(function () { return vm.loadCampuses(); })
        .then(function () { return vm.fetchRaw(); })
        .finally(function () { vm.loading.init = false; $scope.$applyAsync(); });
    };

    vm.init();
  }
})();
