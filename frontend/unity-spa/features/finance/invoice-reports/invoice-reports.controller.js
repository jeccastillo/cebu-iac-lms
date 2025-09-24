(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('InvoiceReportsController', InvoiceReportsController);

  InvoiceReportsController.$inject = ['$scope', 'InvoiceReportsService', 'CampusService'];
  function InvoiceReportsController($scope, InvoiceReportsService, CampusService) {
    var vm = this;

    // State
    vm.title = 'Invoice Reports';
    vm.loading = false;
    vm.error = null;

    // Filters
    vm.filters = {
      date_from: '',
      date_to: '',
      type: '',
      status: ''
    };

    // Data
    vm.rows = [];
    vm.totals = {
      vatable_amount: 0,
      vat_exempt: 0,
      zero_rated: 0,
      total_sales: 0,
      vat: 0,
      ewt_amount: 0,
      net_amount_due: 0
    };

    // Actions
    vm.load = load;
    vm.clear = clear;
    vm.exportCsv = exportCsv;

    // Init
    activate();

    function activate() {
      // Try to default date range to today (optional: leave blank to force explicit selection)
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
      resetTotals();

      var q = {
        date_from: vm.filters.date_from,
        date_to: vm.filters.date_to
      };
      if (vm.filters.type) q.type = vm.filters.type;
      if (vm.filters.status) q.status = vm.filters.status;

      InvoiceReportsService
        .list(q)
        .then(function (resp) {
          // The service returns resp.data (wrapper) by default: { success, data, meta }
          // But be tolerant if it ever returns the raw array.
          var items = [];
          if (resp && Array.isArray(resp.data)) {
            items = resp.data;
          } else if (Array.isArray(resp)) {
            items = resp;
          } else if (resp && resp.success && Array.isArray(resp.data)) {
            items = resp.data;
          }

          vm.rows = items;

          // Compute totals
          vm.rows.forEach(function (r) {
            vm.totals.vatable_amount += num(r.vatable_amount);
            vm.totals.vat_exempt += num(r.vat_exempt);
            vm.totals.zero_rated += num(r.zero_rated);
            vm.totals.total_sales += num(r.total_sales);
            vm.totals.vat += num(r.vat);
            vm.totals.ewt_amount += num(r.ewt_amount);
            vm.totals.net_amount_due += num(r.net_amount_due);
          });

          roundTotals();
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load invoice reports.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function clear() {
      vm.error = null;
      vm.rows = [];
      resetTotals();
      vm.filters.type = '';
      vm.filters.status = '';
      vm.filters.date_from = '';
      vm.filters.date_to = '';
    }

    function exportCsv() {
      try {
        var rows = vm.rows || [];
        if (!rows.length) return;

        var headers = [
          'No',
          'Invoice Date',
          'Invoice Number',
          'Student Number',
          'Payee Name',
          'Payment For',
          'Particulars',
          'Payment Type',
          'MOP',
          'Vatable Amount',
          'Vat Exempt',
          'Zero Rated',
          'Total Sales',
          'VAT',
          'EWT Rate',
          'EWT Amount',
          'Net Amount Due'
        ];

        var lines = [];
        lines.push(headers.join(','));

        for (var i = 0; i < rows.length; i++) {
          var r = rows[i] || {};
          var line = [
            (i + 1),
            csv(r.invoice_date),
            csv(r.invoice_number),
            csv(r.student_number),
            csv(r.payee_name),
            csv(r.payment_for),
            csv(r.particulars),
            csv(r.payment_type),
            csv(r.mop),
            toFixedStr(r.vatable_amount),
            toFixedStr(r.vat_exempt),
            toFixedStr(r.zero_rated),
            toFixedStr(r.total_sales),
            toFixedStr(r.vat),
            num(r.ewt_rate) != null ? String(r.ewt_rate) : '',
            toFixedStr(r.ewt_amount),
            toFixedStr(r.net_amount_due)
          ];
          lines.push(line.join(','));
        }

        // Footer totals
        lines.push([
          '', '', '', '', '', '', '', '', 'Totals',
          toFixedStr(vm.totals.vatable_amount),
          toFixedStr(vm.totals.vat_exempt),
          toFixedStr(vm.totals.zero_rated),
          toFixedStr(vm.totals.total_sales),
          toFixedStr(vm.totals.vat),
          '', // ewt rate aggregate not meaningful
          toFixedStr(vm.totals.ewt_amount),
          toFixedStr(vm.totals.net_amount_due)
        ].join(','));

        var blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;

        var fn = 'invoice-reports-' + (vm.filters.date_from || 'from') + '_to_' + (vm.filters.date_to || 'to') + '.csv';
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

    function resetTotals() {
      vm.totals = {
        vatable_amount: 0,
        vat_exempt: 0,
        zero_rated: 0,
        total_sales: 0,
        vat: 0,
        ewt_amount: 0,
        net_amount_due: 0
      };
    }

    function roundTotals() {
      Object.keys(vm.totals).forEach(function (k) {
        vm.totals[k] = num(vm.totals[k]);
        vm.totals[k] = parseFloat(vm.totals[k].toFixed(2));
      });
    }
  }

})();
