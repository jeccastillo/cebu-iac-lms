(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('StudentRecordsController', StudentRecordsController);

  StudentRecordsController.$inject = [
    '$routeParams', '$location', '$http', '$scope',
    'APP_CONFIG', 'StorageService', 'ChecklistService'
  ];
  function StudentRecordsController($routeParams, $location, $http, $scope, APP_CONFIG, StorageService, ChecklistService) {
    var vm = this;

    vm.title = 'Student Records';
    vm.state = StorageService.getJSON('loginState');
    // Rely on global route guard in core/run.js for auth/roles

    // UI state
    vm.activeTab = 'checklist'; // 'checklist' | 'grades'
    vm.setTab = function (tab) { vm.activeTab = tab; };

    // identifiers
    vm.id = $routeParams.id;
    // optional student number (required for grades API)
    vm.sn = ('' + ($location.search().sn || '')).trim();

    // API endpoints (reuse Student Viewer endpoints)
    vm.api = {
      records: (APP_CONFIG.API_BASE + '/student/records'),
      recordsByTerm: (APP_CONFIG.API_BASE + '/student/records-by-term')
    };

    // Admin headers (optional) - attach X-Faculty-ID if available
    function _adminHeaders(extra) {
      var cfg = angular.isObject(extra) ? extra : {};
      try {
        var state = StorageService.getJSON('loginState') || null;
        cfg.headers = cfg.headers || {};
        if (state && state.faculty_id) {
          cfg.headers['X-Faculty-ID'] = state.faculty_id;
        }
      } catch (e) {}
      return cfg;
    }

    // state
    vm.loading = { checklist: false, checklistAction: false, grades: false };
    vm.error = { checklist: null, checklistAction: null, grades: null };

    // Checklist data
    vm.checklist = null;
    vm.checklistSummary = null;

    // Grades data
    vm.records = null;

    // Helpers
    function normalizeId(id) {
      if (id === null || id === undefined) return null;
      if (typeof id === 'number') return id;
      var n = parseInt(id, 10);
      if (!isNaN(n) && ('' + n) === ('' + id).replace(/^0+/, '')) return n;
      return ('' + id).trim();
    }
    function _termOrder(label) {
      if (!label) return 99;
      var s = ('' + label).toLowerCase();
      if (s.indexOf('1st') !== -1 || s.indexOf('first') !== -1 || s.indexOf('1') === 0) return 1;
      if (s.indexOf('2nd') !== -1 || s.indexOf('second') !== -1 || s.indexOf('2') === 0) return 2;
      if (s.indexOf('3rd') !== -1 || s.indexOf('third') !== -1 || s.indexOf('3') === 0) return 3;
      if (s.indexOf('summer') !== -1) return 4;
      return 99;
    }
    function _yearStartFromText(txt) {
      if (!txt) return null;
      var m = ('' + txt).match(/(\d{4})/);
      return m ? parseInt(m[1], 10) : null;
    }
    function _semText(enumSem, termLabel) {
      var n = enumSem != null ? parseInt(enumSem, 10) : null;
      if (n === 1) return '1st Sem';
      if (n === 2) return '2nd Sem';
      if (n === 3) return '3rd Sem';
      if (n === 4) return 'Summer';
      if (termLabel) {
        var s = ('' + termLabel).toLowerCase();
        if (s.indexOf('summer') !== -1) return 'Summer';
        if (s.indexOf('1st') !== -1 || s.indexOf('first') !== -1 || s.indexOf('1') === 0) return '1st Sem';
        if (s.indexOf('2nd') !== -1 || s.indexOf('second') !== -1 || s.indexOf('2') === 0) return '2nd Sem';
        if (s.indexOf('3rd') !== -1 || s.indexOf('third') !== -1 || s.indexOf('3') === 0) return '3rd Sem';
      }
      return null;
    }
    function buildTermLabelFromRow(r, fallbackLabel) {
      if (!r) return fallbackLabel || null;
      var ys = r.strYearStart || r.year_start || r.sy_year_start || null;
      var ye = r.strYearEnd || r.year_end || r.sy_year_end || null;
      var syText = r.school_year || r.schoolYear || r.sy_text || r.sy || r.strSchoolYear || null;
      var semSource = (r.enumSem != null ? r.enumSem : (r.intSem != null ? r.intSem : null));
      var sem = _semText(semSource, r.term || r.label || r.sem || r.semester || r.strSem || fallbackLabel);
      var yearText = null;
      if (ys && ye) {
        yearText = (parseInt(ys, 10) + '-' + parseInt(ye, 10));
      } else if (syText) {
        // Expect formats like "2025-2026" or other strings that include the range.
        var m = ('' + syText).match(/(\d{4})\s*-\s*(\d{4})/);
        yearText = m ? (m[1] + '-' + m[2]) : ('' + syText);
      }
      if (sem && yearText) return sem + ' ' + yearText;
      if (sem) return sem;
      if (yearText) return yearText;
      return fallbackLabel || null;
    }
    function _deriveTermsShapeIfFlat(data) {
      if (data && angular.isArray(data.records) && !data.terms) {
        var grouped = {};
        data.records.forEach(function (r) {
          var syText = r.school_year || r.schoolYear || r.sy_text || r.sy || r.strSchoolYear || r.syid || 'unknown';
          var friendly = buildTermLabelFromRow(r, r.term || r.label || r.sem || r.semester || r.strSem || '');
          var key = (syText || 'unknown') + '|' + (friendly || '');
          if (!grouped[key]) {
            grouped[key] = {
              syid: r.syid || r.intSYID || r.sy_id || null,
              school_year: syText || null,
              label: friendly || null,
              term: friendly || null,
              records: []
            };
          }
          grouped[key].records.push(r);
        });
        data.terms = Object.keys(grouped).map(function (k) { return grouped[k]; });
      }
      return data;
    }
    vm.sortedTerms = function () {
      if (!vm.records) return [];
      var terms = [];
      if (angular.isArray(vm.records.terms)) {
        terms = vm.records.terms.slice();
      } else if (angular.isArray(vm.records.records)) {
        var shaped = _deriveTermsShapeIfFlat({ records: vm.records.records });
        terms = shaped.terms || [];
      }
      terms.sort(function (a, b) {
        // Order strictly by year start, then by enumSem/intSem, ignoring syid
        var ar = (a.records && a.records[0]) ? a.records[0] : null;
        var br = (b.records && b.records[0]) ? b.records[0] : null;
  
        function yearStartFrom(aTerm, r) {
          if (r && (r.strYearStart != null || r.year_start != null || r.sy_year_start != null)) {
            var ys = r.strYearStart != null ? r.strYearStart : (r.year_start != null ? r.year_start : r.sy_year_start);
            var n = parseInt(ys, 10);
            return isNaN(n) ? null : n;
          }
          var t = aTerm && (aTerm.school_year || aTerm.schoolYear || aTerm.sy_text || aTerm.sy || null);
          return t ? _yearStartFromText(t) : null;
        }
        function semNumberFrom(aTerm, r) {
          if (r && r.enumSem != null) {
            var n1 = parseInt(r.enumSem, 10);
            if (!isNaN(n1)) return n1;
          }
          if (r && r.intSem != null) {
            var n2 = parseInt(r.intSem, 10);
            if (!isNaN(n2)) return n2;
          }
          // Derive from label text as fallback
          var st = _semText(null, aTerm && (aTerm.term || aTerm.label));
          if (st === '1st Sem') return 1;
          if (st === '2nd Sem') return 2;
          if (st === '3rd Sem') return 3;
          if (st === 'Summer') return 4;
          return null;
        }
  
        var ayStart = yearStartFrom(a, ar);
        var byStart = yearStartFrom(b, br);
        if (ayStart != null && byStart != null && ayStart !== byStart) return ayStart - byStart;
  
        var as = semNumberFrom(a, ar);
        var bs = semNumberFrom(b, br);
        if (as != null && bs != null && as !== bs) return as - bs;
  
        // Stable fallback
        return 0;
      });
      // Decorate terms with friendly term text if possible (idempotent)
      terms = terms.map(function (t) {
        var r = (t.records && t.records[0]) ? t.records[0] : null;
        var friendly = buildTermLabelFromRow(r, t.term || t.label);
        if (friendly) {
          t.term = friendly;
          t.label = friendly;
        }
        return t;
      });
      return terms;
    };

    // Grades actions
    vm.fetchGrades = function () {
      if (!vm.id) {
        vm.records = null;
        return;
      }
      vm.loading.grades = true;
      vm.error.grades = null;
      var payload = { student_id: vm.id, include_grades: true };
      return $http.post(vm.api.records, payload, _adminHeaders())
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            var data = resp.data.data || resp.data;
            vm.records = _deriveTermsShapeIfFlat(data);
          } else {
            vm.error.grades = 'Failed to load grades.';
          }
        })
        .catch(function () {
          vm.error.grades = 'Failed to load grades.';
        })
        .finally(function () {
          vm.loading.grades = false;
        });
    };

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
        // intCurriculumID optional; backend falls back to tb_mas_users.intCurriculumID
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

    // If ?sn is missing, attempt to resolve student_number from API: GET /students/{id}
    vm.ensureStudentNumber = function () {
      if (vm.sn) return;
      vm.loading.grades = true;
      vm.error.grades = null;
      return $http.get(APP_CONFIG.API_BASE + '/students/' + encodeURIComponent(vm.id), _adminHeaders())
        .then(function (resp) {
          var data = (resp && resp.data && (resp.data.data || resp.data)) ? (resp.data.data || resp.data) : null;
          var sn = data && (data.student_number || data.strStudentNumber);
          if (sn) {
            vm.sn = ('' + sn).trim();
          }
        })
        .catch(function () {
          // leave vm.sn unset; UI will prompt to provide ?sn
        })
        .finally(function () {
          vm.loading.grades = false;
          if (vm.sn) {
            vm.fetchGrades();
          }
        });
    };

    vm.init = function () {
      // Checklist always by student id
      vm.fetchChecklist();

      // Grades: use ?sn when present, else resolve via /students/{id}
      if (vm.sn) {
        vm.fetchGrades();
      } else {
        vm.ensureStudentNumber();
      }
    };

    vm.init();
  }
})();
