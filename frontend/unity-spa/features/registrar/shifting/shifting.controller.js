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
        // Listen for term changes
        if ($scope && $scope.$on) {
          $scope.$on('termChanged', function (event, data) {
            if (data && data.selectedTerm) {
              vm.selectedTerm = data.selectedTerm;
              vm.term = data.selectedTerm && data.selectedTerm.intID ? data.selectedTerm.intID : vm.term;
              // Refresh registration context on term change
              try { loadRegistration(); } catch (e) {}
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
          // Normalize similar to EnlistmentController
          vm.programs = (rows || []).map(function (p) {
            var id = p.intProgramID || p.id || null;
            var code = p.strProgramCode || p.code || '';
            var desc = p.strProgramDescription || p.title || p.description || '';
            return Object.assign({}, p, {
              intProgramID: id,
              strProgramCode: code,
              strProgramDescription: desc
            });
          }).filter(function (p) { return p.intProgramID !== null; });
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
        if (!s) return;
        StudentsService.listSuggestions(s).then(function (rows) {
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
        // Resolve student id then load registration
        resolveStudentIdIfNeeded().then(function () {
          loadRegistration();
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
          vm.form.program_id = cp;
          if (cp) {
            loadCurricula(cp);
          } else {
            vm.curricula = [];
          }
          vm.form.curriculum_id = (vm.registration.current_curriculum !== undefined && vm.registration.current_curriculum !== null)
            ? vm.registration.current_curriculum : '';
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
        vm.saving = true;
        var fields = {};
        var pid = parseInt(vm.form.program_id, 10);
        var cid = parseInt(vm.form.curriculum_id, 10);
        if (isFinite(pid)) fields.current_program = pid;
        if (isFinite(cid)) fields.current_curriculum = cid;

        UnityService.updateRegistration({
          student_number: sanitizeStudentNumber(vm.studentNumber),
          term: parseInt(vm.term, 10),
          fields: fields
        }).then(function (res) {
          var reg = res && res.data ? res.data.registration : null;
          if (reg) {
            vm.registration = reg;
          }
          if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Saved', text: 'Program and Curriculum updated for the selected term.' });
          } else {
            try { alert('Saved.'); } catch (e) {}
          }
        }).catch(function (err) {
          var m = (err && err.data && err.data.message) || 'Save failed';
          if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Error', text: m });
          } else {
            try { alert(m); } catch (e) {}
          }
        }).finally(function () {
          vm.saving = false;
          if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
        });
      } catch (e) {
        vm.saving = false;
      }
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
