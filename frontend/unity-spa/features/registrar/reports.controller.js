(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ReportsController', ReportsController);

  ReportsController.$inject = ['$location', 'LinkService', 'StorageService'];
  function ReportsController($location, LinkService, StorageService) {
    var vm = this;

    vm.title = 'Registrar Reports';
    vm.state = StorageService.getJSON('loginState');

    // guard if accessed directly without login (extra safety beyond run.js)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Legacy CI links (used during migration)
    vm.links = LinkService.buildLinks();
    // Internal SPA nav
    vm.nav = LinkService.buildSpaLinks();
  }

})();
