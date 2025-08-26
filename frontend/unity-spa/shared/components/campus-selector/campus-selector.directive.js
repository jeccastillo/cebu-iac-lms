(function () {
  'use strict';

  angular
    .module('unityApp')
    .directive('campusSelector', campusSelector);

  function campusSelector() {
    return {
      restrict: 'E',
      templateUrl: 'shared/components/campus-selector/campus-selector.html',
      controller: 'CampusSelectorController',
      controllerAs: 'vm',
      scope: {
        compact: '@?',   // Optional compact mode for smaller displays
        showLabel: '@?'  // Optional to show/hide label
      },
      bindToController: true
    };
  }

})();
