(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('InterviewsService', InterviewsService);

  InterviewsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function InterviewsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

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
      // Send both headers for backend context resolution compatibility
      // Primary: X-User-ID (resolver checks this first)
      if (state && state.user_id != null && !isNaN(parseInt(state.user_id, 10))) {
        headers['X-User-ID'] = parseInt(state.user_id, 10);
      }
      // Legacy/admin: X-Faculty-ID (resolver now also accepts this)
      if (state && state.faculty_id != null && !isNaN(parseInt(state.faculty_id, 10))) {
        headers['X-Faculty-ID'] = parseInt(state.faculty_id, 10);
        // If X-User-ID not set, mirror faculty_id for X-User-ID to ensure stamping
        if (headers['X-User-ID'] == null) {
          headers['X-User-ID'] = parseInt(state.faculty_id, 10);
        }
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    // Normalize various datetime inputs into SQL 'YYYY-MM-DD HH:mm:ss'
    function _toSqlDateTime(val) {
      if (!val) return val;
      try {
        // If a Date object is passed, format directly
        if (val instanceof Date) {
          var d0 = val;
          function pad0(n) { return n.toString().padStart(2, '0'); }
          var Y0 = d0.getFullYear();
          var M0 = pad0(d0.getMonth() + 1);
          var D0 = pad0(d0.getDate());
          var H0 = pad0(d0.getHours());
          var I0 = pad0(d0.getMinutes());
          var S0 = pad0(d0.getSeconds());
          return Y0 + '-' + M0 + '-' + D0 + ' ' + H0 + ':' + I0 + ':' + S0;
        }

        if (typeof val === 'string') {
          var s = val.trim();
          // Strip trailing Z and normalize T separator
          s = s.replace(/Z$/i, '');

          // Handle HTML datetime-local like '2025-09-04T09:00' or '2025-09-04 09:00' or with seconds
          var m1 = s.match(/^(\d{4}-\d{2}-\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/);
          if (m1) {
            var sec = (m1[4] !== undefined && m1[4] !== null && m1[4] !== '') ? m1[4] : '00';
            return m1[1] + ' ' + m1[2] + ':' + m1[3] + ':' + sec;
          }

          // If already 'YYYY-MM-DD HH:mm' add ':00'
          if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(s)) {
            return s + ':00';
          }

          // If already 'YYYY-MM-DD HH:mm:ss' return as-is
          if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(s)) {
            return s;
          }

          // Try native Date parsing last (non-standard strings may fail on some browsers)
          var tryDate = new Date(s);
          if (!isNaN(tryDate.getTime())) {
            var d = tryDate;
            function pad(n) { return n.toString().padStart(2, '0'); }
            var Y = d.getFullYear();
            var M = pad(d.getMonth() + 1);
            var D = pad(d.getDate());
            var H = pad(d.getHours());
            var I = pad(d.getMinutes());
            var S = pad(d.getSeconds());
            return Y + '-' + M + '-' + D + ' ' + H + ':' + I + ':' + S;
          }

          // Fallback: US style 'MM/DD/YYYY hh:mm AM/PM'
          var m = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})[ ,T]+(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
          if (m) {
            var mm = parseInt(m[1], 10) - 1;
            var dd = parseInt(m[2], 10);
            var yyyy = parseInt(m[3], 10);
            var hh = parseInt(m[4], 10);
            var min = parseInt(m[5], 10);
            var mer = m[6].toUpperCase();
            if (mer === 'PM' && hh < 12) hh += 12;
            if (mer === 'AM' && hh === 12) hh = 0;
            function pad2(n) { return n.toString().padStart(2, '0'); }
            return yyyy + '-' + pad2(mm + 1) + '-' + pad2(dd) + ' ' + pad2(hh) + ':' + pad2(min) + ':00';
          }

          // As a last resort, return the normalized string with space separator if it looks ISO-like
          var m2 = s.match(/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})(?::(\d{2}))?$/);
          if (m2) {
            return m2[1] + ' ' + m2[2] + ':' + (m2[3] || '00');
          }

          return s; // let backend normalization handle if possible
        }

        // Non-string fallback
        var d2 = new Date(val);
        if (!isNaN(d2.getTime())) {
          function pad3(n) { return n.toString().padStart(2, '0'); }
          var Y2 = d2.getFullYear();
          var M2 = pad3(d2.getMonth() + 1);
          var D2 = pad3(d2.getDate());
          var H2 = pad3(d2.getHours());
          var I2 = pad3(d2.getMinutes());
          var S2 = pad3(d2.getSeconds());
          return Y2 + '-' + M2 + '-' + D2 + ' ' + H2 + ':' + I2 + ':' + S2;
        }
        return val;
      } catch (e) {
        return val;
      }
    }

    return {
      // GET /admissions/applicant-data/{applicantDataId}/interview
      getByApplicantData: function (applicantDataId) {
        if (!applicantDataId) {
          return Promise.reject({ message: 'Missing applicantDataId' });
        }
        return $http
          .get(BASE + '/admissions/applicant-data/' + encodeURIComponent(applicantDataId) + '/interview', _adminHeaders())
          .then(_unwrap);
      },

      // POST /admissions/interviews
      // payload: { applicant_data_id, scheduled_at, interviewer_user_id?, remarks? }
      schedule: function (applicantDataId, payload) {
        var body = Object.assign({}, payload || {});
        body.applicant_data_id = applicantDataId;

        // If interviewer_user_id not explicitly provided, derive from login state
        // Prefer explicit payload value; otherwise try user_id (if present) then faculty_id
        try {
          var state = _getLoginState();
          var hasExplicit = Object.prototype.hasOwnProperty.call(body, 'interviewer_user_id');
          var isUnset = !hasExplicit || body.interviewer_user_id === null || body.interviewer_user_id === undefined || body.interviewer_user_id === '';
          if (isUnset && state) {
            if (state.user_id != null && !isNaN(parseInt(state.user_id, 10))) {
              body.interviewer_user_id = parseInt(state.user_id, 10);
            } else if (state.faculty_id != null && !isNaN(parseInt(state.faculty_id, 10))) {
              // Fallback: use faculty_id when user_id is not available
              body.interviewer_user_id = parseInt(state.faculty_id, 10);
            }
          }
        } catch (_e) {
          // ignore derive errors
        }

        // Normalize datetime to an SQL-friendly format for the API
        if (body.scheduled_at !== undefined && body.scheduled_at !== null && body.scheduled_at !== '') {
          body.scheduled_at = _toSqlDateTime(body.scheduled_at);
        }
        return $http
          .post(BASE + '/admissions/interviews', body, _adminHeaders())
          .then(_unwrap);
      },

      // PUT /admissions/interviews/{id}/result
      // payload: { assessment: 'Passed'|'Failed'|'No Show', remarks?, reason_for_failing?, completed_at? }
      submitResult: function (interviewId, payload) {
        if (!interviewId) {
          return Promise.reject({ message: 'Missing interviewId' });
        }
        return $http
          .put(BASE + '/admissions/interviews/' + encodeURIComponent(interviewId) + '/result', payload, _adminHeaders())
          .then(_unwrap);
      }
    };
  }

})();
