(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('RequirementsController', RequirementsController)
    .controller('RequirementEditController', RequirementEditController);

  RequirementsController.$inject = ['$location', 'RequirementsService'];
  function RequirementsController($location, RequirementsService) {
    var vm = this;

    vm.title = 'Application Requirements';
    vm.error = null;
    vm.total = 0;

    // Row actions dropdown state
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
    vm.typeOptions = [
      { value: '', label: 'All' },
      { value: 'college', label: 'College' },
      { value: 'shs', label: 'Senior High' },
      { value: 'grad', label: 'Graduate' }
    ];
    vm.foreignOptions = [
      { value: '', label: 'All' },
      { value: 'true', label: 'Foreign: Yes' },
      { value: 'false', label: 'Foreign: No' }
    ];
    vm.filters = {
      search: '',
      type: '',
      is_foreign: '',
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
      RequirementsService.list(vm.filters)
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
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load requirements.';
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
      vm.filters.is_foreign = '';
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
      $location.path('/admissions/requirements/new');
    }

    function edit(row) {
      if (!row || (row.id === undefined || row.id === null)) return;
      $location.path('/admissions/requirements/' + row.id + '/edit');
    }

    function removeItem(row) {
      if (!row || !row.id) return;
      var doRemove = function () {
        vm.loading = true;
        RequirementsService.remove(row.id)
          .then(function () {
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted' });
            load();
          })
          .catch(function (err) { alertErr(err, 'Delete failed'); })
          .finally(function () { vm.loading = false; });
      };
      if (window.Swal) {
        Swal.fire({
          title: 'Delete Requirement',
          text: 'This will permanently delete the record. Continue?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete'
        }).then(function (res) {
          if (res.isConfirmed) doRemove();
        });
      } else {
        if (confirm('Delete this requirement?')) doRemove();
      }
    }

    function alertErr(err, fallback) {
      var m = (err && err.message) || (err && err.data && err.data.message) || fallback || 'Operation failed';
      if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
    }
  }

  RequirementEditController.$inject = ['$routeParams', '$location', 'RequirementsService'];
  function RequirementEditController($routeParams, $location, RequirementsService) {
    var vm = this;
    vm.loading = false;
    vm.id = ($routeParams && $routeParams.id) ? parseInt($routeParams.id, 10) : null;
    vm.isCreate = !vm.id;

    vm.typeOptions = [
      { value: 'college', label: 'College' },
      { value: 'shs', label: 'Senior High' },
      { value: 'grad', label: 'Graduate' }
    ];

    vm.model = {
      name: '',
      type: 'college',
      is_foreign: false,
      is_initial_requirements: false
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
      RequirementsService.show(vm.id)
        .then(function (res) {
          var d = (res && res.data) ? res.data : res;
          if (!d) return;
          vm.model = {
            name: d.name || '',
            type: d.type || 'college',
            is_foreign: !!d.is_foreign,
            is_initial_requirements: !!d.is_initial_requirements
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
      if (!vm.model.type || ['college', 'shs', 'grad'].indexOf(vm.model.type) === -1) {
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Invalid type', text: 'Please select a valid type.' });
        return;
      }

      // Shallow copy and normalize types
      var payload = {
        name: vm.model.name,
        type: vm.model.type,
        is_foreign: !!vm.model.is_foreign,
        is_initial_requirements: !!vm.model.is_initial_requirements
      };

      vm.loading = true;
      var p = vm.isCreate
        ? RequirementsService.create(payload)
        : RequirementsService.update(vm.id, payload);

      p.then(function () {
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Saved' });
          $location.path('/admissions/requirements');
        })
        .catch(function (err) {
          var m = (err && err.message) || (err && err.data && err.data.message) || 'Save failed';
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m });
        })
        .finally(function () { vm.loading = false; });
    }

    function cancel() {
      $location.path('/admissions/requirements');
    }
  }

})();
