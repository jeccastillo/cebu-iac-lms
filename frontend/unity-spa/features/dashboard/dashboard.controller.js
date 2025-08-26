(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('DashboardController', DashboardController);

  DashboardController.$inject = ['$location', '$window', 'LinkService', 'StorageService'];
  function DashboardController($location, $window, LinkService, StorageService) {
    var vm = this;

    // Auth state and redirect targets
    vm.state = null;
    vm.destLabel = '';
    vm.destPath = '';

    // Dashboard data (placeholders until API is wired)
    vm.stats = {
      myClassesCount: 0,
      totalStudentsTaught: 0,
      pendingGrades: 0,
      submittedGrades: 0
    };
    vm.activeTerm = { label: 'ACTIVE TERM', value: '-' };
    vm.appTerm = { label: 'APPLICATION TERM', value: '-' };

    vm.recentSubmissions = []; // [{ className, subject, section, submittedAt, status }]
    vm.myClasses = []; // [{ subjectCode, className, section, units, status, id }]

    // Links to existing CI pages
    vm.links = LinkService.buildLinks();

    activate();

    vm.logout = function logout() {
      try {
        StorageService.remove('loginState');
      } catch (e) {}
      $location.path('/login');
    };

    vm.goToSystem = function goToSystem() {
      if (!vm.destPath) return;
      $window.location.href = vm.destPath;
    };

    function activate() {
      vm.state = StorageService.getJSON('loginState');
      if (!vm.state || !vm.state.loggedIn) {
        $location.path('/login');
        return;
      }

      var redirects = ($window.AFTER_LOGIN_REDIRECTS) || { faculty: '/unity', student: '/portal' };
      vm.destPath = redirects[vm.state.loginType] || '/';
      vm.destLabel = vm.state.loginType === 'student' ? 'Go to Student Portal' : 'Go to Unity';

      // Placeholders for term labels (ready for API)
      vm.activeTerm.value = '-';
      vm.appTerm.value = '-';
    }
  }

})();
