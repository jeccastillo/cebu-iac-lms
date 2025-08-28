(function () {
  'use strict';

  // RBAC constants: role names, provider config (window-overridable), and access matrix defaults
  angular
    .module('unityApp')
    .constant('ROLES', {
      faculty: 'faculty',
      registrar: 'registrar',
      finance: 'finance',
      scholarship: 'scholarship',
      campus_admin: 'campus_admin',
      faculty_admin: 'faculty_admin',
      building_admin: 'building_admin',
      student_view: 'student_view',
      admin: 'admin'
    })
    .constant('ROLE_CONFIG', {
      // Optional window overrides with safe defaults
      get USER_ROLE_MAP() {
        // Example structure (set in index.html for manual testing):
        // window.USER_ROLE_MAP = { 'registrar1': ['registrar'], 'fin1': ['finance'], 'admin1': ['admin'] };
        return window.USER_ROLE_MAP || {};
      },
      get DEFAULT_FACULTY_ROLES() {
        return window.DEFAULT_FACULTY_ROLES || ['faculty'];
      },
      get DEFAULT_STUDENT_ROLES() {
        return window.DEFAULT_STUDENT_ROLES || ['student_view'];
      }
    })
    // Access Matrix: maps route patterns (regex) to allowed roles.
    // Routes not listed here default to "auth-only" allowed (no role gating).
    .constant('ACCESS_MATRIX', [
      { test: '^/faculty/.*$', roles: ['faculty', 'admin'] },
      { test: '^/registrar/.*$', roles: ['registrar', 'admin'] },
      { test: '^/finance/.*$', roles: ['finance', 'admin'] },
      { test: '^/scholarship/.*$', roles: ['scholarship', 'admin'] },
      { test: '^/campuses(?:/.*)?$', roles: ['campus_admin', 'admin'] },
      { test: '^/grading-systems(?:/.*)?$', roles: ['faculty_admin', 'admin'] },
      { test: '^/school-years(?:/.*)?$', roles: ['registrar', 'admin'] },
      { test: '^/roles(?:/.*)?$', roles: ['admin'] },
      { test: '^/students$', roles: ['registrar', 'scholarship', 'finance', 'admin'] },
      { test: '^/students/[^/]+$', roles: ['registrar', 'scholarship', 'finance', 'admin'] },
      { test: '^/programs(?:/.*)?$', roles: ['registrar', 'admin'] },
      { test: '^/subjects(?:/.*)?$', roles: ['registrar', 'admin'] },
      { test: '^/curricula(?:/.*)?$', roles: ['registrar', 'admin'] },
      { test: '^/classlists(?:/.*)?$', roles: ['registrar', 'admin'] },
      { test: '^/classrooms(?:/.*)?$', roles: ['building_admin', 'admin'] },
      { test: '^/logs(?:/.*)?$', roles: ['admin'] }
      // '/dashboard' => any authenticated (intentionally omitted)
    ]);

})();
