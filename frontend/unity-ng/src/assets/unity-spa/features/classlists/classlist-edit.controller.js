(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClasslistEditController', ClasslistEditController);

  ClasslistEditController.$inject = ['$routeParams', '$location', 'ClasslistsService', 'TermService', 'CampusService'];
  function ClasslistEditController($routeParams, $location, ClasslistsService, TermService, CampusService) {
    var vm = this;

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;
    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.model = {
      intSubjectID: '',
      intFacultyID: '',
      strAcademicYear: '',
      strUnits: '',
      intFinalized: 0,
      campus_id: '',
      sectionCode: ''
    };

    // Derived, display-only label for term (e.g., "1 Semester 2024-2025")
    vm.termLabel = '';
    // Display-only campus name (used in Add mode)
    vm.campusName = '';

    vm.subjects = [];
    vm.faculty = [];
    vm.finalizedOptions = [
      { value: 0, label: 'Draft/0' },
      { value: 1, label: 'Midterm/1' },
      { value: 2, label: 'Final/2' }
    ];

    vm.init = init;
    vm.save = save;
    vm.cancel = cancel;

    function init() {
      vm.loading = true;
      vm.error = null;

      // Ensure term context initialized and get default term
      TermService.init()
        .then(function () {
          // Default academic year from selected term when creating
          var sel = TermService.getSelectedTerm();
          if (sel && sel.intID) {
            if (!vm.id) {
              vm.model.strAcademicYear = sel.intID;
            }
            // Compute display label for term
            vm.termLabel = formatTermLabel(sel);
          }

          // Initialize campus from global CampusService for Add mode
          var campusInit = (CampusService && CampusService.init) ? CampusService.init() : (Promise && Promise.resolve ? Promise.resolve() : { then: function(fn){ fn(); }});
          return campusInit.then(function () {
            if (!vm.id && CampusService && CampusService.getSelectedCampus) {
              var campus = CampusService.getSelectedCampus();
              if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
                vm.model.campus_id = parseInt(campus.id, 10);
                vm.campusName = campus.campus_name || campus.name || ('Campus #' + campus.id);
              }
            }
            return loadFilterOptions(sel && sel.intID ? sel.intID : null);
          });
        })
        .then(function () {
          if (vm.id) {
            return ClasslistsService.get(vm.id).then(function (data) {
              var row = (data && data.data) ? data.data : null;
              if (!row) {
                vm.error = 'Classlist not found';
                return;
              }
              vm.model.intSubjectID = row.intSubjectID || '';
              vm.model.intFacultyID = row.intFacultyID || '';
              vm.model.strAcademicYear = row.strAcademicYear || vm.model.strAcademicYear || '';
              vm.model.strUnits = row.strUnits || '';
              vm.model.intFinalized = (row.intFinalized !== undefined && row.intFinalized !== null) ? row.intFinalized : 0;
              vm.model.campus_id = (row.campus_id !== undefined && row.campus_id !== null) ? row.campus_id : '';
              vm.model.sectionCode = row.sectionCode || '';

              // Derive display label for the term based on the loaded record's strAcademicYear
              var terms = (TermService && TermService.availableTerms) ? TermService.availableTerms : [];
              var match = null;
              for (var i = 0; i < (terms ? terms.length : 0); i++) {
                var t = terms[i];
                if (t && ('' + t.intID) === ('' + vm.model.strAcademicYear)) { match = t; break; }
              }
              if (match) {
                vm.termLabel = formatTermLabel(match);
              } else {
                var sel2 = TermService.getSelectedTerm && TermService.getSelectedTerm();
                if (sel2 && ('' + sel2.intID) === ('' + vm.model.strAcademicYear)) {
                  vm.termLabel = formatTermLabel(sel2);
                } else if (!vm.termLabel) {
                  vm.termLabel = 'Term ID: ' + vm.model.strAcademicYear;
                }
              }
            });
          }
        })
        .catch(function (e) {
          console.error('ClasslistEdit init error:', e);
          vm.error = 'Failed to initialize form';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function loadFilterOptions(termId) {
      var p1 = ClasslistsService.getFacultyOptions()
        .then(function (data) {
          vm.faculty = (data && data.data) ? data.data : [];
        })
        .catch(function (e) {
          console.warn('Failed loading faculty options', e);
          vm.faculty = [];
        });

      var p2 = ClasslistsService.getSubjectsByTerm(termId)
        .then(function (data) {
          vm.subjects = (data && data.data) ? data.data : [];
        })
        .catch(function (e) {
          console.warn('Failed loading subject options', e);
          vm.subjects = [];
        });

      return Promise.all([p1, p2]);
    }

    function formatTermLabel(term) {
      if (!term) return '';
      var sem = term.enumSem || '';
      var label = term.term_label || '';
      var ys = term.strYearStart || '';
      var ye = term.strYearEnd || '';
      var span = (ys && ye) ? (ys + '-' + ye) : '';
      return [sem, label, span].filter(Boolean).join(' ').trim();
    }

    function save() {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        intSubjectID: vm.model.intSubjectID,
        intFacultyID: vm.model.intFacultyID || null,
        // Ensure API receives a string per validation rule
        strAcademicYear: (vm.model.strAcademicYear !== undefined && vm.model.strAcademicYear !== null) ? String(vm.model.strAcademicYear) : '',
        strUnits: (vm.model.strUnits !== undefined && vm.model.strUnits !== null) ? String(vm.model.strUnits) : null,        
        intFinalized: vm.model.intFinalized,
        campus_id: vm.model.campus_id || null,
        sectionCode: (vm.model.sectionCode && vm.model.sectionCode.trim() !== '') ? vm.model.sectionCode.trim() : null
      };

      var p = vm.id
        ? ClasslistsService.update(vm.id, payload)
        : ClasslistsService.create(payload);

      p.then(function (data) {
          if (data && data.success) {
            vm.success = 'Saved successfully';
            // Navigate back to list after short delay
            setTimeout(function () {
              try { angular.element(document.body).scope().$apply(function(){ $location.path('/classlists'); }); }
              catch (e) { $location.path('/classlists'); }
            }, 400);
          } else {
            vm.error = (data && data.message) ? data.message : 'Save failed';
          }
        })
        .catch(function (e) {
          var apiMsg = (e && e.data && e.data.message) ? e.data.message : null;
          vm.error = apiMsg || 'Save failed';
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    function cancel() {
      $location.path('/classlists');
    }

    // Kick off
    vm.init();
  }

})();
