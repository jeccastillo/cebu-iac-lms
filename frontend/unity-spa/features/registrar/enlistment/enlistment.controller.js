(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('EnlistmentController', EnlistmentController);

  EnlistmentController.$inject = ['$http', 'APP_CONFIG', 'UnityService', 'ClasslistsService', 'TermService', 'ChecklistService', 'ProgramsService', 'CurriculaService', 'StudentsService', 'SectionsSlotsService', '$location', '$scope'];
  function EnlistmentController($http, APP_CONFIG, UnityService, ClasslistsService, TermService, ChecklistService, ProgramsService, CurriculaService, StudentsService, SectionsSlotsService, $location, $scope) {
    var vm = this;
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    // State
    vm.loading = false;
    vm.subjects = [];
    vm.sections = [];
    vm.blockSection = '';
    vm.blockSections = [];
    vm.results = null;
    vm.student_id = null;
    vm.studentDefaultProgramId = null;
    vm.clGenLoading = false;
    vm.hasChecklist = false;
    // Tuition preview state
    vm.tuition = null;
    vm.tuitionLoading = false;
    vm._tuitionKey = null;
    // Track if auto Student Type 'shiftee' applied per student|term
    vm._autoStudentTypeApplied = {};

    // Global term (read-only, from TermService)
    vm.selectedTerm = null;
    vm.term = '';

    // Student selection (searchable dropdown)
    vm.students = [];
    vm.studentSearch = '';
    vm.studentNumber = '';
    vm.selectedStudentName = '';
    vm.studentFilter = function (s) {
      if (!vm.studentSearch) return true;
      var q = ('' + vm.studentSearch).toLowerCase();
      function has(v) { return (v ? ('' + v) : '').toLowerCase().indexOf(q) !== -1; }
      return has(s.student_number) || has(s.last_name) || has(s.first_name) || has(s.middle_name);
    };

    vm.yearLevel = 1;
    vm.studentType = 'new';

    // Checklist-driven auto-load inputs
    // Legacy (kept for backward compatibility)
    vm.clYearStart = '';
    vm.clYearEnd = '';
    vm.clSem = '';
    // New checklist-style inputs
    vm.clYearLevel = '';
    vm.clSemInt = '';

    vm.current = []; // current enlisted (for selected term)
    vm.ops = [];     // pending operations

    // Registration edit state
    vm.registration = null;
    vm.regForm = {};
    vm.programs = [];
    vm.curricula = [];
    vm.tuitionYears = [];
    vm.regSaving = false;

    // UI selections
    vm.selectedAddClasslistId = '';
    vm.selectedDropClasslistId = '';
    vm.changeFromId = '';
    vm.changeToId = '';

    // Methods
    vm.loadStudents = loadStudents;
    vm.loadCurrent = loadCurrent;
    vm.loadClasslistsForTerm = loadClasslistsForTerm;
    vm.autoLoadByChecklist = autoLoadByChecklist;

    vm.queueAdd = queueAdd;
    vm.queueDrop = queueDrop;
    vm.queueChange = queueChange;
    vm.removeOpAt = removeOpAt;
    vm.clearOps = clearOps;
    vm.submit = submit;
    vm.resetRegistration = resetRegistration;
    vm.onStudentSelected = onStudentSelected;
    vm.autoQueueFromChecklist = autoQueueFromChecklist;
    vm.generateChecklist = generateChecklist;
    vm.refreshChecklistExists = refreshChecklistExists;
    vm.onStudentQuery = onStudentQuery;
    vm.selectedAddRemaining = selectedAddRemaining;
    vm.selectedChangeToRemaining = selectedChangeToRemaining;

    // Registration details panel
    vm.loadRegistration = loadRegistration;
    vm.saveRegistration = saveRegistration;
    vm.resetRegForm = resetRegForm;
    vm.isRegDirty = isRegDirty;
    vm.loadPrograms = loadPrograms;
    vm.loadCurricula = loadCurricula;
    vm.loadTuitionYears = loadTuitionYears;
    vm.programLabel = programLabel;

    // Tuition preview
    vm.loadTuition = loadTuition;

    vm.sanitizeStudentNumber = sanitizeStudentNumber;
    vm.maybeAutoSelectShiftee = maybeAutoSelectShiftee;

    // Installment tabs
    vm.installmentTab = 'standard';
    vm.selectInstallmentTab = selectInstallmentTab;
    vm.installmentData = installmentData;
    vm.displayedTotalDue = displayedTotalDue;
    vm.increaseInfo = increaseInfo;
    vm.canGenerateRegForm = canGenerateRegForm;
    vm.regFormUrl = regFormUrl;
    vm.openRegFormPdf = openRegFormPdf;

    // Add dynamic installment tabs from tuition.summary.installments.plans
    vm.installmentPlans = [];
    vm.selectedInstallmentPlanId = null;

    function selectInstallmentTab(tabKey) {
      try {
        if (!vm.installmentPlans || !vm.installmentPlans.length) return;
        if (tabKey === 'standard' || tabKey === 'dp30' || tabKey === 'dp50') {
          vm.installmentTab = tabKey;
        } else {
          // Check if tabKey matches any plan code
          var found = vm.installmentPlans.find(function (p) { return p.code === tabKey; });
          if (found) {
            vm.installmentTab = tabKey;
          }
        }
      } catch (e) {}
    }

    function installmentData() {
      try {
        var inst = vm.tuition && vm.tuition.summary && vm.tuition.summary.installments;
        if (!inst) return null;
        var plans = inst.plans || [];
        var selectedPlan = null;
        if (vm.selectedInstallmentPlanId) {
          selectedPlan = plans.find(function (p) { return p.id === vm.selectedInstallmentPlanId; });
        }
        if (!selectedPlan && plans.length) {
          selectedPlan = plans[0];
        }
        if (!selectedPlan) return null;

        return {
          dp: selectedPlan.down_payment || 0,
          fee: selectedPlan.installment_fee || 0,
          total: selectedPlan.total_installment || 0
        };
      } catch (e) {
        return null;
      }
    }

    function displayedTotalDue() {
      try {
        var inst = vm.tuition && vm.tuition.summary && vm.tuition.summary.installments;
        if (!inst) return 0;
        var plans = inst.plans || [];
        var selectedPlan = null;
        if (vm.selectedInstallmentPlanId) {
          selectedPlan = plans.find(function (p) { return p.id === vm.selectedInstallmentPlanId; });
        }
        if (!selectedPlan && plans.length) {
          selectedPlan = plans[0];
        }
        if (!selectedPlan) return 0;

        return selectedPlan.total_installment || 0;
      } catch (e) {
        return 0;
      }
    }

    // Tuition save snapshot
    vm.tuitionSaving = false;
    vm.canSaveTuition = canSaveTuition;
    vm.saveTuition = saveTuition;

    function sanitizeStudentNumber(studentNumber){

      const firstSpaceIndex = studentNumber.indexOf(' ');
      var sn = "";
      if(firstSpaceIndex > 0)
        return studentNumber.substring(0, firstSpaceIndex);
      else
        return studentNumber;

    }

    // Auto-select Student Type to 'shiftee' once per student+term if a shifting record exists.
    // Does not modify Registration Details (regForm.enumStudentType) and never overrides manual user choice after first run.
    function maybeAutoSelectShiftee() {
      try {
        var sid = parseInt(vm.student_id, 10);
        var termInt = parseInt(vm.term, 10);
        if (!isFinite(sid) || sid <= 0) return;
        if (!isFinite(termInt) || termInt <= 0) return;

        vm._autoStudentTypeApplied = vm._autoStudentTypeApplied || {};
        var key = String(sid) + '|' + String(termInt);
        if (vm._autoStudentTypeApplied[key]) return;

        if (!StudentsService || typeof StudentsService.shifted !== 'function') {
          vm._autoStudentTypeApplied[key] = true;
          return;
        }

        StudentsService.shifted(sid, termInt)
          .then(function (res) {
            try {
              if (res && res.shifted) {
                vm.studentType = 'shiftee';
              }
            } catch (_e) {}
          })
          .catch(function () { /* swallow */ })
          .finally(function () {
            try { vm._autoStudentTypeApplied[key] = true; } catch (_e2) {}
            if ($scope && $scope.$applyAsync) { try { $scope.$applyAsync(); } catch (_e3) {} }
          });
      } catch (_e4) {}
    }

    // Determine if Reg Form generation should be enabled
    function canGenerateRegForm() {
      try {
        if (!vm.registration) return false;
        var status = (vm.registration.enrollment_status || '').toString().toLowerCase();
        console.log("STATUS",status);
        var enlistedByStatus = (status === 'enlisted' || status === 'enrolled');
        var enlistedByDate = !!vm.registration.date_enlisted; // fallback if status is missing but date_enlisted is present
        var hasKeys = !!vm.studentNumber && !!vm.term;
        return hasKeys && (enlistedByStatus || enlistedByDate);
      } catch (e) {
        return false;
      }
    }
    // Build Registration Form PDF URL (inline open). Visible only if canGenerateRegForm() is true.
    function regFormUrl() {
      try {
        if (!canGenerateRegForm()) return '';
        var termInt = parseInt(vm.term, 10);
        if (!termInt || isNaN(termInt)) return '';
        var sn = vm.sanitizeStudentNumber(vm.studentNumber);
        return UnityService.regFormUrl(sn, termInt) || '';
      } catch (e) {
        return '';
      }
    }
    // Open Registration Form PDF with headers via XHR + Blob (to include X-Faculty-ID)
    function openRegFormPdf() {
      try {
        if (!canGenerateRegForm()) {
          if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Unavailable', text: 'Registration Form is only available for enlisted/enrolled registrations with a selected student and term.' });
          } else {
            try { alert('Registration Form is only available for enlisted/enrolled registrations with a selected student and term.'); } catch (e) {}
          }
          return;
        }
        var termInt = parseInt(vm.term, 10);
        if (!termInt || isNaN(termInt)) {
          if (window.Swal) { Swal.fire({ icon: 'warning', title: 'Invalid term', text: 'Selected term is invalid.' }); }
          else { try { alert('Selected term is invalid.'); } catch (e) {} }
          return;
        }
        var sn = vm.sanitizeStudentNumber(vm.studentNumber);
        vm.regPdfLoading = true;

        UnityService.regFormFetch(sn, termInt).then(function (resp) {
          var data = resp && resp.data;
          if (!data) throw new Error('Empty PDF response');

          // Build filename from Content-Disposition when present
          var filename = 'reg-form-' + sn + '-' + termInt + '.pdf';
          try {
            var cd = resp && resp.headers ? (resp.headers('Content-Disposition') || resp.headers('content-disposition')) : null;
            if (cd) {
              var m = /filename\*?=(?:UTF-8''|")?([^\";]+)/i.exec(cd);
              if (m && m[1]) {
                var raw = (m[1] || '').replace(/\"/g, '');
                try { filename = decodeURIComponent(raw); } catch (e) { filename = raw; }
              }
            }
          } catch (e) { /* ignore */ }

          var blob = new Blob([data], { type: 'application/pdf' });
          var URL_ = (window.URL || window.webkitURL);
          var url = URL_ && URL_.createObjectURL ? URL_.createObjectURL(blob) : null;

          if (url) {
            var win = null;
            try { win = window.open(url, '_blank'); } catch (e) { win = null; }
            if (!win) {
              // Fallback: force download
              var a = document.createElement('a');
              a.href = url;
              a.download = filename;
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
            }
            // Revoke after a short delay
            setTimeout(function () { try { URL_.revokeObjectURL(url); } catch (e) {} }, 10000);
          } else {
            // As a last resort, create a data URL (may be blocked for large files)
            try {
              var reader = new FileReader();
              reader.onloadend = function () {
                var dataUrl = reader.result;
                var w = null;
                try { w = window.open(dataUrl, '_blank'); } catch (e) { w = null; }
                if (!w) {
                  var a2 = document.createElement('a');
                  a2.href = dataUrl;
                  a2.download = filename;
                  document.body.appendChild(a2);
                  a2.click();
                  document.body.removeChild(a2);
                }
              };
              reader.readAsDataURL(blob);
            } catch (e) {
              if (window.Swal) { Swal.fire({ icon: 'error', title: 'Error', text: 'Unable to open the PDF.' }); }
              else { try { alert('Unable to open the PDF.'); } catch (e2) {} }
            }
          }
        }).catch(function (err) {
          console.error('regFormFetch failed', err);
          var msg = (err && err.data && err.data.message) || (err && err.message) || 'Failed to generate Registration Form PDF.';
          if (window.Swal) { Swal.fire({ icon: 'error', title: 'Error', text: msg }); }
          else { try { alert(msg); } catch (e) {} }
        }).finally(function () {
          vm.regPdfLoading = false;
          if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
        });
      } catch (e) {
        vm.regPdfLoading = false;
      }
    }

    // Tuition loader: builds payload from current enlisted and registration
    function loadTuition(force) {
      try {        
        if (!vm.studentNumber || !vm.term) return;        
        // Derive program id
        var programId = null;        
        if (vm.regForm && vm.regForm.current_program) {
          var p1 = parseInt(vm.regForm.current_program, 10);
          if (!isNaN(p1)) programId = p1;
        } else if (vm.registration && vm.registration.current_program !== undefined && vm.registration.current_program !== null) {
          var p2 = parseInt(vm.registration.current_program, 10);
          if (!isNaN(p2)) programId = p2;
        }
        if (!programId || isNaN(programId)) {
          // Without program id, tuition preview cannot compute a rate
          return;
        }

        // Build subject list from current enlisted
        var subjects = [];
        (vm.current || []).forEach(function (c) {
          if (c && c.subject_id) {
            subjects.push({ subject_id: parseInt(c.subject_id, 10), section: c.section_code || '' });
          }
        });
        // If no subjects yet, skip
        if (!subjects.length) {
          return;
        }

        // Avoid duplicate loads by keying inputs
        var key = [vm.studentNumber, ('' + vm.term), programId, subjects.map(function (s) { return s.subject_id; }).join('-')].join('|');
        if (!force && vm._tuitionKey === key && vm.tuition !== null) {
          return;
        }
        vm._tuitionKey = key;

        vm.tuitionLoading = true;       
        
        var payload = {
          student_number: vm.sanitizeStudentNumber(vm.studentNumber),
          program_id: programId,
          term: ('' + vm.term),
          subjects: subjects          
        };
        UnityService.tuitionPreview(payload).then(function (res) {
          // Unwrap TuitionBreakdownResource from response
          var data = (res && res.data) ? res.data : res;
          vm.tuition = data || null;
          // Initialize selected installment plan id (dynamic plans) when available
          try {
            var inst = (vm.tuition && vm.tuition.summary && vm.tuition.summary.installments) ? vm.tuition.summary.installments : null;
            var plans = inst && Array.isArray(inst.plans) ? inst.plans : [];
            if (plans.length) {
              var spid = (inst && inst.selected_plan_id != null) ? inst.selected_plan_id : (plans[0] && plans[0].id);
              if (spid != null) vm.selectedInstallmentPlanId = spid;
            }
          } catch (_eSelPlan) {}

          // Populate dynamic installment plans from payload for tabs
          try {
            var inst = vm.tuition && vm.tuition.summary && vm.tuition.summary.installments;
            var plans = inst && Array.isArray(inst.plans) ? inst.plans.slice() : [];
            vm.installmentPlans = plans;
            if (plans && plans.length) {
              var spid = inst && inst.selected_plan_id != null ? parseInt(inst.selected_plan_id, 10) : null;
              if (isFinite(spid)) {
                vm.selectedInstallmentPlanId = spid;
              } else {
                var firstId = plans[0] && plans[0].id != null ? parseInt(plans[0].id, 10) : null;
                vm.selectedInstallmentPlanId = isFinite(firstId) ? firstId : null;
              }
            } else {
              vm.selectedInstallmentPlanId = null;
            }
          } catch (_ePlans) {
            vm.installmentPlans = [];
            vm.selectedInstallmentPlanId = null;
          }
        }).catch(function (err) {
          console.error('tuitionPreview failed', err);
          vm.tuition = null;
        }).finally(function () {
          vm.tuitionLoading = false;
          if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
        });
      } catch (e) {
        // swallow
      }
    }

    function canSaveTuition() {
      try {
        return !!(vm.tuition && !vm.tuitionLoading && vm.registration && vm.studentNumber && vm.term);
      } catch (e) { return false; }
    }

    function saveTuition() {
      try {
        if (!canSaveTuition()) {
          if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Cannot Save', text: 'Load tuition and ensure a registration exists first.' });
          } else {
            try { alert('Load tuition and ensure a registration exists first.'); } catch (e) {}
          }
          return;
        }
        var termInt = parseInt(vm.term, 10);
        vm.tuitionSaving = true;

        function doSave() {
          UnityService.tuitionSave({
            student_number: vm.sanitizeStudentNumber(vm.studentNumber),
            term: termInt
          }).then(function (res) {
            var ok = res && res.success;
            var msg = (res && res.message) || (ok ? 'Tuition saved.' : 'Failed to save tuition.');
            if (window.Swal) {
              Swal.fire({ icon: ok ? 'success' : 'error', title: ok ? 'Saved' : 'Error', text: msg });
            } else {
              try { alert(msg); } catch (e) {}
            }
          }).catch(function (err) {
            console.error('tuitionSave failed', err);
            var m = (err && err.data && err.data.message) || 'Failed to save tuition.';
            if (window.Swal) {
              Swal.fire({ icon: 'error', title: 'Error', text: m });
            } else {
              try { alert(m); } catch (e) {}
            }
          }).finally(function () {
            vm.tuitionSaving = false;
            if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
          });
        }

        // Preflight: check if a saved snapshot already exists
        UnityService.tuitionSaved({
          student_number: vm.sanitizeStudentNumber(vm.studentNumber),
          term: termInt
        }).then(function (res) {
          var data = (res && res.data) ? res.data : res;
          var exists = !!(data && data.exists);
          if (exists) {
            if (window.Swal) {
              Swal.fire({
                icon: 'warning',
                title: 'Overwrite saved tuition?',
                text: 'A saved tuition snapshot already exists for this registration. Do you want to overwrite it?',
                showCancelButton: true,
                confirmButtonText: 'Overwrite',
                cancelButtonText: 'Cancel'
              }).then(function (result) {
                if (result.isConfirmed) {
                  doSave();
                } else {
                  vm.tuitionSaving = false;
                  if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
                }
              });
            } else {
              var c = false;
              try { c = window.confirm('A saved tuition snapshot already exists. Overwrite?'); } catch (e) { c = true; }
              if (c) { doSave(); } else { vm.tuitionSaving = false; }
            }
          } else {
            doSave();
          }
        }).catch(function () {
          // On preflight error, proceed with save to avoid blocking
          doSave();
        });
      } catch (e) {
        vm.tuitionSaving = false;
      }
    }

    // Installment helpers
    function selectInstallmentTab(tab) {
      try {
        // Dynamic: map by plan code when plans available; fallback to legacy
        var inst = vm.tuition && vm.tuition.summary && vm.tuition.summary.installments;
        var plans = inst && Array.isArray(inst.plans) ? inst.plans : [];
        if (plans.length) {
          var found = null;
          for (var i = 0; i < plans.length; i++) {
            if ((plans[i].code || '') === (tab || '')) { found = plans[i]; break; }
          }
          if (found && found.id != null) {
            vm.selectedInstallmentPlanId = found.id;
            return;
          }
        }
        // Legacy fallback
        vm.installmentTab = tab;
      } catch (e) {}
    }

    function _installments() {
      try {
        return (vm.tuition && vm.tuition.summary && vm.tuition.summary.installments) ? vm.tuition.summary.installments : null;
      } catch (e) { return null; }
    }

    function installmentData() {
      try {
        var inst = _installments();
        if (!inst) return null;
        var plans = inst.plans || [];
        var selected = null;
        if (vm.selectedInstallmentPlanId != null) {
          var selId = parseInt(vm.selectedInstallmentPlanId, 10);
          for (var i = 0; i < plans.length; i++) {
            var p = plans[i] || {};
            if (p.id != null && parseInt(p.id, 10) === selId) { selected = p; break; }
          }
        }
        if (!selected && plans.length) selected = plans[0];
        if (selected) {
          return {
            dp: selected.down_payment || 0,
            fee: selected.installment_fee || 0,
            total: selected.total_installment || 0
          };
        }
        // Fallback to legacy baseline
        return {
          dp: inst.down_payment || 0,
          fee: inst.installment_fee || 0,
          total: inst.total_installment || 0
        };
      } catch (e) { return null; }
    }

    function displayedTotalDue() {
      try {
        var sumDue = (vm.tuition && vm.tuition.summary && vm.tuition.summary.total_due) ? vm.tuition.summary.total_due : 0;
        var inst = _installments();
        if (!inst) return sumDue;
        var plans = inst.plans || [];
        var selected = null;
        if (vm.selectedInstallmentPlanId != null) {
          var selId = parseInt(vm.selectedInstallmentPlanId, 10);
          for (var i = 0; i < plans.length; i++) {
            var p = plans[i] || {};
            if (p.id != null && parseInt(p.id, 10) === selId) { selected = p; break; }
          }
        }
        if (!selected && plans.length) selected = plans[0];
        var total = selected && selected.total_installment;
        if (!isFinite(parseFloat(total))) {
          total = inst.total_installment != null ? inst.total_installment : sumDue;
        }
        return parseFloat(total) || 0;
      } catch (e) { return 0; }
    }

    // Cached calculation to prevent infinite digest caused by returning a new object each digest
    function increaseInfo() {
      try {
        // Must have tuition summary
        if (!vm || !vm.tuition || !vm.tuition.summary) return null;
        var sum = vm.tuition.summary || {};
        var baseTuition = parseFloat(sum.tuition);
        if (!isFinite(baseTuition)) baseTuition = 0;
        var baseLab = parseFloat(sum.lab_total);
        if (!isFinite(baseLab)) baseLab = 0;

        // Determine increase percent:
        // Priority:
        // 1) Selected dynamic plan's increase_percent (when available)
        // 2) vm.tuition.meta.installment_increase_percent
        // 3) Legacy mapping by vm.installmentTab (dp30/dp50) → default to 0 if unknown
        var percent = null;
        var selectedPlan = null;

        var inst = (vm.tuition && vm.tuition.summary && vm.tuition.summary.installments) ? vm.tuition.summary.installments : null;
        var plans = inst && Array.isArray(inst.plans) ? inst.plans : [];

        if (plans.length && vm.selectedInstallmentPlanId != null) {
          var selId = parseInt(vm.selectedInstallmentPlanId, 10);
          if (isFinite(selId)) {
            for (var i = 0; i < plans.length; i++) {
              var p = plans[i] || {};
              var pid = p.id != null ? parseInt(p.id, 10) : null;
              if (pid != null && pid === selId) { selectedPlan = p; break; }
            }
          }
        }
        if (!selectedPlan && plans.length) {
          selectedPlan = plans[0];
        }
        if (selectedPlan && selectedPlan.increase_percent != null) {
          var p1 = parseFloat(selectedPlan.increase_percent);
          if (isFinite(p1)) percent = p1;
        }

        if (percent === null || !isFinite(percent)) {
          var metaPct = vm.tuition && vm.tuition.meta && vm.tuition.meta.installment_increase_percent;
          if (metaPct != null && metaPct !== '') {
            var p2 = parseFloat(metaPct);
            if (isFinite(p2)) percent = p2;
          }
        }

        // Legacy fallback: if still unknown, provide safe defaults per tab
        if (percent === null || !isFinite(percent)) {
          if (vm.installmentTab === 'dp30') {
            percent = 0; // no implicit assumption without backend/meta
          } else if (vm.installmentTab === 'dp50') {
            percent = 0; // no implicit assumption without backend/meta
          } else {
            percent = 0;
          }
        }

        // Compute new amounts
        var factor = 1 + (Math.max(0, percent) / 100.0);
        var tuitionNew = Math.round((baseTuition * factor) * 100) / 100;
        var labNew = Math.round((baseLab * factor) * 100) / 100;

        // Cache to avoid returning a new object every digest
        var cacheKey = [
          vm.installmentTab || '',
          (selectedPlan && selectedPlan.id) != null ? String(selectedPlan.id) : '',
          String(baseTuition),
          String(baseLab),
          String(percent)
        ].join('|');

        vm._increaseInfoCache = vm._increaseInfoCache || { key: null, val: null };
        if (vm._increaseInfoCache.key === cacheKey && vm._increaseInfoCache.val) {
          return vm._increaseInfoCache.val;
        }

        var out = {
          percent: percent,
          tuitionBase: baseTuition,
          labBase: baseLab,
          tuitionNew: tuitionNew,
          labNew: labNew
        };
        vm._increaseInfoCache.key = cacheKey;
        vm._increaseInfoCache.val = out;
        return out;
      } catch (e) {
        return null;
      }
    }

    // Helpers for read-only display and lookups
    vm.findProgramById = function(id) {
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
    };
    vm.findCurriculumById = function(id) {
      try {
        if (id === null || id === undefined || ('' + id).trim() === '') return null;
        var n = parseInt(id, 10);
        if (isNaN(n)) return null;
        var arr = vm.curricula || [];
        for (var i = 0; i < arr.length; i++) {
          var cid = arr[i].intID !== undefined && arr[i].intID !== null ? arr[i].intID : arr[i].id;
          var cn = parseInt(cid, 10);
          if (!isNaN(cn) && cn === n) return arr[i];
        }
        return null;
      } catch (e) { return null; }
    };
    vm.findTuitionYearById = function(id) {
      try {
        if (id === null || id === undefined || ('' + id).trim() === '') return null;
        var n = parseInt(id, 10);
        if (isNaN(n)) return null;
        var arr = vm.tuitionYears || [];
        for (var i = 0; i < arr.length; i++) {
          var tid = arr[i].intID !== undefined && arr[i].intID !== null ? arr[i].intID : arr[i].id;
          var tn = parseInt(tid, 10);
          if (!isNaN(tn) && tn === n) return arr[i];
        }
        return null;
      } catch (e) { return null; }
    };
    vm.readableWithdrawalPeriod = function(v) {
      try {
        if (v === null || v === undefined || v === '') return '';
        if (typeof v === 'number') {
          if (v === 0) return 'Before';
          if (v === 1) return 'Start';
          if (v === 2) return 'End';
          return '' + v;
        }
        var s = ('' + v).toLowerCase().trim();
        if (s === 'before' || s === '0') return 'Before';
        if (s === 'start' || s === '1') return 'Start';
        if (s === 'end' || s === '2') return 'End';
        return ('' + v);
      } catch (e) { return ''; }
    };
    vm.curriculumLabel = function(c) {
      if (!c) return '';
      return c.strName || c.name || ('Curriculum #' + (c.intID || c.id || ''));
    };
    vm.tuitionYearLabel = function(id) {
      var ty = vm.findTuitionYearById(id);
      if (!ty) return '';
      return ty.label || ty.year || ('Tuition Year #' + (ty.intID || ty.id || ''));
    };

    activate();

    function activate() {
      // Initialize global term context and preload students
      if (TermService && TermService.init) {
        TermService.init().then(function () {
          try {
            var sel = TermService.getSelectedTerm && TermService.getSelectedTerm();
            vm.selectedTerm = sel || null;
            vm.term = (sel && sel.intID) ? sel.intID : '';
          } catch (e) {
            vm.selectedTerm = null;
            vm.term = '';
          }
        }).finally(function () {
          loadPrograms();
          loadTuitionYears();
          try { initFromQueryString(); } catch (e) {}
        });
      } else {
        loadPrograms();
        loadTuitionYears();
        try { initFromQueryString(); } catch (e) {}
      }

      // React to global term changes
      if ($scope && $scope.$on) {
        $scope.$on('termChanged', function (event, data) {
          if (data && data.selectedTerm) {
            vm.selectedTerm = data.selectedTerm;
            vm.term = data.selectedTerm && data.selectedTerm.intID ? data.selectedTerm.intID : vm.term;
            // Optionally refresh data if a student is selected
            if (vm.studentNumber) {
              loadCurrent();
              loadClasslistsForTerm();
              // Always try reloading registration panel on term changes
              loadRegistration();
              try { maybeAutoSelectShiftee(); } catch (e) {}
            }
            
          }
        });
      }
    }
 
    // Initialize from URL query: ?student_id=ID
    function initFromQueryString() {
      try {
        if (!$location || !$location.search) return;
        var qs = $location.search() || {};
        var sid = qs.student_id || qs.studentId || qs.sid;
        var id = parseInt(sid, 10);
        if (isNaN(id) || id <= 0) return;
        // Do not override if a student has been selected via search/input
        if (vm.studentNumber) return;

        vm.loading = true;
        $http.get(APP_CONFIG.API_BASE + '/students/' + id).then(function (res) {
          var data = (res && res.data) ? res.data : res;
          var row = (data && data.data) ? data.data : data;
          if (!row) return;

          var sn = row.student_number || row.strStudentNumber || '';
          if (!sn) return;

          // Cache ids and number
          vm.student_id = (row.id != null) ? row.id : ((row.intID != null) ? row.intID : id);
          vm.studentNumber = sn;
          try {
            vm.studentDefaultProgramId = (row.program_id != null) ? row.program_id
              : ((row.intProgramID != null) ? row.intProgramID : null);
          } catch (e) {
            vm.studentDefaultProgramId = null;
          }

          // Reset tuition state similar to onStudentSelected
          vm.tuition = null;
          vm.installmentTab = 'standard';
          vm._tuitionKey = null;

          // Selected name display
          try {
            var ln = row.last_name || row.strLastname || '';
            var fn = row.first_name || row.strFirstname || '';
            var mn = row.middle_name || row.strMiddlename || '';
            var full = (ln ? (ln + ', ') : '') + (fn || '') + (mn ? (' ' + mn) : '');
            var disp = (full || '').trim();
            vm.selectedStudentName = disp ? (disp + ' (' + sn + ')') : sn;
          } catch (e) {
            try { setSelectedStudentName(); } catch (e2) {}
          }

          refreshChecklistExists();

          if (vm.term) {
            loadCurrent();
            loadRegistration();
          }
        }).finally(function () {
          vm.loading = false;
          if ($scope && $scope.$applyAsync) { try { $scope.$applyAsync(); } catch (e) {} }
        });
      } catch (e) {}
    }
 
    function onStudentSelected() {
      if (vm.studentNumber) {
        setSelectedStudentName();        
        // Reset tuition on change of student
        vm.tuition = null;
        vm.installmentTab = 'standard';
        vm._tuitionKey = null;
        // Clear any previously resolved/cached id so that resolution follows the selected studentNumber
        vm.student_id = null;
        // Resolve and cache student id before loading current and registration
        resolveStudentIdIfNeeded().then(function (sid) {
          if (sid) {
            try {
              $http.get(APP_CONFIG.API_BASE + '/students/' + sid).then(function (resp) {
                var data = (resp && resp.data) ? resp.data : resp;
                var row = (data && data.data) ? data.data : data;
                vm.studentDefaultProgramId = (row && (row.program_id != null ? row.program_id : (row.intProgramID != null ? row.intProgramID : null))) || null;
              }).catch(function () { vm.studentDefaultProgramId = null; });
            } catch (e) { vm.studentDefaultProgramId = null; }
          } else {
            vm.studentDefaultProgramId = null;
          }
        }).finally(function () {
          loadCurrent();
          loadRegistration();
          refreshChecklistExists();
          try { maybeAutoSelectShiftee(); } catch (e) {}
        });
      } else {
        vm.selectedStudentName = '';
        vm.registration = null;
        vm.regForm = {};
        vm.student_id = null;
        vm.studentDefaultProgramId = null;
        vm.hasChecklist = false;
      }
    }
    
    function setSelectedStudentName() {      
      try {
        var sel = (vm.students || []).find(function (s) { return s.student_number === vm.studentNumber; });
        if (sel) {
          var full = (sel.last_name || '') + ', ' + (sel.first_name || '') + (sel.middle_name ? (' ' + sel.middle_name) : '');
          vm.selectedStudentName = full + ' (' + (sel.student_number || '') + ')';
        } else {
          vm.selectedStudentName = vm.studentNumber || '';
        }
      } catch (e) {
        vm.selectedStudentName = vm.studentNumber || '';
      }
    }

    // Resolve student id if missing; prefer cached id, then local list, finally API lookup by student_number
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
          // Fallback 1: exact student_number filter
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
            })
            .catch(function () { resolve(null); });
        } catch (e) {
          resolve(null);
        }
      });
    }

    function refreshChecklistExists() {
      try {
        vm.hasChecklist = false;
        if (!vm.studentNumber && !vm.student_id) return;
        resolveStudentIdIfNeeded().then(function (sid) {
          if (!sid) { vm.hasChecklist = false; return; }
          return ChecklistService.get(sid, {}).then(function (res) {
            var data = (res && res.data) ? res.data : res;
            var has = !!(data && ((data.id != null) || (data.items && data.items.length > 0)));
            vm.hasChecklist = has;
          }).catch(function () {
            vm.hasChecklist = false;
          }).finally(function () {
            if ($scope && $scope.$applyAsync) { try { $scope.$applyAsync(); } catch (e) {} }
          });
        });
      } catch (e) {
        vm.hasChecklist = false;
      }
    }

    // Auto-queue from checklist for current year level and term
    function autoQueueFromChecklist() {
      if (!vm.term || (!vm.studentNumber && !vm.student_id)) {
        if (window.Swal) {
          Swal.fire({ icon: 'warning', title: 'Missing data', text: 'Select a student and term first.' });
        }
        return;
      }

      // Resolve student id (prefer cached vm.student_id, fallback to selected list)
      var sid = null;
      if (vm.student_id) {
        var tmp = parseInt(vm.student_id, 10);
        if (!isNaN(tmp) && tmp > 0) sid = tmp;
      }
      if (!sid && vm.studentNumber) {
        var student = (vm.students || []).find(function (s) { return s && s.student_number === vm.studentNumber; });
        if (student && student.id) sid = student.id;
      }
      if (!sid) {
        if (window.Swal) {
          Swal.fire({ icon: 'error', title: 'Student not found', text: 'Unable to resolve student id for checklist.' });
        }
        return;
      }

      // Normalizer for subject codes
      function normalizeCode(s) {
        return String(s || '').toUpperCase().replace(/[\s\-_]/g, '');
      }

      var alreadyEnlistedBySubject = {};
      (vm.current || []).forEach(function (c) {
        if (c && c.code) { alreadyEnlistedBySubject[normalizeCode(c.code)] = true; }
      });
      

      // Build lookups of available classlists by subject code and by subject id for the selected term      
      var classlistsBySubjectId = {};
      (vm.sections || []).forEach(function (sec) {        
        var sidRaw = (sec && (sec.subject_id !== undefined && sec.subject_id !== null)) ? sec.subject_id : null;
        var sidKey = (sidRaw !== null && sidRaw !== undefined && String(sidRaw).trim() !== '') ? String(parseInt(sidRaw, 10)) : null;
        
        if (sidKey) {          
          if (!classlistsBySubjectId[sidKey]) classlistsBySubjectId[sidKey] = [];
          classlistsBySubjectId[sidKey].push(sec);
        }
      });      

      vm.loading = true;
      ChecklistService.get(sid, {}).then(function (res) {
        var data = res && res.data ? res.data : res;
        var items = (data && data.items) ? data.items : [];
        var candidateItems = [];

        // First pass: collect all candidate items
        items.forEach(function (it) {
          if (!it || !it.subject || !it.subject.code) return;          
          // Only planned/required items matching the UI year level, if provided
          var status = (it.strStatus || '').toLowerCase();
          var required = !!it.isRequired;

          // Use explicit checklist Year filter first, then fallback to UI yearLevel; only enforce when both filter and item value exist
          var yrFilter = (vm.clYearLevel !== '' && vm.clYearLevel !== null && vm.clYearLevel !== undefined)
            ? parseInt(vm.clYearLevel, 10)
            : (vm.yearLevel ? parseInt(vm.yearLevel, 10) : null);
          var ylMatch = true;
          if (yrFilter !== null && yrFilter !== undefined && it.intYearLevel !== null && it.intYearLevel !== undefined) {
            ylMatch = parseInt(it.intYearLevel, 10) === yrFilter;
          } else {
            ylMatch = true; // do not exclude when metadata is missing
          }

          // Optional Sem filter: enforce only when both filter and item value exist
          var semFilter = (vm.clSemInt !== '' && vm.clSemInt !== null && vm.clSemInt !== undefined)
            ? parseInt(vm.clSemInt, 10)
            : null;
          var semMatch = true;
          if (semFilter !== null && it.intSem !== null && it.intSem !== undefined) {
            semMatch = parseInt(it.intSem, 10) === semFilter;
          } else {
            semMatch = true; // do not exclude when metadata is missing
          }

          if (status !== 'planned' || !required || !ylMatch || !semMatch) return;

          var subjCode = normalizeCode(it.subject.code);
          var subjId = null;
          if (it.intSubjectID !== undefined && it.intSubjectID !== null) {
            subjId = parseInt(it.intSubjectID, 10);
          } else if (it.subject && it.subject.id !== undefined && it.subject.id !== null) {
            subjId = parseInt(it.subject.id, 10);
          }

          if (alreadyEnlistedBySubject[subjCode]) return;          

          var options = [];
          var sidKey = (subjId !== null && !isNaN(subjId)) ? String(subjId) : null;                    
          if (sidKey) {
            options = classlistsBySubjectId[sidKey] || [];            
          }

          // Apply optional Block Section filter
          if (vm.blockSection && ('' + vm.blockSection).trim() !== '') {
            var want = ('' + vm.blockSection).trim().toUpperCase();
            options = (options || []).filter(function (o) {
              var sec = (o && o.sectionCode != null) ? ('' + o.sectionCode).trim().toUpperCase() : '';
              return sec === want;
            });
          }

          // Prefer only sections with remaining slots > 0
          var available = (options || []).filter(function (o) {
            if (o && o.remaining_slots !== undefined && o.remaining_slots !== null) {
              var n = parseInt(o.remaining_slots, 10);
              return isNaN(n) ? true : n > 0;
            }
            return true;
          });
          if (!available.length) return;

          // Choose a section to queue:
          // - Prefer the one with the highest remaining_slots (when numeric)
          // - Fallback to the first available option
          var chosen = null;
          try {
            var withNumericRem = available.filter(function (o) {
              var n = parseInt(o && o.remaining_slots, 10);
              return !isNaN(n);
            });
            if (withNumericRem.length) {
              withNumericRem.sort(function (a, b) {
                var an = parseInt(a.remaining_slots, 10) || 0;
                var bn = parseInt(b.remaining_slots, 10) || 0;
                return bn - an; // descending
              });
              chosen = withNumericRem[0];
            } else {
              chosen = available[0];
            }
          } catch (e) {
            chosen = available[0];
          }

          var cid = chosen && chosen.intID ? parseInt(chosen.intID, 10) : 0;
          if (!cid) return;

          // Skip if already queued
          var exists = (vm.ops || []).some(function (op) {
            return op && op.type === 'add' && parseInt(op.classlist_id, 10) === cid;
          });
          if (exists) return;

          candidateItems.push({
            item: it,
            chosen: chosen,
            cid: cid,
            subjId: subjId,
            subjCode: subjCode
          });
        });

        // Second pass: check prerequisites for all candidates
        if (candidateItems.length === 0) {
          if (window.Swal) {
            Swal.fire({
              icon: 'info',
              title: 'No subjects queued',
              text: 'No planned checklist items matched available sections.'
            });
          }
          vm.loading = false;
          return;
        }

        // Collect all subject IDs for batch prerequisite checking
        var subjectIds = candidateItems.map(function(candidate) {
          return candidate.subjId;
        }).filter(function(id) {
          return id !== null && !isNaN(id);
        });

        if (subjectIds.length === 0) {
          // No valid subject IDs, add all without prerequisite checking
          candidateItems.forEach(function(candidate) {
            vm.ops.push({
              type: 'add',
              classlist_id: candidate.cid,
              subjectCode: candidate.chosen.subjectCode || candidate.chosen.strCode || candidate.item.subject.code,
              sectionCode: candidate.chosen.sectionCode || ''
            });
          });
          
          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Queued from Checklist',
              text: 'Added ' + candidateItems.length + ' subject(s) to the queue.'
            });
          }
          vm.loading = false;
          return;
        }

        // Batch check prerequisites
        $http.post(APP_CONFIG.API_BASE + '/subjects/check-prerequisites-batch', {
          student_number: vm.sanitizeStudentNumber(vm.studentNumber),
          subject_ids: subjectIds
        }).then(function(resp) {
          var prerequisiteResults = (resp && resp.data && resp.data.data) ? resp.data.data : {};
          var adds = 0;
          var skipped = 0;
          var skippedSubjects = [];

          candidateItems.forEach(function(candidate) {
            var prerequisiteCheck = prerequisiteResults[candidate.subjId] || { passed: true, missing_prerequisites: [] };
            
            if (prerequisiteCheck.passed) {
              // Prerequisites satisfied, add to queue
              vm.ops.push({
                type: 'add',
                classlist_id: candidate.cid,
                subjectCode: candidate.chosen.subjectCode || candidate.chosen.strCode || candidate.item.subject.code,
                sectionCode: candidate.chosen.sectionCode || '',
                prerequisite_check: prerequisiteCheck
              });
              adds += 1;
            } else {
              // Prerequisites not satisfied, skip this subject
              skipped += 1;
              var missingCodes = prerequisiteCheck.missing_prerequisites.map(function(p) { return p.code; });
              skippedSubjects.push({
                code: candidate.item.subject.code,
                missing: missingCodes
              });
            }
          });

          // Show results
          if (window.Swal) {
            var message = '';
            if (adds > 0) {
              message += 'Added ' + adds + ' subject(s) to the queue.';
            }
            if (skipped > 0) {
              message += (adds > 0 ? '\n\n' : '') + 'Skipped ' + skipped + ' subject(s) due to missing prerequisites:\n';
              skippedSubjects.forEach(function(subj) {
                message += '• ' + subj.code + ' (missing: ' + subj.missing.join(', ') + ')\n';
              });
            }
            
            Swal.fire({
              icon: adds > 0 ? (skipped > 0 ? 'warning' : 'success') : 'info',
              title: adds > 0 ? 'Queued from Checklist' : 'No subjects queued',
              text: message || 'No subjects could be queued.',
              showConfirmButton: true
            });
          }
        }).catch(function(err) {
          console.error('Error checking prerequisites for auto-queue:', err);
          // On error, add all subjects without prerequisite checking
          candidateItems.forEach(function(candidate) {
            vm.ops.push({
              type: 'add',
              classlist_id: candidate.cid,
              subjectCode: candidate.chosen.subjectCode || candidate.chosen.strCode || candidate.item.subject.code,
              sectionCode: candidate.chosen.sectionCode || ''
            });
          });
          
          if (window.Swal) {
            Swal.fire({
              icon: 'warning',
              title: 'Queued from Checklist',
              text: 'Added ' + candidateItems.length + ' subject(s) to the queue. (Prerequisite checking failed)'
            });
          }
        }).finally(function() {
          vm.loading = false;
          if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
        });
      }).catch(function(err) {
        console.error('Error loading checklist:', err);
        vm.loading = false;
        if (window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load checklist data.'
          });
        }
      });
    }
 
    function generateChecklist() {
      try {
        if (!vm.studentNumber) {
          if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Select a student', text: 'Please select a student first.' });
          } else {
            try { alert('Please select a student first.'); } catch (e) {}
          }
          return;
        }

        var sid = null;
        if (vm.student_id) {
          sid = vm.student_id;
        } else {
          try {
            var sel = (vm.students || []).find(function (s) { return s && s.student_number === vm.studentNumber; });
            if (sel && sel.id) sid = sel.id;
          } catch (e) {}
        }

        if (!sid) {
          if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Student not found', text: 'Unable to resolve student id for checklist.' });
          } else {
            try { alert('Unable to resolve student id for checklist.'); } catch (e) {}
          }
          return;
        }

        vm.clGenLoading = true;
        ChecklistService.generate(sid, {}).then(function (res) {
          vm.hasChecklist = true;
          if ($scope && $scope.$applyAsync) { try { $scope.$applyAsync(); } catch (e) {} }
          if (window.Swal) {
            Swal.fire({ icon: 'success', title: 'Checklist generated', text: 'Graduation checklist was generated from curriculum.' });
          }
        }).catch(function (err) {
          console.error('checklist.generate failed', err);
          var m = (err && err.data && err.data.message) || 'Failed to generate checklist.';
          if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Error', text: m });
          } else {
            try { alert(m); } catch (e) {}
          }
        }).finally(function () {
          vm.clGenLoading = false;
          if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
        });
      } catch (e) {}
    }
 
     // Load students for searchable dropdown (aggregate all pages)
     function loadStudents() {
      vm.loading = true;
      vm.students = [];
      var perPage = 100; // backend caps per_page at 100
      var acc = [];

      function normalizeRows(rows) {
        return rows.map(function (r) {
          return {
            id: r.id || r.intID || null,
            student_number: r.student_number || r.strStudentNumber || '',
            last_name: r.last_name || r.strLastname || r.lastName || '',
            first_name: r.first_name || r.strFirstname || r.firstName || '',
            middle_name: r.middle_name || r.strMiddlename || r.middleName || ''
          };
        });
      }

      function fetchPage(page) {
        var params = { per_page: perPage, page: page };
        return $http.get(APP_CONFIG.API_BASE + '/students', { params: params }).then(function (resp) {
          var data = (resp && resp.data) ? resp.data : {};
          var rows = data && data.data ? data.data : (Array.isArray(data) ? data : []);
          var meta = data && data.meta ? data.meta : {};
          var total = meta && meta.total ? meta.total : (acc.length + rows.length);

          acc = acc.concat(rows);

          if (acc.length < total && rows.length > 0) {
            return fetchPage(page + 1);
          } else {
            vm.students = normalizeRows(acc);
          }
        });
      }

      return fetchPage(1).finally(function () {
        vm.loading = false;
        try { if (vm.studentNumber) { setSelectedStudentName(); } } catch (e) {}
      });
    }

    // Remote query hook for pui-autocomplete: fetch suggestions based on user input
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

    // Load current enlisted subjects for student/term
    function loadCurrent() {
      vm.results = null;
      vm.current = [];
      if (!vm.term) return Promise.resolve();
 
      // Ensure we have student id before requesting records for term
      return resolveStudentIdIfNeeded().then(function (sid) {
        if (!sid) return;

        vm.loading = true;
        return $http.post(BASE + '/student/records-by-term', {
          student_id: sid,
          term: ('' + vm.term),
          include_grades: false
        }).then(function (resp) {
          var data = (resp && resp.data) ? resp.data : resp;
          var payload = data && data.data ? data.data : {};
          var terms = payload.terms || [];
          // Persist student_id from payload if provided
          if (payload && payload.student_id) vm.student_id = payload.student_id;
          var first = terms.length ? terms[0] : null;
          var recs = first ? (first.records || []) : [];
          // Normalize minimal fields for display and drop selection
          vm.current = recs.map(function (r) {
            return {
              classlist_id: r.classlist_id || r.classListId || r.classListID || r.classlistID || null,
              code: r.code,
              description: r.description,
              units: r.units,
              section_code: r.section_code || r.sectionCode || '',
              subject_id: (function () {
                var sid = r.subject_id || r.subjectId || r.subjectID || null;
                if (sid === null || sid === undefined || ('' + sid).trim() === '') return null;
                var n = parseInt(sid, 10);
                return isNaN(n) ? null : n;
              })()
            };
          }).filter(function (x) { return x.classlist_id !== null; });
        }).finally(function () {
          vm.loading = false;
          // Auto-load tuition after current is loaded (if possible)
          try { vm.loadTuition(); } catch (e) {}
        });
      }).finally(function () {
        // Refresh classlists for term for "Add" and "Change To" selections
        loadClasslistsForTerm();
      });      
    }

    // Load classlists for selected term (for add/change UI)
    function loadClasslistsForTerm() {
      vm.sections = [];
      vm.subjects = [];
      if (!vm.term) return;

      // Use existing service for subjects by term (registrar grading meta source)
      ClasslistsService.getSubjectsByTerm(('' + vm.term)).then(function (res) {
        vm.subjects = res && res.data ? res.data : [];
      });

      // Helper: fetch sections slots utilization and merge remaining slots into vm.sections
      function fetchSlotsAndMerge() {
        var acc = [];
        var page = 1;
        var perPage = 200;

        function next() {
          return SectionsSlotsService.list({ term: ('' + vm.term), page: page, perPage: perPage }).then(function (res) {
            var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
            var meta = (res && res.meta) ? res.meta : null;
            acc = acc.concat(rows || []);
            var hasMore = false;
            if (meta && meta.total && meta.per_page && meta.page) {
              hasMore = (meta.page * meta.per_page) < meta.total;
            } else {
              hasMore = (rows || []).length === perPage;
            }
            if (hasMore) {
              page += 1;
              return next();
            }
          }).finally(function () {
            try {
              var byId = {};
              (acc || []).forEach(function (r) {
                if (r && r.classlist_id != null) {
                  var id = parseInt(r.classlist_id, 10);
                  if (!isNaN(id)) byId[id] = r;
                }
              });
              (vm.sections || []).forEach(function (s) {
                var id = parseInt(s.intID, 10);
                var row = byId[id];
                if (row) {
                  s.slots = row.slots;
                  s.enlisted_count = row.enlisted_count;
                  s.enrolled_count = row.enrolled_count;
                  s.remaining_slots = row.remaining_slots;
                }
                var subj = s.subjectCode || s.strCode || '';
                var sect = s.sectionCode || '';
                var remVal = (s.remaining_slots !== undefined && s.remaining_slots !== null) ? ('' + s.remaining_slots) : null;
                var extra = remVal !== null ? (' — rem: ' + remVal) : '';
                s.display = subj + (sect ? (' — ' + sect) : '') + extra;
              });
            } catch (e) {}
          });
        }
        return next();
      }

      // List all classlists for the term for add/change target (no pagination)
      ClasslistsService.listAll({ term: ('' + vm.term) }).then(function (res) {
        var rows = res && res.data ? res.data : (Array.isArray(res) ? res : []);
        vm.sections = rows.map(function (cl) {
          // widen fallbacks to handle different API shapes
          var subjCode = cl.subjectCode || cl.subject_code || cl.strCode || cl.code || '';
          var sectCode = cl.SectionCode || cl.sectionCode || cl.section_code || cl.strSectionCode || cl.strSection || '';
          return {
            intID: cl.intID || cl.id,
            strDescription: cl.strDescription || cl.description,
            // normalize subject code and section code fields for downstream usage
            subjectCode: subjCode,
            strCode: subjCode,
            sectionCode: sectCode,
            subject_id: cl.intSubjectID || cl.subject_id || cl.subjectId || cl.subject_id_id || null,
            display: subjCode + (sectCode ? (' — ' + sectCode) : '')
          };
        });

        // Build Block Section list from available classlists
        try {
          var uniq = {};
          (vm.sections || []).forEach(function (s) {
            var code = (s && s.sectionCode != null) ? ('' + s.sectionCode).trim() : '';
            if (code) {
              var key = code.toUpperCase();
              if (!uniq[key]) uniq[key] = code;
            }
          });
          vm.blockSections = Object.keys(uniq).sort().map(function (k) { return uniq[k]; });
        } catch (e) {
          vm.blockSections = [];
        }

        // Merge slots info and update display with remaining slots
        fetchSlotsAndMerge();
      });
      
    }

    // Auto-load by checklist-provided year/sem (supports new intYearLevel/intSem and legacy YearStart/YearEnd/Sem)
    function autoLoadByChecklist() {
      try {
        // Helper: set selected term and optionally refresh lists
        function applyTerm(termObj) {
          if (!termObj || !termObj.intID) return;
          if (TermService && TermService.setSelectedTerm) {
            TermService.setSelectedTerm(termObj);
          } else {
            vm.selectedTerm = termObj;
            vm.term = termObj.intID;
            if (vm.studentNumber) {
              loadCurrent();
            }
            loadClasslistsForTerm();
          }
        }
        // Helper: normalize enumSem label
        function normalizeSemLabel(x) {
          var s = ('' + x).trim();
          return s;
        }
        // Helper: map sem int to label (per provided mapping)
        function mapSemIntToLabel(n) {
          var i = parseInt(n, 10);
          if (i === 1) return '1st';
          if (i === 2) return '2nd';
          if (i === 3) return '3rd';
          if (i === 4) return '4th';
          if (i === 5) return 'Summer';
          return null;
        }
        // Helper: choose latest term among candidates (by strYearStart then strYearEnd desc)
        function pickLatestByYear(candidates) {
          if (!Array.isArray(candidates) || !candidates.length) return null;
          var sorted = candidates.slice().sort(function (a, b) {
            var ayA = parseInt(a.strYearStart, 10) || 0;
            var ayB = parseInt(b.strYearStart, 10) || 0;
            if (ayA !== ayB) return ayB - ayA;
            var byA = parseInt(a.strYearEnd, 10) || 0;
            var byB = parseInt(b.strYearEnd, 10) || 0;
            return byB - byA;
          });
          return sorted[0];
        }

        // Prefer new checklist-style inputs
        var hasNewInputs = (('' + vm.clSemInt).trim() !== '') || (('' + vm.clYearLevel).trim() !== '');
        if (hasNewInputs) {
          // Set year level if provided and valid (1..6)
          var yl = parseInt(vm.clYearLevel, 10);
          if (!isNaN(yl) && yl >= 1 && yl <= 6) {
            vm.yearLevel = yl;
          }
          // Map sem int to enumSem label
          var semLabel = mapSemIntToLabel(vm.clSemInt);
          if (!semLabel) return;

          // Find matching term by enumSem only; pick latest by academic year
          var list = (TermService && TermService.availableTerms) ? TermService.availableTerms : [];
          var proceedWithList = function (terms) {
            var candidates = (terms || []).filter(function (t) {
              return (normalizeSemLabel(t.enumSem).toLowerCase() === semLabel.toLowerCase());
            });
            var chosen = pickLatestByYear(candidates);
            if (chosen) {
              applyTerm(chosen);
            }
          };

          if (Array.isArray(list) && list.length) {
            proceedWithList(list);
          } else {
            vm.loading = true;
            $http.get(APP_CONFIG.API_BASE + '/generic/terms').then(function (resp) {
              var data = (resp && resp.data) ? resp.data : resp;
              var terms = (data && data.data) ? data.data : (Array.isArray(data) ? data : []);
              try { if (TermService) { TermService.availableTerms = terms; } } catch (e) {}
              proceedWithList(terms);
            }).finally(function () { vm.loading = false; });
          }
          return;
        }

        // Legacy path: Year Start / Year End / Sem string
        var ys = ('' + vm.clYearStart).trim();
        var ye = ('' + vm.clYearEnd).trim();
        var semIn = ('' + vm.clSem).trim();
        if (!ys || !ye || !semIn) return;

        function normalizeLegacySem(x) {
          var s = ('' + x).toLowerCase().trim().replace(/[^a-z0-9]/g, '');
          if (s === '1' || s === 'first' || s === '1st') return '1st';
          if (s === '2' || s === 'second' || s === '2nd') return '2nd';
          if (s === '3' || s === 'third' || s === '3rd') return '3rd';
          if (s === '4' || s === 'fourth' || s === '4th') return '4th';
          if (s.indexOf('sum') === 0) return 'Summer';
          return ('' + x).trim();
        }
        function semEq(a, b) {
          return normalizeLegacySem(a).toLowerCase() === normalizeLegacySem(b).toLowerCase();
        }

        var listLegacy = (TermService && TermService.availableTerms) ? TermService.availableTerms : [];
        var match = null;
        if (angular.isArray(listLegacy) && listLegacy.length) {
          match = listLegacy.find(function (t) {
            return ('' + t.strYearStart) === ys && ('' + t.strYearEnd) === ye && semEq(t.enumSem, semIn);
          });
        }
        if (match) {
          applyTerm(match);
          return;
        }
        vm.loading = true;
        $http.get(APP_CONFIG.API_BASE + '/generic/terms').then(function (resp) {
          var data = (resp && resp.data) ? resp.data : resp;
          var terms = (data && data.data) ? data.data : (Array.isArray(data) ? data : []);
          try { if (TermService) { TermService.availableTerms = terms; } } catch (e) {}
          var found = terms.find(function (t) {
            return ('' + t.strYearStart) === ys && ('' + t.strYearEnd) === ye && semEq(t.enumSem, semIn);
          });
          if (found) {
            applyTerm(found);
          }
        }).finally(function () {
          vm.loading = false;
        });
      } catch (e) {
        // swallow
      }
    }

    // Filter helpers: hide items already queued
    vm.filteredSections = function () {
      var excluded = {};
      (vm.ops || []).forEach(function (op) {
        if (op && op.type === 'add' && op.classlist_id) {
          excluded[parseInt(op.classlist_id, 10)] = true;
        }
      });
      return (vm.sections || []).filter(function (s) {
        var id = parseInt(s.intID, 10);
        return !excluded[id];
      });
    };

    vm.filteredCurrent = function () {
      var excluded = {};
      (vm.ops || []).forEach(function (op) {
        if (op && op.type === 'drop' && op.classlist_id) {
          excluded[parseInt(op.classlist_id, 10)] = true;
        }
      });
      return (vm.current || []).filter(function (c) {
        var id = parseInt(c.classlist_id, 10);
        return !excluded[id];
      });
    };

    // Remaining slots helpers
    function selectedAddRemaining() {
      try {
        var cid = parseInt(vm.selectedAddClasslistId, 10);
        if (!cid) return null;
        var sel = (vm.sections || []).find(function (s) { return parseInt(s.intID, 10) === cid; });
        if (!sel) return null;
        if (sel.remaining_slots === undefined || sel.remaining_slots === null) return null;
        var n = parseInt(sel.remaining_slots, 10);
        return isNaN(n) ? null : n;
      } catch (e) { return null; }
    }
    function selectedChangeToRemaining() {
      try {
        var toId = parseInt(vm.changeToId, 10);
        if (!toId) return null;
        var sel = (vm.sections || []).find(function (s) { return parseInt(s.intID, 10) === toId; });
        if (!sel) return null;
        if (sel.remaining_slots === undefined || sel.remaining_slots === null) return null;
        var n = parseInt(sel.remaining_slots, 10);
        return isNaN(n) ? null : n;
      } catch (e) { return null; }
    }

    // Queue operations
    function queueAdd() {
      var cid = parseInt(vm.selectedAddClasslistId, 10);
      if (!cid) return;
      
      // prevent duplicate add of the same classlist
      var exists = (vm.ops || []).some(function (op) { return op.type === 'add' && parseInt(op.classlist_id, 10) === cid; });
      if (exists) return;
      
      var sel = (vm.sections || []).find(function (s) { return parseInt(s.intID, 10) === cid; });
      var subjCode = sel ? (sel.subjectCode || sel.strCode || '') : '';
      var sectCode = sel ? (sel.sectionCode || '') : '';
      
      // Prevent queuing when section is full (remaining slots <= 0)
      if (sel && sel.remaining_slots !== undefined && sel.remaining_slots !== null && parseInt(sel.remaining_slots, 10) <= 0) {
        if (window.Swal) {
          Swal.fire({ icon: 'warning', title: 'Section full', text: 'No remaining slots. Cannot queue this section.' });
        } else {
          try { alert('Section is full. Cannot queue this section.'); } catch (e) {}
        }
        return;
      }

      // Check prerequisites before adding to queue
      if (vm.studentNumber && sel && sel.subject_id) {
        vm.loading = true;
        checkPrerequisites(sel.subject_id, function(prerequisiteCheck) {
          vm.loading = false;
          
          if (!prerequisiteCheck.passed) {
            // Show prerequisite warning
            var missingCodes = prerequisiteCheck.missing_prerequisites.map(function(p) { return p.code; });
            var message = 'Prerequisites not satisfied for ' + subjCode + '.\n\nMissing: ' + missingCodes.join(', ');
            
            if (window.Swal) {
              Swal.fire({
                icon: 'warning',
                title: 'Prerequisites Required',
                html: 'Prerequisites not satisfied for <strong>' + subjCode + '</strong>.<br><br>' +
                      'Missing: <strong>' + missingCodes.join(', ') + '</strong><br><br>' +
                      'Do you want to add it anyway?',
                showCancelButton: true,
                confirmButtonText: 'Add Anyway',
                cancelButtonText: 'Cancel'
              }).then(function(result) {
                if (result.isConfirmed) {
                  addToQueue(cid, subjCode, sectCode, prerequisiteCheck);
                }
              });
            } else {
              if (confirm(message + '\n\nDo you want to add it anyway?')) {
                addToQueue(cid, subjCode, sectCode, prerequisiteCheck);
              }
            }
          } else {
            // Prerequisites satisfied, add directly
            addToQueue(cid, subjCode, sectCode, prerequisiteCheck);
          }
        });
      } else {
        // No student selected or no subject ID, add without checking
        addToQueue(cid, subjCode, sectCode, null);
      }
    }
    
    function addToQueue(cid, subjCode, sectCode, prerequisiteCheck) {
      vm.ops.push({ 
        type: 'add', 
        classlist_id: cid, 
        subjectCode: subjCode, 
        sectionCode: sectCode,
        prerequisite_check: prerequisiteCheck
      });
      vm.selectedAddClasslistId = '';
    }
    
    function checkPrerequisites(subjectId, callback) {
      if (!vm.studentNumber || !subjectId) {
        callback({ passed: true, missing_prerequisites: [] });
        return;
      }
      
      $http.post(APP_CONFIG.API_BASE + '/subjects/' + subjectId + '/check-prerequisites', {
        student_number: vm.sanitizeStudentNumber(vm.studentNumber), student_id: vm.student_id
      }).then(function(resp) {
        var data = (resp && resp.data && resp.data.data) ? resp.data.data : { passed: true, missing_prerequisites: [] };
        callback(data);
      }).catch(function(err) {
        console.error('Error checking prerequisites:', err);
        callback({ passed: true, missing_prerequisites: [] }); // Allow on error
      });
    }

    function queueDrop() {
      var cid = parseInt(vm.selectedDropClasslistId, 10);
      if (!cid) return;
      // prevent duplicate drop of the same classlist
      var exists = (vm.ops || []).some(function (op) { return op.type === 'drop' && parseInt(op.classlist_id, 10) === cid; });
      if (exists) return;
      var cur = (vm.current || []).find(function (c) { return parseInt(c.classlist_id, 10) === cid; });
      var subjCode = cur ? (cur.code || '') : '';
      var sectCode = cur ? (cur.section_code || '') : '';
      vm.ops.push({ type: 'drop', classlist_id: cid, subjectCode: subjCode, sectionCode: sectCode });
      vm.selectedDropClasslistId = '';
    }

    function queueChange() {
      var fromId = parseInt(vm.changeFromId, 10);
      var toId = parseInt(vm.changeToId, 10);
      if (!fromId || !toId) return;
      var fromCur = (vm.current || []).find(function (c) { return parseInt(c.classlist_id, 10) === fromId; });
      var toSec = (vm.sections || []).find(function (s) { return parseInt(s.intID, 10) === toId; });
      var fromSubj = fromCur ? (fromCur.code || '') : '';
      var fromSect = fromCur ? (fromCur.section_code || '') : '';
      var toSubj = toSec ? (toSec.subjectCode || toSec.strCode || '') : '';
      var toSect = toSec ? (toSec.sectionCode || '') : '';
      // Prevent queuing change when target section is full (remaining slots <= 0)
      if (toSec && toSec.remaining_slots !== undefined && toSec.remaining_slots !== null && parseInt(toSec.remaining_slots, 10) <= 0) {
        if (window.Swal) {
          Swal.fire({ icon: 'warning', title: 'Section full', text: 'No remaining slots. Cannot queue change to this section.' });
        } else {
          try { alert('Section is full. Cannot queue change to this section.'); } catch (e) {}
        }
        return;
      }
      vm.ops.push({
        type: 'change_section',
        from_classlist_id: fromId,
        to_classlist_id: toId,
        fromSubjectCode: fromSubj,
        fromSectionCode: fromSect,
        toSubjectCode: toSubj,
        toSectionCode: toSect
      });
      vm.changeFromId = '';
      vm.changeToId = '';
    }

    function removeOpAt(idx) {
      if (idx >= 0 && idx < vm.ops.length) {
        vm.ops.splice(idx, 1);
      }
    }

    function clearOps() {
      vm.ops = [];
    }

    // Submit to API
    function submit() {
      if (!vm.studentNumber || !vm.term || !vm.yearLevel || vm.ops.length === 0) return;

      vm.loading = true;
      vm.results = null;

      var payload = {
        student_number: vm.sanitizeStudentNumber(vm.studentNumber),
        term: parseInt(vm.term, 10),
        year_level: parseInt(vm.yearLevel, 10),
        student_type: vm.studentType || 'continuing',
        operations: angular.copy(vm.ops)
      };

      UnityService.enlist(payload).then(function (res) {
        // res is already unwrapped
        vm.results = res;
 
        // Invalidate tuition cache and clear preview before recompute
        vm._tuitionKey = null;
        vm.tuition = null;
 
        // Clear queued ops early to reflect submitted state
        vm.ops = [];
 
        // Chain refreshes to ensure tuition computes from updated data
        return loadCurrent()
          .then(function () { return loadRegistration(); })
          .then(function () { try { vm.loadTuition(true); } catch (e) {} });
      }).finally(function () {
        vm.loading = false;
        if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
      });
    }

    // Reset registration flow (SweetAlert2 password confirm, then call API)
    function resetRegistration() {
      if (!vm.studentNumber) {
        if (window.Swal) {
          Swal.fire({
            icon: 'warning',
            title: 'Select a student',
            text: 'Please select a student first.'
          });
        } else {
          try { alert('Please select a student first.'); } catch (e) {}
        }
        return;
      }
 
      var termInt = vm.term ? parseInt(vm.term, 10) : null;
      var title = 'Reset registration';
      var text = 'This will delete all enlisted classes and the registration for ' + vm.studentNumber +
        (termInt ? (' (term ' + termInt + ')') : '') + '.';
 
      if (window.Swal) {
        Swal.fire({
          title: title,
          text: text,
          input: 'password',
          inputLabel: 'Enter your password to confirm',
          inputAttributes: { autocapitalize: 'off', autocomplete: 'new-password' },
          showCancelButton: true,
          confirmButtonText: 'Confirm Reset',
          cancelButtonText: 'Cancel',
          preConfirm: function (value) {
            if (!value) {
              Swal.showValidationMessage('Password is required');
            }
            return value;
          }
        }).then(function (result) {
          if (!result.isConfirmed) return;
          var pw = result.value;
 
          vm.loading = true;
          var payload = { student_number: vm.sanitizeStudentNumber(vm.studentNumber)};
          if (termInt) payload.term = termInt;
          payload.password = pw;
 
          UnityService.resetRegistration(payload).then(function (res) {
            // Refresh current state after reset
            loadCurrent();
            // Success alert with counts
            try {
              var d = (res && res.data && res.data.deleted) || (res && res.deleted) || {};
              var cls = d && d.classlist_student_rows ? d.classlist_student_rows : 0;
              var regs = d && d.registrations ? d.registrations : 0;
              Swal.fire({
                icon: 'success',
                title: 'Reset completed',
                text: 'Deleted ' + cls + ' classlist row(s) and ' + regs + ' registration row(s).'
              });
            } catch (e) {
              Swal.fire({ icon: 'success', title: 'Reset completed' });
            }
          }, function (err) {
            var m = (err && err.data && err.data.message) || 'Reset failed';
            Swal.fire({ icon: 'error', title: 'Reset failed', text: m });
          }).finally(function () {
            vm.loading = false;
            if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
          });
        });
      } else {
        // Fallback to native prompt/alert if SweetAlert2 is unavailable
        var pw = null;
        try {
          pw = window.prompt(text + '\nTo confirm, enter your password:', '');
        } catch (e) {
          pw = '';
        }
        if (pw === null) return; // cancelled
 
        vm.loading = true;
        var payload = { student_number: vm.sanitizeStudentNumber(vm.studentNumber) };
        if (termInt) payload.term = termInt;
        payload.password = pw;
 
        UnityService.resetRegistration(payload).then(function (res) {
          loadCurrent();
          try {
            var d = (res && res.data && res.data.deleted) || (res && res.deleted) || {};
            var cls = d && d.classlist_student_rows ? d.classlist_student_rows : 0;
            var regs = d && d.registrations ? d.registrations : 0;
            alert('Reset completed. Deleted ' + cls + ' classlist row(s) and ' + regs + ' registration row(s).');            
          } catch (e) {}
        }, function (err) {
          var m = (err && err.data && err.data.message) || 'Reset failed';
          alert(m);
        }).finally(function () {
          vm.loading = false;
          
        });
      }
    }
    // Registration Details: helpers (moved inside controller to capture vm and injected services)
    function loadPrograms() {
      try {
        ProgramsService.list({ enabledOnly: false }).then(function (res) {
          var rows = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
          // Normalize to a consistent shape so labels render properly
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
      } catch (e) { /* ignore */ }
    }

    function loadCurricula(programId) {
      var opts = {};
      if (programId !== undefined && programId !== null && ('' + programId) !== '') {
        opts.program_id = programId;
      }
      CurriculaService.list(opts).then(function (res) {
        vm.curricula = (res && res.data) ? res.data : (Array.isArray(res) ? res : []);
      });
    }

    // Build a display label for Programs regardless of backend shape
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

    // Load tuition years for dropdown (read-only list)
    function loadTuitionYears() {
      try {
        $http.get(APP_CONFIG.API_BASE + '/tuition-years').then(function (resp) {
          var data = (resp && resp.data) ? resp.data : resp;
          var rows = (data && data.data) ? data.data : (Array.isArray(data) ? data : []);
          vm.tuitionYears = rows.map(function (r) {
            return {
              intID: r.intID || r.id,
              year: r.year || r.sy || (r.strLabel || ''),
              label: (r.year || r.sy || (r.strLabel || '')) + ''
            };
          });
        });
      } catch (e) { /* ignore */ }
    }

    function loadRegistration() {
      if (!vm.studentNumber || !vm.term) {
        vm.registration = null;
        vm.regForm = {};
        return;
      }
      vm.regLoading = true;
      // Prefer student_id when available; fallback to sanitized student_number
      var termInt = parseInt(vm.term, 10);
      var sn = vm.sanitizeStudentNumber(vm.studentNumber);
      var getRegPromise = (vm.student_id && !isNaN(parseInt(vm.student_id, 10)))
        ? UnityService.getRegistrationById(parseInt(vm.student_id, 10), termInt)
        : UnityService.getRegistration(sn, termInt);
      return getRegPromise.then(function (res) {
        // UnityService returns unwrapped payload: { success, data: { exists, registration } }
        var exists = res && res.data && res.data.exists;
        var row = res && res.data ? res.data.registration : null;
        vm.registration = exists ? row : null;
        resetRegForm();
        if (vm.regForm.current_program) {
          vm.loadCurricula(vm.regForm.current_program);
        } else {
          vm.curricula = [];
        }
      }).catch(function (err) {
        console.error('loadRegistration failed', err);
        vm.registration = null;
        vm.regForm = {};
      }).finally(function () {
        vm.regLoading = false;
        // Auto-load tuition when registration changed (if possible)
        try { vm.loadTuition(); } catch (e) {}
        if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
      });
    }

    function resetRegForm() {
      if (!vm.registration) {
        vm.regForm = {};
        vm._regBaseline = {};
        return;
      }
      vm.regForm = {
        intYearLevel: vm.registration.intYearLevel ? parseInt(vm.registration.intYearLevel, 10) : (vm.yearLevel ? parseInt(vm.yearLevel, 10) : 1),
        enumStudentType: vm.registration.enumStudentType || (vm.studentType || 'continuing'),
        current_program: (vm.registration.current_program !== undefined) ? vm.registration.current_program : null,
        current_curriculum: (vm.registration.current_curriculum !== undefined) ? vm.registration.current_curriculum : null,
        tuition_year: (vm.registration.tuition_year !== undefined) ? vm.registration.tuition_year : null,
        paymentType: vm.registration.paymentType || '',
        loa_remarks: vm.registration.loa_remarks || '',
        withdrawal_period: (function(w){
          if (w === null || w === undefined || w === '') return '';
          if (typeof w === 'string') return ('' + w).toLowerCase();
          var n = parseInt(w, 10);
          if (n === 0) return 'before';
          if (n === 1) return 'start';
          if (n === 2) return 'end';
          return '';
        })(vm.registration.withdrawal_period)
      };
      vm._regBaseline = angular.copy(vm.regForm);
    }

    function isRegDirty() {
      try {
        return JSON.stringify(vm.regForm) !== JSON.stringify(vm._regBaseline);
      } catch (e) { return true; }
    }

    function saveRegistration() {
      if (!vm.registration || !vm.studentNumber || !vm.term) return;
      vm.regSaving = true;

      // Build fields, omitting empty-string values to avoid validation errors
      var fields = {};
      function setIfPresent(key, value) {
        if (value === undefined || value === null) return;
        if (typeof value === 'string' && value.trim() === '') return;
        fields[key] = value;
      }
      setIfPresent('intYearLevel', vm.regForm.intYearLevel);
      setIfPresent('enumStudentType', vm.regForm.enumStudentType);
      setIfPresent('current_program', vm.regForm.current_program);
      setIfPresent('current_curriculum', vm.regForm.current_curriculum);
      setIfPresent('tuition_year', vm.regForm.tuition_year);
      setIfPresent('paymentType', vm.regForm.paymentType);
      setIfPresent('loa_remarks', vm.regForm.loa_remarks);
      // Normalize empty string to null for withdrawal_period; backend allows nullable|string|in:before,start,end
      var wp = vm.regForm.withdrawal_period;
      if (wp === '') { wp = null; }
      // Explicitly include withdrawal_period even when null to allow clearing it
      fields['withdrawal_period'] = wp;

      UnityService.updateRegistration({
        student_number: vm.sanitizeStudentNumber(vm.studentNumber),
        term: parseInt(vm.term, 10),
        fields: fields
      }).then(function (res) {
        // UnityService returns unwrapped payload: { success, message, data: { updated, registration } }
        var reg = res && res.data ? res.data.registration : null;
        if (reg) {
          vm.registration = reg;
          resetRegForm();
          // Refresh tuition after saving registration (program may have changed)
          try { vm.loadTuition(true); } catch (e) {}
        }
        if (window.Swal) {
          Swal.fire({ icon: 'success', title: 'Saved', text: 'Registration updated.' });
        }
      }).catch(function (err) {
        var m = (err && err.data && err.data.message) || 'Save failed';
        if (window.Swal) {
          Swal.fire({ icon: 'error', title: 'Error', text: m });
        } else {
          try { alert(m); } catch (e) {}
        }
      }).finally(function () {
        vm.regSaving = false;
        if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
      });
    }
  }

})();
