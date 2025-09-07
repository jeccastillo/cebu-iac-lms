(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('StudentViewerController', StudentViewerController);

  StudentViewerController.$inject = ['$routeParams', '$location', '$http', '$scope', 'APP_CONFIG', 'StorageService', 'LinkService', 'TermService', 'ChecklistService'];
  function StudentViewerController($routeParams, $location, $http, $scope, APP_CONFIG, StorageService, LinkService, TermService, ChecklistService) {
    var vm = this;

    vm.title = 'Student Viewer';
    vm.state = StorageService.getJSON('loginState');

    // guard: require login
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Links for legacy CI pages (optional navigations)
    vm.links = LinkService.buildLinks();
    vm.nav = LinkService.buildSpaLinks();

    // identifiers
    vm.id = $routeParams.id;

    // api endpoints
    vm.api = {
      balances: APP_CONFIG.API_BASE + '/student/balances',
      records: APP_CONFIG.API_BASE + '/student/records',
      recordsByTerm: APP_CONFIG.API_BASE + '/student/records-by-term',
      ledger: APP_CONFIG.API_BASE + '/student/ledger',
      terms: APP_CONFIG.API_BASE + '/generic/terms'
    };

    // state
    vm.loading = { balances: false, records: false, ledger: false, checklist: false, checklistAction: false };
    vm.error = { balances: null, records: null, ledger: null, checklist: null, checklistAction: null };
    vm.balances = null;
    vm.records = null;
    vm.ledger = null;

    // Checklist state
    vm.checklist = null;
    vm.checklistSummary = null;
    vm.genYearLevel = 1;
    vm.genSem = '1st';
    vm.addSubjectId = null;
    vm.terms = [];
    vm.selectedTermId = null;

    // Helper: normalize term IDs to number if numeric, else trimmed string
    function normalizeId(id) {
      if (id === null || id === undefined) return null;
      if (typeof id === 'number') return id;
      var n = parseInt(id, 10);
      if (!isNaN(n) && ('' + n) === ('' + id).replace(/^0+/, '')) return n;
      return ('' + id).trim();
    }

    // Dates: helpers for input[type=date] binding compatibility
    function parseDateYMD(s) {
      if (!s) return null;
      if (s instanceof Date) return s;
      try {
        var d = new Date(s);
        return isNaN(d.getTime()) ? null : d;
      } catch (e) {
        return null;
      }
    }
    function toYMD(d) {
      if (!d) return null;
      var dt = d instanceof Date ? d : new Date(d);
      if (isNaN(dt.getTime())) return null;
      var mm = ('0' + (dt.getMonth() + 1)).slice(-2);
      var dd = ('0' + dt.getDate()).slice(-2);
      return dt.getFullYear() + '-' + mm + '-' + dd;
    }

    // When term changes via the dropdown or broadcast, refetch records
    vm.onTermChange = function () {
      vm.selectedTermId = normalizeId(vm.selectedTermId);
      // If selectedTermId is null, backend should return all terms; still handle client-side filtering
      vm.fetchRecords();
    };

    // Returns record terms filtered by selectedTermId (fallback if backend ignores term)
    vm.filteredTerms = function () {
      if (!vm.records || !vm.records.terms || !angular.isArray(vm.records.terms)) return [];

      var sid = normalizeId(vm.selectedTermId);
      if (!sid) return vm.records.terms;

      // Try to locate the selected term object from vm.terms
      var selected = null;
      for (var i = 0; i < vm.terms.length; i++) {
        var cand = vm.terms[i];
        if (cand && normalizeId(cand.intID) === sid) {
          selected = cand;
          break;
        }
      }

      // Primary: compare selected term's school-year id (syid) to records' syid variants
      var selSy = null;
      if (selected) {
        selSy = normalizeId(
          selected.syid || selected.sy_id || selected.syId ||
          selected.intSYID || selected.intSyID ||
          selected.school_year_id || selected.schoolYearId
        );
      }
      // Fallback: if selected term object doesn't carry a syid, use selectedTermId (sid)
      if (selSy === null || selSy === undefined || selSy === '') {
        selSy = sid;
      }

      return vm.records.terms.filter(function (t) {
        if (!t) return false;

        // If we have a selected SY, match records by their SY fields first
        if (selSy != null) {
          var recSy = normalizeId(
            t.syid || t.sy_id || t.syId ||
            t.intSYID || t.intSyID ||
            t.school_year_id || t.schoolYearId
          );
          if (recSy != null && recSy === selSy) return true;
        }

        // Match by common id fields after normalization
        if (normalizeId(t.intID) === sid) return true;
        if (normalizeId(t.term_id) === sid) return true;
        if (normalizeId(t.sem_id) === sid) return true;
        if (normalizeId(t.semester_id) === sid) return true;

        // Label-based fallback
        if (selected) {
          var selLabel = (selected.label != null ? ('' + selected.label).trim() : null);
          if (selLabel) {
            if (t.label && ('' + t.label).trim() === selLabel) return true;
            if (t.term && ('' + t.term).trim() === selLabel) return true;
          }
        }
        return false;
      });
    };

    // Checklist actions
    vm.fetchChecklist = function () {
      vm.loading.checklist = true;
      vm.error.checklist = null;
      return ChecklistService.get(vm.id, {})
        .then(function (resp) {
          // API returns { success, data }
          var data = resp && resp.data ? resp.data : (resp || null);
          vm.checklist = data;

          // Normalize date fields for date input binding
          try {
            if (vm.checklist && vm.checklist.items && angular.isArray(vm.checklist.items)) {
              vm.checklist.items.forEach(function (it) {
                if (it && it.dteCompleted) {
                  it.dteCompleted = parseDateYMD(it.dteCompleted);
                }
              });
            }
          } catch (e) {}

          return ChecklistService.summary(vm.id, {});
        })
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : (resp || null);
          vm.checklistSummary = data;
        })
        .catch(function () {
          vm.error.checklist = 'Failed to load checklist.';
        })
        .finally(function () {
          vm.loading.checklist = false;
        });
    };

    vm.generateChecklist = function () {
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      var payload = {
        // intCurriculumID is optional; backend falls back to tb_mas_users.intCurriculumID
      };
      return ChecklistService.generate(vm.id, payload)
        .then(function () {
          return vm.fetchChecklist();
        })
        .catch(function () {
          vm.error.checklistAction = 'Failed to generate checklist.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    vm.updateChecklistItem = function (item) {
      if (!item || !item.id) return;
      var payload = {
        strStatus: item.strStatus,
        isRequired: item.isRequired ? 1 : 0,
        dteCompleted: toYMD(item.dteCompleted),
        intYearLevel: item.intYearLevel != null ? parseInt(item.intYearLevel, 10) : null,
        intSem: item.intSem != null ? parseInt(item.intSem, 10) : null
      };
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      ChecklistService.updateItem(vm.id, item.id, payload)
        .then(function () {
          return vm.fetchChecklist();
        })
        .catch(function () {
          vm.error.checklistAction = 'Failed to update item.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    vm.removeChecklistItem = function (item) {
      if (!item || !item.id) return;
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      ChecklistService.deleteItem(vm.id, item.id)
        .then(function () {
          return vm.fetchChecklist();
        })
        .catch(function () {
          vm.error.checklistAction = 'Failed to remove item.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    vm.addChecklistItem = function () {
      var sid = parseInt(vm.addSubjectId, 10);
      if (isNaN(sid) || !vm.checklist || !vm.checklist.id) return;
      vm.loading.checklistAction = true;
      vm.error.checklistAction = null;
      var payload = {
        intChecklistID: vm.checklist.id,
        intSubjectID: sid,
        strStatus: 'planned',
        isRequired: 1
      };
      ChecklistService.addItem(vm.id, payload)
        .then(function () {
          vm.addSubjectId = null;
          return vm.fetchChecklist();
        })
        .catch(function () {
          vm.error.checklistAction = 'Failed to add subject.';
        })
        .finally(function () {
          vm.loading.checklistAction = false;
        });
    };

    // actions
    vm.fetchBalances = function () {
      vm.loading.balances = true;
      vm.error.balances = null;
      return $http.post(vm.api.balances, { student_id: vm.id })
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            vm.balances = resp.data.data || resp.data;
          } else {
            vm.error.balances = 'Failed to load balances.';
          }
        })
        .catch(function () {
          vm.error.balances = 'Failed to load balances.';
        })
        .finally(function () {
          vm.loading.balances = false;
        });
    };

    vm.fetchRecords = function () {
      vm.loading.records = true;
      vm.error.records = null;

      var payload = { student_id: vm.id, include_grades: true };
      var sid = normalizeId(vm.selectedTermId);
      if (sid !== null && sid !== undefined && sid !== '') {
        // API requires 'term' to be a string per StudentRecordsRequest
        payload.term = '' + sid;
      }

      function deriveTermsShapeIfFlat(data) {
        if (data && angular.isArray(data.records) && !data.terms) {
          var grouped = {};
          data.records.forEach(function (r) {
            var key = normalizeId(r.syid);
            var label = r.term || (r.label || null);
            if (key === null || key === undefined || key === '') key = 'unknown';
            if (!grouped[key]) {
              grouped[key] = {
                syid: r.syid || null,
                label: label,
                term: label,
                records: []
              };
            }
            grouped[key].records.push(r);
          });
          data.terms = Object.keys(grouped).map(function (k) { return grouped[k]; });
        }
        return data;
      }

      function hasAnyRecords(data) {
        if (!data) return false;
        if (angular.isArray(data.records)) return data.records.length > 0;
        if (data.terms && angular.isArray(data.terms)) {
          if (!data.terms.length) return false;
          for (var i = 0; i < data.terms.length; i++) {
            var t = data.terms[i];
            if (t && angular.isArray(t.records) && t.records.length) return true;
          }
          return false;
        }
        return false;
      }
      
      var endpoint = (sid !== null && sid !== undefined && sid !== '' ? vm.api.recordsByTerm : vm.api.records);
      return $http.post(endpoint, payload)
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            var data = resp.data.data || resp.data;
            data = deriveTermsShapeIfFlat(data);
            vm.records = data;
          } else {
            vm.error.records = 'Failed to load records.';
          }
        })
        .catch(function () {
          vm.error.records = 'Failed to load records.';
        })
        .finally(function () {
          vm.loading.records = false;
        });
    };

    vm.fetchLedger = function () {
      vm.loading.ledger = true;
      vm.error.ledger = null;
      return $http.post(vm.api.ledger, { student_id: vm.id })
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            vm.ledger = resp.data.data || resp.data;
          } else {
            vm.error.ledger = 'Failed to load ledger.';
          }
        })
        .catch(function () {
          vm.error.ledger = 'Failed to load ledger.';
        })
        .finally(function () {
          vm.loading.ledger = false;
        });
    };

    vm.loadTerms = function () {
      return $http.get(vm.api.terms)
        .then(function (resp) {
          if (resp && resp.data) {
            vm.terms = resp.data.data || resp.data;

            // If term is provided via query (?term=INTID), prioritize it
            try {
              var termQ = $location.search().term;
              if (termQ !== undefined && termQ !== null && ('' + termQ).trim() !== '') {
                vm.selectedTermId = normalizeId(termQ);
                vm.onTermChange();
                return;
              }
            } catch (e) {}

            // Preselect term from TermService or storage if available
            try {
              var saved = (TermService && TermService.getSelectedTerm && TermService.getSelectedTerm()) || StorageService.getJSON('selectedTerm');
              if (saved && saved.intID) {
                vm.selectedTermId = normalizeId(saved.intID);
                // Trigger refresh with selected term
                vm.onTermChange();
              }
            } catch (e) {}

            // If still no selected term, fall back to active term from API
            if (!vm.selectedTermId) {
              try {
                TermService.getActiveTerm().then(function (activeTerm) {
                  if (activeTerm && activeTerm.intID) {
                    vm.selectedTermId = normalizeId(activeTerm.intID);
                    vm.onTermChange();
                  }
                });
              } catch (e) {}
            }
          }
        });
    };

    // Listen to global term changes from TermService (sidebar selector)
    $scope.$on('termChanged', function (event, data) {
      if (data && data.selectedTerm && data.selectedTerm.intID) {
        vm.selectedTermId = normalizeId(data.selectedTerm.intID);
        vm.onTermChange();
      }
    });

    // legacy utility links (optional)
    vm.editUrl = function () {
      return vm.links.unity.replace('/unity', '') + '/student/edit_student/' + vm.id;
    };
    vm.financesUrl = function () {
      return '#/finance/cashier/' + vm.id;
    };

    // init
    vm.init = function () {
      // Checklist operates by student id
      vm.fetchChecklist();

      // Load balances and ledger right away using student_id
      vm.fetchBalances();
      vm.fetchLedger();

      // Load terms first to apply saved/active term, then fetch records accordingly
      vm.loadTerms().then(function () {
        if (!vm.selectedTermId) {          
          // No preset term; fetch unfiltered records
          vm.fetchRecords();
        }
        // If selectedTermId was set, vm.onTermChange() inside loadTerms already triggered fetchRecords()
      });
    };
    
    vm.init();
  }

})();
