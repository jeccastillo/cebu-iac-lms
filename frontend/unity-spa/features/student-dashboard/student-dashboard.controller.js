(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('StudentDashboardController', StudentDashboardController);

  StudentDashboardController.$inject = ['$location', '$http', '$q', 'APP_CONFIG', 'StorageService'];
  function StudentDashboardController($location, $http, $q, APP_CONFIG, StorageService) {
    var vm = this;

    // Page meta
    vm.title = 'My Dashboard';

    // Auth state
    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // API endpoints
    vm.api = {
      viewer: APP_CONFIG.API_BASE + '/student/viewer',
      records: APP_CONFIG.API_BASE + '/student/records'
    };

    // UI state
    vm.loading = { profile: false, records: false };
    vm.error = { profile: null, records: null };

    // Data
    vm.profile = null; // { first_name, last_name, course_name, student_number, last_term, last_term_sy }
    vm.student_number = null;
    vm.terms = [];     // [{ syid, label, term, records: [...] }]
    vm.summary = {
      totalSubjects: 0,
      totalUnits: 0
    };

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
      if (s.indexOf('1st') !== -1 || s.indexOf('first') !== -1) return 1;
      if (s.indexOf('2nd') !== -1 || s.indexOf('second') !== -1) return 2;
      if (s.indexOf('3rd') !== -1 || s.indexOf('third') !== -1) return 3;
      if (s.indexOf('summer') !== -1) return 4;
      return 99;
    }
    function _yearStartFromRow(r) {
      var ys = r && (r.strYearStart || r.year_start || r.sy_year_start || null);
      var n = ys != null ? parseInt(ys, 10) : null;
      return isNaN(n) ? null : n;
    }
    function semText(enumSem, termLabel) {
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
    function buildTermLabel(enumSem, ys, ye, termLabel) {
      var sem = semText(enumSem, termLabel);
      var ysNum = null, yeNum = null;

      if (ys && ye) {
        ysNum = parseInt(ys, 10);
        yeNum = parseInt(ye, 10);
      }
      // Allow passing "YYYY-YYYY" via ys when ye is missing
      if ((!ysNum || !yeNum) && ys && !ye) {
        var m = ('' + ys).match(/(\d{4})\s*-\s*(\d{4})/);
        if (m) {
          ysNum = parseInt(m[1], 10);
          yeNum = parseInt(m[2], 10);
        }
      }
      var yearText = (ysNum && yeNum) ? (ysNum + '-' + yeNum) : (ys && ye ? (ys + '-' + ye) : (ys || null));
      if (sem && yearText) return sem + ' ' + yearText;
      if (sem) return sem;
      if (yearText) return yearText;
      return termLabel || null;
    }

    // Grouping: derive {terms: [...] } shape when API returns flat records
    function deriveTermsShapeIfFlat(data) {
      if (!data || !angular.isArray(data.records)) return data;
      if (data.terms && angular.isArray(data.terms)) return data;

      var grouped = {};
      data.records.forEach(function (r) {
        var key = normalizeId(r.syid);
        if (key === null || key === undefined || key === '') key = 'unknown';
        if (!grouped[key]) {
          var ys = r.strYearStart || r.year_start || r.sy_year_start || null;
          var ye = r.strYearEnd || r.year_end || r.sy_year_end || null;
          var syText = r.school_year || r.schoolYear || r.sy_text || r.sy || r.strSchoolYear || null;
          var semSource = (r.enumSem != null ? r.enumSem : (r.intSem != null ? r.intSem : null));
          var baseLabel = buildTermLabel(semSource, ys || syText, ye, r.term || r.label || r.sem || r.semester);
          var label = baseLabel;
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
      return data;
    }

    function sortTerms(terms) {
      if (!angular.isArray(terms)) return [];
      var copy = terms.slice();
      copy.sort(function (a, b) {
        // Prefer ordering by school year start, then by numeric semester (1,2,3,4). Ignore intID/syid.
        var ar = (a.records && a.records[0]) ? a.records[0] : null;
        var br = (b.records && b.records[0]) ? b.records[0] : null;
  
        function parseYearStart(text) {
          if (!text) return null;
          var m = ('' + text).match(/(\d{4})\s*-\s*\d{4}/);
          if (m) return parseInt(m[1], 10);
          var m2 = ('' + text).match(/(\d{4})/);
          return m2 ? parseInt(m2[1], 10) : null;
        }
        function yearStartFromTerm(t, r) {
          // First try explicit fields from the first record (most reliable)
          var ys = _yearStartFromRow(r);
          if (ys != null) return ys;
          // Fall back to school_year or textual term/label
          var syText = t && (t.school_year || t.schoolYear || t.sy_text || t.sy || null);
          if (syText) {
            var n = parseYearStart(syText);
            if (n != null) return n;
          }
          var lbl = t && (t.term || t.label) ? ('' + (t.term || t.label)) : null;
          return parseYearStart(lbl);
        }
        function semNumberFrom(t, r) {
          // Prefer numeric from record
          if (r && r.enumSem != null) {
            var n1 = parseInt(r.enumSem, 10);
            if (!isNaN(n1)) return n1;
          }
          if (r && r.intSem != null) {
            var n2 = parseInt(r.intSem, 10);
            if (!isNaN(n2)) return n2;
          }
          // Fallback to label text
          var lbl = t && (t.term || t.label) ? ('' + (t.term || t.label)).toLowerCase() : '';
          if (lbl.indexOf('summer') !== -1) return 4;
          if (lbl.indexOf('1st') !== -1 || lbl.indexOf('first') !== -1 || lbl.indexOf('1 ') === 0) return 1;
          if (lbl.indexOf('2nd') !== -1 || lbl.indexOf('second') !== -1 || lbl.indexOf('2 ') === 0) return 2;
          if (lbl.indexOf('3rd') !== -1 || lbl.indexOf('third') !== -1 || lbl.indexOf('3 ') === 0) return 3;
          return null;
        }
  
        var ayStart = yearStartFromTerm(a, ar);
        var byStart = yearStartFromTerm(b, br);
        if (ayStart != null && byStart != null && ayStart !== byStart) return ayStart - byStart;
  
        var as = semNumberFrom(a, ar);
        var bs = semNumberFrom(b, br);
        if (as != null && bs != null && as !== bs) return as - bs;
  
        // Stable fallback
        return 0;
      });
      return copy;
    }

    function computeSummary(terms) {
      var subjects = 0;
      var units = 0;
      (terms || []).forEach(function (t) {
        (t.records || []).forEach(function (r) {
          subjects += 1;
          var u = r && r.units != null ? parseInt(r.units, 10) : 0;
          if (!isNaN(u)) units += u;
        });
      });
      vm.summary.totalSubjects = subjects;
      vm.summary.totalUnits = units;
    }
 
    // Fallback schedule formatter: prefer backend schedule_text; otherwise compose from fields
    vm.formatSchedule = function (r) {
      if (!r) return '-';
      var txt = (r.schedule_text !== undefined && r.schedule_text !== null) ? ('' + r.schedule_text).trim() : '';
      if (txt) return txt;
 
      // Try alternate field names as defensive fallbacks
      var days = r.schedule_days || r.scheduleDays || r.days || null;
      var times = r.schedule_times || r.scheduleTimes || r.times || null;
      var rooms = r.schedule_rooms || r.scheduleRooms || r.rooms || null;
 
      var parts = [];
      if (days && times) {
        parts.push((days + ' ' + times).trim());
      } else if (times) {
        parts.push(('' + times).trim());
      } else if (days) {
        parts.push(('' + days).trim());
      }
      if (rooms) {
        parts.push(('' + rooms).trim());
      }
 
      var out = parts.join(' — ');
      return out && out.trim() !== '' ? out : '-';
    };
 
    // Data loaders
    function loadProfile() {
      vm.loading.profile = true;
      vm.error.profile = null;
      var username = (vm.state && vm.state.username) ? ('' + vm.state.username).trim() : '';
      if (!username) {
        vm.loading.profile = false;
        vm.error.profile = 'Missing login username.';
        return $q.reject(new Error('missing username'));
      }
      // Resolve basic profile from token (gsuite email per backend parity)
      return $http.post(vm.api.viewer, { token: username })
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            var data = resp.data.data || resp.data;
            // StudentResource is returned; unwrap if needed
            vm.profile = (data && data.data) ? data.data : data;
            vm.student_number = (vm.profile && vm.profile.student_number) ? ('' + vm.profile.student_number).trim() : null;
            vm.student_id = (vm.profile && vm.profile.student_id) ? ('' + vm.profile.student_id).trim() : null;
            if (!vm.student_number) {
              // Fallback to username if backend profile lacks student_number
              vm.student_number = username;
            }
          } else {
            vm.error.profile = 'Failed to load profile.';
            // Fallback: treat username as student_number when API returns success=false
            vm.student_number = username;
          }
        })
        .catch(function () {
          // Fallback: treat username as student_number
          vm.profile = null;
          vm.student_number = username;
        })
        .finally(function () {
          vm.loading.profile = false;
        });
    }

    function loadRecords() {
      vm.loading.records = true;
      vm.error.records = null;
      if (!vm.student_id) {
        vm.loading.records = false;
        vm.error.records = 'Missing student ID.';
        return $q.reject(new Error('missing student id'));
      }
      var payload = { student_id: vm.student_id, include_grades: true };
      return $http.post(vm.api.records, payload)
        .then(function (resp) {
          if (resp && resp.data && resp.data.success !== false) {
            var data = resp.data.data || resp.data;
            data = deriveTermsShapeIfFlat(data) || {};
            // First decorate each term to ensure we have a friendly label with year text,
            // then perform ordering using year start and numeric semester.
            var decorated = (data.terms || []).map(function (t) {
              var r = (t.records && t.records[0]) ? t.records[0] : null;
              var ys = r ? (r.strYearStart || r.year_start || r.sy_year_start) : null;
              var ye = r ? (r.strYearEnd || r.year_end || r.sy_year_end) : null;
              var syText = r ? (r.school_year || r.schoolYear || r.sy_text || r.sy || r.strSchoolYear) : null;
              var semSource = r ? (r.enumSem != null ? r.enumSem : (r.intSem != null ? r.intSem : null)) : null;
              var friendly = buildTermLabel(semSource, ys || syText, ye, t.term || t.label);
              if (friendly) {
                t.term = friendly;
                t.label = friendly;
              }
              return t;
            });
            vm.terms = sortTerms(decorated);
            computeSummary(vm.terms);
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
    }

    // init
    function init() {
      // Students should land here, but allow admin to view page as well
      // Proceed with loading profile and records
      loadProfile().finally(function () {
        loadRecords();
      });
    }

    init();
  }

})();
