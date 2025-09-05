(function () {
  'use strict';

  // Decorate $templateRequest to prepend the assets base path so AngularJS templates
  // resolve correctly when the SPA is served under Angular CLI (assets/unity-spa/...).
  //
  // Example:
  //   templateUrl: "features/registrar/enlistment/enlistment.html"
  // becomes:
  //   assets/unity-spa/features/registrar/enlistment/enlistment.html
  //
  // This avoids having to change every templateUrl in the codebase.
  angular.module('unityApp')
    .config(['$provide', function ($provide) {
      $provide.decorator('$templateRequest', ['$delegate', function ($delegate) {
        var ASSETS_PREFIX = 'assets/unity-spa/';
        return function (tpl, ignoreRequestError) {
          try {
            if (typeof tpl === 'string') {
              var isHttp = /^https?:\/\//i.test(tpl);
              var alreadyPrefixed = tpl.indexOf(ASSETS_PREFIX) === 0;
              if (!isHttp && !alreadyPrefixed) {
                // Normalize leading slashes
                tpl = ASSETS_PREFIX + tpl.replace(/^\/+/, '');
              }
            }
          } catch (e) {
            // swallow and continue with original value
          }
          return $delegate(tpl, ignoreRequestError);
        };
      }]);
    }]);

})();
