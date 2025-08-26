(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('ScholarshipStudentsController', ScholarshipStudentsController);

  ScholarshipStudentsController.$inject = ['$location', 'LinkService', 'StorageService'];
  function ScholarshipStudentsController($location, LinkService, StorageService) {
    var vm = this;

    vm.title = 'Students with Scholarships';
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
