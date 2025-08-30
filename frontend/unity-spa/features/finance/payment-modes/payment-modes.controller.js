(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('PaymentModesController', PaymentModesController)
    .controller('PaymentModeEditController', PaymentModeEditController);

  PaymentModesController.$inject = ['$location', 'PaymentModesService'];
  function PaymentModesController($location, PaymentModesService) {
    var vm = this;

    vm.title = 'Payment Modes';
    vm.error = null;
    vm.total = 0;

    // Row actions dropdown state (for Actions menu like Students page)
    vm.menuOpenId = null;
    vm.toggleMenu = function (id) { vm.menuOpenId = (vm.menuOpenId === id ? null : id); };
    vm.isMenuOpen = function (id) { return vm.menuOpenId === id; };
    vm.closeMenu = function () { vm.menuOpenId = null; };

    // Client-side per-column filters (Name, Type, Channel, Method)
    vm.cf = { name: '', type: '', pchannel: '', pmethod: '' };
    vm.filteredRows = function () {
      var rows = vm.rows || [];
      var cf = vm.cf || {};
      function has(v, q) {
        if (!q) return true;
        var s = (v === null || v === undefined) ? '' : ('' + v);
        return s.toLowerCase().indexOf(('' + q).toLowerCase()) !== -1;
      }
      return rows.filter(function (r) {
        return has(r.name, cf.name)
          && has(r.type, cf.type)
          && has(r.pchannel, cf.pchannel)
          && has(r.pmethod, cf.pmethod);
      });
    };

    // State
    vm.loading = false;
    vm.rows = [];
    vm.meta = { current_page: 1, per_page: 10, total: 0, last_page: 1 };
    vm.filters = {
      search: '',
      type: '',
      pchannel: '',
      pmethod: '',
      is_active: '',
      is_nonbank: '',
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
    vm.restore = restoreItem;
    vm.toggleActive = toggleActive;

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
      PaymentModesService.list(vm.filters)
        .then(function (res) {
          var r = normalizeResponse(res);
          // unwrap resource collection (it is already array-like)
          vm.rows = Array.isArray(r.data) ? r.data : [];
          if (r.meta) {
            vm.meta.current_page = r.meta.current_page || 1;
            vm.meta.per_page = r.meta.per_page || vm.filters.per_page;
            vm.meta.total = r.meta.total || vm.rows.length;
            vm.meta.last_page = r.meta.last_page || 1;
          } else {
            // Non-paginated response
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
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load payment modes.';
        })
        .finally(function () { vm.loading = false; });
    }

    function search() {
      vm.filters.page = 1;
      load();
    }

    function clearFilters() {
      vm.filters.search = '';
      vm.filters.type = '';
      vm.filters.pchannel = '';
      vm.filters.pmethod = '';
      vm.filters.is_active = '';
      vm.filters.is_nonbank = '';
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
      $location.path('/finance/payment-modes/new');
    }

    function edit(row) {
      if (!row || row.id === undefined || row.id === null) return;
      $location.path('/finance/payment-modes/' + row.id + '/edit');
    }

    function removeItem(row) {
      if (!row || !row.id) return;
      var doRemove = function () {
        vm.loading = true;
        PaymentModesService.remove(row.id)
          .then(function () {
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' });
            load();
          })
          .catch(function (err) { alertErr(err, 'Delete failed'); })
          .finally(function () { vm.loading = false; });
      };
      if (window.Swal) {
        Swal.fire({
          title: 'Delete Payment Mode',
          text: 'This will soft-delete the record. Continue?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete'
        }).then(function (res) {
          if (res.isConfirmed) doRemove();
        });
      } else {
        if (confirm('Delete this payment mode?')) doRemove();
      }
    }

    function restoreItem(row) {
      if (!row || !row.id) return;
      vm.loading = true;
      PaymentModesService.restore(row.id)
        .then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Restored' });
          load();
        })
        .catch(function (err) { alertErr(err, 'Restore failed'); })
        .finally(function () { vm.loading = false; });
    }

    function toggleActive(row) {
      if (!row || !row.id) return;
      var newVal = !row.is_active;
      vm.loading = true;
      PaymentModesService.update(row.id, { is_active: newVal })
        .then(function () {
          row.is_active = newVal;
        })
        .catch(function (err) { alertErr(err, 'Toggle failed'); })
        .finally(function () { vm.loading = false; });
    }

    function alertErr(err, fallback) {
      var m = (err && err.message) || (err && err.data && err.data.message) || fallback || 'Operation failed';
      if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
    }
  }

  PaymentModeEditController.$inject = ['$routeParams', '$location', 'PaymentModesService'];
  function PaymentModeEditController($routeParams, $location, PaymentModesService) {
    var vm = this;
    vm.loading = false;
    vm.id = ($routeParams && $routeParams.id) ? parseInt($routeParams.id, 10) : null;
    vm.isCreate = !vm.id;

    vm.model = {
      name: '',
      image_url: '',
      type: '',
      charge: 0,
      is_active: true,
      pchannel: '',
      pmethod: '',
      is_nonbank: false
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
      PaymentModesService.show(vm.id)
        .then(function (res) {
          var d = (res && res.data) ? res.data : res;
          if (!d) return;
          vm.model = {
            name: d.name || '',
            image_url: d.image_url || '',
            type: d.type || '',
            charge: (typeof d.charge === 'number') ? d.charge : parseFloat(d.charge || '0') || 0,
            is_active: !!d.is_active,
            pchannel: d.pchannel || '',
            pmethod: d.pmethod || '',
            is_nonbank: !!d.is_nonbank
          };
        })
        .finally(function () { vm.loading = false; });
    }

    function save() {
      // Basic client-side validation
      if (!vm.model.name || !vm.model.type || !vm.model.pchannel || !vm.model.pmethod) {
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Missing required fields', text: 'Name, Type, Channel, and Method are required.' });
        return;
      }
      var payload = Object.assign({}, vm.model);
      payload.charge = (payload.charge !== null && payload.charge !== '' && !isNaN(payload.charge)) ? parseFloat(payload.charge) : 0;

      vm.loading = true;
      var p = vm.isCreate
        ? PaymentModesService.create(payload)
        : PaymentModesService.update(vm.id, payload);

      p.then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Saved' });
          $location.path('/finance/payment-modes');
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Save failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function cancel() {
      $location.path('/finance/payment-modes');
    }
  }

})();
