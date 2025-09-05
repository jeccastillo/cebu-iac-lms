(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultyLoadingController', FacultyLoadingController);

  FacultyLoadingController.$inject = ['$scope', '$rootScope', '$q', 'StorageService', 'TermService', 'ToastService', 'FacultyLoadingService'];
  function FacultyLoadingController($scope, $rootScope, $q, StorageService, TermService, ToastService, FacultyLoadingService) {
    var vm = this;

    // State
    vm.state = StorageService.getJSON('loginState');
    vm.loading = {
      bootstrap: true,
      list: false,
      savingRow: {},
      savingAll: false,
      facultyOptions: {}
    };

    // Term selector
    vm.term = null;
    vm.termOptions = [];
    vm.setTerm = setTerm;

    // Server paging/meta
    vm.rows = [];
    vm.meta = { current_page: 1, per_page: 20, total: 0, last_page: 1 };

    // Filters
    vm.filters = {
      intSubjectID: '',
      intFacultyID: '',
      sectionCode: '',
      intFinalized: ''
    };

    // Local search convenience (maps to filters)
    vm.search = {
      sectionCode: '',
      subject: ''
    };

    // Faculty options cache by campus_id (null key supported)
    // Map<number|null, Array<FacultyOption>>
    vm.facultyOptionsByCampus = Object.create(null);

    // Pending edits map: { [classlistId]: facultyId }
    vm.edits = Object.create(null);

    // Expose actions
    vm.reload = reload;
    vm.applySearch = applySearch;
    vm.clearSearch = clearSearch;
    vm.pagePrev = pagePrev;
    vm.pageNext = pageNext;
    vm.pageGo = pageGo;
    vm.onFacultyChange = onFacultyChange;
    vm.saveRow = saveRow;
    vm.hasEdits = hasEdits;
    vm.saveAll = saveAll;
    vm.facultyName = facultyName;
    vm.ensureFacultyOptions = ensureFacultyOptions;
    vm.isSavingRow = isSavingRow;

    activate();

    function activate() {
      if (!vm.state || !vm.state.loggedIn) {
        vm.loading.bootstrap = false;
        return;
      }

      function syncTermFromService() {
        try {
          var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
          vm.term = (sel && (sel.intID || sel.id)) || null;
        } catch (e) {
          vm.term = null;
        }
      }

      $q.when()
        .then(function () { return TermService.init(); })
        .then(function () {
          syncTermFromService();
          // Initial load
          return reload(true);
        })
        .then(function () {
          // Subscribe to global term changes
          $rootScope.$on('termChanged', function () {
            syncTermFromService();
            reload(true);
          });
        })
        .finally(function () { vm.loading.bootstrap = false; });
    }

    function loadTerms() {
      try {
        return TermService.init().then(function () {
          var raw = Array.isArray(TermService.availableTerms) ? TermService.availableTerms : [];
          vm.termOptions = raw.map(function (t) {
            var id = (t && t.intID !== undefined) ? t.intID : (t && t.id !== undefined ? t.id : null);
            var label = (t && (t.term_label || t.label)) || '';
            if (!label) {
              var ys = (t && t.strYearStart) ? ('' + t.strYearStart) : '';
              var ye = (t && t.strYearEnd) ? ('' + t.strYearEnd) : '';
              var sem = (t && t.enumSem) ? ('' + t.enumSem) : '';
              label = [ys, ye].filter(Boolean).join('-');
              if (sem) label = (label ? (label + ' ' + sem) : sem);
              if (!label) label = 'Term ' + (id !== null ? id : '');
            }
            return { id: id, label: label };
          }).filter(function (o) { return o.id !== null; });

          var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
          if (sel && (sel.intID || sel.id)) {
            vm.term = sel.intID || sel.id;
          } else if (vm.termOptions && vm.termOptions.length) {
            vm.term = vm.termOptions[0].id;
          } else {
            vm.term = null;
          }
        });
      } catch (e) {
        vm.termOptions = [];
        vm.term = null;
        return $q.when();
      }
    }

    function setTerm(id) {
      vm.term = id;
      vm.meta.current_page = 1;
      return reload(true);
    }

    function buildParams() {
      var p = {
        term: vm.term,
        page: vm.meta.current_page,
        per_page: vm.meta.per_page || 20
      };
      Object.keys(vm.filters).forEach(function (k) {
        var v = vm.filters[k];
        if (v !== null && v !== undefined && ('' + v).trim() !== '') {
          p[k] = v;
        }
      });
      return p;
    }

    function reload(force) {
      if (!vm.term) return $q.when();
      if (vm.loading.list) return $q.when();

      vm.loading.list = true;
      var params = buildParams();

      return FacultyLoadingService.list(params)
        .then(function (body) {
          var data = (body && body.data) || [];
          var meta = (body && body.meta) || {};
          vm.rows = Array.isArray(data) ? data : [];
          vm.meta = {
            current_page: parseInt(meta.current_page, 10) || 1,
            per_page: parseInt(meta.per_page, 10) || 20,
            total: parseInt(meta.total, 10) || 0,
            last_page: parseInt(meta.last_page, 10) || 1
          };
          // Preload faculty options for campuses seen on page
          return preloadFacultyOptionsForCurrentPage();
        })
        .catch(function (err) {
          var msg = (err && err.message) || (err && err.statusText) || 'Failed to load classlists.';
          safeToastError(msg);
        })
        .finally(function () {
          vm.loading.list = false;
        });
    }

    function preloadFacultyOptionsForCurrentPage() {
      var campuses = {};
      (vm.rows || []).forEach(function (r) {
        var cid = (r && r.campus_id !== undefined) ? r.campus_id : null;
        campuses[cid === null ? 'null' : String(cid)] = (cid === null ? null : Number(cid));
      });
      var tasks = Object.keys(campuses).map(function (k) {
        var cid = campuses[k];
        return ensureFacultyOptions(cid);
      });
      return $q.all(tasks);
    }

    function ensureFacultyOptions(campusId) {
      var key = (campusId === null || typeof campusId === 'undefined') ? 'null' : String(campusId);
      if (Object.prototype.hasOwnProperty.call(vm.facultyOptionsByCampus, key)) {
        return $q.when(vm.facultyOptionsByCampus[key]);
      }
      vm.loading.facultyOptions[key] = true;
      var params = { teaching: 1 };
      if (campusId !== null && typeof campusId !== 'undefined') {
        params.campus_id = campusId;
      }
      return FacultyLoadingService.facultyOptions(params)
        .then(function (res) {
          var options = (res && res.data) || [];
          vm.facultyOptionsByCampus[key] = options;
          return options;
        })
        .catch(function (err) {
          vm.facultyOptionsByCampus[key] = [];
          return [];
        })
        .finally(function () {
          vm.loading.facultyOptions[key] = false;
        });
    }

    function applySearch() {
      vm.filters.sectionCode = (vm.search && vm.search.sectionCode) || '';
      // Subject search by code/description is not directly supported server-side; keep to sectionCode for now.
      vm.meta.current_page = 1;
      return reload(true);
    }

    function clearSearch() {
      vm.search = { sectionCode: '', subject: '' };
      vm.filters.sectionCode = '';
      vm.meta.current_page = 1;
      return reload(true);
    }

    function pagePrev() {
      if (vm.meta.current_page > 1) {
        vm.meta.current_page -= 1;
        return reload(true);
      }
      return $q.when();
    }

    function pageNext() {
      if (vm.meta.current_page < vm.meta.last_page) {
        vm.meta.current_page += 1;
        return reload(true);
      }
      return $q.when();
    }

    function pageGo(n) {
      var p = parseInt(n, 10);
      if (isFinite(p) && p >= 1 && p <= vm.meta.last_page) {
        vm.meta.current_page = p;
        return reload(true);
      }
      return $q.when();
    }

    function facultyName(r) {
      if (!r) return '';
      var f = [];
      if (r.facultyFirstname) f.push(r.facultyFirstname);
      if (r.facultyLastname) f.push(r.facultyLastname);
      return f.join(' ');
    }

    function onFacultyChange(row) {
      if (!row || !row.intID) return;
      var fid = row._selectedFacultyId;
      if (fid === undefined || fid === null || ('' + fid).trim() === '') {
        delete vm.edits[row.intID];
      } else {
        vm.edits[row.intID] = parseInt(fid, 10);
      }
    }

    function isSavingRow(classlistId) {
      return !!vm.loading.savingRow[classlistId];
    }

    function saveRow(row) {
      if (!row || !row.intID) return $q.when();
      var cid = row.intID;
      var fid = vm.edits[cid];
      if (fid === undefined) {
        safeToastInfo('No changes to save for this row.');
        return $q.when();
      }
      vm.loading.savingRow[cid] = true;
      return FacultyLoadingService.updateSingle(cid, fid)
        .then(function () {
          safeToastSuccess('Saved.');
          // Reflect the change locally
          row.intFacultyID = fid;
          // Update faculty display by refetching page or best-effort local change
          // For simplicity, refresh page to update facultyFirstname/Lastname
          return reload(true);
        })
        .catch(function (err) {
          var msg = (err && err.message) || 'Failed to save.';
          safeToastError(msg);
        })
        .finally(function () {
          vm.loading.savingRow[cid] = false;
          // Clear pending edit for this row on success or leave it on error
        });
    }

    function hasEdits() {
      return Object.keys(vm.edits).length > 0;
    }

    function saveAll() {
      if (!hasEdits()) {
        safeToastInfo('No pending changes.');
        return $q.when();
      }
      if (!vm.term) {
        safeToastError('Term is required.');
        return $q.when();
      }
      var assignments = [];
      Object.keys(vm.edits).forEach(function (cidStr) {
        var cid = parseInt(cidStr, 10);
        var fid = parseInt(vm.edits[cidStr], 10);
        if (isFinite(cid) && isFinite(fid)) {
          assignments.push({ classlist_id: cid, faculty_id: fid });
        }
      });
      if (!assignments.length) {
        safeToastInfo('No valid changes to submit.');
        return $q.when();
      }

      vm.loading.savingAll = true;
      return FacultyLoadingService.assignBulk(vm.term, assignments)
        .then(function (res) {
          var applied = res && typeof res.applied_count !== 'undefined' ? res.applied_count : 0;
          var total = res && typeof res.total !== 'undefined' ? res.total : assignments.length;
          if (applied === total) {
            safeToastSuccess('All changes saved (' + applied + '/' + total + ').');
            vm.edits = Object.create(null);
          } else {
            safeToastError('Saved with some errors (' + applied + '/' + total + '). Check per-row results.');
          }
          // Refresh list to update faculty display
          return reload(true);
        })
        .catch(function (err) {
          var msg = (err && err.message) || 'Bulk save failed.';
          safeToastError(msg);
        })
        .finally(function () {
          vm.loading.savingAll = false;
        });
    }

    // Toast helpers
    function safeToastSuccess(msg) { try { ToastService.success(msg); } catch (e) {} }
    function safeToastError(msg) { try { ToastService.error(msg); } catch (e) {} }
    function safeToastInfo(msg) { try { ToastService.info ? ToastService.info(msg) : ToastService.success(msg); } catch (e) {} }
  }
})();
