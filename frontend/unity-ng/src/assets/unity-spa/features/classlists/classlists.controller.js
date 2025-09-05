(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClasslistsController', ClasslistsController);

  ClasslistsController.$inject = ['$scope', '$location', 'ClasslistsService', 'TermService'];
  function ClasslistsController($scope, $location, ClasslistsService, TermService) {
    var vm = this;

    // State
    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.records = [];
    vm.subjects = [];
    vm.faculty = [];

    vm.includeDissolved = false;
    vm.filters = {
      term: '',
      intSubjectID: '',
      intFacultyID: '',
      intFinalized: '',
      sectionCode: ''
    };

    // Expose
    vm.init = init;
    vm.reload = reload;
    vm.goAdd = goAdd;
    vm.goEdit = goEdit;
    vm.goView = goView;
    vm.onTermChange = onTermChange;
    vm.dissolve = dissolve;
    vm.resetFilters = resetFilters;
    vm.nextPage = nextPage;
    vm.prevPage = prevPage;
    vm.onPerPageChange = onPerPageChange;
    vm.goToPage = goToPage;

    vm.finalizedOptions = [
      { value: '', label: 'All' },
      { value: 0, label: 'Draft/0' },
      { value: 1, label: 'Midterm/1' },
      { value: 2, label: 'Final/2' }
    ];

    // Pagination state
    vm.page = 1;
    vm.perPage = 10;
    vm.total = 0;
    vm.lastPage = 1;
    vm.meta = {};
    vm.perPageOptions = [10, 20, 50, 100];

    // Internal flags to prevent duplicate loads
    vm._booting = true;
    vm._loadingList = false;
    vm._reloadPromise = null;

    function init() {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      // Ensure term service initialized (also pulls campus scope)
      TermService.init()
        .then(function () {
          var sel = TermService.getSelectedTerm();
          if (sel && sel.intID) {
            vm.filters.term = sel.intID;
            vm.termLabel = formatTermLabel(sel);
          } else {
            vm.termLabel = '';
          }
        })
        .then(loadFilterOptions)
        .then(reload)
        .catch(function (e) {
          vm.error = 'Failed to initialize Classlists';
          console.error('Classlists init error:', e);
        })
        .finally(function () {
          vm.loading = false;
          vm._booting = false;
        });

      // React to term changes broadcasted by TermService
      $scope.$on('termChanged', function (evt, payload) {
        if (vm._booting) return;
        var sel = TermService.getSelectedTerm();
        if (sel && sel.intID) {
          if (vm.filters.term === sel.intID) {
            return;
          }
          vm.filters.term = sel.intID;
          vm.termLabel = formatTermLabel(sel);
          onTermChange();
        }
      });
    }

    function loadFilterOptions() {
      var termId = vm.filters.term;

      var p1 = ClasslistsService.getFacultyOptions()
        .then(function (data) {
          // Expect data = { success: true, data: [...] }
          vm.faculty = (data && data.data) ? data.data : [];
        })
        .catch(function (e) {
          console.warn('Failed loading faculty options', e);
          vm.faculty = [];
        });

      var p2 = ClasslistsService.getSubjectsByTerm(termId)
        .then(function (data) {
          // Normalized in service
          vm.subjects = (data && data.data) ? data.data : [];
        })
        .catch(function (e) {
          console.warn('Failed loading subject options', e);
          vm.subjects = [];
        });

      return Promise.all([p1, p2]);
    }

    function formatTermLabel(term) {
      if (!term) return '';
      var sem = term.enumSem || term.sem || '';
      var ys = term.strYearStart || term.yearStart || '';
      var ye = term.strYearEnd || term.yearEnd || '';
      // ordinal suffix conversion
      var n = parseInt(sem, 10);
      var ord = sem;
      if (!isNaN(n)) {
        var suffix = 'th';
        if (n % 100 < 11 || n % 100 > 13) {
          if (n % 10 === 1) suffix = 'st';
          else if (n % 10 === 2) suffix = 'nd';
          else if (n % 10 === 3) suffix = 'rd';
        }
        ord = n + suffix;
      }
      var span = (ys && ye) ? (ys + '-' + ye) : '';
      return [ord, 'Term', span].filter(Boolean).join(' ');
    }

    function reload() {
      if (vm._loadingList) {
        return vm._reloadPromise || Promise.resolve();
      }
      vm._loadingList = true;
      vm.loading = true;
      vm.error = null;

      var opts = {
        includeDissolved: !!vm.includeDissolved,
        term: vm.filters.term,
        intSubjectID: vm.filters.intSubjectID || undefined,
        intFacultyID: vm.filters.intFacultyID || undefined,
        intFinalized: vm.filters.intFinalized !== '' ? vm.filters.intFinalized : undefined,
        sectionCode: (vm.filters.sectionCode && vm.filters.sectionCode.trim() !== '') ? vm.filters.sectionCode.trim() : undefined,
        page: vm.page,
        per_page: vm.perPage
      };

      return (vm._reloadPromise = ClasslistsService.list(opts)
        .then(function (data) {
          vm.records = (data && data.data) ? data.data : [];
          vm.meta = (data && data.meta) ? data.meta : {};
          vm.total = vm.meta.total || (vm.records ? vm.records.length : 0);
          vm.lastPage = vm.meta.last_page || 1;
          // If current page is beyond last page due to filter/perPage change, snap back;
          // avoid immediate second reload to prevent double API calls on load.
          if (vm.page > vm.lastPage && vm.lastPage >= 1) {
            vm.page = vm.lastPage;
          }
        })
        .catch(function (e) {
          vm.error = 'Failed to load classlists';
          console.error('Classlists reload error:', e);
          vm.records = [];
          vm.meta = {};
          vm.total = 0;
          vm.lastPage = 1;
        })
        .finally(function () {
          vm.loading = false;
          vm._loadingList = false;
        }));
    }

    function goAdd() {
      $location.path('/classlists/add');
    }

    function goEdit(id) {
      $location.path('/classlists/' + encodeURIComponent(id) + '/edit');
    }

    function goView(id) {
      $location.path('/classlists/' + encodeURIComponent(id) + '/viewer');
    }

    function onTermChange() {
      // Refresh subjects based on selected term, and reload list
      vm.page = 1;
      loadFilterOptions().then(reload);
    }

    function dissolve(item) {
      if (!item || !item.intID) return;
      var id = item.intID;
      var msg = 'Are you sure you want to dissolve this classlist? This sets isDissolved=1.';
      if (!window.confirm(msg)) {
        return;
      }
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClasslistsService.dissolve(id)
        .then(function (data) {
          if (data && data.success) {
            vm.success = data.message || 'Classlist dissolved';
            // update in-place without reloading entire table
            item.isDissolved = 1;
          } else {
            vm.error = (data && data.message) ? data.message : 'Failed to dissolve';
          }
        })
        .catch(function (e) {
          // 422 from API should include message about students exist
          var apiMsg = (e && e.data && e.data.message) ? e.data.message : null;
          vm.error = apiMsg || 'Cannot dissolve classlist; students may exist.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Pagination controls
    function nextPage() {
      if (vm.page < vm.lastPage) {
        vm.page++;
        reload();
      }
    }

    function prevPage() {
      if (vm.page > 1) {
        vm.page--;
        reload();
      }
    }

    function onPerPageChange() {
      vm.page = 1;
      reload();
    }

    function goToPage(p) {
      var num = parseInt(p, 10);
      if (!isNaN(num) && num >= 1 && num <= vm.lastPage && num !== vm.page) {
        vm.page = num;
        reload();
      }
    }

    function resetFilters() {
      var term = vm.filters.term;
      vm.filters = {
        term: term || '',
        intSubjectID: '',
        intFacultyID: '',
        intFinalized: '',
        sectionCode: ''
      };
      vm.page = 1;
      reload();
    }

    // Kick off
    vm.init();
  }

})();
