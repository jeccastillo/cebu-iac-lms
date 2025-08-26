(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('RoleService', RoleService);

  RoleService.$inject = ['StorageService', 'ACCESS_MATRIX', 'ROLE_CONFIG', 'ROLES'];
  function RoleService(StorageService, ACCESS_MATRIX, ROLE_CONFIG, ROLES) {
    var cachedRoles = null;
    var cachedUserKey = null;

    var service = {
      getRoles: getRoles,
      deriveRoles: deriveRoles,
      hasRole: hasRole,
      canAccess: canAccess,
      normalizeState: normalizeState,
      // for testing
      _clearCache: clearCache
    };
    return service;

    // -------------- public --------------

    function getRoles() {
      var state = _getLoginState();
      if (!state) return [];

      var key = _buildUserKey(state);
      if (cachedRoles && cachedUserKey === key) {
        return cachedRoles.slice();
      }

      var roles;
      if (Array.isArray(state.roles) && state.roles.length) {
        roles = state.roles.slice();
      } else {
        roles = deriveRoles(state.username, state.loginType);
      }

      roles = _normalizeRoles(roles);

      cachedRoles = roles.slice();
      cachedUserKey = key;
      return roles;
    }

    function deriveRoles(username, loginType) {
      var roles = [];

      if (username && ROLE_CONFIG.USER_ROLE_MAP && ROLE_CONFIG.USER_ROLE_MAP[username]) {
        roles = (ROLE_CONFIG.USER_ROLE_MAP[username] || []).slice();
      } else {
        if (loginType === 'student') {
          roles = (ROLE_CONFIG.DEFAULT_STUDENT_ROLES || ['student_view']).slice();
        } else {
          roles = (ROLE_CONFIG.DEFAULT_FACULTY_ROLES || ['faculty']).slice();
        }
      }

      return _normalizeRoles(roles);
    }

    function hasRole(role) {
      role = (role || '').toLowerCase();
      if (!role) return false;
      return getRoles().indexOf(role) !== -1;
    }

    // pathOrRoute: string path ('/registrar/reports') or $route definition (with originalPath, requiredRoles)
    function canAccess(pathOrRoute) {
      var path = '';
      var required = null;

      if (typeof pathOrRoute === 'string') {
        path = pathOrRoute;
      } else if (pathOrRoute && pathOrRoute.originalPath) {
        path = pathOrRoute.originalPath;
        if (Array.isArray(pathOrRoute.requiredRoles)) {
          required = pathOrRoute.requiredRoles;
        }
      }

      if (!path) return true;

      var mine = getRoles();

      // 1) Route-level requiredRoles has priority if present
      if (required && required.length) {
        return _rolesIntersect(required, mine);
      }

      // 2) Access matrix pattern match
      var matrixRoles = _matchAccessMatrix(path);
      if (matrixRoles && matrixRoles.length) {
        return _rolesIntersect(matrixRoles, mine);
      }

      // 3) Default: any authenticated (role-agnostic)
      return true;
    }

    function normalizeState(state) {
      if (!state) return state;
      if (!Array.isArray(state.roles) || !state.roles.length) {
        state.roles = deriveRoles(state.username, state.loginType);
        // Persist back for downstream consumers
        StorageService.setJSON('loginState', state);
        clearCache();
      }
      return state;
    }

    // -------------- internal --------------

    function _getLoginState() {
      return StorageService.getJSON('loginState') || null;
    }

    function _buildUserKey(state) {
      if (!state) return 'anon';
      return (state.username || '') + '|' + (state.loginType || '');
    }

    function _normalizeRoles(arr) {
      var out = [];
      var seen = {};
      (arr || []).forEach(function (r) {
        if (!r) return;
        var v = (r + '').trim().toLowerCase();
        if (!v) return;
        if (!seen[v]) {
          seen[v] = true;
          out.push(v);
        }
      });
      return out;
    }

    function _rolesIntersect(allowed, mine) {
      if (!allowed || !allowed.length) return true;
      if (!mine || !mine.length) return false;
      for (var i = 0; i < allowed.length; i++) {
        var a = (allowed[i] + '').toLowerCase();
        if (mine.indexOf(a) !== -1) return true;
      }
      return false;
    }

    function _matchAccessMatrix(path) {
      for (var i = 0; i < ACCESS_MATRIX.length; i++) {
        var item = ACCESS_MATRIX[i];
        try {
          var rx = new RegExp(item.test);
          if (rx.test(path)) {
            return item.roles || [];
          }
        } catch (e) {
          // ignore malformed patterns
        }
      }
      return null;
    }

    function clearCache() {
      cachedRoles = null;
      cachedUserKey = null;
    }
  }

})();
