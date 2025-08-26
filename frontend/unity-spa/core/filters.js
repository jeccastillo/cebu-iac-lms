(function () {
  'use strict';

  // Global reusable filters for the Unity SPA
  angular
    .module('unityApp')
    .filter('ceil', function () {
      return function (input) {
        var n = Number(input || 0);
        return Math.ceil(n);
      };
    });

})();
