(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FinancePaymentActionsController', FinancePaymentActionsController);

  FinancePaymentActionsController.$inject = ['$q', '$timeout', 'FinancePaymentActionsService', 'RoleService'];
  function FinancePaymentActionsController($q, $timeout, FinancePaymentActionsService, RoleService) {
    var vm = this;

    // RBAC guard
    vm.isFinanceAdmin = RoleService.hasRole('finance_admin') || RoleService.hasRole('admin');

    // Filters and pagination
    vm.filters = {
      or_number: '',
      invoice_number: '',
      student_number: '',
      student_id: '',
      syid: '',
      status: '',
      page: 1,
      per_page: 20
    };

    // Data state
    vm.loading = false;
    vm.items = [];
    vm.meta = { page: 1, per_page: 20, total: 0 };
    vm.error = null;
    vm.success = null;

    // Public methods
    vm.search = search;
    vm.resetFilters = resetFilters;
    vm.goPage = goPage;
    vm.doVoid = doVoid;
    vm.retract = retract;
    vm.dateOnly = dateOnly;

    activate();

    function activate() {
      if (!vm.isFinanceAdmin) {
        vm.error = 'Access denied. Finance Admin or Admin only.';
        return;
      }
      // Do not auto-search; require at least one of OR/Invoice
    }

    function search(resetPage) {
      if (!vm.isFinanceAdmin) return $q.when();
      vm.error = null;
      vm.success = null;

      // Require at least one of OR/Invoice numbers
      var hasOr = !!(vm.filters.or_number && ('' + vm.filters.or_number).trim());
      var hasInv = !!(vm.filters.invoice_number && ('' + vm.filters.invoice_number).trim());
      if (!hasOr && !hasInv) {
        vm.error = 'Enter OR number and/or Invoice number to search.';
        return $q.reject();
      }

      if (resetPage) vm.filters.page = 1;
      vm.loading = true;

      var f = _cleanFilters(vm.filters);
      return FinancePaymentActionsService.search(f)
        .then(function (body) {
          var data = body && body.data ? body.data : body;
          // data: { items, meta }
          vm.items = (data && data.items) ? data.items : [];
          vm.meta = (data && data.meta) ? data.meta : { page: vm.filters.page, per_page: vm.filters.per_page, total: 0 };
          if (!vm.items.length) {
            vm.success = 'No results found.';
          }
        })
        .catch(function (err) {
          var msg = 'Failed to load results.';
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

    function doVoid(item) {
      if (!vm.isFinanceAdmin) return $q.when();
      if (!item || !item.id) return $q.when();

      var id = item.id;
      var msg = 'Void payment_details id ' + id + '?';
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

      return FinancePaymentActionsService.void(id, { remarks: 'Voided via Finance Payment Actions' })
        .then(function (body) {
          var data = body && body.data ? body.data : body;
          vm.success = 'Payment voided.';
          // Update in list
          try {
            for (var i = 0; i < vm.items.length; i++) {
              if (vm.items[i].id === id) {
                vm.items[i] = data;
                break;
              }
            }
          } catch (e) {}
          // Refresh search to maintain ordering/filters
          $timeout(function () { search(false); }, 300);
        })
        .catch(function (err) {
          var msg = 'Void failed.';
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

    function retract(item) {
      if (!vm.isFinanceAdmin) return $q.when();
      if (!item || !item.id) return $q.when();

      var id = item.id;
      var msg = 'Retract (hard delete) payment_details id ' + id + '? This cannot be undone.';
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

      return FinancePaymentActionsService.retract(id)
        .then(function () {
          vm.success = 'Payment retracted.';
          try {
            vm.items = (vm.items || []).filter(function (x) { return x && x.id !== id; });
            if (vm.meta && vm.meta.total != null) {
              vm.meta.total = Math.max(0, (vm.meta.total | 0) - 1);
            }
          } catch (e) {}
          $timeout(function () { search(false); }, 200);
        })
        .catch(function (err) {
          var msg = 'Retract failed.';
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

    function resetFilters() {
      vm.filters = {
        or_number: '',
        invoice_number: '',
        student_number: '',
        student_id: '',
        syid: '',
        status: '',
        page: 1,
        per_page: vm.meta && vm.meta.per_page ? vm.meta.per_page : 20
      };
      vm.items = [];
      vm.meta = { page: 1, per_page: vm.filters.per_page, total: 0 };
      vm.error = null;
      vm.success = null;
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

    function dateOnly(x) {
      if (!x) return '';
      try {
        var s = ('' + x).trim();
        return s.length >= 10 ? s.substring(0, 10) : s;
      } catch (e) {
        return '';
      }
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
  }

})();
