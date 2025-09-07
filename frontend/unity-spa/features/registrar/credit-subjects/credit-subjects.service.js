(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('CreditSubjectsService', CreditSubjectsService);

  CreditSubjectsService.$inject = ['$http', '$q', 'APP_CONFIG', 'StorageService'];
  function CreditSubjectsService($http, $q, APP_CONFIG, StorageService) {
    // Use runtime-configured API base to avoid 404s when app is served under a subfolder.
    var base = (APP_CONFIG && APP_CONFIG.API_BASE ? APP_CONFIG.API_BASE : '/laravel-api/public/api/v1');

    // Helpers to include admin header (X-Faculty-ID) for protected routes
    function _getLoginState() {
      try {
        return (StorageService.getJSON && StorageService.getJSON('loginState')) || StorageService.get('loginState') || null;
      } catch (e) {
        return null;
      }
    }
    function _headers(extra) {
      var state = _getLoginState();
      var headers = Object.assign({}, extra || {});
      if (state && state.faculty_id != null && ('' + state.faculty_id).trim() !== '') {
        headers['X-Faculty-ID'] = state.faculty_id;
      } else {
        // Dev fallback for protected endpoints (super admin context)
        headers['X-Faculty-ID'] = 'smssuperadmin';
      }
      return { headers: headers };
    }

    return {
      list: list,
      add: add,
      remove: remove
    };

    /**
     * List credited subjects for a student by student_number.
     */
    function list(studentNumber) {
      if (!studentNumber) return $q.reject({ data: { message: 'student_number required' } });
      return $http.get(base + '/students/' + encodeURIComponent(studentNumber) + '/credits', _headers())
        .then(pluckData);
    }

    /**
     * Add credited subject entry.
     * payload: { subject_id:int, term_taken?:string, school_taken?:string, remarks?:string, floatFinalGrade?:number }
     */
    function add(studentNumber, payload) {
      if (!studentNumber) return $q.reject({ data: { message: 'student_number required' } });
      return $http.post(base + '/students/' + encodeURIComponent(studentNumber) + '/credits', payload, _headers())
        .then(pluckData);
    }

    /**
     * Delete credited subject entry by id.
     */
    function remove(studentNumber, id) {
      if (!studentNumber) return $q.reject({ data: { message: 'student_number required' } });
      if (!id) return $q.reject({ data: { message: 'id required' } });
      return $http.delete(base + '/students/' + encodeURIComponent(studentNumber) + '/credits/' + encodeURIComponent(id), _headers())
        .then(pluckData);
    }

    function pluckData(resp) {
      return resp && resp.data ? resp.data : resp;
    }
  }
})();
