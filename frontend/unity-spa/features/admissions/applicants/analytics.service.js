(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ApplicantsAnalyticsService', ApplicantsAnalyticsService);

  ApplicantsAnalyticsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ApplicantsAnalyticsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1
    var ROOT = BASE + '/applicants/analytics';

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
      // Backend wraps as { success, data, meta? }
      return (resp && resp.data) ? resp.data : resp;
    }

    function _paramsFromFilters(filters) {
      var p = {};
      if (!filters) return p;

      // Normalize Angular option-encoded values like "number:123" to integers
      function toNum(val) {
        try {
          if (val === null || val === undefined) return null;
          var s = String(val);
          if (s.indexOf('number:') === 0) s = s.split(':')[1];
          var n = parseInt(s, 10);
          return isNaN(n) ? null : n;
        } catch (e) { return null; }
      }

      // Primary terms (support arrays for combined analysis)
      var aArrRaw = Array.isArray(filters.syidsA) ? filters.syidsA : [];
      var aArr = aArrRaw.map(toNum).filter(function (x) { return x !== null; });
      if (aArr.length > 1) {
        // Use repeated array params with [] so PHP parses as array
        p['syids[]'] = aArr;
      } else if (aArr.length === 1) {
        p.syid = aArr[0];
      } else if (filters.syidA) {
        // Backward-compat in case controller still passes single value
        var singleA = toNum(filters.syidA);
        if (singleA !== null) p.syid = singleA;
      }

      // Compare terms (optional; support arrays)
      var bArrRaw = Array.isArray(filters.syidsB) ? filters.syidsB : [];
      var bArr = bArrRaw.map(toNum).filter(function (x) { return x !== null; });
      if (bArr.length > 1) {
        p['compare_syids[]'] = bArr;
      } else if (bArr.length === 1) {
        p.compare_syid = bArr[0];
      } else if (filters.syidB) {
        // Backward-compat single
        var singleB = toNum(filters.syidB);
        if (singleB !== null) p.compare_syid = singleB;
      }

      // Optional date range
      if (filters.start) p.start = filters.start;
      if (filters.end) p.end = filters.end;

      // Filters
      if (filters.campus !== undefined && filters.campus !== null && filters.campus !== '') p.campus = filters.campus;
      if (filters.status) p.status = filters.status;
      if (filters.type) p.type = filters.type;
      if (filters.sub_type) p.sub_type = filters.sub_type;
      if (filters.search) p.search = filters.search;

      return p;
    }

    return {
      // Summary analytics for term (and optional compare term)
      summary: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(ROOT + '/summary', cfg).then(_unwrap);
      }
    };
  }

})();
