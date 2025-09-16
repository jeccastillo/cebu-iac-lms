(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('StudentAdvisorService', StudentAdvisorService);

  StudentAdvisorService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function StudentAdvisorService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    function _getLoginState() {
      try { return StorageService.getJSON('loginState') || null; } catch (e) { return null; }
    }

    function _adminHeaders(extra) {
      var state = _getLoginState();
      var headers = Object.assign({ 'Accept': 'application/json' }, extra || {});
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      // GET /student-advisors?student_id=... | ?student_number=...
      getByStudent: function (opts) {
        var params = {};
        if (opts && opts.student_id) params.student_id = parseInt(opts.student_id, 10);
        if (opts && opts.student_number) params.student_number = ('' + opts.student_number).trim();
        return $http.get(BASE + '/student-advisors', Object.keys(params).length ? { params: params, headers: _adminHeaders().headers } : _adminHeaders())
          .then(_unwrap);
      },

      // POST /advisors/{advisorId}/assign-bulk
      // payload: { student_ids?: number[], student_numbers?: string[], replace_existing?: boolean }
      assignBulk: function (advisorId, payload) {
        payload = payload || {};
        // Ensure advisor_id is included in body for parity/analytics
        if (advisorId != null && payload.advisor_id == null) {
          var aid = parseInt(advisorId, 10);
          if (isFinite(aid)) payload.advisor_id = aid;
        }
        return $http.post(
          BASE + '/advisors/' + encodeURIComponent(advisorId) + '/assign-bulk',
          payload,
          _adminHeaders({ 'Content-Type': 'application/json' })
        ).then(_unwrap);
      },

      // POST /advisors/switch
      // payload: { from_advisor_id: number, to_advisor_id: number }
      switchAll: function (payload) {
        payload = payload || {};
        return $http.post(
          BASE + '/advisors/switch',
          payload,
          _adminHeaders({ 'Content-Type': 'application/json' })
        ).then(_unwrap);
      },

      // DELETE /student-advisors/{studentId}
      unassign: function (studentId) {
        return $http.delete(
          BASE + '/student-advisors/' + encodeURIComponent(studentId),
          _adminHeaders()
        ).then(_unwrap);
      },

      // GET /student-advisors/list?campus_id=ID&page=N&per_page=M&has_advisor=1|0
      // Returns paginated list of students with their advisor_name, sorted by last_name then first_name (API-side)
      // opts: { campusId?: number, page?: number, perPage?: number, hasAdvisor?: '1'|'0'|true|false|'with'|'without' }
      listByCampus: function (opts) {
        opts = opts || {};
        var params = {};
        try {
          if (opts.campusId !== undefined && opts.campusId !== null && ('' + opts.campusId).trim() !== '') {
            var cid = parseInt(opts.campusId, 10);
            if (isFinite(cid)) params.campus_id = cid;
          }
        } catch (e) {}
        try {
          if (opts.page !== undefined && opts.page !== null && ('' + opts.page).trim() !== '') {
            var p = parseInt(opts.page, 10);
            if (isFinite(p) && p > 0) params.page = p;
          }
        } catch (e2) {}
        try {
          if (opts.perPage !== undefined && opts.perPage !== null && ('' + opts.perPage).trim() !== '') {
            var pp = parseInt(opts.perPage, 10);
            if (isFinite(pp) && pp > 0) params.per_page = pp;
          }
        } catch (e3) {}
        try {
          if (opts.hasAdvisor !== undefined && opts.hasAdvisor !== null && ('' + opts.hasAdvisor).trim() !== '') {
            var hv = ('' + opts.hasAdvisor).toLowerCase().trim();
            if (hv === 'with' || hv === '1' || hv === 'true' || hv === 'yes') params.has_advisor = 1;
            else if (hv === 'without' || hv === '0' || hv === 'false' || hv === 'no') params.has_advisor = 0;
          }
        } catch (e4) {}
        return $http.get(
          BASE + '/student-advisors/list',
          { params: params, headers: _adminHeaders().headers }
        ).then(_unwrap);
      }
    };
  }
})();
