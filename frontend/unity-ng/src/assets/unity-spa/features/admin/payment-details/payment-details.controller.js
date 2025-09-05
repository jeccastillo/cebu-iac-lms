(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminPaymentDetailsController', AdminPaymentDetailsController);

  AdminPaymentDetailsController.$inject = ['$q', '$timeout', 'AdminPaymentDetailsService', 'RoleService', 'PaymentModesService'];
  function AdminPaymentDetailsController($q, $timeout, AdminPaymentDetailsService, RoleService, PaymentModesService) {
    var vm = this;

    // RBAC guard
    vm.isAdmin = RoleService.hasRole('admin');

    // Filters and pagination
    vm.filters = {
      q: '',
      student_number: '',
      student_id: '',
      syid: '',
      status: '',
      mode: '', // 'or' | 'invoice' | ''
      or_number: '',
      invoice_number: '',
      date_from: '',
      date_to: '',
      page: 1,
      per_page: 20
    };

    // Data state
    vm.loading = false;
    vm.items = [];
    vm.meta = { page: 1, per_page: 20, total: 0 };
    vm.error = null;
    vm.success = null;
    // Payment modes list for dropdown
    vm.paymentModes = [];

    // Selected item for editing
    vm.selected = null;
    vm.editOpen = false;
    vm.edit = {};

    // Public methods
    vm.search = search;
    vm.resetFilters = resetFilters;
    vm.goPage = goPage;
    vm.openEdit = openEdit;
    vm.closeEdit = closeEdit;
    vm.save = save;
    vm.remove = remove;
    vm.canSave = canSave;
    vm.loadPaymentModes = loadPaymentModes;
    vm.modeName = modeName;
    vm.dateOnly = dateOnly;

    activate();

    function activate() {
      if (!vm.isAdmin) {
        vm.error = 'Access denied. Admin only.';
        return;
      }
      loadPaymentModes();
      search(true);
    }

    function search(resetPage) {
      if (!vm.isAdmin) return $q.when();
      vm.error = null;
      vm.success = null;
      if (resetPage) vm.filters.page = 1;
      vm.loading = true;
      var f = _cleanFilters(vm.filters);
      return AdminPaymentDetailsService.search(f)
        .then(function (body) {
          var data = body && body.data ? body.data : body;
          // data: { items, meta }
          vm.items = (data && data.items) ? data.items : [];
          vm.meta = (data && data.meta) ? data.meta : { page: vm.filters.page, per_page: vm.filters.per_page, total: 0 };
        })
        .catch(function (err) {
          vm.error = 'Failed to load payment details.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function loadPaymentModes() {
      // Fetch active payment modes for dropdown; request a large page to avoid pagination handling here
      return PaymentModesService.list({ is_active: 1, per_page: 1000 })
        .then(function (body) {
          var data = (body && body.data) ? body.data : body;
          // Body may be an array or an object with data/meta
          vm.paymentModes = Array.isArray(data) ? data : (data && Array.isArray(data.data) ? data.data : []);
        })
        .catch(function () {
          vm.paymentModes = [];
        });
    }

    function modeName(id) {
      if (!id || !Array.isArray(vm.paymentModes)) return '';
      for (var i = 0; i < vm.paymentModes.length; i++) {
        var m = vm.paymentModes[i];
        if (m && +m.id === +id) {
          var label = (m.name || '');
          if (m.pchannel) label += ' — ' + m.pchannel;
          if (m.pmethod) label += ' — ' + m.pmethod;
          return label;
        }
      }
      return '';
    }

    function dateOnly(x) {
      if (!x) return '';
      try {
        var s = ('' + x).trim();
        return s.length >= 10 ? s.substring(0, 10) : s;
      } catch (e) {
        return '';
      }
    }

    function resetFilters() {
      vm.filters = {
        q: '',
        student_number: '',
        student_id: '',
        syid: '',
        status: '',
        mode: '',
        or_number: '',
        invoice_number: '',
        date_from: '',
        date_to: '',
        page: 1,
        per_page: vm.meta && vm.meta.per_page ? vm.meta.per_page : 20
      };
      search(true);
    }

    function goPage(delta) {
      var p = (vm.meta.page || 1) + (delta || 0);
      var maxPage = Math.max(1, Math.ceil((vm.meta.total || 0) / (vm.meta.per_page || 20)));
      if (p < 1) p = 1;
      if (p > maxPage) p = maxPage;
      if (p === vm.meta.page) return;
      vm.filters.page = p;
      search(false);
    }

    function openEdit(item) {
      if (!item || !item.id) return;
      vm.error = null;
      vm.success = null;
      vm.selected = item;
      // Shallow clone editable fields
      vm.edit = {
        description: item.description || '',
        subtotal_order: item.subtotal_order != null ? parseFloat(item.subtotal_order) : null,
        total_amount_due: item.total_amount_due != null ? parseFloat(item.total_amount_due) : null,
        status: item.status || '',
        remarks: item.remarks || '',
        method: item.method || '',
        mode_of_payment_id: item.mode_of_payment_id || null,
        posted_at: item.posted_at || '',
        // number fields (service will map/validate uniqueness)
        or_no: item.or_no || '',
        invoice_number: item.invoice_number || ''
      };
      // Derive OR Date (date-only) from posted_at for convenience
      try {
        var s = ('' + (item.posted_at || '')).trim();
        vm.edit.or_date = s ? (s.length >= 10 ? s.substring(0, 10) : s) : '';
      } catch (e) {
        vm.edit.or_date = '';
      }
      // Ensure payment modes are loaded when opening editor
      if (!vm.paymentModes || vm.paymentModes.length === 0) {
        loadPaymentModes();
      }
      vm.editOpen = true;
    }

    function closeEdit() {
      vm.selected = null;
      vm.edit = {};
      vm.editOpen = false;
    }

    function canSave() {
      if (!vm.selected || !vm.selected.id) return false;
      // Basic guards
      if (vm.edit.subtotal_order != null && !(parseFloat(vm.edit.subtotal_order) > 0)) return false;
      if (vm.edit.total_amount_due != null && !(parseFloat(vm.edit.total_amount_due) >= (parseFloat(vm.edit.subtotal_order) || 0))) return false;
      return true;
    }

    function save() {
      if (!vm.selected || !vm.selected.id) return $q.when();
      if (!canSave()) {
        vm.error = 'Please correct validation errors before saving.';
        return $q.reject();
      }
      vm.error = null;
      vm.success = null;
      var id = vm.selected.id;
      var payload = _cleanPayload(vm.edit);
      // Map or_date (date-only) to posted_at if provided
      if (payload.or_date) {
        payload.posted_at = payload.or_date;
        delete payload.or_date;
      }
      return AdminPaymentDetailsService.update(id, payload)
        .then(function (body) {
          var data = body && body.data ? body.data : body;
          vm.success = 'Payment details updated.';
          // Update in list
          try {
            for (var i = 0; i < vm.items.length; i++) {
              if (vm.items[i].id === id) {
                vm.items[i] = data;
                break;
              }
            }
          } catch (e) {}
          // Refresh search silently after a small delay to keep ordering accurate
          $timeout(function () { search(false); }, 300);
          closeEdit();
        })
        .catch(function (err) {
          var msg = 'Update failed.';
          try {
            if (err && err.data && err.data.errors) {
              msg = Object.keys(err.data.errors).map(function (k) {
                return err.data.errors[k].join(', ');
              }).join('; ');
            } else if (err && err.data && err.data.message) {
              msg = err.data.message;
            }
          } catch (e) {}
          vm.error = msg;
        });
    }

    function remove(item) {
      if (!vm.isAdmin) return $q.when();
      if (!item || !item.id) return $q.when();

      var id = item.id;
      var msg = 'Delete payment_details id ' + id + '? This cannot be undone.';
      try {
        var label = [];
        if (item.or_no) label.push('OR: ' + item.or_no);
        if (item.invoice_number) label.push('Invoice: ' + item.invoice_number);
        if (item.description) label.push('Desc: ' + item.description);
        if (label.length) msg += '\n\n' + label.join(' | ');
      } catch (e) {}

      if (!window.confirm(msg)) {
        return $q.when();
      }

      vm.error = null;
      vm.success = null;
      vm.loading = true;

      return AdminPaymentDetailsService.remove(id)
        .then(function () {
          vm.success = 'Payment details deleted.';
          // Remove from list and adjust meta
          try {
            vm.items = (vm.items || []).filter(function (x) { return x && x.id !== id; });
            if (vm.meta && vm.meta.total != null) {
              vm.meta.total = Math.max(0, (vm.meta.total | 0) - 1);
            }
          } catch (e) {}
          // Close editor if it was the deleted item
          if (vm.selected && vm.selected.id === id) {
            vm.closeEdit();
          }
          // Refresh results to keep ordering accurate
          $timeout(function () { search(false); }, 200);
        })
        .catch(function (err) {
          var msg = 'Delete failed.';
          try {
            if (err && err.data && err.data.errors) {
              msg = Object.keys(err.data.errors).map(function (k) {
                return err.data.errors[k].join(', ');
              }).join('; ');
            } else if (err && err.data && err.data.message) {
              msg = err.data.message;
            }
          } catch (e) {}
          vm.error = msg;
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function _cleanFilters(f) {
      var out = {};
      Object.keys(f || {}).forEach(function (k) {
        var v = f[k];
        if (v === '' || v === null || typeof v === 'undefined') return;
        out[k] = v;
      });
      return out;
    }

    function _cleanPayload(p) {
      var out = {};
      Object.keys(p || {}).forEach(function (k) {
        var v = p[k];
        if (v === '' || typeof v === 'undefined') return;
        out[k] = v;
      });
      return out;
    }
  }

})();
