(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultyProfileController', FacultyProfileController);

  FacultyProfileController.$inject = ['StorageService'];
  function FacultyProfileController(StorageService) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    vm.title = 'My Profile';
    vm.user = {
      username: vm.state ? vm.state.username : 'guest',
      loginType: vm.state ? vm.state.loginType : 'faculty'
    };

    // Placeholder data; will be replaced by API integration
    vm.profile = {
      firstName: '',
      lastName: '',
      email: '',
      department: '',
      position: ''
    };
  }

})();
