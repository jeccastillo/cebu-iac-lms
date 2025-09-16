(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdvisorsController', AdvisorsController);

  AdvisorsController.$inject = ['$scope', '$http', 'APP_CONFIG', 'StorageService', 'CampusService', 'StudentsService', 'StudentAdvisorService'];
  function AdvisorsController($scope, $http, APP_CONFIG, StorageService, CampusService, StudentsService, StudentAdvisorService) {
    var vm = this;
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    // UI State
    vm.loading = false;
    vm.error = null;
    vm.message = null;

    // Bulk assign form
    vm.advisorSearch = '';
    vm.advisorOptions = [];     // [{id, text}]
    vm.selectedAdvisorId = null;
    vm.studentNumbers = [];     // select2 multi-select tags: student numbers
    vm.studentNumbersRaw = '';  // legacy textarea fallback (kept for compatibility)
    vm.replaceExisting = false;

    // Autocomplete-based selection of students (by name/number)
    vm.studentSelectId = null;  // ng-model for pui-autocomplete
    vm.studentItems = [];       // pui-autocomplete source list (suggestions)
    vm.selectedStudents = [];   // [{ id, student_number, last_name, first_name, middle_name }]

    // Quick switch form
    vm.fromAdvisorSearch = '';
    vm.fromAdvisorOptions = [];
    vm.fromAdvisorId = null;

    vm.toAdvisorSearch = '';
    vm.toAdvisorOptions = [];
    vm.toAdvisorId = null;

    // Lookup
    vm.lookupStudentId = '';
    vm.lookupStudentNumber = '';
    vm.lookupResult = null;
    // Autocomplete for lookup
    vm.lookupStudentSelectId = null;
    vm.lookupStudentItems = [];

    // Expose methods
    vm.searchAdvisors = searchAdvisors;
    vm.searchFromAdvisors = searchFromAdvisors;
    vm.searchToAdvisors = searchToAdvisors;
 
    // Autocomplete handlers
    vm.onStudentQuery = onStudentQuery;
    vm.addSelectedStudent = addSelectedStudent;
    vm.removeSelectedStudent = removeSelectedStudent;
    // Lookup autocomplete handlers
    vm.onLookupStudentQuery = onLookupStudentQuery;
    vm.onLookupStudentSelected = onLookupStudentSelected;

    vm.assignBulk = assignBulk;
    vm.switchAll = switchAll;
    vm.lookup = lookup;

    // Helpers
    vm.parseStudentNumbers = parseStudentNumbers;
    vm.clearMessages = clearMessages;

    activate();

    function activate() {
      vm.loading = true;
      vm.error = null;
      vm.message = null;

      // Ensure campus service initialized (for campus-aware advisor search)
      var p = (CampusService && CampusService.init) ? CampusService.init() : null;
      if (p && p.then) {
        p.finally(function () { vm.loading = false; });
      } else {
        vm.loading = false;
      }

      // Prime advisor lists for convenience (empty search yields teaching=1 by campus or all)
      try {
        searchAdvisors();
        searchFromAdvisors();
        searchToAdvisors();
      } catch (e) {}
    }

    function getSelectedCampusId() {
      try {
        var c = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        var id = (c && c.id !== undefined && c.id !== null && ('' + c.id).trim() !== '') ? parseInt(c.id, 10) : null;
        return isFinite(id) ? id : null;
      } catch (e) { return null; }
    }

    function _getLoginState() {
      try { return StorageService.getJSON('loginState') || null; } catch (e) { return null; }
    }

    function _adminHeaders(extra) {
      var state = _getLoginState();
      var headers = Object.assign({ 'Accept': 'application/json' }, extra || {});
      if (state && state.faculty_id) headers['X-Faculty-ID'] = state.faculty_id;
      return { headers: headers };
    }

    // ---------------- Advisor search (uses Generic API for teaching=1 list, campus-aware) ----------------

    function buildFacultyQueryPayload(q) {
      var params = { teaching: 1 };
      if (q && ('' + q).trim() !== '') params.q = ('' + q).trim();
      var campusId = getSelectedCampusId();
      if (campusId !== null) params.campus_id = campusId;
      return params;
    }

    function mapFacultyRowsToOptions(rows) {
      try {
        var opts = (rows || []).map(function (r) {
          var txt = r.full_name || (r.first_name || '') + ' ' + (r.last_name || '');
          return { id: r.id, text: txt.trim() };
        });
        // Dedup by id (if any)
        var seen = {};
        return opts.filter(function (o) {
          if (!o || o.id == null) return false;
          if (seen[o.id]) return false;
          seen[o.id] = true;
          return true;
        });
      } catch (e) { return []; }
    }

    function _searchGenericFaculty(q, onDone) {
      var payload = buildFacultyQueryPayload(q);
      $http.get(BASE + '/generic/faculty', { params: payload, headers: _adminHeaders().headers })
        .then(function (resp) {
          var d = resp && resp.data ? resp.data.data || resp.data : [];
          var opts = mapFacultyRowsToOptions(d);
          if (typeof onDone === 'function') onDone(null, opts);
        })
        .catch(function (err) {
          if (typeof onDone === 'function') onDone(err, []);
        });
    }

    function searchAdvisors() {
      _searchGenericFaculty(vm.advisorSearch, function (err, opts) {
        if (err) return;
        vm.advisorOptions = opts;
        // Maintain selection if still present
        if (vm.selectedAdvisorId) {
          var found = opts.some(function (o) { return ('' + o.id) === ('' + vm.selectedAdvisorId); });
          if (!found) vm.selectedAdvisorId = null;
        }
      });
    }

    function searchFromAdvisors() {
      _searchGenericFaculty(vm.fromAdvisorSearch, function (err, opts) {
        if (err) return;
        vm.fromAdvisorOptions = opts;
        if (vm.fromAdvisorId) {
          var found = opts.some(function (o) { return ('' + o.id) === ('' + vm.fromAdvisorId); });
          if (!found) vm.fromAdvisorId = null;
        }
      });
    }

    function searchToAdvisors() {
      _searchGenericFaculty(vm.toAdvisorSearch, function (err, opts) {
        if (err) return;
        vm.toAdvisorOptions = opts;
        if (vm.toAdvisorId) {
          var found = opts.some(function (o) { return ('' + o.id) === ('' + vm.toAdvisorId); });
          if (!found) vm.toAdvisorId = null;
        }
      });
    }

    // ---------------- Autocomplete: Students (name/number) ----------------

    function onStudentQuery(q) {
      try {
        // Returns a promise; directive doesn't require it to return anything, we just update source
        return StudentsService.listSuggestions(q).then(function (rows) {
          vm.studentItems = Array.isArray(rows) ? rows : [];
        });
      } catch (e) {
        vm.studentItems = [];
      }
    }

    function addSelectedStudent() {
      try {
        var sid = vm.studentSelectId != null ? parseInt(vm.studentSelectId, 10) : null;
        if (!sid || isNaN(sid)) return;
        // Find full item from current suggestions (best-effort)
        var it = null;
        for (var i = 0; i < vm.studentItems.length; i++) {
          if (('' + vm.studentItems[i].id) === ('' + sid)) { it = vm.studentItems[i]; break; }
        }
        // Deduplicate by id
        var exists = vm.selectedStudents.some(function (s) { return ('' + s.id) === ('' + sid); });
        if (!exists) {
          if (it) {
            vm.selectedStudents.push(it);
          } else {
            // Fallback minimal record
            vm.selectedStudents.push({ id: sid });
          }
        }
        // Clear model for next search (input text may remain; model is reset)
        vm.studentSelectId = null;
      } catch (e) {}
    }

    function removeSelectedStudent(idxOrObj) {
      try {
        if (typeof idxOrObj === 'number') {
          if (idxOrObj >= 0 && idxOrObj < vm.selectedStudents.length) {
            vm.selectedStudents.splice(idxOrObj, 1);
          }
          return;
        }
        var id = idxOrObj && idxOrObj.id ? idxOrObj.id : null;
        if (id == null) return;
        vm.selectedStudents = vm.selectedStudents.filter(function (s) { return ('' + s.id) !== ('' + id); });
      } catch (e) {}
    }

    // Lookup autocomplete handlers
    function onLookupStudentQuery(q) {
      try {
        return StudentsService.listSuggestions(q).then(function (rows) {
          vm.lookupStudentItems = Array.isArray(rows) ? rows : [];
        });
      } catch (e) {
        vm.lookupStudentItems = [];
      }
    }

    function onLookupStudentSelected() {
      try {
        var sid = vm.lookupStudentSelectId != null ? parseInt(vm.lookupStudentSelectId, 10) : null;
        if (sid && !isNaN(sid)) {
          vm.lookupStudentId = sid;
          vm.lookup();
        }
      } catch (e) {}
    }

    // ---------------- Bulk Assign ----------------

    function parseStudentNumbers(raw) {
      if (!raw) return [];
      try {
        var cleaned = ('' + raw)
          .replace(/[\r\n,;]+/g, '\n')  // unify delimiters to newline
          .split('\n')
          .map(function (s) { return ('' + s).trim(); })
          .filter(function (s) { return s.length > 0; });
        // Dedup
        var seen = {};
        var out = [];
        cleaned.forEach(function (s) {
          if (!seen[s]) { seen[s] = true; out.push(s); }
        });
        return out;
      } catch (e) {
        return [];
      }
    }

    function clearMessages() {
      vm.error = null;
      vm.message = null;
    }

    function assignBulk() {
      clearMessages();
      var advisorId = vm.selectedAdvisorId != null ? parseInt(vm.selectedAdvisorId, 10) : null;
      if (!advisorId || isNaN(advisorId)) {
        vm.error = 'Select an advisor to assign.';
        return;
      }
  
      // Prefer selected students via autocomplete; fallback to legacy student_numbers if available
      var student_ids = [];
      try {
        if (Array.isArray(vm.selectedStudents) && vm.selectedStudents.length) {
          var seenIds = {};
          vm.selectedStudents.forEach(function (s) {
            var id = (s && s.id != null) ? parseInt(s.id, 10) : null;
            if (isFinite(id) && id > 0 && !seenIds[id]) {
              seenIds[id] = true;
              student_ids.push(id);
            }
          });
        }
      } catch (e) {}
  
      // Legacy fallback (kept for compatibility; UI no longer exposes inputs for this)
      var student_numbers = [];
      try {
        var nums = (Array.isArray(vm.studentNumbers) && vm.studentNumbers.length)
          ? vm.studentNumbers.slice(0)
          : parseStudentNumbers(vm.studentNumbersRaw);
        student_numbers = (nums || []).map(function (s) { return ('' + s).trim(); })
          .filter(function (s) { return s.length > 0; });
        (function dedup() {
          var seen = {};
          student_numbers = student_numbers.filter(function (s) {
            if (seen[s]) return false;
            seen[s] = true;
            return true;
          });
        })();
      } catch (e2) {}
  
      if ((!student_ids || !student_ids.length) && (!student_numbers || !student_numbers.length)) {
        vm.error = 'Select at least one student.';
        return;
      }
  
      vm.loading = true;
      var payload = {
        replace_existing: !!vm.replaceExisting,
        campus_id: getSelectedCampusId()
      };
      if (student_ids && student_ids.length) {
        payload.student_ids = student_ids;
      } else {
        payload.student_numbers = student_numbers;
      }

      // Include advisor_id in request body for parity/analytics, even though API also uses path param
      payload.advisor_id = advisorId;
  
      StudentAdvisorService.assignBulk(advisorId, payload)
        .then(function (res) {
          vm.message = 'Bulk assignment completed.';
          // Normalize display of results
          var data = res && res.data ? res.data : (res && res.results ? res : null);
          vm.bulkResult = data || res;
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Bulk assignment failed.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // ---------------- Quick Switch ----------------

    function switchAll() {
      clearMessages();
      var fromId = vm.fromAdvisorId != null ? parseInt(vm.fromAdvisorId, 10) : null;
      var toId = vm.toAdvisorId != null ? parseInt(vm.toAdvisorId, 10) : null;
      if (!fromId || !toId || isNaN(fromId) || isNaN(toId)) {
        vm.error = 'Select both source and target advisors.';
        return;
      }
      if (fromId === toId) {
        vm.error = 'Source and target advisors must be different.';
        return;
      }

      vm.loading = true;
      StudentAdvisorService.switchAll({ from_advisor_id: fromId, to_advisor_id: toId })
        .then(function (res) {
          vm.message = 'Switch operation completed.';
          vm.switchResult = res && res.data ? res.data : res;
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Switch operation failed.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    // ---------------- Lookup ----------------

    function lookup() {
      clearMessages();
      // Prefer selection from autocomplete if present
      var sid = (vm.lookupStudentSelectId != null && ('' + vm.lookupStudentSelectId).trim() !== '')
        ? String(parseInt(vm.lookupStudentSelectId, 10))
        : ('' + (vm.lookupStudentId || '')).trim();
      var sn = ('' + (vm.lookupStudentNumber || '')).trim();
      if (!sid && !sn) {
        vm.error = 'Enter a Student ID or Student Number to lookup.';
        return;
      }

      vm.loading = true;
      StudentAdvisorService.getByStudent({
        student_id: sid ? parseInt(sid, 10) : undefined,
        student_number: sn || undefined
      })
        .then(function (res) {
          vm.lookupResult = res && res.data ? res.data : res;
          vm.message = 'Lookup complete.';
        })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Lookup failed.';
        })
        .finally(function () {
          vm.loading = false;
        });
    }
  }
})();
