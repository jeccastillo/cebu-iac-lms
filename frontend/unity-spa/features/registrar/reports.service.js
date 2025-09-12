(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ReportsService', ReportsService);

  ReportsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ReportsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    var svc = {
      exportEnrolled: exportEnrolled,
      exportEnrollmentStatsPdf: exportEnrollmentStatsPdf,
      generateStudentTranscript: generateStudentTranscript,
      transcriptFee: transcriptFee,
      listTranscriptRequests: listTranscriptRequests,
      reprintTranscript: reprintTranscript,
      createTranscriptBilling: createTranscriptBilling
    };

    return svc;

    // --------------- Helpers ---------------

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
      // RequireRole middleware expects X-Faculty-ID on protected routes
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    // --------------- API ---------------

    /**
     * Export enrolled students for a term as XLSX.
     * @param {number|string} syid - tb_mas_sy.intID (selected term)
     * Returns full $http response to access headers and binary data.
     */
    function exportEnrolled(syid) {
      var params = { syid: syid };
      var cfg = Object.assign({ params: params, responseType: 'arraybuffer' }, _adminHeaders());
      return $http.get(BASE + '/reports/enrolled-students/export', cfg);
    }

    /**
     * Get Enrollment Statistics PDF for a term (opens inline/new tab).
     * @param {number|string} syid
     * Returns full $http response (arraybuffer) for caller to open in new tab.
     */
    function exportEnrollmentStatsPdf(syid) {
      var params = { syid: syid };
      var cfg = Object.assign({ params: params, responseType: 'arraybuffer' }, _adminHeaders());
      return $http.get(BASE + '/reports/enrollment-statistics/pdf', cfg);
    }

    /**
     * Generate Transcript/Copy of Grades PDF for a student.
     * @param {number|string} studentId
     * @param {Object} payload { date_issued, remarks?, prepared_by?, verified_by?, registrar_signatory?, signatory?, type, term_ids:[] }
     * @return {$http.Promise} full response with ArrayBuffer PDF
     */
    function generateStudentTranscript(studentId, payload) {
      var url = BASE + '/reports/students/' + encodeURIComponent(studentId) + '/transcript';
      var cfg = Object.assign({ responseType: 'arraybuffer' }, _adminHeaders());
      return $http.post(url, payload, cfg);
    }

    /**
     * Resolve transcript/copy fee and PD id.
     * @param {number|string} studentId
     * @param {'transcript'|'copy'} type
     * @param {number|string=} termId
     * @return {Promise<{description:string, amount:number|null, payment_description_id:number|null, campus_id:number|null}>}
     */
    function transcriptFee(studentId, type, termId) {
      var params = { student_id: studentId, type: type };
      if (termId != null) { params.term_id = termId; }
      var cfg = Object.assign({ params: params }, _adminHeaders());
      return $http.get(BASE + '/reports/transcript-fee', cfg).then(function (resp) {
        return (resp && resp.data && resp.data.data) ? resp.data.data : null;
      });
    }

    /**
     * List transcript requests history for student.
     * @param {number|string} studentId
     * @return {Promise<Array>}
     */
    function listTranscriptRequests(studentId, termId) {
      var cfg = Object.assign({}, _adminHeaders());
      if (termId != null && termId !== '') {
        cfg.params = { term_id: termId };
      }
      return $http.get(BASE + '/reports/students/' + encodeURIComponent(studentId) + '/transcripts', cfg)
        .then(function (resp) {
          return (resp && resp.data && resp.data.data) ? resp.data.data : [];
        });
    }

    /**
     * Reprint a saved transcript request as PDF (arraybuffer).
     * @param {number|string} studentId
     * @param {number|string} requestId
     * @return {$http.Promise}
     */
    function reprintTranscript(studentId, requestId) {
      var cfg = Object.assign({ responseType: 'arraybuffer' }, _adminHeaders());
      return $http.get(
        BASE + '/reports/students/' + encodeURIComponent(studentId) + '/transcripts/' + encodeURIComponent(requestId) + '/reprint',
        cfg
      );
    }

    /**
     * Create student billing for a transcript request if missing.
     * @param {number|string} studentId
     * @param {number|string} requestId
     * @return {Promise<Object>} JSON response
     */
    function createTranscriptBilling(studentId, requestId, termId) {
      var cfg = Object.assign({}, _adminHeaders());
      var body = {};
      if (termId != null && termId !== '') {
        body.term_id = termId;
      }
      return $http.post(
        BASE + '/reports/students/' + encodeURIComponent(studentId) + '/transcripts/' + encodeURIComponent(requestId) + '/billing',
        body,
        cfg
      ).then(function (resp) {
        return (resp && resp.data) ? resp.data : null;
      });
    }
  }

})();
