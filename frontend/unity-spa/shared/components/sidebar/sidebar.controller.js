(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('SidebarController', SidebarController);

  SidebarController.$inject = ['$scope', '$location', 'StorageService', 'TermService', 'CampusService', 'RoleService', 'LinkService'];
  function SidebarController($scope, $location, StorageService, TermService, CampusService, RoleService, LinkService) {
    var vm = this;

    // Sidebar state
    vm.isCollapsed = false;
    vm.isVisible = true;

    // User state
    vm.loginState = null;

    // Public methods
    vm.toggleCollapse = toggleCollapse;
    vm.isCurrentPath = isCurrentPath;

    // RBAC helpers exposed to template
    vm.canAccess = RoleService.canAccess;
    vm.hasRole = RoleService.hasRole;
    vm.roles = [];
    // External system links (e.g., CI endpoints)
    vm.links = LinkService.buildLinks();

    // Initialize
    activate();

    function activate() {
      // Get login state
      vm.loginState = StorageService.getJSON('loginState');
      RoleService.normalizeState(vm.loginState);
      vm.roles = RoleService.getRoles();
      
      // Initialize services
      TermService.init();
      CampusService.init();

      // Watch for login state changes
      $scope.$watch(function() {
        return StorageService.getJSON('loginState');
      }, function(newState) {
        vm.loginState = newState;
        if (newState && newState.loggedIn) {
          RoleService.normalizeState(newState);
          vm.roles = RoleService.getRoles();
        } else {
          vm.roles = [];
        }
        vm.isVisible = !!(newState && newState.loggedIn);
      }, true);

      // Watch route changes to determine sidebar visibility
      $scope.$on('$routeChangeSuccess', function() {
        var path = $location.path();
        // Hide sidebar on login page
        vm.isVisible = path !== '/login' && !!(vm.loginState && vm.loginState.loggedIn);
      });

      // Load collapsed state from storage
      var savedCollapsed = StorageService.get('sidebarCollapsed');
      if (savedCollapsed === 'true') {
        vm.isCollapsed = true;
      }
    }

    function toggleCollapse() {
      vm.isCollapsed = !vm.isCollapsed;
      StorageService.set('sidebarCollapsed', vm.isCollapsed.toString());
    }

    function isCurrentPath(path) {
      return $location.path() === path;
    }
  }

})();
