(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultyProfileController', FacultyProfileController);

  FacultyProfileController.$inject = ['StorageService', 'FacultyService'];
  function FacultyProfileController(StorageService, FacultyService) {
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

    // Load actual profile from API
    loadProfile();

    function loadProfile() {
      return FacultyService.getMe()
        .then(function (f) {
          f = f || {};
          vm.profile = {
            firstName: f.strFirstname || f.firstName || '',
            lastName: f.strLastname || f.lastName || '',
            email: f.strEmail || f.email || '',
            department: f.department || f.department_name || f.departmentName || '',
            position: f.position || f.position_name || f.positionName || ''
          };
        })
        .catch(function () {
          // keep placeholders on error
        });
    }
  }

})();
