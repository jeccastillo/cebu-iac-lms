(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('StudentsController', StudentsController);

  StudentsController.$inject = ['$location', '$http', 'APP_CONFIG', 'LinkService', 'StorageService', 'CampusService', 'StudentsService', '$scope', '$timeout', '$window'];
  function StudentsController($location, $http, APP_CONFIG, LinkService, StorageService, CampusService, StudentsService, $scope, $timeout, $window) {
    var vm = this;

    vm.title = 'Students';
    vm.state = StorageService.getJSON('loginState');

    // extra guard (in addition to run.js)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Links for legacy CI pages (used by actions)
    vm.links = LinkService.buildLinks();
    vm.nav = LinkService.buildSpaLinks();

    // Filters (mirrors CI UI where possible)
    vm.filters = {
      program: 0,
      academicStatus: 0, // 1=regular,2=irregular,3=new (TBD in API)
      year_level: 0,
      gender: 0,         // 1=male, 2=female
      graduated: 0,      // 1=yes, 2=no
      registered: 0,     // 1=enlisted, 2=enrolled (requires sem)
      inactive: 'active',// active|inactive|loa|awol
      level: 0,          // student type (TBD)
      sem: 0             // syid required for registered filter
    };

    // Pagination state
    vm.page = 1;
    vm.perPage = 20;
    vm.total = 0;
    vm.rows = [];
    // Client-side column filters (per-column search)
    vm.cf = {
      student_number: '',
      last_name: '',
      first_name: '',
      middle_name: '',
      program: '',
      year_level: '',
      status: '',
      type: '',
      student_level: ''
    };

    // Debounced trigger for server-side column search
    vm._cfTimer = null;
    vm.onColumnFilterChange = function () {
      if (vm._cfTimer) { $timeout.cancel(vm._cfTimer); }
      vm._cfTimer = $timeout(function () {
        vm.page = 1;
        vm.search();
      }, 300);
    };

    // Return rows filtered by per-column inputs (case-insensitive)
    vm.filteredRows = function () {
      var rows = vm.rows || [];
      var cf = vm.cf || {};
      function has(v, q) {
        if (!q) return true;
        var s = (v === null || v === undefined) ? '' : ('' + v);
        return s.toLowerCase().indexOf(('' + q).toLowerCase()) !== -1;
      }
      return rows.filter(function (r) {
        return has(r.student_number, cf.student_number)
          && has(r.last_name, cf.last_name)
          && has(r.first_name, cf.first_name)
          && has(r.middle_name, cf.middle_name)
          && has(r.program, cf.program)
          && has(r.year_level, cf.year_level)
          && has(r.status, cf.status)
          && has(r.type, cf.type)
          && has(r.student_level, cf.student_level);
      });
    };

    vm.loading = false;
    vm.error = null;

    // Import state
    vm.importing = false;
    vm.importError = null;
    vm.importSummary = null;
    vm._selectedFile = null;

    vm.downloadTemplate = function () {
      vm.importError = null;
      try {
        StudentsService.downloadTemplate().then(function (res) {
          var data = res && res.data ? res.data : null;
          var filename = (res && res.filename) ? res.filename : 'students-import-template.xlsx';
          if (!data) {
            vm.importError = 'Failed to download template.';
            return;
          }
          var blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
          var url = ($window.URL || $window.webkitURL).createObjectURL(blob);
          var a = document.createElement('a');
          a.href = url;
          a.download = filename;
          document.body.appendChild(a);
          a.click();
          setTimeout(function () {
            ($window.URL || $window.webkitURL).revokeObjectURL(url);
            try { document.body.removeChild(a); } catch (e) {}
          }, 0);
        }).catch(function () {
          vm.importError = 'Failed to download template.';
        });
      } catch (e) {
        vm.importError = 'Failed to download template.';
      }
    };

    vm.openImportDialog = function () {
      vm.importError = null;
      vm.importSummary = null;
      var el = document.getElementById('studentsImportFile');
      if (el) {
        try {
          // reset previous selection to ensure change event fires even for same file
          el.value = '';
          el.onchange = function (evt) {
            var files = (evt && evt.target) ? evt.target.files : null;
            try {
              $scope.$applyAsync(function () {
                vm.onFileSelected(files);
              });
            } catch (e) {
              // fallback without digest
              vm.onFileSelected(files);
            }
          };
        } catch (e) {}
        el.click();
      }
    };

    vm.onFileSelected = function (files) {
      vm.importError = null;
      vm.importSummary = null;
      try {
        if (files && files.length > 0) {
          vm._selectedFile = files[0];
        } else {
          vm._selectedFile = null;
        }
      } catch (e) {
        vm._selectedFile = null;
      }
      // Auto-run import upon selection
      if (vm._selectedFile) {
        vm.runImport();
      }
    };

    vm.runImport = function () {
      if (!vm._selectedFile) {
        vm.importError = 'No file selected';
        return;
      }
      vm.importing = true;
      vm.importError = null;
      vm.importSummary = null;
      StudentsService.importFile(vm._selectedFile, { dry_run: false })
        .then(function (res) {
          var ok = res && (res.success !== false);
          var result = res && res.result ? res.result : null;
          if (!ok || !result) {
            vm.importError = (res && res.message) ? res.message : 'Import failed.';
            return;
          }
          vm.importSummary = {
            totalRows: result.totalRows || 0,
            inserted: result.inserted || 0,
            updated: result.updated || 0,
            skipped: result.skipped || 0,
            errors: Array.isArray(result.errors) ? result.errors : []
          };
          // Refresh list after successful import
          vm.search();
        })
        .catch(function (e) {
          vm.importError = (e && e.data && e.data.message) ? e.data.message : 'Import failed.';
        })
        .finally(function () {
          vm.importing = false;
          // clear file input
          try {
            var el = document.getElementById('studentsImportFile');
            if (el) el.value = '';
          } catch (e) {}
          vm._selectedFile = null;
        });
    };

    vm.programs = [];
    vm.loadPrograms = function () {
      return $http.get(APP_CONFIG.API_BASE + '/programs')
        .then(function (resp) {
          if (resp && resp.data && angular.isArray(resp.data.data)) {
            vm.programs = resp.data.data;
          } else if (resp && angular.isArray(resp.data)) {
            // fallback if controller returns plain array
            vm.programs = resp.data;
          }
        });
    };

    // Terms (tb_mas_sy) for Enrollment filter dropdown
    vm.terms = [];
    vm.loadTerms = function () {
      return $http.get(APP_CONFIG.API_BASE + '/generic/terms')
        .then(function (resp) {
          if (resp && resp.data && angular.isArray(resp.data.data)) {
            vm.terms = resp.data.data;
          } else if (resp && angular.isArray(resp.data)) {
            vm.terms = resp.data;
          }
        });
    };

    vm.buildParams = function () {
      var p = {
        page: vm.page,
        per_page: vm.perPage
      };
      if (vm.filters.program && +vm.filters.program > 0) p.program = +vm.filters.program;
      if (vm.filters.year_level && +vm.filters.year_level > 0) p.year_level = +vm.filters.year_level;
      if (vm.filters.gender && +vm.filters.gender > 0) p.gender = +vm.filters.gender;
      if (vm.filters.graduated && +vm.filters.graduated > 0) p.graduated = +vm.filters.graduated;
      if (vm.filters.inactive) p.inactive = vm.filters.inactive;
      if (vm.filters.registered && +vm.filters.registered > 0) p.registered = +vm.filters.registered;
      if (vm.filters.sem && ('' + vm.filters.sem).trim() !== '') p.sem = vm.filters.sem;

      // Scope by selected campus if available
      try {
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
          p.campus_id = +campus.id;
        }
      } catch (e) {
        // no-op if CampusService not available
      }

      // Column search parameters (server-side filtering)
      try {
        if (vm.cf) {
          if (vm.cf.student_number) p.student_number = vm.cf.student_number;
          if (vm.cf.last_name) p.last_name = vm.cf.last_name;
          if (vm.cf.first_name) p.first_name = vm.cf.first_name;
          if (vm.cf.middle_name) p.middle_name = vm.cf.middle_name;
          // Program code: code only (e.g., "BSIT")
          if (vm.cf.program) p.program_code = vm.cf.program;
          // Year level: exact numeric match only; ignore non-numeric input
          if (vm.cf.year_level) {
            var yl = parseInt(vm.cf.year_level, 10);
            if (!isNaN(yl) && yl > 0) {
              p.year_level = yl;
            }
          }
          if (vm.cf.status) p.status_text = vm.cf.status;
          if (vm.cf.student_level) p.student_level_text = vm.cf.student_level;
          if (vm.cf.type) p.type_text = vm.cf.type;
        }
      } catch (e) {}

      // academicStatus and level are placeholders for parity; not sent yet
      return p;
    };

    vm.search = function () {
      vm.loading = true;
      vm.error = null;
      var params = vm.buildParams();

      return $http.get(APP_CONFIG.API_BASE + '/students', { params: params })
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            vm.rows = resp.data.data || [];
            var meta = resp.data.meta || {};
            vm.total = meta.total || vm.rows.length || 0;
            vm.page = meta.page || vm.page;
            vm.perPage = meta.per_page || vm.perPage;
          } else {
            vm.rows = [];
            vm.total = 0;
            vm.error = 'Failed to load students.';
          }
        })
        .catch(function () {
          vm.rows = [];
          vm.total = 0;
          vm.error = 'Failed to load students.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.nextPage = function () {
      var maxPage = Math.max(1, Math.ceil(vm.total / vm.perPage));
      if (vm.page < maxPage) {
        vm.page += 1;
        vm.search();
      }
    };

    vm.prevPage = function () {
      if (vm.page > 1) {
        vm.page -= 1;
        vm.search();
      }
    };

    vm.editUrl = function (id) {
      return vm.links.unity.replace('/unity', '') + '/student/edit_student/' + id;
    };

    vm.financesUrl = function (id) {
      return '#/finance/cashier/' + id;
    };

    vm.applicantTxUrl = function (studentNumber) {
      return vm.links.unity.replace('/unity', '') + '/finance/manualPay/' + encodeURIComponent(studentNumber || '');
    };

    vm.viewerUrl = function (row) {
      return '#/students/' + row.id;
    };

    // Row actions dropdown state
    vm.menuOpenId = null;
    vm.toggleMenu = function (id) {
      vm.menuOpenId = (vm.menuOpenId === id ? null : id);
    };
    vm.isMenuOpen = function (id) {
      return vm.menuOpenId === id;
    };
    vm.closeMenu = function () {
      vm.menuOpenId = null;
    };

    // React to campus changes: reset to first page and re-run search
    $scope.$on('campusChanged', function () {
      vm.page = 1;
      vm.search();
    });

    // Init: ensure CampusService is initialized so selected campus is available before first search
    vm.loadPrograms()
      .then(vm.loadTerms)
      .then(function () {
        if (CampusService && CampusService.init) {
          return CampusService.init();
        }
      })
      .finally(function () {
        vm.search();
      });
  }

})();
