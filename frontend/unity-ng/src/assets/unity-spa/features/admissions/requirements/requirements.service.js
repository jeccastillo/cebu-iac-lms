(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('RequirementsService', RequirementsService);

  RequirementsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function RequirementsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1
    var ROOT = BASE + '/requirements';

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

      // Support boolean and string values for is_foreign
      if (filters.is_foreign !== undefined && filters.is_foreign !== null && filters.is_foreign !== '') {
        if (typeof filters.is_foreign === 'boolean') {
          p.is_foreign = filters.is_foreign ? 'true' : 'false';
        } else if (filters.is_foreign === '1' || filters.is_foreign === 1) {
          p.is_foreign = 'true';
        } else if (filters.is_foreign === '0' || filters.is_foreign === 0) {
          p.is_foreign = 'false';
        } else {
          p.is_foreign = '' + filters.is_foreign;
        }
      }

      if (filters.sort) p.sort = filters.sort;
      if (filters.order) p.order = filters.order;
      if (filters.page) p.page = filters.page;
      if (filters.per_page) p.per_page = filters.per_page;

      return p;
    }

    return {
      // -----------------------------
      // Requirements CRUD
      // -----------------------------

      // GET /requirements (filters: search; type; is_foreign; sort/order; page/per_page)
      list: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(ROOT, cfg).then(_unwrap);
      },

      // GET /requirements/{id}
      show: function (id) {
        return $http.get(ROOT + '/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // POST /requirements
      create: function (payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(ROOT, body, _adminHeaders()).then(_unwrap);
      },

      // PUT /requirements/{id}
      update: function (id, payload) {
        var body = Object.assign({}, payload || {});
        return $http.put(ROOT + '/' + encodeURIComponent(id), body, _adminHeaders()).then(_unwrap);
      },

      // DELETE /requirements/{id}
      remove: function (id) {
        return $http.delete(ROOT + '/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
