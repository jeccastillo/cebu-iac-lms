(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ApplicantJourneyService', ApplicantJourneyService);

  ApplicantJourneyService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ApplicantJourneyService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    function _getLoginState() {
      try {
        return StorageService.getJSON('loginState') || null;
      } catch (e) {
        return null;
      }
    }

    // Use same header strategy as InterviewsService for proper backend stamping
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

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      // GET /admissions/applicant-data/{applicantDataId}/journey
      // Optional params: { page, perPage }
      listByApplicantData: function (applicantDataId, params) {
        if (!applicantDataId) {
          return Promise.reject({ message: 'Missing applicantDataId' });
        }
        var cfg = _adminHeaders();
        if (params && typeof params === 'object') {
          cfg.params = {};
          if (params.page != null) cfg.params.page = params.page;
          if (params.perPage != null) cfg.params.perPage = params.perPage;
        }
        return $http
          .get(BASE + '/admissions/applicant-data/' + encodeURIComponent(applicantDataId) + '/journey', cfg)
          .then(_unwrap);
      }
    };
  }

})();
