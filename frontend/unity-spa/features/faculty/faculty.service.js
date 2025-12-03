(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('FacultyService', FacultyService);

  FacultyService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function FacultyService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

    function _getLoginState() {
      try {
        return StorageService.getJSON('loginState') || null;
      } catch (e) {
        return null;
      }
    }

    // Attach X-Faculty-ID header when available (pattern consistent with other services)
    function _headers(extra) {
      var headers = Object.assign({ 'Accept': 'application/json' }, extra || {});
      var state = _getLoginState();
      if (state && state.faculty_id != null && ('' + state.faculty_id).trim() !== '') {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return headers;
    }

    function _unwrap(resp) {
      var data = (resp && resp.data) ? resp.data : resp;
      // Most API responses shape: { success, data }
      if (data && typeof data === 'object' && ('data' in data)) {
        return data.data;
      }
      return data;
    }

    // GET /faculty/me
    function getMe() {
      return $http.get(BASE + '/faculty/me', { headers: _headers() })
        .then(function (resp) { return _unwrap(resp); });
    }

    // PUT /faculty/me
    function updateMe(payload) {
      return $http.put(BASE + '/faculty/me', payload, { headers: _headers({ 'Content-Type': 'application/json' }) })
        .then(function (resp) { return _unwrap(resp); });
    }

    // POST /faculty/me/password
    function updatePassword(payload) {
      return $http.post(BASE + '/faculty/me/password', payload, { headers: _headers({ 'Content-Type': 'application/json' }) })
        .then(function (resp) { return _unwrap(resp); });
    }

    return {
      getMe: getMe,
      updateMe: updateMe,
      updatePassword: updatePassword
    };
  }

})();
