(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultyLoadingByFacultyController', FacultyLoadingByFacultyController);

  FacultyLoadingByFacultyController.$inject = ['$scope', '$q', 'StorageService', 'TermService', 'ToastService', 'FacultyLoadingService'];
  function FacultyLoadingByFacultyController($scope, $q, StorageService, TermService, ToastService, FacultyLoadingService) {
    var vm = this;

    // Auth state
    vm.state = StorageService.getJSON('loginState');

    // Loading flags
    vm.loading = {
      bootstrap: true,
      lists: false,
      facultyOptions: false,
      saving: false
    };

    // Term and faculty selection
    vm.term = null;
    vm.termLabel = '';
    vm.selectedFacultyId = null;

    // Faculty options list (teaching=1; optionally filter by campus)
    vm.facultyOptions = [];

    // Lists and meta
    vm.assigned = [];
    vm.unassigned = [];
    vm.assignedMeta = { current_page: 1, per_page: 20, total: 0, last_page: 1 };
    vm.unassignedMeta = { current_page: 1, per_page: 20, total: 0, last_page: 1 };

    // Search
    vm.search = {
      sectionCode: '',
      subjectCode: ''
    };
    // Section code autocomplete source and handlers
    vm.sectionOptions = [];
    vm.onSectionQuery = onSectionQuery;
    vm.onSectionSelect = onSectionSelect;

    // Queues
    // assignQueue: Set<classlistId> to assign to selectedFacultyId
    // unassignQueue: Set<classlistId> to clear assignment (intFacultyID=null)
    vm.assignQueue = Object.create(null);
    vm.unassignQueue = Object.create(null);

    // Actions
    vm.activate = activate;
    vm.setTerm = setTerm;
    vm.onFacultyChange = onFacultyChange;
    vm.reload = reload;
    vm.applySearch = applySearch;
    vm.clearSearch = clearSearch;

    vm.pageAssignedPrev = pageAssignedPrev;
    vm.pageAssignedNext = pageAssignedNext;
    vm.pageAssignedGo = pageAssignedGo;
    vm.pageUnassignedPrev = pageUnassignedPrev;
    vm.pageUnassignedNext = pageUnassignedNext;
    vm.pageUnassignedGo = pageUnassignedGo;

    vm.moveToAssign = moveToAssign;
    vm.moveToUnassign = moveToUnassign;
    vm.queueAssign = queueAssign;
    vm.queueUnassign = queueUnassign;
    vm.isQueuedAssign = isQueuedAssign;
    vm.isQueuedUnassign = isQueuedUnassign;
    vm.hasPending = hasPending;
    vm.saveAll = saveAll;
    vm.includeUnassignedForExport = false;
    vm.exportAssignments = exportAssignments;

    vm.facultyNameOfRow = facultyNameOfRow;

    activate();

    function activate() {
      if (!vm.state || !vm.state.loggedIn) {
        vm.loading.bootstrap = false;
        return;
      }
      return $q.when()
        .then(function () { return TermService.init(); })
        .then(function () {
          // sync active term
          try {
            var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
            vm.term = (sel && (sel.intID || sel.id)) || null;
            vm.termLabel = getTermLabel(sel, vm.term);
          } catch (e) {
            vm.term = null;
            vm.termLabel = '';
          }
          return loadFacultyOptions();
        })
        .then(function () {
          // Initial lists load
          return reload(true);
        })
        .finally(function () {
          vm.loading.bootstrap = false;
          // subscribe to global term changes
          $scope.$on('termChanged', function () {
            try {
              var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
              vm.term = (sel && (sel.intID || sel.id)) || null;
              vm.termLabel = getTermLabel(sel, vm.term);
            } catch (e) {}
            vm.assignedMeta.current_page = 1;
            vm.unassignedMeta.current_page = 1;
            reload(true);
          });
        });
    }

    function loadFacultyOptions() {
      vm.loading.facultyOptions = true;
      // teaching=1 default; optionally filter by campus later per UI needs
      return FacultyLoadingService.facultyOptions({ teaching: 1 })
        .then(function (res) {
          vm.facultyOptions = (res && res.data) || [];
        })
        .catch(function () {
          vm.facultyOptions = [];
        })
        .finally(function () {
          vm.loading.facultyOptions = false;
        });
    }

    function setTerm(id) {
      vm.term = id;
      vm.termLabel = getTermLabel(null, id);
      vm.assignedMeta.current_page = 1;
      vm.unassignedMeta.current_page = 1;
      return reload(true);
    }

    function onFacultyChange() {
      // Reset queues when switching faculty
      vm.assignQueue = Object.create(null);
      vm.unassignQueue = Object.create(null);
      vm.assignedMeta.current_page = 1;
      return reload(true);
    }

    function buildBaseParams() {
      var p = {
        term: vm.term
      };
      if (vm.search && vm.search.sectionCode) {
        p.sectionCode = vm.search.sectionCode;
      }
      if (vm.search && vm.search.subjectCode) {
        p.subjectCode = vm.search.subjectCode;
      }
      return p;
    }

    // Resolve a human-friendly term label like "1st Sem 2025-2026"
    function getTermLabel(sel, id) {
      try {
        if (sel && sel.label) return sel.label;
        var list = Array.isArray(TermService.availableTerms) ? TermService.availableTerms : [];
        var termId = id != null ? id : (sel && (sel.intID || sel.id));
        for (var i = 0; i < list.length; i++) {
          var t = list[i];
          var tid = (t && t.intID != null) ? t.intID : (t && t.id != null ? t.id : null);
          if (tid != null && termId != null && parseInt(tid, 10) === parseInt(termId, 10)) {
            return t.label || '';
          }
        }
      } catch (e) {}
      return id ? ('#' + id) : '';
    }

    // Autocomplete: query section codes from server and build unique list
    function onSectionQuery(q) {
      if (!vm.term) { vm.sectionOptions = []; return; }
      var params = {
        term: vm.term,
        sectionCode: q || '',
        page: 1,
        per_page: 20
      };
      return FacultyLoadingService.list(params).then(function (body) {
        var data = (body && body.data) || [];
        var seen = Object.create(null);
        var out = [];
        for (var i = 0; i < data.length; i++) {
          var code = data[i] && data[i].sectionCode;
          if (code && !seen[code]) {
            seen[code] = true;
            out.push({ code: code });
          }
        }
        vm.sectionOptions = out;
      }).catch(function () {
        vm.sectionOptions = [];
      });
    }

    function onSectionSelect() {
      // Apply search when user picks a section suggestion
      return vm.applySearch();
    }

    function reload(force) {
      if (!vm.term) return $q.when();
      vm.loading.lists = true;

      var base = buildBaseParams();

      var pAssigned = $q.when().then(function () {
        if (!vm.selectedFacultyId) {
          // No faculty selected -> assigned list is empty
          vm.assigned = [];
          vm.assignedMeta = { current_page: 1, per_page: 20, total: 0, last_page: 1 };
          return;
        }
        var params = Object.assign({}, base, {
          intFacultyID: vm.selectedFacultyId,
          page: vm.assignedMeta.current_page,
          per_page: vm.assignedMeta.per_page || 20
        });
        return FacultyLoadingService.listByFaculty(params).then(function (body) {
          var data = (body && body.data) || [];
          var meta = (body && body.meta) || {};
          vm.assigned = Array.isArray(data) ? data : [];          
          vm.assignedMeta = {
            current_page: parseInt(meta.current_page, 10) || 1,
            per_page: parseInt(meta.per_page, 10) || 20,
            total: parseInt(meta.total, 10) || 0,
            last_page: parseInt(meta.last_page, 10) || 1
          };
        });
      });

      var pUnassigned = $q.when().then(function () {
        var params = Object.assign({}, base, {
          page: vm.unassignedMeta.current_page,
          per_page: vm.unassignedMeta.per_page || 20
        });
        
        return FacultyLoadingService.listUnassigned(params).then(function (body) {
          var data = (body && body.data) || [];
          var meta = (body && body.meta) || {};          
          vm.unassigned = Array.isArray(data) ? data : [];          
          vm.unassignedMeta = {
            current_page: parseInt(meta.current_page, 10) || 1,
            per_page: parseInt(meta.per_page, 10) || 20,
            total: parseInt(meta.total, 10) || 0,
            last_page: parseInt(meta.last_page, 10) || 1
          };
        });
      });

      return $q.all([pAssigned, pUnassigned])
        .catch(function (err) {
          var msg = (err && err.message) || (err && err.statusText) || 'Failed to load lists.';
          toastError(msg);
        })
        .finally(function () {
          vm.loading.lists = false;
        });
    }

    function applySearch() {
      vm.assignedMeta.current_page = 1;
      vm.unassignedMeta.current_page = 1;
      return reload(true);
    }

    function clearSearch() {
      vm.search = { sectionCode: '', subjectCode: '' };
      return applySearch();
    }

    // Pagination helpers (Assigned)
    function pageAssignedPrev() {
      if (vm.assignedMeta.current_page > 1) {
        vm.assignedMeta.current_page -= 1;
        return reload(true);
      }
      return $q.when();
    }
    function pageAssignedNext() {
      if (vm.assignedMeta.current_page < vm.assignedMeta.last_page) {
        vm.assignedMeta.current_page += 1;
        return reload(true);
      }
      return $q.when();
    }
    function pageAssignedGo(n) {
      var p = parseInt(n, 10);
      if (isFinite(p) && p >= 1 && p <= vm.assignedMeta.last_page) {
        vm.assignedMeta.current_page = p;
        return reload(true);
      }
      return $q.when();
    }

    // Pagination helpers (Unassigned)
    function pageUnassignedPrev() {
      if (vm.unassignedMeta.current_page > 1) {
        vm.unassignedMeta.current_page -= 1;
        return reload(true);
      }
      return $q.when();
    }
    function pageUnassignedNext() {
      if (vm.unassignedMeta.current_page < vm.unassignedMeta.last_page) {
        vm.unassignedMeta.current_page += 1;
        return reload(true);
      }
      return $q.when();
    }
    function pageUnassignedGo(n) {
      var p = parseInt(n, 10);
      if (isFinite(p) && p >= 1 && p <= vm.unassignedMeta.last_page) {
        vm.unassignedMeta.current_page = p;
        return reload(true);
      }
      return $q.when();
    }

    // UI helpers
    function facultyNameOfRow(r) {
      if (!r) return '';
      var f = [];
      if (r.facultyFirstname) f.push(r.facultyFirstname);
      if (r.facultyLastname) f.push(r.facultyLastname);
      return f.join(' ');
    }

    function moveToAssign(row) {
      if (!row || !row.intID) return;
      // Add to queue assign; remove from unassign queue if present
      vm.assignQueue[row.intID] = true;
      delete vm.unassignQueue[row.intID];
      // optimistic move in UI
      vm.unassigned = vm.unassigned.filter(function (x) { return x.intID !== row.intID; });
      vm.assigned.unshift(row);
    }

    function moveToUnassign(row) {
      if (!row || !row.intID) return;
      // Add to unassign queue; remove from assign queue if present
      vm.unassignQueue[row.intID] = true;
      delete vm.assignQueue[row.intID];
      // optimistic move in UI
      vm.assigned = vm.assigned.filter(function (x) { return x.intID !== row.intID; });
      vm.unassigned.unshift(row);
    }

    function queueAssign(row) {
      if (!row || !row.intID) return;
      vm.assignQueue[row.intID] = true;
      delete vm.unassignQueue[row.intID];
    }

    function queueUnassign(row) {
      if (!row || !row.intID) return;
      vm.unassignQueue[row.intID] = true;
      delete vm.assignQueue[row.intID];
    }

    function isQueuedAssign(row) {
      return !!(row && row.intID && vm.assignQueue[row.intID]);
    }

    function isQueuedUnassign(row) {
      return !!(row && row.intID && vm.unassignQueue[row.intID]);
    }

    function hasPending() {
      return Object.keys(vm.assignQueue).length > 0 || Object.keys(vm.unassignQueue).length > 0;
    }

    function saveAll() {
      if (!vm.term) {
        toastError('Term is required.');
        return $q.when();
      }
      if (!vm.selectedFacultyId && Object.keys(vm.assignQueue).length > 0) {
        toastError('Select a faculty first before assigning.');
        return $q.when();
      }
      if (!hasPending()) {
        toastInfo('No pending changes.');
        return $q.when();
      }

      vm.loading.saving = true;

      // Build assignments payload
      var assignments = Object.keys(vm.assignQueue)
        .map(function (cidStr) { return parseInt(cidStr, 10); })
        .filter(function (cid) { return isFinite(cid); })
        .map(function (cid) { return { classlist_id: cid, faculty_id: parseInt(vm.selectedFacultyId, 10) }; });

      // Build unassign promises (per-row PUT with intFacultyID=null)
      var unassignIds = Object.keys(vm.unassignQueue)
        .map(function (cidStr) { return parseInt(cidStr, 10); })
        .filter(function (cid) { return isFinite(cid); });
      

      var pAssign = $q.when().then(function () {
        if (!assignments.length) return { applied_count: 0, total: 0, results: [] };
        return FacultyLoadingService.assignBulk(vm.term, assignments)
          .then(function (res) {
            return {
              applied_count: res && typeof res.applied_count !== 'undefined' ? res.applied_count : 0,
              total: res && typeof res.total !== 'undefined' ? res.total : assignments.length,
              results: (res && res.results) || []
            };
          });
      });

      var pUnassign = $q.when().then(function () {
        if (!unassignIds.length) return { applied: 0, total: 0, results: [] };
        var ok = 0;
        var results = [];
        // Chain sequentially to avoid rate spikes
        var seq = $q.when();
        unassignIds.forEach(function (cid) {
          seq = seq.then(function () {
            return FacultyLoadingService.updateSingle(cid, null)
              .then(function () {
                ok += 1;
                results.push({ classlist_id: cid, ok: true });
              })
              .catch(function (err) {
                results.push({ classlist_id: cid, ok: false, message: (err && err.message) || 'Unassign failed' });
              });
          });
        });
        return seq.then(function () {
          return { applied: ok, total: unassignIds.length, results: results };
        });
      });

      return $q.all([pAssign, pUnassign])
        .then(function (pair) {
          var assignRes = pair[0] || { applied_count: 0, total: 0 };
          var unassignRes = pair[1] || { applied: 0, total: 0 };
          var msg = [
            'Assign: ' + assignRes.applied_count + '/' + assignRes.total,
            'Unassign: ' + unassignRes.applied + '/' + unassignRes.total
          ].join(' • ');
          if (assignRes.applied_count === assignRes.total && unassignRes.applied === unassignRes.total) {
            toastSuccess('All changes saved. ' + msg);
          } else {
            toastError('Saved with some errors. ' + msg);
          }
          // Clear queues after save
          vm.assignQueue = Object.create(null);
          vm.unassignQueue = Object.create(null);
          // Refresh lists
          return reload(true);
        })
        .catch(function (err) {
          var msg = (err && err.message) || 'Save failed.';
          toastError(msg);
        })
        .finally(function () {
          vm.loading.saving = false;
        });
    }

    function exportAssignments() {
      if (!vm.term) {
        toastError('Term is required.');
        return $q.when();
      }
      var params = buildBaseParams();
      if (vm.selectedFacultyId) {
        params.intFacultyID = vm.selectedFacultyId;
      }
      params.includeUnassigned = !!vm.includeUnassignedForExport;

      return FacultyLoadingService.exportAssignments(params)
        .then(function () {
          toastSuccess('Export generated. If download did not start, check your browser download settings.');
        })
        .catch(function (err) {
          var msg = (err && err.message) || 'Export failed.';
          toastError(msg);
        });
    }

    // Toast helpers
    function toastSuccess(msg) { try { ToastService.success(msg); } catch (e) {} }
    function toastError(msg) { try { ToastService.error(msg); } catch (e) {} }
    function toastInfo(msg) { try { ToastService.info ? ToastService.info(msg) : ToastService.success(msg); } catch (e) {} }
  }

})();
