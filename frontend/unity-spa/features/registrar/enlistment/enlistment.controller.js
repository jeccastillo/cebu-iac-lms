(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('EnlistmentController', EnlistmentController);

  EnlistmentController.$inject = ['$http', 'APP_CONFIG', 'UnityService', 'ClasslistsService', 'TermService', 'ChecklistService', '$scope'];
  function EnlistmentController($http, APP_CONFIG, UnityService, ClasslistsService, TermService, ChecklistService, $scope) {
    var vm = this;
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    // State
    vm.loading = false;
    vm.subjects = [];
    vm.sections = [];
    vm.results = null;

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
    vm.studentType = 'continuing';

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
          loadStudents();
        });
      } else {
        loadStudents();
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
            }
          }
        });
      }
    }

    function onStudentSelected() {
      if (vm.studentNumber) {
        setSelectedStudentName();
        loadCurrent();
      } else {
        vm.selectedStudentName = '';
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

    // Auto-queue from checklist for current year level and term
    function autoQueueFromChecklist() {
      if (!vm.studentNumber || !vm.term) {
        if (window.Swal) {
          Swal.fire({ icon: 'warning', title: 'Missing data', text: 'Select a student and term first.' });
        }
        return;
      }

      // Resolve student id by student number
      var student = (vm.students || []).find(function (s) { return s && s.student_number === vm.studentNumber; });
      if (!student || !student.id) {
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
      ChecklistService.get(student.id, {}).then(function (res) {
        var data = res && res.data ? res.data : res;
        var items = (data && data.items) ? data.items : [];
        var adds = 0;

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
          console.log(options);
          if (!options.length) return;
          console.log("Options Passed");
          // Choose the first available section (could be improved with section preferences)
          var chosen = options[0];
          var cid = chosen && chosen.intID ? parseInt(chosen.intID, 10) : 0;
          if (!cid) return;

          // Skip if already queued
          var exists = (vm.ops || []).some(function (op) {
            return op && op.type === 'add' && parseInt(op.classlist_id, 10) === cid;
          });
          if (exists) return;

          vm.ops.push({
            type: 'add',
            classlist_id: cid,
            subjectCode: chosen.subjectCode || chosen.strCode || it.subject.code,
            sectionCode: chosen.sectionCode || ''
          });
          adds += 1;
        });

        if (window.Swal) {
          Swal.fire({
            icon: adds > 0 ? 'success' : 'info',
            title: adds > 0 ? 'Queued from Checklist' : 'No subjects queued',
            text: adds > 0 ? ('Added ' + adds + ' subject(s) to the queue.') : 'No planned checklist items matched available sections.'
          });
        }
      }).finally(function () {
        vm.loading = false;
        if ($scope && $scope.$applyAsync) { $scope.$applyAsync(); }
      });
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

    // Load current enlisted subjects for student/term
    function loadCurrent() {
      vm.results = null;
      vm.current = [];
      if (!vm.studentNumber || !vm.term) return;

      vm.loading = true;
      $http.post(BASE + '/student/records-by-term', {
        student_number: vm.studentNumber,
        term: ('' + vm.term),
        include_grades: false
      }).then(function (resp) {
        var data = (resp && resp.data) ? resp.data : resp;
        var payload = data && data.data ? data.data : {};
        var terms = payload.terms || [];
        var first = terms.length ? terms[0] : null;
        var recs = first ? (first.records || []) : [];
        // Normalize minimal fields for display and drop selection
        vm.current = recs.map(function (r) {
          return {
            classlist_id: r.classlist_id || r.classListId || r.classListID || r.classlistID || null,
            code: r.code,
            description: r.description,
            units: r.units,
            section_code: r.section_code || r.sectionCode || ''
          };
        }).filter(function (x) { return x.classlist_id !== null; });
      }).finally(function () {
        vm.loading = false;
      });

      // Refresh classlists for term for "Add" and "Change To" selections
      loadClasslistsForTerm();
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
      vm.ops.push({ type: 'add', classlist_id: cid, subjectCode: subjCode, sectionCode: sectCode });
      vm.selectedAddClasslistId = '';
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
        student_number: vm.studentNumber,
        term: parseInt(vm.term, 10),
        year_level: parseInt(vm.yearLevel, 10),
        student_type: vm.studentType || 'continuing',
        operations: angular.copy(vm.ops)
      };

      UnityService.enlist(payload).then(function (res) {
        // res is already unwrapped
        vm.results = res;
        // Refresh current if success
        loadCurrent();
        // Clear queued ops
        vm.ops = [];
      }).finally(function () {
        vm.loading = false;
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
          var payload = { student_number: vm.studentNumber };
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
        var payload = { student_number: vm.studentNumber };
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
  }

})();
