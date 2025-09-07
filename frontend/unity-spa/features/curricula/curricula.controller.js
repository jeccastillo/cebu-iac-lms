(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CurriculaListController', CurriculaListController)
    .controller('CurriculumEditController', CurriculumEditController);

  CurriculaListController.$inject = ['$location', '$scope', '$window', 'CurriculaService', 'CampusService'];
  function CurriculaListController($location, $scope, $window, CurriculaService, CampusService) {
    var vm = this;
    // Expose CampusService for template bindings
    vm.CampusService = CampusService;

    // State
    vm.loading = false;
    vm.error = null;
    vm.rows = [];
    vm.searchTerm = '';
    vm.page = 1;
    vm.limit = 25;

    // Methods
    vm.load = load;
    vm.onSearch = onSearch;
    vm.addNew = addNew;
    vm.edit = edit;
    vm.delete = remove;
    // Import/Template
    vm.downloadTemplate = downloadTemplate;
    vm.openImportDialog = openImportDialog;
    vm.onFileSelected = onFileSelected;
    vm.runImport = runImport;

    // Import state
    vm.importing = false;
    vm.importError = null;
    vm.importSummary = null;
    vm._selectedFile = null;

    activate();

    function activate() {
      // Ensure campus context is ready, then initial load
      var p = (CampusService && CampusService.init) ? CampusService.init() : null;
      if (p && p.then) {
        p.then(function () {
          load();
        });
      } else {
        load();
      }

      // React to campus changes: reset to first page and reload
      $scope.$on('campusChanged', function () {
        vm.page = 1;
        load();
      });
    }

    function buildParams() {
      var params = {
        limit: vm.limit,
        page: vm.page
      };
      if (vm.searchTerm && ('' + vm.searchTerm).trim() !== '') {
        params.search = vm.searchTerm;
      }
      try {
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
          params.campus_id = parseInt(campus.id, 10);
        }
      } catch (e) {
        // ignore
      }
      return params;
    }

    function load() {
      vm.loading = true;
      vm.error = null;
      CurriculaService.list(buildParams())
        .then(function (data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.rows = data.data;
            vm.meta = data.meta || null;
          } else if (angular.isArray(data)) {
            vm.rows = data;
            vm.meta = null;
          } else {
            vm.rows = [];
            vm.meta = null;
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load curricula.';
          vm.rows = [];
          console.error('Curricula list error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function onSearch() {
      vm.page = 1;
      load();
    }

    function addNew() {
      $location.path('/curricula/add');
    }

    function edit(row) {
      if (!row || !row.intID) return;
      $location.path('/curricula/' + row.intID + '/edit');
    }

    function remove(row) {
      if (!row || !row.intID) return;
      var ok = window.confirm('Delete curriculum "' + (row.strName || row.intID) + '"? This cannot be undone.');
      if (!ok) return;

      vm.loading = true;
      CurriculaService.remove(row.intID)
        .then(function (res) {
          if (res && res.success !== false) {
            load();
          } else {
            vm.error = (res && res.message) ? res.message : 'Delete failed.';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Delete failed (see console).';
          console.error('Delete curriculum error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // Import/Template handlers
    function downloadTemplate() {
      vm.importError = null;
      try {
        CurriculaService.downloadImportTemplate().then(function (res) {
          var data = res && res.data ? res.data : null;
          var filename = (res && res.filename) ? res.filename : 'curriculum-import-template.xlsx';
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
        }).catch(function () {
          vm.importError = 'Failed to download template.';
        });
      } catch (e) {
        vm.importError = 'Failed to download template.';
      }
    }

    function openImportDialog() {
      vm.importError = null;
      vm.importSummary = null;
      var el = document.getElementById('curriculumImportFile');
      if (el) {
        try {
          el.value = '';
          el.onchange = function (evt) {
            var files = (evt && evt.target) ? evt.target.files : null;
            try {
              $scope.$applyAsync(function () { vm.onFileSelected(files); });
            } catch (e) {
              vm.onFileSelected(files);
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
        vm.runImport();
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
      CurriculaService.importFile(vm._selectedFile, { dry_run: false })
        .then(function (res) {
          var ok = res && (res.success !== false);
          var result = res && res.result ? res.result : null;
          if (!ok || !result) {
            vm.importError = (res && res.message) ? res.message : 'Import failed.';
            return;
          }
          vm.importSummary = {
            totalRows: result.totalRows || 0,
            insertedCurricula: result.insertedCurricula || 0,
            updatedCurricula: result.updatedCurricula || 0,
            skippedCurricula: result.skippedCurricula || 0,
            insertedSubjectLinks: result.insertedSubjectLinks || 0,
            updatedSubjectLinks: result.updatedSubjectLinks || 0,
            skippedSubjectLinks: result.skippedSubjectLinks || 0,
            errors: Array.isArray(result.errors) ? result.errors : []
          };
          vm.load();
        })
        .catch(function (e) {
          vm.importError = (e && e.data && e.data.message) ? e.data.message : 'Import failed.';
        })
        .finally(function () {
          vm.importing = false;
          try {
            var el = document.getElementById('curriculumImportFile');
            if (el) el.value = '';
          } catch (e) {}
          vm._selectedFile = null;
        });
    }
  }

  CurriculumEditController.$inject = ['$routeParams', '$location', '$scope', '$q', 'CurriculaService', 'CampusService', 'SubjectsService', 'TermService'];
  function CurriculumEditController($routeParams, $location, $scope, $q, CurriculaService, CampusService, SubjectsService, TermService) {
    var vm = this;
    // Expose CampusService if template needs to reference it
    vm.CampusService = CampusService;

    vm.isEdit = !!($routeParams && $routeParams.id);
    vm.loading = false;
    vm.saving = false;
    vm.error = null;
    vm.success = null;

    // Form model
    vm.model = {
      strName: '',
      intProgramID: null,
      campus_id: null,
      active: 1,
      isEnhanced: 0
    };

    // Dropdown data
    vm.programs = [];
    vm.campuses = [];

    // Bulk-add UI state
    vm.subjectsSearch = '';
    vm.subjectsDepartment = '';
    vm.subjectsPage = 1;
    vm.subjectsLimit = 25;
    vm.subjectsRows = [];
    vm.subjectsLoading = false;
    vm.linkedSubjectIds = {}; // { [id]: true }
    vm.selectionMap = {};     // { [id]: { intYearLevel, intSem } }
    vm.defaultYearLevel = 1;
    vm.defaultSem = 1;
    vm.updateIfExists = false;

    // Tabs + already added list
    vm.activeTab = 'added'; // 'added' | 'add'
    vm.curriculumSubjects = [];
    vm.curriculumGroups = [];

    // Base Academic Year start (e.g., 2024 from "2024-2025") used to derive labels per Year Level
    vm.termBaseStartYear = null;

    // Methods
    vm.load = load;
    vm.loadDropdowns = loadDropdowns;
    vm.save = save;
    vm.cancel = cancel;
    vm.onCampusChange = onCampusChange;

    // Bulk-add methods
    vm.loadCurriculumSubjects = loadCurriculumSubjects;
    vm.loadSubjects = loadSubjects;
    vm.toggleSelectSubject = toggleSelectSubject;
    vm.applyDefaultsToSelection = applyDefaultsToSelection;
    vm.submitSubjectsBulk = submitSubjectsBulk;
    vm.selectedCount = selectedCount;
    vm.removeSubjectLink = removeSubjectLink;

    activate();

    function activate() {
      // Ensure campus service is ready to default campus on Add
      var initPromise = (CampusService && CampusService.init) ? CampusService.init() : $q.resolve();
      initPromise
        .then(function () {
          return loadDropdowns();
        })
        .then(function () {
          if (vm.isEdit) {
            return load();
          } else {
            // Default campus from global selection when adding
            try {
              var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
              if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
                vm.model.campus_id = parseInt(campus.id, 10);
              }
            } catch (e) { /* no-op */ }
          }
        })
        .then(function () {
          // After dropdowns and record load (if editing), load subjects context
          if (vm.isEdit) {
            return loadCurriculumSubjects();
          }
        })
        .then(function () {
          loadSubjects();
        });

      // Keep campus in sync while adding
      $scope.$on('campusChanged', function (event, data) {
        if (vm.isEdit) return;
        var campus = data && data.selectedCampus ? data.selectedCampus : null;
        var id = (campus && campus.id !== undefined && campus.id !== null) ? parseInt(campus.id, 10) : null;
        vm.model.campus_id = id;
      });
    }

    function loadDropdowns() {
      vm.loading = true;
      vm.error = null;

      var proms = [
        CurriculaService.getPrograms(),
        (function () {
          // Prefer CampusService cache for consistency
          var list = CampusService && CampusService.availableCampuses ? CampusService.availableCampuses : [];
          if (list && list.length) {
            return $q.resolve({ data: list, success: true });
          }
          return CurriculaService.getCampuses();
        })()
      ];

      return $q.all(proms)
        .then(function (results) {
          // Programs
          var p = results[0];
          var pdata = (p && p.data) ? p.data : p;
          if (pdata && pdata.success !== false && angular.isArray(pdata.data)) {
            vm.programs = pdata.data;
          } else if (angular.isArray(pdata)) {
            vm.programs = pdata;
          } else {
            vm.programs = [];
          }

          // Campuses
          var c = results[1];
          var cdata = (c && c.data) ? c.data : c;
          if (cdata && cdata.success !== false && angular.isArray(cdata.data)) {
            vm.campuses = cdata.data;
          } else if (angular.isArray(cdata)) {
            vm.campuses = cdata;
          } else {
            vm.campuses = [];
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load dropdown data.';
          console.error('Dropdown load error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function load() {
      if (!vm.isEdit) return $q.resolve();
      vm.loading = true;
      vm.error = null;

      return CurriculaService.get($routeParams.id)
        .then(function (data) {
          if (data && data.success !== false && data.data) {
            var row = data.data;
            vm.model.strName = row.strName || '';
            vm.model.intProgramID = (row.intProgramID !== undefined && row.intProgramID !== null) ? parseInt(row.intProgramID, 10) : null;
            vm.model.campus_id = (row.campus_id !== undefined && row.campus_id !== null) ? parseInt(row.campus_id, 10) : null;
            vm.model.active = (row.active !== undefined && row.active !== null) ? parseInt(row.active, 10) : 1;
            vm.model.isEnhanced = (row.isEnhanced !== undefined && row.isEnhanced !== null) ? parseInt(row.isEnhanced, 10) : 0;
          } else {
            vm.error = 'Failed to load curriculum.';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to load curriculum.';
          console.error('Load curriculum error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function onCampusChange() {
      // Hook kept for parity with other features if needed later
    }

    function save() {
      vm.saving = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        strName: (vm.model.strName || '').trim(),
        intProgramID: vm.model.intProgramID !== null ? parseInt(vm.model.intProgramID, 10) : null,
        campus_id: vm.model.campus_id !== null ? parseInt(vm.model.campus_id, 10) : null,
        active: vm.model.active ? 1 : 0,
        isEnhanced: vm.model.isEnhanced ? 1 : 0
      };

      var p;
      if (vm.isEdit) {
        p = CurriculaService.update($routeParams.id, payload);
      } else {
        p = CurriculaService.create(payload);
      }

      p.then(function (res) {
        if (res && res.success !== false) {
          $location.path('/curricula');
        } else {
          vm.error = (res && res.message) ? res.message : 'Save failed.';
        }
      })
      .catch(function (err) {
        vm.error = (err && err.data && err.data.message) ? err.data.message : 'Save failed (see console).';
        console.error('Save curriculum error:', err);
      })
      .finally(function () {
        vm.saving = false;
      });
    }

    function cancel() {
      $location.path('/curricula');
    }

    // -------- Bulk Add: helpers --------

    function selectedCount() {
      return Object.keys(vm.selectionMap || {}).length;
    }

    // Helpers to compute Academic Year labels from a base year
    function _extractStartYearFromLabel(label) {
      try {
        var m = /(\d{4})\s*-\s*(\d{4})/.exec(label || '');
        if (m && m[1]) return parseInt(m[1], 10);
      } catch (e) {}
      return null;
    }
    function _ayForYearLevel(yearLevel) {
      var base = (vm.termBaseStartYear != null ? vm.termBaseStartYear : (new Date()).getFullYear());
      var offset = (parseInt(yearLevel || 1, 10) - 1);
      var start = base + (offset >= 0 ? offset : 0);
      return start + '-' + (start + 1);
    }
    function _ordinalYearLabel(n) {
      n = parseInt(n || 0, 10);
      if (!n || n < 1) return 'Year';
      var suffix = 'th';
      if (n % 100 < 11 || n % 100 > 13) {
        switch (n % 10) {
          case 1: suffix = 'st'; break;
          case 2: suffix = 'nd'; break;
          case 3: suffix = 'rd'; break;
        }
      }
      return n + suffix + ' Year';
    }

    function loadCurriculumSubjects() {
      if (!vm.isEdit) return $q.resolve();
      vm.linkedSubjectIds = {};
      return CurriculaService.subjects($routeParams.id).then(function (data) {
        try {
          var rows = (data && data.data) ? data.data : (Array.isArray(data) ? data : []);
          // Cache full list for "Already Added" tab
          vm.curriculumSubjects = rows;
          (function () {
            try {
              // Initialize base start year from selected term label if not set
              if (vm.termBaseStartYear == null) {
                try {
                  var sel = (TermService && TermService.getSelectedTerm) ? TermService.getSelectedTerm() : null;
                  var lbl = sel && sel.label;
                  var st = _extractStartYearFromLabel(lbl);
                  vm.termBaseStartYear = (st !== null) ? st : (new Date()).getFullYear();
                } catch (e0) {
                  vm.termBaseStartYear = (new Date()).getFullYear();
                }
              }

              var map = {};
              rows.forEach(function (r) {
                var y = parseInt(r.intYearLevel || 0, 10);
                var s = parseInt(r.intSem || 0, 10);
                var key = y + '|' + s;
                if (!map[key]) {
                  var semLbl = (s === 1 ? '1st Term' : (s === 2 ? '2nd Term' : (s === 3 ? '3rd Term' : ('Term ' + s))));
                  var yearLbl = _ordinalYearLabel(y);
                  map[key] = { key: key, year: y, sem: s, title: yearLbl + ' - ' + semLbl, items: [] };
                }
                map[key].items.push(r);
              });
              var arr = Object.keys(map).map(function (k) { return map[k]; });
              arr.sort(function (a, b) {
                if (a.year !== b.year) return a.year - b.year;
                return a.sem - b.sem;
              });
              vm.curriculumGroups = arr;
            } catch (e) {
              vm.curriculumGroups = [];
            }
          })();
          // Rebuild quick lookup map
          rows.forEach(function (r) {
            if (r && r.intID != null) vm.linkedSubjectIds[parseInt(r.intID, 10)] = true;
          });
        } catch (e) {
          // no-op
        }
      });
    }

    function loadSubjects() {
      vm.subjectsLoading = true;
      var opts = {
        search: (vm.subjectsSearch || '').trim(),
        department: (vm.subjectsDepartment || '').trim(),
        page: vm.subjectsPage,
        limit: vm.subjectsLimit
      };
      SubjectsService.list(opts).then(function (res) {
        var items = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        // Mark already linked; keep them visible so users can edit Year/Sem with "Update if exists"
        items.forEach(function (row) {
          row.alreadyLinked = !!vm.linkedSubjectIds[parseInt(row.intID, 10)];
        });
        vm.subjectsRows = items;
        vm.subjectsMeta = res && res.meta ? res.meta : null;
      }).catch(function () {
        vm.subjectsRows = [];
        vm.subjectsMeta = null;
      }).finally(function () {
        vm.subjectsLoading = false;
      });
    }

    function toggleSelectSubject(row) {
      if (!row || row.intID == null) return;
      var id = parseInt(row.intID, 10);
      if (vm.selectionMap[id]) {
        delete vm.selectionMap[id];
        return;
      }
      // Enforce selection cap (60)
      if (selectedCount() >= 60) {
        return; // silently ignore; UI can show message if desired
      }
      vm.selectionMap[id] = {
        intYearLevel: vm.defaultYearLevel || 1,
        intSem: vm.defaultSem || 1
      };
    }

    function applyDefaultsToSelection() {
      var yl = vm.defaultYearLevel || 1;
      var sem = vm.defaultSem || 1;
      Object.keys(vm.selectionMap).forEach(function (k) {
        if (!vm.selectionMap[k]) return;
        if (vm.selectionMap[k].intYearLevel == null) vm.selectionMap[k].intYearLevel = yl;
        if (vm.selectionMap[k].intSem == null) vm.selectionMap[k].intSem = sem;
      });
    }

    function submitSubjectsBulk() {
      if (!vm.isEdit) return;
      var ids = Object.keys(vm.selectionMap);
      if (!ids.length) return;

      var payload = {
        update_if_exists: !!vm.updateIfExists,
        subjects: ids.slice(0, 60).map(function (sid) {
          var s = vm.selectionMap[sid] || {};
          return {
            intSubjectID: parseInt(sid, 10),
            intYearLevel: parseInt(s.intYearLevel || vm.defaultYearLevel || 1, 10),
            intSem: parseInt(s.intSem || vm.defaultSem || 1, 10)
          };
        })
      };

      vm.saving = true;
      CurriculaService.addSubjectsBulk($routeParams.id, payload)
        .then(function (res) {
          // Refresh linked subjects + list; clear selection
          vm.selectionMap = {};
          vm.updateIfExists = false;
          vm.defaultYearLevel = 1;
          vm.defaultSem = 1;
          vm.loadCurriculumSubjects();
          vm.loadSubjects();
          // Optionally display summary in vm.success/vm.error
          if (res && res.result) {
            vm.success = 'Inserted: ' + (res.result.inserted || 0) + ', Updated: ' + (res.result.updated || 0) + ', Skipped: ' + (res.result.skipped || 0);
          } else {
            vm.success = 'Subjects processed.';
          }
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Bulk add failed.';
        })
        .finally(function () {
          vm.saving = false;
        });
    }

    function removeSubjectLink(row) {
      if (!vm.isEdit || !row || row.intID == null) return;
      var sid = parseInt(row.intID, 10);
      if (!(sid > 0)) return;
      var ok = window.confirm('Remove subject "' + (row.strCode || sid) + '" from this curriculum?');
      if (!ok) return;

      vm.saving = true;
      CurriculaService.removeSubject($routeParams.id, sid)
        .then(function (res) {
          // Refresh both lists and badges
          vm.loadCurriculumSubjects();
          vm.loadSubjects();
          vm.success = 'Subject removed.';
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Remove failed.';
        })
        .finally(function () {
          vm.saving = false;
        });
    }
  }

})();
