(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('LinkService', LinkService);

  LinkService.$inject = ['$window'];
  function LinkService($window) {
    var baseRoot = computeBaseRoot();

    function computeBaseRoot() {
      var path = $window.location.pathname || '/';
      var parts = path.split('/');
      var trimmed = parts;
      // remove last 3 segments: 'frontend', 'unity-spa', and file/empty
      if (parts.length >= 3) {
        trimmed = parts.slice(0, parts.length - 3);
      }
      var root = trimmed.join('/') || '/';
      if (root.length > 1) {
        root = root.replace(/\/+$/, '');
      }
      return root;
    }

    function refresh() {
      baseRoot = computeBaseRoot();
    }

    function rootPrefix() {
      return (baseRoot === '/' ? '' : baseRoot);
    }

    function buildLinks() {
      var root = rootPrefix();
      return {
        classlist: root + '/unity/view_classlist',
        profile: root + '/faculty/my_profile',
        settings: root + '/faculty/edit_profile',
        facultyDashboard: root + '/unity/faculty_dashboard',
        logout: root + '/users/logout',
        unity: root + '/unity',
        portal: root + '/portal',
        registrarReports: root + '/registrar/registrar_reports',
        financeStudentLedger: root + '/finance/view_all_students_ledger',
        scholarshipStudents: root + '/scholarship/scholarship_view'
      };
    }

    function buildSpaLinks() {
      return {
        dashboard: '#!/dashboard',
        facultyClasses: '#!/faculty/classes',
        facultyProfile: '#!/faculty/profile',
        facultySettings: '#!/faculty/settings',
        login: '#!/login'
      };
    }

    return {
      getBaseRoot: function () { return baseRoot; },
      computeBaseRoot: computeBaseRoot,
      refresh: refresh,
      buildLinks: buildLinks,
      buildSpaLinks: buildSpaLinks
    };
  }

})();
