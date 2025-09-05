(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClassroomEditController', ClassroomEditController);

  ClassroomEditController.$inject = ['$location', '$routeParams', 'StorageService', 'ClassroomsService', 'CampusesService', 'RoleService', 'ToastService'];
  function ClassroomEditController($location, $routeParams, StorageService, ClassroomsService, CampusesService, RoleService, ToastService) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    vm.isEdit = !!$routeParams.id;
    vm.title = vm.isEdit ? 'Edit Classroom' : 'Add Classroom';
    vm.loading = false;
    vm.saving = false;
    vm.error = null;
    vm.campuses = [];

    // Guard route
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Check if user can edit
    if (!RoleService.hasAny(['registrar', 'admin'])) {
      ToastService.show('You do not have permission to create or edit classrooms.', 'error');
      $location.path('/classrooms');
      return;
    }

    // Load campuses for dropdown
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

    // Form data
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

    // Status options - remove this since there's no status field in the API
    // vm.statusOptions = [
    //   { value: 'active', label: 'Active' },
    //   { value: 'inactive', label: 'Inactive' },
    //   { value: 'maintenance', label: 'Under Maintenance' }
    // ];

    // Load classroom data if editing
    if (vm.isEdit) {
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
          console.error('ClassroomEditController load error:', err);
        })
        .finally(function () {
          vm.loading = false;
        });
    }

    vm.save = function () {
      if (vm.saving) return;

      // Basic validation
      if (!vm.form.strRoomCode || !vm.form.strRoomCode.trim()) {
        ToastService.show('Room code is required.', 'error');
        return;
      }

      if (!vm.form.campus_id) {
        ToastService.show('Campus is required.', 'error');
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
        .then(function () {
          var msg = vm.isEdit ? 'Classroom updated successfully.' : 'Classroom created successfully.';
          ToastService.show(msg, 'success');
          $location.path('/classrooms');
        })
        .catch(function (err) {
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
          ToastService.show(msg, 'error');
        })
        .finally(function () {
          vm.saving = false;
        });
    };

    vm.cancel = function () {
      $location.path('/classrooms');
    };

    // Clear alerts
    vm.clearAlert = function () {
      vm.error = null;
    };
  }

})();
