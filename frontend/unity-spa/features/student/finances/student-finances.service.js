(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('StudentFinancesService', StudentFinancesService);

  StudentFinancesService.$inject = ['$http', '$q', 'APP_CONFIG', 'StorageService'];
  function StudentFinancesService($http, $q, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE;

    var api = {
      viewer: BASE + '/student/viewer',
      balances: BASE + '/student/balances',
      summary: BASE + '/student/finances/summary',
      invoices: BASE + '/student/finances/invoices',
      payments: BASE + '/student/finances/payments',
      modes: BASE + '/student/finances/payment-modes',
      checkout: BASE + '/payments/checkout',
      activeTerm: BASE + '/generic/active-term',
      tuitionCompute: BASE + '/tuition/compute'
    };

    return {
      api: api,
      resolveProfile: resolveProfile,
      getActiveTerm: getActiveTerm,
      getSummary: getSummary,
      getInvoices: getInvoices,
      getPayments: getPayments,
      getPaymentModes: getPaymentModes,
      getBalances: getBalances,
      getTuitionBreakdown: getTuitionBreakdown,
      computeCharges: computeCharges,
      buildOrderItems: buildOrderItems,
      checkout: checkout
    };

    // Resolve student profile via token from loginState (fallback to student_number if present)
    function resolveProfile() {
      var state = StorageService.getJSON('loginState') || {};
      var username = state && state.username ? ('' + state.username).trim() : null;
      var student_number = state && state.student_number ? ('' + state.student_number).trim() : null;

      if (username) {
        return $http.post(api.viewer, { token: username }).then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            var d = resp.data.data || resp.data;
            // StudentResource returns resource-like; unwrap when needed
            var data = d.data || d;
            return {
              student_id: (data.student_id != null) ? parseInt(data.student_id, 10) : null,
              student_number: data.student_number || student_number || null,
              first_name: data.first_name || null,
              last_name: data.last_name || null,
              email: data.personal_email || username || null,
              contact_number: data.contact_number || state.contact_number || null
            };
          }
          return $q.reject(new Error('viewer failed'));
        }, function () {
          // fall back to state only
          return {
            student_id: null,
            student_number: student_number || username || null,
            first_name: null,
            last_name: null,
            email: username || null,
            contact_number: null
          };
        });
      }

      // No username -> fallback only
      return $q.when({
        student_id: null,
        student_number: student_number || null,
        first_name: null,
        last_name: null,
        email: null
      });
    }

    // Resolve active term from API (used when TermService state is unavailable)
    function getActiveTerm() {
      return $http.get(api.activeTerm).then(function (resp) {
        var d = resp && resp.data ? (resp.data.data || resp.data) : null;
        if (!d) return null;
        return {
          id: d.intID || d.id || null,
          label: d.label || null
        };
      }, function () {
        return null;
      });
    }

    // GET /tuition/compute (breakdown)
    function getTuitionBreakdown(args) {
      // args: { student_number: string, syid: number, discount_id?, scholarship_id? }
      var cfg = { params: { student_number: args.student_number, term: args.syid } };
      if (args.discount_id != null) cfg.params.discount_id = args.discount_id;
      if (args.scholarship_id != null) cfg.params.scholarship_id = args.scholarship_id;
      return $http.get(api.tuitionCompute, cfg).then(function (resp) {
        // TuitionController returns TuitionBreakdownResource or {success:false}
        if (!resp || !resp.data) return null;
        var d = resp.data;
        // When using Resource, fields may be top-level; normalize
        if (d.summary || d.items || d.meta) return d;
        if (d.data && (d.data.summary || d.data.items || d.data.meta)) return d.data;
        return d;
      }, function () {
        return null;
      });
    }

    // GET /student/finances/summary
    function getSummary(args) {
      // args: { student_id?, student_number?, syid: number }
      var cfg = { params: {} };
      if (args.student_id) cfg.params.student_id = args.student_id;
      if (args.student_number) cfg.params.student_number = args.student_number;
      cfg.params.syid = args.syid;

      return $http.get(api.summary, cfg).then(function (resp) {
        var d = resp && resp.data ? (resp.data.data || resp.data) : null;
        return d;
      });
    }

    // GET /student/finances/invoices
    function getInvoices(args) {
      // args: { student_id?, student_number?, syid: number, include_draft?: boolean }
      var cfg = { params: {} };
      if (args.student_id) cfg.params.student_id = args.student_id;
      if (args.student_number) cfg.params.student_number = args.student_number;
      cfg.params.syid = args.syid;
      cfg.params.include_draft = args.include_draft === false ? 0 : 1;

      return $http.get(api.invoices, cfg).then(function (resp) {
        var d = resp && resp.data ? (resp.data.items || []) : [];
        return d;
      });
    }

    // GET /student/finances/payments
    // returns { items: PaymentItem[], meta: {...} }
    function getPayments(args) {
      // args: { student_id?, student_number?, syid: number }
      var cfg = { params: {} };
      if (args.student_id) cfg.params.student_id = args.student_id;
      if (args.student_number) cfg.params.student_number = args.student_number;
      cfg.params.syid = args.syid;

      return $http.get(api.payments, cfg).then(function (resp) {
        var data = resp && resp.data ? resp.data : null;
        return {
          items: (data && data.items) ? data.items : [],
          meta: (data && data.meta) ? data.meta : {}
        };
      });
    }

    // GET /student/finances/payment-modes
    function getPaymentModes() {
      return $http.get(api.modes).then(function (resp) {
        var d = resp && resp.data ? (resp.data.items || []) : [];
        return d;
      });
    }

    // POST /student/balances (returns ledger; filter client-side by syid)
    function getBalances(student_id) {
      return $http.post(api.balances, { student_id: student_id }).then(function (resp) {
        var d = resp && resp.data ? (resp.data.data || resp.data) : null;
        return d;
      });
    }

    // Compute charges locally to preview (mirror server percentage with min=28 rule)
    function computeCharges(mode, amount) {
      var type = mode && mode.type || 'fixed';
      var rawCharge = 0;
      if (type === 'percentage') {
        var percent = parseFloat(mode.charge || 0);
        rawCharge = Math.round((amount * (percent / 100)) * 100) / 100;
        if (rawCharge < 28) rawCharge = 28.00;
      } else {
        rawCharge = parseFloat(mode.charge || 0);
      }
      var totalWith = Math.round((amount + rawCharge) * 100) / 100;
      return { charge: rawCharge, total_with_charge: totalWith };
    }

    // Build gateway order items (parity with PaymentGatewayController)
    function buildOrderItems(target, amount, context) {
      // Build item with required validation fields:
      // id (int), title (string), qty (int>=1), price_default (number), term (optional), academic_year (optional)
      var title = 'Payment';
      var invNo = context && context.invoice_number ? ('' + context.invoice_number) : '';
      if (target === 'tuition') {
        title = 'Tuition Payment';
      } else if (target === 'invoice') {
        title = invNo ? ('Invoice #' + invNo) : 'Invoice Payment';
      }

      // Derive a numeric id for the line item:
      // - For invoice payments, prefer the invoice's DB id when available.
      // - For tuition payments, use syid (term id).
      var itemId = 0;
      if (target === 'invoice') {
        if (context && context.invoice_id != null) {
          itemId = parseInt(context.invoice_id, 10);
          if (isNaN(itemId)) itemId = 0;
        }
      } else if (target === 'tuition') {
        if (context && context.syid != null) {
          itemId = parseInt(context.syid, 10);
          if (isNaN(itemId)) itemId = 0;
        }
      }

      // Optional: include term label and academic year text if available
      var termLabel = (context && context.term_label) ? ('' + context.term_label) : null;
      var ayText = null;
      if (termLabel) {
        var m = termLabel.match(/(\d{4}-\d{4})/);
        if (m && m[1]) {
          ayText = m[1];
        }
      }

      return [{
        id: itemId || 1, // fallback to a positive id to satisfy validation
        title: title,
        qty: 1,
        price_default: parseFloat(amount || 0),
        term: termLabel || null,
        academic_year: ayText || null
      }];
    }

    // POST /payments/checkout
    function checkout(payload) {
      // payload must include description, student_number, first_name, last_name, email, contact_number,
      // mode_of_payment_id, total_price_without_charge, total_price_with_charge, charge, order_items,
      // and optional syid, invoice_number
      return $http.post(api.checkout, payload).then(function (resp) {
        return resp && resp.data ? resp.data : resp;
      });
    }
  }
})();
