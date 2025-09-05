(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('AdminPaymentDetailsService', AdminPaymentDetailsService);

  AdminPaymentDetailsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function AdminPaymentDetailsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

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
      if (filters.q) p.q = filters.q;
      if (filters.student_number) p.student_number = filters.student_number;
      if (filters.student_id) p.student_id = filters.student_id;
      if (filters.syid) p.syid = filters.syid;
      if (filters.status) p.status = filters.status;
      if (filters.mode) p.mode = filters.mode;
      if (filters.or_number) p.or_number = filters.or_number;
      if (filters.invoice_number) p.invoice_number = filters.invoice_number;
      if (filters.date_from) p.date_from = filters.date_from;
      if (filters.date_to) p.date_to = filters.date_to;
      if (filters.page) p.page = filters.page;
      if (filters.per_page) p.per_page = filters.per_page;
      return p;
    }

    return {
      // GET /finance/payment-details/admin (search + pagination)
      search: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/finance/payment-details/admin', cfg).then(_unwrap);
      },

      // GET /finance/payment-details/{id}
      show: function (id) {
        return $http.get(BASE + '/finance/payment-details/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // PATCH /finance/payment-details/{id}
      update: function (id, payload) {
        var body = Object.assign({}, payload || {});
        return $http.patch(BASE + '/finance/payment-details/' + encodeURIComponent(id), body, _adminHeaders()).then(_unwrap);
      },

      // DELETE /finance/payment-details/{id}
      remove: function (id) {
        return $http.delete(BASE + '/finance/payment-details/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
