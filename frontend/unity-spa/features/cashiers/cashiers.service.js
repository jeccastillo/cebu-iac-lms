(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('CashiersService', CashiersService);

  CashiersService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function CashiersService($http, APP_CONFIG, StorageService) {
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
      // GET /cashiers/me
      me: function () {
        return $http.get(BASE + '/cashiers/me', _adminHeaders()).then(_unwrap);
      },

      // GET /cashiers?includeStats=bool&amp;campus_id=...
      list: function (opts) {
        var params = {};
        if (opts && typeof opts.includeStats !== 'undefined') {
          params.includeStats = !!opts.includeStats;
        }
        if (opts && opts.campus_id !== null && opts.campus_id !== undefined && opts.campus_id !== '') {
          params.campus_id = opts.campus_id;
        }
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/cashiers', cfg).then(_unwrap);
      },

      // POST /cashiers
      create: function (payload) {
        return $http.post(BASE + '/cashiers', payload, _adminHeaders()).then(_unwrap);
      },

      // PATCH /cashiers/{id}
      update: function (id, payload) {
        return $http.patch(BASE + '/cashiers/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },

      // POST /cashiers/{id}/ranges
      updateRanges: function (id, payload) {
        return $http.post(BASE + '/cashiers/' + encodeURIComponent(id) + '/ranges', payload, _adminHeaders()).then(_unwrap);
      },

      // GET /cashiers/{id}/stats
      stats: function (id) {
        return $http.get(BASE + '/cashiers/' + encodeURIComponent(id) + '/stats', _adminHeaders()).then(_unwrap);
      },

      // GET /cashiers/stats?page=&amp;perPage=
      statsAll: function (opts) {
        var params = {};
        if (opts && opts.page) params.page = opts.page;
        if (opts && opts.perPage) params.perPage = opts.perPage;
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/cashiers/stats', cfg).then(_unwrap);
      },

      // PATCH /cashiers/{id}/assign
      assign: function (id, faculty_id) {
        return $http.patch(BASE + '/cashiers/' + encodeURIComponent(id) + '/assign', { faculty_id: faculty_id }, _adminHeaders()).then(_unwrap);
      },

      // GET /faculty/search?q=&amp;campus_id=
      searchFaculty: function (query, campusId, perPage) {
        var params = {};
        if (query) params.q = query;
        if (campusId !== null && campusId !== undefined && campusId !== '') params.campus_id = campusId;
        if (perPage) params.per_page = perPage;
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/faculty/search', cfg).then(_unwrap);
      },

      // POST /cashiers/{id}/payments
      createPayment: function (cashierId, payload) {
        return $http.post(
          BASE + '/cashiers/' + encodeURIComponent(cashierId) + '/payments',
          payload,
          _adminHeaders()
        ).then(_unwrap);
      },

      // DELETE /cashiers/{id}
      delete: function (id) {
        return $http.delete(BASE + '/cashiers/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
