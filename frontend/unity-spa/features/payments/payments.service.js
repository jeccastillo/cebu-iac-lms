(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('PaymentsService', PaymentsService);

  PaymentsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function PaymentsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

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
      try {
        var roles = null;
        if (state && Array.isArray(state.roles)) roles = state.roles;
        else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
        else if (state && typeof state.roles === 'string') roles = [state.roles];
        if (roles && roles.length) headers['X-User-Roles'] = roles.join(',');
        if (state && state.faculty_id) headers['X-Faculty-ID'] = state.faculty_id;
      } catch (e) {}
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      // GET /payment-modes (filter Maya out in controller/consumer)
      listPaymentModes: function (opts) {
        var params = {};
        if (opts && typeof opts.is_active !== 'undefined') params.is_active = !!opts.is_active;
        return $http.get(BASE + '/payment-modes', { params: params }).then(_unwrap);
      },

      // POST /payments/checkout
      checkout: function (payload) {
        return $http.post(BASE + '/payments/checkout', payload, _headers()).then(_unwrap);
      },

      // POST /payments/cancel (finance/admin only)
      cancel: function (requestId) {
        return $http.post(BASE + '/payments/cancel', { request_id: requestId }, _headers()).then(_unwrap);
      }
    };
  }
})();
