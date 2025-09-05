(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('SectionsSlotsService', SectionsSlotsService);

  SectionsSlotsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function SectionsSlotsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    var svc = {
      list: list
    };
    return svc;

    // ---------------- Helpers ----------------
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

    // ---------------- API ----------------
    /**
     * List classlists with slots utilization for a given term.
     * params: {
     *   term: number (required),
     *   page?: number,
     *   perPage?: number,
     *   intSubjectID?, intFacultyID?, section?, class_name?, year?, sub_section?
     * }
     * Returns { data: [], meta: { page, per_page, total } }
     */
    function list(params) {
      var cfg = Object.assign({ params: params || {} }, _adminHeaders());
      // Using temporary debug endpoint until a stable endpoint is added
      return $http.get(BASE + '/debug/classlists/slots', cfg).then(function (res) {
        return res && res.data ? res.data : { data: [], meta: { page: 1, per_page: 20, total: 0 } };
      });
    }
  }
})();
