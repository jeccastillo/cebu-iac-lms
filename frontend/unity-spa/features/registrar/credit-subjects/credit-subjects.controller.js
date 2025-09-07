(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CreditSubjectsController', CreditSubjectsController);

  CreditSubjectsController.$inject = ['$q', 'ToastService', 'CreditSubjectsService'];
  function CreditSubjectsController($q, ToastService, CreditSubjectsService) {
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

    // Add form
    vm.form = {
      subject_id: '',
      term_taken: '',
      school_taken: '',
      remarks: 'credited'
    };

    // Actions
    vm.load = load;
    vm.add = add;
    vm.remove = remove;
    vm.clearForm = clearForm;

    activate();

    function activate() {
      // page init
    }

    function load() {
      if (!vm.student_number || !vm.student_number.trim()) {
        ToastService.warning('Enter a student number to load credited subjects.');
        return $q.when();
      }
      if (vm.loading.list) return $q.when();

      vm.loading.list = true;
      return CreditSubjectsService.list(vm.student_number.trim())
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
      if (!vm.student_number || !vm.student_number.trim()) {
        ToastService.warning('Enter a student number first.');
        return $q.when();
      }
      var subjId = parseInt(vm.form.subject_id, 10);
      if (!subjId || subjId <= 0) {
        ToastService.warning('Enter a valid subject ID.');
        return $q.when();
      }
      if (vm.loading.add) return $q.when();

      vm.loading.add = true;
      var payload = {
        subject_id: subjId,
        term_taken: nullIfEmpty(vm.form.term_taken),
        school_taken: nullIfEmpty(vm.form.school_taken),
        remarks: nullIfEmpty(vm.form.remarks)
      };

      return CreditSubjectsService.add(vm.student_number.trim(), payload)
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
        ToastService.warning('Select a valid credited entry.');
        return $q.when();
      }
      return CreditSubjectsService.remove(vm.student_number.trim(), row.id)
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
      vm.form.remarks = 'credited';
    }

    function nullIfEmpty(v) {
      if (v === undefined || v === null) return null;
      var s = ('' + v).trim();
      return s === '' ? null : s;
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
