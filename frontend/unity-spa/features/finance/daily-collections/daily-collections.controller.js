(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('DailyCollectionsController', DailyCollectionsController);

  DailyCollectionsController.$inject = ['$scope', 'DailyCollectionsService', 'CampusService'];
  function DailyCollectionsController($scope, DailyCollectionsService, CampusService) {
    var vm = this;

    // State
    vm.title = 'Daily Collections';
    vm.loading = false;
    vm.error = null;

    // Filters
    vm.filters = {
      date_from: '',
      date_to: '',
      cashier_id: ''
    };

    // Data
    vm.daily = []; // [{ date, total_paid, count_paid, by_method: {m:amt}, by_cashier: [{cashier_id,total}]}]
    vm.meta = {
      count_rows: 0,
      grand_total: 0
    };

    // Actions
    vm.load = load;
    vm.clear = clear;
    vm.exportCsv = exportCsv;

    // Init
    activate();

    function activate() {
      // Default dates to today
      try {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = ('0' + (today.getMonth() + 1)).slice(-2);
        var dd = ('0' + today.getDate()).slice(-2);
        var ymd = yyyy + '-' + mm + '-' + dd;
        vm.filters.date_from = ymd;
        vm.filters.date_to = ymd;
      } catch (e) {}

      // Optionally auto-load today's data
      // load();
    }

    function validate() {
      vm.error = null;
      if (!vm.filters.date_from || !vm.filters.date_to) {
        vm.error = 'Please select both From and To dates.';
        return false;
      }
      if (vm.filters.date_from > vm.filters.date_to) {
        vm.error = 'Start date must be before or equal to end date.';
        return false;
      }
      return true;
    }

    function load() {
      if (!validate()) return;

      vm.loading = true;
      vm.error = null;
      vm.daily = [];
      vm.meta = { count_rows: 0, grand_total: 0 };

      var q = {
        date_from: vm.filters.date_from,
        date_to: vm.filters.date_to
      };
      if (vm.filters.cashier_id != null && ('' + vm.filters.cashier_id).trim() !== '') {
        q.cashier_id = vm.filters.cashier_id;
      }

      DailyCollectionsService
        .list(q)
        .then(function (resp) {
          // Backend returns:
          // { date_from, date_to, meta: { count_rows, grand_total }, daily: [...] }
          if (resp && resp.daily && Array.isArray(resp.daily)) {
            vm.daily = resp.daily;
            vm.meta = resp.meta || vm.meta;
          } else if (Array.isArray(resp)) {
            vm.daily = resp;
          } else if (resp && resp.data && Array.isArray(resp.data)) {
            // just in case a wrapper is returned
            vm.daily = resp.data;
            if (resp.meta) vm.meta = resp.meta;
          }
          // Round meta.grand_total for UI
          try { vm.meta.grand_total = toFixedNum(vm.meta.grand_total); } catch (e) {}
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load daily collections.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function clear() {
      vm.error = null;
      vm.daily = [];
      vm.meta = { count_rows: 0, grand_total: 0 };
      vm.filters.cashier_id = '';
      vm.filters.date_from = '';
      vm.filters.date_to = '';
    }

    function exportCsv() {
      try {
        var rows = vm.daily || [];
        if (!rows.length) return;

        var headers = [
          'No',
          'Date',
          'Total Paid',
          'Count Paid',
          'By Method',
          'By Cashier'
        ];

        var lines = [];
        lines.push(headers.join(','));

        for (var i = 0; i < rows.length; i++) {
          var r = rows[i] || {};
          var byMethodStr = serializeByMethod(r.by_method);
          var byCashierStr = serializeByCashier(r.by_cashier);

          var line = [
            (i + 1),
            csv(r.date),
            toFixedStr(r.total_paid),
            (r.count_paid != null ? String(r.count_paid) : '0'),
            csv(byMethodStr),
            csv(byCashierStr)
          ];
          lines.push(line.join(','));
        }

        // Footer totals
        lines.push([
          '', 'Totals', toFixedStr(vm.meta && vm.meta.grand_total), vm.meta && vm.meta.count_rows ? String(vm.meta.count_rows) : '', '', ''
        ].join(','));

        var blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;

        var fn = 'daily-collections-' + (vm.filters.date_from || 'from') + '_to_' + (vm.filters.date_to || 'to') + '.csv';
        a.download = fn;
        document.body.appendChild(a);
        a.click();
        setTimeout(function () {
          document.body.removeChild(a);
          window.URL.revokeObjectURL(url);
        }, 0);
      } catch (e) {
        vm.error = 'Failed to export CSV.';
      }
    }

    // ---------------- Utils ----------------

    function csv(v) {
      if (v === null || v === undefined) return '';
      var s = String(v);
      if (s.indexOf(',') >= 0 || s.indexOf('"') >= 0 || s.indexOf('\n') >= 0) {
        return '"' + s.replace(/"/g, '""') + '"';
      }
      return s;
    }

    function num(v) {
      var n = parseFloat(v);
      return isNaN(n) ? 0 : n;
    }

    function toFixedStr(v) {
      var n = num(v);
      return n.toFixed(2);
    }

    function toFixedNum(v) {
      return parseFloat(toFixedStr(v));
    }

    function serializeByMethod(obj) {
      // obj: { methodName: amount, ... }
      if (!obj || typeof obj !== 'object') return '';
      try {
        var parts = [];
        Object.keys(obj).forEach(function (k) {
          var amt = toFixedStr(obj[k]);
          var label = (k && ('' + k).trim() !== '') ? k : 'Unknown';
          parts.push(label + ': ' + amt);
        });
        return parts.join('; ');
      } catch (e) {
        return '';
      }
    }

    function serializeByCashier(arr) {
      // arr: [{ cashier_id, total }, ...]
      if (!arr || !Array.isArray(arr)) return '';
      try {
        var parts = [];
        for (var i = 0; i < arr.length; i++) {
          var r = arr[i] || {};
          var id = (r.cashier_id != null) ? String(r.cashier_id) : 'N/A';
          var amt = toFixedStr(r.total);
          parts.push('ID ' + id + ': ' + amt);
        }
        return parts.join('; ');
      } catch (e) {
        return '';
      }
    }
  }

})();
