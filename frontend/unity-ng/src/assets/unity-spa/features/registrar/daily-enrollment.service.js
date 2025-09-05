(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('DailyEnrollmentService', DailyEnrollmentService);

  DailyEnrollmentService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function DailyEnrollmentService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    var svc = {
      getDailyEnrollment: getDailyEnrollment
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
     * Fetch Daily Enrollment summary buckets by date and student type for a term and range.
     * @param {number|string} syid - tb_mas_sy.intID (selected term)
     * @param {string} dateFrom - YYYY-MM-DD
     * @param {string} dateTo - YYYY-MM-DD (inclusive)
     * @returns {Promise} $http promise resolving to JSON: { syid, date_from, date_to, data: [], totals: {} }
     */
    function getDailyEnrollment(syid, dateFrom, dateTo) {
      // Ensure backend receives Y-m-d formatted strings (Angular date inputs bind Date objects)
      function fmt(v) {
        if (!v) return '';
        // Date object
        if (Object.prototype.toString.call(v) === '[object Date]') {
          var yyyy = v.getFullYear();
          var mm = ('0' + (v.getMonth() + 1)).slice(-2);
          var dd = ('0' + v.getDate()).slice(-2);
          return yyyy + '-' + mm + '-' + dd;
        }
        // String already in Y-m-d
        if (typeof v === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
        // Try to parse other string formats into local Y-m-d
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

      var params = { syid: syid };
      if (dateFrom) { params.date_from = fmt(dateFrom); }
      if (dateTo) { params.date_to = fmt(dateTo); }
      var cfg = Object.assign({ params: params }, _adminHeaders());
      return $http.get(BASE + '/reports/daily-enrollment', cfg);
    }
  }

})();
