(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('InitialRequirementsService', InitialRequirementsService);

  InitialRequirementsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function InitialRequirementsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    // Admin header helpers to stamp X-User-ID / X-Faculty-ID for system logging
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
      // Primary: X-User-ID
      if (state && state.user_id != null && !isNaN(parseInt(state.user_id, 10))) {
        headers['X-User-ID'] = parseInt(state.user_id, 10);
      }
      // Legacy/admin fallback: X-Faculty-ID
      if (state && state.faculty_id != null && !isNaN(parseInt(state.faculty_id, 10))) {
        headers['X-Faculty-ID'] = parseInt(state.faculty_id, 10);
        if (headers['X-User-ID'] == null) {
          headers['X-User-ID'] = parseInt(state.faculty_id, 10);
        }
      }
      return { headers: headers };
    }

    return {
      // GET /public/initial-requirements/{hash}
      getList: function (hash) {
        return $http.get(BASE + '/public/initial-requirements/' + encodeURIComponent(hash))
          .then(_unwrap);
      },

      // POST multipart: /public/initial-requirements/{hash}/upload/{appReqId}
      // This mirrors the public upload behavior; typically used by the public upload page via ng-file-upload.
      upload: function (hash, appReqId, formDataConfig) {
        return $http.post(
          BASE + '/public/initial-requirements/' + encodeURIComponent(hash) + '/upload/' + encodeURIComponent(appReqId),
          formDataConfig.data,
          {
            headers: angular.extend({ 'Content-Type': undefined }, formDataConfig.headers || {}),
            transformRequest: angular.identity
          }
        ).then(_unwrap);
      },

      // POST multipart (admin/admissions): /admissions/initial-requirements/{student}/upload/{appReqId}
      // Used by Admissions/Admin from the Applicant Details viewer to upload/replace a file on behalf of the applicant.
      adminUpload: function (studentId, appReqId, file) {
        if (!studentId || !appReqId || !file) {
          return Promise.reject({ message: 'Missing parameters for admin upload.' });
        }
        var fd = new FormData();
        fd.append('file', file);
        var cfg = _adminHeaders({ 'Content-Type': undefined });
        cfg.transformRequest = angular.identity;
        return $http.post(
          BASE + '/admissions/initial-requirements/' + encodeURIComponent(studentId) + '/upload/' + encodeURIComponent(appReqId),
          fd,
          cfg
        ).then(_unwrap);
      }
    };
  }
})();
