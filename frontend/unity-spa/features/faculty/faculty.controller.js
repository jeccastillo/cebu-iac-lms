(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultyController', FacultyController)
    .controller('FacultyEditController', FacultyEditController);

  FacultyController.$inject = ['$location', '$window', 'StorageService', 'FacultyService', 'ToastService'];
  function FacultyController($location, $window, StorageService, FacultyService, ToastService) {
    var vm = this;

    vm.title = 'Faculty';
    vm.state = StorageService.getJSON('loginState');

    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.filters = {
      q: '',
      teaching: null,
      isActive: null
    };
    vm.page = 1;
    vm.per_page = 20;
    vm.meta = { current_page: 1, per_page: 20, total: 0, last_page: 1 };
    vm.total = 0;

    vm.rows = [];
    vm.loading = false;
    vm.error = null;

    // Client-side column filters (applied on current page results)
    vm.cf = {
      id: '',
      username: '',
      last_name: '',
      first_name: '',
      middle_name: '',
      email: ''
    };

    vm.filteredRows = function () {
      var rows = Array.isArray(vm.rows) ? vm.rows : [];
      var cf = vm.cf || {};
      function inc(hay, needle) {
        if (!needle) return true;
        hay = (hay === 0 ? '0' : (hay || '')).toString().toLowerCase();
        needle = (needle || '').toString().toLowerCase();
        return hay.indexOf(needle) !== -1;
      }
      return rows.filter(function (r) {
        var idVal = (r.intID != null ? r.intID : r.id);
        return inc(idVal, cf.id) &&
               inc(r.strUsername, cf.username) &&
               inc(r.strLastname, cf.last_name) &&
               inc(r.strFirstname, cf.first_name) &&
               inc(r.strMiddlename, cf.middle_name) &&
               inc(r.strEmail, cf.email);
      });
    };

    vm.search = function () {
      vm.loading = true;
      vm.error = null;
      var opts = {};
      if (vm.filters.q && ('' + vm.filters.q).trim() !== '') {
        opts.q = vm.filters.q.trim();
      }
      if (vm.filters.teaching === 0 || vm.filters.teaching === 1 || vm.filters.teaching === '0' || vm.filters.teaching === '1') {
        opts.teaching = parseInt(vm.filters.teaching, 10);
      }
      if (vm.filters.isActive === 0 || vm.filters.isActive === 1 || vm.filters.isActive === '0' || vm.filters.isActive === '1') {
        opts.isActive = parseInt(vm.filters.isActive, 10);
      }
      opts.page = vm.page;
      opts.per_page = vm.per_page;

      FacultyService.list(opts)
        .then(function (res) {
          // Expecting { success: true, data: [...], meta: {...} }
          if (res && res.meta) {
            vm.meta = {
              current_page: parseInt(res.meta.current_page || 1, 10),
              per_page: parseInt(res.meta.per_page || vm.per_page, 10),
              total: parseInt(res.meta.total || 0, 10),
              last_page: parseInt(res.meta.last_page || 1, 10)
            };
          } else {
            vm.meta = { current_page: vm.page, per_page: vm.per_page, total: (res && res.data && res.data.length) ? res.data.length : 0, last_page: 1 };
          }
          vm.total = vm.meta.total;

          if (res && res.success !== false && angular.isArray(res.data)) {
            vm.rows = res.data.map(function (r) {
              r.fullName = [r.strFirstname || '', r.strMiddlename || '', r.strLastname || ''].join(' ').replace(/\s+/g, ' ').trim();
              return r;
            });
          } else if (angular.isArray(res)) {
            vm.rows = res;
          } else {
            vm.rows = [];
          }
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load faculty list.';
          vm.rows = [];
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.hasPrev = function () {
      return vm.meta && vm.meta.current_page > 1;
    };
    vm.hasNext = function () {
      return vm.meta && vm.meta.current_page < vm.meta.last_page;
    };
    vm.prevPage = function () {
      if (vm.hasPrev()) {
        vm.page = vm.meta.current_page - 1;
        vm.search();
      }
    };
    vm.nextPage = function () {
      if (vm.hasNext()) {
        vm.page = vm.meta.current_page + 1;
        vm.search();
      }
    };
    vm.goToPage = function (n) {
      n = parseInt(n, 10) || 1;
      if (n < 1) n = 1;
      if (vm.meta && n > vm.meta.last_page) n = vm.meta.last_page;
      vm.page = n;
      vm.search();
    };

    vm.add = function () {
      $location.path('/faculty/add');
    };

    vm.edit = function (row) {
      var id = row && (row.intID || row.id) ? (row.intID || row.id) : row;
      if (!id) return;
      $location.path('/faculty/' + id + '/edit');
    };

    vm.remove = function (row) {
      var id = row && (row.intID || row.id) ? (row.intID || row.id) : row;
      if (!id) return;
      if ($window.confirm('Delete this faculty record? This action cannot be undone.')) {
        vm.loading = true;
        vm.error = null;
        FacultyService.remove(id)
          .then(function (res) {
            if (ToastService && ToastService.success) {
              ToastService.success('Faculty deleted.');
            }
            vm.search();
          })
        .catch(function (err) {
          var msg = 'Delete failed.';
          // Prefer backend-provided message (e.g., in_use, unknown_state), fallback to generic 409 text
          if (err && err.data && err.data.message) {
            msg = err.data.message;
          } else if (err && err.status === 409) {
            msg = 'Unable to delete: record is referenced by other data.';
          }
          vm.error = msg;
          if (ToastService && ToastService.error) {
            ToastService.error(msg);
          }
        })
          .finally(function () {
            vm.loading = false;
          });
      }
    };

    // Init
    vm.search();
  }

  FacultyEditController.$inject = ['$routeParams', '$location', 'StorageService', 'FacultyService', 'ToastService', 'DepartmentDeficienciesService', 'CampusService', '$scope'];
  function FacultyEditController($routeParams, $location, StorageService, FacultyService, ToastService, DepartmentDeficienciesService, CampusService, $scope) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;
    vm.isEdit = !!vm.id;
    vm.title = vm.isEdit ? 'Edit Faculty' : 'Add Faculty';

    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.model = {
      strUsername: '',
      strPass: '',
      strFirstname: '',
      strMiddlename: '',
      strLastname: '',
      strEmail: '',
      strMobileNumber: '',
      strAddress: '',
      strDepartment: '',
      strSchool: '',
      intUserLevel: 0,
      teaching: 1,
      isActive: 1,
      strFacultyNumber: '',
      campus_id: null
    };

    // Department tagging state
    vm.dept = {
      list: [],
      meta: { departments: [] },
      form: { department_code: '' },
      selectedCampus: null,
      loading: false,
      error: null
    };

    // Bind to globally selected campus (Header campus selector)
    vm.initCampusBinding = function () {
      function setFromSelectedCampus() {
        try {
          var campus = (CampusService && CampusService.getSelectedCampus) ? CampusService.getSelectedCampus() : null;
          vm.dept.selectedCampus = campus || null;
        } catch (e) {}
      }
      var p = (CampusService && CampusService.init) ? CampusService.init() : null;
      if (p && p.then) { p.then(setFromSelectedCampus); } else { setFromSelectedCampus(); }

      // Listen to global campus changes
      if ($scope && $scope.$on) {
        $scope.$on('campusChanged', function (event, data) {
          var campus = data && data.selectedCampus ? data.selectedCampus : null;
          vm.dept.selectedCampus = campus || null;
        });
      }
    };
    try { vm.initCampusBinding(); } catch (e) {}

    vm.loadDepartmentsMeta = function () {
      vm.dept.error = null;
      DepartmentDeficienciesService.meta()
        .then(function (res) {
          if (res && res.success !== false && res.data && Array.isArray(res.data.departments)) {
            vm.dept.meta.departments = res.data.departments;
            if (!vm.dept.form.department_code && vm.dept.meta.departments.length) {
              vm.dept.form.department_code = vm.dept.meta.departments[0];
            }
          } else if (res && Array.isArray(res.departments)) {
            vm.dept.meta.departments = res.departments;
          } else {
            vm.dept.meta.departments = [];
          }
        })
        .catch(function (err) {
          vm.dept.error = (err && err.message) ? err.message : 'Failed to load departments meta.';
        });
    };

    vm.loadDepartments = function () {
      if (!vm.isEdit || !vm.id) return;
      vm.dept.loading = true;
      vm.dept.error = null;
      FacultyService.listDepartments(vm.id)
        .then(function (res) {
          var rows = (res && res.success !== false && res.data) ? res.data : res;
          vm.dept.list = Array.isArray(rows) ? rows : [];
        })
        .catch(function (err) {
          vm.dept.error = (err && err.message) ? err.message : 'Failed to load department tags.';
        })
        .finally(function () { vm.dept.loading = false; });
    };

    vm.addDepartmentTag = function () {
      if (!vm.isEdit || !vm.id) return;
      var code = (vm.dept.form.department_code || '').trim();

      // Campus should be the globally selected campus
      var campusId = null;
      try {
        if (vm.dept.selectedCampus && vm.dept.selectedCampus.id !== undefined && vm.dept.selectedCampus.id !== null) {
          campusId = parseInt(vm.dept.selectedCampus.id, 10);
          if (isNaN(campusId)) campusId = null;
        }
      } catch (e) { campusId = null; }

      if (!code) {
        vm.dept.error = 'Please select a department.';
        return;
      }
      if (campusId === null) {
        vm.dept.error = 'Please select a campus from the global campus selector.';
        return;
      }

      vm.dept.loading = true;
      vm.dept.error = null;
      FacultyService.addDepartment(vm.id, { department_code: code, campus_id: campusId })
        .then(function () {
          if (ToastService && ToastService.success) {
            ToastService.success('Department tag saved.');
          }
          vm.loadDepartments();
        })
        .catch(function (err) {
          var msg = (err && err.message) ? err.message : 'Failed to save department tag.';
          vm.dept.error = msg;
          if (ToastService && ToastService.error) {
            ToastService.error(msg);
          }
        })
        .finally(function () { vm.dept.loading = false; });
    };

    vm.removeDepartmentTag = function (tag) {
      if (!vm.isEdit || !vm.id || !tag) return;
      var code = tag.department_code || tag.code || tag.department || '';
      var campusId = (typeof tag.campus_id !== 'undefined') ? tag.campus_id : null;
      vm.dept.loading = true;
      vm.dept.error = null;
      FacultyService.removeDepartment(vm.id, code, campusId)
        .then(function () {
          if (ToastService && ToastService.success) {
            ToastService.success('Department tag removed.');
          }
          vm.loadDepartments();
        })
        .catch(function (err) {
          var msg = (err && err.message) ? err.message : 'Failed to remove department tag.';
          vm.dept.error = msg;
          if (ToastService && ToastService.error) {
            ToastService.error(msg);
          }
        })
        .finally(function () { vm.dept.loading = false; });
    };

    vm.load = function () {
      if (!vm.isEdit) return;
      vm.loading = true;
      vm.error = null;
      FacultyService.get(vm.id)
        .then(function (res) {
          var row = (res && res.success !== false && res.data) ? res.data : res;
          if (!row || (!row.intID && !row.id)) {
            vm.error = 'Faculty not found.';
            return;
          }
          vm.model.strUsername      = row.strUsername || '';
          vm.model.strFirstname     = row.strFirstname || '';
          vm.model.strMiddlename    = row.strMiddlename || '';
          vm.model.strLastname      = row.strLastname || '';
          vm.model.strEmail         = row.strEmail || '';
          vm.model.strMobileNumber  = row.strMobileNumber || '';
          vm.model.strAddress       = row.strAddress || '';
          vm.model.strDepartment    = row.strDepartment || '';
          vm.model.strSchool        = row.strSchool || '';
          vm.model.intUserLevel     = (typeof row.intUserLevel !== 'undefined' && row.intUserLevel !== null) ? parseInt(row.intUserLevel, 10) : 0;
          vm.model.teaching         = (typeof row.teaching !== 'undefined' && row.teaching !== null) ? parseInt(row.teaching, 10) : 1;
          vm.model.isActive         = (typeof row.isActive !== 'undefined' && row.isActive !== null) ? parseInt(row.isActive, 10) : 1;
          vm.model.strFacultyNumber = row.strFacultyNumber || '';
          vm.model.campus_id        = (typeof row.campus_id !== 'undefined' && row.campus_id !== null) ? parseInt(row.campus_id, 10) : null;
          vm.model.strPass          = ''; // never prefill password

          // Load department tagging meta and current tags
          try { vm.loadDepartmentsMeta(); } catch (e) {}
          try { vm.loadDepartments(); } catch (e) {}
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load faculty.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.save = function () {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        strUsername: (vm.model.strUsername || '').trim(),
        strFirstname: (vm.model.strFirstname || '').trim(),
        strMiddlename: (vm.model.strMiddlename || '').trim(),
        strLastname: (vm.model.strLastname || '').trim(),
        strEmail: (vm.model.strEmail || '').trim(),
        strMobileNumber: (vm.model.strMobileNumber || '').trim(),
        strAddress: (vm.model.strAddress || '').trim(),
        strDepartment: (vm.model.strDepartment || '').trim(),
        strSchool: (vm.model.strSchool || '').trim(),
        intUserLevel: (vm.model.intUserLevel !== null && vm.model.intUserLevel !== '') ? parseInt(vm.model.intUserLevel, 10) : 0,
        teaching: vm.model.teaching ? 1 : 0,
        isActive: vm.model.isActive ? 1 : 0,
        strFacultyNumber: (vm.model.strFacultyNumber || '').trim() || null,
        campus_id: (vm.model.campus_id !== null && vm.model.campus_id !== '') ? parseInt(vm.model.campus_id, 10) : null
      };

      // Password: required on create; optional on update
      if (vm.isEdit) {
        if (vm.model.strPass && vm.model.strPass.trim() !== '') {
          payload.strPass = vm.model.strPass;
        }
      } else {
        payload.strPass = (vm.model.strPass || '').trim();
      }

      var p = vm.isEdit
        ? FacultyService.update(vm.id, payload)
        : FacultyService.create(payload);

      p.then(function (res) {
          if (res && res.success !== false) {
            vm.success = 'Saved.';
            if (ToastService && ToastService.success) {
              ToastService.success('Saved.');
            }
            setTimeout(function () {
              try { vm.success = null; } catch (e) {}
              window.location.hash = '#/faculty';
            }, 300);
          } else {
            vm.error = 'Save failed.';
            if (ToastService && ToastService.error) {
              ToastService.error('Save failed.');
            }
          }
        })
        .catch(function (err) {
          if (err && err.data && err.data.errors) {
            var firstKey = Object.keys(err.data.errors)[0];
            vm.error = (err.data.errors[firstKey] && err.data.errors[firstKey][0]) || 'Validation failed.';
          } else if (err && err.data && err.data.message) {
            vm.error = err.data.message;
          } else {
            vm.error = 'Save failed.';
          }
          if (ToastService && ToastService.error) {
            ToastService.error(vm.error);
          }
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.cancel = function () {
      $location.path('/faculty');
    };

    vm.load();
  }

})();
