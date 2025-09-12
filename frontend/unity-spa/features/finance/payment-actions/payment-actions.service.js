(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('FinancePaymentActionsService', FinancePaymentActionsService);

  FinancePaymentActionsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function FinancePaymentActionsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

    function _getLoginState() {
      try {
        return StorageService.getJSON('loginState') || null;
      } catch (e) {
        return null;
      }
    }

    function _headers(extra) {
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
      if (filters.or_number) p.or_number = filters.or_number;
      if (filters.invoice_number) p.invoice_number = filters.invoice_number;
      if (filters.student_number) p.student_number = filters.student_number;
      if (filters.student_id) p.student_id = filters.student_id;
      if (filters.syid) p.syid = filters.syid;
      if (filters.status) p.status = filters.status;
      if (filters.page) p.page = filters.page;
      if (filters.per_page) p.per_page = filters.per_page;
      return p;
    }

    return {
      // GET /finance/payment-actions/search
      search: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _headers();
        cfg.params = params;
        return $http.get(BASE + '/finance/payment-actions/search', cfg).then(_unwrap);
      },

      // POST /finance/payment-actions/{id}/void
      void: function (id, payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(BASE + '/finance/payment-actions/' + encodeURIComponent(id) + '/void', body, _headers()).then(_unwrap);
      },

      // DELETE /finance/payment-actions/{id}/retract
      retract: function (id) {
        return $http.delete(BASE + '/finance/payment-actions/' + encodeURIComponent(id) + '/retract', _headers()).then(_unwrap);
      }
    };
  }

})();
