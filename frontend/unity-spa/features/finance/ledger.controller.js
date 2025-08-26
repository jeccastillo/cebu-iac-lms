(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FinanceLedgerController', FinanceLedgerController);

  FinanceLedgerController.$inject = ['$location', 'LinkService', 'StorageService'];
  function FinanceLedgerController($location, LinkService, StorageService) {
    var vm = this;

    vm.title = 'Student Ledger';
    vm.state = StorageService.getJSON('loginState');

    // extra guard (in addition to run.js)
    if (!vm.state || !vm.state.loggedIn) {
      $location.path('/login');
      return;
    }

    // Legacy CI links (used during migration) and SPA nav
    vm.links = LinkService.buildLinks();
    vm.nav = LinkService.buildSpaLinks();
  }

})();
