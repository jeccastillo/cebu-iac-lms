(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('StudentBillingService', StudentBillingService);

  StudentBillingService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function StudentBillingService($http, APP_CONFIG, StorageService) {
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
      // Include X-Faculty-ID when available (parity with other finance/admin endpoints)
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
      if (filters.student_id !== null && filters.student_id !== undefined && filters.student_id !== '') {
        var sid = parseInt(filters.student_id, 10);
        if (!isNaN(sid)) p.student_id = sid;
      }
      if (filters.term !== null && filters.term !== undefined && filters.term !== '') {
        var term = parseInt(filters.term, 10);
        if (!isNaN(term)) p.term = term;
      }
      return p;
    }

    return {
      // GET /finance/student-billing?student_id&amp;term
      list: function (filters) {
        var cfg = _headers();
        cfg.params = _paramsFromFilters(filters);
        return $http
          .get(BASE + '/finance/student-billing', cfg)
          .then(_unwrap);
      },

      // GET /finance/student-billing/{id}
      show: function (id) {
        return $http
          .get(BASE + '/finance/student-billing/' + encodeURIComponent(id), _headers())
          .then(_unwrap);
      },

      // POST /finance/student-billing
      // payload: { student_id, term, description, amount, posted_at?, remarks?, generate_invoice? }
      create: function (payload) {
        var body = Object.assign({}, payload || {});
        if (body.student_id !== null && body.student_id !== undefined && body.student_id !== '') {
          body.student_id = parseInt(body.student_id, 10);
        }
        if (body.term !== null && body.term !== undefined && body.term !== '') {
          body.term = parseInt(body.term, 10);
        }
        if (typeof body.amount === 'string') {
          var amt = parseFloat(body.amount);
          if (!isNaN(amt)) body.amount = amt;
        }
        return $http
          .post(BASE + '/finance/student-billing', body, _headers())
          .then(_unwrap);
      },

      // PUT /finance/student-billing/{id}
      // payload: { description?, amount?, posted_at?, remarks? }
      update: function (id, payload) {
        var body = Object.assign({}, payload || {});
        return $http
          .put(BASE + '/finance/student-billing/' + encodeURIComponent(id), body, _headers())
          .then(_unwrap);
      },

      // DELETE /finance/student-billing/{id}
      remove: function (id) {
        return $http
          .delete(BASE + '/finance/student-billing/' + encodeURIComponent(id), _headers())
          .then(_unwrap);
      },

      // GET /finance/student-billing/missing-invoices
      // filters: { student_id:int, term:int }
      missingInvoices: function (filters) {
        var cfg = _headers();
        cfg.params = {};
        if (filters && filters.student_id != null && filters.student_id !== '') {
          var sid = parseInt(filters.student_id, 10);
          if (!isNaN(sid)) cfg.params.student_id = sid;
        }
        if (filters && filters.term != null && filters.term !== '') {
          var term = parseInt(filters.term, 10);
          if (!isNaN(term)) cfg.params.term = term;
        }
        return $http
          .get(BASE + '/finance/student-billing/missing-invoices', cfg)
          .then(_unwrap);
      },

      // POST /finance/student-billing/{id}/generate-invoice
      // body: { posted_at?: string, remarks?: string }
      generateInvoiceForBilling: function (id, body) {
        var payload = Object.assign({}, body || {});
        return $http
          .post(BASE + '/finance/student-billing/' + encodeURIComponent(id) + '/generate-invoice', payload, _headers())
          .then(_unwrap);
      }
    };
  }

})();
