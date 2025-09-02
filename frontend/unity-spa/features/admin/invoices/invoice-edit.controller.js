(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminInvoiceEditController', AdminInvoiceEditController);

  AdminInvoiceEditController.$inject = ['$routeParams', '$location', 'AdminInvoicesService'];
  function AdminInvoiceEditController($routeParams, $location, AdminInvoicesService) {
    var vm = this;

    vm.id = ($routeParams && $routeParams.id) ? parseInt($routeParams.id, 10) : null;
    vm.isCreate = !vm.id;

    vm.loading = false;
    vm.error = null;
    vm.title = vm.isCreate ? 'Create Invoice' : 'Edit Invoice';

    vm.types = ['tuition', 'billing', 'other'];
    vm.statuses = ['Draft', 'Issued', 'Paid', 'Void'];

    // Unified model for create/update
    vm.model = {
      // required for create (generate)
      type: 'other',
      student_id: null,
      term: null, // syid alias

      // common optional fields
      status: 'Draft',
      invoice_number: null,
      amount: null,
      posted_at: null,
      due_at: null,
      campus_id: null,
      cashier_id: null,
      registration_id: null,
      remarks: '',

      // update-only field: payload (JSON)
      payload: ''
    };

    // Methods
    vm.load = load;
    vm.save = save;
    vm.cancel = cancel;

    activate();

    function activate() {
      if (!vm.isCreate) {
        load();
      }
    }

    function load() {
      vm.loading = true;
      vm.error = null;
      AdminInvoicesService.show(vm.id)
        .then(function (res) {
          var d = (res && res.data) ? res.data : res;
          if (!d) return;

          vm.model.type = d.type || vm.model.type;
          vm.model.status = d.status || vm.model.status;

          vm.model.student_id = d.student_id || null;
          vm.model.term = d.syid || null;

          vm.model.invoice_number = (d.invoice_number === undefined ? null : d.invoice_number);
          vm.model.amount = (d.amount_total === undefined ? null : parseFloat(d.amount_total));
          vm.model.posted_at = d.posted_at || null;
          vm.model.due_at = d.due_at || null;
          vm.model.campus_id = d.campus_id || null;
          vm.model.cashier_id = d.cashier_id || null;
          vm.model.registration_id = d.registration_id || null;
          vm.model.remarks = d.remarks || '';

          // payload may be object; display as pretty JSON
          try {
            var p = (d.payload && typeof d.payload === 'object') ? d.payload : (d.payload ? JSON.parse(d.payload) : null);
            vm.model.payload = p ? JSON.stringify(p, null, 2) : '';
          } catch (e) {
            vm.model.payload = '';
          }
        })
        .catch(function (err) {
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load invoice.';
        })
        .finally(function () { vm.loading = false; });
    }

    function validateCreate() {
      var miss = [];
      if (!vm.model.type) miss.push('type');
      if (!vm.model.student_id) miss.push('student_id');
      if (!vm.model.term) miss.push('term (syid)');
      if (miss.length) {
        var msg = 'Missing required: ' + miss.join(', ');
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Validation error', text: msg });
        return false;
      }
      return true;
    }

    function normalizeCreatePayload() {
      var body = {
        type: vm.model.type,
        student_id: parseInt(vm.model.student_id, 10),
        term: parseInt(vm.model.term, 10)
      };
      if (vm.model.status) body.status = vm.model.status;
      if (vm.model.invoice_number !== null && vm.model.invoice_number !== '' && !isNaN(vm.model.invoice_number)) body.invoice_number = parseInt(vm.model.invoice_number, 10);
      if (vm.model.amount !== null && vm.model.amount !== '' && !isNaN(vm.model.amount)) body.amount = parseFloat(vm.model.amount);
      if (vm.model.posted_at) body.posted_at = vm.model.posted_at;
      if (vm.model.due_at) body.due_at = vm.model.due_at;
      if (vm.model.campus_id !== null && vm.model.campus_id !== '' && !isNaN(vm.model.campus_id)) body.campus_id = parseInt(vm.model.campus_id, 10);
      if (vm.model.cashier_id !== null && vm.model.cashier_id !== '' && !isNaN(vm.model.cashier_id)) body.cashier_id = parseInt(vm.model.cashier_id, 10);
      if (vm.model.registration_id !== null && vm.model.registration_id !== '' && !isNaN(vm.model.registration_id)) body.registration_id = parseInt(vm.model.registration_id, 10);
      if (vm.model.remarks) body.remarks = vm.model.remarks;
      return body;
    }

    function normalizeUpdatePayload() {
      var body = {};
      if (vm.model.status) body.status = vm.model.status;
      if (vm.model.posted_at) body.posted_at = vm.model.posted_at;
      if (vm.model.due_at) body.due_at = vm.model.due_at;
      if (vm.model.remarks !== undefined) body.remarks = vm.model.remarks;

      if (vm.model.campus_id !== null && vm.model.campus_id !== '' && !isNaN(vm.model.campus_id)) body.campus_id = parseInt(vm.model.campus_id, 10);
      if (vm.model.cashier_id !== null && vm.model.cashier_id !== '' && !isNaN(vm.model.cashier_id)) body.cashier_id = parseInt(vm.model.cashier_id, 10);
      if (vm.model.invoice_number !== null && vm.model.invoice_number !== '' && !isNaN(vm.model.invoice_number)) body.invoice_number = parseInt(vm.model.invoice_number, 10);

      if (vm.model.amount !== null && vm.model.amount !== '' && !isNaN(vm.model.amount)) body.amount = parseFloat(vm.model.amount);

      // payload: textarea JSON
      if (vm.model.payload && typeof vm.model.payload === 'string') {
        try {
          body.payload = JSON.parse(vm.model.payload);
        } catch (e) {
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Invalid JSON', text: 'Payload is not valid JSON.' });
          throw e;
        }
      }
      return body;
    }

    function save() {
      vm.loading = true;
      vm.error = null;

      if (vm.isCreate) {
        if (!validateCreate()) {
          vm.loading = false;
          return;
        }
        var createPayload = normalizeCreatePayload();
        AdminInvoicesService.create(createPayload)
          .then(function () {
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Invoice created' });
            $location.path('/admin/invoices');
          })
          .catch(function (err) {
            var m = (err && (err.message || (err.data && err.data.message))) || 'Create failed';
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
          })
          .finally(function () { vm.loading = false; });
        return;
      }

      // Update
      var updatePayload;
      try {
        updatePayload = normalizeUpdatePayload();
      } catch (e) {
        vm.loading = false;
        return;
      }
      AdminInvoicesService.update(vm.id, updatePayload)
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Invoice updated' });
          $location.path('/admin/invoices');
        })
        .catch(function (err) {
          var m = (err && err.data && err.data.code === 'DUPLICATE_INVOICE_NUMBER')
            ? 'Duplicate invoice number'
            : (err && (err.message || (err.data && err.data.message)) || 'Update failed');
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function cancel() {
      $location.path('/admin/invoices');
    }
  }

})();
