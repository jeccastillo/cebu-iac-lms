(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClassroomsController', ClassroomsController);

  ClassroomsController.$inject = ['$location', '$window', '$routeParams', '$scope', 'StorageService', 'ClassroomsService', 'CampusesService', 'CampusService', 'RoleService', 'ToastService'];
  function ClassroomsController($location, $window, $routeParams, $scope, StorageService, ClassroomsService, CampusesService, CampusService, RoleService, ToastService) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');

    // Determine view mode based on route
    vm.isEdit = !!$routeParams.id;
    vm.isAdd = $location.path().indexOf('/add') !== -1;
    vm.isForm = vm.isEdit || vm.isAdd;
    vm.isList = !vm.isForm;

    // Titles
    vm.title = vm.isEdit ? 'Edit Classroom' : (vm.isAdd ? 'Add Classroom' : 'Classrooms');

    // Guard route (also enforced globally in run.js for most pages)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Check permissions
    vm.canEdit = RoleService.hasAny(['building_admin', 'admin']);
    vm.canAdd = vm.canEdit;
    vm.canDelete = vm.canEdit;

    // Check if user can access edit/add forms
    if (vm.isForm && !vm.canEdit) {
      ToastService.error('You do not have permission to create or edit classrooms.');
      $location.path('/classrooms');
      return;
    }

    // === LIST VIEW PROPERTIES ===
    vm.q = '';
    vm.rows = [];
    vm.loading = false;
    vm.error = null;
    vm.success = null;

    // === FORM VIEW PROPERTIES ===
    vm.saving = false;
    vm.campuses = [];
    vm.form = {
      strRoomCode: '',
      description: '',
      enumType: 'lecture',
      campus_id: null
    };

    // Type options
    vm.typeOptions = [
      { value: 'lecture', label: 'Lecture Hall' },
      { value: 'laboratory', label: 'Laboratory' },
      { value: 'hrm', label: 'HRM (Hotel/Restaurant Management)' },
      { value: 'pe', label: 'Physical Education' }
    ];

    // === IMPORT (Classrooms) ===
    vm.importing = false;
    vm.importResult = null;

    vm.clearImportResult = function () {
      vm.importResult = null;
    };

    vm.downloadTemplate = function () {
      vm.error = null;
      var filename = 'classrooms-import-template.xlsx';
      ClassroomsService.downloadImportTemplate()
        .then(function (data) {
          try {
            var blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var url = (window.URL || window.webkitURL).createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            (window.URL || window.webkitURL).revokeObjectURL(url);
            ToastService.success('Template downloaded.');
          } catch (e) {
            console.error('Download template error (classrooms):', e);
            vm.error = 'Failed to download template.';
            ToastService.error(vm.error);
          }
        })
        .catch(function (err) {
          console.error('Download template request error (classrooms):', err);
          vm.error = 'Failed to download template.';
          ToastService.error(vm.error);
        });
    };

    vm.openImportDialog = function () {
      var input = document.getElementById('classroomsImportInput');
      if (!input) {
        vm.error = 'Import input not found in DOM.';
        ToastService.error(vm.error);
        return;
      }
      // Bind change handler once
      if (!input._bbBound) {
        input._bbBound = true;
        input.addEventListener('change', function (ev) {
          var file = ev.target.files && ev.target.files[0];
          if (!file) return;
          // Ensure Angular digest
          $scope.$apply(function () {
            vm.importFileSelected(file, input);
          });
        });
      }
      input.click();
    };

    vm.importFileSelected = function (file, inputEl) {
      vm.importing = true;
      vm.importResult = null;
      vm.error = null;

      // Single-step import (no dry-run)
      ClassroomsService.importFile(file)
        .then(function (resp) {
          var result = resp && resp.result ? resp.result : resp;
          vm.importResult = result;
          ToastService.success('Import completed.');
          // Refresh list view after import completes
          if (vm.isList) {
            search();
          }
        })
        .catch(function (err) {
          console.error('Import error (classrooms):', err);
          var msg = 'Import failed.';
          if (err && err.data && err.data.message) {
            msg = err.data.message;
          }
          vm.error = msg;
          ToastService.error(msg);
        })
        .finally(function () {
          vm.importing = false;
          try {
            if (inputEl) inputEl.value = '';
          } catch (_) {}
        });
    };

    // === INITIALIZATION ===
    init();

    function init() {
      if (vm.isForm) {
        // Load campuses for form
        loadCampuses();
        
        // Load classroom data if editing
        if (vm.isEdit) {
          loadClassroom();
        }
      } else {
        // Load classroom list only for list view, scoped by selected campus
        var p = (CampusService && CampusService.init) ? CampusService.init() : null;
        function run() { search(); }
        if (p && p.then) { p.then(run); } else { run(); }

        // React to global campus changes
        $scope.$on('campusChanged', function () {
          search();
        });
      }
    }

    // === LIST VIEW METHODS ===
    function search() {
      vm.loading = true;
      vm.error = null;

      // Build params with search and selected campus_id
      var opts = {};
      if (vm.q && ('' + vm.q).trim() !== '') {
        opts.search = vm.q;
      }
      try {
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        if (campus && campus.id !== undefined && campus.id !== null && ('' + campus.id).trim() !== '') {
          opts.campus_id = parseInt(campus.id, 10);
        }
      } catch (e) { /* no-op */ }

      return ClassroomsService.list(opts)
        .then(function (data) {
          // data may be { success, data } or plain array fallback
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.rows = data.data;
          } else if (angular.isArray(data)) {
            vm.rows = data;
          } else if (data && angular.isArray(data.rows)) {
            vm.rows = data.rows;
          } else {
            vm.rows = [];
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load classrooms.';
          vm.rows = [];
          console.error('ClassroomsController.search error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    vm.search = search;

    vm.add = function () {
      $location.path('/classrooms/add');
    };

    vm.edit = function (row) {
      if (!row || !row.intID) {
        ToastService.error('Invalid classroom data.');
        return;
      }
      $location.path('/classrooms/' + row.intID + '/edit');
    };

    vm.delete = function (row) {
      if (!row || !row.intID) {
        ToastService.error('Invalid classroom data.');
        return;
      }

      if (!$window.confirm('Are you sure you want to delete "' + (row.strRoomCode || 'this classroom') + '"?')) {
        return;
      }

      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClassroomsService.delete(row.intID)
        .then(function (response) {
          // Check if the response indicates success
          if (response && response.success !== false) {
            ToastService.success('Classroom deleted successfully.');
            search(); // Reload the list
          } else {
            // Handle case where API returns success:false
            var errorMsg = (response && response.message) ? response.message : 'Delete operation failed.';
            vm.error = errorMsg;
            ToastService.error(errorMsg);
          }
        })
        .catch(function (err) {
          console.error('Delete error:', err); // Debug logging
          var msg = 'Failed to delete classroom.';
          if (err && err.data && err.data.message) {
            msg = err.data.message;
          }
          vm.error = msg;
          ToastService.error(msg);
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    // === FORM VIEW METHODS ===
    function loadCampuses() {
      CampusesService.list()
        .then(function (data) {
          if (data && data.success !== false && angular.isArray(data.data)) {
            vm.campuses = data.data;
          } else if (angular.isArray(data)) {
            vm.campuses = data;
          } else if (data && angular.isArray(data.rows)) {
            vm.campuses = data.rows;
          }
        })
        .catch(function (err) {
          console.error('Failed to load campuses:', err);
        });
    }

    function loadClassroom() {
      vm.loading = true;
      ClassroomsService.get($routeParams.id)
        .then(function (data) {
          if (data && data.success !== false) {
            var classroom = data.data || data;
            vm.form.strRoomCode = classroom.strRoomCode || '';
            vm.form.description = classroom.description || '';
            vm.form.enumType = classroom.enumType || 'lecture';
            vm.form.campus_id = classroom.campus_id || null;
          } else {
            vm.error = 'Failed to load classroom data.';
          }
        })
        .catch(function (err) {
          vm.error = 'Failed to load classroom data.';
          console.error('ClassroomsController loadClassroom error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    vm.save = function () {
      if (vm.saving) return;

      // Basic validation
      if (!vm.form.strRoomCode || !vm.form.strRoomCode.trim()) {
        ToastService.error('Room code is required.');
        return;
      }

      if (!vm.form.campus_id) {
        ToastService.error('Campus is required.');
        return;
      }

      vm.saving = true;
      vm.error = null;

      var payload = {
        strRoomCode: vm.form.strRoomCode.trim(),
        description: vm.form.description ? vm.form.description.trim() : '',
        enumType: vm.form.enumType || 'lecture',
        campus_id: parseInt(vm.form.campus_id, 10)
      };

      var promise = vm.isEdit 
        ? ClassroomsService.update($routeParams.id, payload)
        : ClassroomsService.create(payload);

      promise
        .then(function (response) {
          // Check if the response indicates success
          if (response && response.success !== false) {
            var msg = vm.isEdit ? 'Classroom updated successfully.' : 'Classroom created successfully.';
            ToastService.success(msg);
            $location.path('/classrooms');
          } else {
            // Handle case where API returns success:false
            var errorMsg = (response && response.message) ? response.message : 'Operation failed.';
            vm.error = errorMsg;
            ToastService.error(errorMsg);
          }
        })
        .catch(function (err) {
          console.error('Save error:', err); // Debug logging
          var msg = vm.isEdit ? 'Failed to update classroom.' : 'Failed to create classroom.';
          if (err && err.data && err.data.message) {
            msg = err.data.message;
          } else if (err && err.data && err.data.errors) {
            // Laravel validation errors
            var errors = err.data.errors;
            var errorMessages = [];
            for (var field in errors) {
              if (errors.hasOwnProperty(field)) {
                errorMessages = errorMessages.concat(errors[field]);
              }
            }
            if (errorMessages.length > 0) {
              msg = errorMessages.join(' ');
            }
          }
          vm.error = msg;
          ToastService.error(msg);
        })
        .finally(function () {
          vm.saving = false;
        });
    };

    vm.cancel = function () {
      $location.path('/classrooms');
    };

    // === SHARED METHODS ===
    vm.clearAlert = function () {
      vm.error = null;
      vm.success = null;
    };
  }

})();
