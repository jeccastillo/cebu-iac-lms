(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('AdminInvoicesService', AdminInvoicesService);

  AdminInvoicesService.$inject = ['$http', '$window', 'APP_CONFIG', 'StorageService'];
  function AdminInvoicesService($http, $window, APP_CONFIG, StorageService) {
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
        headers['X-Faculty-ID'] = state.faculty_id; // Required by role middleware
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    function _paramsFromFilters(filters) {
      var p = {};
      if (!filters) return p;
      // Supported filters by API:
      // student_id, student_number, syid, type, status, campus_id, registration_id, term (alias for syid)
      if (filters.student_id) p.student_id = filters.student_id;
      if (filters.student_number) p.student_number = filters.student_number;
      if (filters.syid) p.syid = filters.syid;
      if (filters.term) p.term = filters.term;
      if (filters.type) p.type = filters.type;
      if (filters.status) p.status = filters.status;
      if (filters.campus_id) p.campus_id = filters.campus_id;
      if (filters.registration_id) p.registration_id = filters.registration_id;
      return p;
    }

    function _jsonOrNull(v) {
      if (v === null || v === undefined || v === '') return null;
      if (typeof v === 'object') return v;
      try {
        return JSON.parse(v);
      } catch (e) {
        return null;
      }
    }

    return {
      // Read endpoints (admin/finance)
      list: function (filters) {
        var params = _paramsFromFilters(filters);
        var cfg = _adminHeaders();
        cfg.params = params;
        return $http.get(BASE + '/finance/invoices', cfg).then(_unwrap);
      },

      show: function (id) {
        return $http.get(BASE + '/finance/invoices/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // Admin-only CRUD
      create: function (payload) {
        // Payload for POST /finance/invoices follows InvoiceGenerateRequest
        // Requires: type, student_id, term (syid alias)
        var body = Object.assign({}, payload || {});
        return $http.post(BASE + '/finance/invoices', body, _adminHeaders()).then(_unwrap);
      },

      update: function (id, payload) {
        // Payload for PUT /finance/invoices/{id} follows InvoiceUpdateRequest
        // Fields: status, posted_at, due_at, remarks, campus_id, cashier_id, invoice_number, amount, payload (array/object)
        var body = Object.assign({}, payload || {});
        // If payload.payload is string JSON, parse to object (backend expects array/object JSON to encode)
        if (body.payload && typeof body.payload === 'string') {
          var parsed = _jsonOrNull(body.payload);
          if (parsed !== null) body.payload = parsed;
        }
        return $http.put(BASE + '/finance/invoices/' + encodeURIComponent(id), body, _adminHeaders()).then(_unwrap);
      },

      remove: function (id) {
        return $http.delete(BASE + '/finance/invoices/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // PDF streaming (needs header; use XHR to fetch and open blob)
      pdf: function (id) {
        var cfg = _adminHeaders();
        cfg.responseType = 'arraybuffer';
        cfg.headers = cfg.headers || {};
        cfg.headers['Accept'] = 'application/pdf';
        return $http.get(BASE + '/finance/invoices/' + encodeURIComponent(id) + '/pdf', cfg).then(function (resp) {
          var data = resp.data;
          var blob = new Blob([data], { type: 'application/pdf' });
          var url = URL.createObjectURL(blob);
          // Open in new tab/window
          $window.open(url, '_blank');
          // Best-effort revoke after some delay
          setTimeout(function () { URL.revokeObjectURL(url); }, 30000);
          return true;
        });
      }
    };
  }

})();
