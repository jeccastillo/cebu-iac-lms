(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('FacultyLoadingService', FacultyLoadingService);

  FacultyLoadingService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function FacultyLoadingService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

    var svc = {
      list: list,
      updateSingle: updateSingle,
      assignBulk: assignBulk,
      facultyOptions: facultyOptions
    };
    return svc;

    // ---------------- Helpers ----------------
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

    // ---------------- API ----------------
    /**
     * List classlists for a given term.
     * params: {
     *   term: number (required),
     *   page?: number,
     *   per_page?: number,
     *   intSubjectID?, intFacultyID?, sectionCode?, intFinalized?
     * }
     * Returns { success, data: [], meta: { current_page, per_page, total, last_page } }
     */
    function list(params) {
      var query = Object.assign({}, params || {});
      var cfg = Object.assign({ params: query }, _adminHeaders());
      return $http.get(BASE + '/classlists', cfg).then(_unwrap, _unwrapErr);
    }

    /**
     * Update a single classlist faculty assignment.
     * @param {number} classlistId
     * @param {number} facultyId
     * Body: { intFacultyID: facultyId }
     */
    function updateSingle(classlistId, facultyId) {
      var body = { intFacultyID: facultyId };
      var cfg = _adminHeaders({ 'Content-Type': 'application/json' });
      return $http.put(BASE + '/classlists/' + classlistId, body, cfg).then(_unwrap, _unwrapErr);
    }

    /**
     * Bulk assignment of faculty to classlists.
     * @param {number} term tb_mas_sy.intID
     * @param {Array<{classlist_id:number, faculty_id:number}>} assignments
     * Returns { success, applied_count, total, results: [{classlist_id, ok, message?}] }
     */
    function assignBulk(term, assignments) {
      var body = { term: term, assignments: assignments || [] };
      var cfg = _adminHeaders({ 'Content-Type': 'application/json' });
      return $http.post(BASE + '/classlists/assign-faculty-bulk', body, cfg).then(_unwrap, _unwrapErr);
    }

    /**
     * Fetch faculty options.
     * @param {Object} params
     *    - q?: string
     *    - id?: number
     *    - teaching?: 0|1 (defaults to 1 if not provided)
     *    - campus_id?: number
     * Returns { success, data: FacultyOption[] }
     */
    function facultyOptions(params) {
      var query = Object.assign({ teaching: 1 }, params || {});
      var cfg = Object.assign({ params: query }, _adminHeaders());
      return $http.get(BASE + '/generic/faculty', cfg).then(_unwrap, _unwrapErr);
    }

    // --------------- Internal response helpers ---------------
    function _unwrap(res) {
      if (res && res.data) return res.data;
      return { success: false, data: null };
    }

    function _unwrapErr(err) {
      // Normalize error structure
      var msg = (err && err.data && (err.data.message || err.data.error)) || err.statusText || 'Request failed';
      return Promise.reject({ success: false, message: msg, raw: err });
    }
  }
})();
