(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminCurriculaImportController', AdminCurriculaImportController);

  AdminCurriculaImportController.$inject = ['$scope', '$timeout', 'CurriculaService'];
  function AdminCurriculaImportController($scope, $timeout, CurriculaService) {
    var vm = this;

    vm.title = 'Curricula Import';
    vm.importing = false;
    vm.dry_run = false;
    vm.file = null;
    vm.error = null;
    vm.summary = null;

    vm.downloadTemplate = downloadTemplate;
    vm.openFileDialog = openFileDialog;
    vm.onFileChanged = onFileChanged;
    vm.importFile = importFile;

    function downloadTemplate() {
      CurriculaService.downloadImportTemplate()
        .then(function (res) {
          try {
            var blob = new Blob([res.data], {
              type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });
            var url = (window.URL || window.webkitURL).createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = res.filename || 'curriculum-import-template.xlsx';
            document.body.appendChild(a);
            a.click();
            safeDefer(function () {
              try {
                (window.URL || window.webkitURL).revokeObjectURL(url);
                document.body.removeChild(a);
              } catch (e) {}
            });
          } catch (e) {
            vm.error = 'Failed to download template.';
            safeClearErrorSoon();
          }
        })
        .catch(function (err) {
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to download template.';
          safeClearErrorSoon();
        });
    }

    function openFileDialog() {
      try {
        var el = document.getElementById('curriculaImportFile');
        if (el && el.click) el.click();
      } catch (e) {}
    }

    function onFileChanged($event) {
      try {
        vm.file = ($event && $event.target && $event.target.files && $event.target.files[0]) || null;
      } catch (e) {
        vm.file = null;
      }
      try { $scope.$applyAsync(); } catch (e) {}
    }

    function importFile() {
      vm.error = null;
      vm.summary = null;

      if (!vm.file) {
        vm.error = 'Please choose a file (.xlsx preferred).';
        return;
      }

      vm.importing = true;

      CurriculaService.importFile(vm.file, {
        dry_run: vm.dry_run
      })
        .then(function (resp) {
          var body = (resp && resp.data !== undefined) ? resp : (resp || {});
          var result = (body && body.result) ? body.result : body;
          vm.summary = result || null;
        })
        .catch(function (err) {
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Import failed.';
        })
        .finally(function () {
          vm.importing = false;
        });
    }

    function safeDefer(fn) {
      try { $timeout(fn, 0); } catch (e) {}
    }

    function safeClearErrorSoon() {
      try { $timeout(function () { vm.error = null; }, 5000); } catch (e) {}
    }
  }
})();
