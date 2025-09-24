(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('OrReportsController', OrReportsController);

  OrReportsController.$inject = ['$scope', 'OrReportsService'];
  function OrReportsController($scope, OrReportsService) {
    var vm = this;

    // State
    vm.title = 'OFFICIAL RECEIPT REPORT';
    vm.loading = false;
    vm.error = null;

    // Filters
    vm.filters = {
      date_from: '',
      date_to: ''
    };

    // Data
    vm.rows = [];
    vm.total_received = 0;

    // Actions
    vm.load = load;
    vm.clear = clear;
    vm.exportCsv = exportCsv;

    // Init
    activate();

    function activate() {
      try {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = ('0' + (today.getMonth() + 1)).slice(-2);
        var dd = ('0' + today.getDate()).slice(-2);
        var ymd = yyyy + '-' + mm + '-' + dd;
        vm.filters.date_from = ymd;
        vm.filters.date_to = ymd;
      } catch (e) {}
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
      vm.rows = [];
      vm.total_received = 0;

      var q = {
        date_from: vm.filters.date_from,
        date_to: vm.filters.date_to
      };

      OrReportsService
        .list(q)
        .then(function (resp) {
          var items = [];
          if (resp && Array.isArray(resp.data)) {
            items = resp.data;
          } else if (Array.isArray(resp)) {
            items = resp;
          } else if (resp && resp.success && Array.isArray(resp.data)) {
            items = resp.data;
          }

          vm.rows = items;

          // Totals
          vm.total_received = 0;
          vm.rows.forEach(function (r) {
            vm.total_received += toNum(r.payment_received);
          });
          vm.total_received = round2(vm.total_received);
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load OR reports.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function clear() {
      vm.error = null;
      vm.rows = [];
      vm.total_received = 0;
      vm.filters.date_from = '';
      vm.filters.date_to = '';
    }

    function exportCsv() {
      try {
        var rows = vm.rows || [];
        if (!rows.length) return;

        var headers = [
          'No',
          'OR Date',
          'OR Number',
          'Invoice Number',
          'Student Number',
          'Payee Name',
          'Payment For',
          'Particulars',
          'Payment Received'
        ];
        var lines = [];
        lines.push(headers.join(','));

        for (var i = 0; i < rows.length; i++) {
          var r = rows[i] || {};
          var line = [
            (i + 1),
            csv(r.or_date),
            csv(r.or_number),
            csv(r.invoice_number),
            csv(r.student_number),
            csv(r.payee_name),
            csv(r.payment_for),
            csv(r.particulars),
            toFixedStr(r.payment_received)
          ];
          lines.push(line.join(','));
        }

        // Footer total
        lines.push([
          '', '', '', '', '', '', '', 'Total',
          toFixedStr(vm.total_received)
        ].join(','));

        var blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;

        var fn = 'or-reports-' + (vm.filters.date_from || 'from') + '_to_' + (vm.filters.date_to || 'to') + '.csv';
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

    function toNum(v) {
      var n = parseFloat(v);
      return isNaN(n) ? 0 : n;
    }

    function round2(v) {
      var n = toNum(v);
      return parseFloat(n.toFixed(2));
    }

    function toFixedStr(v) {
      return round2(v).toFixed(2);
    }
  }

})();
