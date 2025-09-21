(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('NonStudentPaymentsController', NonStudentPaymentsController);

  NonStudentPaymentsController.$inject = [
    '$scope',
    '$q',
    'NonStudentPaymentsService',
    'PaymentModesService',
    'ToastService'
  ];
  function NonStudentPaymentsController($scope, $q, NonStudentPaymentsService, PaymentModesService, ToastService) {
    var vm = this;

    // UI State
    vm.loading = false;
    vm.submitting = false;
    vm.error = null;
    vm.success = null;

    // Data
    vm.myCashier = null;
    vm.paymentModes = [];
    vm.modes = [
      { key: 'or', label: 'Official Receipt (OR)' },
      { key: 'invoice', label: 'Invoice' },
      { key: 'none', label: 'No Number (encode only)' }
    ];

    // Payee search (optional, requires finance_admin or admin; otherwise manual entry)
    vm.enablePayeeSearch = true; // if backend forbids (403), we fallback automatically
    vm.payeeQuery = '';
    vm.payeeResults = [];
    vm.payeeLoading = false;
    vm.showPayeeResults = false;

    // Form model
    vm.form = resetForm();

    // Public methods
    vm.init = init;
    vm.searchPayees = searchPayees;
    vm.selectPayee = selectPayee;
    vm.clearPayee = clearPayee;

    vm.canSubmit = canSubmit;
    vm.submit = submit;
    vm.reset = softReset;

    vm.numberDisabled = numberDisabled;
    vm.onModeChange = onModeChange;

    vm.amountInputAttrs = amountInputAttrs;
    vm.clampAmount = clampAmount;

    activate();

    function activate() {
      init();
    }

    function init() {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      // Load myCashier + payment modes in parallel
      Promise.allSettled([
        NonStudentPaymentsService.myCashier(),
        PaymentModesService.list({ is_active: 1, per_page: 1000 })
      ]).then(function (results) {
        // myCashier
        try {
          var r0 = results[0];
          if (r0.status === 'fulfilled') {
            var d0 = unwrap(r0.value);
            // Accept structures: {success:true, data:{...}} or direct object
            vm.myCashier = (d0 && d0.data) ? d0.data : d0;
          } else {
            vm.myCashier = null;
          }
        } catch (e) {
          vm.myCashier = null;
        }

        // payment modes
        try {
          var r1 = results[1];
          if (r1.status === 'fulfilled') {
            var d1 = unwrap(r1.value);
            // Normalize list from various shapes
            var rows = Array.isArray(d1) ? d1
              : (Array.isArray(get(d1, ['data'])) ? d1.data
                : (Array.isArray(get(d1, ['items'])) ? d1.items : []));
            vm.paymentModes = (rows || []).map(function (r) {
              // Accept both {id, name} and other variations
              return {
                id: (r.id != null ? r.id : r.intID),
                name: (r.name || r.label || r.pmethod || ('Mode #' + (r.id || r.intID)))
              };
            }).filter(function (x) { return x && x.id; });
          } else {
            vm.paymentModes = [];
          }
        } catch (e) {
          vm.paymentModes = [];
        }

        // Default first payment mode if any
        if (!vm.form.mode_of_payment_id && vm.paymentModes.length > 0) {
          vm.form.mode_of_payment_id = vm.paymentModes[0].id;
        }
      }).catch(function (err) {
        vm.error = formatError(err);
      }).finally(function () {
        vm.loading = false;
      });
    }

    // -----------------------------
    // Payee Search (best-effort)
    // -----------------------------
    function searchPayees() {
      try {
        vm.payeeResults = [];
        vm.showPayeeResults = false;
        if (!vm.enablePayeeSearch) return;
        var q = (vm.payeeQuery || '').trim();
        if (q.length < 2) return;

        vm.payeeLoading = true;
        NonStudentPaymentsService.searchPayees({ search: q, per_page: 10 }).then(function (rows) {
          // If [] returned because of 403, disable search UI
          if (Array.isArray(rows) && rows.length === 0 && q.length >= 2) {
            // Could be either "no results" or forbidden; keep enabled but show no results
          }
          vm.payeeResults = (rows || []).map(function (r) {
            return {
              id: r.id || r.intID,
              id_number: (r.id_number || r.ID || r.idno || ''),
              name: buildPayeeName(r)
            };
          }).filter(function (x) { return x && x.id; });
          vm.showPayeeResults = true;
        }).catch(function (err) {
          // If forbidden, permanently disable search and show hint
          if (err && err.status === 403) {
            vm.enablePayeeSearch = false;
            vm.payeeResults = [];
            vm.showPayeeResults = false;
          } else {
            // Other errors: keep UI, but no results
            vm.payeeResults = [];
            vm.showPayeeResults = true;
          }
        }).finally(function () {
          vm.payeeLoading = false;
        });
      } catch (e) {
        vm.payeeLoading = false;
      }
    }

    function selectPayee(item) {
      if (!item) return;
      vm.form.payee_id = item.id;
      vm.form.id_number = item.id_number || '';
      vm.form._payee_name = item.name || '';
      vm.payeeQuery = item.name || ('' + item.id);
      vm.payeeResults = [];
      vm.showPayeeResults = false;
    }

    function clearPayee() {
      vm.form.payee_id = null;
      vm.form.id_number = '';
      vm.form._payee_name = '';
      vm.payeeQuery = '';
      vm.payeeResults = [];
      vm.showPayeeResults = false;
    }

    // -----------------------------
    // Submit
    // -----------------------------
    function canSubmit() {
      if (vm.submitting || vm.loading) return false;
      if (!vm.myCashier || !vm.myCashier.intID && !vm.myCashier.id) return false;
      if (!vm.form.payee_id) return false;
      if (!vm.form.id_number || !vm.form.id_number.trim()) return false;
      if (!vm.form.mode || ['or', 'invoice', 'none'].indexOf(vm.form.mode) < 0) return false;
      if (!vm.form.amount || isNaN(vm.form.amount) || parseFloat(vm.form.amount) <= 0) return false;
      if (!vm.form.description || !vm.form.description.trim()) return false;
      if (!vm.form.remarks || !vm.form.remarks.trim()) return false;
      if (!vm.form.mode_of_payment_id) return false;
      return true;
    }

    function submit() {
      vm.error = null;
      vm.success = null;
      if (!canSubmit()) return;

      vm.submitting = true;

      var payload = {
        payee_id: toInt(vm.form.payee_id),
        id_number: (vm.form.id_number || '').trim(),
        mode: vm.form.mode,
        amount: toMoney(vm.form.amount),
        description: (vm.form.description || '').trim(),
        mode_of_payment_id: toInt(vm.form.mode_of_payment_id),
        remarks: (vm.form.remarks || '').trim()
      };

      // Optional fields (only include if present)
      if (exists(vm.form.method)) payload.method = (vm.form.method || '').trim();
      if (exists(vm.form.posted_at)) payload.posted_at = vm.form.posted_at;
      if (exists(vm.form.or_date)) payload.or_date = vm.form.or_date;
      if (exists(vm.form.convenience_fee)) payload.convenience_fee = toMoney(vm.form.convenience_fee);
      if (exists(vm.form.campus_id)) payload.campus_id = toInt(vm.form.campus_id);
      if (exists(vm.form.invoice_id)) payload.invoice_id = toInt(vm.form.invoice_id);
      if (exists(vm.form.invoice_number)) payload.invoice_number = toInt(vm.form.invoice_number);
      if (exists(vm.form.number) && vm.form.mode !== 'none') payload.number = toInt(vm.form.number);

      var cashierId = vm.myCashier.intID || vm.myCashier.id;

      NonStudentPaymentsService.create(cashierId, payload).then(function (res) {
        var d = unwrap(res);
        var data = d && d.data ? d.data : d;
        var numberInfo = (data && typeof data.number_used !== 'undefined') ? (' Number: ' + data.number_used) : '';
        vm.success = 'Payment created. ID: ' + (data && data.id ? data.id : 'N/A') + (numberInfo || '');
        try { ToastService.success(vm.success); } catch (e) {}
        // Keep payee and mode_of_payment; reset transactional fields
        vm.form.amount = null;
        vm.form.description = '';
        vm.form.remarks = '';
        vm.form.method = '';
        vm.form.number = null;
        vm.form.invoice_id = null;
        vm.form.invoice_number = null;
        vm.form.convenience_fee = null;
        vm.form.posted_at = '';
        vm.form.or_date = '';
      }).catch(function (err) {
        vm.error = formatError(err);
        try { ToastService.error(vm.error); } catch (e) {}
      }).finally(function () {
        vm.submitting = false;
      });
    }

    function softReset() {
      var keep = {
        payee_id: vm.form.payee_id,
        id_number: vm.form.id_number,
        _payee_name: vm.form._payee_name,
        mode_of_payment_id: vm.form.mode_of_payment_id,
        mode: vm.form.mode
      };
      vm.form = resetForm();
      Object.assign(vm.form, keep);
      vm.payeeQuery = keep._payee_name || '';
      vm.payeeResults = [];
      vm.showPayeeResults = false;
      vm.error = null;
      vm.success = null;
    }

    function resetForm() {
      return {
        payee_id: null,
        id_number: '',
        _payee_name: '',
        mode: 'or',
        number: null,
        amount: null,
        description: '',
        mode_of_payment_id: null,
        method: '',
        remarks: '',
        posted_at: '',
        or_date: '',
        convenience_fee: null,
        campus_id: null,
        invoice_id: null,
        invoice_number: null
      };
    }

    // -----------------------------
    // Helpers
    // -----------------------------
    function numberDisabled() {
      return vm.form.mode === 'none';
    }

    function onModeChange() {
      if (vm.form.mode === 'none') {
        vm.form.number = null;
      }
    }

    function amountInputAttrs() {
      return { step: '0.01', min: '0.01' };
    }

    function clampAmount() {
      try {
        if (vm.form.amount == null || vm.form.amount === '') return;
        var v = parseFloat(vm.form.amount);
        if (isNaN(v) || v <= 0) {
          vm.form.amount = null;
          return;
        }
        // round to 2 decimals
        vm.form.amount = Math.round(v * 100) / 100;
      } catch (e) {}
    }

    function formatError(err) {
      try {
        // Laravel validation shape: { message, errors: { field: [..] } }
        if (err && err.data) err = err.data;
        var parts = [];
        if (err && err.message) parts.push(err.message);
        var errs = (err && err.errors) ? err.errors : null;
        if (errs) {
          Object.keys(errs).forEach(function (k) {
            var arr = errs[k];
            if (Array.isArray(arr)) {
              arr.forEach(function (m) { parts.push(k + ': ' + m); });
            }
          });
        }
        if (!parts.length && typeof err === 'string') parts.push(err);
        if (!parts.length) return 'Request failed';
        return parts.join('; ');
      } catch (e) {
        return 'Request failed';
      }
    }

    function unwrap(resp) {
      // Unity services often return resp or resp.data
      if (resp && resp.data) return resp.data;
      return resp;
    }

    function get(obj, pathArr, dflt) {
      try {
        var cur = obj;
        for (var i = 0; i < pathArr.length; i++) {
          if (cur == null) return dflt;
          cur = cur[pathArr[i]];
        }
        return (typeof cur === 'undefined') ? dflt : cur;
      } catch (e) { return dflt; }
    }

    function toInt(v) {
      var n = parseInt(v, 10);
      return isNaN(n) ? null : n;
    }

    function toMoney(v) {
      var n = parseFloat(v);
      if (isNaN(n)) return null;
      return Math.round(n * 100) / 100;
    }

    function exists(v) {
      return !(v === null || typeof v === 'undefined' || v === '');
    }

    function buildPayeeName(r) {
      var fn = r.firstname || r.first_name || '';
      var mn = r.middlename || r.middle_name || '';
      var ln = r.lastname || r.last_name || '';
      var nm = (fn + ' ' + (mn ? (mn + ' ') : '') + ln).trim();
      if (!nm) {
        nm = r.name || r.payee_name || '';
      }
      var idn = r.id_number || r.ID || r.idno || '';
      if (idn) nm += ' (' + idn + ')';
      return nm || ('Payee #' + (r.id || r.intID || '?'));
    }
  }
})();
