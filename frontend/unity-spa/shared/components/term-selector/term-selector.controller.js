(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('TermSelectorController', TermSelectorController);

  TermSelectorController.$inject = ['$scope', 'TermService'];
  function TermSelectorController($scope, TermService) {
    var vm = this;

    // Component state
    vm.selectedTerm = null;
    vm.availableTerms = [];
    vm.loading = false;
    vm.error = null;
    vm.dropdownOpen = false;

    // Filter properties
    vm.filterStudentType = '';
    vm.filterYear = '';
    vm.filterSemester = '';

    // Component options (from directive scope)
    vm.compact = vm.compact === 'true';
    vm.showLabel = vm.showLabel !== 'false'; // Default to true

    // Public methods
    vm.toggleDropdown = toggleDropdown;
    vm.selectTerm = selectTerm;
    vm.closeDropdown = closeDropdown;
    vm.refresh = refresh;
    vm.filteredTerms = filteredTerms;
    vm.clearFilters = clearFilters;

    // Initialize
    activate();

    function activate() {
      // Sync with TermService state
      vm.selectedTerm = TermService.getSelectedTerm();
      vm.availableTerms = TermService.availableTerms;
      vm.loading = TermService.loading;
      vm.error = TermService.error;

      // Listen for term changes from other components
      $scope.$on('termChanged', function(event, data) {
        vm.selectedTerm = data.selectedTerm;
        vm.availableTerms = data.availableTerms;
        vm.closeDropdown();
      });

      // Watch TermService state changes
      $scope.$watch(function() {
        return {
          loading: TermService.loading,
          error: TermService.error,
          availableTerms: TermService.availableTerms,
          selectedTerm: TermService.selectedTerm
        };
      }, function(newVal) {
        vm.loading = newVal.loading;
        vm.error = newVal.error;
        vm.availableTerms = newVal.availableTerms;
        vm.selectedTerm = newVal.selectedTerm;
      }, true);

      // Close dropdown when clicking outside
      $scope.$on('$destroy', function() {
        angular.element(document).off('click.termSelector');
      });

      // Setup outside click handler
      angular.element(document).on('click.termSelector', function(event) {
        var target = angular.element(event.target);
        var termSelector = target.closest('.term-selector');
        if (termSelector.length === 0 && vm.dropdownOpen) {
          $scope.$apply(function() {
            vm.closeDropdown();
          });
        }
      });
    }

    function toggleDropdown() {
      vm.dropdownOpen = !vm.dropdownOpen;
    }

    function selectTerm(term) {
      if (term && term.intID !== (vm.selectedTerm && vm.selectedTerm.intID)) {
        TermService.setSelectedTerm(term);
      }
      vm.closeDropdown();
    }

    function closeDropdown() {
      vm.dropdownOpen = false;
      // Reset filters when dropdown closes
      vm.clearFilters();
    }

    function refresh() {
      TermService.clearCache();
      TermService.loadTerms();
    }

    function filteredTerms() {
      if (!vm.availableTerms || !vm.availableTerms.length) {
        return [];
      }

      var studentTypeFilter = (vm.filterStudentType || '').toLowerCase().trim();
      var yearFilter = (vm.filterYear || '').trim();
      var semesterFilter = (vm.filterSemester || '').toLowerCase().trim();

      // If no filters applied, return all terms
      if (!studentTypeFilter && !yearFilter && !semesterFilter) {
        return vm.availableTerms;
      }

      return vm.availableTerms.filter(function(term) {
        var matches = true;

        // Filter by student type (partial match)
        if (studentTypeFilter) {
          var termStudentType = (term.term_student_type || '').toLowerCase();
          matches = matches && termStudentType.indexOf(studentTypeFilter) !== -1;
        }

        // Filter by year (matches either start or end year)
        if (yearFilter && matches) {
          var yearStart = (term.strYearStart || '').toString();
          var yearEnd = (term.strYearEnd || '').toString();
          matches = matches && (yearStart.indexOf(yearFilter) !== -1 || yearEnd.indexOf(yearFilter) !== -1);
        }

        // Filter by semester/term (partial match)
        if (semesterFilter && matches) {
          var enumSem = (term.enumSem || '').toLowerCase();
          matches = matches && enumSem.indexOf(semesterFilter) !== -1;
        }

        return matches;
      });
    }

    function clearFilters() {
      vm.filterStudentType = '';
      vm.filterYear = '';
      vm.filterSemester = '';
    }
  }

})();
