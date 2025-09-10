(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('FinanceLedgerService', FinanceLedgerService);

  FinanceLedgerService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function FinanceLedgerService($http, APP_CONFIG, StorageService) {
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
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      // GET /finance/student-ledger
      getLedger: function (params) {
        var cfg = _headers();
        cfg.params = {
          term: params && params.term !== undefined && params.term !== null ? params.term : 'all',
          sort: params && params.sort ? params.sort : 'asc'
        };
        if (params && params.student_number) cfg.params.student_number = params.student_number;
        if (params && params.student_id) cfg.params.student_id = params.student_id;

        return $http.get(BASE + '/finance/student-ledger', cfg).then(_unwrap);
      },

      // POST /finance/ledger/excess/apply
      applyExcess: function (payload) {
        var cfg = _headers({ 'Content-Type': 'application/json' });
        return $http.post(BASE + '/finance/ledger/excess/apply', payload, cfg).then(_unwrap);
      },

      // POST /finance/ledger/excess/revert
      revertExcess: function (payload) {
        var cfg = _headers({ 'Content-Type': 'application/json' });
        return $http.post(BASE + '/finance/ledger/excess/revert', payload, cfg).then(_unwrap);
      },

      // Utility to format rows into CSV string
      toCsv: function (rows) {
        var header = [
          'Transaction Date',
          'OR Number',
          'Invoice Number',
          'Assessment',
          'Payment',
          'Cashier Name',
          'Running Balance',
          'SY Label',
          'Type',
          'Remarks'
        ];
        var lines = [header.join(',')];

        (rows || []).forEach(function (r) {
          var cols = [
            quoteCsv(r.posted_at || ''),
            quoteCsv(r.or_no !== null && r.or_no !== undefined ? r.or_no : ''),
            quoteCsv(r.invoice_number !== null && r.invoice_number !== undefined ? r.invoice_number : ''),
            formatNumber(r.assessment),
            formatNumber(r.payment),
            quoteCsv(r.cashier_name || ''),
            formatNumber(r.running_balance),
            quoteCsv(r.sy_label || ''),
            quoteCsv(r.ref_type || ''),
            quoteCsv(r.remarks || '')
          ];
          lines.push(cols.join(','));
        });

        return lines.join('\n');

        function quoteCsv(val) {
          var s = (val === null || val === undefined) ? '' : String(val);
          if (/[",\n]/.test(s)) {
            return '"' + s.replace(/"/g, '""') + '"';
          }
          return s;
        }

        function formatNumber(n) {
          if (n === null || n === undefined) return '';
          var num = Number(n);
          if (!isFinite(num)) return '';
          return num.toFixed(2);
        }
      }
    };
  }

})();
