(function () {
  'use strict';

  angular
    .module('unityApp')
    .directive('termSelector', termSelector);

  function termSelector() {
    return {
      restrict: 'E',
      templateUrl: 'shared/components/term-selector/term-selector.html',
      controller: 'TermSelectorController',
      controllerAs: 'vm',
      scope: {
        compact: '@?', // Optional compact mode for smaller displays
        showLabel: '@?' // Optional to show/hide label
      },
      bindToController: true
    };
  }

})();
