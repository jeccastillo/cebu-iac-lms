(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('PaymentDescriptionsController', PaymentDescriptionsController)
    .controller('PaymentDescriptionEditController', PaymentDescriptionEditController);

  PaymentDescriptionsController.$inject = ['$location', 'PaymentDescriptionsService'];
  function PaymentDescriptionsController($location, PaymentDescriptionsService) {
    var vm = this;

    vm.title = 'Payment Descriptions';
    vm.error = null;
    vm.total = 0;

    // Row actions dropdown state (same UX pattern used elsewhere)
    vm.menuOpenId = null;
    vm.toggleMenu = function (id) { vm.menuOpenId = (vm.menuOpenId === id ? null : id); };
    vm.isMenuOpen = function (id) { return vm.menuOpenId === id; };
    vm.closeMenu = function () { vm.menuOpenId = null; };

    // Client-side per-column filters (Name only)
    vm.cf = { name: '' };
    vm.filteredRows = function () {
      var rows = vm.rows || [];
      var cf = vm.cf || {};
      function has(v, q) {
        if (!q) return true;
        var s = (v === null || v === undefined) ? '' : ('' + v);
        return s.toLowerCase().indexOf(('' + q).toLowerCase()) !== -1;
      }
      return rows.filter(function (r) {
        return has(r.name, cf.name);
      });
    };

    // State
    vm.loading = false;
    vm.rows = [];
    vm.meta = { current_page: 1, per_page: 10, total: 0, last_page: 1 };
    vm.filters = {
      search: '',
      sort: 'name',
      order: 'asc',
      page: 1,
      per_page: 10
    };

    // Methods
    vm.load = load;
    vm.search = search;
    vm.clearFilters = clearFilters;
    vm.changeSort = changeSort;
    vm.changePage = changePage;
    vm.changePerPage = changePerPage;
    vm.add = add;
    vm.edit = edit;
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
      PaymentDescriptionsService.list(vm.filters)
        .then(function (res) {
          var r = normalizeResponse(res);
          vm.rows = Array.isArray(r.data) ? r.data : [];
          if (r.meta) {
            vm.meta.current_page = r.meta.current_page || 1;
            vm.meta.per_page = r.meta.per_page || vm.filters.per_page;
            vm.meta.total = r.meta.total || vm.rows.length;
            vm.meta.last_page = r.meta.last_page || 1;
          } else {
            vm.meta.current_page = 1;
            vm.meta.last_page = 1;
            vm.meta.total = vm.rows.length;
          }
          vm.total = vm.meta.total || (vm.rows ? vm.rows.length : 0);
        })
        .catch(function (err) {
          vm.rows = [];
          vm.total = 0;
          vm.meta.current_page = 1;
          vm.meta.last_page = 1;
          vm.meta.total = 0;
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load payment descriptions.';
        })
        .finally(function () { vm.loading = false; });
    }

    function search() {
      vm.filters.page = 1;
      load();
    }

    function clearFilters() {
      vm.filters.search = '';
      vm.filters.sort = 'name';
      vm.filters.order = 'asc';
      vm.filters.page = 1;
      load();
    }

    function changeSort(field) {
      if (vm.filters.sort === field) {
        vm.filters.order = (vm.filters.order === 'asc') ? 'desc' : 'asc';
      } else {
        vm.filters.sort = field;
        vm.filters.order = 'asc';
      }
      load();
    }

    function changePage(delta) {
      var p = (vm.filters.page || 1) + delta;
      if (p < 1) p = 1;
      if (vm.meta && vm.meta.last_page && p > vm.meta.last_page) p = vm.meta.last_page;
      vm.filters.page = p;
      load();
    }

    function changePerPage() {
      vm.filters.page = 1;
      load();
    }

    function add() {
      $location.path('/finance/payment-descriptions/new');
    }

    function edit(row) {
      if (!row || (row.intID === undefined || row.intID === null)) return;
      $location.path('/finance/payment-descriptions/' + row.intID + '/edit');
    }

    function removeItem(row) {
      if (!row || !row.intID) return;
      var doRemove = function () {
        vm.loading = true;
        PaymentDescriptionsService.remove(row.intID)
          .then(function () {
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' });
            load();
          })
          .catch(function (err) { alertErr(err, 'Delete failed'); })
          .finally(function () { vm.loading = false; });
      };
      if (window.Swal) {
        Swal.fire({
          title: 'Delete Payment Description',
          text: 'This will permanently delete the record. Continue?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete'
        }).then(function (res) {
          if (res.isConfirmed) doRemove();
        });
      } else {
        if (confirm('Delete this payment description?')) doRemove();
      }
    }

    function alertErr(err, fallback) {
      var m = (err && err.message) || (err && err.data && err.data.message) || fallback || 'Operation failed';
      if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
    }
  }

  PaymentDescriptionEditController.$inject = ['$routeParams', '$location', 'PaymentDescriptionsService'];
  function PaymentDescriptionEditController($routeParams, $location, PaymentDescriptionsService) {
    var vm = this;
    vm.loading = false;
    vm.id = ($routeParams && $routeParams.id) ? parseInt($routeParams.id, 10) : null;
    vm.isCreate = !vm.id;

    vm.model = {
      name: '',
      amount: null
    };

    vm.save = save;
    vm.cancel = cancel;
    vm.load = load;

    activate();

    function activate() {
      if (!vm.isCreate) {
        load();
      }
    }

    function load() {
      vm.loading = true;
      PaymentDescriptionsService.show(vm.id)
        .then(function (res) {
          var d = (res && res.data) ? res.data : res;
          if (!d) return;
          vm.model = {
            name: d.name || '',
            amount: (d.amount === null || d.amount === undefined || d.amount === '') ? null : parseFloat(d.amount)
          };
        })
        .finally(function () { vm.loading = false; });
    }

    function save() {
      // Basic client-side validation
      if (!vm.model.name) {
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Missing required field', text: 'Name is required.' });
        return;
      }
      var payload = Object.assign({}, vm.model);
      if (payload.amount === '' || payload.amount === null || payload.amount === undefined || isNaN(payload.amount)) {
        payload.amount = null;
      } else {
        payload.amount = parseFloat(payload.amount);
      }

      vm.loading = true;
      var p = vm.isCreate
        ? PaymentDescriptionsService.create(payload)
        : PaymentDescriptionsService.update(vm.id, payload);

      p.then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Saved' });
          $location.path('/finance/payment-descriptions');
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Save failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function cancel() {
      $location.path('/finance/payment-descriptions');
    }
  }

})();
