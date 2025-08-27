(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('UnityService', UnityService);

  UnityService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function UnityService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

    function _getLoginState() {
      try {
        return StorageService.getJSON('loginState') || null;
      } catch (e) {
        return null;
      }
    }

    function _adminHeaders(extra) {
      var state = _getLoginState();
      var headers = Object.assign({}, extra || {});
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      // Advising placeholder
      advising: function (payload) {
        return $http.post(BASE + '/unity/advising', payload, _adminHeaders()).then(_unwrap);
      },
      // Enlist registrar operations (add/drop/change_section)
      enlist: function (payload) {
        // payload: { student_number, term, year_level, student_type?, operations: [...] }
        return $http.post(BASE + '/unity/enlist', payload, _adminHeaders()).then(_unwrap);
      },
      // Reset registration for a student/term
      resetRegistration: function (payload) {
        // payload: { student_number, term?, password? }
        return $http.post(BASE + '/unity/reset-registration', payload, _adminHeaders()).then(_unwrap);
      },
      // Tag status placeholder
      tagStatus: function (payload) {
        return $http.post(BASE + '/unity/tag-status', payload, _adminHeaders()).then(_unwrap);
      },
      // Tuition preview wrapper
      tuitionPreview: function (payload) {
        return $http.post(BASE + '/unity/tuition-preview', payload, _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
