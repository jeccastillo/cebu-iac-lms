(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('DailyEnrollmentController', DailyEnrollmentController);

  DailyEnrollmentController.$inject = ['$scope', 'DailyEnrollmentService', 'ReportsService', 'TermService'];
  function DailyEnrollmentController($scope, DailyEnrollmentService, ReportsService, TermService) {
    var vm = this;

    // State
    vm.title = 'Daily Enrollment';
    vm.selectedTerm = null;
    vm.dateFrom = '';
    vm.dateTo = '';
    vm.loading = false;
    vm.error = null;

    // Data
    vm.rows = [];
    vm.totals = null;
    vm.overallTotal = 0;

    // Actions
    vm.load = load;
    vm.exportEnrolled = exportEnrolled;

    // Local helpers
    function fmt(v) {
      if (!v) return '';
      if (Object.prototype.toString.call(v) === '[object Date]') {
        var yyyy = v.getFullYear();
        var mm = ('0' + (v.getMonth() + 1)).slice(-2);
        var dd = ('0' + v.getDate()).slice(-2);
        return yyyy + '-' + mm + '-' + dd;
      }
      if (typeof v === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
      if (typeof v === 'string') {
        var d = new Date(v);
        if (!isNaN(d.getTime())) {
          var yyyy2 = d.getFullYear();
          var mm2 = ('0' + (d.getMonth() + 1)).slice(-2);
          var dd2 = ('0' + d.getDate()).slice(-2);
          return yyyy2 + '-' + mm2 + '-' + dd2;
        }
      }
      return v;
    }

    // Init
    init();

    function init() {
      // Default selected term
      try {
        var t = TermService && TermService.getSelectedTerm && TermService.getSelectedTerm();
        if (t && t.intID) {
          vm.selectedTerm = t;
        }
      } catch (e) {}

      // Default date range: empty to load full term if not specified
      vm.dateFrom = '';
      vm.dateTo = '';

      // Auto-load full term when term is available
      if (vm.selectedTerm && vm.selectedTerm.intID) {
        vm.load();
      }

      // Listen for term changes from the sidebar TermSelector
      $scope.$on('termChanged', function (event, data) {
        if (data && data.selectedTerm) {
          vm.selectedTerm = data.selectedTerm;
        }
      });
    }

    function validate() {
      vm.error = null;
      if (!vm.selectedTerm || !vm.selectedTerm.intID) {
        vm.error = 'Please select an academic term first.';
        return false;
      }

      // Allow empty date range to mean "full term"
      var hasFrom = !!vm.dateFrom;
      var hasTo = !!vm.dateTo;

      // If both are empty, treat as full-term query (valid)
      if (!hasFrom && !hasTo) {
        return true;
      }

      // If only one is provided, invalid
      if ((hasFrom && !hasTo) || (!hasFrom && hasTo)) {
        vm.error = 'Please provide both start and end dates, or leave both empty to use full term.';
        return false;
      }

      // If both are provided, ensure correct order
      if (vm.dateFrom > vm.dateTo) {
        vm.error = 'Start date must be before or equal to end date.';
        return false;
      }

      return true;
    }

    function load() {
      if (!validate()) return;

      vm.loading = true;
      vm.rows = [];
      vm.totals = null;
      vm.overallTotal = 0;

      // Normalize dates to Y-m-d before sending (defensive; service also formats)
      var df = fmt(vm.dateFrom);
      var dt = fmt(vm.dateTo);

      DailyEnrollmentService
        .getDailyEnrollment(vm.selectedTerm.intID, df, dt)
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          if (!data) {
            vm.error = 'No data returned from server.';
            return;
          }
          vm.rows = Array.isArray(data.data) ? data.data : [];
          vm.totals = data.totals || null;

          // Compute overall total across days
          vm.overallTotal = 0;
          vm.rows.forEach(function (r) {
            if (typeof r.total === 'number') {
              vm.overallTotal += r.total;
            }
          });
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load data.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function exportEnrolled() {
      vm.error = null;
      if (!vm.selectedTerm || !vm.selectedTerm.intID) {
        vm.error = 'Please select an academic term first.';
        return;
      }

      ReportsService.exportEnrolled(vm.selectedTerm.intID)
        .then(function (resp) {
          var disposition = resp.headers ? resp.headers('content-disposition') : null;
          var filename = null;
          if (disposition && typeof disposition === 'string') {
            var match = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i.exec(disposition);
            if (match && match[1]) {
              filename = match[1].replace(/['"]/g, '');
            }
          }
          if (!filename) {
            var dt = new Date();
            var pad = function (n) { return (n < 10 ? '0' : '') + n; };
            var stamp = dt.getFullYear().toString()
              + pad(dt.getMonth() + 1)
              + pad(dt.getDate())
              + '-' + pad(dt.getHours())
              + pad(dt.getMinutes())
              + pad(dt.getSeconds());
            filename = 'enrolled-students-' + stamp + '.xlsx';
          }

          var blob = new Blob([resp.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
          var url = window.URL.createObjectURL(blob);
          var a = document.createElement('a');
          a.href = url;
          a.download = filename;
          document.body.appendChild(a);
          a.click();
          setTimeout(function () {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
          }, 0);
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Export failed';
        });
    }
  }

})();
