(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('FacultyClassesController', FacultyClassesController);

  FacultyClassesController.$inject = ['LinkService'];
  function FacultyClassesController(LinkService) {
    var vm = this;
    vm.title = 'My Classes';

    // While we migrate, link out to CI "View My Classes"
    vm.links = LinkService.buildLinks();

    // Placeholder list to show layout; will be populated from API later
    vm.classes = [];
  }

})();
