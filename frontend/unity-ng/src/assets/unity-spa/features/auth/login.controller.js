(function () {
  'use strict';

  angular
    .module('unityApp')
    .controller('LoginController', LoginController);

  LoginController.$inject = ['$location', '$window', 'AuthService', 'APP_CONFIG', 'StorageService', 'RoleService', 'CampusService', '$q'];
  function LoginController($location, $window, AuthService, APP_CONFIG, StorageService, RoleService, CampusService, $q) {
    var vm = this;

    vm.form = {
      username: '',
      password: '',
      loginType: 'faculty' // 'faculty' or 'student'
    };
    vm.loading = false;
    vm.error = '';

    vm.submit = function submit() {
      vm.error = '';
      if (!vm.form.username || !vm.form.password) {
        vm.error = 'Please enter your username and password.';
        return;
      }

      vm.loading = true;

      var p;
      if (vm.form.loginType === 'student') {
        p = AuthService.loginStudent(vm.form.username, vm.form.password);
      } else {
        p = AuthService.loginFacultyOrStaff(vm.form.username, vm.form.password, vm.form.loginType);
      }

      p.then(function (data) {
        if (data && data.success) {
          // Prefer roles from API if provided; otherwise derive deterministically
          var roles = (data && Array.isArray(data.roles) && data.roles.length)
            ? data.roles
            : RoleService.deriveRoles(vm.form.username, vm.form.loginType);

          var state = {
            loggedIn: true,
            username: vm.form.username,
            loginType: vm.form.loginType,
            ts: Date.now(),
            roles: roles,
            campus_id: (data && data.campus_id !== undefined && data.campus_id !== null) ? data.campus_id : null,
            faculty_id: (data && data.faculty_id !== undefined && data.faculty_id !== null) ? data.faculty_id : null
          };
          StorageService.setJSON('loginState', state);

          // Ensure campus matches logged-in user's campus (for non-student logins)
          var proceed = ($q && $q.when) ? $q.when() : Promise.resolve();
          if (vm.form.loginType !== 'student' && data && data.campus_id !== undefined && data.campus_id !== null && ('' + data.campus_id).trim() !== '') {
            var cid = '' + data.campus_id;
            var initPromise = (CampusService && CampusService.init) ? CampusService.init() : proceed;
            proceed = initPromise.then(function () {
              try {
                var list = CampusService && CampusService.availableCampuses ? CampusService.availableCampuses : [];
                for (var i = 0; i < list.length; i++) {
                  if (('' + list[i].id) === cid) {
                    CampusService.setSelectedCampus(list[i]);
                    break;
                  }
                }
              } catch (e) {
                // ignore campus selection errors
              }
            });
          }
          proceed.finally ? proceed.finally(doRedirect) : proceed.then(doRedirect, doRedirect);

          function doRedirect() {
            var useRedirects = (APP_CONFIG.LOGIN_APP_CONFIG && APP_CONFIG.LOGIN_APP_CONFIG.useRedirects) || false;
            if (useRedirects) {
              var redirects = APP_CONFIG.AFTER_LOGIN_REDIRECTS || { faculty: '/unity', student: '/portal' };
              var path = redirects[vm.form.loginType] || '/';
              $window.location.href = path;
            } else {
              // SPA routing: route based on resolved roles to avoid mis-selected loginType
              var hasStudentRole = Array.isArray(roles) && roles.some(function (r) {
                return (r + '').toLowerCase() === 'student_view';
              });
              var dest = hasStudentRole ? '/student/dashboard' : '/dashboard';
              $location.path(dest);
            }
          }
        } else {
          vm.error = (data && data.message) ? data.message : 'Login failed';
        }
      }).catch(function (err) {
        vm.error = (err && err.message) ? err.message : 'Login request failed';
      }).finally(function () {
        vm.loading = false;
      });
    };
  }

})();
