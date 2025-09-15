(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ShiftingController', ShiftingController);

  ShiftingController.$inject = [
    '$http',
    'APP_CONFIG',
    'UnityService',
    'ProgramsService',
    'CurriculaService',
    'StudentsService',
    'ChecklistService',
    'TermService',
    '$location',
    '$scope',
    'StorageService'
  ];

  function ShiftingController(
    $http,
    APP_CONFIG,
    UnityService,
    ProgramsService,
    CurriculaService,
    StudentsService,
    ChecklistService,
    TermService,
    $location,
    $scope,
    StorageService
  ) {
    var vm = this;
    var BASE = APP_CONFIG.API_BASE;

    // State
    vm.loading = false;
    vm.regLoading = false;
    vm.saving = false;
    vm.reqSaving = false;

    // Term
    vm.selectedTerm = null;
    vm.term = '';

    // Student selection
    vm.students = [];
    vm.studentSearch = '';
    vm.studentNumber = '';
    vm.student_id = null;
    vm.selectedStudentName = '';

    // Options
    vm.programs = [];
    vm.curricula = [];

    // Registration snapshot
    vm.registration = null;
    vm.shiftRequest = null;
    vm.pendingProgramId = null; // defer program selection until programs list is loaded

    // Form selections
    vm.form = {
      program_id: '',
      curriculum_id: ''
    };

    // Methods
    vm.activate = activate;
    vm.loadPrograms = loadPrograms;
    vm.loadCurricula = loadCurricula;
    vm.onProgramChange = onProgramChange;
    vm.onStudentQuery = onStudentQuery;
    vm.onStudentSelected = onStudentSelected;
    vm.resolveStudentIdIfNeeded = resolveStudentIdIfNeeded;
    vm.loadRegistration = loadRegistration;
    vm.canSave = canSave;
    vm.saveShift = saveShift;
    vm.programLabel = programLabel;
    vm.curriculumLabel = curriculumLabel;
    vm.findProgramById = findProgramById;
    vm.sanitizeStudentNumber = sanitizeStudentNumber;
    vm.loadShifteeCandidates = loadShifteeCandidates;
    vm.loadShiftRequest = loadShiftRequest;
    vm.programShort = programShort;
    vm.setShiftRequestStatus = setShiftRequestStatus;
    vm.rejectShiftRequest = function () { return setShiftRequestStatus('rejected'); };
    vm.cancelShiftRequest = function () { return setShiftRequestStatus('cancelled'); };

    // Initialize
    activate();

    function activate() {
      // Initialize global term
      vm.loading = true;
      var initP = TermService && TermService.init ? TermService.init() : Promise.resolve();
      Promise.resolve(initP).then(function () {
        try {
          var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
          vm.selectedTerm = sel || null;
          vm.term = (sel && sel.intID) ? sel.intID : '';
        } catch (e) {
          vm.selectedTerm = null;
          vm.term = '';
        }
      }).finally(function () {
        vm.loading = false;
        loadPrograms();
        try { loadShifteeCandidates(); } catch (e) {}
        // Listen for term changes
        if ($scope && $scope.$on) {
          $scope.$on('termChanged', function (event, data) {
            if (data && data.selectedTerm) {
              vm.selectedTerm = data.selectedTerm;
              vm.term = data.selectedTerm && data.selectedTerm.intID ? data.selectedTerm.intID : vm.term;
              // Refresh registration context on term change
              try { loadRegistration(); } catch (e) {}
              try { loadShifteeCandidates(); } catch (e) {}
              try { vm.loadShiftRequest(); } catch (e) {}
            }
          });
        }
      });

      // Optional bootstrap from query string (?student_id=ID)
      try {
        if ($location && $location.search) {
          var qs = $location.search() || {};
          var sid = parseInt(qs.student_id || qs.sid || qs.id, 10);
          if (!isNaN(sid) && sid > 0) {
            vm.loading = true;
            $http.get(BASE + '/students/' + sid).then(function (res) {
              var data = (res && res.data) ? res.data : res;
              var row = (data && data.data) ? data.data : data;
              if (!row) return;
              var sn = row.student_number || row.strStudentNumber || '';
              if (!sn) return;

              vm.student_id = (row.id != null) ? row.id : ((row.intID != null) ? row.intID : sid);
              vm.studentNumber = sn;

              // Selected name display
              try {
                var ln = row.last_name || row.strLastname || '';
                var fn = row.first_name || row.strFirstname || '';
                var mn = row.middle_name || row.strMiddlename || '';
                var full = (ln ? (ln + ', ') : '') + (fn || '') + (mn ? (' ' + mn) : '');
                vm.selectedStudentName = (full || '').trim() || sn;
              } catch (e) {
                setSelectedStudentNameFallback();
              }

              // Pull registration if term is present
              if (vm.term) {
                loadRegistration();
              }
            }).finally(function () {
              vm.loading = false;
              if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
            });
          }
        }
      } catch (e) {}
    }

    function loadPrograms() {
      try {
        ProgramsService.list({ enabledOnly: false }).then(function (res) {
          var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          // Normalize similar to EnlistmentController, and COERCE ids to integers to avoid unknown option "?"
          vm.programs = (rows || []).map(function (p) {
            var rawId = (p.intProgramID != null ? p.intProgramID : p.id);
            var id = parseInt(rawId, 10);
            if (isNaN(id)) id = null;
            var code = p.strProgramCode || p.code || '';
            var desc = p.strProgramDescription || p.title || p.description || '';
            return Object.assign({}, p, {
              intProgramID: id,
              strProgramCode: code,
              strProgramDescription: desc
            });
          }).filter(function (p) { return p.intProgramID !== null; });
          // Reconcile current selection type (ensure vm.form.program_id is integer)
          try {
            if (vm.form && vm.form.program_id != null && ('' + vm.form.program_id).trim() !== '') {
              var pid = parseInt(vm.form.program_id, 10);
              if (!isNaN(pid)) vm.form.program_id = pid;
            }
          } catch (e) {}
          // If a program was requested before the list loaded, apply it now
          try {
            if (vm.pendingProgramId != null) {
              var pend = parseInt(vm.pendingProgramId, 10);
              if (!isNaN(pend) && pend > 0) {
                vm.form.program_id = pend;
                loadCurricula(pend);
              }
              vm.pendingProgramId = null;
            }
          } catch (e) {}
        });
      } catch (e) {
        vm.programs = [];
      }
    }

    function loadCurricula(programId) {
      var pid = (programId != null && programId !== '') ? parseInt(programId, 10) : null;
      var opts = {};
      if (pid && !isNaN(pid)) {
        opts.program_id = pid;
      }
      try {
        CurriculaService.list(opts).then(function (res) {
          vm.curricula = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
        }).catch(function () {
          vm.curricula = [];
        });
      } catch (e) {
        vm.curricula = [];
      }
    }

    // Load student candidates with unprocessed shift requests (scoped by term)
    function loadShifteeCandidates() {
      try {
        vm.students = [];
        var t = parseInt(vm.term, 10);
        if (!isFinite(t) || t <= 0) { return Promise.resolve([]); }
        var per = 100;
        var page = 1;
        var acc = [];
        function nextPage() {
          return StudentsService.listPage({ per_page: per, page: page, has_shift_request: 1, unprocessed_only: 1, syid: t })
            .then(function (rows) {
              rows = rows || [];
              acc = acc.concat(rows);
              if (rows.length === per) {
                page += 1;
                return nextPage();
              } else {
                vm.students = acc;
                try { if (vm.studentNumber) { setSelectedStudentName(); } } catch (e) {}
                return acc;
              }
            })
            .catch(function () {
              vm.students = acc;
              return acc;
            });
        }
        return nextPage();
      } catch (e) {
        vm.students = vm.students || [];
        return Promise.resolve(vm.students);
      }
    }

    function onProgramChange() {
      try {
        // Reset curriculum selection when program changes
        vm.form.curriculum_id = '';
        var pid = vm.form.program_id;
        if (pid != null && ('' + pid).trim() !== '') {
          loadCurricula(pid);
        } else {
          vm.curricula = [];
        }
      } catch (e) {}
    }

    function onStudentQuery(q) {
      try {
        var s = (q != null) ? ('' + q).trim() : '';
        if (!s) { vm.students = []; return; }
        var opts = { q: s, per_page: 20, page: 1, has_shift_request: 1, unprocessed_only: 1 };
        // Scope to selected term when available
        try {
          var t = parseInt(vm.term, 10);
          if (isFinite(t) && t > 0) { opts.syid = t; }
        } catch (e) {}
        StudentsService.listPage(opts).then(function (rows) {
          vm.students = rows || [];
          try { if (vm.studentNumber) { setSelectedStudentName(); } } catch (e) {}
        }).catch(function () {
          vm.students = [];
        });
      } catch (e) {
        vm.students = [];
      }
    }

    function onStudentSelected() {
      if (vm.studentNumber) {
        setSelectedStudentName();
        // Clear cached id so we resolve fresh for this studentNumber
        vm.student_id = null;
        vm.registration = null;
        // Resolve student id then load registration and shift request
        resolveStudentIdIfNeeded().then(function () {
          return loadRegistration();
        }).then(function () {
          return vm.loadShiftRequest();
        });
      } else {
        vm.selectedStudentName = '';
        vm.registration = null;
        vm.student_id = null;
      }
    }

    function setSelectedStudentName() {
      try {
        var sel = (vm.students || []).find(function (s) { return s && s.student_number === vm.studentNumber; });
        if (sel) {
          var full = (sel.last_name || '') + ', ' + (sel.first_name || '') + (sel.middle_name ? (' ' + sel.middle_name) : '');
          vm.selectedStudentName = (full + ' (' + (sel.student_number || '') + ')').trim();
        } else {
          vm.selectedStudentName = vm.studentNumber || '';
        }
      } catch (e) {
        vm.selectedStudentName = vm.studentNumber || '';
      }
    }

    function setSelectedStudentNameFallback() {
      try {
        vm.selectedStudentName = vm.studentNumber || '';
      } catch (e) {
        vm.selectedStudentName = vm.studentNumber || '';
      }
    }

    function resolveStudentIdIfNeeded() {
      return new Promise(function (resolve) {
        try {
          var existing = parseInt(vm.student_id, 10);
          if (!isNaN(existing) && existing > 0) { resolve(existing); return; }
          if (!vm.studentNumber) { resolve(null); return; }
          // Try local list first
          var sel = (vm.students || []).find(function (s) { return s && s.student_number === vm.studentNumber; });
          if (sel && sel.id) {
            vm.student_id = sel.id;
            resolve(sel.id);
            return;
          }
          // Fallback: exact student_number filter
          $http.get(APP_CONFIG.API_BASE + '/students', { params: { per_page: 1, page: 1, student_number: vm.studentNumber } })
            .then(function (resp) {
              var data = (resp && resp.data) ? resp.data : {};
              var rows = data && data.data ? data.data : (Array.isArray(data) ? data : []);
              if (rows && rows.length) {
                var row = rows[0];
                var id1 = row && (row.id != null ? row.id : (row.intID != null ? row.intID : null));
                if (id1 != null) vm.student_id = id1;
                resolve(id1 || null);
                return;
              }
              resolve(null);
            })
            .catch(function () { resolve(null); });
        } catch (e) {
          resolve(null);
        }
      });
    }

    function loadRegistration() {
      if (!vm.studentNumber || !vm.term) {
        vm.registration = null;
        return;
      }
      vm.regLoading = true;
      var termInt = parseInt(vm.term, 10);
      var sn = sanitizeStudentNumber(vm.studentNumber);
      var getRegPromise = (vm.student_id && !isNaN(parseInt(vm.student_id, 10)))
        ? UnityService.getRegistrationById(parseInt(vm.student_id, 10), termInt)
        : UnityService.getRegistration(sn, termInt);

      return getRegPromise.then(function (res) {
        // Expect shape: { success, data: { exists, registration } }
        var exists = res && res.data && res.data.exists;
        var row = res && res.data ? res.data.registration : null;
        vm.registration = exists ? row : null;

        // Pre-fill form defaults based on registration
        if (vm.registration) {
          var cp = (vm.registration.current_program !== undefined && vm.registration.current_program !== null)
            ? vm.registration.current_program : '';
          // Coerce to integer to match ng-options integer values
          if (cp !== '') {
            var cpInt = parseInt(cp, 10);
            vm.form.program_id = !isNaN(cpInt) ? cpInt : cp;
          } else {
            vm.form.program_id = '';
          }
          if (vm.form.program_id) {
            loadCurricula(vm.form.program_id);
          } else {
            vm.curricula = [];
          }
          var cc = (vm.registration.current_curriculum !== undefined && vm.registration.current_curriculum !== null)
            ? vm.registration.current_curriculum : '';
          vm.form.curriculum_id = cc !== '' ? cc : '';
        } else {
          vm.form.program_id = '';
          vm.form.curriculum_id = '';
          vm.curricula = [];
        }
      }).catch(function () {
        vm.registration = null;
      }).finally(function () {
        vm.regLoading = false;
        if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
      });
    }

    function canSave() {
      try {
        var termOk = !!vm.term && !isNaN(parseInt(vm.term, 10));
        var hasStudent = !!vm.studentNumber;
        var p = parseInt(vm.form.program_id, 10);
        var c = parseInt(vm.form.curriculum_id, 10);
        return termOk && hasStudent && isFinite(p) && p > 0 && isFinite(c) && c > 0;
      } catch (e) {
        return false;
      }
    }

    function saveShift() {
      try {
        if (!canSave()) {
          var msg = 'Select a student, term, program and curriculum.';
          if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Incomplete', text: msg });
          } else {
            try { alert(msg); } catch (e) {}
          }
          return;
        }

        var pid = parseInt(vm.form.program_id, 10);
        var cid = parseInt(vm.form.curriculum_id, 10);
        var termInt = parseInt(vm.term, 10);
        var sn = sanitizeStudentNumber(vm.studentNumber);

        // If a registration exists for the term: reset registration first (password-confirm), then shift base Program/Curriculum.
        if (vm.registration) {
          if (window.Swal) {
            Swal.fire({
              title: 'Reset registration before shifting?',
              text: 'This will delete all enlisted classes and the registration for the selected term before applying the program/curriculum change.',
              input: 'password',
              inputLabel: 'Enter your password to confirm',
              inputAttributes: { autocapitalize: 'off', autocomplete: 'new-password' },
              showCancelButton: true,
              confirmButtonText: 'Confirm',
              cancelButtonText: 'Cancel',
              preConfirm: function (value) {
                if (!value) {
                  Swal.showValidationMessage('Password is required');
                }
                return value;
              }
            }).then(function (result) {
              if (!result.isConfirmed) { return; }
              var pw = result.value;
              vm.saving = true;

              UnityService.resetRegistration({ student_number: sn, term: termInt, password: pw })
                .then(function () {
                  return doShift(pid, cid);
                })
                .then(function () {
                  // After reset+shift, registration likely no longer exists; reload to reflect state
                  return loadRegistration();
                })
                .then(function () {
                  // Auto-generate checklist for the new curriculum; do not block overall success on failure
                  return autoGenerateChecklist(cid).catch(function (e) {
                    try { console.error('Auto checklist generation failed', e); } catch (_e) {}
                    return null;
                  });
                })
                .then(function () { return vm.loadShiftRequest(); })
                .then(function () {
                  if (window.Swal) {
                    Swal.fire({ icon: 'success', title: 'Shifted', text: 'Registration was reset and base Program/Curriculum updated. Checklist generated.' });
                  } else {
                    try { alert('Shift successful.'); } catch (e) {}
                  }
                })
                .catch(function (err) {
                  var m = (err && err.data && err.data.message) || (err && err.message) || 'Shift failed';
                  if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Error', text: m });
                  } else {
                    try { alert(m); } catch (e) {}
                  }
                })
                .finally(function () {
                  vm.saving = false;
                  if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
                });
            });
          } else {
            // Fallback prompt
            var pw = null;
            try {
              pw = window.prompt('Reset registration will delete enlisted classes and the registration for the selected term.\nTo confirm, enter your password:', '');
            } catch (e) { pw = ''; }
            if (pw === null || pw === '') return;

            vm.saving = true;
            UnityService.resetRegistration({ student_number: sn, term: termInt, password: pw })
              .then(function () { return doShift(pid, cid); })
              .then(function () { return loadRegistration(); })
              .then(function () { try { alert('Shift successful.'); } catch (e) {} })
              .catch(function (err) {
                var m = (err && err.data && err.data.message) || (err && err.message) || 'Shift failed';
                try { alert(m); } catch (e) {}
              })
              .finally(function () {
                vm.saving = false;
                if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
              });
          }
        } else {
          // No registration for term: directly shift base Program/Curriculum
          vm.saving = true;
          doShift(pid, cid)
            .then(function () {
              return loadRegistration();
            })
            .then(function () {
              // Auto-generate checklist for the new curriculum; ignore failures
              return autoGenerateChecklist(cid).catch(function (e) {
                try { console.error('Auto checklist generation failed', e); } catch (_e) {}
                return null;
              });
            })
            .then(function () { return vm.loadShiftRequest(); })
            .then(function () {
              if (window.Swal) {
                Swal.fire({ icon: 'success', title: 'Shifted', text: 'Base Program/Curriculum updated. Checklist generated.' });
              } else {
                try { alert('Shift successful.'); } catch (e) {}
              }
            })
            .catch(function (err) {
              var m = (err && err.data && err.data.message) || (err && err.message) || 'Shift failed';
              if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Error', text: m });
              } else {
                try { alert(m); } catch (e) {}
              }
            })
            .finally(function () {
              vm.saving = false;
              if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
            });
        }
      } catch (e) {
        vm.saving = false;
      }
    }

    // Performs POST /students/{id}/shift with admin headers.
    function doShift(pid, cid) {
      return vm.resolveStudentIdIfNeeded().then(function (sid) {
        if (!sid) {
          return Promise.reject({ message: 'Unable to resolve student id for shifting.' });
        }
        var t = parseInt(vm.term, 10);
        var payload = {
          intProgramID: pid,
          intCurriculumID: cid,
          term: isFinite(t) ? t : null
        };
        return $http.post(BASE + '/students/' + encodeURIComponent(sid) + '/shift', payload, _adminHeaders());
      });
    }

    // Auto-generate graduation checklist from the selected curriculum after shifting
    function autoGenerateChecklist(curriculumId) {
      return vm.resolveStudentIdIfNeeded().then(function (sid) {
        if (!sid) {
          return Promise.reject({ message: 'Unable to resolve student id for checklist generation.' });
        }
        var payload = {};
        var cid = parseInt(curriculumId, 10);
        if (isFinite(cid) && cid > 0) {
          payload.intCurriculumID = cid;
        }
        return ChecklistService.generate(sid, payload);
      });
    }

    // Load shift request for selected student and term
    function loadShiftRequest() {
      try {
        var sid = parseInt(vm.student_id, 10);
        var t = parseInt(vm.term, 10);
        if (!sid || isNaN(sid) || !t || isNaN(t)) {
          vm.shiftRequest = null;
          return Promise.resolve(null);
        }
        var params = { student_id: sid, term: t };
        return $http.get(BASE + '/student/shift-requests', { params: params })
          .then(function (resp) {
            var data = (resp && resp.data) ? resp.data : {};
            var rows = data && data.data ? data.data : (Array.isArray(data) ? data : []);
            vm.shiftRequest = (rows && rows.length) ? rows[0] : null;

            // Auto-select Program based on program_to from the shift request
            try {
              if (vm.shiftRequest && vm.shiftRequest.program_to != null && ('' + vm.shiftRequest.program_to).trim() !== '') {
                var toPid = parseInt(vm.shiftRequest.program_to, 10);
                if (!isNaN(toPid) && toPid > 0) {
                  // If programs are already loaded, set immediately; otherwise defer
                  if (Array.isArray(vm.programs) && vm.programs.length > 0) {
                    vm.form.program_id = toPid;
                    // Reset curriculum when program changes
                    vm.form.curriculum_id = '';
                    // Load curricula for the selected program
                    loadCurricula(toPid);
                  } else {
                    vm.pendingProgramId = toPid;
                  }
                }
              }
            } catch (e) {}

            return vm.shiftRequest;
          })
          .catch(function () { vm.shiftRequest = null; return null; });
      } catch (e) {
        vm.shiftRequest = null;
        return Promise.resolve(null);
      }
    }

    // Update shift request status (rejected|cancelled)
    function setShiftRequestStatus(status) {
      try {
        if (!vm.shiftRequest) {
          return Promise.reject({ message: 'No shift request found for this student and term.' });
        }
        // Only allow from pending
        var current = (vm.shiftRequest.status || 'pending').toLowerCase();
        if (current !== 'pending') {
          return Promise.reject({ message: 'Shift request is already ' + current + '.' });
        }
        var sid = parseInt(vm.student_id, 10);
        var t = parseInt(vm.term, 10);
        if (!sid || isNaN(sid) || !t || isNaN(t)) {
          return Promise.reject({ message: 'Select a student and term first.' });
        }

        var doUpdate = function () {
          vm.reqSaving = true;
          var payload = { student_id: sid, term: t, status: status };
          return $http.patch(BASE + '/student/shift-requests/status', payload, _adminHeaders())
            .then(function () {
              return vm.loadShiftRequest();
            })
            .then(function () {
              try { loadShifteeCandidates(); } catch (e) {}
              try {
                if (window.Swal) {
                  Swal.fire({ icon: 'success', title: 'Updated', text: 'Shift request ' + status + '.' });
                }
              } catch (e) {}
              return vm.shiftRequest;
            })
            .catch(function (err) {
              var m = (err && err.data && err.data.message) || (err && err.message) || 'Update failed';
              if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Error', text: m });
              } else {
                try { alert(m); } catch (e) {}
              }
              throw err;
            })
            .finally(function () {
              vm.reqSaving = false;
              if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
            });
        };

        if (window.Swal) {
          return Swal.fire({
            title: 'Confirm ' + status + '?',
            text: 'This will mark the request as ' + status + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, ' + status,
            cancelButtonText: 'Cancel'
          }).then(function (res) {
            if (!res.isConfirmed) { return null; }
            return doUpdate();
          });
        } else {
          var ok = true;
          try { ok = window.confirm('Confirm ' + status + ' the shift request?'); } catch (e) {}
          if (!ok) return Promise.resolve(null);
          return doUpdate();
        }
      } catch (e) {
        return Promise.reject(e);
      }
    }

    // Short label for program id
    function programShort(id) {
      try {
        var p = vm.findProgramById(id);
        if (!p) return (id != null ? ('' + id) : 'N/A');
        var code = p.strProgramCode || p.code || '';
        if (code) return code;
        var desc = p.strProgramDescription || p.title || '';
        return desc || ('#' + (p.intProgramID || p.id || ''));
      } catch (e) {
        return (id != null ? ('' + id) : 'N/A');
      }
    }

    // Attach X-Faculty-ID for registrar/admin context
    function _adminHeaders(extra) {
      var headers = Object.assign({}, extra || {});
      try {
        var state = StorageService && StorageService.getJSON ? StorageService.getJSON('loginState') : null;
        if (state && state.faculty_id) {
          headers['X-Faculty-ID'] = state.faculty_id;
        }
      } catch (e) {}
      return { headers: headers };
    }

    // Helpers
    function programLabel(p) {
      if (!p) return '';
      var id = p.intProgramID || p.id || '';
      var code = p.strProgramCode || p.code || '';
      var desc = p.strProgramDescription || p.title || '';
      if (code && desc) return code + ' — ' + desc;
      if (desc) return desc;
      if (code) return code;
      return id ? ('Program #' + id) : 'Program';
    }

    function curriculumLabel(c) {
      if (!c) return '';
      return c.strName || c.name || ('Curriculum #' + (c.intID || c.id || ''));
    }

    function findProgramById(id) {
      try {
        if (id === null || id === undefined || ('' + id).trim() === '') return null;
        var n = parseInt(id, 10);
        if (isNaN(n)) return null;
        var arr = vm.programs || [];
        for (var i = 0; i < arr.length; i++) {
          var pid = arr[i].intProgramID !== undefined && arr[i].intProgramID !== null ? arr[i].intProgramID : arr[i].id;
          var pn = parseInt(pid, 10);
          if (!isNaN(pn) && pn === n) return arr[i];
        }
        return null;
      } catch (e) { return null; }
    }

    function sanitizeStudentNumber(studentNumber) {
      try {
        var firstSpaceIndex = ('' + studentNumber).indexOf(' ');
        if (firstSpaceIndex > 0) return ('' + studentNumber).substring(0, firstSpaceIndex);
        return studentNumber;
      } catch (e) {
        return studentNumber;
      }
    }
  }
})();
