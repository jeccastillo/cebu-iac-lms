(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CreditSubjectsController', CreditSubjectsController);

  CreditSubjectsController.$inject = ['$q', 'ToastService', 'CreditSubjectsService', 'SubjectsService', 'StudentsService'];
  function CreditSubjectsController($q, ToastService, CreditSubjectsService, SubjectsService, StudentsService) {
    var vm = this;

    // State
    vm.loading = {
      list: false,
      add: false,
      bootstrap: false,
    };

    // Search/select student by student_number
    vm.student_number = '';
    vm.credits = [];
    vm.subjects = [];
    vm.studentResults = []; // pui-autocomplete dynamic source

    // Add form
    vm.form = {
      subject_id: '',
      term_taken: '',
      school_taken: '',
      floatFinalGrade: '',
      remarks: 'credited'
    };

    // Actions
    vm.load = load;
    vm.add = add;
    vm.remove = remove;
    vm.clearForm = clearForm;
    vm.onStudentQuery = onStudentQuery; // pui-autocomplete remote query

    activate();

    function activate() {
      // preload subjects for dropdown
      vm.loading.bootstrap = true;
      vm.subjects = [];
      try {
        SubjectsService.list({ limit: 500, page: 1 })
          .then(function (res) {
            var items = (res && res.data) ? res.data : (res || []);
            vm.subjects = Array.isArray(items) ? items : [];
          })
          .catch(function (err) {
            var msg = extractMsg(err, 'Failed to load subjects.');
            ToastService.error(msg);
          })
          .finally(function () {
            vm.loading.bootstrap = false;
          });
      } catch (e) {
        vm.loading.bootstrap = false;
      }
    }

    // Remote query for student_number autocomplete (no preloading)
    function onStudentQuery(q) {
      var term = (q || '').trim();
      if (term.length < 2) {
        vm.studentResults = [];
        return $q.when();
      }
      // Query students endpoint with free-text filter
      return StudentsService.listSuggestions(term)
        .then(function (items) {
          vm.studentResults = Array.isArray(items) ? items : [];
        })
        .catch(function () {
          vm.studentResults = [];
        });
    }

    function load() {
      var sn = normalizedStudentNumber();
      if (!sn) {
        ToastService.warn('Enter a student number to load credited subjects.');
        return $q.when();
      }
      if (vm.loading.list) return $q.when();

      vm.loading.list = true;
      return CreditSubjectsService.list(sn)
        .then(function (resp) {
          // resp expected shape: { success: true, data: [...] }
          var data = resp && resp.data ? resp.data : (resp || []);
          vm.credits = Array.isArray(data) ? data : [];
          if (!Array.isArray(data) && Array.isArray(resp)) {
            vm.credits = resp;
          }
        })
        .catch(function (err) {
          var msg = extractMsg(err, 'Failed to load credited subjects.');
          ToastService.error(msg);
        })
        .finally(function () { vm.loading.list = false; });
    }

    function add() {
      var sn = normalizedStudentNumber();
      if (!sn) {
        ToastService.warn('Enter a student number first.');
        return $q.when();
      }
      var subjId = parseInt(vm.form.subject_id, 10);
      if (!subjId || subjId <= 0) {
        ToastService.warn('Enter a valid subject ID.');
        return $q.when();
      }
      if (vm.loading.add) return $q.when();

      vm.loading.add = true;
      var payload = {
        subject_id: subjId,
        term_taken: nullIfEmpty(vm.form.term_taken),
        school_taken: nullIfEmpty(vm.form.school_taken),
        remarks: nullIfEmpty(vm.form.remarks),
        floatFinalGrade: nullIfEmpty(vm.form.floatFinalGrade)
      };

      return CreditSubjectsService.add(sn, payload)
        .then(function () {
          ToastService.success('Credited subject added.');
          clearForm();
          return load();
        })
        .catch(function (err) {
          var msg = extractMsg(err, 'Failed to add credited subject.');
          ToastService.error(msg);
        })
        .finally(function () { vm.loading.add = false; });
    }

    function remove(row) {
      if (!row || !row.id) {
        ToastService.warn('Select a valid credited entry.');
        return $q.when();
      }
      var sn = normalizedStudentNumber();
      if (!sn) {
        ToastService.warn('Enter a student number first.');
        return $q.when();
      }
      return CreditSubjectsService.remove(sn, row.id)
        .then(function () {
          ToastService.success('Credited subject removed.');
          return load();
        })
        .catch(function (err) {
          var msg = extractMsg(err, 'Failed to delete credited subject.');
          ToastService.error(msg);
        });
    }

    function clearForm() {
      vm.form.subject_id = '';
      vm.form.term_taken = '';
      vm.form.school_taken = '';
      vm.form.floatFinalGrade = '';
      vm.form.remarks = 'credited';
    }

    function nullIfEmpty(v) {
      if (v === undefined || v === null) return null;
      var s = ('' + v).trim();
      return s === '' ? null : s;
    }

    function normalizedStudentNumber() {
      try {
        var raw = (vm.student_number || '').toString().trim();
        if (!raw) return '';
        // When the input displays "STUDENT_NUMBER — Last, First", keep the student number part.
        if (raw.indexOf('—') !== -1) {
          raw = raw.split('—')[0].trim();
        }
        return raw;
      } catch (e) {
        return (vm.student_number || '').trim();
      }
    }

    function extractMsg(err, fallback) {
      try {
        if (err && err.data) {
          return err.data.message || err.data.error || fallback;
        }
      } catch (e) {}
      return fallback;
    }
  }
})();
