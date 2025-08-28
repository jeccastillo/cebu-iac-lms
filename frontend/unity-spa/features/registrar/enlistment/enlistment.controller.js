(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('EnlistmentController', EnlistmentController);

  EnlistmentController.$inject = ['$http', 'APP_CONFIG', 'UnityService', 'ClasslistsService', 'TermService', 'ChecklistService', 'ProgramsService', 'CurriculaService', '$scope'];
  function EnlistmentController($http, APP_CONFIG, UnityService, ClasslistsService, TermService, ChecklistService, ProgramsService, CurriculaService, $scope) {
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

    // Registration details panel
    vm.loadRegistration = loadRegistration;
    vm.saveRegistration = saveRegistration;
    vm.resetRegForm = resetRegForm;
    vm.isRegDirty = isRegDirty;
    vm.loadPrograms = loadPrograms;
    vm.loadCurricula = loadCurricula;
    vm.loadTuitionYears = loadTuitionYears;
    vm.programLabel = programLabel;
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
          loadStudents();
          loadPrograms();
          loadTuitionYears();
        });
      } else {
        loadStudents();
        loadPrograms();
        loadTuitionYears();
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
            }
            
          }
        });
      }
    }

    function onStudentSelected() {
      if (vm.studentNumber) {
        setSelectedStudentName();
        loadCurrent();
        loadRegistration();
      } else {
        vm.selectedStudentName = '';
        vm.registration = null;
        vm.regForm = {};
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
          
          if (!options.length) return;
          
          // Choose the first available section (could be improved with section preferences)
          var chosen = options[0];
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
          student_number: vm.studentNumber,
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
        student_number: vm.studentNumber
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
      UnityService.getRegistration(vm.studentNumber, parseInt(vm.term, 10)).then(function (res) {
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
        student_number: vm.studentNumber,
        term: parseInt(vm.term, 10),
        fields: fields
      }).then(function (res) {
        // UnityService returns unwrapped payload: { success, message, data: { updated, registration } }
        var reg = res && res.data ? res.data.registration : null;
        if (reg) {
          vm.registration = reg;
          resetRegForm();
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
