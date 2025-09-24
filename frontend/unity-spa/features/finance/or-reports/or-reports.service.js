(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('OrReportsService', OrReportsService);

  OrReportsService.$inject = ['$http', 'APP_CONFIG', 'StorageService', 'CampusService'];
  function OrReportsService($http, APP_CONFIG, StorageService, CampusService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    return {
      list: list
    };

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
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id; // Required by role middleware
      }
      return { headers: headers };
    }

    function _fmtDate(v) {
      if (!v) return '';
      if (Object.prototype.toString.call(v) === '[object Date]') {
        var yyyy = v.getFullYear();
        var mm = ('0' + (v.getMonth() + 1)).slice(-2);
        var dd = ('0' + v.getDate()).slice(-2);
        return yyyy + '-' + mm + '-' + dd;
      }
      if (typeof v === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
      if (typeof v === 'string') {
        var d = new Date(v);
        if (!isNaN(d.getTime())) {
          var yyyy2 = d.getFullYear();
          var mm2 = ('0' + (d.getMonth() + 1)).slice(-2);
          var dd2 = ('0' + d.getDate()).slice(-2);
          return yyyy2 + '-' + mm2 + '-' + dd2;
        }
      }
      return v;
    }

    // ---------------- API ----------------

    /**
     * Fetch Official Receipt reports for a date range (filters use payment_details.or_date when present).
     * @param {{date_from: string|Date, date_to: string|Date}} filters
     * @returns {Promise} resolves to { success, data: Row[], meta }
     */
    function list(filters) {
      var params = {};
      filters = filters || {};
      if (filters.date_from) params.date_from = _fmtDate(filters.date_from);
      if (filters.date_to) params.date_to = _fmtDate(filters.date_to);

      // Attach selected campus automatically when available
      try {
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id != null) {
          params.campus_id = campus.id;
        }
      } catch (e) {}

      var cfg = Object.assign({ params: params }, _adminHeaders());
      return $http.get(BASE + '/reports/official-receipts', cfg).then(function (resp) {
        return (resp && resp.data) ? resp.data : resp;
      });
    }
  }
})();
