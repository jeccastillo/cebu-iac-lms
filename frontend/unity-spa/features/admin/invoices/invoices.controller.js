(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdminInvoicesController', AdminInvoicesController);

  AdminInvoicesController.$inject = ['$location', 'AdminInvoicesService'];
  function AdminInvoicesController($location, AdminInvoicesService) {
    var vm = this;

    // Page state
    vm.title = 'Invoices (Admin)';
    vm.loading = false;
    vm.error = null;
    vm.rows = [];
    vm.total = 0;
    vm.meta = { count: 0 };

    // Filters (supported by API)
    vm.filters = {
      student_id: '',
      student_number: '',
      term: '',
      syid: '',
      type: '',
      status: '',
      campus_id: '',
      registration_id: ''
    };

    // Row actions dropdown state
    vm.menuOpenId = null;
    vm.toggleMenu = function (id) { vm.menuOpenId = (vm.menuOpenId === id ? null : id); };
    vm.isMenuOpen = function (id) { return vm.menuOpenId === id; };
    vm.closeMenu = function () { vm.menuOpenId = null; };

    // Methods
    vm.load = load;
    vm.search = search;
    vm.clearFilters = clearFilters;
    vm.create = create;
    vm.edit = edit;
    vm.pdf = pdf;
    vm.remove = removeItem;

    activate();

    function activate() {
      load();
    }

    function normalizeResponse(res) {
      // API returns { success, data, meta? }
      var data = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
      var meta = (res && res.meta) ? res.meta : null;
      return { data: data, meta: meta };
    }

    function load() {
      vm.loading = true;
      vm.error = null;

      // Normalize numeric filters
      var f = Object.assign({}, vm.filters);
      ['student_id', 'term', 'syid', 'campus_id', 'registration_id'].forEach(function (k) {
        if (f[k] !== '' && f[k] !== null && f[k] !== undefined) {
          var n = parseInt(f[k], 10);
          if (!isNaN(n)) f[k] = n;
        } else {
          delete f[k];
        }
      });
      if (!f.student_number) delete f.student_number;
      if (!f.type) delete f.type;
      if (!f.status) delete f.status;

      AdminInvoicesService.list(f)
        .then(function (res) {
          var r = normalizeResponse(res);
          vm.rows = Array.isArray(r.data) ? r.data : [];
          vm.meta = r.meta || { count: (vm.rows ? vm.rows.length : 0) };
          vm.total = (vm.meta.count !== undefined) ? vm.meta.count : (vm.rows ? vm.rows.length : 0);
        })
        .catch(function (err) {
          vm.rows = [];
          vm.total = 0;
          vm.meta = { count: 0 };
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load invoices.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function search() {
      load();
    }

    function clearFilters() {
      vm.filters.student_id = '';
      vm.filters.student_number = '';
      vm.filters.term = '';
      vm.filters.syid = '';
      vm.filters.type = '';
      vm.filters.status = '';
      vm.filters.campus_id = '';
      vm.filters.registration_id = '';
      load();
    }

    function create() {
      $location.path('/admin/invoices/new');
    }

    function edit(row) {
      if (!row || !row.id) return;
      $location.path('/admin/invoices/' + row.id + '/edit');
    }

    function pdf(row) {
      if (!row || !row.id) return;
      AdminInvoicesService.pdf(row.id)
        .catch(function (err) {
          var m = (err && (err.message || (err.data && err.data.message))) || 'Failed to open PDF.';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        });
    }

    function removeItem(row) {
      if (!row || !row.id) return;
      var doRemove = function () {
        vm.loading = true;
        AdminInvoicesService.remove(row.id)
          .then(function () {
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' });
            load();
          })
          .catch(function (err) {
            var m = (err && (err.message || (err.data && err.data.message))) || 'Delete failed';
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
          })
          .finally(function () { vm.loading = false; });
      };

      if (window.Swal) {
        Swal.fire({
          title: 'Delete Invoice',
          text: 'This will permanently delete the invoice. Continue?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete'
        }).then(function (res) {
          if (res.isConfirmed) doRemove();
        });
      } else {
        if (confirm('Delete this invoice?')) doRemove();
      }
    }
  }

})();
