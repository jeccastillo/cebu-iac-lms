(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('StudentChangeProgramRequestController', StudentChangeProgramRequestController);

  StudentChangeProgramRequestController.$inject = [
    '$scope',
    '$http',
    '$q',
    'APP_CONFIG',
    'StorageService',
    'TermService',
    'ProgramsService',
    'StudentFinancesService',
    'ToastService'
  ];
  function StudentChangeProgramRequestController(
    $scope,
    $http,
    $q,
    APP_CONFIG,
    StorageService,
    TermService,
    ProgramsService,
    StudentFinancesService,
    ToastService
  ) {
    var vm = this;
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

    // Page meta
    vm.title = 'Request Program Change';

    // Auth state
    vm.state = StorageService.getJSON('loginState');

    // UI/loading/error states
    vm.loading = {
      init: false,
      programs: false,
      submit: false
    };
    vm.error = {
      init: null,
      programs: null,
      submit: null
    };

    // Data/state
    vm.term = null;                // { id, label }
    vm.profile = null;             // { student_id, student_number, ... }
    vm.programs = [];              // list of { id, code, name }
    vm.form = {
      program_to: null,            // number
      reason: ''                   // string
    };
    vm.submitted = false;          // lock UI after successful submit
    vm.programInfoResolved = false; // marks completion of current program resolution

    // Methods
    vm.reloadPrograms = reloadPrograms;
    vm.onSubmit = onSubmit;
    vm.canSubmit = canSubmit;
    vm.programLabel = function (p) {
      if (!p) return '';
      var label = p.name || p.title || '';
      var prefix = p.code ? ('[' + p.code + '] ') : '';
      if (!label && p.id != null) {
        label = 'Program #' + p.id;
      }
      return prefix + label;
    };
    vm.currentProgramLabel = function () {
      var parts = [];
      if (vm.currentProgramCode) parts.push(vm.currentProgramCode);
      if (vm.currentProgramName) parts.push(vm.currentProgramName);
      if (!parts.length) return 'Unknown';
      return parts.join(' — ');
    };

    // Init
    init();

    function init() {
      vm.loading.init = true;
      vm.error.init = null;

      // Initialize global term, resolve selected or fallback to active
      TermService.init()
        .then(function () {
          var t = TermService.getSelectedTerm();
          if (t && t.intID) {
            vm.term = { id: t.intID, label: t.label || null };
          }
        })
        .then(function () {
          if (!vm.term || !vm.term.id) {
            // Fallback to backend active term
            return $http.get(BASE + '/generic/active-term').then(function (resp) {
              var body = (resp && resp.data) ? resp.data : resp;
              var data = (body && body.data) ? body.data : body;
              if (data && data.id) {
                vm.term = { id: data.id, label: data.label || null };
              }
            });
          }
        })
        .then(function () {
          // Resolve student profile (id, number, name, email) using existing service
          return StudentFinancesService.resolveProfile()
            .then(function (p) {
              vm.profile = p || null;

              function _assignFromStudentShow(data) {
                vm.currentProgramId = data.program_id || null;
                vm.currentProgramCode = data.program || null;
                vm.currentProgramName = data.program_description || null;
                vm.studentLevel = data.student_level || null;
                vm.studentLevelNormalized = (vm.studentLevel || '').toLowerCase();
                if (vm.studentLevelNormalized !== 'college' && vm.studentLevelNormalized !== 'shs') {
                  vm.studentLevelNormalized = null;
                }
                vm.programInfoResolved = true;
              }

              function _clearProgramInfo() {
                vm.currentProgramId = null;
                vm.currentProgramCode = null;
                vm.currentProgramName = null;
                vm.studentLevel = null;
                vm.studentLevelNormalized = null;
                vm.programInfoResolved = true;
              }

              function _fetchStudentShowById(id) {
                if (!id) { _clearProgramInfo(); return $q.when(); }
                return $http.get(BASE + '/students/' + id)
                  .then(function (resp) {
                    var data = resp && resp.data ? resp.data.data : null;
                    if (data) _assignFromStudentShow(data); else _clearProgramInfo();
                  })
                  .catch(function () { _clearProgramInfo(); });
              }

              function _fallbackByStudentNumber() {
                // Try resolving via list endpoint using student_number
                var sn = vm.profile && vm.profile.student_number ? ('' + vm.profile.student_number).trim() : '';
                if (!sn) { _clearProgramInfo(); return $q.when(); }
                return $http.get(BASE + '/students', { params: { student_number: sn, per_page: 1, page: 1 } })
                  .then(function (resp) {
                    var body = resp && resp.data ? resp.data : resp;
                    var rows = body && body.data ? body.data : (Array.isArray(body) ? body : []);
                    var row = (Array.isArray(rows) && rows.length > 0) ? rows[0] : null;
                    if (row) {
                      _assignFromStudentShow({
                        program_id: row.program_id != null ? row.program_id : null,
                        program: row.program || null,
                        program_description: row.program_description || null,
                        student_level: row.student_level || row.level || null
                      });
                      // Set numeric profile id when available
                      if (row.id != null && (vm.profile && vm.profile.student_id == null)) {
                        try { vm.profile.student_id = parseInt(row.id, 10); } catch (e) { vm.profile.student_id = row.id; }
                      }
                    } else {
                      _clearProgramInfo();
                    }
                  })
                  .catch(function () { _clearProgramInfo(); });
              }

              // Primary path: use student_id from viewer profile
              if (vm.profile && vm.profile.student_id) {
                return _fetchStudentShowById(vm.profile.student_id);
              }

              // Fallback path: resolve id via POST /student/applicant using token or student_number
              var token = null;
              try { token = (vm.state && vm.state.username) ? vm.state.username : null; } catch (e) {}
              if (!token && vm.profile && vm.profile.student_number) token = vm.profile.student_number;

              if (token) {
                return $http.post(BASE + '/student/applicant', {
                  token: token,
                  student_number: (vm.profile && vm.profile.student_number) ? vm.profile.student_number : null
                }).then(function (resp) {
                  var body = resp && resp.data ? resp.data : resp;
                  if (body && body.success === false) {
                    // Try fallback by student number
                    return _fallbackByStudentNumber();
                  }
                  var d = body && body.data ? body.data : body;
                  var user = d && d.user ? d.user : null;
                  var sid = null;
                  if (user) {
                    sid = (user.intID != null) ? user.intID : ((user.id != null) ? user.id : null);
                  }
                  if (sid != null) {
                    try { vm.profile.student_id = parseInt(sid, 10); } catch (e) { vm.profile.student_id = sid; }
                    return _fetchStudentShowById(vm.profile.student_id);
                  }
                  // Try fallback by student number when sid not found
                  return _fallbackByStudentNumber();
                }).catch(function () {
                  // Applicant endpoint failed; try fallback by student number
                  return _fallbackByStudentNumber();
                });
              }

              // No token; try fallback by student number, else clear
              return _fallbackByStudentNumber();
            })
            .catch(function () {
              // Graceful fallback when viewer returns { success:false } or similar
              var state = StorageService.getJSON('loginState') || {};
              vm.profile = {
                student_id: null,
                student_number: state.student_number || state.username || null,
                first_name: state.first_name || null,
                last_name: state.last_name || null,
                email: state.username || null,
                contact_number: state.contact_number || null
              };
              // Attempt fallback resolution using applicant endpoint even if resolveProfile failed
              var token = state && state.username ? state.username : (vm.profile && vm.profile.student_number ? vm.profile.student_number : null);
              if (token) {
                return $http.post(BASE + '/student/applicant', { token: token, student_number: vm.profile.student_number || null })
                  .then(function (resp) {
                    var body = resp && resp.data ? resp.data : resp;
                    if (body && body.success === false) return _fallbackByStudentNumber();
                    var d = body && body.data ? body.data : body;
                    var user = d && d.user ? d.user : null;
                    var sid = null;
                    if (user) {
                      sid = (user.intID != null) ? user.intID : ((user.id != null) ? user.id : null);
                    }
                    if (sid != null) {
                      try { vm.profile.student_id = parseInt(sid, 10); } catch (e) { vm.profile.student_id = sid; }
                      return $http.get(BASE + '/students/' + vm.profile.student_id)
                        .then(function (resp2) {
                          var data = resp2 && resp2.data ? resp2.data.data : null;
                          if (data) {
                            _assignFromStudentShow(data);
                          } else {
                            return _fallbackByStudentNumber();
                          }
                        });
                    }
                    return _fallbackByStudentNumber();
                  })
                  .catch(function () {
                    return _fallbackByStudentNumber();
                  });
              } else {
                return _fallbackByStudentNumber();
              }
            });
        })
        .then(function () {
          return reloadPrograms();
        })
        .catch(function (e) {
          console.error('Init error:', e);
          vm.error.init = 'Failed to initialize page.';
        })
        .finally(function () {
          vm.loading.init = false;
        });

      // Respond to global term changes
      $scope.$on('termChanged', function () {
        var t = TermService.getSelectedTerm();
        if (t && t.intID) {
          vm.term = { id: t.intID, label: t.label || null };
        }
      });
    }

    function reloadPrograms() {
      vm.loading.programs = true;
      vm.error.programs = null;
      vm.programs = [];

      // Determine filter type based on student level
      var filterType = vm.studentLevelNormalized || null;

      // Some datasets have enumEnabled unset/0 for legacy rows; include all to avoid blank list
      return ProgramsService.list({ enabledOnly: false, type: filterType })
        .then(function (resp) {
          // ProgramsService.list returns resp.data (controller agnostic). Normalize shape.
          var rows = [];
          var data = resp && resp.data ? resp.data : (Array.isArray(resp) ? resp : []);
          var arr = Array.isArray(data) ? data : (Array.isArray(resp) ? resp : []);
          rows = (arr || []).map(function (r) {
            var id = r.intProgramID != null ? r.intProgramID : (r.id != null ? r.id : null);
            var code = r.strProgramCode || r.code || '';
            var name = r.strProgramDescription || r.title || r.name || r.description || '';
            return { id: id, code: code, name: name, title: r.title || null };
          }).filter(function (x) {
            return x.id != null && x.id !== vm.currentProgramId;
          });

          vm.programs = rows;
        })
        .catch(function (e) {
          console.error('Programs load error:', e);
          vm.error.programs = 'Failed to load programs.';
          vm.programs = [];
        })
        .finally(function () {
          vm.loading.programs = false;
        });
    }

    function canSubmit() {
      if (vm.submitted) return false;
      var hasIdentity = !!(vm.profile && (vm.profile.student_id || (vm.state && vm.state.username)));
      if (!hasIdentity) return false;
      if (!vm.term || !vm.term.id) return false;
      if (!vm.form || !vm.form.program_to) return false;
      // Prevent submitting the same program if somehow selected
      if (vm.currentProgramId != null && ('' + vm.form.program_to) === ('' + vm.currentProgramId)) return false;
      return true;
    }

    function onSubmit() {
      if (!canSubmit()) return;

      vm.loading.submit = true;
      vm.error.submit = null;

      var payload = {
        // Provide both when available: backend resolves token OR student_id
        student_id: (vm.profile && vm.profile.student_id) ? vm.profile.student_id : null,
        token: (vm.state && vm.state.username) ? vm.state.username : null,
        term: vm.term.id,
        program_to: vm.form.program_to,
        reason: vm.form.reason || ''
      };

      var headers = {};
      try {
        // Include X-Term-ID for parity with backend term resolvers
        if (vm.term && vm.term.id) headers['X-Term-ID'] = vm.term.id;
        // Include X-Faculty-ID when available (kept consistent with other student-safe endpoints)
        var state = StorageService.getJSON('loginState');
        if (state && state.faculty_id != null) headers['X-Faculty-ID'] = state.faculty_id;
      } catch (e) {}

      return $http.post(BASE + '/student/shift-requests', payload, { headers: headers })
        .then(function (resp) {
          var code = (resp && resp.status) ? resp.status : 200;
          if (code === 201 || (resp && resp.data && resp.data.success)) {
            vm.submitted = true;
            if (ToastService && typeof ToastService.success === 'function') {
              ToastService.success('Your program change request has been submitted.');
            } else {
              try { alert('Your program change request has been submitted.'); } catch (_e) {}
            }
          } else {
            throw new Error('Unexpected response.');
          }
        })
        .catch(function (err) {
          var msg = (err && err.data && (err.data.message || err.data.error)) || '';
          if ((err && err.status) === 409) {
            msg = msg || 'A request already exists for this term.';
          } else if ((err && err.status) === 422) {
            msg = msg || 'Invalid input. Please check your selections.';
          } else {
            msg = msg || 'Failed to submit request.';
          }
          vm.error.submit = msg;
        })
        .finally(function () {
          vm.loading.submit = false;
        });
    }
  }
})();
