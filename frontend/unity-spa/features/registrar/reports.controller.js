(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ReportsController', ReportsController);

  ReportsController.$inject = ['$scope', '$location', 'LinkService', 'StorageService', 'ReportsService', 'TermService'];
  function ReportsController($scope, $location, LinkService, StorageService, ReportsService, TermService) {
    var vm = this;

    vm.title = 'Registrar Reports';
    vm.state = StorageService.getJSON('loginState');

    // guard if accessed directly without login (extra safety beyond run.js)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Legacy CI links (used during migration)
    vm.links = LinkService.buildLinks();
    // Internal SPA nav
    vm.nav = LinkService.buildSpaLinks();

    // Export state and actions
    vm.loading = false;
    vm.error = null;
    vm.selectedTerm = (TermService && TermService.getSelectedTerm) ? TermService.getSelectedTerm() : null;

    vm.exportEnrolled = exportEnrolled;
    vm.exportEnrollmentStatsPdf = exportEnrollmentStatsPdf;

    // Keep selected term in sync with sidebar TermSelector
    $scope.$on('termChanged', function (event, data) {
      if (data && data.selectedTerm) {
        vm.selectedTerm = data.selectedTerm;
      }
    });

    function exportEnrolled() {
      vm.error = null;
      vm.loading = true;

      var term = vm.selectedTerm;
      if (!term || !term.intID) {
        try {
          var t = TermService && TermService.getSelectedTerm && TermService.getSelectedTerm();
          if (t && t.intID) {
            term = t;
            vm.selectedTerm = t;
          }
        } catch (e) {}
      }

      if (!term || !term.intID) {
        vm.loading = false;
        vm.error = 'Please select an academic term first.';
        return;
      }

      ReportsService.exportEnrolled(term.intID)
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
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Open Enrollment Statistics PDF in a new tab (inline)
    function exportEnrollmentStatsPdf() {
      vm.error = null;
      vm.loadingStats = true;

      var term = vm.selectedTerm;
      if (!term || !term.intID) {
        try {
          var t = TermService && TermService.getSelectedTerm && TermService.getSelectedTerm();
          if (t && t.intID) {
            term = t;
            vm.selectedTerm = t;
          }
        } catch (e) {}
      }

      if (!term || !term.intID) {
        vm.loadingStats = false;
        vm.error = 'Please select an academic term first.';
        return;
      }

      ReportsService.exportEnrollmentStatsPdf(term.intID)
        .then(function (resp) {
          var blob = new Blob([resp.data], { type: 'application/pdf' });
          var url = window.URL.createObjectURL(blob);
          // Open in a new tab
          var win = window.open(url, '_blank');
          if (!win) {
            // Popup blocked; fallback to creating a link the user can click
            var a = document.createElement('a');
            a.href = url;
            a.target = '_blank';
            a.textContent = 'Open Enrollment Statistics PDF';
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
              document.body.removeChild(a);
            }, 0);
          }
          // Revoke after some time to ensure the tab loads
          setTimeout(function () { window.URL.revokeObjectURL(url); }, 60000);
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to generate PDF';
        })
        .finally(function () {
          vm.loadingStats = false;
        });
    }
  }

})();
