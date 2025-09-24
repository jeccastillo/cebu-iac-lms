(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('SubjectsService', SubjectsService);

  SubjectsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function SubjectsService($http, APP_CONFIG, StorageService) {
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
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      // GET /subjects?search=&department=&page=&limit=
      list: function (opts) {
        var params = {};
        if (opts && opts.search && ('' + opts.search).trim() !== '') {
          params.search = ('' + opts.search).trim();
        }
        if (opts && typeof opts.department !== 'undefined' && opts.department !== null && opts.department !== '') {
          params.department = '' + opts.department;
        }
        if (opts && typeof opts.page !== 'undefined' && opts.page !== null && opts.page !== '') {
          params.page = parseInt(opts.page, 10);
        }
        if (opts && typeof opts.limit !== 'undefined' && opts.limit !== null && opts.limit !== '') {
          params.limit = parseInt(opts.limit, 10);
        }
        return $http.get(BASE + '/subjects', { params: params }).then(_unwrap);
      },

      // GET /subjects/{id}
      get: function (id) {
        return $http.get(BASE + '/subjects/' + encodeURIComponent(id)).then(_unwrap);
      },

      // POST /subjects/submit
      create: function (payload) {
        return $http.post(BASE + '/subjects/submit', payload, _adminHeaders()).then(_unwrap);
      },

      // POST /subjects/edit (payload must include intID)
      update: function (id, payload) {
        var body = Object.assign({}, payload || {});
        if (id !== undefined && id !== null && id !== '') {
          body.intID = parseInt(id, 10);
        }
        return $http.post(BASE + '/subjects/edit', body, _adminHeaders()).then(_unwrap);
      },

      // POST /subjects/delete
      destroy: function (id) {
        return $http.post(BASE + '/subjects/delete', { id: id }, _adminHeaders()).then(_unwrap);
      },

      // GET /subjects/{id}/prerequisites
      getPrereqs: function (subjectId) {
        return $http.get(
          BASE + '/subjects/' + encodeURIComponent(subjectId) + '/prerequisites'
        ).then(_unwrap);
      },

      // POST /subjects/submit-prereq
      // Body: { intSubjectID, program, intPrerequisiteID, required_grade }
      addPrereq: function (subjectId, prereqId, program, requiredGrade) {
        var body = {
          intSubjectID: (subjectId !== null && subjectId !== undefined) ? parseInt(subjectId, 10) : null,
          intPrerequisiteID: (prereqId !== null && prereqId !== undefined) ? parseInt(prereqId, 10) : null
        };
        if (typeof program !== 'undefined') {
          body.program = program;
        }
        if (typeof requiredGrade !== 'undefined' && requiredGrade !== null) {
          body.required_grade = parseFloat(requiredGrade);
        }
        return $http.post(BASE + '/subjects/submit-prereq', body, _adminHeaders()).then(_unwrap);
      },

      // POST /subjects/delete-prereq
      // Body: { id }
      removePrereq: function (rowId) {
        return $http.post(BASE + '/subjects/delete-prereq', { id: rowId }, _adminHeaders()).then(_unwrap);
      },

      // -----------------------------
      // Corequisites (parity with prerequisites)
      // -----------------------------

      // GET /subjects/{id}/corequisites
      getCoreqs: function (subjectId) {
        return $http.get(
          BASE + '/subjects/' + encodeURIComponent(subjectId) + '/corequisites'
        ).then(_unwrap);
      },

      // POST /subjects/submit-coreq
      // Body: { intSubjectID, program, intCorequisiteID }
      addCoreq: function (subjectId, coreqId, program) {
        var body = {
          intSubjectID: (subjectId !== null && subjectId !== undefined) ? parseInt(subjectId, 10) : null,
          intCorequisiteID: (coreqId !== null && coreqId !== undefined) ? parseInt(coreqId, 10) : null
        };
        if (typeof program !== 'undefined') {
          body.program = program;
        }
        return $http.post(BASE + '/subjects/submit-coreq', body, _adminHeaders()).then(_unwrap);
      },

      // POST /subjects/delete-coreq
      // Body: { id }
      removeCoreq: function (rowId) {
        return $http.post(BASE + '/subjects/delete-coreq', { id: rowId }, _adminHeaders()).then(_unwrap);
      },

      // POST /subjects/{id}/check-corequisites
      // Body: { student_number: string, program?: string, planned_subject_ids?: [int...] }
      checkCoreqs: function (subjectId, payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(
          BASE + '/subjects/' + encodeURIComponent(subjectId) + '/check-corequisites',
          body
        ).then(_unwrap);
      },

      // POST /subjects/check-corequisites-batch
      // Body: { student_number: string, subject_ids: [int...], program?: string, planned_subject_ids?: [int...] }
      checkCoreqsBatch: function (payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(
          BASE + '/subjects/check-corequisites-batch',
          body
        ).then(_unwrap);
      },

      // Download import template (.xlsx)
      downloadImportTemplate: function () {
        var cfg = { responseType: 'arraybuffer', headers: {} };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            cfg.headers['X-Faculty-ID'] = state.faculty_id;
          }
        } catch (e) {}
        return $http.get(BASE + '/subjects/import/template', cfg).then(function (resp) {
          var headers = resp && resp.headers ? resp.headers : null;
          var filename = 'subjects-import-template.xlsx';
          try {
            if (headers && typeof headers === 'function') {
              var cd = headers('Content-Disposition') || headers('content-disposition');
              if (cd && /filename="?([^"]+)"?/i.test(cd)) {
                filename = cd.match(/filename="?([^"]+)"?/i)[1];
              }
            }
          } catch (e) {}
          return { data: resp.data, filename: filename };
        });
      },

      // Import subjects file (.xlsx/.xls/.csv)
      importFile: function (file, opts) {
        if (!file) {
          return Promise.reject({ message: 'No file selected' });
        }
        var fd = new FormData();
        fd.append('file', file);
        if (opts && typeof opts.dry_run !== 'undefined') {
          fd.append('dry_run', opts.dry_run ? '1' : '0');
        }
        var headers = { 'Content-Type': undefined };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            headers['X-Faculty-ID'] = state.faculty_id;
          }
        } catch (e) {}
        return $http.post(BASE + '/subjects/import', fd, {
          headers: headers,
          transformRequest: angular.identity
        }).then(_unwrap);
      }
    };
  }

})();
