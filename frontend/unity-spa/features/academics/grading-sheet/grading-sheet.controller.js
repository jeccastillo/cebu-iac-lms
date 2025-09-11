(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('GradingSheetController', GradingSheetController);

  GradingSheetController.$inject = ['$scope', '$q', 'StorageService', 'TermService', 'StudentsService', 'GradingSheetService', 'ToastService'];
  function GradingSheetController($scope, $q, StorageService, TermService, StudentsService, GradingSheetService, ToastService) {
    var vm = this;

    // UI state
    vm.title = 'Grading Sheet';
    vm.loading = false;
    vm.error = null;

    // Term (readonly from TermService)
    vm.selectedTerm = null;
    vm.termLabel = '';

    // Student autocomplete
    vm.students = [];           // suggestions
    vm.selectedStudentId = null;

    // Period selector
    vm.period = 'final';        // 'midterm' | 'final' (default final)

    // Methods
    vm.onStudentQuery = onStudentQuery;
    vm.generate = generate;
    vm.studentLabel = studentLabel;

    activate();

    function activate() {
      // Sync term from TermService without re-fetching if already loaded by sidebar
      $q.when(TermService.init())
        .finally(function () {
          try {
            var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
            if (sel && (sel.intID || sel.id)) {
              vm.selectedTerm = sel;
              vm.termLabel = buildTermLabel(sel);
            } else {
              // Fallback to active term
              var p = TermService && TermService.getActiveTerm ? TermService.getActiveTerm() : null;
              if (p && typeof p.then === 'function') {
                p.then(function (t) {
                  if (t && (t.intID || t.id)) {
                    vm.selectedTerm = t;
                    vm.termLabel = buildTermLabel(t);
                  }
                });
              }
            }
          } catch (e) {}

          // Preload first page of students for dropdown-like behavior
          loadStudents();
        });

      // Listen for global term changes
      if ($scope && $scope.$on) {
        $scope.$on('termChanged', function (event, data) {
          try {
            var sel = TermService && TermService.getSelectedTerm ? TermService.getSelectedTerm() : null;
            if (sel && (sel.intID || sel.id)) {
              vm.selectedTerm = sel;
              vm.termLabel = buildTermLabel(sel);
            }
          } catch (e) {}
        });
      }
    }

    function buildTermLabel(t) {
      try {
        var y = (t.strYearStart != null && t.strYearEnd != null) ? (t.strYearStart + '-' + t.strYearEnd) : '';
        var sem = (t.enumSem != null) ? ('' + t.enumSem) : (t.term_label || '');
        var parts = [];
        if (sem) parts.push(sem);
        if (y) parts.push(y);
        return parts.join(' ');
      } catch (e) { return ''; }
    }

    function loadStudents() {
      try {
        StudentsService.listPage({ per_page: 20, page: 1 })
          .then(function (list) {
            vm.students = Array.isArray(list) ? list : [];
          });
      } catch (e) {}
    }

    // Remote autocomplete query handler (debounced by directive)
    function onStudentQuery(q) {
      try {
        StudentsService.listPage({ q: q, per_page: 20, page: 1 }).then(function (list) {
          vm.students = Array.isArray(list) ? list : [];
        });
      } catch (e) {}
    }

    function studentLabel(item) {
      if (!item) return '';
      var ln = item.last_name || '';
      var fn = item.first_name || '';
      var mn = item.middle_name || '';
      var sn = item.student_number || '';
      var name = (ln ? (ln + ', ') : '') + fn + (mn ? (' ' + mn) : '');
      return (sn ? (sn + ' — ') : '') + name;
    }

    function generate() {
      vm.error = null;

      var term = vm.selectedTerm;
      if (!term || !(term.intID || term.id)) {
        vm.error = 'Please select an academic term.';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      var sid = vm.selectedStudentId;
      if (!sid) {
        vm.error = 'Please select a student.';
        ToastService && ToastService.error && ToastService.error(vm.error);
        return;
      }
      var period = vm.period === 'midterm' ? 'midterm' : 'final';

      vm.loading = true;
      var syid = term.intID || term.id;
      GradingSheetService.exportPdf({ student_id: sid, syid: syid, period: period })
        .catch(function (err) {
          vm.error = (err && err.data && err.data.message) ? err.data.message : 'Failed to generate PDF';
          ToastService && ToastService.error && ToastService.error(vm.error);
        })
        .finally(function () {
          vm.loading = false;
        });
    }
  }

})();
