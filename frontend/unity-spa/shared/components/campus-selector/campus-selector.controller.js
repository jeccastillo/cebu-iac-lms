(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CampusSelectorController', CampusSelectorController);

  CampusSelectorController.$inject = ['$scope', 'CampusService'];
  function CampusSelectorController($scope, CampusService) {
    var vm = this;

    // Component state
    vm.selectedCampus = null;
    vm.availableCampuses = [];
    vm.loading = false;
    vm.error = null;
    vm.dropdownOpen = false;

    // Component options (from directive scope)
    vm.compact = vm.compact === 'true';
    vm.showLabel = vm.showLabel !== 'false'; // Default to true

    // Public methods
    vm.toggleDropdown = toggleDropdown;
    vm.selectCampus = selectCampus;
    vm.closeDropdown = closeDropdown;
    vm.refresh = refresh;

    // Initialize
    activate();

    function activate() {
      // Sync with CampusService state
      vm.selectedCampus = CampusService.getSelectedCampus && CampusService.getSelectedCampus();
      vm.availableCampuses = CampusService.availableCampuses || [];
      vm.loading = CampusService.loading || false;
      vm.error = CampusService.error || null;

      // Listen for campus changes from other components
      $scope.$on('campusChanged', function (event, data) {
        vm.selectedCampus = data.selectedCampus;
        vm.availableCampuses = data.availableCampuses;
        vm.closeDropdown();
      });

      // Watch CampusService state changes
      $scope.$watch(function () {
        return {
          loading: CampusService.loading,
          error: CampusService.error,
          availableCampuses: CampusService.availableCampuses,
          selectedCampus: CampusService.selectedCampus
        };
      }, function (newVal) {
        vm.loading = newVal.loading;
        vm.error = newVal.error;
        vm.availableCampuses = newVal.availableCampuses;
        vm.selectedCampus = newVal.selectedCampus;
      }, true);

      // Clean up outside click handler
      $scope.$on('$destroy', function () {
        angular.element(document).off('click.campusSelector');
      });

      // Setup outside click handler to close dropdown
      angular.element(document).on('click.campusSelector', function (event) {
        var target = angular.element(event.target);
        var campusSelector = target.closest('.campus-selector');
        if (campusSelector.length === 0 && vm.dropdownOpen) {
          $scope.$apply(function () {
            vm.closeDropdown();
          });
        }
      });
    }

    function toggleDropdown() {
      vm.dropdownOpen = !vm.dropdownOpen;
    }

    function selectCampus(campus) {
      if (!campus) return;
      var currentId = vm.selectedCampus ? ('' + vm.selectedCampus.id) : null;
      if (('' + campus.id) !== currentId) {
        CampusService.setSelectedCampus(campus);
      }
      vm.closeDropdown();
    }

    function closeDropdown() {
      vm.dropdownOpen = false;
    }

    function refresh() {
      CampusService.clearCache();
      CampusService.loadCampuses();
    }
  }

})();
