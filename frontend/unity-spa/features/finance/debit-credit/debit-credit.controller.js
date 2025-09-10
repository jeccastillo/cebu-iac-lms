(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('DebitCreditController', DebitCreditController);

  DebitCreditController.$inject = [
    '$http',
    '$q',
    '$location',
    '$rootScope',
    '$scope',
    'APP_CONFIG',
    'RoleService',
    'StorageService',
    'TermService',
    'StudentsService',
    'DebitCreditService',
    'ToastService',
    'PaymentModesService'
  ];
  function DebitCreditController(
    $http, $q, $location, $rootScope, $scope,
    APP_CONFIG, RoleService, StorageService, TermService, StudentsService,
    DebitCreditService, ToastService, PaymentModesService
  ) {
    var vm = this;

    // Auth guard via RoleService
    vm.canEdit = !!(RoleService && typeof RoleService.hasAny === 'function'
      ? RoleService.hasAny(['finance', 'admin'])
      : false);

    if (!vm.canEdit) {
      try { ToastService && ToastService.error && ToastService.error('You are not allowed to access this page'); } catch (e) {}
      // Do not redirect automatically; display read-only UI with notice
    }

    vm.state = StorageService.getJSON('loginState') || {};
    vm.API = APP_CONFIG.API_BASE;

    // Selected student and term context
    vm.term = null;
    vm.termLabel = '';
    vm.students = [];
    vm.selectedStudent = null; // object { id, student_number, last_name, first_name, middle_name }
    vm.studentQuery = '';

    // UI Loading/Error indicators
    vm.loading = {
      bootstrap: false,
      students: false,
      invoices: false,
      payments: false,
      postDebit: false,
      postCredit: false,
      paymentModes: false
    };
    vm.error = {
      students: null,
      invoices: null,
      payments: null
    };

    // Payment modes for dropdown
    vm.paymentModes = [];

    // Invoices for selected student+term (optional linkage)
    vm.invoices = [];
    vm.paymentDetails = null;

    // Form models
    vm.debit = {
      amount: null,
      description: '',
      remarks: '',
      method: null,
      posted_at: null,
      mode_of_payment_id: null,
      invoice_id: null,
      invoice_number: null
    };
    vm.credit = {
      amount: null,
      description: '',
      remarks: '',
      method: null,
      posted_at: null,
      mode_of_payment_id: null,
      invoice_id: null,
      invoice_number: null,
      enforce_invoice_remaining: true
    };

    // Method options (fixed list)
    vm.methodOptions = ['Cash', 'Check', 'Credit Card', 'Debit Card', 'Online Payment'];

    // Helpers
    vm.currency = function (num) {
      var n = parseFloat(num || 0);
      if (!isFinite(n)) n = 0;
      var s = n.toFixed(2);
      return s.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    // Autocomplete student query
    vm._studentQCache = {};
    vm.onStudentQuery = function (q) {
      try {
        var text = (q == null ? '' : ('' + q)).trim();
        vm.studentQuery = text;
        if (Object.prototype.hasOwnProperty.call(vm._studentQCache, text)) {
          vm.students = Array.isArray(vm._studentQCache[text]) ? vm._studentQCache[text].slice() : [];
          return;
        }
        vm.loading.students = true;
        return StudentsService.listPage({ q: text, per_page: 20, page: 1, include_applicants: 1 })
          .then(function (list) {
            var items = Array.isArray(list) ? list : [];
            vm._studentQCache[text] = items.slice();
            vm.students = items;
          })
          .catch(function () {
            vm._studentQCache[text] = [];
            vm.students = [];
          })
          .finally(function () {
            vm.loading.students = false;
          });
      } catch (e) {
        vm.students = vm.students || [];
      }
    };

    vm.onStudentSelected = function (item) {
      try {
        vm.selectedStudent = item || null;
        if (vm.selectedStudent) {
          // Reset forms and load invoices/payments
          vm.resetForms();
          vm.loadInvoices(true);
          vm.loadPaymentDetails(true);
        }
      } catch (e) {}
    };

    function selectedStudentId() {
      return vm.selectedStudent && vm.selectedStudent.id != null ? parseInt(vm.selectedStudent.id, 10) : null;
    }

    // Load payment modes for dropdown
    vm.loadPaymentModes = function () {
      vm.loading.paymentModes = true;
      return (PaymentModesService && PaymentModesService.list
        ? PaymentModesService.list({ is_active: 1, per_page: 1000 })
        : $q.when({ data: [] }))
        .then(function (res) {
          var items = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          vm.paymentModes = Array.isArray(items) ? items : [];
        })
        .catch(function () { vm.paymentModes = []; })
        .finally(function () { vm.loading.paymentModes = false; });
    };

    // Term helpers
    function buildTermLabel(t) {
      try {
        var parts = [];
        if (t.term_student_type) parts.push(t.term_student_type);
        if (t.enumSem) parts.push(t.enumSem);
        if (t.term_label) parts.push(t.term_label);
        if (t.strYearStart && t.strYearEnd) parts.push(t.strYearStart + '-' + t.strYearEnd);
        return parts.join(' ').replace(/\s+/g, ' ').trim() || (t.label || ('SY ' + (t.syid || t.intID || '')));
      } catch (e) {
        return t && (t.label || ('SY ' + (t.syid || t.intID || ''))) || '';
      }
    }
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
    var unbindTermChanged = $rootScope.$on('termChanged', function () {
      var prev = vm.term;
      applyGlobalTerm();
      if (prev !== vm.term && vm.selectedStudent) {
        vm.loadInvoices(true);
        vm.loadPaymentDetails(true);
      }
    });
    $scope.$on('$destroy', function () {
      if (typeof unbindTermChanged === 'function') unbindTermChanged();
    });

    // Load invoices for student+term
    vm.loadInvoices = function (force) {
      if (!vm.term || !selectedStudentId()) return $q.when();
      vm.loading.invoices = true;
      vm.error.invoices = null;
      return DebitCreditService.listInvoices({ student_id: selectedStudentId(), term: vm.term })
        .then(function (body) {
          var data = body && body.data ? body.data : body;
          var list = Array.isArray(data) ? data : (data && data.items ? data.items : []);
          vm.invoices = Array.isArray(list) ? list.slice() : [];
          // Pre-sort recent first
          vm.invoices.sort(function (a, b) {
            function d(x) { return new Date(x || '').getTime() || 0; }
            var ad = d(a && (a.posted_at || a.created_at || a.updated_at));
            var bd = d(b && (b.posted_at || b.created_at || b.updated_at));
            if (ad !== bd) return bd - ad;
            var ai = (a && a.id != null) ? parseInt(a.id, 10) : 0;
            var bi = (b && b.id != null) ? parseInt(b.id, 10) : 0;
            return bi - ai;
          });
        })
        .catch(function () {
          vm.invoices = [];
          vm.error.invoices = 'Failed to load invoices.';
        })
        .finally(function () {
          vm.loading.invoices = false;
        });
    };

    // Load payment details list for refresh after posting
    vm.loadPaymentDetails = function (force) {
      if (!vm.term || !selectedStudentId()) return $q.when();
      vm.loading.payments = true;
      vm.error.payments = null;
      return DebitCreditService.paymentDetails({ student_id: selectedStudentId(), term: vm.term })
        .then(function (body) {
          var data = body && body.data ? body.data : body;
          vm.paymentDetails = data || { items: [], meta: {} };
        })
        .catch(function () {
          vm.paymentDetails = { items: [], meta: {} };
          vm.error.payments = 'Failed to load payment details.';
        })
        .finally(function () {
          vm.loading.payments = false;
        });
    };

    // Invoice selection helpers (mirror number and compute remaining client-side if needed)
    vm.onDebitInvoiceSelected = function () {
      var sel = vm.debit.invoice_id != null ? parseInt(vm.debit.invoice_id, 10) : null;
      if (!isFinite(sel)) { vm.debit.invoice_number = null; return; }
      var inv = findInvoice(sel);
      vm.debit.invoice_number = inv ? (inv.invoice_number || inv.number || null) : null;
    };
    vm.onCreditInvoiceSelected = function () {
      var sel = vm.credit.invoice_id != null ? parseInt(vm.credit.invoice_id, 10) : null;
      if (!isFinite(sel)) { vm.credit.invoice_number = null; return; }
      var inv = findInvoice(sel);
      vm.credit.invoice_number = inv ? (inv.invoice_number || inv.number || null) : null;
    };
    function findInvoice(id) {
      try {
        var list = Array.isArray(vm.invoices) ? vm.invoices : [];
        for (var i = 0; i < list.length; i++) {
          var inv = list[i] || {};
          var iid = inv && inv.id != null ? parseInt(inv.id, 10) : null;
          if (isFinite(iid) && iid === id) return inv;
        }
        return null;
      } catch (e) {
        return null;
      }
    }

    // Form guards
    vm.canSubmitDebit = function () {
      try {
        if (!vm.canEdit) return false;
        if (!vm.term || !selectedStudentId()) return false;
        var p = vm.debit || {};
        var amount = parseFloat(p.amount);
        var amountOk = isFinite(amount) && amount > 0;
        var descOk = !!(p.description && ('' + p.description).trim().length > 0);
        // remarks optional; method optional; mode_of_payment_id optional
        return amountOk && descOk && !vm.loading.postDebit;
      } catch (e) {
        return false;
      }
    };
    vm.canSubmitCredit = function () {
      try {
        if (!vm.canEdit) return false;
        if (!vm.term || !selectedStudentId()) return false;
        var p = vm.credit || {};
        var amount = parseFloat(p.amount);
        var amountOk = isFinite(amount) && amount > 0;
        var descOk = !!(p.description && ('' + p.description).trim().length > 0);
        return amountOk && descOk && !vm.loading.postCredit;
      } catch (e) {
        return false;
      }
    };

    vm.submitDebit = function () {
      if (!vm.canSubmitDebit()) return $q.when();
      vm.loading.postDebit = true;

      var payload = {
        student_id: selectedStudentId(),
        term: vm.term,
        amount: parseAmount(vm.debit.amount),
        description: ('' + vm.debit.description).trim(),
        remarks: (vm.debit.remarks || '').trim() || 'DEBIT ADJUSTMENT',
        method: vm.debit.method || null,
        posted_at: vm.debit.posted_at || null,
        mode_of_payment_id: isFinite(parseInt(vm.debit.mode_of_payment_id, 10)) ? parseInt(vm.debit.mode_of_payment_id, 10) : null
      };
      if (isFinite(parseInt(vm.debit.invoice_id, 10))) payload.invoice_id = parseInt(vm.debit.invoice_id, 10);
      if (payload.invoice_id == null && isFinite(parseInt(vm.debit.invoice_number, 10))) payload.invoice_number = parseInt(vm.debit.invoice_number, 10);

      return DebitCreditService.postDebit(payload)
        .then(function () {
          ToastService && ToastService.success && ToastService.success('Debit posted.');
          vm.debit = { amount: null, description: '', remarks: '', method: null, posted_at: null, mode_of_payment_id: null, invoice_id: null, invoice_number: null };
          return $q.when()
            .then(function () { return vm.loadPaymentDetails(true); });
        })
        .catch(function (err) {
          var msg = 'Failed to post debit.';
          try { if (err && err.data && err.data.message) msg = err.data.message; else if (err && err.message) msg = err.message; } catch (e) {}
          ToastService && ToastService.error && ToastService.error(msg);
        })
        .finally(function () { vm.loading.postDebit = false; });
    };

    vm.submitCredit = function () {
      if (!vm.canSubmitCredit()) return $q.when();
      vm.loading.postCredit = true;

      var payload = {
        student_id: selectedStudentId(),
        term: vm.term,
        amount: parseAmount(vm.credit.amount),
        description: ('' + vm.credit.description).trim(),
        remarks: (vm.credit.remarks || '').trim() || 'CREDIT ADJUSTMENT',
        method: vm.credit.method || null,
        posted_at: vm.credit.posted_at || null,
        mode_of_payment_id: isFinite(parseInt(vm.credit.mode_of_payment_id, 10)) ? parseInt(vm.credit.mode_of_payment_id, 10) : null,
        enforce_invoice_remaining: vm.credit.enforce_invoice_remaining !== false
      };
      if (isFinite(parseInt(vm.credit.invoice_id, 10))) payload.invoice_id = parseInt(vm.credit.invoice_id, 10);
      if (payload.invoice_id == null && isFinite(parseInt(vm.credit.invoice_number, 10))) payload.invoice_number = parseInt(vm.credit.invoice_number, 10);

      return DebitCreditService.postCredit(payload)
        .then(function () {
          ToastService && ToastService.success && ToastService.success('Credit posted.');
          vm.credit = { amount: null, description: '', remarks: '', method: null, posted_at: null, mode_of_payment_id: null, invoice_id: null, invoice_number: null, enforce_invoice_remaining: true };
          return $q.when()
            .then(function () { return vm.loadPaymentDetails(true); });
        })
        .catch(function (err) {
          var msg = 'Failed to post credit.';
          try { if (err && err.data && err.data.message) msg = err.data.message; else if (err && err.message) msg = err.message; } catch (e) {}
          ToastService && ToastService.error && ToastService.error(msg);
        })
        .finally(function () { vm.loading.postCredit = false; });
    };

    function parseAmount(v) {
      var n = parseFloat(v);
      if (!isFinite(n)) return null;
      return Math.floor(n * 100) / 100;
    }

    vm.resetForms = function () {
      vm.debit = { amount: null, description: '', remarks: '', method: null, posted_at: null, mode_of_payment_id: null, invoice_id: null, invoice_number: null };
      vm.credit = { amount: null, description: '', remarks: '', method: null, posted_at: null, mode_of_payment_id: null, invoice_id: null, invoice_number: null, enforce_invoice_remaining: true };
    };

    // Bootstrap
    vm.loading.bootstrap = true;
    $q.when()
      .then(function () { applyGlobalTerm(); })
      .then(vm.loadPaymentModes)
      .finally(function () { vm.loading.bootstrap = false; });
  }
})();
