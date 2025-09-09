(function () {
  'use strict';

  angular
    .module('unityApp')
  .controller('ClasslistsController', ClasslistsController);

  ClasslistsController.$inject = ['$scope', '$location', '$window', 'ClasslistsService', 'TermService'];
  function ClasslistsController($scope, $location, $window, ClasslistsService, TermService) {
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

    // Selection / Merge state
    vm.selected = {};
    vm._selectAll = false;
    vm.mergeDialogOpen = false;
    vm.mergeTargetId = null;
    vm.mergeCandidates = [];
    vm.merging = false;

    // Expose
    vm.init = init;
    vm.reload = reload;
    vm.downloadTemplate = downloadTemplate;
    vm.openImportDialog = openImportDialog;
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

    // Merge handlers
    vm.openMergeDialog = openMergeDialog;
    vm.closeMergeDialog = closeMergeDialog;
    vm.selectedCount = selectedCount;
    vm.toggleAll = toggleAll;
    vm.onToggleRow = onToggleRow;
    vm.mergeSelected = mergeSelected;

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

    // Import UI State
    vm.importing = false;
    vm.importError = null;
    vm.importSummary = null;
    vm._selectedFile = null;

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

    // -----------------------------
    // Import: Template download + File upload (parity with Subjects)
    // -----------------------------
    function downloadTemplate() {
      vm.importError = null;
      try {
        ClasslistsService.downloadImportTemplate()
          .then(function (res) {
            var data = res && res.data ? res.data : null;
            var filename = (res && res.filename) ? res.filename : 'classlists-import-template.xlsx';
            if (!data) {
              vm.importError = 'Failed to download template.';
              return;
            }
            var blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var url = ($window.URL || $window.webkitURL).createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
              ($window.URL || $window.webkitURL).revokeObjectURL(url);
              try { document.body.removeChild(a); } catch (e) {}
            }, 0);
          })
          .catch(function () {
            vm.importError = 'Failed to download template.';
          });
      } catch (e) {
        vm.importError = 'Failed to download template.';
      }
    }

    function openImportDialog() {
      vm.importError = null;
      vm.importSummary = null;
      var el = document.getElementById('classlistsImportFile');
      if (el) {
        try {
          el.value = '';
          el.onchange = function (evt) {
            var files = (evt && evt.target) ? evt.target.files : null;
            try {
              onFileSelected(files);
            } catch (e) {
              onFileSelected(files);
            }
          };
        } catch (e) {}
        el.click();
      }
    }

    function onFileSelected(files) {
      vm.importError = null;
      vm.importSummary = null;
      try {
        if (files && files.length > 0) {
          vm._selectedFile = files[0];
        } else {
          vm._selectedFile = null;
        }
      } catch (e) {
        vm._selectedFile = null;
      }
      if (vm._selectedFile) {
        runImport();
      }
    }

    function runImport() {
      if (!vm._selectedFile) {
        vm.importError = 'No file selected';
        return;
      }
      vm.importing = true;
      vm.importError = null;
      vm.importSummary = null;

      ClasslistsService.importFile(vm._selectedFile, { dry_run: false })
        .then(function (res) {
          var ok = res && (res.success !== false);
          var result = res && res.result ? res.result : null;
          if (!ok || !result) {
            vm.importError = (res && res.message) ? res.message : 'Import failed.';
            return;
          }
          vm.importSummary = {
            totalRows: result.totalRows || 0,
            inserted: result.inserted || 0,
            updated: result.updated || 0,
            skipped: result.skipped || 0,
            errors: Array.isArray(result.errors) ? result.errors : []
          };
          // Refresh list after successful import
          reload();
        })
        .catch(function (e) {
          vm.importError = (e && e.data && e.data.message) ? e.data.message : 'Import failed.';
        })
        .finally(function () {
          vm.importing = false;
          // clear file input
          try {
            var el = document.getElementById('classlistsImportFile');
            if (el) el.value = '';
          } catch (e) {}
          vm._selectedFile = null;
        });
    }

    // -----------------------------
    // Merge helpers/handlers
    // -----------------------------
    function selectedCount() {
      try {
        return Object.keys(vm.selected || {}).filter(function (k) { return !!vm.selected[k]; }).length;
      } catch (e) {
        return 0;
      }
    }

    function eligibleRow(row) {
      if (!row) return false;
      if (row.isDissolved == 1) return false;
      if (parseInt(row.intFinalized, 10) !== 0) return false;
      return true;
    }

    function toggleAll() {
      var sel = !!vm._selectAll;
      (vm.records || []).forEach(function (r) {
        if (eligibleRow(r)) {
          vm.selected[r.intID] = sel;
        }
      });
    }

    function onToggleRow(row) {
      // Update header checkbox based on current page selection state
      var allEligible = true;
      var anyEligible = false;
      (vm.records || []).forEach(function (r) {
        if (eligibleRow(r)) {
          anyEligible = true;
          if (!vm.selected[r.intID]) {
            allEligible = false;
          }
        }
      });
      vm._selectAll = anyEligible && allEligible;
    }

    function openMergeDialog() {
      var ids = [];
      var candidates = [];
      (vm.records || []).forEach(function (r) {
        if (vm.selected[r.intID] && eligibleRow(r)) {
          ids.push(r.intID);
          candidates.push(r);
        }
      });
      if (ids.length < 2) {
        vm.error = 'Select at least two eligible classlists (non-finalized, not dissolved) to merge.';
        return;
      }
      vm.mergeCandidates = candidates;
      vm.mergeTargetId = (candidates[0] && candidates[0].intID) || null;
      vm.mergeDialogOpen = true;
      vm.error = null;
      vm.success = null;
    }

    function closeMergeDialog() {
      vm.mergeDialogOpen = false;
      vm.mergeTargetId = null;
      vm.merging = false;
    }

    function mergeSelected() {
      if (!vm.mergeDialogOpen) return;
      var target = parseInt(vm.mergeTargetId, 10);
      if (!target) {
        vm.error = 'Please select a target classlist.';
        return;
      }
      var selectedIds = (vm.mergeCandidates || []).map(function (r) { return r.intID; });
      var sourceIds = selectedIds.filter(function (id) { return id !== target; });
      if (sourceIds.length < 1) {
        vm.error = 'Select at least one source classlist.';
        return;
      }

      vm.merging = true;
      ClasslistsService.merge({ target_id: target, source_ids: sourceIds })
        .then(function (res) {
          var result = (res && res.result) ? res.result : res;
          var moved = result && result.moved || 0;
          var skipped = result && result.skipped || 0;
          var dissolved = result && result.dissolved_sources || 0;
          vm.success = 'Merge completed. Moved: ' + moved + ', Skipped: ' + skipped + ', Sources dissolved: ' + dissolved + '.';
          vm.error = null;

          // Clear selection for processed ids
          selectedIds.forEach(function (id) { delete vm.selected[id]; });
          vm._selectAll = false;

          closeMergeDialog();
          // Refresh list to reflect dissolved sources
          reload();
        })
        .catch(function (e) {
          var msg = (e && e.data && e.data.message) ? e.data.message : 'Merge failed.';
          vm.error = msg;
        })
        .finally(function () {
          vm.merging = false;
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
