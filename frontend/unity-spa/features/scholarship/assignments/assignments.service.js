(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ScholarshipAssignmentsService', ScholarshipAssignmentsService);

  ScholarshipAssignmentsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ScholarshipAssignmentsService($http, APP_CONFIG, StorageService) {
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
      // GET /scholarships/assignments?syid=&student_id=&q=
      list: function (opts) {
        var params = {};
        if (opts) {
          if (opts.syid != null && opts.syid !== '') params.syid = opts.syid;
          if (opts.student_id != null && opts.student_id !== '') params.student_id = opts.student_id;
          if (opts.q && ('' + opts.q).trim() !== '') params.q = ('' + opts.q).trim();
        }
        return $http.get(BASE + '/scholarships/assignments', Object.assign({ params: params }, _adminHeaders())).then(_unwrap);
      },

      // POST /scholarships/assignments
      // payload: {
      //   student_id: number,
      //   syid: number,
      //   discount_id: number,
      //   // Optional referral fields (UI will include these for referral-type discounts):
      //   // - referrer_student_id?: number
      //   // - referrer_name?: string
      // }
      create: function (payload) {
        return $http.post(BASE + '/scholarships/assignments', payload, _adminHeaders()).then(_unwrap);
      },

      // PATCH /scholarships/assignments/apply
      // payload: { ids: number[], force?: boolean }
      apply: function (ids, force) {
        var payload = { ids: ids };
        if (typeof force === 'boolean') payload.force = !!force;
        return $http.patch(BASE + '/scholarships/assignments/apply', payload, _adminHeaders()).then(_unwrap);
      },

      // DELETE /scholarships/assignments/{id}
      remove: function (id) {
        return $http.delete(BASE + '/scholarships/assignments/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      }
    };
  }
})();
