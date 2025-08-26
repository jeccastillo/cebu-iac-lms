(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CampusEditController', CampusEditController);

  CampusEditController.$inject = ['$routeParams', '$location', 'StorageService', 'CampusesService'];
  function CampusEditController($routeParams, $location, StorageService, CampusesService) {
    var vm = this;

    vm.state = StorageService.getJSON('loginState');
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.id = $routeParams.id ? parseInt($routeParams.id, 10) : null;
    vm.isEdit = !!vm.id;
    vm.title = vm.isEdit ? 'Edit Campus' : 'Add Campus';

    vm.model = {
      campus_name: '',
      description: '',
      status: 'active'
    };

    vm.loading = false;
    vm.error = null;
    vm.success = null;

    vm.load = function () {
      if (!vm.isEdit) return;
      vm.loading = true;
      vm.error = null;
      CampusesService.get(vm.id)
        .then(function (data) {
          if (data && data.success !== false && data.data) {
            vm.model.campus_name = data.data.campus_name || '';
            vm.model.description = data.data.description || '';
            vm.model.status = data.data.status || 'active';
          } else if (data && data.campus_name) {
            vm.model.campus_name = data.campus_name || '';
            vm.model.description = data.description || '';
            vm.model.status = data.status || 'active';
          } else {
            vm.error = 'Campus not found.';
          }
        })
        .catch(function () {
          vm.error = 'Failed to load campus.';
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.save = function () {
      vm.loading = true;
      vm.error = null;
      vm.success = null;

      var payload = {
        campus_name: (vm.model.campus_name || '').trim(),
        description: vm.model.description || null,
        status: vm.model.status || 'active'
      };

      var p = vm.isEdit
        ? CampusesService.update(vm.id, payload)
        : CampusesService.create(payload);

      p.then(function (data) {
          if (data && data.success !== false) {
            vm.success = 'Saved.';
            // Redirect back to list after brief delay
            setTimeout(function () {
              try { vm.success = null; } catch (e) {}
              window.location.hash = '#/campuses';
            }, 300);
          } else {
            vm.error = 'Save failed.';
          }
        })
        .catch(function (err) {
          // Basic validation surface
          if (err && err.data && err.data.errors) {
            var firstKey = Object.keys(err.data.errors)[0];
            vm.error = (err.data.errors[firstKey] && err.data.errors[firstKey][0]) || 'Validation failed.';
          } else if (err && err.data && err.data.message) {
            vm.error = err.data.message;
          } else {
            vm.error = 'Save failed.';
          }
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.cancel = function () {
      $location.path('/campuses');
    };

    vm.load();
  }

})();
