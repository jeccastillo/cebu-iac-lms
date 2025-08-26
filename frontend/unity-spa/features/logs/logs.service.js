(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('SystemLogsService', SystemLogsService);

  SystemLogsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function SystemLogsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    var svc = {
      list: list
    };

    return svc;

    // --------------- Helpers ---------------

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
      // RequireRole middleware expects X-Faculty-ID on protected routes
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      // API returns { success, data, meta? }
      return (resp && resp.data) ? resp.data : resp;
    }

    // --------------- API ---------------

    /**
     * List system logs with filters.
     * params: {
     *   page?, per_page?, entity?, action?, user_id?, entity_id?, method?, path?, q?, date_from?, date_to?
     * }
     */
    function list(params) {
      var cfg = Object.assign({ params: params || {} }, _adminHeaders());
      return $http.get(BASE + '/system-logs', cfg).then(_unwrap);
    }
  }

})();
