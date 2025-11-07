(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ApplicantsService', ApplicantsService);

  ApplicantsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ApplicantsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1
    var ROOT = BASE + '/applicants';
    var ENLIST_ROOT = BASE + '/enlistment/applicants';

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

      if (filters.search) p.search = filters.search;
      if (filters.status) p.status = filters.status;
      if (filters.campus) p.campus = filters.campus;
      if (filters.date_from) p.date_from = filters.date_from;
      if (filters.date_to) p.date_to = filters.date_to;

      if (filters.sort) p.sort = filters.sort;
      if (filters.order) p.order = filters.order;
      if (filters.page) p.page = filters.page;
      if (filters.per_page) p.per_page = filters.per_page;

      // Term filter (syid)
      if (filters.syid) p.syid = filters.syid;

      return p;
    }

    return {
      // List applicants with optional filters/pagination
      list: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(ROOT, cfg).then(_unwrap);
      },
      // List applicants eligible for enlistment (status=Reserved and paid flags)
      listEligible: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(ENLIST_ROOT, cfg).then(_unwrap);
      },
      // Get applicant details by tb_mas_users.intID
      show: function (id) {
        return $http.get(ROOT + '/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },
      // Update core identity/contact fields for applicant
      update: function (id, payload) {
        return $http.put(ROOT + '/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      // Download applicants import template
      downloadTemplate: function () {
        var cfg = _adminHeaders();
        cfg.responseType = 'arraybuffer';
        return $http.get(ROOT + '/template', cfg).then(function (res) {
          return res;
        });
      }
    };
  }

})();
