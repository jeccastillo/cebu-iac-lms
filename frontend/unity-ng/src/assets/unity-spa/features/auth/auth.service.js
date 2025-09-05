(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('AuthService', AuthService);

  AuthService.$inject = ['$http', '$q', 'APP_CONFIG'];
  function AuthService($http, $q, APP_CONFIG) {
    var API_BASE = (APP_CONFIG.API_BASE || '/laravel-api/public/api/v1').replace(/\/+$/, '');

    function loginFacultyOrStaff(username, password, loginType) {
      loginType = loginType || 'faculty';
      var payload = {
        strUsername: username,
        strPass: password,
        loginType: loginType
      };
      return $http.post(API_BASE + '/users/auth', payload)
        .then(handleResponse, handleError);
    }

    function loginStudent(username, password) {
      var payload = {
        strUsername: username,
        strPass: password
      };
      return $http.post(API_BASE + '/users/auth-student', payload)
        .then(handleResponse, handleError);
    }

    function handleResponse(resp) {
      return resp.data;
    }

    function handleError(err) {
      var message = 'Request failed';
      if (err && err.data) {
        if (typeof err.data === 'string') {
          message = err.data;
        } else if (err.data.message) {
          message = err.data.message;
        } else if (err.data.error) {
          message = err.data.error;
        }
      }
      return $q.reject({
        success: false,
        message: message,
        raw: err
      });
    }

    return {
      apiBase: API_BASE,
      loginFacultyOrStaff: loginFacultyOrStaff,
      loginStudent: loginStudent
    };
  }

})();
