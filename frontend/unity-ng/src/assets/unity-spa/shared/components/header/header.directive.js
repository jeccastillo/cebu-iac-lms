(function () {
  'use strict';

  angular
    .module('unityApp')
    .directive('appHeader', appHeader);

  function appHeader() {
    return {
      restrict: 'E',
      scope: {},
      controller: 'HeaderController',
      controllerAs: 'vm',
      bindToController: true,
      templateUrl: 'shared/components/header/header.html'
    };
  }

})();
