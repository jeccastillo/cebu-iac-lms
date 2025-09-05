(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ApplicantTypesService', ApplicantTypesService);

  ApplicantTypesService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ApplicantTypesService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1
    var ROOT = BASE + '/applicant-types';
    var PUBLIC_ROOT = BASE + '/admissions/applicant-types'; // read-only endpoint for applicant forms

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
      if (filters.type) p.type = filters.type;
      if (filters.sort) p.sort = filters.sort;
      if (filters.order) p.order = filters.order;
      if (filters.page) p.page = filters.page;
      if (filters.per_page) p.per_page = filters.per_page;

      return p;
    }

    return {
      // Admin CRUD
      list: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(ROOT, cfg).then(_unwrap);
      },
      show: function (id) {
        return $http.get(ROOT + '/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },
      create: function (payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(ROOT, body, _adminHeaders()).then(_unwrap);
      },
      update: function (id, payload) {
        var body = Object.assign({}, payload || {});
        return $http.put(ROOT + '/' + encodeURIComponent(id), body, _adminHeaders()).then(_unwrap);
      },
      remove: function (id) {
        return $http.delete(ROOT + '/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // Public read-only (not used by admin UI but exposed for applicant forms)
      publicList: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = { params: params };
        return $http.get(PUBLIC_ROOT, cfg).then(_unwrap);
      }
    };
  }

})();
