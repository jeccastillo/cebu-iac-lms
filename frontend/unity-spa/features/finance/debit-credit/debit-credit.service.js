(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('DebitCreditService', DebitCreditService);

  DebitCreditService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function DebitCreditService($http, APP_CONFIG, StorageService) {
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
      // POST /finance/payment-details/debit
      postDebit: function (payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(BASE + '/finance/payment-details/debit', body, _adminHeaders())
          .then(_unwrap);
      },

      // POST /finance/payment-details/credit
      postCredit: function (payload) {
        var body = Object.assign({ enforce_invoice_remaining: true }, payload || {});
        return $http.post(BASE + '/finance/payment-details/credit', body, _adminHeaders())
          .then(_unwrap);
      },

      // GET /finance/invoices?student_id=&term=
      listInvoices: function (opts) {
        var params = {};
        if (opts && opts.student_id != null) params.student_id = opts.student_id;
        if (opts && opts.term != null) params.term = opts.term;
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/finance/invoices', cfg).then(_unwrap);
      },

      // GET /finance/payment-details?student_id=&term=
      paymentDetails: function (opts) {
        var params = {};
        if (opts && opts.student_id != null) params.student_id = opts.student_id;
        if (opts && opts.term != null) params.term = opts.term;
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/finance/payment-details', cfg).then(_unwrap);
      }
    };
  }

})();
