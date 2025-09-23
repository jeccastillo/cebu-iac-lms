(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdvisorsQuickViewController', AdvisorsQuickViewController);

  AdvisorsQuickViewController.$inject = ['$scope', '$rootScope', '$http', 'APP_CONFIG', 'CampusService', 'StudentAdvisorService'];
  function AdvisorsQuickViewController($scope, $rootScope, $http, APP_CONFIG, CampusService, StudentAdvisorService) {
    var vm = this;

    // UI State
    vm.loading = false;
    vm.error = null;
    vm.items = [];
    vm.total = 0;

    // Pagination
    vm.page = 1;
    vm.perPage = 20;
    vm.totalPages = 1;
    vm.meta = null;

    // Advisor filter: 'all' | 'with' | 'without'
    vm.hasAdvisor = 'all';

    // Campus
    vm.selectedCampus = null;

    // Methods
    vm.refresh = refresh;
    vm.nextPage = nextPage;
    vm.prevPage = prevPage;
    vm.goToPage = goToPage;
    vm.onPerPageChange = onPerPageChange;
    vm.onAdvisorFilterChange = onAdvisorFilterChange;
    vm.getAdvisorName = getAdvisorName;

    activate();

    function activate() {
      vm.loading = true;
      vm.error = null;

      var p = (CampusService && CampusService.init) ? CampusService.init() : null;
      if (p && p.then) {
        p.then(function () {
          setCampusFromService();
          return loadList();
        }).catch(function () {
          setCampusFromService();
          return loadList();
        }).finally(function () {
          vm.loading = false;
        });
      } else {
        setCampusFromService();
        loadList().finally(function () {
          vm.loading = false;
        });
      }

      // React to global campus changes
      $scope.$on('campusChanged', function () {
        setCampusFromService();
        vm.page = 1;
        refresh();
      });
    }

    function setCampusFromService() {
      try {
        var c = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        vm.selectedCampus = c || null;
      } catch (e) {
        vm.selectedCampus = null;
      }
    }

    function refresh() {
      vm.loading = true;
      vm.error = null;
      loadList().finally(function () {
        vm.loading = false;
      });
    }

    function loadList() {
      var campusId = null;
      try {
        campusId = (vm.selectedCampus && vm.selectedCampus.id != null) ? parseInt(vm.selectedCampus.id, 10) : null;
      } catch (e) {
        campusId = null;
      }

      return StudentAdvisorService.listByCampus({
        campusId: campusId,
        page: vm.page,
        perPage: vm.perPage,
        hasAdvisor: (vm.hasAdvisor === 'with' || vm.hasAdvisor === 'without') ? vm.hasAdvisor : undefined
      })
        .then(function (res) {
          // res is unwrapped payload: { success, data, meta }
          var rows = res && res.data ? res.data : [];
          var meta = res && res.meta ? res.meta : null;

          vm.items = Array.isArray(rows) ? rows : [];
          vm.meta = meta;
          // Prefer total from meta; fallback to items length
          var total = (meta && meta.total != null) ? parseInt(meta.total, 10) : vm.items.length;
          var perPage = (meta && meta.per_page != null) ? parseInt(meta.per_page, 10) : vm.perPage;
          vm.total = isFinite(total) ? total : vm.items.length;
          vm.perPage = isFinite(perPage) && perPage > 0 ? perPage : vm.perPage;
          vm.totalPages = vm.perPage > 0 ? Math.max(1, Math.ceil(vm.total / vm.perPage)) : 1;

          // Clamp current page within bounds (in case campus change reduced total)
          if (vm.page > vm.totalPages) {
            vm.page = vm.totalPages;
            return loadList();
          }
        })
        .catch(function (err) {
          vm.items = [];
          vm.total = 0;
          vm.totalPages = 1;
          vm.meta = null;
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load students with advisors.';
        });
    }

    function nextPage() {
      if (vm.page < vm.totalPages) {
        vm.page += 1;
        refresh();
      }
    }

    function prevPage() {
      if (vm.page > 1) {
        vm.page -= 1;
        refresh();
      }
    }

    function goToPage(p) {
      var np = parseInt(p, 10);
      if (isFinite(np) && np >= 1 && np <= vm.totalPages) {
        vm.page = np;
        refresh();
      }
    }

    function onPerPageChange() {
      vm.page = 1;
      refresh();
    }

    function onAdvisorFilterChange() {
      vm.page = 1;
      refresh();
    }

    function getAdvisorName(row) {
      if (!row) return '-';
      var n = (row.advisor_name || '').trim();
      if (n.length > 0) return n;
      if (row.advisor_id != null) return '' + row.advisor_id;
      return '-';
    }
  }
})();
