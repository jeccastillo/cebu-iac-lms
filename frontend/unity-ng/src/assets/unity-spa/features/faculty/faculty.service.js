(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('FacultyService', FacultyService);

  FacultyService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function FacultyService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    function _getLoginState() {
      try {
        return StorageService.getJSON('loginState') || null;
      } catch (e) {
        return null;
      }
    }

    function _adminHeaders(extra) {
      var state = _getLoginState();
      var headers = Object.assign({}, extra || {});
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      // List faculty with optional filters { q, teaching, isActive, page, per_page }
      list: function (opts) {
        var params = {};
        if (opts && opts.q) params.q = ('' + opts.q).trim();
        if (opts && (opts.teaching === 0 || opts.teaching === 1)) params.teaching = opts.teaching;
        if (opts && (opts.isActive === 0 || opts.isActive === 1)) params.isActive = opts.isActive;
        if (opts && opts.page) params.page = parseInt(opts.page, 10);
        if (opts && opts.per_page) params.per_page = parseInt(opts.per_page, 10);
        return $http.get(BASE + '/faculty', Object.keys(params).length ? { params: params, headers: _adminHeaders().headers } : _adminHeaders())
          .then(_unwrap);
      },
      // Get faculty by id
      get: function (id) {
        return $http.get(BASE + '/faculty/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },
      // Create faculty (payload must include required legacy fields)
      create: function (payload) {
        return $http.post(BASE + '/faculty', payload, _adminHeaders()).then(_unwrap);
      },
      // Update faculty (password optional; if provided will be hashed on backend)
      update: function (id, payload) {
        return $http.put(BASE + '/faculty/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      // Delete faculty
      remove: function (id) {
        return $http.delete(BASE + '/faculty/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
