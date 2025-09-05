(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CashierViewerController', CashierViewerController);

  CashierViewerController.$inject = [
    '$routeParams',
    '$location',
    '$http',
    '$q',
    '$rootScope',
    '$scope',
    '$timeout',
    '$injector',
    'APP_CONFIG',
    'StorageService',
    'UnityService',
    'TermService',
    'TuitionYearsService',
    'StudentsService',
    'RoleService',
    'CashiersService',
    'PaymentModesService',
    'PaymentDescriptionsService',
    'StudentBillingService',
    'ApplicantsService',
    'AdminPaymentDetailsService'
  ];
  function CashierViewerController(
    $routeParams, $location, $http, $q, $rootScope, $scope, $timeout, $injector,
    APP_CONFIG, StorageService, UnityService, TermService, TuitionYearsService, StudentsService, RoleService, CashiersService, PaymentModesService, PaymentDescriptionsService, StudentBillingService, ApplicantsService, AdminPaymentDetailsService
  ) {
    var vm = this;

    // Auth guard
    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Capability: allow edits only for Finance/Admin
    vm.canEdit = !!(RoleService && typeof RoleService.hasAny === 'function' ? RoleService.hasAny(['finance', 'admin']) : false);

    // Identifiers
    vm.id = parseInt($routeParams.id, 10);
    vm.sn = null; // student_number resolved from student/{id}
    vm.term = null; // selected term (intID)

    // Student dropdown (for quick navigation across students)
    vm.students = [];
    vm.selectedStudentId = vm.id || null;
    vm.onStudentSelected = function () {
      try {
        var targetId = vm.selectedStudentId != null ? parseInt(vm.selectedStudentId, 10) : null;
        if (targetId && targetId !== vm.id) {
          $location.path('/finance/cashier/' + targetId);
        }
      } catch (e) {}
    };

    // API base
    var API = APP_CONFIG.API_BASE;

    // UI state
    vm.loading = {
      bootstrap: false,
      student: false,
      registration: false,
      tuition: false,
      ledger: false,
      records: false,
      payments: false,
      invoices: false,
      update: false,
      tuitionYears: false
    };
    vm.error = {
      student: null,
      registration: null,
      tuition: null,
      ledger: null,
      records: null,
      payments: null,
      invoices: null,
      update: null
    };

    // Data models
    vm.student = null;
    vm.registrationResp = null; // {success, data:{exists, registration}}
    vm.registration = null;     // registration object or null
    vm.tuition = null;          // TuitionBreakdownResource payload (data or direct)
    vm.tuitionSaved = null;     // Saved tuition snapshot row (if any)
    vm.ledger = null;           // { student_number, transactions, ... } or ledger array shape
    vm.paymentDetails = null;   // Payment details for selected term/registration
    vm.invoices = [];           // Invoices for selected term/registration
    vm.records = null;          // records or terms shape (see DataFetcherService)
    vm.tuitionYearOptions = []; // dropdown options for Tuition Year
    vm.tuitionYearsLoaded = false;
    vm.meta = {
      amount_paid: 0,
      remaining_amount: 0,
      tuition_source: 'computed' // 'saved' when using snapshot
    };
    // Track last loaded params to avoid duplicate API calls
    vm._last = {
      registration: null,
      tuition: null,
      records: null,
      payments: null,
      invoices: null,
      billing: null
    };
    // Track last handled termChanged to suppress duplicate bursts
    vm._lastTermEvent = { term: null, ts: 0 };
    // Selected tuition amount based on registered payment type (full vs partial)
    vm.selectedTuitionAmount = null;

    // =========================
    // Applicant context for Application/Reservation Fee controls
    // =========================
    vm.applicantSyid = null;       // latest tb_mas_applicant_data.syid (nullable)
    vm.appWaiveAppFee = null;      // latest waive_application_fee (nullable)
    vm.paidApplicationFee = null;  // latest paid_application_fee (nullable boolean)
    vm.interviewed = false;        // latest interviewed flag (boolean)
    vm.billingItems = [];          // student billing items for current term
    vm.hasAppBilling = false;      // whether Application Fee billing exists for current term
    vm.hasReservationBilling = false; // whether Reservation Fee billing exists for current term
    vm.generatingAppFee = false;   // UI flag during app fee generation
    vm.appFeeGenerateError = null; // error message for app fee generation
    vm.generatingResFee = false;   // UI flag during reservation fee generation
    vm.resFeeGenerateError = null; // error message for reservation fee generation

    // Helper: case-insensitive check for "Application Payment" or "Application Fee"
    function _isApplicationFeeDesc(s) {
      try {
        var d = (s == null ? '' : ('' + s)).trim().toLowerCase();
        return d === 'application payment' || d === 'application fee';
      } catch (e) {
        return false;
      }
    }
    // Helper: case-insensitive check for "Reservation Payment" or "Reservation Fee"
    function _isReservationFeeDesc(s) {
      try {
        var d = (s == null ? '' : ('' + s)).trim().toLowerCase();
        return d === 'reservation payment' || d === 'reservation fee';
      } catch (e) {
        return false;
      }
    }

    // Load applicant info (syid, waiver flag, paid app fee, interviewed) for this user id
    vm.loadApplicantInfo = function () {
      try {
        if (!ApplicantsService || !vm.id) return $q.when();
        return ApplicantsService.show(vm.id).then(function (body) {
          var d = body && body.data ? body.data : body;
          if (!d) { vm.applicantSyid = null; vm.appWaiveAppFee = null; vm.paidApplicationFee = null; vm.interviewed = false; return; }
          var syid = d.syid != null ? parseInt(d.syid, 10) : null;
          vm.applicantSyid = (isFinite(syid) ? syid : null);
          vm.appWaiveAppFee = !!(d.waive_application_fee === true || d.waive_application_fee === 1 || d.waive_application_fee === '1');
          // Capture applicant flags
          vm.paidApplicationFee = (d.paid_application_fee === true || d.paid_application_fee === 1 || d.paid_application_fee === '1');
          vm.interviewed = !!d.interviewed;
        }).catch(function () {
          vm.applicantSyid = null;
          vm.appWaiveAppFee = null;
          vm.paidApplicationFee = null;
          vm.interviewed = false;
        });
      } catch (e) {
        vm.applicantSyid = null;
        vm.appWaiveAppFee = null;
        vm.paidApplicationFee = null;
        vm.interviewed = false;
        return $q.when();
      }
    };

    // Load student billing for current student+term and detect Application Fee billing
    vm.loadBilling = function (force) {
      var hasId = vm.student && vm.student.id != null;
      if (!vm.term || !hasId) return $q.when();
      var key = 'id:' + vm.student.id;
      if (!force && vm._last && vm._last.billing && vm._last.billing.key === key && vm._last.billing.term === vm.term) {
        return $q.when();
      }
      if (!StudentBillingService || !StudentBillingService.list) return $q.when();

      return StudentBillingService.list({ student_id: vm.student.id, term: vm.term })
        .then(function (body) {
          var list = body && body.data ? body.data : (Array.isArray(body) ? body : []);
          list = Array.isArray(list) ? list.slice() : [];
          vm.billingItems = list;
          vm.hasAppBilling = false;
          vm.hasReservationBilling = false;
          for (var i = 0; i < list.length; i++) {
            var it = list[i] || {};
            var desc = it.description || it.name || it.code || '';
            if (!vm.hasAppBilling && _isApplicationFeeDesc(desc)) vm.hasAppBilling = true;
            if (!vm.hasReservationBilling && _isReservationFeeDesc(desc)) vm.hasReservationBilling = true;
            if (vm.hasAppBilling && vm.hasReservationBilling) break;
          }
          vm._last.billing = { key: key, term: vm.term };
        })
        .catch(function () {
          vm.billingItems = [];
          vm.hasAppBilling = false;
          vm.hasReservationBilling = false;
        });
    };

    // Check invoices for an Application Fee invoice (from billing type invoices with matching item)
    vm.hasAppInvoice = function () {
      try {
        var list = Array.isArray(vm.invoices) ? vm.invoices : [];
        for (var i = 0; i < list.length; i++) {
          var inv = list[i] || {};
          // Restrict to billing invoices when type present; otherwise scan any
          if (inv.type && ('' + inv.type).toLowerCase() !== 'billing') {
            // continue scanning all types in case backend doesn't tag; do not 'continue' strictly
          }
          var items = [];
          if (Array.isArray(inv.items)) items = inv.items;
          else if (Array.isArray(inv.invoice_items)) items = inv.invoice_items;
          for (var j = 0; j < items.length; j++) {
            var it = items[j] || {};
            var desc = it.description || it.name || it.code || '';
            if (_isApplicationFeeDesc(desc)) return true;
          }
        }
        return false;
      } catch (e) {
        return false;
      }
    };
    // Check invoices for a Reservation Fee invoice
    vm.hasReservationInvoice = function () {
      try {
        var list = Array.isArray(vm.invoices) ? vm.invoices : [];
        for (var i = 0; i < list.length; i++) {
          var inv = list[i] || {};
          // Prefer billing type but still scan all if not tagged
          var items = [];
          if (Array.isArray(inv.items)) items = inv.items;
          else if (Array.isArray(inv.invoice_items)) items = inv.invoice_items;
          for (var j = 0; j < items.length; j++) {
            var it = items[j] || {};
            var desc = it.description || it.name || it.code || '';
            if (_isReservationFeeDesc(desc)) return true;
          }
        }
        return false;
      } catch (e) {
        return false;
      }
    };

    // Guard visibility for Generate Application Fee button
    vm.canShowGenerateApplicationFee = function () {
      try {
        // Ensure editor capability and cashier context
        if (!vm.canEdit) return false;
        if (!vm.myCashier || !vm.myCashier.id) return false;
        if (!vm.student || !vm.student.id) return false;
        if (!vm.term) return false;

        // Applicant term must match selected term
        var appTerm = vm.applicantSyid != null ? parseInt(vm.applicantSyid, 10) : null;
        var selTerm = vm.term != null ? parseInt(vm.term, 10) : null;
        if (!isFinite(appTerm) || !isFinite(selTerm) || appTerm !== selTerm) return false;

        // Waiver must be false (0)
        if (vm.appWaiveAppFee === true) return false;

        // Both billing and invoice for Application Fee must be missing
        if (vm.hasAppBilling) return false;
        if (typeof vm.hasAppInvoice === 'function' && vm.hasAppInvoice()) return false;

        return true;
      } catch (e) {
        return false;
      }
    };
    // Guard visibility for Generate Reservation Fee button
    vm.canShowGenerateReservationFee = function () {
      try {
        if (!vm.canEdit) return false;
        if (!vm.myCashier || !vm.myCashier.id) return false;
        if (!vm.student || !vm.student.id) return false;
        if (!vm.term) return false;

        // Applicant term must match selected term
        var appTerm = vm.applicantSyid != null ? parseInt(vm.applicantSyid, 10) : null;
        var selTerm = vm.term != null ? parseInt(vm.term, 10) : null;
        if (!isFinite(appTerm) || !isFinite(selTerm) || appTerm !== selTerm) return false;

        // Prerequisites: paid application fee AND interviewed
        if (!vm.paidApplicationFee) return false;
        if (!vm.interviewed) return false;

        // Both billing and invoice for Reservation Fee must be missing
        if (vm.hasReservationBilling) return false;
        if (typeof vm.hasReservationInvoice === 'function' && vm.hasReservationInvoice()) return false;

        return true;
      } catch (e) {
        return false;
      }
    };

    // Generate Application Fee billing and invoice
    vm.generateApplicationPayment = function () {
      if (!vm.canShowGenerateApplicationFee()) return $q.when();
      vm.generatingAppFee = true;
      vm.appFeeGenerateError = null;
      try {
        // Resolve amount from PaymentDescriptions index if present
        var amt = null;
        try {
          var idx = vm.paymentDescriptionsIndex || {};
          var ap = idx['application payment'] || idx['application fee'] || null;
          if (ap && ap.amount != null) {
            var n = parseFloat(ap.amount);
            if (isFinite(n)) amt = n;
          }
        } catch (_eAmt) {}

        if (!isFinite(amt) || amt <= 0) {
          vm.appFeeGenerateError = 'Application Payment amount is not configured. Please set an amount in Payment Descriptions.';
          vm.generatingAppFee = false;
          return $q.when();
        }

        // Build posted_at now string
        var postedAt = null;
        try {
          var d = new Date();
          function pad(n) { return n < 10 ? ('0' + n) : ('' + n); }
          postedAt = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
        } catch (e2) {}

        var payload = {
          student_id: vm.student.id,
          term: vm.term,
          description: 'Application Payment',
          amount: Math.floor(parseFloat(amt) * 100) / 100,
          posted_at: postedAt,
          remarks: 'Application fee',
          generate_invoice: true
        };

        return StudentBillingService.create(payload)
          .then(function () {
            // Refresh dependent panels
            return $q.when()
              .then(function () { return vm.loadBilling(true); })
              .then(function () { return vm.loadInvoices(true); })
              .then(function () { return vm.loadPaymentDetails(true); });
          })
          .catch(function (err) {
            var msg = 'Failed to generate Application Fee.';
            try {
              if (err && err.data && err.data.message) msg = err.data.message;
              else if (err && err.message) msg = err.message;
            } catch (_e3) {}
            vm.appFeeGenerateError = msg;
          })
          .finally(function () {
            vm.generatingAppFee = false;
          });
      } catch (e) {
        vm.generatingAppFee = false;
        return $q.when();
      }
    };

    // Generate Reservation Fee billing and invoice
    vm.generateReservationPayment = function () {
      if (!vm.canShowGenerateReservationFee()) return $q.when();
      vm.generatingResFee = true;
      vm.resFeeGenerateError = null;
      try {
        // Resolve amount from PaymentDescriptions index if present
        var amt = null;
        try {
          var idx = vm.paymentDescriptionsIndex || {};
          var rp = idx['reservation payment'] || idx['reservation fee'] || null;
          if (rp && rp.amount != null) {
            var n = parseFloat(rp.amount);
            if (isFinite(n)) amt = n;
          }
        } catch (_eAmt) {}

        if (!isFinite(amt) || amt <= 0) {
          vm.resFeeGenerateError = 'Reservation Payment amount is not configured. Please set an amount in Payment Descriptions.';
          vm.generatingResFee = false;
          return $q.when();
        }

        // Build posted_at now string
        var postedAt = null;
        try {
          var d = new Date();
          function pad(n) { return n < 10 ? ('0' + n) : ('' + n); }
          postedAt = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
        } catch (e2) {}

        var payload = {
          student_id: vm.student.id,
          term: vm.term,
          description: 'Reservation Payment',
          amount: Math.floor(parseFloat(amt) * 100) / 100,
          posted_at: postedAt,
          remarks: 'Reservation fee',
          generate_invoice: true
        };

        return StudentBillingService.create(payload)
          .then(function () {
            // Refresh dependent panels
            return $q.when()
              .then(function () { return vm.loadBilling(true); })
              .then(function () { return vm.loadInvoices(true); })
              .then(function () { return vm.loadPaymentDetails(true); });
          })
          .catch(function (err) {
            var msg = 'Failed to generate Reservation Fee.';
            try {
              if (err && err.data && err.data.message) msg = err.data.message;
              else if (err && err.message) msg = err.message;
            } catch (_e3) {}
            vm.resFeeGenerateError = msg;
          })
          .finally(function () {
            vm.generatingResFee = false;
          });
      } catch (e) {
        vm.generatingResFee = false;
        return $q.when();
      }
    };

    // =========================
    // Tuition Invoice (Generate)
    // =========================
    vm.tuitionInvoice = null;     // existing tuition invoice linked to current registration (type: 'tuition')
    vm.generatingInvoice = false; // UI flag during invoice generation
    vm.invoiceGenerateError = null; // error message shown when invoice generation fails

    vm.canShowGenerateInvoice = function () {
      try {
        return !!(vm.canEdit &&
          vm.registration && vm.registration.intRegistrationID &&
          !vm.tuitionInvoice);
      } catch (e) {
        return false;
      }
    };

    vm.checkTuitionInvoice = function () {
      try {
        if (!vm.registration || !vm.registration.intRegistrationID) {
          vm.tuitionInvoice = null;
          return $q.when();
        }
        var regId = parseInt(vm.registration.intRegistrationID, 10);
        if (!isFinite(regId) || regId <= 0) {
          vm.tuitionInvoice = null;
          return $q.when();
        }
        return UnityService.invoicesList({ registration_id: regId, type: 'tuition' })
          .then(function (body) {
            var data = body && body.data ? body.data : body;
            var list = Array.isArray(data) ? data : (data && data.items ? data.items : []);
            vm.tuitionInvoice = (list && list.length) ? list[0] : null;
          })
          .catch(function () {
            vm.tuitionInvoice = null;
          });
      } catch (e) {
        vm.tuitionInvoice = null;
        return $q.when();
      }
    };

    vm.generateTuitionInvoice = function () {
      if (!vm.canShowGenerateInvoice()) return $q.when();
      vm.generatingInvoice = true;
      vm.invoiceGenerateError = null;
      try {
        var regId = parseInt(vm.registration.intRegistrationID, 10);

        // Determine amount from selected tuition amount or fallback heuristics
        var amount = null;
        function toNum(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }
        if (toNum(vm.selectedTuitionAmount) !== null) {
          amount = toNum(vm.selectedTuitionAmount);
        } else {
          try {
            var sd = vm.tuitionSaved && vm.tuitionSaved.payload ? vm.tuitionSaved.payload : null;
            var td = vm.tuition || null;
            if (sd) {
              var ssum = sd.summary || {};
              var sInst = ssum.installments || {};
              var candidatesS = [sd.total, ssum.total_due, ssum.total, ssum.grand_total, sd.total_installment, sInst.total_installment, (ssum.total_installment || null)];
              for (var i = 0; i < candidatesS.length; i++) { var nv = toNum(candidatesS[i]); if (nv !== null) { amount = nv; break; } }
            }
            if (amount === null && td) {
              var summary = td.summary || {};
              var inst = summary.installments || {};
              var candidatesT = [td.total, summary.total_due, summary.total, summary.grand_total, td.total_installment, inst.total_installment, (summary.total_installment || null)];
              for (var j = 0; j < candidatesT.length; j++) { var nv2 = toNum(candidatesT[j]); if (nv2 !== null) { amount = nv2; break; } }
            }
          } catch (e2) {}
        }

        // Build minimal items when amount present so payload has content
        var items = [];
        if (toNum(amount) !== null) {
          items.push({ description: 'Tuition Fee', amount: toNum(amount) });
        }

        // Build posted_at now string Y-m-d H:i:s
        var postedAt = null;
        try {
          var d = new Date();
          function pad(n) { return n < 10 ? ('0' + n) : ('' + n); }
          postedAt = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
        } catch (e3) { postedAt = null; }

        var payload = {
          type: 'tuition',
          student_id: vm.student && vm.student.id ? vm.student.id : null,
          term: vm.term,
          registration_id: regId,
          status: 'Draft',
          remarks: 'Tuition invoice'
        };
        if (toNum(amount) !== null) payload.amount = toNum(amount);
        if (items.length) payload.items = items;

        // campus/cashier context
        try {
          var cid = vm.student && vm.student.campus_id != null ? parseInt(vm.student.campus_id, 10) : null;
          if (isFinite(cid)) payload.campus_id = cid;
        } catch (e4) {}
        try {
          var cashierId = vm.myCashier && vm.myCashier.id != null ? parseInt(vm.myCashier.id, 10) : null;
          if (isFinite(cashierId)) payload.cashier_id = cashierId;
        } catch (e5) {}
        if (postedAt) payload.posted_at = postedAt;

        return UnityService.invoicesGenerate(payload)
          .then(function (body) {
            var inv = body && body.data ? body.data : body;
            vm.tuitionInvoice = inv || null;
            try { if (typeof vm.loadInvoices === 'function') vm.loadInvoices(true); } catch (e) {}
          })
          .catch(function (err) {
            var msg = 'Failed to generate invoice.';
            try {
              if (err && err.data && err.data.message) {
                msg = err.data.message;
              } else if (err && err.message) {
                msg = err.message;
              }
            } catch (e) {}
            vm.invoiceGenerateError = msg;
          })
          .finally(function () {
            vm.generatingInvoice = false;
          });
      } catch (e) {
        vm.generatingInvoice = false;
        return $q.when();
      }
    };

    // Controls (editable fields)
    vm.edit = {
      paymentType: null,
      tuition_year: null,
      allow_enroll: null,
      downpayment: null,
      enrollment_status: null
    };

    // Helpers
    vm.currency = function (num) {
      var n = parseFloat(num || 0);
      var s = n.toFixed(2);
      return s.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    // UI: Tuition Breakdown modal state and helpers
    vm.ui = vm.ui || { showTuitionModal: false, showInvoiceModal: false, showAssignNumberModal: false };

    vm.openTuitionModal = function () {
      vm.ui.showTuitionModal = true;
    };

    vm.closeTuitionModal = function () {
      vm.ui.showTuitionModal = false;
    };

    // Invoice Details modal state and helpers
    vm.invoiceModal = {
      invoice: null,
      payments: [],
      items: [],
      totals: { total: 0, paid: 0, remaining: 0 }
    };

    // Track printing state per invoice id
    vm.printing = {};
    vm.isPrinting = function (idOrInv) {
      try {
        var id = null;
        if (idOrInv && typeof idOrInv === 'object' && idOrInv.id != null) {
          id = parseInt(idOrInv.id, 10);
        } else if (idOrInv != null) {
          id = parseInt(idOrInv, 10);
        }
        if (!isFinite(id)) return false;
        return !!vm.printing[id];
      } catch (e) {
        return false;
      }
    };

    vm.openInvoiceModal = function (inv) {
      try {
        vm.invoiceModal = vm.invoiceModal || {};
        vm.invoiceModal.invoice = inv || null;

        // Resolve invoice number and total
        var invNo = (inv && (inv.invoice_number || inv.number)) ? ('' + (inv.invoice_number || inv.number)).trim() : '';
        var total = null;
        var cands = [inv && inv.amount_total, inv && inv.amount, inv && inv.total];
        for (var i = 0; i < cands.length; i++) {
          var v = parseFloat(cands[i]);
          if (isFinite(v)) { total = v; break; }
        }

        // Collect related payments (any status) and compute paid sum for Paid status
        var rows = (vm.paymentDetails && vm.paymentDetails.items) ? (vm.paymentDetails.items.slice() || []) : [];
        var payments = [];
        var paid = 0;
        for (var j = 0; j < rows.length; j++) {
          var p = rows[j];
          if (!p) continue;
          var pInv = (p.invoice_number != null) ? ('' + p.invoice_number).trim() : '';
          if (invNo && pInv && pInv === invNo) {
            payments.push(p);
            if (p.status === 'Paid') {
              var amt = parseFloat(p.subtotal_order);
              paid += isFinite(amt) ? amt : 0;
            }
          }
        }

        // Sort payments by date desc
        payments.sort(function (a, b) {
          function toTs(x) {
            var s = x || '';
            return new Date(s).getTime() || 0;
          }
          var ad = toTs(a && (a.or_date || a.posted_at));
          var bd = toTs(b && (b.or_date || b.posted_at));
          return bd - ad;
        });

        // Invoice items if present on the invoice payload
        var items = [];
        try {
          if (inv && Array.isArray(inv.items)) items = inv.items.slice();
          else if (inv && Array.isArray(inv.invoice_items)) items = inv.invoice_items.slice();
        } catch (_e) { items = []; }

        var remaining = (isFinite(total) ? (total - paid) : null);
        if (remaining != null && remaining < 0) remaining = 0;

        vm.invoiceModal.payments = payments;
        vm.invoiceModal.items = items;
        vm.invoiceModal.totals = {
          total: isFinite(total) ? total : 0,
          paid: paid,
          remaining: (remaining != null ? remaining : 0)
        };

        vm.ui.showInvoiceModal = true;
      } catch (e) {
        vm.ui.showInvoiceModal = true;
      }
    };

    vm.closeInvoiceModal = function () {
      vm.ui.showInvoiceModal = false;
    };

    // Build admin headers for PDF request (inject X-Faculty-ID when available)
    vm._adminHeaders = function () {
      try {
        var state = vm.state || (StorageService && StorageService.getJSON ? StorageService.getJSON('loginState') : null);
        var headers = {};
        if (state && state.faculty_id != null) {
          headers['X-Faculty-ID'] = state.faculty_id;
        }
        return headers;
      } catch (e) {
        return {};
      }
    };

    // Trigger invoice PDF download
    vm.printInvoice = function (inv) {
      try {
        inv = inv || (vm.invoiceModal && vm.invoiceModal.invoice) || null;
        if (!inv || inv.id == null) return $q.when();

        var id = parseInt(inv.id, 10);
        if (!isFinite(id)) return $q.when();

        // Prevent duplicate clicks
        vm.printing = vm.printing || {};
        if (vm.printing[id]) return $q.when();
        vm.printing[id] = true;

        var url = (APP_CONFIG && APP_CONFIG.API_BASE ? APP_CONFIG.API_BASE : '') + '/finance/invoices/' + id + '/pdf';
        return $http.get(url, {
          responseType: 'arraybuffer',
          headers: vm._adminHeaders()
        }).then(function (resp) {
          try {
            var blob = new Blob([resp.data], { type: 'application/pdf' });
            var link = document.createElement('a');
            var num = inv.invoice_number || inv.number || id;
            link.href = window.URL.createObjectURL(blob);
            link.download = 'invoice-' + num + '.pdf';
            document.body.appendChild(link);
            link.click();
            setTimeout(function () {
              try { document.body.removeChild(link); } catch (_e) {}
              try { window.URL.revokeObjectURL(link.href); } catch (_e2) {}
            }, 0);
          } catch (e) {
            // fallback open in new tab
            try {
              var blob2 = new Blob([resp.data], { type: 'application/pdf' });
              var url2 = window.URL.createObjectURL(blob2);
              window.open(url2, '_blank');
              setTimeout(function () { try { window.URL.revokeObjectURL(url2); } catch (_e3) {} }, 1000);
            } catch (e2) {}
          }
        }).catch(function () {
          // no-op; could surface toast if available
        }).finally(function () {
          try { vm.printing[id] = false; } catch (_e4) {}
        });
      } catch (e) {
        try {
          if (inv && inv.id != null) vm.printing[parseInt(inv.id, 10)] = false;
        } catch (_e5) {}
        return $q.when();
      }
    };

    // =========================
    // Assign Number Modal (OR/Invoice)
    // =========================
    vm.ui = vm.ui || {};
if (vm.ui.showAssignNumberModal == null) vm.ui.showAssignNumberModal = false;
    vm.assignNumberPayment = null;
    vm.assignNumber = { type: null, customNumber: null, selectedInvoiceId: null, selectedInvoiceNumber: null };
    vm.assignNumberError = null;
    if (!vm.loading) vm.loading = {};
    vm.loading.assignNumber = false;

    vm.canShowAssignButton = function (p) {
      try {
        if (!vm.canEdit) return false;
        if (!vm.myCashier || !vm.myCashier.id) return false;
        if (!p) return false;
        // Show when at least one of the number fields is missing
        return (!p.or_no || !p.invoice_number);
      } catch (e) { return false; }
    };

    vm.openAssignNumberModal = function (payment, preferredType) {
      try {
        vm.assignNumberPayment = payment || null;
        vm.assignNumberError = null;
        var t = preferredType || null;
        if (!t) {
          // Prefer assigning what is missing; default to 'or' when both missing
          var hasOr = !!(payment && payment.or_no);
          var hasInv = !!(payment && payment.invoice_number);
          if (!hasOr && hasInv) t = 'or';
          else if (hasOr && !hasInv) t = 'invoice';
          else t = 'or';
        }
        vm.assignNumber = { type: t, customNumber: null, selectedInvoiceId: null, selectedInvoiceNumber: null };
        vm.ui.showAssignNumberModal = true;
      } catch (e) {
        vm.assignNumberPayment = null;
        vm.assignNumber = { type: null, customNumber: null, selectedInvoiceId: null, selectedInvoiceNumber: null };
        vm.assignNumberError = null;
        vm.ui.showAssignNumberModal = true;
      }
    };

    vm.closeAssignNumberModal = function () {
      vm.ui.showAssignNumberModal = false;
      vm.assignNumberPayment = null;
      vm.assignNumber = { type: null, customNumber: null, selectedInvoiceId: null, selectedInvoiceNumber: null };
      vm.assignNumberError = null;
    };

    vm.hasAvailableNumbers = function () {
      try {
        var t = vm.assignNumber && vm.assignNumber.type;
        if (t === 'or') return !!(vm.myCashier && vm.myCashier.or && (vm.myCashier.or.current != null));
        if (t === 'invoice') return !!(vm.myCashier && vm.myCashier.invoice && (vm.myCashier.invoice.current != null));
        return false;
      } catch (e) { return false; }
    };

    vm.canAssignNumber = function () {
      try {
        if (!vm.canEdit) return false;
        if (!vm.myCashier || !vm.myCashier.id) return false;
        if (!vm.assignNumberPayment) return false;
        var t = vm.assignNumber && vm.assignNumber.type;
        if (t !== 'or' && t !== 'invoice') return false;
        if (vm.loading && vm.loading.assignNumber) return false;

        // Do not allow assigning a type that already exists on the row
        var row = vm.assignNumberPayment || {};
        if (t === 'or' && row.or_no) return false;
        if (t === 'invoice' && row.invoice_number) return false;

        // Either has an available next number or a valid customNumber (positive integer and within range when defined)
        var custom = vm.assignNumber && vm.assignNumber.customNumber;
        var hasCustom = (custom != null && custom !== '' && isFinite(parseFloat(custom)) && parseFloat(custom) > 0);
        if (hasCustom) {
          var n = Math.floor(parseFloat(custom));
          // enforce integer
          if (n !== parseFloat(custom)) return false;
          var st = null, en = null;
          if (t === 'or') {
            st = vm.myCashier && vm.myCashier.or && vm.myCashier.or.start;
            en = vm.myCashier && vm.myCashier.or && vm.myCashier.or.end;
          } else if (t === 'invoice') {
            st = vm.myCashier && vm.myCashier.invoice && vm.myCashier.invoice.start;
            en = vm.myCashier && vm.myCashier.invoice && vm.myCashier.invoice.end;
          }
          st = (st != null && isFinite(parseInt(st, 10))) ? parseInt(st, 10) : null;
          en = (en != null && isFinite(parseInt(en, 10))) ? parseInt(en, 10) : null;
          if (st != null && n < st) return false;
          if (en != null && n > en) return false;
        }
        // Allow assigning when an invoice is explicitly selected in the modal
        var hasSelectedInvoice = false;
        if (t === 'invoice') {
          try {
            var sel = vm.assignNumber && vm.assignNumber.selectedInvoiceId != null ? parseInt(vm.assignNumber.selectedInvoiceId, 10) : null;
            hasSelectedInvoice = isFinite(sel);
          } catch (_e) { hasSelectedInvoice = false; }
        }
        return vm.hasAvailableNumbers() || hasCustom || hasSelectedInvoice;
      } catch (e) { return false; }
    };

    vm.confirmAssignNumber = function () {
      if (!vm.canAssignNumber()) return $q.when();
      vm.loading.assignNumber = true;
      vm.assignNumberError = null;

      try {
        var row = vm.assignNumberPayment || {};
        var id = row && row.id != null ? parseInt(row.id, 10) : null;
        if (!isFinite(id)) {
          vm.assignNumberError = 'This payment row has no identifier; cannot assign number.';
          vm.loading.assignNumber = false;
          return $q.when();
        }

        var t = vm.assignNumber && vm.assignNumber.type;
        // Prevent assigning a number type that already exists on the selected row
        if (t === 'or' && row && row.or_no) {
          vm.assignNumberError = 'This payment already has an OR number.';
          vm.loading.assignNumber = false;
          return $q.when();
        }
        if (t === 'invoice' && row && row.invoice_number) {
          vm.assignNumberError = 'This payment already has an Invoice number.';
          vm.loading.assignNumber = false;
          return $q.when();
        }

        var custom = vm.assignNumber && vm.assignNumber.customNumber;
        // Validate custom integer and range when provided
        if (custom != null && custom !== '' && isFinite(parseFloat(custom))) {
          var n = Math.floor(parseFloat(custom));
          if (n !== parseFloat(custom)) {
            vm.assignNumberError = 'Custom number must be a whole number.';
            vm.loading.assignNumber = false;
            return $q.when();
          }
          var st = null, en = null;
          if (t === 'or') { st = vm.myCashier && vm.myCashier.or && vm.myCashier.or.start; en = vm.myCashier && vm.myCashier.or && vm.myCashier.or.end; }
          else if (t === 'invoice') { st = vm.myCashier && vm.myCashier.invoice && vm.myCashier.invoice.start; en = vm.myCashier && vm.myCashier.invoice && vm.myCashier.invoice.end; }
          st = (st != null && isFinite(parseInt(st, 10))) ? parseInt(st, 10) : null;
          en = (en != null && isFinite(parseInt(en, 10))) ? parseInt(en, 10) : null;
          if (st != null && n < st) {
            vm.assignNumberError = 'Custom number is below your assigned range (' + st + (en != null ? ('–' + en) : '') + ').';
            vm.loading.assignNumber = false;
            return $q.when();
          }
          if (en != null && n > en) {
            vm.assignNumberError = 'Custom number is above your assigned range (' + (st != null ? st : '—') + '–' + en + ').';
            vm.loading.assignNumber = false;
            return $q.when();
          }
        }

        var num = null;
        // Prefer selected invoice number when assigning invoice number
        if (t === 'invoice') {
          var selId = vm.assignNumber && vm.assignNumber.selectedInvoiceId != null ? parseInt(vm.assignNumber.selectedInvoiceId, 10) : null;
          if (isFinite(selId)) {
            try {
              var listSel = Array.isArray(vm.invoices) ? vm.invoices : [];
              for (var si = 0; si < listSel.length; si++) {
                var invSel = listSel[si];
                var idSel = invSel && invSel.id != null ? parseInt(invSel.id, 10) : null;
                if (isFinite(idSel) && idSel === selId) { num = (invSel.invoice_number || invSel.number || null); break; }
              }
            } catch (_eSel) { num = null; }
          }
        }
        if (!num) {
          if (custom != null && custom !== '' && isFinite(parseFloat(custom))) {
            num = ('' + custom).trim();
          } else {
            try {
              if (t === 'or') num = (vm.myCashier && vm.myCashier.or && vm.myCashier.or.current != null) ? ('' + vm.myCashier.or.current).trim() : null;
              else if (t === 'invoice') num = (vm.myCashier && vm.myCashier.invoice && vm.myCashier.invoice.current != null) ? ('' + vm.myCashier.invoice.current).trim() : null;
            } catch (_e) { num = null; }
          }
        }
        if (!num) {
          vm.assignNumberError = 'No number available. Please enter a custom number or check your assigned range.';
          vm.loading.assignNumber = false;
          return $q.when();
        }

        var payload = {};
        if (t === 'or') payload.or_no = num;
        else if (t === 'invoice') payload.invoice_number = ('' + num).trim();

        return AdminPaymentDetailsService.update(id, payload)
          .then(function () {
            vm.closeAssignNumberModal();
            // Refresh payments and invoices to reflect changes
            return $q.when()
              .then(function () { return vm.loadPaymentDetails(true); })
              .then(function () { return vm.loadInvoices(true); })
              .then(function () {
                // Recompute invoice remaining if a selection exists
                try {
                  if (vm.payment && vm.payment.invoice_id != null && typeof vm.computeInvoiceRemaining === 'function') {
                    vm.computeInvoiceRemaining();
                  }
                } catch (_e2) {}
                return null;
              });
          })
          .catch(function (err) {
            var msg = 'Failed to assign number.';
            try {
              if (err && err.data && err.data.message) msg = err.data.message;
              else if (err && err.message) msg = err.message;
            } catch (_e3) {}
            vm.assignNumberError = msg;
          })
          .finally(function () {
            vm.loading.assignNumber = false;
          });
      } catch (e) {
        vm.loading.assignNumber = false;
        return $q.when();
      }
    };
    // Payment Modes (dropdown source)
    vm.paymentModes = [];
    vm.loadPaymentModes = function () {
      try {
        // Resolve service defensively to avoid DI-related stalls
        var svc = null;
        try { svc = PaymentModesService; } catch (e) {}
        if (!svc && $injector && typeof $injector.has === 'function' && $injector.has('PaymentModesService')) {
          svc = $injector.get('PaymentModesService');
        }
        if (!svc || !svc.list) {
          // Fallback: keep empty list but do not break bootstrap chain
          vm.paymentModes = vm.paymentModes || [];
          return $q.when(vm.paymentModes);
        }
        return svc.list({ is_active: 1, per_page: 1000 })
          .then(function (res) {
            var items = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
            vm.paymentModes = Array.isArray(items) ? items : [];
          })
          .catch(function () {
            vm.paymentModes = vm.paymentModes || [];
          });
      } catch (e) {
        vm.paymentModes = vm.paymentModes || [];
        return $q.when(vm.paymentModes);
      }
    };
 
    // Payment Descriptions (dropdown source)
    vm.paymentDescriptionOptions = [];
    vm.paymentDescriptionsIndex = {};
    vm.loadPaymentDescriptions = function () {
      try {
        // Resolve service defensively
        var svc = null;
        try { svc = PaymentDescriptionsService; } catch (e) {}
        if (!svc && $injector && typeof $injector.has === 'function' && $injector.has('PaymentDescriptionsService')) {
          svc = $injector.get('PaymentDescriptionsService');
        }

        // Defaults required even when DB has none
        var defaults = ['Tuition Fee', 'Reservation Payment', 'Application Payment'];

        function dedup(list) {
          var out = [];
          var seen = {};
          for (var i = 0; i < list.length; i++) {
            var s = ('' + list[i]).trim();
            if (!s) continue;
            var key = s.toLowerCase();
            if (!seen[key]) { seen[key] = true; out.push(s); }
          }
          return out;
        }

        if (!svc || !svc.list) {
          vm.paymentDescriptionOptions = dedup(defaults.concat(vm.paymentDescriptionOptions || []));
          // keep existing index when service is unavailable
          return $q.when(vm.paymentDescriptionOptions);
        }

        return svc.list({ per_page: 1000 })
          .then(function (res) {
            var items = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
            var names = [];
            var index = {};
            if (Array.isArray(items)) {
              for (var i = 0; i < items.length; i++) {
                var it = items[i];
                if (it && it.name) {
                  var nm = ('' + it.name).trim();
                  if (nm) {
                    names.push(nm);
                    var amtRaw = (it.amount != null) ? parseFloat(it.amount) : null;
                    var amt = isFinite(amtRaw) ? amtRaw : null;
                    index[nm.toLowerCase()] = { amount: amt };
                  }
                }
              }
            }
            vm.paymentDescriptionsIndex = index;
            vm.paymentDescriptionOptions = dedup(defaults.concat(names));
          })
          .catch(function () {
            vm.paymentDescriptionsIndex = {};
            vm.paymentDescriptionOptions = dedup(defaults);
          });
      } catch (e) {
        vm.paymentDescriptionsIndex = {};
        vm.paymentDescriptionOptions = ['Tuition Fee', 'Reservation Payment', 'Application Payment'];
        return $q.when(vm.paymentDescriptionOptions);
      }
    };

    // Auto-fill method from selected Payment Mode (use pmethod as value)
    vm.onPaymentModeChange = function () {
      try {
        var mopId = parseInt(vm.payment && vm.payment.mode_of_payment_id, 10);
        if (!isFinite(mopId)) {
          vm.payment.method = null;
          return;
        }
        var list = vm.paymentModes || [];
        for (var i = 0; i < list.length; i++) {
          var m = list[i];
          if (m && (parseInt(m.id, 10) === mopId)) {
            // Prefer pmethod; fallback to method if present
            vm.payment.method = (m.pmethod != null ? ('' + m.pmethod).trim() : (m.method != null ? ('' + m.method).trim() : ''));
            return;
          }
        }
        vm.payment.method = null;
      } catch (e) {
        vm.payment.method = null;
      }
    };

    // Auto-fill amount when selecting a description that comes from payment_descriptions table
    vm.onPaymentDescriptionChange = function () {
      try {
        var desc = (vm.payment && vm.payment.description != null) ? ('' + vm.payment.description).trim() : '';
        if (!desc) return;
        var key = desc.toLowerCase();
        var idx = vm.paymentDescriptionsIndex || {};
        if (Object.prototype.hasOwnProperty.call(idx, key)) {
          var info = idx[key] || {};
          var num = parseFloat(info.amount);
          if (isFinite(num)) {
            vm.payment.amount = num;
            // Ensure not exceeding any active max cap (e.g., selected invoice remaining)
            try { if (typeof vm.onAmountChange === 'function') vm.onAmountChange(); } catch (_e) {}
          }
        }
      } catch (e) {
        // ignore
      }
    };
 
    // Clear invoice selection when mode changes away from 'or'
    vm.onModeChange = function () {
      try {
        var mode = vm.payment && vm.payment.mode ? ('' + vm.payment.mode).toLowerCase() : 'or';
        if (mode !== 'or') {
          vm.payment.invoice_id = null;
          vm.payment.invoice_number = null;
          vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
        }
      } catch (e) {
        vm.payment.invoice_id = null;
        vm.payment.invoice_number = null;
        vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
      }
    };

    // Derive invoice_number from selected invoice id and compute remaining cap
    vm.onInvoiceSelected = function () {
      try {
        var sel = vm.payment && vm.payment.invoice_id != null ? parseInt(vm.payment.invoice_id, 10) : null;
        if (!isFinite(sel)) {
          vm.payment.invoice_number = null;
          vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
          return;
        }
        var list = Array.isArray(vm.invoices) ? vm.invoices : [];
        var matched = null;
        for (var i = 0; i < list.length; i++) {
          var inv = list[i];
          var id = inv && inv.id != null ? parseInt(inv.id, 10) : null;
          if (isFinite(id) && id === sel) {
            matched = inv;
            break;
          }
        }
        if (matched) {
          vm.payment.invoice_number = (matched.invoice_number || matched.number || null);
          // compute remaining cap and clamp amount if needed
          try { if (typeof vm.computeInvoiceRemaining === 'function') vm.computeInvoiceRemaining(); } catch (_e) {}
          try { if (typeof vm.onAmountChange === 'function') vm.onAmountChange(); } catch (_e2) {}
        } else {
          vm.payment.invoice_number = null;
          vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
        }
      } catch (e) {
        vm.payment.invoice_number = null;
        vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
      }
    };
    // Compute remaining amount for selected invoice based on payment_details 'Paid' rows
    vm.computeInvoiceRemaining = function () {
      try {
        var sel = vm.payment && vm.payment.invoice_id != null ? parseInt(vm.payment.invoice_id, 10) : null;
        if (!isFinite(sel)) {
          vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
          return null;
        }
        var invoices = Array.isArray(vm.invoices) ? vm.invoices : [];
        var invObj = null;
        for (var i = 0; i < invoices.length; i++) {
          var inv = invoices[i];
          var id = inv && inv.id != null ? parseInt(inv.id, 10) : null;
          if (isFinite(id) && id === sel) { invObj = inv; break; }
        }
        var invNo = null;
        var total = 0;
        if (invObj) {
          invNo = invObj.invoice_number || invObj.number || null;
          var cands = [invObj.amount_total, invObj.amount, invObj.total];
          for (var j = 0; j < cands.length; j++) {
            var v = parseFloat(cands[j]);
            if (isFinite(v)) { total = v; break; }
          }
        }
        if (!invNo) {
          vm.invoiceCtx = { id: sel, number: null, total: null, paid: 0, remaining: null };
          return null;
        }
        // sum of paid amounts for this invoice number from loaded payment_details
        var items = (vm.paymentDetails && vm.paymentDetails.items) ? vm.paymentDetails.items : [];
        var paid = 0;
        for (var k = 0; k < items.length; k++) {
          var p = items[k];
          if (!p) continue;
          var pInv = p.invoice_number != null ? ('' + p.invoice_number).trim() : '';
          var tgt = ('' + invNo).trim();
          if (pInv && tgt && pInv === tgt && (p.status === 'Paid')) {
            var amt = parseFloat(p.subtotal_order);
            paid += isFinite(amt) ? amt : 0;
          }
        }
        var remaining = total - paid;
        if (!isFinite(remaining)) remaining = null;
        if (remaining != null && remaining < 0) remaining = 0;
        vm.invoiceCtx = { id: sel, number: invNo, total: isFinite(total) ? total : null, paid: paid, remaining: remaining };
        return remaining;
      } catch (e) {
        vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
        return null;
      }
    };
    // Recompute per-invoice paid and remaining from loaded payment_details
    vm.recomputeInvoicesPayments = function () {
      try {
        var items = (vm.paymentDetails && vm.paymentDetails.items) ? vm.paymentDetails.items : [];
        var paidByInv = {};
        for (var i = 0; i < items.length; i++) {
          var p = items[i];
          if (!p) continue;
          if (p.status !== 'Paid') continue;
          var invNo = p.invoice_number != null ? ('' + p.invoice_number).trim() : '';
          if (!invNo) continue;
          var amt = parseFloat(p.subtotal_order);
          if (!isFinite(amt)) amt = 0;
          paidByInv[invNo] = (paidByInv[invNo] || 0) + amt;
        }

        var list = Array.isArray(vm.invoices) ? vm.invoices : [];
        for (var j = 0; j < list.length; j++) {
          var inv = list[j] || {};
          var invNo2 = inv.invoice_number != null ? ('' + inv.invoice_number).trim()
                      : (inv.number != null ? ('' + inv.number).trim() : '');
          var total = null;
          var cands = [inv.amount_total, inv.amount, inv.total];
          for (var k = 0; k < cands.length; k++) {
            var v = parseFloat(cands[k]);
            if (isFinite(v)) { total = v; break; }
          }
          var paid = invNo2 ? (paidByInv[invNo2] || 0) : 0;
          var remaining = (total != null && isFinite(total)) ? (total - paid) : null;
          if (remaining != null && remaining < 0) remaining = 0;

          inv._total = (total != null && isFinite(total)) ? total : null;
          inv._paid = paid;
          inv._remaining = remaining;
        }
        return true;
      } catch (e) {
        return false;
      }
    };
    // Return current max cap for amount (when invoice is selected)
    vm.amountMax = function () {
      try {
        if (vm.payment && vm.payment.invoice_id != null) {
          var rem = vm.invoiceCtx && vm.invoiceCtx.remaining;
          return (rem != null && isFinite(rem)) ? rem : null;
        }
      } catch (e) {}
      return null;
    };
    // Helpers: sanitize amount to at most 2 decimals (truncate, not round)
    vm._toTwoDecimals = function (val) {
      try {
        if (val === null || val === undefined || val === '') return null;
        var n = parseFloat(val);
        if (!isFinite(n)) return null;
        var truncated = Math.floor(n * 100) / 100;
        return truncated;
      } catch (e) {
        return null;
      }
    };

    // Clamp and sanitize amount (enforce 2 decimals, apply max cap if any)
    vm.onAmountChange = function () {
      try {
        var max = (typeof vm.amountMax === 'function') ? vm.amountMax() : null;
        var vRaw = (vm.payment && vm.payment.amount != null) ? vm.payment.amount : null;
        var v = vm._toTwoDecimals(vRaw);
        if (v === null) return;

        // Apply max cap if present
        if (max != null && isFinite(max) && v > (max + 0.00001)) {
          v = Math.floor(max * 100) / 100; // cap and truncate to 2dp
        }

        // Update model only if value changed (to reduce digest churn)
        if (parseFloat(vm.payment.amount) !== v) {
          vm.payment.amount = v;
        }
      } catch (e) {}
    };

    // Finalize sanitation on blur: enforce min and max, and two decimals
    vm.onAmountBlur = function () {
      try {
        var v = vm._toTwoDecimals(vm.payment && vm.payment.amount);
        if (v === null) { vm.payment.amount = null; return; }

        // Enforce min=0.01
        if (v < 0.01) v = 0.01;

        // Apply max cap if present
        var max = (typeof vm.amountMax === 'function') ? vm.amountMax() : null;
        if (max != null && isFinite(max) && v > (max + 0.00001)) {
          v = Math.floor(max * 100) / 100;
        }

        // Ensure two decimals after clamps
        v = Math.floor(v * 100) / 100;

        if (parseFloat(vm.payment.amount) !== v) {
          vm.payment.amount = v;
        }
      } catch (e) {}
    };

    vm.myCashier = null; // resolved cashier row of acting faculty (by faculty_id)
    vm.payment = {
      mode: 'or',              // 'or' | 'invoice'
      amount: null,            // numeric
      description: '',         // string
      remarks: '',             // string (required)
      method: null,            // optional string
      posted_at: null,         // optional string (ISO or 'Y-m-d H:i:s')
      mode_of_payment_id: null, // required: selected payment mode id
      // Optional invoice reference (when mode='or')
      invoice_id: null,        // selected invoice id from invoices table
      invoice_number: null     // derived from selected invoice (display/submit hint)
    };
    // Track selected invoice context (for remaining computation)
    vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
    vm.paymentError = null;
    if (!vm.loading) vm.loading = {};
vm.loading.createPayment = false;
    vm.loading.assignNumber = false;
    vm.assignNumber = { type: null, customNumber: null, selectedInvoiceId: null, selectedInvoiceNumber: null };
    vm.assignNumberPayment = null;
    vm.assignNumberError = null;

    // Handle selection of invoice in Assign Number modal
    vm.onAssignInvoiceSelected = function () {
      try {
        var sel = vm.assignNumber && vm.assignNumber.selectedInvoiceId != null ? parseInt(vm.assignNumber.selectedInvoiceId, 10) : null;
        if (!isFinite(sel)) {
          vm.assignNumber.selectedInvoiceNumber = null;
          return;
        }
        var list = Array.isArray(vm.invoices) ? vm.invoices : [];
        for (var i = 0; i < list.length; i++) {
          var inv = list[i];
          var id = inv && inv.id != null ? parseInt(inv.id, 10) : null;
          if (isFinite(id) && id === sel) {
            vm.assignNumber.selectedInvoiceNumber = inv.invoice_number || inv.number || null;
            return;
          }
        }
        vm.assignNumber.selectedInvoiceNumber = null;
      } catch (e) {
        vm.assignNumber.selectedInvoiceNumber = null;
      }
    };

    // Resolve acting cashier record using current faculty_id (X-Faculty-ID header is handled by service)
    vm.loadMyCashier = function () {
      try {
        // If no login state or faculty id, skip resolution gracefully
        var state = vm.state || StorageService.getJSON('loginState');
        var fid = state && state.faculty_id != null ? parseInt(state.faculty_id, 10) : null;
        if (!fid || isNaN(fid)) {
          vm.myCashier = null;
          return $q.when();
        }

        // First try lightweight self-resolution (allowed for finance/admin via /cashiers/me)
        return CashiersService.me()
          .then(function (body) {
            var data = body && body.data ? body.data : body;
            vm.myCashier = data || null;
            // If still null, fall back to listing (requires cashier_admin/admin)
            if (!vm.myCashier) {
              return CashiersService.list({ includeStats: false }).then(function (res) {
                var items = Array.isArray(res && res.data) ? res.data : (Array.isArray(res) ? res : []);
                if (!items || !items.length) {
                  vm.myCashier = null;
                  return;
                }
                for (var i = 0; i < items.length; i++) {
                  var r = items[i];
                  if (r && r.faculty_id != null && parseInt(r.faculty_id, 10) === fid) {
                    vm.myCashier = r;
                    break;
                  }
                }
                if (!vm.myCashier) vm.myCashier = null;
              }).catch(function () {
                vm.myCashier = null;
              });
            }
            return null;
          })
          .catch(function () {
            // If /cashiers/me is not accessible, try list (may be restricted)
            return CashiersService.list({ includeStats: false })
              .then(function (res) {
                var items = Array.isArray(res && res.data) ? res.data : (Array.isArray(res) ? res : []);
                if (!items || !items.length) {
                  vm.myCashier = null;
                  return;
                }
                for (var i = 0; i < items.length; i++) {
                  var r = items[i];
                  if (r && r.faculty_id != null && parseInt(r.faculty_id, 10) === fid) {
                    vm.myCashier = r;
                    break;
                  }
                }
                if (!vm.myCashier) vm.myCashier = null;
              })
              .catch(function () { vm.myCashier = null; });
          });
      } catch (e) {
        vm.myCashier = null;
        return $q.when();
      }
    };

    vm.resetPaymentForm = function () {
      vm.payment = {
        mode: 'or',
        amount: null,
        description: '',
        remarks: '',
        method: null,
        posted_at: null,
        mode_of_payment_id: null,
        invoice_id: null,
        invoice_number: null
      };
      vm.invoiceCtx = { id: null, number: null, total: null, paid: 0, remaining: null };
      vm.paymentError = null;
    };

    vm.canSubmitPayment = function () {
      try {
        if (!vm.canEdit) return false;
        if (!vm.myCashier || !vm.myCashier.id) return false;
        if (!vm.student || !vm.student.id) return false;
        if (!vm.term) return false;

        var p = vm.payment || {};
        var modeOk = (p.mode === 'or' || p.mode === 'invoice' || p.mode === 'none');
        var amt = parseFloat(p.amount);
        var maxCap = (typeof vm.amountMax === 'function') ? vm.amountMax() : null;
        var amtOk = isFinite(amt) && amt > 0 && (maxCap == null || amt <= (maxCap + 0.00001));
        var descOk = !!(p.description && ('' + p.description).trim().length > 0);
        var remarksOk = !!(p.remarks && ('' + p.remarks).trim().length > 0);
        var mopId = parseInt(p.mode_of_payment_id, 10);
        var mopOk = isFinite(mopId) && mopId > 0;

        return modeOk && amtOk && descOk && remarksOk && mopOk && !vm.loading.createPayment;
      } catch (e) { return false; }
    };

    vm.submitPayment = function () {
      if (!vm.canSubmitPayment()) return $q.when();

      vm.loading.createPayment = true;
      vm.paymentError = null;

      // Build payload
      var p = vm.payment || {};
      var payload = {
        student_id: vm.student.id,
        term: vm.term,
        mode: (p.mode === 'invoice' ? 'invoice' : (p.mode === 'none' ? 'none' : 'or')),
        amount: parseFloat(p.amount),
        convenience_fee: 0,
        description: ('' + p.description).trim(),
        remarks: ('' + p.remarks).trim(),
        mode_of_payment_id: parseInt(p.mode_of_payment_id, 10)
      };
      if (p.method && ('' + p.method).trim().length > 0) {
        payload.method = ('' + p.method).trim();
      }
      // Optional invoice linking when OR mode: include selected invoice id/number (backend may ignore if not supported)
      // When mode is 'none', omit invoice fields
      if (p.mode !== 'none') {
        try {
          var iid = p.invoice_id != null ? parseInt(p.invoice_id, 10) : null;
          if (isFinite(iid)) payload.invoice_id = iid;
        } catch (e) {}
        try {
          if (p.invoice_number && ('' + p.invoice_number).trim().length > 0) {
            payload.invoice_number = ('' + p.invoice_number).trim();
          }
        } catch (e2) {}
      }
      // Default or_date to current date (Y-m-d); posted_at defaults server-side
      try {
        function pad(n) { return n < 10 ? ('0' + n) : ('' + n); }
        var d = new Date();
        var dateStr = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        payload.or_date = dateStr;
      } catch (e) {
        // if formatting fails, omit
      }
      // Optional campus: use student campus when available; otherwise omit
      try {
        if (vm.student && (vm.student.campus_id != null)) {
          var cid = parseInt(vm.student.campus_id, 10);
          if (!isNaN(cid)) payload.campus_id = cid;
        }
      } catch (e) {}

      return CashiersService.createPayment(vm.myCashier.id, payload)
        .then(function () {
          // Reset form and force refresh of dependent panels
          vm.resetPaymentForm();
          // Clear cached 'last' to ensure reloads
          vm._last.payments = null;

          // Refresh cashier pointers so UI shows updated current OR/Invoice numbers
          var p = null;
          try {
            p = (typeof vm.loadMyCashier === 'function') ? vm.loadMyCashier() : null;
          } catch (e) { p = null; }

          return $q.when(p)
            .then(function () { return vm.loadPaymentDetails(true); })
            .then(function () { return vm.loadLedger(); })
            .then(function () {
              if (typeof vm.refreshTuitionSummary === 'function') {
                vm.refreshTuitionSummary();
              }
              // Recompute invoice remaining if a selection exists (so cap reflects updated payments)
              try {
                if (vm.payment && vm.payment.invoice_id != null && typeof vm.computeInvoiceRemaining === 'function') {
                  vm.computeInvoiceRemaining();
                }
              } catch (_e3) {}
              return null;
            });
        })
        .catch(function (err) {
          vm.paymentError = 'Failed to create payment.';
          try {
            if (err && err.data && err.data.message) {
              vm.paymentError = err.data.message;
            } else if (err && err.message) {
              vm.paymentError = err.message;
            }
          } catch (e) {}
        })
        .finally(function () {
          vm.loading.createPayment = false;
        });
    };

    // Compute preview figures for DP30 and DP50 scenarios
    vm.installmentPreview = function (payload) {
      try {
        var p = payload || vm.tuitionPayload() || {};
        var s = p.summary || {};
        function num(x) { var v = parseFloat(x); return isFinite(v) ? v : 0; }
        var tuition = num(s.tuition);
        var misc = num(s.misc_total);
        var lab = num(s.lab_total);
        var additional = num(s.additional_total);
        var scholarships = num(s.scholarships_total);
        var discounts = num(s.discounts_total);

        function compute(mult) {
          var tuitionNew = tuition * mult;
          var miscNew = misc * mult;
          var labNew = lab;
          var additionalNew = additional;
          var totalNew = (tuitionNew + miscNew + labNew + additionalNew) - scholarships - discounts;
          return {
            tuitionNew: tuitionNew,
            miscNew: miscNew,
            labNew: labNew,
            additionalNew: additionalNew,
            scholarships: scholarships,
            discounts: discounts,
            totalNew: totalNew
          };
        }

        return {
          dp30: compute(1.15),
          dp50: compute(1.09)
        };
      } catch (e) {
        return { dp30: null, dp50: null };
      }
    };

    // Recompute selected tuition amount and remaining from current data (tuition/tuitionSaved + paymentType)
    vm.refreshTuitionSummary = function () {
      try {
        var paymentType = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
        var isPartial = paymentType === 'partial';

        // Prefer saved payload when present
        var source = null;
        var total = null;
        var totalInst = null;

        if (vm.tuitionSaved && vm.tuitionSaved.payload) {
          var sd = vm.tuitionSaved.payload || {};
          var ssum = sd.summary || {};
          var sinst = (ssum.installments || {});
          function nS(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }

          var sFullCandidates = [sd.total, ssum.total_due, ssum.total, ssum.grand_total];
          var sInstCandidates = [sd.total_installment, (sinst ? sinst.total_installment : null), (ssum ? ssum.total_installment : null), (sd.meta && sd.meta.installments ? sd.meta.installments.total_installment : null)];

          for (var i = 0; i < sFullCandidates.length; i++) { var fv = nS(sFullCandidates[i]); if (fv !== null) { total = fv; break; } }
          for (var j = 0; j < sInstCandidates.length; j++) { var iv = nS(sInstCandidates[j]); if (iv !== null) { totalInst = iv; break; } }

          source = 'saved';
        } else if (vm.tuition) {
          var td = vm.tuition || {};
          var summary = td.summary || {};
          var installments = summary.installments || {};
          function n(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }

          var fullCandidates = [td.total, summary.total_due, summary.total, summary.grand_total];
          var instCandidates = [td.total_installment, (installments ? installments.total_installment : null), (summary ? summary.total_installment : null), (td.meta && td.meta.installments ? td.meta.installments.total_installment : null)];

          for (var i2 = 0; i2 < fullCandidates.length; i2++) { var fv2 = n(fullCandidates[i2]); if (fv2 !== null) { total = fv2; break; } }
          for (var j2 = 0; j2 < instCandidates.length; j2++) { var iv2 = n(instCandidates[j2]); if (iv2 !== null) { totalInst = iv2; break; } }

          source = 'computed';
        }

        if (total !== null || totalInst !== null) {
          var sel = isPartial ? (totalInst != null ? totalInst : 0) : (total != null ? total : 0);
          if (isFinite(sel)) {
            vm.selectedTuitionAmount = sel;
            if (!vm.meta) vm.meta = {};
            if (source) vm.meta.tuition_source = source;

            // Compute remaining = (total_due + billing_total) - amount_paid
            var payload = (typeof vm.tuitionPayload === 'function') ? vm.tuitionPayload() : (vm.tuitionSaved && vm.tuitionSaved.payload) || vm.tuition || {};
            var sum = payload && payload.summary ? payload.summary : {};
            function _n(x){ var v = parseFloat(x); return isFinite(v) ? v : 0; }
            var totalDue = _n(sum.total_due || payload.total || sum.total || sum.grand_total);
            var billingTotal = _n(payload.billing_total || sum.billing_total || (payload.meta && payload.meta.billing_total));
            vm.meta.billing_total = billingTotal;
            var remain = (totalDue + billingTotal) - _n(vm.meta.amount_paid);
            vm.meta.remaining_amount = isFinite(remain) ? remain : 0;
          }
        }
      } catch (e) {
        // no-op
      }
    };

    var _termChangePromise = null;
    vm.onTermChange = function () {
      // Debounce to prevent burst-triggered duplicate loads
      if (_termChangePromise) {
        $timeout.cancel(_termChangePromise);
      }
      _termChangePromise = $timeout(function () {
        _termChangePromise = null;

        // Allow flows that work with student_id even when student_number is missing (applicants)
        var hasId = vm.student && vm.student.id != null;
        if (!vm.term || (!hasId && !vm.sn)) return;

        // Chain loads: registration/tuition/records/ledger only when student_number is available
        var chain = $q.when();
        if (vm.sn) {
          chain = chain
            .then(vm.loadRegistration)
            .then(vm.loadTuition)
            .then(vm.loadRecords)
            .then(vm.loadLedger);
        }

        // Always try invoices and payment details (they can use student_id)
        chain = chain
          .then(vm.loadInvoices)
          .then(vm.loadPaymentDetails)
          .then(vm.loadBilling)
          .catch(function () { /* errors captured per-call */ });
      }, 150);
    };

    // Build human-readable term label from global term object
    function buildTermLabel(t) {
      try {
        var parts = [];
        if (t.term_student_type) parts.push(t.term_student_type);
        if (t.enumSem) parts.push(t.enumSem);
        if (t.term_label) parts.push(t.term_label);
        if (t.strYearStart && t.strYearEnd) parts.push(t.strYearStart + '-' + t.strYearEnd);
        var label = parts.join(' ').replace(/\s+/g, ' ').trim();
        return label || (t.label || ('SY ' + (t.syid || t.intID || '')));
      } catch (e) {
        return t && (t.label || ('SY ' + (t.syid || t.intID || ''))) || '';
      }
    }

    // Sync controller term with global TermService selection
    function applyGlobalTerm() {
      try {
        var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
        if (sel && sel.intID) {
          var parsed = parseInt(sel.intID, 10);
          vm.term = isFinite(parsed) ? parsed : sel.intID;
          vm.termLabel = buildTermLabel(sel);
        } else {
          vm.term = null;
          vm.termLabel = '';
        }
      } catch (e) {
        vm.term = null;
        vm.termLabel = '';
      }
    }

    // Listen for global term changes from the sidebar and reload page data (with cleanup and bootstrap guard)
    var unbindTermChanged = $rootScope.$on('termChanged', function () {
      if (vm._inBootstrap) return; // ignore during bootstrap to prevent duplicate loads

      // Capture current and updated term
      var prevTerm = vm.term;
      applyGlobalTerm();
      var newTerm = vm.term;

      // Guard: ignore duplicate term events within a suppression window
      try {
        var now = Date.now();
        if (vm._lastTermEvent && vm._lastTermEvent.term === newTerm && (now - vm._lastTermEvent.ts) < 1000) {
          return;
        }
        vm._lastTermEvent = { term: newTerm, ts: now };
      } catch (e) {
        // noop
      }

      // If term didn't actually change, skip noisy triggers
      if (prevTerm === newTerm) {
        return;
      }

      vm.onTermChange();
    });
    $scope.$on('$destroy', function () {
      if (typeof unbindTermChanged === 'function') {
        unbindTermChanged();
      }
    });

    // Data loaders
    // Load students for dropdown - lightweight (single page) to avoid flooding API
    vm.loadStudents = function () {
      try {
        if (StudentsService && typeof StudentsService.listPage === 'function') {
          return StudentsService.listPage({ per_page: 100, page: 1, include_applicants: 1 }).then(function (list) {
            vm.students = Array.isArray(list) ? list : [];
            // Initialize dropdown selection to current route id when first loading
            if (!vm.selectedStudentId && vm.id) {
              vm.selectedStudentId = vm.id;
            }
          }).catch(function () {
            vm.students = vm.students || [];
          });
        }
        // Fallback: direct lightweight request for first page only
        return $http.get(API + '/students', { params: { per_page: 100, page: 1, include_applicants: 1 } })
          .then(function (resp) {
            var data = (resp && resp.data) ? resp.data : {};
            var rows = data && data.data ? data.data : (Array.isArray(data) ? data : []);
            vm.students = (rows || []).map(function (r) {
              return {
                id: r.id != null ? r.id : (r.intID != null ? r.intID : null),
                student_number: r.student_number || r.strStudentNumber || '',
                last_name: r.last_name || r.strLastname || r.lastName || '',
                first_name: r.first_name || r.strFirstname || r.firstName || '',
                middle_name: r.middle_name || r.strMiddlename || r.middleName || ''
              };
            }).filter(function (r) { return r.id !== null; });
            if (!vm.selectedStudentId && vm.id) {
              vm.selectedStudentId = vm.id;
            }
          })
          .catch(function () {
            vm.students = vm.students || [];
          });
      } catch (e) {
        vm.students = vm.students || [];
        return $q.when(vm.students);
      }
    };

    // Dynamic student query for autocomplete (debounced via pui-autocomplete)
    // Fetches first page of results filtered by the user's input.
    vm._studentQCache = {};
    vm.onStudentQuery = function (q) {
      try {
        var text = (q == null ? '' : ('' + q)).trim();
        // Basic cache to avoid refetching same term repeatedly during focus/blur churn
        if (Object.prototype.hasOwnProperty.call(vm._studentQCache, text)) {
          vm.students = Array.isArray(vm._studentQCache[text]) ? vm._studentQCache[text].slice() : [];
          return;
        }
        // Service-driven query (single page small size for suggestions)
        if (StudentsService && typeof StudentsService.listPage === 'function') {
          return StudentsService.listPage({ q: text, per_page: 20, page: 1, include_applicants: 1 })
            .then(function (list) {
              vm._studentQCache[text] = Array.isArray(list) ? list.slice() : [];
              vm.students = Array.isArray(list) ? list : [];
            })
            .catch(function () {
              vm._studentQCache[text] = [];
              vm.students = vm.students || [];
            });
        }
        // Fallback: direct GET to first page with query
        var params = { per_page: 20, page: 1, include_applicants: 1 };
        if (text) params.q = text;
        return $http.get(API + '/students', { params: params })
          .then(function (resp) {
            var data = resp && resp.data ? resp.data : {};
            var rows = data && data.data ? data.data : (Array.isArray(data) ? data : []);
            var items = (rows || []).map(function (r) {
              return {
                id: r.id != null ? r.id : (r.intID != null ? r.intID : null),
                student_number: r.student_number || r.strStudentNumber || '',
                last_name: r.last_name || r.strLastname || r.lastName || '',
                first_name: r.first_name || r.strFirstname || r.firstName || '',
                middle_name: r.middle_name || r.strMiddlename || r.middleName || ''
              };
            }).filter(function (r) { return r.id !== null; });
            vm._studentQCache[text] = items.slice();
            vm.students = items;
          })
          .catch(function () {
            vm._studentQCache[text] = [];
            vm.students = vm.students || [];
          });
      } catch (e) {
        vm.students = vm.students || [];
      }
    };

    vm.loadStudent = function () {
      vm.loading.student = true;
      vm.error.student = null;
      return $http.get(API + '/students/' + vm.id)
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          var obj = null;
          if (data && data.data) obj = data.data;
          else obj = data;
          vm.student = obj || null;

          // Resolve student_number from possible shapes
          if (vm.student) {
            vm.sn = vm.student.student_number || vm.student.strStudentNumber || vm.student.strstudentnumber || null;
            if (vm.student.slug && !vm.student.student_number && !vm.student.strStudentNumber) {
              // keep slug if needed in future; not required by current endpoints
            }
          }

          if (!vm.sn) {
            // Applicants may not have a student_number; continue with student_id for invoices/payment details.
            vm.error.student = null;
          }
        })
        .catch(function () {
          vm.error.student = 'Please select a Student.';
        })
        .finally(function () {
          vm.loading.student = false;
        });
    };


    // Load Tuition Years options for dropdown
    vm.loadTuitionYears = function () {
      // Avoid duplicate loads; tuition year options are global/static enough for this screen
      if (vm.tuitionYearsLoaded) return $q.when(vm.tuitionYearOptions);
      if (vm.loading.tuitionYears) return $q.when();

      vm.loading.tuitionYears = true;
      return TuitionYearsService.list({})
        .then(function (res) {
          var items = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.tuitionYearOptions = (items || []).map(function (r) {
            var id = r.intID || r.id;
            var label = r.year || r.strLabel || ('Tuition Year ' + id);
            var nid = parseInt(id, 10);
            return { id: isNaN(nid) ? id : nid, label: label };
          });
          vm.tuitionYearsLoaded = true;
        })
        .catch(function () {
          // Keep any previously loaded options; only default to [] when nothing exists
          if (!Array.isArray(vm.tuitionYearOptions) || vm.tuitionYearOptions.length === 0) {
            vm.tuitionYearOptions = [];
          }
        })
        .finally(function () {
          vm.loading.tuitionYears = false;
        });
    };

    vm.loadRegistration = function (force) {
      if (!vm.sn || !vm.term) return $q.when();
      // Avoid duplicate loads with same params unless forced
      if (!force && vm._last && vm._last.registration && vm._last.registration.sn === vm.sn && vm._last.registration.term === vm.term) {
        return $q.when();
      }
      if (vm.loading.registration) return $q.when();
      vm.loading.registration = true;
      vm.error.registration = null;

      // Use UnityService to include X-Faculty-ID header automatically
      return UnityService.getRegistration(vm.sn, vm.term)
        .then(function (data) {
          // UnityService._unwrap returns the body (resp.data)
          vm.registrationResp = data || null;
          var exists = data && data.data && data.data.exists === true;
          vm.registration = exists ? (data.data.registration || null) : null;

          // Prefer the student's registered term when no explicit term is pre-selected
          try {
            if (!vm.term && vm.registration && vm.registration.intAYID) {
              var t = parseInt(vm.registration.intAYID, 10);
              if (!isNaN(t) && t > 0) {
                vm.term = t;
              }
            }
          } catch (e) {}

          // Bind editable fields snapshot
          vm.edit.paymentType = vm.registration ? (vm.registration.paymentType || null) : null;
          vm.edit.tuition_year = vm.registration ? (vm.registration.tuition_year || null) : null;
          vm.edit.allow_enroll = vm.registration ? (vm.registration.allow_enroll != null ? parseInt(vm.registration.allow_enroll, 10) : null) : null;
          vm.edit.downpayment = vm.registration ? (vm.registration.downpayment != null ? parseInt(vm.registration.downpayment, 10) : null) : null;
          vm.edit.enrollment_status = vm.registration ? (vm.registration.enrollment_status != null ? parseInt(vm.registration.enrollment_status, 10) : null) : null;
          // Mark last loaded params to prevent duplicate API calls
          vm._last.registration = { sn: vm.sn, term: vm.term };

          // Refresh tuition invoice state for this registration
          if (typeof vm.checkTuitionInvoice === 'function') {
            vm.checkTuitionInvoice();
          }
        })
        .catch(function () {
          vm.error.registration = 'Failed to load registration.';
        })
        .finally(function () {
          vm.loading.registration = false;
        });
    };

    vm.loadTuition = function (force) {
      if (!vm.sn || !vm.term) return $q.when();
      // Avoid duplicate loads with same params unless forced
      if (!force && vm._last && vm._last.tuition && vm._last.tuition.sn === vm.sn && vm._last.tuition.term === vm.term) {
        return $q.when();
      }
      if (vm.loading.tuition) return $q.when();
      vm.loading.tuition = true;
      vm.error.tuition = null;

      // Build params (Laravel ignores tuition_year currently; registration context is used server-side)
      var params = { student_number: vm.sn, student_id: vm.student.id, term: vm.term };

      // Reset saved snapshot state and default source
      vm.tuitionSaved = null;
      vm.meta.tuition_source = 'computed';

      // First: get computed tuition breakdown
      return $http.get(API + '/tuition/compute', { params: params })
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          vm.tuition = (data && data.data) ? data.data : (data || null);

          // Reset meta, then compute from API whenever available
          vm.meta.amount_paid = null; // initialize as null so ledger fallback can decide whether to compute sum
          vm.meta.remaining_amount = 0;
          vm.selectedTuitionAmount = null;

          try {
            var td = vm.tuition || {};
            // Prefer API-provided paid/remaining figures if present
            if (td.meta) {
              if (td.meta.amount_paid != null) vm.meta.amount_paid = parseFloat(td.meta.amount_paid) || 0;
              // Some payloads may carry 'remaining' or 'remaining_amount'
              var remainingApi = td.meta.remaining != null ? td.meta.remaining : td.meta.remaining_amount;
              if (remainingApi != null) vm.meta.remaining_amount = parseFloat(remainingApi) || 0;
            }

            // Normalize TuitionBreakdownResource shape (summary/installments) to flat totals for UI compatibility
            var summary = td.summary || {};
            var installments = summary.installments || {};
            function _num(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }
            // Candidates for full total
            var fullCandidates = [
              td.total,
              summary.total_due,
              summary.total,
              summary.grand_total
            ];
            // Candidates for installment total
            var instCandidates = [
              td.total_installment,
              (installments ? installments.total_installment : null),
              (summary ? summary.total_installment : null),
              (td.meta && td.meta.installments ? td.meta.installments.total_installment : null)
            ];
            var full = null;
            for (var i = 0; i < fullCandidates.length; i++) { var fv = _num(fullCandidates[i]); if (fv !== null) { full = fv; break; } }
            var inst = null;
            for (var j = 0; j < instCandidates.length; j++) { var iv = _num(instCandidates[j]); if (iv !== null) { inst = iv; break; } }
            td.total = full || 0;
            td.total_installment = inst || 0;

            // Extract billing_total for UI
            try {
              var _bt = parseFloat(td.billing_total != null ? td.billing_total : (summary && summary.billing_total != null ? summary.billing_total : (td.meta && td.meta.billing_total)));
              vm.meta.billing_total = isFinite(_bt) ? _bt : 0;
            } catch (_eBt) {
              // keep previous or default to 0
              if (vm.meta && (vm.meta.billing_total == null || !isFinite(vm.meta.billing_total))) vm.meta.billing_total = 0;
            }

            // Determine registered payment type: prefer saved registration over edit snapshot
            var paymentType = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
            var isPartial = paymentType === 'partial';

            // Choose correct tuition total based on registered payment type (computed values)
            var computedSelected = isPartial
              ? (td.total_installment != null ? parseFloat(td.total_installment) : 0)
              : (td.total != null ? parseFloat(td.total) : 0);

            vm.selectedTuitionAmount = computedSelected || 0;

            // If API did not provide remaining, fallback to client computation:
            // remaining = total_due + billing_total - amount_paid
            if (!vm.meta.remaining_amount) {
              var s2 = td.summary || {};
              function _n2(x){ var v = parseFloat(x); return isFinite(v) ? v : 0; }
              var totalDue2 = _n2(s2.total_due || td.total || s2.total || s2.grand_total);
              var billingTotal2 = _n2(td.billing_total || s2.billing_total || (td.meta && td.meta.billing_total));
              vm.meta.billing_total = billingTotal2;
              vm.meta.remaining_amount = (totalDue2 + billingTotal2) - _n2(vm.meta.amount_paid);
              if (!isFinite(vm.meta.remaining_amount)) vm.meta.remaining_amount = 0;
            }
          } catch (e) {}
        })
        .catch(function () {
          // Keep error note for compute, but we will still attempt to fetch saved snapshot
          vm.error.tuition = 'Failed to load tuition breakdown.';
        })
        .then(function () {
          // Second: attempt to fetch saved tuition snapshot via UnityService (adds admin headers)
          return UnityService.tuitionSaved(params);
        })
        .then(function (body) {
          var payload = body && body.data ? body.data : null;
          var exists = payload && payload.exists === true;
          var saved = exists ? (payload.saved || null) : null;

          if (saved && saved.payload) {
            vm.tuitionSaved = saved;

            try {
              // Determine registered payment type again (authoritative)
              var paymentType = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
              var isPartial = paymentType === 'partial';

              var sd = saved.payload || {};
              // Normalize saved payload shape to flat totals
              var ssum = sd.summary || {};
              var sinst = (ssum.installments || {});
              function _numS(x) { var v = parseFloat(x); return isFinite(v) ? v : null; }
              var sFullCandidates = [
                sd.total,
                ssum.total_due,
                ssum.total,
                ssum.grand_total
              ];
              var sInstCandidates = [
                sd.total_installment,
                (sinst ? sinst.total_installment : null),
                (ssum ? ssum.total_installment : null),
                (sd.meta && sd.meta.installments ? sd.meta.installments.total_installment : null)
              ];
              var sFull = null;
              for (var si = 0; si < sFullCandidates.length; si++) { var sfv = _numS(sFullCandidates[si]); if (sfv !== null) { sFull = sfv; break; } }
              var sInst = null;
              for (var sj = 0; sj < sInstCandidates.length; sj++) { var siv = _numS(sInstCandidates[sj]); if (siv !== null) { sInst = siv; break; } }
              sd.total = sFull || 0;
              sd.total_installment = sInst || 0;

              // Extract billing_total for UI from saved payload
              try {
                var _btS = parseFloat(sd.billing_total != null ? sd.billing_total : (ssum && ssum.billing_total != null ? ssum.billing_total : (sd.meta && sd.meta.billing_total)));
                vm.meta.billing_total = isFinite(_btS) ? _btS : (vm.meta.billing_total || 0);
              } catch (_eBtS) {}

              var selectedSaved = isPartial
                ? (sd.total_installment != null ? parseFloat(sd.total_installment) : 0)
                : (sd.total != null ? parseFloat(sd.total) : 0);

              if (isFinite(selectedSaved)) {
                vm.selectedTuitionAmount = selectedSaved;
                vm.meta.tuition_source = 'saved';

                // Recompute remaining based on total_due + billing_total - amount_paid
                var totalDueS = _numS(ssum.total_due || sd.total || ssum.total || ssum.grand_total);
                var billingTotalS = _numS(sd.billing_total || ssum.billing_total || (sd.meta && sd.meta.billing_total));
                var apaid = _numS(vm.meta.amount_paid);
                apaid = isFinite(apaid) ? apaid : 0;
                vm.meta.billing_total = (isFinite(billingTotalS) ? billingTotalS : 0);
                vm.meta.remaining_amount = ((isFinite(totalDueS) ? totalDueS : 0) + (isFinite(billingTotalS) ? billingTotalS : 0)) - apaid;
                if (!isFinite(vm.meta.remaining_amount)) vm.meta.remaining_amount = 0;
              }
            } catch (e) {}
          }
        })
        .catch(function () {
          // Ignore saved snapshot fetch failures; computed values already set if available
        })
        .finally(function () {
          // Mark last loaded params after finishing any tuition requests
          if (vm.sn && vm.term) {
            vm._last.tuition = { sn: vm.sn, term: vm.term };
          }
          vm.loading.tuition = false;
        });
    };

    vm.loadLedger = function () {
      if (!vm.sn) return $q.when();
      vm.loading.ledger = true;
      vm.error.ledger = null;
      return $http.post(API + '/student/ledger', { student_number: vm.sn, student_id: vm.student.id})
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          vm.ledger = (data && data.data) ? data.data : (data || null);

          // compute paid sum; prefer API-provided filtered amounts when available
          try {
            // Always compute transactions sum for comparison UI
            var txs = vm.ledger && vm.ledger.transactions ? vm.ledger.transactions : [];
            var sum = 0;
            for (var i = 0; i < txs.length; i++) {
              var t = txs[i];
              var amt = (t && t.amount != null) ? parseFloat(t.amount) : 0;
              sum += isFinite(amt) ? amt : 0;
            }
            vm.transactionsTotal = sum;

            // If API returns meta.amount_paid specific to current registration/term, use it.
            var apiPaid = vm.ledger && vm.ledger.meta && vm.ledger.meta.amount_paid != null
              ? parseFloat(vm.ledger.meta.amount_paid) : null;

            if (apiPaid != null && isFinite(apiPaid)) {
              vm.meta.amount_paid = apiPaid;
            } else if (vm.meta.amount_paid == null) {
              // Safeguard: do not override amount_paid if tuition/compute already provided it
              vm.meta.amount_paid = sum;
            }
          } catch (e) {}
        })
        .catch(function () {
          vm.error.ledger = 'Failed to load ledger.';
        })
        .finally(function () {
          vm.loading.ledger = false;
        });
    };

    // Invoices loader (for selected term/registration)
    vm.loadInvoices = function (force) {
      var hasId = vm.student && vm.student.id != null;
      if (!vm.term || (!vm.sn && !hasId)) return $q.when();
      // Avoid duplicate loads with same params unless forced
      var key = vm.sn ? ('sn:' + vm.sn) : (hasId ? ('id:' + vm.student.id) : '');
      if (!force && vm._last && vm._last.invoices && vm._last.invoices.key === key && vm._last.invoices.term === vm.term) {
        return $q.when();
      }
      if (vm.loading.invoices) return $q.when();
      vm.loading.invoices = true;
      vm.error.invoices = null;

      var params = { term: vm.term };
      try {
        if (vm.student && (vm.student.id != null)) params.student_id = vm.student.id;
        if (vm.registration && vm.registration.intRegistrationID) {
          var rid = parseInt(vm.registration.intRegistrationID, 10);
          if (isFinite(rid) && rid > 0) params.registration_id = rid;
        }
      } catch (e) {}

      return UnityService.invoicesList(params)
        .then(function (body) {
          var data = body && body.data ? body.data : body;
          var list = Array.isArray(data) ? data : (data && data.items ? data.items : []);
          list = Array.isArray(list) ? list.slice() : [];
          list.sort(function (a, b) {
            function d(x) { return new Date(x || '').getTime() || 0; }
            var ad = d(a && (a.posted_at || a.created_at || a.updated_at));
            var bd = d(b && (b.posted_at || b.created_at || b.updated_at));
            if (ad !== bd) return bd - ad;
            var ai = (a && a.id != null) ? parseInt(a.id, 10) : 0;
            var bi = (b && b.id != null) ? parseInt(b.id, 10) : 0;
            return bi - ai;
          });
          vm.invoices = list;
          try { if (typeof vm.recomputeInvoicesPayments === 'function') vm.recomputeInvoicesPayments(); } catch (e) {}
          vm._last.invoices = { key: key, term: vm.term };
        })
        .catch(function () {
          vm.invoices = [];
          vm.error.invoices = 'Failed to load invoices.';
        })
        .finally(function () {
          vm.loading.invoices = false;
        });
    };

    // Payment Details loader (for selected term/registration)
    vm.loadPaymentDetails = function (force) {
        var hasId = vm.student && vm.student.id != null;
        if (!vm.term || (!vm.sn && !hasId)) return $q.when();
        // Avoid duplicate loads with same params unless forced
        var key = vm.sn ? ('sn:' + vm.sn) : (hasId ? ('id:' + vm.student.id) : '');
        if (!force && vm._last && vm._last.payments && vm._last.payments.key === key && vm._last.payments.term === vm.term) {
            return $q.when();
        }
        if (vm.loading.payments) return $q.when();
        vm.loading.payments = true;
        vm.error.payments = null;

        // Prefer student_id -> payment_details.student_information_id matching; fallback to student_number
        var params = { term: vm.term };
        if (vm.student && (vm.student.id != null)) {
            params.student_id = vm.student.id;
        } else if (vm.sn) {
            params.student_number = vm.sn;
        }
        return UnityService.paymentDetails(params)
            .then(function (body) {
            // UnityService._unwrap returns body, but keep both shapes safe
            var data = body && body.data ? body.data : body;
            vm.paymentDetails = data || { items: [], meta: {} };

            try {
                var total = (vm.paymentDetails && vm.paymentDetails.meta && vm.paymentDetails.meta.total_paid_filtered != null)
                ? parseFloat(vm.paymentDetails.meta.total_paid_filtered)
                : null;
                vm.paymentDetailsTotal = isFinite(total) ? total : 0;

                // If tuition/ledger did not provide an amount_paid, use filtered payment_details total
                if (vm.meta && (vm.meta.amount_paid == null || !isFinite(vm.meta.amount_paid))) {
                vm.meta.amount_paid = vm.paymentDetailsTotal || 0;
                }
                // Refresh remaining computation with latest paid
                if (typeof vm.refreshTuitionSummary === 'function') {
                vm.refreshTuitionSummary();
                }
                try { if (typeof vm.recomputeInvoicesPayments === 'function') vm.recomputeInvoicesPayments(); } catch (e2) {}
            } catch (e) {}
            // Mark last
            vm._last.payments = { key: key, term: vm.term };
            })
            .catch(function () {
            vm.error.payments = 'Failed to load payment details.';
            })
            .finally(function () {
            vm.loading.payments = false;
            });
    };

    vm.loadRecords = function (force) {
      if (!vm.sn) return $q.when();
      // Avoid duplicate loads with same params (term may be undefined when loading all)
      var termKey = vm.term || null;
      if (!force && vm._last && vm._last.records && vm._last.records.sn === vm.sn && vm._last.records.term === termKey) {
        return $q.when();
      }
      if (vm.loading.records) return $q.when();
      vm.loading.records = true;
      vm.error.records = null;

      var payload = { student_number: vm.sn, student_id: vm.student.id, include_grades: true };
      var endpoint = API + '/student/records';
      if (vm.term) {
        endpoint = API + '/student/records-by-term';
        payload.term = '' + vm.term; // API expects string
      }
      return $http.post(endpoint, payload)
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          vm.records = (data && data.data) ? data.data : (data || null);
        })
        .catch(function () {
          vm.error.records = 'Failed to load records.';
        })
        .finally(function () {
          // Mark last loaded params
          vm._last.records = { sn: vm.sn, term: termKey };
          vm.loading.records = false;
        });
    };

    vm.updateRegistration = function () {
      if (!vm.sn || !vm.term) return;
      if (!vm.canEdit) return; // guard: read-only for non-finance/admin
      vm.loading.update = true;
      vm.error.update = null;

      var fields = {};
      if (vm.edit.paymentType !== null && vm.edit.paymentType !== undefined) fields.paymentType = vm.edit.paymentType;
      if (vm.edit.tuition_year !== null && vm.edit.tuition_year !== undefined) fields.tuition_year = vm.edit.tuition_year;
      if (vm.edit.allow_enroll !== null && vm.edit.allow_enroll !== undefined) fields.allow_enroll = parseInt(vm.edit.allow_enroll, 10);
      if (vm.edit.downpayment !== null && vm.edit.downpayment !== undefined) fields.downpayment = parseInt(vm.edit.downpayment, 10);
      if (vm.edit.enrollment_status !== null && vm.edit.enrollment_status !== undefined) fields.enrollment_status = parseInt(vm.edit.enrollment_status, 10);

      var payload = {
        student_number: vm.sn,
        term: vm.term,
        fields: fields
      };

      // Use UnityService to ensure headers carry X-Faculty-ID if available
      return UnityService.updateRegistration(payload)
        .then(function () {
          // After registration update, recompute and persist the saved tuition snapshot
          return UnityService.tuitionSave({
            student_number: vm.sn,
            term: vm.term
          }).catch(function () {
            // don't block UI if save fails; proceed to reload
            return null;
          });
        })
        .then(function () {
          // Force refresh: clear last-cache and reload fresh data and summaries
          vm._last.registration = null;
          vm._last.tuition = null;
          vm._last.records = null;
          vm._last.payments = null;
          vm.tuitionSaved = null;

          return vm.loadRegistration(true)
            .then(function () { return vm.loadTuition(true); })
            // Auto-reload invoices after saving options to reflect updated tuition invoice totals/creation
            .then(function () { return vm.loadInvoices(true); })
            .then(function () { return vm.loadPaymentDetails(true); })
            .then(function () {
              // Ensure summary reflects latest server state immediately after tuition reload
              vm.refreshTuitionSummary();
              return vm.loadRecords(true);
            });
        })
        .catch(function () {
          vm.error.update = 'Failed to update registration.';
        })
        .finally(function () {
          vm.loading.update = false;
        });
    };

    // Auto-update saved tuition when options change
    vm.onOptionChange = function () {
      if (!vm.canEdit) return; // guard: read-only for non-finance/admin
      // Trigger update, which chains tuition-save and tuition reload
      vm.updateRegistration();
    };

    // Bootstrap sequence (ensure registration loads before tuition to carry tuition_year)
    vm._inBootstrap = true;
    vm.loading.bootstrap = true;
    $q.when()
      .then(vm.loadStudent)
      .then(vm.loadStudents)
      // Resolve acting cashier if callable (finance without cashier_admin may be forbidden; UI will handle null)
      .then(vm.loadMyCashier)
      // Fire-and-forget: do NOT block bootstrap on payment modes in case API is slow/unreachable
      .then(function () {
        try { vm.loadPaymentModes(); } catch (e) {}
        try { vm.loadPaymentDescriptions(); } catch (e2) {}
        return null;
      })
      // Use existing global term selection; avoid calling TermService.init() here to prevent duplicate term list fetches
      .then(function () {
        applyGlobalTerm();
        return vm.loadTuitionYears();
      })
      .then(vm.loadApplicantInfo)
      .then(function () { return vm.loadRegistration(); })
      .then(function () { return vm.loadTuition(); })
      .then(function () { return vm.loadLedger(); })
      .then(function () { return vm.loadInvoices(); })
      .then(function () { return vm.loadPaymentDetails(); })
      .then(function () { return vm.loadBilling(); })
      .then(function () { return vm.loadRecords(); })
      .finally(function () {
        vm._inBootstrap = false;
        vm.loading.bootstrap = false;
      });
  }

})();
