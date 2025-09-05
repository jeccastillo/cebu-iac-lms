(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('CampusesController', CampusesController);

  CampusesController.$inject = ['$location', '$window', 'StorageService', 'CampusesService'];
  function CampusesController($location, $window, StorageService, CampusesService) {
    var vm = this;

    vm.title = 'Campuses';
    vm.state = StorageService.getJSON('loginState');

    // Guard route (also enforced globally in run.js for most pages)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    vm.q = '';
    vm.rows = [];
    vm.loading = false;
    vm.error = null;

    vm.search = function () {
      vm.loading = true;
      vm.error = null;
      return CampusesService.list(vm.q)
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
        .catch(function () {
          vm.error = 'Failed to load campuses.';
          vm.rows = [];
        })
        .finally(function () {
          vm.loading = false;
        });
    };

    vm.add = function () {
      $location.path('/campuses/add');
    };

    vm.edit = function (row) {
      var id = row && row.id ? row.id : row;
      if (!id) return;
      $location.path('/campuses/' + id + '/edit');
    };

    vm.remove = function (row) {
      var id = row && row.id ? row.id : row;
      if (!id) return;
      if ($window.confirm('Delete this campus? Deletion will be blocked if the campus is in use. Consider setting status to inactive instead.')) {
        vm.loading = true;
        vm.error = null;
        CampusesService.remove(id)
          .then(function () {
            // success true expected
            vm.search();
          })
          .catch(function (err) {
            // Surface backend 409 details with usage and suggestion
            var msg = 'Delete failed.';
            try {
              if (err && err.status === 409 && err.data) {
                var payload = err.data;
                var usageText = '';
                if (payload && payload.usage && typeof payload.usage === 'object') {
                  var parts = [];
                  for (var k in payload.usage) {
                    if (Object.prototype.hasOwnProperty.call(payload.usage, k)) {
                      parts.push(k + ': ' + payload.usage[k]);
                    }
                  }
                  if (parts.length) usageText = ' (' + parts.join(', ') + ')';
                }
                msg = (payload.message || msg) + usageText + (payload.suggestion ? ' ' + payload.suggestion : '');
              } else if (err && err.data && err.data.message) {
                msg = err.data.message;
              }
            } catch (e) {
              // fallback to default message
            }
            vm.error = msg;
          })
          .finally(function () {
            vm.loading = false;
          });
      }
    };

    // Init
    vm.search();
  }

})();
