(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultySettingsController', FacultySettingsController);

  FacultySettingsController.$inject = ['StorageService'];
  function FacultySettingsController(StorageService) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    vm.title = 'Settings';
    vm.model = {
      displayName: vm.state ? vm.state.username : '',
      notifyByEmail: true,
      theme: 'light'
    };

    vm.saving = false;
    vm.message = '';

    vm.save = function save() {
      vm.saving = true;
      vm.message = '';
      // Placeholder for API integration
      setTimeout(function () {
        vm.saving = false;
        vm.message = 'Settings saved (local placeholder)';
      }, 500);
    };
  }

})();
