(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('EnlistmentApplicantsController', EnlistmentApplicantsController);

  EnlistmentApplicantsController.$inject = ['$scope', '$timeout', 'ApplicantsService', 'TermService'];
  function EnlistmentApplicantsController($scope, $timeout, ApplicantsService, TermService) {
    var vm = this;

    // Request coalescing/guards
    vm._inFlight = false;
    vm._debounce = null;
    vm._lastKey = null;

    vm.title = 'Enlistment Applicants';
    vm.loading = false;
    vm.error = null;

    vm.rows = [];
    vm.total = 0;
    vm.meta = { current_page: 1, per_page: 10, total: 0, last_page: 1 };

    vm.selectedTerm = null;

    vm.filters = {
      search: '',
      sort: 'application_created_at',
      order: 'desc',
      page: 1,
      per_page: 10,
      syid: ''
    };

    // Methods
    vm.load = load;
    vm.search = search;
    vm.clearFilters = clearFilters;
    vm.changeSort = changeSort;
    vm.changePage = changePage;
    vm.changePerPage = changePerPage;

    vm.fullName = function (r) {
      var ln = (r && r.strLastname) ? ('' + r.strLastname).toUpperCase() : '';
      var fn = (r && r.strFirstname) ? r.strFirstname : '';
      return (ln && fn) ? (ln + ', ' + fn) : (ln || fn || '(no name)');
    };

    vm.readablePaid = function (v) {
      try {
        return (v === 1 || v === true || v === '1') ? 'Yes' : 'No';
      } catch (e) {
        return 'No';
      }
    };

    activate();

    function activate() {
      // Initialize global term and load once
      if (TermService && TermService.init) {
        TermService.init().then(function () {
          try {
            var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
            vm.selectedTerm = sel || null;
            var tid = (sel && sel.intID) ? sel.intID : '';
            vm.filters.syid = tid;
          } catch (e) {
            vm.selectedTerm = null;
            vm.filters.syid = '';
          }
          load(); // load once after init using current term
        });
      } else {
        load();
      }

      // React to global term changes; only load if syid actually changes
      if ($scope && $scope.$on) {
        $scope.$on('termChanged', function (event, data) {
          try {
            if (data && data.selectedTerm) {
              var newSel = data.selectedTerm || null;
              var newTid = (newSel && newSel.intID) ? newSel.intID : '';
              var prev = (vm.filters.syid !== undefined && vm.filters.syid !== null) ? String(vm.filters.syid) : '';
              if (String(newTid) !== prev) {
                vm.selectedTerm = newSel;
                vm.filters.syid = newTid;
                vm.filters.page = 1;
                load();
              }
            }
          } catch (e) {}
        });
      }

      // Clean up pending debounce on destroy
      if ($scope && $scope.$on) {
        $scope.$on('$destroy', function () {
          try { if (vm._debounce) { $timeout.cancel(vm._debounce); } } catch (e) {}
        });
      }
    }

    function normalizeResponse(res) {
      // ApplicantsService returns wrapper { success, data, meta? }
      var data = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
      var meta = (res && res.meta) ? res.meta : null;
      return { data: data, meta: meta };
    }

    // Build a stable key for current filter state to avoid redundant same-params requests
    function _buildKey() {
      try {
        return [
          vm.filters.search || '',
          vm.filters.sort || '',
          vm.filters.order || '',
          vm.filters.page || 1,
          vm.filters.per_page || 10,
          vm.filters.syid || ''
        ].join('|');
      } catch (e) {
        return '';
      }
    }

    // Debounced public loader: coalesces multiple triggers within a short window
    function load() {
      if (vm._debounce) {
        $timeout.cancel(vm._debounce);
      }
      vm._debounce = $timeout(_doLoad, 150);
    }

    // Actual loader (non-debounced) with in-flight + same-key guards
    function _doLoad() {
      var key = _buildKey();
      if (vm._inFlight) {
        return;
      }
      if (vm._lastKey === key && (vm.rows && vm.rows.length)) {
        return;
      }
      vm._inFlight = true;
      vm.loading = true;
      vm.error = null;

      ApplicantsService.listEligible(vm.filters)
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
          vm._lastKey = key;
        })
        .catch(function (err) {
          vm.rows = [];
          vm.total = 0;
          vm.meta.current_page = 1;
          vm.meta.last_page = 1;
          vm.meta.total = 0;
          vm.error = (err && (err.message || (err.data && err.data.message))) || 'Failed to load enlistment applicants.';
        })
        .finally(function () {
          vm.loading = false;
          vm._inFlight = false;
        });
    }

    function search() {
      vm.filters.page = 1;
      load();
    }

    function clearFilters() {
      vm.filters.search = '';
      vm.filters.sort = 'application_created_at';
      vm.filters.order = 'desc';
      vm.filters.page = 1;
      // Keep syid synced to selectedTerm
      try {
        var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : vm.selectedTerm;
        vm.filters.syid = (sel && sel.intID) ? sel.intID : '';
      } catch (e) {
        vm.filters.syid = '';
      }
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
  }

})();
