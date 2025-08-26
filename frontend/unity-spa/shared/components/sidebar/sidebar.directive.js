(function () {
  'use strict';

  angular
    .module('unityApp')
    .directive('appSidebar', appSidebar);

  function appSidebar() {
    return {
      restrict: 'E',
      templateUrl: 'shared/components/sidebar/sidebar.html',
      controller: 'SidebarController',
      controllerAs: 'vm',
      scope: {},
      bindToController: true
    };
  }

})();
