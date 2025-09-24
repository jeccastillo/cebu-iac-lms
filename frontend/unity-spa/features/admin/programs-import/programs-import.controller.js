(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminProgramsImportController', AdminProgramsImportController);

  AdminProgramsImportController.$inject = ['$scope', '$timeout', 'CampusService', 'ProgramsService'];
  function AdminProgramsImportController($scope, $timeout, CampusService, ProgramsService) {
    var vm = this;

    vm.title = 'Programs Import';
    vm.importing = false;
    vm.dry_run = false;
    vm.file = null;
    vm.error = null;
    vm.summary = null;
    vm.selectedCampus = null;

    vm.CampusService = CampusService;

    vm.downloadTemplate = downloadTemplate;
    vm.openFileDialog = openFileDialog;
    vm.onFileChanged = onFileChanged;
    vm.importFile = importFile;

    activate();

    function activate() {
      try {
        var p = (CampusService && CampusService.init) ? CampusService.init() : null;

        function setFromSelectedCampus() {
          try {
            vm.selectedCampus = (CampusService && CampusService.getSelectedCampus)
              ? CampusService.getSelectedCampus()
              : null;
          } catch (e) {
            vm.selectedCampus = null;
          }
        }

        if (p && p.then) { p.then(setFromSelectedCampus); } else { setFromSelectedCampus(); }

        $scope.$on('campusChanged', function (event, data) {
          if (data && data.selectedCampus) {
            vm.selectedCampus = data.selectedCampus;
          } else {
            try {
              vm.selectedCampus = (CampusService && CampusService.getSelectedCampus)
                ? CampusService.getSelectedCampus()
                : null;
            } catch (e) {}
          }
        });
      } catch (e) {}
    }

    function downloadTemplate() {
      ProgramsService.downloadImportTemplate()
        .then(function (res) {
          try {
            var blob = new Blob([res.data], {
              type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });
            var url = (window.URL || window.webkitURL).createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = res.filename || 'programs-import-template.xlsx';
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
        var el = document.getElementById('programsImportFile');
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

      var campusId = null;
      try {
        var c = vm.selectedCampus || (CampusService && CampusService.getSelectedCampus && CampusService.getSelectedCampus());
        if (c && c.id !== undefined && c.id !== null && ('' + c.id).trim() !== '') {
          campusId = parseInt(c.id, 10);
        }
      } catch (e) {}

      if (campusId === null || !isFinite(campusId)) {
        vm.error = 'Please select a campus.';
        return;
      }

      vm.importing = true;

      ProgramsService.importFile(vm.file, {
        campus_id: campusId,
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
