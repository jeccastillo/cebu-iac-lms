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
      listByFaculty: listByFaculty,
      listUnassigned: listUnassigned,
      updateSingle: updateSingle,
      assignBulk: assignBulk,
      facultyOptions: facultyOptions,
      exportAssignments: exportAssignments
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
     * Export faculty assignments to Excel.
     * params: {
     *   term: number (required),
     *   intFacultyID?: number,
     *   sectionCode?: string,
     *   subjectCode?: string,
     *   includeUnassigned?: boolean,
     *   includeDissolved?: boolean
     * }
     * Returns a Promise resolving to a Blob download.
     */
    function exportAssignments(params) {
      var query = Object.assign({}, params || {});
      // Coerce boolean-like flags to 1/0 so Laravel boolean validator accepts them
      if (typeof query.includeUnassigned !== 'undefined') {
        query.includeUnassigned = query.includeUnassigned ? 1 : 0;
      }
      if (typeof query.includeDissolved !== 'undefined') {
        query.includeDissolved = query.includeDissolved ? 1 : 0;
      }
      var cfg = Object.assign(
        { params: query, responseType: 'arraybuffer' },
        _adminHeaders({ 'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
      );
      return $http.get(BASE + '/classlists/export-faculty-assignments', cfg).then(function (res) {
        var blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        var contentDisposition = res.headers('content-disposition') || '';
        var filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i);
        var filename = filenameMatch ? filenameMatch[1].replace(/['"]/g, '') : 'faculty-assignments.xlsx';
        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
          window.navigator.msSaveOrOpenBlob(blob, filename);
        } else {
          var link = document.createElement('a');
          link.href = window.URL.createObjectURL(blob);
          link.download = filename;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        }
        return Promise.resolve();
      }, _unwrapErr);
    }

    /**
     * Convenience wrapper to list classlists assigned to a specific faculty.
     * @param {{ term:number, facultyId?:number, intFacultyID?:number, page?:number, per_page?:number }} params
     */
    function listByFaculty(params) {
      var p = Object.assign({}, params || {});           
      var fid = p.facultyId != null ? p.facultyId : p.intFacultyID;
      if (fid != null) {
        p.intFacultyID = fid;
      }
      delete p.facultyId;
      return list(p);
    }

    /**
     * Client-side "unassigned" list. Fetch page and filter locally where intFacultyID is null/0/''.
     * Returns a list-shaped object with filtered data. Meta is adjusted for data length only (server totals preserved via original meta if needed).
     * @param {{ term:number, page?:number, per_page?:number }} params
     */
    function listUnassigned(params) {
      // Aggregate across pages (server caps per_page at 100). We fetch pages until we have
      // enough unassigned rows to fill the requested page or we reach the last page.
      
      var desiredPage = parseInt(params && params.page, 10) || 1;
      var desiredPerPage = parseInt(params && params.per_page, 10) || 20;
      var base = Object.assign({}, params || {});      
      var page = 1;
      var perPage = 100;
      var filtered = [];
      var lastPage = null;
      
      function isUnassigned(r) {
        var v = r && r.intFacultyID;
        // Treat 0, '0', null, '', undefined as unassigned
        return v === null || v === 0 || v === '' || v === '0' || typeof v === 'undefined';
      }
      
      function loop() {
        var q = Object.assign({}, base, { page: page, per_page: perPage });
        return list(q).then(function (res) {          
          var data = Array.isArray(res.data) ? res.data : [];          
          filtered.push.apply(filtered, data.filter(isUnassigned));
          console.log(filtered);
          var meta = res.meta || {};
          lastPage = parseInt(meta.last_page, 10) || page; // fallback to current if missing
  
          // Stop early if we already have enough rows to fill the requested page
          var need = desiredPage * desiredPerPage;
          if (filtered.length >= need) {
            return done();
          }
  
          page += 1;
          if (page > lastPage) {
            return done();
          }
          return loop();
        });
      }
  
      function done() {
        var total = filtered.length;
        var computedLast = Math.max(1, Math.ceil(total / desiredPerPage));
        if (desiredPage > computedLast) desiredPage = computedLast;
        var start = (desiredPage - 1) * desiredPerPage;
        var end = start + desiredPerPage;
        var pageData = filtered.slice(start, end);             
        return {
          success: true,
          data: pageData,
          meta: {
            current_page: desiredPage,
            per_page: desiredPerPage,
            total: total,
            last_page: computedLast
          }
        };
      }
  
      return loop();
    }

    /**
     * Update a single classlist faculty assignment.
     * @param {number} classlistId
     * @param {number|null} facultyId
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
