(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('CreditSubjectsService', CreditSubjectsService);

  CreditSubjectsService.$inject = ['$http', '$q'];
  function CreditSubjectsService($http, $q) {
    var base = '/laravel-api/public/api/v1';

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
      return $http.get(base + '/students/' + encodeURIComponent(studentNumber) + '/credits')
        .then(pluckData);
    }

    /**
     * Add credited subject entry.
     * payload: { subject_id:int, term_taken?:string, school_taken?:string, remarks?:string }
     */
    function add(studentNumber, payload) {
      if (!studentNumber) return $q.reject({ data: { message: 'student_number required' } });
      return $http.post(base + '/students/' + encodeURIComponent(studentNumber) + '/credits', payload)
        .then(pluckData);
    }

    /**
     * Delete credited subject entry by id.
     */
    function remove(studentNumber, id) {
      if (!studentNumber) return $q.reject({ data: { message: 'student_number required' } });
      if (!id) return $q.reject({ data: { message: 'id required' } });
      return $http.delete(base + '/students/' + encodeURIComponent(studentNumber) + '/credits/' + encodeURIComponent(id))
        .then(pluckData);
    }

    function pluckData(resp) {
      return resp && resp.data ? resp.data : resp;
    }
  }
})();
