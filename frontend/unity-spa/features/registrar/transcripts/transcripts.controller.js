(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('RegistrarTranscriptsController', RegistrarTranscriptsController);

  RegistrarTranscriptsController.$inject = ['$http', '$q', '$scope', 'APP_CONFIG', 'StorageService', 'ReportsService', 'StudentsService', 'TermService', 'StudentBillingService', 'UnityService'];
  function RegistrarTranscriptsController($http, $q, $scope, APP_CONFIG, StorageService, ReportsService, StudentsService, TermService, StudentBillingService, UnityService) {
    var vm = this;

    vm.title = 'Generate Transcript / Copy of Grades';

    // Admin headers (attach X-Faculty-ID when present)
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

    // Page state
    vm.loading = {
      students: false,
      terms: false,
      generate: false,
      history: false
    };
    vm.error = {
      students: null,
      terms: null,
      generate: null,
      history: null
    };

    // Global term (from TermService)
    vm.globalTermId = null;
    // Initialize global term inline to avoid ReferenceError before helper is defined
    (function initGlobalTerm() {
      try {
        var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
        if (sel && (sel.intID || sel.id)) {
          vm.globalTermId = Number(sel.intID || sel.id);
        } else {
          var p = TermService && TermService.getActiveTerm ? TermService.getActiveTerm() : null;
          if (p && typeof p.then === 'function') {
            p.then(function (t) {
              if (t && (t.intID || t.id)) {
                vm.globalTermId = Number(t.intID || t.id);
              }
            }).catch(function () {});
          }
        }
      } catch (e) {}
    })();
    // React to global term changes
    if ($scope && $scope.$on) {
      $scope.$on('termChanged', function (event, data) {
        try {
          if (data && data.selectedTerm && (data.selectedTerm.intID || data.selectedTerm.id)) {
            vm.globalTermId = Number(data.selectedTerm.intID || data.selectedTerm.id);
            // Refresh history and paid billing if a student is selected
            if (vm.studentId) {
              loadHistory(vm.studentId);
            }
          }
        } catch (e) {}
      });
    }

    // Student search/selection
    vm.studentQuery = '';
    vm.studentSuggestions = [];
    vm.studentId = null;
    vm.selectedStudent = null;

    vm.onStudentQueryChange = onStudentQueryChange;
    vm.selectStudent = selectStudent;
    vm.clearStudent = clearStudent;

    // Terms for selected student
    vm.terms = []; // [{ syid, label }]
    vm.selectedTermIds = []; // default: all

    // History of generated transcripts for selected student
    vm.history = [];

    // Form fields (defaults)
    vm.form = {
      date_issued: nowLocalDatetime(),
      remarks: '',
      prepared_by: '',
      verified_by: '',
      registrar_signatory: '',
      signatory: '',
      type: 'transcript' // 'transcript' | 'copy'
    };

    vm.toggleAllTerms = toggleAllTerms;
    vm.generate = generate;
    vm.reprint = reprint;
    vm.createBilling = createBilling;

    // -------------- Functions --------------

    function nowLocalDatetime() {
      try {
        var d = new Date();
        d.setSeconds(0, 0);
        var yyyy = d.getFullYear();
        var mm = ('0' + (d.getMonth() + 1)).slice(-2);
        var dd = ('0' + d.getDate()).slice(-2);
        var hh = ('0' + d.getHours()).slice(-2);
        var min = ('0' + d.getMinutes()).slice(-2);
        // HTML datetime-local friendly
        var s = yyyy + '-' + mm + '-' + dd + 'T' + hh + ':' + min;
        // Fix for AngularJS date input format: remove seconds if present
        if (s.length > 16) s = s.substring(0, 16);
        return s;
      } catch (e) {
        return '';
      }
    }

    function onStudentQueryChange() {
      var q = (vm.studentQuery || '').trim();
      if (!q) {
        vm.studentSuggestions = [];
        return;
      }
      vm.loading.students = true;
      vm.error.students = null;
      StudentsService.listSuggestions(q)
        .then(function (rows) {
          vm.studentSuggestions = Array.isArray(rows) ? rows.slice(0, 20) : [];
        })
        .catch(function () {
          vm.error.students = 'Failed to load suggestions.';
          vm.studentSuggestions = [];
        })
        .finally(function () {
          vm.loading.students = false;
        });
    }

    function selectStudent(item) {
      if (!item) return;
      vm.studentId = item.id;
      vm.selectedStudent = item;
      vm.studentQuery = (item.student_number ? (item.student_number + ' — ') : '') +
                        (item.last_name || '') + ', ' + (item.first_name || '');
      vm.studentSuggestions = [];
      loadTerms(item.id);
      loadHistory(item.id);
    }

    function clearStudent() {
      vm.studentId = null;
      vm.selectedStudent = null;
      vm.terms = [];
      vm.selectedTermIds = [];
      vm.studentQuery = '';
      vm.studentSuggestions = [];
    }

    function loadTerms(studentId) {
      vm.loading.terms = true;
      vm.error.terms = null;
      // Use /student/records to fetch all terms with records
      var url = APP_CONFIG.API_BASE + '/student/records';
      var body = { student_id: studentId, include_grades: true };
      return $http.post(url, body, _adminHeaders())
        .then(function (resp) {
          var data = (resp && resp.data) ? (resp.data.data || resp.data) : null;
          var terms = [];
          // data may be { terms: [...] } or flat records
          if (data && angular.isArray(data.terms)) {
            terms = data.terms.map(function (t) {
              var label = t.label || t.term || '';
              return { syid: t.syid, label: label };
            }).filter(function (t) { return t.syid != null; });
          } else if (data && angular.isArray(data.records)) {
            // group by syid
            var map = {};
            data.records.forEach(function (r) {
              var syid = r.syid != null ? r.syid : null;
              if (syid == null) return;
              if (!map[syid]) {
                var ys = r.strYearStart || null;
                var ye = r.strYearEnd || null;
                var sem = (r.enumSem != null ? r.enumSem : (r.term || r.label || ''));
                var friendly = (sem ? (sem + ' ') : '') + (ys && ye ? (ys + '-' + ye) : '');
                map[syid] = { syid: syid, label: friendly || ('' + syid) };
              }
            });
            terms = Object.keys(map).map(function (k) { return map[k]; });
          }
          // Sort by syid ascending
          terms.sort(function (a, b) { return (a.syid || 0) - (b.syid || 0); });
          vm.terms = terms;
          // Default select all (per requirement)
          vm.selectedTermIds = terms.map(function (t) { return t.syid; });
        })
        .catch(function () {
          vm.error.terms = 'Failed to load student terms.';
          vm.terms = [];
          vm.selectedTermIds = [];
        })
        .finally(function () {
          vm.loading.terms = false;
        });
    }


    function loadHistory(studentId) {
      vm.loading.history = true;
      vm.error.history = null;
      // Pass global term so backend can compute has_billing for the selected term
      return ReportsService.listTranscriptRequests(studentId, vm.globalTermId)
        .then(function (items) {
          vm.history = Array.isArray(items) ? items : [];
        })
        .catch(function () {
          vm.error.history = 'Failed to load history.';
          vm.history = [];
        })
        .finally(function () {
          vm.loading.history = false;
        });
    }

    function reprint(item) {
      if (!item || !vm.studentId) return;
      return ReportsService.reprintTranscript(vm.studentId, item.id)
        .then(function (resp) {
          var data = resp && resp.data ? resp.data : null;
          if (!data || !(data instanceof ArrayBuffer)) {
            vm.error.generate = 'Unexpected response.';
            return;
          }
          var blob = new Blob([data], { type: 'application/pdf' });
          var url = URL.createObjectURL(blob);
          window.open(url, '_blank');
        })
        .catch(function () {
          vm.error.generate = 'Failed to reprint PDF.';
        });
    }

    function createBilling(item) {
      if (!item || !vm.studentId) return;
      item._billingLoading = true;
      vm.error.history = null;
      // Use global term id as the billing syid target
      return ReportsService.createTranscriptBilling(vm.studentId, item.id, vm.globalTermId)
        .then(function () {
          // Refresh history to update UI flags
          return loadHistory(vm.studentId);
        })
        .catch(function () {
          vm.error.history = 'Failed to create billing.';
        })
        .finally(function () {
          item._billingLoading = false;
        });
    }

    function toggleAllTerms() {
      if (!vm.terms || !vm.terms.length) return;
      if (vm.selectedTermIds.length === vm.terms.length) {
        vm.selectedTermIds = [];
      } else {
        vm.selectedTermIds = vm.terms.map(function (t) { return t.syid; });
      }
    }

    function normalizeDateIssued(val) {
      // Accept datetime-local string and convert to "YYYY-mm-dd HH:ii:ss"
      if (!val) return '';
      try {
        var s = ('' + val).replace('T', ' ');
        // If already contains seconds, keep; else append :00
        if (!/:..$/.test(s)) s = s + ':00';
        return s;
      } catch (e) { return '';}
    }

    function generate() {
      if (!vm.studentId) {
        vm.error.generate = 'Select a student.';
        return;
      }
      if (!vm.selectedTermIds || !vm.selectedTermIds.length) {
        vm.error.generate = 'Select at least one term.';
        return;
      }

      vm.loading.generate = true;
      vm.error.generate = null;

      var payload = {
        date_issued: normalizeDateIssued(vm.form.date_issued),
        remarks: (vm.form.remarks || ''),
        prepared_by: (vm.form.prepared_by || ''),
        verified_by: (vm.form.verified_by || ''),
        registrar_signatory: (vm.form.registrar_signatory || ''),
        signatory: (vm.form.signatory || ''),
        type: (vm.form.type || 'transcript'),
        term_ids: vm.selectedTermIds.slice(),
        // Ensure billing is created for the globally selected term (not the first selected term)
        billing_term_id: vm.globalTermId
      };

      var type = (vm.form.type || 'transcript');
      var firstTerm = (vm.selectedTermIds && vm.selectedTermIds.length) ? vm.selectedTermIds[0] : null;
      // Use globally selected term for fee resolution (campus-aware), fallback to first included term
      var feeTerm = (vm.globalTermId !== null && vm.globalTermId !== undefined && vm.globalTermId !== '')
        ? vm.globalTermId
        : firstTerm;

      return ReportsService.transcriptFee(vm.studentId, type, feeTerm)
        .then(function (info) {
          var desc = (info && info.description) ? info.description : (type === 'copy' ? 'Copy of Grades' : 'Transcript of Records');
          var amount = info ? info.amount : null;
          var msg = (amount != null)
            ? ('This action will bill the student for "' + desc + '" in the amount of ₱' + Number(amount).toFixed(2) + '. Proceed?')
            : ('Amount not configured; proceed anyway?');

          if (!window.confirm(msg)) {
            // Treat as cancelled without error
            throw new Error('cancelled');
          }
          return ReportsService.generateStudentTranscript(vm.studentId, payload);
        })
        .then(function (resp) {
          // If previous step was cancelled, this is skipped
          if (!resp) return;
          var data = resp && resp.data ? resp.data : null;
          if (!data || !(data instanceof ArrayBuffer)) {
            vm.error.generate = 'Unexpected response.';
            return;
          }
          var blob = new Blob([data], { type: 'application/pdf' });
          var url = URL.createObjectURL(blob);
          window.open(url, '_blank');
          // Refresh history after successful generation
          loadHistory(vm.studentId);
        })
        .catch(function (e) {
          if (e && e.message === 'cancelled') {
            // No-op on user cancel
            vm.error.generate = null;
            return;
          }
          vm.error.generate = 'Failed to generate PDF.';
        })
        .finally(function () {
          vm.loading.generate = false;
        });
    }

    // -------------- Helpers --------------
    function syncGlobalTerm() {
      try {
        var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
        if (sel && (sel.intID || sel.id)) {
          vm.globalTermId = Number(sel.intID || sel.id);
        } else {
          // Fallback to active term (async) if available
          var p = TermService && TermService.getActiveTerm ? TermService.getActiveTerm() : null;
          if (p && typeof p.then === 'function') {
            p.then(function (t) {
              if (t && (t.intID || t.id)) {
                vm.globalTermId = Number(t.intID || t.id);
              }
            }).catch(function () {});
          }
        }
      } catch (e) {}
    }
  }
})();
