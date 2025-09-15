(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('RegistrarChangePasswordController', RegistrarChangePasswordController);

  RegistrarChangePasswordController.$inject = ['$q', '$timeout', 'StudentsService', 'RegistrarChangePasswordService', 'ToastService'];
  function RegistrarChangePasswordController($q, $timeout, StudentsService, RegistrarChangePasswordService, ToastService) {
    var vm = this;

    // State
    vm.query = '';
    vm.suggestions = [];
    vm.loadingSuggestions = false;
    vm.selected = null; // { id, student_number, last_name, first_name, middle_name }

    vm.mode = 'generate'; // 'generate' | 'set'
    vm.newPassword = '';
    vm.note = '';

    vm.submitting = false;
    vm.result = null; // { success, message, data: { student_id, mode, generated_password?, updated_at } }

    // Methods
    vm.onQueryChange = onQueryChange;
    vm.selectSuggestion = selectSuggestion;
    vm.clearSelection = clearSelection;
    vm.canSubmit = canSubmit;
    vm.submit = submit;
    vm.resetForm = resetForm;

    // Debounce timer
    var searchTimer = null;

    function onQueryChange() {
      vm.result = null;
      vm.selected = null;
      if (searchTimer) {
        $timeout.cancel(searchTimer);
        searchTimer = null;
      }
      var q = (vm.query || '').trim();
      if (q.length < 2) {
        vm.suggestions = [];
        return;
      }
      vm.loadingSuggestions = true;
      searchTimer = $timeout(function () {
        StudentsService.listSuggestions(q)
          .then(function (rows) {
            // Normalize display label "STUDENT_NUMBER — Last, First"
            vm.suggestions = (rows || []).map(function (r) {
              var name = (r.last_name || '') + ', ' + (r.first_name || '');
              var label = (r.student_number ? (r.student_number + ' — ') : '') + name;
              return {
                id: r.id,
                student_number: r.student_number || '',
                last_name: r.last_name || '',
                first_name: r.first_name || '',
                middle_name: r.middle_name || '',
                label: label
              };
            });
          })
          .finally(function () {
            vm.loadingSuggestions = false;
          });
      }, 250);
    }

    function selectSuggestion(s) {
      if (!s) return;
      vm.selected = s;
      vm.query = s.label;
      vm.suggestions = [];
    }

    function clearSelection() {
      vm.selected = null;
      vm.query = '';
      vm.suggestions = [];
      vm.result = null;
    }

    function canSubmit() {
      if (!vm.selected || !vm.selected.id) return false;
      if (vm.mode === 'set') {
        return !!vm.newPassword && ('' + vm.newPassword).length >= 8;
      }
      // generate mode
      return true;
    }

    function submit() {
      if (!canSubmit()) {
        ToastService.warn('Please complete required fields.');
        return;
      }
      vm.submitting = true;
      vm.result = null;

      var note = (vm.note || '').trim();
      var p = RegistrarChangePasswordService.changePassword(
        vm.selected.id,
        vm.mode,
        vm.mode === 'set' ? ('' + vm.newPassword) : undefined,
        note !== '' ? note : undefined
      );

      p.then(function (resp) {
        if (resp && resp.success) {
          vm.result = resp;
          // UX messages
          if (vm.mode === 'generate') {
            ToastService.success('Password generated successfully.');
          } else {
            ToastService.success('Password updated successfully.');
          }
          // For set mode, do not keep the plaintext in UI
          if (vm.mode === 'set') {
            vm.newPassword = '';
          }
        } else {
          var msg = (resp && resp.message) ? resp.message : 'Operation failed.';
          ToastService.error(msg);
        }
      }).catch(function (err) {
        var msg = 'Request failed';
        try {
          if (err && err.data && err.data.message) msg = err.data.message;
        } catch (e) {}
        ToastService.error(msg);
      }).finally(function () {
        vm.submitting = false;
      });
    }

    function resetForm() {
      vm.query = '';
      vm.suggestions = [];
      vm.selected = null;
      vm.mode = 'generate';
      vm.newPassword = '';
      vm.note = '';
      vm.submitting = false;
      vm.result = null;
    }
  }
})();
