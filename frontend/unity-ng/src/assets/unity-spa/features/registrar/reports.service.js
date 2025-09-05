(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ReportsService', ReportsService);

  ReportsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ReportsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    var svc = {
      exportEnrolled: exportEnrolled
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

    // --------------- API ---------------

    /**
     * Export enrolled students for a term as XLSX.
     * @param {number|string} syid - tb_mas_sy.intID (selected term)
     * Returns full $http response to access headers and binary data.
     */
    function exportEnrolled(syid) {
      var params = { syid: syid };
      var cfg = Object.assign({ params: params, responseType: 'arraybuffer' }, _adminHeaders());
      return $http.get(BASE + '/reports/enrolled-students/export', cfg);
    }
  }

})();
