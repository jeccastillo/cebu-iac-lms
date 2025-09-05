(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('PaymentModesService', PaymentModesService);

  PaymentModesService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function PaymentModesService($http, APP_CONFIG, StorageService) {
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

    function _paramsFromFilters(filters) {
      var p = {};
      if (!filters) return p;
      if (filters.search) p.search = filters.search;
      if (filters.type) p.type = filters.type;
      if (filters.pchannel) p.pchannel = filters.pchannel;
      if (filters.pmethod) p.pmethod = filters.pmethod;
      if (typeof filters.is_active !== 'undefined' && filters.is_active !== null && filters.is_active !== '') {
        p.is_active = +filters.is_active;
      }
      if (typeof filters.is_nonbank !== 'undefined' && filters.is_nonbank !== null && filters.is_nonbank !== '') {
        p.is_nonbank = +filters.is_nonbank;
      }
      if (filters.sort) p.sort = filters.sort;
      if (filters.order) p.order = filters.order;
      if (filters.page) p.page = filters.page;
      if (filters.per_page) p.per_page = filters.per_page;
      return p;
    }

    return {
      // -----------------------------
      // Payment Modes CRUD
      // -----------------------------

      // GET /payment-modes (filters: search, type, is_active, is_nonbank, pchannel, pmethod; sort/order; page/per_page)
      list: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/payment-modes', cfg).then(_unwrap);
      },

      // GET /payment-modes/{id}
      show: function (id) {
        return $http.get(BASE + '/payment-modes/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // POST /payment-modes
      create: function (payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(BASE + '/payment-modes', body, _adminHeaders()).then(_unwrap);
      },

      // PUT /payment-modes/{id}
      update: function (id, payload) {
        var body = Object.assign({}, payload || {});
        return $http.put(BASE + '/payment-modes/' + encodeURIComponent(id), body, _adminHeaders()).then(_unwrap);
      },

      // DELETE /payment-modes/{id}
      remove: function (id) {
        return $http.delete(BASE + '/payment-modes/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // POST /payment-modes/{id}/restore
      restore: function (id) {
        return $http.post(BASE + '/payment-modes/' + encodeURIComponent(id) + '/restore', {}, _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
