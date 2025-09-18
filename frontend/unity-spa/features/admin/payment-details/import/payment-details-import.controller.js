(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('PaymentDetailsImportController', PaymentDetailsImportController);

  PaymentDetailsImportController.$inject = ['$scope', 'PaymentDetailsImportService', 'ToastService'];
  function PaymentDetailsImportController($scope, PaymentDetailsImportService, ToastService) {
    var vm = this;

    vm.importFile = null;
    vm.importing = false;
    vm.importError = '';
    vm.importSummary = null;

    vm.onFileSelected = onFileSelected;
    vm.downloadTemplate = downloadTemplate;
    vm.upload = upload;
    vm.clearSummary = clearSummary;

    function onFileSelected(files) {
      try {
        if (files && files.length) {
          vm.importFile = files[0];
        }
      } catch (e) {}
    }

    function downloadTemplate() {
      try {
        PaymentDetailsImportService.downloadTemplate().then(function () {
          // no-op
        }).catch(function (err) {
          vm.importError = (err && (err.message || err.data && err.data.message)) || 'Failed to download template.';
          ToastService.error(vm.importError);
        });
      } catch (e) {
        vm.importError = e.message || 'Failed to download template.';
        ToastService.error(vm.importError);
      }
    }

    function upload() {
      if (!vm.importFile || vm.importing) return;
      vm.importError = '';
      vm.importSummary = null;
      vm.importing = true;

      PaymentDetailsImportService.upload(vm.importFile)
        .then(function (res) {
          if (res && res.success && res.result) {
            vm.importSummary = res.result;
            ToastService.success('Import completed.');
          } else {
            vm.importError = (res && res.message) || 'Import failed.';
            ToastService.error(vm.importError);
          }
        })
        .catch(function (err) {
          vm.importError = (err && (err.message || (err.data && err.data.message))) || 'Import failed.';
          ToastService.error(vm.importError);
        })
        .finally(function () {
          vm.importing = false;
          // Trigger digest if needed
          try { $scope.$applyAsync(); } catch (e) {}
        });
    }

    function clearSummary() {
      vm.importSummary = null;
    }
  }

})();
