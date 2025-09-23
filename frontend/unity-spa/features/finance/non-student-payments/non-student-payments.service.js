(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('NonStudentPaymentsService', NonStudentPaymentsService);

  NonStudentPaymentsService.$inject = ['$http', 'APP_CONFIG', 'StorageService', 'CashiersService'];
  function NonStudentPaymentsService($http, APP_CONFIG, StorageService, CashiersService) {
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

    function _safeGet(obj, keys, dflt) {
      try {
        var cur = obj;
        for (var i = 0; i < keys.length; i++) {
          if (cur == null) return dflt;
          cur = cur[keys[i]];
        }
        return (typeof cur === 'undefined') ? dflt : cur;
      } catch (e) { return dflt; }
    }

    return {
      // Resolve acting cashier for the current faculty (roles: cashier_admin, finance, admin)
      myCashier: function () {
        return CashiersService.me(); // already attaches X-Faculty-ID and unwraps
      },

      // Create a Non-Student (Payee) payment
      // payload must include:
      //   payee_id, id_number, mode, amount, description, mode_of_payment_id, remarks
      // optional:
      //   method, posted_at, campus_id, invoice_id, invoice_number, or_date, convenience_fee, number
      create: function (cashierId, payload) {
        var id = (cashierId != null) ? encodeURIComponent(cashierId) : '';
        var body = Object.assign({}, payload || {});
        return $http.post(
          BASE + '/cashiers/' + id + '/payee-payments',
          body,
          _adminHeaders()
        ).then(_unwrap);
      },

      // Best-effort Payees search (requires finance_admin or admin; gracefully handles 403 by returning [])
      // opts: { search?: string, page?: number, per_page?: number }
      searchPayees: function (opts) {
        var params = {};
        if (opts && opts.search) params.search = opts.search;
        if (opts && opts.page) params.page = opts.page;
        if (opts && opts.per_page) params.per_page = opts.per_page;
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/payees', cfg)
          .then(_unwrap)
          .then(function (data) {
            // Normalize to array; backend list likely under data.data or similar
            var rows = Array.isArray(data) ? data
              : (Array.isArray(_safeGet(data, ['data'])) ? data.data
                : (Array.isArray(_safeGet(data, ['items'])) ? data.items : []));
            return rows;
          })
          .catch(function (err) {
            // Hide feature when forbidden
            if (err && err.status === 403) return [];
            return Promise.reject(err);
          });
      }
    };
  }

})();
