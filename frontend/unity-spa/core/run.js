(function () {
  'use strict';

  angular
    .module('unityApp')
    .run(initialize);

  initialize.$inject = ['$rootScope', '$location', 'APP_CONFIG', 'StorageService', 'RoleService'];
  function initialize($rootScope, $location, APP_CONFIG, StorageService, RoleService) {
    // Expose app name and runtime config for templates if needed
    $rootScope.appName = 'iACADEMY Unity SPA';
    $rootScope.runtime = {
      redirects: APP_CONFIG.AFTER_LOGIN_REDIRECTS,
      options: APP_CONFIG.LOGIN_APP_CONFIG
    };

    // Normalize malformed hash paths like '#//registrar/...'
    try {
      var currentPath = $location.path() || '';
      var normalizedPath = currentPath.replace(/\/{2,}/g, '/');
      if (normalizedPath !== currentPath) {
        $location.path(normalizedPath);
      }
    } catch (e) {
      // ignore normalization errors
    }

    // Basic route guard + role-based gate
    $rootScope.$on('$routeChangeStart', function (event, next) {
      var state = getLoginState();
      var route = (next && next.$$route) || {};
      var target = route.originalPath || '';

      // Toggle global chrome (header/sidebar) for public pages
      // Hide on: /public/initial-requirements/:hash
      try {
        $rootScope.hideChrome = !!(typeof target === 'string' && target.indexOf('/public/initial-requirements') === 0);
      } catch (e) {
        $rootScope.hideChrome = false;
      }

      var isProtected =
        target === '/dashboard' ||
        target === '/students' ||
        target.indexOf('/faculty/') === 0 ||
        target.indexOf('/registrar/') === 0 ||
        target.indexOf('/finance/') === 0 ||
        target.indexOf('/scholarship/') === 0 ||
        target.indexOf('/campuses') === 0;

      if (isProtected && (!state || !state.loggedIn)) {
        // prevent unauthorized navigation
        event.preventDefault();
        $location.path('/login');
        return;
      }

      // Normalize roles into login state for downstream checks
      if (state && state.loggedIn) {
        RoleService.normalizeState(state);
      }

      // Enforce role checks only when route defines requiredRoles metadata
      if (route && Array.isArray(route.requiredRoles) && route.requiredRoles.length > 0) {
        if (!RoleService.canAccess(route)) {
          event.preventDefault();
          $location.path('/dashboard');
        }
      }
    });

    function getLoginState() {
      return StorageService.getJSON('loginState');
    }
  }

})();
