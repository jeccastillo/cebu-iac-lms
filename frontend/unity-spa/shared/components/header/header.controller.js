(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('HeaderController', HeaderController);

  HeaderController.$inject = ['$rootScope', '$location', '$window', '$timeout', 'LinkService', 'StorageService'];
  function HeaderController($rootScope, $location, $window, $timeout, LinkService, StorageService) {
    var vm = this;
    var closeMenuTimeout;

    vm.state = StorageService.getJSON('loginState');
    vm.isLoggedIn = !!(vm.state && vm.state.loggedIn);

    // External CI links and internal SPA links
    vm.links = LinkService.buildLinks();
    vm.nav = LinkService.buildSpaLinks();

    vm.userMenuOpen = false;
    
    vm.toggleUserMenu = function toggleUserMenu() {
      // Cancel any pending close timeout
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
        closeMenuTimeout = null;
      }
      vm.userMenuOpen = !vm.userMenuOpen;
    };
    
    vm.closeUserMenu = function closeUserMenu() {
      vm.userMenuOpen = false;
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
        closeMenuTimeout = null;
      }
    };

    vm.scheduleCloseUserMenu = function scheduleCloseUserMenu() {
      // Cancel any existing timeout
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
      }
      // Schedule close after a short delay
      closeMenuTimeout = $timeout(function() {
        vm.userMenuOpen = false;
        closeMenuTimeout = null;
      }, 300); // 300ms delay
    };

    vm.cancelCloseUserMenu = function cancelCloseUserMenu() {
      if (closeMenuTimeout) {
        $timeout.cancel(closeMenuTimeout);
        closeMenuTimeout = null;
      }
    };

    // Close dropdowns on route change
    $rootScope.$on('$routeChangeStart', function () {
      vm.closeUserMenu();
    });

    vm.logout = function logout() {
      StorageService.remove('loginState');
      $location.path('/login');
    };

    vm.goToEmployeePortal = function () {
      $window.open('https://employeeportal.iacademy.edu.ph', '_blank');
    };
  }

})();
