(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('StudentFinancesController', StudentFinancesController);

  StudentFinancesController.$inject = ['$scope', '$q', '$http', '$window', 'APP_CONFIG', 'StorageService', 'TermService', 'StudentFinancesService', 'ToastService'];
  function StudentFinancesController($scope, $q, $http, $window, APP_CONFIG, StorageService, TermService, StudentFinancesService, ToastService) {
    var vm = this;

    // Page meta
    vm.title = 'My Finances';

    // Auth state
    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      // Let route guard handle redirects in run.js; we still defensively no-op
    }

    // API refs
    vm.api = StudentFinancesService.api;

    // UI state
    vm.loading = {
      init: false,
      summary: false,
      invoices: false,
      modes: false,
      payments: false,
      checkout: false
    };
    vm.error = {
      init: null,
      summary: null,
      invoices: null,
      modes: null,
      payments: null,
      checkout: null
    };

    // Data
    vm.term = null;           // { id, label }
    vm.profile = null;        // { student_id, student_number, first_name, last_name, email }
    vm.summary = null;        // StudentFinancesSummary
    vm.invoices = [];         // InvoiceItem[]
    vm.modes = [];            // Online payment modes
    vm.payments = {           // from payment_details (filtered by term)
      items: [],
      totalPaid: 0
    };
    vm.paymentsFilter = { status: 'all' };

    // Selection for checkout
    vm.selection = {
      // target: 'tuition' | 'invoice'
      target: 'tuition',
      // amount to pay (number)
      amount: null,
      // selected invoice (if target === 'invoice')
      invoice: null,
      // selected payment mode (object from vm.modes)
      mode: null
    };

    // Tuition breakdown modal state
    vm.breakdown = {
      open: false,
      loading: false,
      error: null,
      data: null // structure of TuitionBreakdownResource { summary, items, meta }
    };

    // Installment Plan UI (read-only)
    vm.installmentsUI = { show: false, summary: null, list: [] };
    vm.selectedInstallmentPlanName = null;
    vm.installmentsSummary = function () {
      try { return (vm.installmentsUI && vm.installmentsUI.summary) ? vm.installmentsUI.summary : null; }
      catch (e) { return null; }
    };
    vm.installmentsList = function () {
      try { return (vm.installmentsUI && Array.isArray(vm.installmentsUI.list)) ? vm.installmentsUI.list : []; }
      catch (e) { return []; }
    };
    vm._recomputeInstallmentsPanel = _recomputeInstallmentsPanel;
    vm._autoSetAmount = _autoSetAmount;

    // Exposed methods
    vm.reload = reload;
    vm.selectTarget = selectTarget;
    vm.selectInvoice = selectInvoice;
    vm.suggestAmounts = suggestAmounts;
    vm.computePreview = computePreview;
    vm.currency = currency;
    vm.submitCheckout = submitCheckout;
    vm.openBreakdown = openBreakdown;
    vm.closeBreakdown = closeBreakdown;
    vm.hasBreakdown = hasBreakdown;
    vm.sum = sum;
    vm.filteredPayments = filteredPayments;
    vm.paymentsTotalDisplay = paymentsTotalDisplay;

    // Helpers for view
    vm.outstanding = function () {
      if (!vm.summary) return 0;
      return number(vm.summary.outstanding);
    };
    vm.selectedTotal = function () {
      if (!vm.summary) return 0;
      return number((vm.summary.tuition && vm.summary.tuition.selected_total) || 0);
    };
    vm.allowPartialTuition = function () {
      return !!(vm.summary && vm.summary.registration && vm.summary.registration.paymentType === 'partial');
    };
    vm.invoiceRemaining = function (inv) {
      if (!inv) return null;
      if (typeof inv._remaining_effective === 'number') return inv._remaining_effective;
      if (typeof inv.remaining === 'number') return inv.remaining;
      return null;
    };

    // Init
    init();

    // Implementation

    function init() {
      vm.loading.init = true;
      vm.error.init = null;

      // Ensure global term service initialized
      TermService.init()
        .then(function () {
          var t = TermService.getSelectedTerm();
          if (t && t.intID) {
            vm.term = { id: t.intID, label: t.label || null };
          }
        })
        .then(function () {
          // Fallback to API active term if TermService did not resolve a selected term
          if (!vm.term || !vm.term.id) {
            return StudentFinancesService.getActiveTerm().then(function (active) {
              if (active && active.id) {
                vm.term = active;
              }
            });
          }
        })
        .then(function () {
          // Resolve profile (student_id, student_number)
          return StudentFinancesService.resolveProfile().then(function (p) {
            vm.profile = p;
          });
        })
        .then(function () {
          // Load all data for selected term
          return reload();
        })
        .catch(function (e) {
          vm.error.init = 'Failed to initialize.';
          console.error(e);
        })
        .finally(function () {
          vm.loading.init = false;
        });

      // Refresh on global term changes
      $scope.$on('termChanged', function (ev, data) {
        var t = TermService.getSelectedTerm();
        if (t && t.intID && (!vm.term || vm.term.id !== t.intID)) {
          vm.term = { id: t.intID, label: t.label || null };
          reload();
        }
      });
    }

    function reload() {
      if (!vm.profile) return $q.resolve();
      if (!vm.term || !vm.term.id) return $q.resolve();

      var sid = vm.profile.student_id;
      var sno = vm.profile.student_number;

      vm.loading.summary = true;
      vm.loading.invoices = true;
      vm.loading.modes = true;
      vm.loading.payments = true;
      vm.error.summary = null;
      vm.error.invoices = null;
      vm.error.modes = null;
      vm.error.payments = null;

      // Build params
      var params = { syid: vm.term.id };
      if (sid) params.student_id = sid; else if (sno) params.student_number = sno;

      var p1 = StudentFinancesService.getSummary(params).then(function (d) {
        vm.summary = d || null;

        // Default target/amount
        vm.selection.target = 'tuition';
        vm.selection.invoice = null;
        vm.selection.amount = clampAmount(vm.outstanding());
      }, function () {
        vm.error.summary = 'Failed to load summary.';
        vm.summary = null;
      }).finally(function () { vm.loading.summary = false; });

      var p2 = StudentFinancesService.getInvoices(Object.assign({}, params, { include_draft: true }))
        .then(function (items) {
          // Only show tuition + billing types (if needed, filter here)
          vm.invoices = Array.isArray(items) ? items : [];
          // Recompute reservation offsets for Tuition invoice after invoices load
          recomputeInvoicesReservationOffset();
          // Recompute payments total using invoice types to exclude Application-related payments
          try { recomputePaymentsTotalUsingInvoices(); } catch (e) {}

          // If user had selected invoice previously, refresh remaining reference
          if (vm.selection.target === 'invoice' && vm.selection.invoice) {
            var refreshed = vm.invoices.find(function (it) {
              return ('' + (it.invoice_number || '')) === ('' + (vm.selection.invoice.invoice_number || ''));
            });
            vm.selection.invoice = refreshed || null;
            if (vm.selection.invoice) {
              var rem = vm.invoiceRemaining(vm.selection.invoice);
              vm.selection.amount = clampAmount(rem != null ? rem : 0);
            }
          }
        }, function () {
          vm.error.invoices = 'Failed to load invoices.';
          vm.invoices = [];
        })
        .finally(function () { vm.loading.invoices = false; });

      var p3 = StudentFinancesService.getPaymentModes().then(function (items) {
        vm.modes = Array.isArray(items) ? items : [];
        vm.selection.mode = (vm.modes && vm.modes[0]) || null;
      }, function () {
        vm.error.modes = 'Failed to load payment modes.';
        vm.modes = [];
      }).finally(function () { vm.loading.modes = false; });

      // Payments (payment_details via student-safe endpoint)
      var p4 = StudentFinancesService.getPayments(params).then(function (res) {
        var items = Array.isArray(res.items) ? res.items : [];
        vm.payments.items = items.sort(function (a, b) {
          return (a.posted_at || '').localeCompare(b.posted_at || '');
        });
        var meta = res.meta || {};
        // Prefer invoice-type-informed computation to exclude Application-related payments from totals
        try { 
          recomputePaymentsTotalUsingInvoices(); 
        } catch (e) { 
          // Fallback to heuristic-only if helper not yet defined
          var totalList = 0;
          (items || []).forEach(function (p) {
            var desc = (p && p.description) ? ('' + p.description).toLowerCase().trim() : '';
            var st = (p && p.status) ? ('' + p.status).toLowerCase().trim() : '';
            var isAppFee = (desc.indexOf('application') !== -1) 
              || (desc.indexOf('app fee') !== -1) 
              || (desc.indexOf('admission fee') !== -1) 
              || (desc.indexOf('admissions fee') !== -1);
            if (st === 'paid' && !isAppFee) {
              totalList += number(p.amount || 0);
            }
          });
          vm.payments.totalPaid = round2(totalList);
        }

        // After payments load, recompute reservation offsets for Tuition invoice
        recomputeInvoicesReservationOffset();
      }, function () {
        vm.error.payments = 'Failed to load payments.';
        vm.payments.items = [];
        vm.payments.totalPaid = 0;
      }).finally(function () { vm.loading.payments = false; });

      // Tuition breakdown for Installment Plans panel
      var p5 = (sno ? StudentFinancesService.getTuitionBreakdown({ student_number: sno, syid: vm.term.id }) : $q.when(null))
        .then(function (d) { vm._tuitionInstallments = d || null; }, function () { vm._tuitionInstallments = null; });

      return $q.all([p1, p2, p3, p4, p5]).then(function () {
        try { if (typeof vm._recomputeInstallmentsPanel === 'function') vm._recomputeInstallmentsPanel(); } catch (e) {}
        try { if (typeof vm._autoSetAmount === 'function') vm._autoSetAmount(); } catch (_e) {}
      });
    }

    function selectTarget(target) {
      vm.selection.target = target === 'invoice' ? 'invoice' : 'tuition';
      if (vm.selection.target === 'tuition') {
        vm.selection.invoice = null;
      } else {
        // pick first invoice with remaining > 0 if available
        var pick = (vm.invoices || []).find(function (it) {
          var rem = vm.invoiceRemaining(it);
          return rem == null ? false : (rem > 0.009);
        });
        vm.selection.invoice = pick || null;
      }
      try { if (typeof vm._autoSetAmount === 'function') vm._autoSetAmount(); } catch (_e) {}
    }

    function selectInvoice(inv) {
      vm.selection.invoice = inv;
      try { if (typeof vm._autoSetAmount === 'function') vm._autoSetAmount(); } catch (_e) {}
    }

    function suggestAmounts() {
      // For tuition: suggest either full outstanding or for partial mode suggest some installment-based hints
      var a = [];
      var outstanding = vm.outstanding();
      if (vm.selection.target === 'tuition') {
        if (vm.allowPartialTuition()) {
          // Heuristic suggestions (the real breakdown can be complex; offer fractions of outstanding)
          var half = round2(outstanding * 0.5);
          var third = round2(outstanding / 3);
          var quarter = round2(outstanding / 4);
          a = uniquePos([third, quarter, half, outstanding]).filter(function (v) { return v > 0; });
        } else {
          a = uniquePos([outstanding]).filter(function (v) { return v > 0; });
        }
      } else if (vm.selection.target === 'invoice' && vm.selection.invoice) {
        var rem = vm.invoiceRemaining(vm.selection.invoice) || 0;
        a = uniquePos([round2(rem / 2), round2(rem / 3), round2(rem / 4), rem]).filter(function (v) { return v > 0; });
      }
      return a.slice(0, 4);
    }

    function computePreview() {
      var amount = number(vm.selection.amount);
      if (!vm.selection.mode || !(amount > 0)) {
        return { charge: 0, total_with_charge: amount };
      }
      return StudentFinancesService.computeCharges(vm.selection.mode, amount);
    }

    function submitCheckout() {
      vm.loading.checkout = true;
      vm.error.checkout = null;

      try {
        // Validate selection based on rules
        if (!vm.selection.mode) {
          vm.error.checkout = 'Please select a payment method.';
          return;
        }
        var amount = number(vm.selection.amount);
        if (!(amount > 0)) {
          vm.error.checkout = 'Please enter a valid amount.';
          return;
        }

        if (vm.selection.target === 'tuition' && !vm.allowPartialTuition()) {
          var out = vm.outstanding();
          // enforce paying full outstanding
          amount = round2(out);
          vm.selection.amount = amount;
        }

        if (vm.selection.target === 'invoice') {
          if (!vm.selection.invoice) {
            vm.error.checkout = 'Please select an invoice.';
            return;
          }
          var rem = vm.invoiceRemaining(vm.selection.invoice);
          if (rem != null && amount > (rem + 0.01)) {
            vm.error.checkout = 'Amount exceeds invoice remaining.';
            return;
          }
        }

        // Build payload
        var mode = vm.selection.mode;
        var viewer = vm.profile || {};
        var preview = StudentFinancesService.computeCharges(mode, amount);
        var orderItems = StudentFinancesService.buildOrderItems(
          vm.selection.target,
          amount,
          {
            invoice_number: vm.selection.invoice ? vm.selection.invoice.invoice_number : null,
            invoice_id: vm.selection.invoice ? vm.selection.invoice.id : null,
            syid: vm.term && vm.term.id ? vm.term.id : null,
            term_label: vm.term && vm.term.label ? vm.term.label : null
          }
        );

        var payload = {
          description: vm.selection.target === 'tuition' ? 'Tuition Payment' : 'Invoice Payment',
          // Backend requires student_information_id (int) even for student flows; use student_id as surrogate.
          student_information_id: (vm.profile && vm.profile.student_id) ? vm.profile.student_id : 0,
          student_number: viewer.student_number || '',
          first_name: viewer.first_name || '',
          last_name: viewer.last_name || '',
          middle_name: viewer.middle_name || '',
          email: viewer.email || '',
          contact_number: viewer.contact_number || '',
          mode_of_payment_id: mode.id,
          total_price_without_charge: amount,
          total_price_with_charge: preview.total_with_charge,
          charge: preview.charge,
          order_items: orderItems,
          syid: vm.term && vm.term.id ? vm.term.id : null
        };
        if (vm.selection.target === 'invoice' && vm.selection.invoice && vm.selection.invoice.invoice_number) {
          payload.invoice_number = vm.selection.invoice.invoice_number;
        }

        return StudentFinancesService.checkout(payload).then(function (resp) {
          // Handle different gateways
          if (resp && resp.success && resp.gateway) {
            if (resp.payment_link) {
              $window.location.href = resp.payment_link;
              return;
            }
            if (resp.gateway === 'bdo_pay' && resp.action_url && resp.post_data) {
              // Auto-submit form for BDO
              postToUrl(resp.action_url, resp.post_data);
              return;
            }
          }
          // Fallback: show toast and log response
          if (ToastService && ToastService.info) {
            ToastService.info('Checkout created. Follow the instructions to complete payment.');
          }
          console.log('Checkout response:', resp);
        }, function (err) {
          vm.error.checkout = (err && err.data && (err.data.message || err.data.error)) || 'Checkout failed.';
        });
      } finally {
        vm.loading.checkout = false;
      }
    }

    // Tuition breakdown modal helpers
    function openBreakdown() {
      if (!vm.profile || !vm.term || !vm.term.id) return;
      vm.breakdown.open = true;
      vm.breakdown.loading = true;
      vm.breakdown.error = null;
      vm.breakdown.data = null;

      var sno = (vm.profile && vm.profile.student_number) ? vm.profile.student_number : null;
      if (!sno) {
        vm.breakdown.loading = false;
        vm.breakdown.error = 'Missing student number.';
        return;
      }
      StudentFinancesService.getTuitionBreakdown({ student_number: sno, syid: vm.term.id })
        .then(function (d) {
          if (!d || (!d.summary && !d.items)) {
            vm.breakdown.error = 'No breakdown available.';
            vm.breakdown.data = null;
          } else {
            vm.breakdown.data = d;
            // keep a copy for Installment Plan panel and recompute
            vm._tuitionInstallments = d;
            try { if (typeof vm._recomputeInstallmentsPanel === 'function') vm._recomputeInstallmentsPanel(); } catch (_eIP) {}
          }
        })
        .catch(function () {
          vm.breakdown.error = 'Failed to load tuition breakdown.';
          vm.breakdown.data = null;
        })
        .finally(function () {
          vm.breakdown.loading = false;
        });
    }

    function closeBreakdown() {
      vm.breakdown.open = false;
    }

    function hasBreakdown() {
      var d = vm.breakdown && vm.breakdown.data;
      return !!(d && d.summary);
    }

    function sum(arr, key) {
      var t = 0;
      (arr || []).forEach(function (x) {
        var v = parseFloat((key ? x[key] : x) || 0);
        if (!isNaN(v)) t += v;
      });
      return round2(t);
    }

    // Payments filter helper
    function filteredPayments() {
      var list = (vm.payments && Array.isArray(vm.payments.items)) ? vm.payments.items : [];
      var status = (vm.paymentsFilter && vm.paymentsFilter.status) ? vm.paymentsFilter.status : 'all';
      function lc(s) { return ('' + (s || '')).toLowerCase(); }
      if (status === 'paid') {
        return list.filter(function (x) { return lc(x.status) === 'paid'; });
      }
      if (status === 'not_paid') {
        return list.filter(function (x) { return lc(x.status) !== 'paid'; });
      }
      return list;
    }

    // Recompute Tuition invoice totals/remaining after applying Reservation payments (display-only)
    function recomputeInvoicesReservationOffset() {
      if (!Array.isArray(vm.invoices) || !vm.invoices.length) return;
      // Sum Paid Reservation payments from payment_details items for the selected term
      var reservationPaid = 0;
      (vm.payments.items || []).forEach(function (p) {
        var desc = (p && p.description) ? ('' + p.description).toLowerCase() : '';
        var st = (p && p.status) ? ('' + p.status).toLowerCase() : '';
        if (st === 'paid' && desc.indexOf('reservation') === 0) {
          reservationPaid += number(p.amount);
        }
      });
      reservationPaid = round2(reservationPaid);

      vm.invoices.forEach(function (inv) {
        // Only apply to Tuition invoice rows
        var t = (inv && inv.type) ? ('' + inv.type).toLowerCase() : '';
        if (t === 'tuition' && reservationPaid > 0) {
          var total = number(inv.amount_total || 0);
          var remaining = vm.invoiceRemaining(inv);
          if (remaining == null) remaining = number(inv.amount_total || 0); // fallback

          inv._reservation_applied = reservationPaid;
          inv._total_effective = round2(Math.max(0, total - reservationPaid));
          inv._remaining_effective = round2(Math.max(0, number(remaining) - reservationPaid));
        } else {
          // Clear effective fields for non-tuition rows
          inv._reservation_applied = 0;
          inv._total_effective = null;
          inv._remaining_effective = null;
        }
      });
    }

    // Display helper for Payments total (kept for legacy bindings)
    function paymentsTotalDisplay() {
      return round2(number((vm.payments && vm.payments.totalPaid) || 0));
    }

    // Auto-set amount for Pay Online based on selection and installment plan
    function _autoSetAmount() {
      try {
        if (!vm.selection) vm.selection = {};
        var tgt = vm.selection.target || 'tuition';
        if (tgt === 'invoice') {
          var inv = vm.selection.invoice || null;
          var rem = vm.invoiceRemaining(inv);
          vm.selection.amount = clampAmount(rem != null ? rem : 0);
          return;
        }
        // Tuition target
        if (vm.allowPartialTuition() && vm.installmentsUI && vm.installmentsUI.show) {
          var fd = (vm.installmentsUI && vm.installmentsUI.firstDue) ? vm.installmentsUI.firstDue : null;
          var amt = fd && isFinite(parseFloat(fd.amount)) ? Math.floor(parseFloat(fd.amount) * 100) / 100 : clampAmount(vm.outstanding());
          vm.selection.amount = amt;
          return;
        }
        // Fallback: full outstanding
        vm.selection.amount = clampAmount(vm.outstanding());
      } catch (e) {
        try { vm.selection.amount = clampAmount(vm.outstanding()); } catch (_e) {}
      }
    }

    // Read-only Installment Plan computation (mirrors cashier viewer without selection)
    function _recomputeInstallmentsPanel() {
      try {
        var isPartial = !!(vm.summary && vm.summary.registration && vm.summary.registration.paymentType === 'partial');
        var payload = vm._tuitionInstallments || (vm.breakdown && vm.breakdown.data) || null;
        var inst = payload && payload.summary && payload.summary.installments ? payload.summary.installments : null;

        var show = isPartial && !!inst;
        vm.installmentsUI.show = !!show;
        if (!show) {
          vm.installmentsUI.summary = null;
          vm.installmentsUI.list = [];
          vm.selectedInstallmentPlanName = null;
          return;
        }

        function num(x) { var v = parseFloat(x); return isFinite(v) ? v : 0; }

        // Resolve selected plan from dynamic plans (when available)
        var plans = (inst && Array.isArray(inst.plans)) ? inst.plans.slice() : [];
        var selPlan = null;
        if (plans.length) {
          var selId = (inst.selected_plan_id != null) ? ('' + inst.selected_plan_id) : null;
          for (var i = 0; i < plans.length; i++) {
            var p = plans[i] || {};
            if (selId && ('' + p.id) === selId) { selPlan = p; break; }
          }
          if (!selPlan) selPlan = plans[0];
        }
        vm.selectedInstallmentPlanName = selPlan ? (selPlan.label || selPlan.code || 'Plan') : 'Standard';

        var base = {
          dp: (selPlan && selPlan.down_payment != null) ? num(selPlan.down_payment) : num(inst.down_payment),
          fee: (selPlan && selPlan.installment_fee != null) ? num(selPlan.installment_fee) : num(inst.installment_fee)
        };
        var countCand = null;
        if (selPlan && selPlan.installment_count != null) countCand = selPlan.installment_count;
        else if (inst && inst.installment_count != null) countCand = inst.installment_count;
        else if (inst && inst.count != null) countCand = inst.count;
        var count = parseInt(countCand, 10); if (!isFinite(count) || count < 0) count = 5;

        // Sum of Paid Tuition/Reservation payments
        var paidTuition = 0;
        try {
          var rows = (vm.payments && Array.isArray(vm.payments.items)) ? vm.payments.items : [];
          for (var r = 0; r < rows.length; r++) {
            var row = rows[r] || {};
            var st = (row.status == null ? '' : ('' + row.status)).trim().toLowerCase();
            if (st !== 'paid') continue;
            var desc = (row.description == null ? '' : ('' + row.description)).trim().toLowerCase();
            var isTu = desc.indexOf('tuition') !== -1;
            var isRes = desc.indexOf('reservation') === 0;
            if (!(isTu || isRes)) continue;
            var amt = parseFloat(row.amount);
            if (isFinite(amt)) paidTuition += amt;
          }
        } catch (_ePaid) { paidTuition = 0; }

        // Build buckets and compute remaining per bucket (truncate to 2dp)
        var buckets = [{ key: 'dp', label: 'Down Payment', due: base.dp }];
        for (var j = 1; j <= count; j++) {
          buckets.push({ key: ('i' + j), label: ('Installment ' + j), due: base.fee });
        }

        var paidLeft = isFinite(paidTuition) ? paidTuition : 0;
        var list = [];
        var dpRemaining = base.dp;
        var installmentsRemainingTotal = 0;

        for (var b = 0; b < buckets.length; b++) {
          var bucket = buckets[b];
          var due = num(bucket.due);
          var remain = due;

          if (isFinite(paidLeft) && paidLeft > 0) {
            if (paidLeft >= due) {
              remain = 0;
              paidLeft -= due;
            } else {
              remain = due - paidLeft;
              paidLeft = 0;
            }
          }

          remain = Math.max(0, Math.floor(remain * 100) / 100);
          if (bucket.key === 'dp') dpRemaining = remain;
          else installmentsRemainingTotal += remain;

          list.push({ key: bucket.key, label: bucket.label, amount: remain });
        }

        vm.installmentsUI.summary = { dp: dpRemaining, fee: base.fee, total: installmentsRemainingTotal, count: count };
        vm.installmentsUI.list = list;

        // Determine the first non-zero due amount (prefer DP, else first installment)
        var first = null;
        try {
          if (isFinite(dpRemaining) && dpRemaining > 0) {
            first = { key: 'dp', label: 'Down Payment', amount: dpRemaining };
          } else if (Array.isArray(list)) {
            for (var fi = 0; fi < list.length; fi++) {
              var ri = list[fi] || {};
              if (ri.key === 'dp') continue;
              var amt = parseFloat(ri.amount);
              if (isFinite(amt) && amt > 0) { first = { key: ri.key, label: ri.label, amount: amt }; break; }
            }
          }
        } catch (_eFD) { first = null; }
        vm.installmentsUI.firstDue = first;

        // Auto-set amount when panel recomputed (tuition partial flow)
        try { if (typeof vm._autoSetAmount === 'function') vm._autoSetAmount(); } catch (_eAuto) {}
      } catch (e) {
        try { vm.installmentsUI.show = false; vm.installmentsUI.summary = null; vm.installmentsUI.list = []; } catch (_e) {}
      }
    }

    // Build an invoice_number -> type map for robust Application fee exclusion
    function _invoiceTypeMap() {
      var map = {};
      (vm.invoices || []).forEach(function (inv) {
        var no = (inv && inv.invoice_number) ? ('' + inv.invoice_number) : null;
        if (!no) return;
        var typ = (inv && inv.type) ? ('' + inv.type).toLowerCase() : '';
        map[no] = typ;
      });
      return map;
    }

    // Recompute totalPaid from PAID payments only, excluding Application-related payments
    function recomputePaymentsTotalUsingInvoices() {
      var items = (vm.payments && Array.isArray(vm.payments.items)) ? vm.payments.items : [];
      var map = _invoiceTypeMap();
      var total = 0;
      (items || []).forEach(function (p) {
        var st = (p && p.status) ? ('' + p.status).toLowerCase().trim() : '';
        if (st !== 'paid') return;

        var desc = (p && p.description) ? ('' + p.description).toLowerCase().trim() : '';
        // Heuristic description-based detection
        var isAppDesc = (desc.indexOf('application') !== -1)
          || (desc.indexOf('app fee') !== -1)
          || (desc.indexOf('admission fee') !== -1)
          || (desc.indexOf('admissions fee') !== -1);

        // Invoice type detection (more reliable when invoice is linked)
        var invNo = (p && p.invoice_number) ? ('' + p.invoice_number) : null;
        var invType = invNo ? (map[invNo] || '') : '';
        var isAppInv = invType && invType.indexOf('application') === 0;

        if (isAppDesc || isAppInv) return;

        total += number(p.amount || 0);
      });
      vm.payments.totalPaid = round2(total);
    }

    // Utilities

    function postToUrl(url, data) {
      var form = document.createElement('form');
      form.method = 'POST';
      form.action = url;
      Object.keys(data || {}).forEach(function (k) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = data[k];
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
    }

    function currency(n) {
      return (number(n) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function number(v) {
      var n = parseFloat(v);
      return isNaN(n) ? 0 : n;
    }

    function round2(v) {
      return Math.round((number(v)) * 100) / 100;
    }

    function clampAmount(v) {
      var n = round2(v);
      if (n < 0) n = 0;
      return n;
    }

    function uniquePos(list) {
      var seen = {};
      var out = [];
      (list || []).forEach(function (v) {
        var n = round2(v);
        if (n > 0 && !seen[n]) {
          seen[n] = true;
          out.push(n);
        }
      });
      return out;
    }
  }

})();
