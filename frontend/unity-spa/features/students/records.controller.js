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
    function _deriveTermsShapeIfFlat(data) {
      if (data && angular.isArray(data.records) && !data.terms) {
        var grouped = {};
        data.records.forEach(function (r) {
          var syText = r.school_year || r.schoolYear || r.sy_text || r.sy || r.strSchoolYear || r.syid || 'unknown';
          var label = r.term || r.label || r.sem || r.semester || r.strSem || '';
          var key = (syText || 'unknown') + '|' + (label || '');
          if (!grouped[key]) {
            grouped[key] = {
              syid: r.syid || r.intSYID || r.sy_id || null,
              school_year: syText || null,
              label: label || null,
              term: label || null,
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
        var ay = normalizeId(a.syid);
        var by = normalizeId(b.syid);
        var ayText = a.school_year || a.schoolYear || a.sy_text || a.sy || null;
        var byText = b.school_year || b.schoolYear || b.sy_text || b.sy || null;
        var ayStart = ayText ? _yearStartFromText(ayText) : null;
        var byStart = byText ? _yearStartFromText(byText) : null;

        // Prefer numeric school year id if comparable, else fallback to year start text
        if (ay != null && by != null && ay !== by) return ay - by;
        if (ayStart != null && byStart != null && ayStart !== byStart) return ayStart - byStart;

        // Within year, sort by term order
        var at = _termOrder(a.term || a.label);
        var bt = _termOrder(b.term || b.label);
        if (at !== bt) return at - bt;

        // Stable fallback
        return 0;
      });
      return terms;
    };

    // Grades actions
    vm.fetchGrades = function () {
      if (!vm.sn) {
        vm.records = null;
        return;
      }
      vm.loading.grades = true;
      vm.error.grades = null;
      var payload = { student_number: vm.sn, include_grades: true };
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
