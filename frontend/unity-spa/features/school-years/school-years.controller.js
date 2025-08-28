(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('SchoolYearsListController', SchoolYearsListController)
    .controller('SchoolYearEditController', SchoolYearEditController);

  SchoolYearsListController.$inject = ['$location', '$window', 'StorageService', 'SchoolYearsService', 'CampusService'];
  function SchoolYearsListController($location, $window, StorageService, SchoolYearsService, CampusService) {
    var vm = this;

    vm.title = 'School Years';
    vm.state = StorageService.getJSON('loginState');

    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.filters = {
      search: '',
      term_student_type: '',
      campus_id: null
    };

    vm.types = [
      { value: '', label: 'All student types' },
      { value: 'college', label: 'College' },
      { value: 'shs', label: 'SHS' },
      { value: 'next', label: 'Next' },
      { value: 'others', label: 'Others' }
    ];

    vm.rows = [];
    vm.loading = false;
    vm.error = null;

    vm.initCampus = function () {
      try {
        var p = CampusService && CampusService.init ? CampusService.init() : null;
        function setCampus() {
          var c = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
          vm.filters.campus_id = (c && c.id !== undefined && c.id !== null) ? parseInt(c.id, 10) : null;
        }
        if (p && p.then) p.then(setCampus); else setCampus();

        // react to campus changes
        if (p && p.then) {
          // listen to broadcast
        }
      } catch (e) {}
    };

    vm.search = function () {
      vm.loading = true;
      vm.error = null;
      var opts = {};
      if (vm.filters.campus_id !== null && vm.filters.campus_id !== undefined && vm.filters.campus_id !== '') {
        opts.campus_id = vm.filters.campus_id;
      }
      if (vm.filters.term_student_type) opts.term_student_type = vm.filters.term_student_type;
      if (vm.filters.search && ('' + vm.filters.search).trim() !== '') {
        opts.search = vm.filters.search.trim();
      }

      SchoolYearsService.list(opts)
        .then(function (data) {
          // Expecting { success: true, data: [...] } from API
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.rows = data.data;
          } else if (angular.isArray(data)) {
            vm.rows = data;
          } else if (data && angular.isArray(data.rows)) {
            vm.rows = data.rows;
          } else {
            vm.rows = [];
          }
        })
        .catch(function () {
          vm.error = 'Failed to load school years.';
          vm.rows = [];
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.add = function () {
      $location.path('/school-years/new');
    };

    vm.edit = function (row) {
      var id = row && (row.intID || row.id) ? (row.intID || row.id) : row;
      if (!id) return;
      $location.path('/school-years/' + id + '/edit');
    };

    vm.remove = function (row) {
      var id = row && (row.intID || row.id) ? (row.intID || row.id) : row;
      if (!id) return;
      if ($window.confirm('Delete/Disable this School Year? This may set enumStatus=inactive if supported.')) {
        vm.loading = true;
        vm.error = null;
        SchoolYearsService.remove(id)
          .then(function () {
            vm.search();
          })
          .catch(function (err) {
            var msg = 'Delete failed.';
            if (err && err.data && err.data.message) msg = err.data.message;
            vm.error = msg;
          })
          .finally(function () {
            vm.loading = false;
          });
      }
    };

    // Init
    vm.initCampus();
    vm.search();
  }

  SchoolYearEditController.$inject = ['$routeParams', '$location', '$window', 'StorageService', 'SchoolYearsService', 'CampusService', '$scope'];
  function SchoolYearEditController($routeParams, $location, $window, StorageService, SchoolYearsService, CampusService, $scope) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;
    vm.isEdit = !!vm.id;
    vm.title = vm.isEdit ? 'Edit School Year' : 'Add School Year';

    // Tab state for edit form
    vm.activeTab = 'core';
    vm.setTab = function (tab) { vm.activeTab = tab; };
    vm.isTab = function (tab) { return vm.activeTab === tab; };

    vm.model = {
      enumSem: '1st',
      strYearStart: '',
      strYearEnd: '',
      term_label: 'Semester',
      term_student_type: '',
      campus_id: null,

      // Grading windows (dates)
      midterm_start: null,
      midterm_end: null,
      final_start: null,
      final_end: null,
      end_of_submission: null,

      // Academic timeline (dates)
      start_of_classes: null,
      final_exam_start: null,
      final_exam_end: null,

      // Viewing windows (dates)
      viewing_midterm_start: null,
      viewing_midterm_end: null,
      viewing_final_start: null,
      viewing_final_end: null,

      // Application and reconciliation (dates)
      endOfApplicationPeriod: null,
      reconf_start: null,
      reconf_end: null,
      ar_report_date_generation: null,

      // Installment schedule (dates)
      installment1: null,
      installment2: null,
      installment3: null,
      installment4: null,
      installment5: null,

      // Operational flags / enums
      classType: '',
      pay_student_visa: '',
      is_locked: '',
      enumGradingPeriod: '',
      enumMGradingPeriod: '',
      enumFGradingPeriod: '',

      // Existing flags
      intProcessing: null,
      enumStatus: null,
      enumFinalized: null
    };

    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.studentTypes = [
      { value: '', label: 'Select type' },
      { value: 'college', label: 'College' },
      { value: 'shs', label: 'SHS' },
      { value: 'next', label: 'Next' }
    ];

    vm.semesters = [
      { value: '1st', label: '1st' },
      { value: '2nd', label: '2nd' },
      { value: '3rd', label: '3rd' },
      { value: '4th', label: '4th' },
      { value: 'Summer', label: 'Summer' }
    ];

    function _fmtDateTime(dt) {
      if (!dt) return null;
      // Accepts:
      //  - string 'YYYY-MM-DDTHH:mm' (from input[type=datetime-local])
      //  - string 'YYYY-MM-DD HH:mm[:ss]'
      //  - string 'YYYY-MM-DDTHH:mm:ss[.fraction][Z]'
      //  - Date object
      try {
        if (typeof dt === 'string') {
          var s = dt.trim();
          if (!s) return null;
          // Replace 'T' with space for backend
          if (s.indexOf('T') !== -1) {
            s = s.replace('T', ' ');
          }
          // If contains 'Z' or fractional seconds, strip them
          s = s.replace(/Z$/, '').replace(/(\.\d+)$/, '');
          // If only yyyy-mm-dd, append midnight
          if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
            s = s + ' 00:00:00';
          }
          // If missing seconds (yyyy-mm-dd hh:mm), append :00
          if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(s)) {
            s = s + ':00';
          }
          return s;
        }
        if (dt instanceof Date) {
          var y = dt.getFullYear();
          var m = ('0' + (dt.getMonth() + 1)).slice(-2);
          var d = ('0' + dt.getDate()).slice(-2);
          var hh = ('0' + dt.getHours()).slice(-2);
          var mm = ('0' + dt.getMinutes()).slice(-2);
          var ss = ('0' + dt.getSeconds()).slice(-2);
          return y + '-' + m + '-' + d + ' ' + hh + ':' + mm + ':' + ss;
        }
      } catch (e) {}
      return null;
    }

    // Format date-only to 'YYYY-MM-DD' (accepts Date or string)
    function _fmtDate(dt) {
      if (!dt) return null;
      try {
        if (dt instanceof Date) {
          var y = dt.getFullYear();
          var m = ('0' + (dt.getMonth() + 1)).slice(-2);
          var d = ('0' + dt.getDate()).slice(-2);
          return y + '-' + m + '-' + d;
        }
        var s = (dt + '').trim();
        if (!s) return null;
        // Normalize and extract YYYY-MM-DD
        s = s.replace('T', ' ').replace(/Z$/, '').replace(/(\.\d+)$/, '');
        var m1 = s.match(/^(\d{4}-\d{2}-\d{2})/);
        if (m1) return m1[1];
        return s;
      } catch (e) {
        return null;
      }
    }

    // Normalize incoming API date/time to Date object for input[type=date]
    function _toLocalInput(dt) {
      try {
        if (!dt) return null;
        if (dt instanceof Date) {
          return new Date(dt.getFullYear(), dt.getMonth(), dt.getDate());
        }
        var s = (dt + '').trim();
        if (!s) return null;
        s = s.replace('T', ' ').replace(/Z$/, '').replace(/(\.\d+)$/, '');
        var m1 = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (m1) {
          return new Date(parseInt(m1[1], 10), parseInt(m1[2], 10) - 1, parseInt(m1[3], 10));
        }
        return null;
      } catch (e) {
        return null;
      }
    }

    vm.initCampusBinding = function () {
      function setFromSelectedCampus() {
        try {
          var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
          var id = (campus && campus.id !== undefined && campus.id !== null) ? parseInt(campus.id, 10) : null;
          vm.model.campus_id = id;
        } catch (e) {}
      }
      var p = (CampusService && CampusService.init) ? CampusService.init() : null;
      if (p && p.then) { p.then(setFromSelectedCampus); } else { setFromSelectedCampus(); }

      // Update when campus changes
      var unbind = $scope.$on('campusChanged', function (event, data) {
        var campus = data && data.selectedCampus ? data.selectedCampus : null;
        var id = (campus && campus.id !== undefined && campus.id !== null) ? parseInt(campus.id, 10) : null;
        vm.model.campus_id = id;
      });
      $scope.$on('$destroy', unbind);
    };

    vm.load = function () {
      if (!vm.isEdit) return;
      vm.loading = true;
      vm.error = null;
      SchoolYearsService.get(vm.id)
        .then(function (data) {
          var row = (data && data.success !== false && data.data) ? data.data : data;
          if (!row || (!row.intID && !row.id)) {
            vm.error = 'School Year not found.';
            return;
          }
          vm.model.enumSem = row.enumSem || '1st';
          vm.model.strYearStart = row.strYearStart || '';
          vm.model.strYearEnd = row.strYearEnd || '';
          vm.model.term_label = row.term_label || 'Semester';
          vm.model.term_student_type = row.term_student_type || '';
          vm.model.campus_id = (row.campus_id !== undefined && row.campus_id !== null) ? parseInt(row.campus_id, 10) : null;
          // Grading windows
          vm.model.midterm_start = _toLocalInput(row.midterm_start);
          vm.model.midterm_end = _toLocalInput(row.midterm_end);
          vm.model.final_start = _toLocalInput(row.final_start);
          vm.model.final_end = _toLocalInput(row.final_end);
          vm.model.end_of_submission = _toLocalInput(row.end_of_submission);

          // Academic timeline
          vm.model.start_of_classes = _toLocalInput(row.start_of_classes);
          vm.model.final_exam_start = _toLocalInput(row.final_exam_start);
          vm.model.final_exam_end = _toLocalInput(row.final_exam_end);

          // Viewing windows
          vm.model.viewing_midterm_start = _toLocalInput(row.viewing_midterm_start);
          vm.model.viewing_midterm_end = _toLocalInput(row.viewing_midterm_end);
          vm.model.viewing_final_start = _toLocalInput(row.viewing_final_start);
          vm.model.viewing_final_end = _toLocalInput(row.viewing_final_end);

          // Application and reconciliation dates
          vm.model.endOfApplicationPeriod = _toLocalInput(row.endOfApplicationPeriod);
          vm.model.reconf_start = _toLocalInput(row.reconf_start);
          vm.model.reconf_end = _toLocalInput(row.reconf_end);
          vm.model.ar_report_date_generation = _toLocalInput(row.ar_report_date_generation);

          // Installments
          vm.model.installment1 = _toLocalInput(row.installment1);
          vm.model.installment2 = _toLocalInput(row.installment2);
          vm.model.installment3 = _toLocalInput(row.installment3);
          vm.model.installment4 = _toLocalInput(row.installment4);
          vm.model.installment5 = _toLocalInput(row.installment5);

          // Operational flags / enums
          vm.model.classType = row.classType || '';
          vm.model.pay_student_visa = (row.pay_student_visa !== undefined && row.pay_student_visa !== null) ? String(parseInt(row.pay_student_visa, 10)) : '';
          vm.model.is_locked = (row.is_locked !== undefined && row.is_locked !== null) ? String(parseInt(row.is_locked, 10)) : '';
          vm.model.enumGradingPeriod = row.enumGradingPeriod || '';
          vm.model.enumMGradingPeriod = row.enumMGradingPeriod || '';
          vm.model.enumFGradingPeriod = row.enumFGradingPeriod || '';

          // Existing flags
          vm.model.intProcessing = (row.intProcessing !== undefined && row.intProcessing !== null) ? parseInt(row.intProcessing, 10) : '';
          vm.model.enumStatus = row.enumStatus || '';
          vm.model.enumFinalized = row.enumFinalized || '';
        })
        .catch(function () {
          vm.error = 'Failed to load School Year.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.save = function () {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      // Basic validations
      var y1 = (vm.model.strYearStart + '').trim();
      var y2 = (vm.model.strYearEnd + '').trim();
      if (!y1 || !/^\d{4}$/.test(y1)) {
        vm.error = 'Start year is required (YYYY).';
        vm.loading = false;
        return;
      }
      if (!y2 || !/^\d{4}$/.test(y2)) {
        vm.error = 'End year is required (YYYY).';
        vm.loading = false;
        return;
      }

      var payload = {
        // Core
        enumSem: vm.model.enumSem,
        strYearStart: y1,
        strYearEnd: y2,
        term_label: (vm.model.term_label || '') || null,
        term_student_type: (vm.model.term_student_type || '') || null,
        campus_id: (vm.model.campus_id !== null && vm.model.campus_id !== '') ? parseInt(vm.model.campus_id, 10) : null,

        // Grading windows (date-only)
        midterm_start: _fmtDate(vm.model.midterm_start),
        midterm_end: _fmtDate(vm.model.midterm_end),
        final_start: _fmtDate(vm.model.final_start),
        final_end: _fmtDate(vm.model.final_end),
        end_of_submission: _fmtDate(vm.model.end_of_submission),

        // Academic timeline (date-only)
        start_of_classes: _fmtDate(vm.model.start_of_classes),
        final_exam_start: _fmtDate(vm.model.final_exam_start),
        final_exam_end: _fmtDate(vm.model.final_exam_end),

        // Viewing windows (date-only)
        viewing_midterm_start: _fmtDate(vm.model.viewing_midterm_start),
        viewing_midterm_end: _fmtDate(vm.model.viewing_midterm_end),
        viewing_final_start: _fmtDate(vm.model.viewing_final_start),
        viewing_final_end: _fmtDate(vm.model.viewing_final_end),

        // Application / reconciliation (date-only)
        endOfApplicationPeriod: _fmtDate(vm.model.endOfApplicationPeriod),
        reconf_start: _fmtDate(vm.model.reconf_start),
        reconf_end: _fmtDate(vm.model.reconf_end),
        ar_report_date_generation: _fmtDate(vm.model.ar_report_date_generation),

        // Installments (date-only)
        installment1: _fmtDate(vm.model.installment1),
        installment2: _fmtDate(vm.model.installment2),
        installment3: _fmtDate(vm.model.installment3),
        installment4: _fmtDate(vm.model.installment4),
        installment5: _fmtDate(vm.model.installment5),

        // Operational flags / enums
        classType: (vm.model.classType || '') || null,
        pay_student_visa: (vm.model.pay_student_visa !== null && vm.model.pay_student_visa !== '') ? parseInt(vm.model.pay_student_visa, 10) : null,
        is_locked: (vm.model.is_locked !== null && vm.model.is_locked !== '') ? parseInt(vm.model.is_locked, 10) : null,
        enumGradingPeriod: (vm.model.enumGradingPeriod || '') || null,
        enumMGradingPeriod: (vm.model.enumMGradingPeriod || '') || null,
        enumFGradingPeriod: (vm.model.enumFGradingPeriod || '') || null,

        // Existing flags
        intProcessing: (vm.model.intProcessing !== null && vm.model.intProcessing !== '') ? parseInt(vm.model.intProcessing, 10) : null,
        enumStatus: (vm.model.enumStatus || '') || null,
        enumFinalized: (vm.model.enumFinalized || '') || null
      };

      var p = vm.isEdit
        ? SchoolYearsService.update(vm.id, payload)
        : SchoolYearsService.create(payload);

      p.then(function (data) {
          if (data && data.success !== false) {
            vm.success = 'Saved.';
            setTimeout(function () {
              try { vm.success = null; } catch (e) {}
              window.location.hash = '#/school-years';
            }, 300);
          } else {
            vm.error = 'Save failed.';
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
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.cancel = function () {
      $location.path('/school-years');
    };

    if (!vm.isEdit) {
      vm.initCampusBinding();
    }
    vm.load();
  }

})();
