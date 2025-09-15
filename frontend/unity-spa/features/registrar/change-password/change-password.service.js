(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('RegistrarChangePasswordService', RegistrarChangePasswordService);

  RegistrarChangePasswordService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function RegistrarChangePasswordService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE;

    function _getLoginState() {
      try {
        return StorageService.getJSON('loginState') || null;
      } catch (e) {
        return null;
      }
    }

    function _headers() {
      var state = _getLoginState();
      var headers = {};
      // Expose acting faculty for role middleware (temporary dev approach)
      if (state && state.faculty_id != null) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      // Also expose campus when available for multi-campus scoping
      if (state && state.campus_id != null) {
        headers['X-Campus-ID'] = state.campus_id;
      }
      // Roles header (fallback when Auth context is absent)
      if (state && Array.isArray(state.roles) && state.roles.length > 0) {
        headers['X-User-Roles'] = state.roles.join(',');
      }
      return headers;
    }

    function _unwrap(resp) {
      return resp && resp.data ? resp.data : resp;
    }

    /**
     * Change a student's password (registrar/admin).
     * @param {number} studentId
     * @param {'generate'|'set'} mode
     * @param {string=} newPassword required when mode === 'set'
     * @param {string=} note optional audit note
     */
    function changePassword(studentId, mode, newPassword, note) {
      var payload = { mode: mode };
      if (mode === 'set') {
        payload.new_password = newPassword || '';
      }
      if (note && ('' + note).trim() !== '') {
        payload.note = ('' + note).trim();
      }
      return $http.post(BASE + '/students/' + encodeURIComponent(studentId) + '/password', payload, {
        headers: _headers()
      }).then(_unwrap);
    }

    return {
      changePassword: changePassword
    };
  }
})();
