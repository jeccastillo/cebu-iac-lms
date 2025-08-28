(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ClassroomsController', ClassroomsController);

  ClassroomsController.$inject = ['$location', '$window', 'StorageService', 'ClassroomsService', 'RoleService', 'ToastService'];
  function ClassroomsController($location, $window, StorageService, ClassroomsService, RoleService, ToastService) {
    var vm = this;

    vm.title = 'Classrooms';
    vm.state = StorageService.getJSON('loginState');

    // Guard route (also enforced globally in run.js for most pages)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Check permissions
    vm.canEdit = RoleService.hasAny(['registrar', 'admin']);
    vm.canAdd = vm.canEdit;
    vm.canDelete = vm.canEdit;

    vm.q = '';
    vm.rows = [];
    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.search = function () {
      vm.loading = true;
      vm.error = null;
      return ClassroomsService.list(vm.q)
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
    };

    vm.add = function () {
      $location.path('/classrooms/add');
    };

    vm.edit = function (row) {
      if (!row || !row.intID) {
        ToastService.show('Invalid classroom data.', 'error');
        return;
      }
      $location.path('/classrooms/' + row.intID + '/edit');
    };

    vm.delete = function (row) {
      if (!row || !row.intID) {
        ToastService.show('Invalid classroom data.', 'error');
        return;
      }

      if (!$window.confirm('Are you sure you want to delete "' + (row.strRoomCode || 'this classroom') + '"?')) {
        return;
      }

      vm.loading = true;
      vm.error = null;
      vm.success = null;

      ClassroomsService.delete(row.intID)
        .then(function () {
          ToastService.show('Classroom deleted successfully.', 'success');
          vm.search(); // Reload the list
        })
        .catch(function (err) {
          var msg = 'Failed to delete classroom.';
          if (err && err.data && err.data.message) {
            msg = err.data.message;
          }
          vm.error = msg;
          ToastService.show(msg, 'error');
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    // Clear alerts
    vm.clearAlert = function () {
      vm.error = null;
      vm.success = null;
    };

    // Initialize
    vm.search();
  }

})();
