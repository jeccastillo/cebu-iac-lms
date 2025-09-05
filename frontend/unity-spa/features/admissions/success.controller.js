(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('AdmissionsSuccessController', AdmissionsSuccessController);

  AdmissionsSuccessController.$inject = ['$location', '$scope'];

  function AdmissionsSuccessController($location, $scope) {
    var vm = this;

    vm.hash = null;
    vm.uploadUrl = null;

    try {
      vm.hash = ($location.search() && $location.search().hash) || null;
    } catch (e) {
      vm.hash = null;
    }

    if (vm.hash) {
      vm.uploadUrl = '#/public/initial-requirements/' + encodeURIComponent(vm.hash);
    }
  }
})();
