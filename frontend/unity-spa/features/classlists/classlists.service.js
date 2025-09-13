(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ClasslistsService', ClasslistsService);

  ClasslistsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ClasslistsService($http, APP_CONFIG, StorageService) {
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
      // Propagate roles to API for permission fallback when Gate/Auth user is missing
      try {
        var roles = null;
        if (state && Array.isArray(state.roles)) roles = state.roles;
        else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
        else if (state && typeof state.roles === 'string') roles = [state.roles];
        if (roles && roles.length) {
          headers['X-User-Roles'] = roles.join(',');
        }
      } catch (e) {
        // no-op
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return (resp && resp.data) ? resp.data : resp;
    }

    return {
      list: function (opts) {
        var params = {};
        if (opts && typeof opts.includeDissolved !== 'undefined') params.includeDissolved = !!opts.includeDissolved;
        if (opts && opts.term) params.strAcademicYear = opts.term; // API expects strAcademicYear
        if (opts && opts.intSubjectID) params.intSubjectID = opts.intSubjectID;
        if (opts && opts.intFacultyID) params.intFacultyID = opts.intFacultyID;
        if (opts && opts.sectionCode) params.sectionCode = opts.sectionCode;
        if (opts && (opts.intFinalized !== undefined && opts.intFinalized !== '')) params.intFinalized = opts.intFinalized;
        // Pagination params
        if (opts && (opts.page !== undefined && opts.page !== null)) params.page = opts.page;
        if (opts && (opts.per_page !== undefined && opts.per_page !== null)) params.per_page = opts.per_page;
        return $http.get(BASE + '/classlists', { params: params }).then(_unwrap);
      },
      // Aggregate all pages to return the full classlists set (no pagination for UI selects)
      listAll: function (opts) {
        var perPage = (opts && opts.per_page) ? opts.per_page : 200;
        var page = 1;
        var acc = [];

        // Build base params with the same mapping used by list()
        function buildBaseParams(o) {
          var params = {};
          if (o && typeof o.includeDissolved !== 'undefined') params.includeDissolved = !!o.includeDissolved;
          if (o && o.term) params.strAcademicYear = o.term; // API expects strAcademicYear
          if (o && o.intSubjectID) params.intSubjectID = o.intSubjectID;
          if (o && o.intFacultyID) params.intFacultyID = o.intFacultyID;
          if (o && o.sectionCode) params.sectionCode = o.sectionCode;
          if (o && (o.intFinalized !== undefined && o.intFinalized !== '')) params.intFinalized = o.intFinalized;
          return params;
        }

        var baseParams = buildBaseParams(opts || {});

        function normalizeRows(res) {
          // _unwrap returns either an array (when controller returns plain list)
          // or an object { data: [...], meta: {...} } when paginated
          if (res && Array.isArray(res)) return res;
          if (res && res.data && Array.isArray(res.data)) return res.data;
          return [];
        }

        function getMeta(res) {
          return (res && res.meta) ? res.meta : null;
        }

        function next() {
          var params = Object.assign({}, baseParams, { page: page, per_page: perPage });
          return $http.get(BASE + '/classlists', { params: params })
            .then(_unwrap)
            .then(function (res) {
              var rows = normalizeRows(res);
              var meta = getMeta(res);
              acc = acc.concat(rows);

              var hasMore = false;
              if (meta && typeof meta.total !== 'undefined' && meta.total !== null) {
                hasMore = acc.length < meta.total && rows.length > 0;
              } else {
                hasMore = rows.length === perPage;
              }

              if (hasMore) {
                page += 1;
                return next();
              }
              return acc;
            });
        }

        return next();
      },
      get: function (id) {
        return $http.get(BASE + '/classlists/' + encodeURIComponent(id)).then(_unwrap);
      },
      create: function (payload) {
        // Restricted fields are ignored by backend and overwritten to "", so do not include them here at all.
        return $http.post(BASE + '/classlists', payload, _adminHeaders()).then(_unwrap);
      },
      update: function (id, payload) {
        return $http.put(BASE + '/classlists/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      dissolve: function (id) {
        return $http.delete(BASE + '/classlists/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },
      // Helpers for filters
      getFacultyOptions: function () {
        // Filter to teaching=1 per requirement
        return $http.get(BASE + '/generic/faculty', { params: { teaching: 1 } }).then(function (resp) {
          var data = _unwrap(resp);
          // API returns { success: true, data: [ { id, first_name, middle_name, last_name, full_name, ... } ] }
          var rows = (data && data.data) ? data.data : (Array.isArray(data) ? data : []);
          // Normalize to expected shape for dropdown: { intID, strFirstname, strLastname, full_name }
          var normalized = rows.map(function (it) {
            var id = it.intID || it.id;
            var first = it.strFirstname || it.first_name || '';
            var middle = it.strMiddlename || it.middle_name || '';
            var last = it.strLastname || it.last_name || '';
            var full = it.full_name || [first, middle, last].filter(Boolean).join(' ').trim();
            return {
              intID: id,
              strFirstname: first,
              strLastname: last,
              full_name: full
            };
          });
          return { success: true, data: normalized };
        });
      },
      getSubjectsByTerm: function (term) {
        // Use registrar grading sections to get subjects for the selected term
        return $http.get(BASE + '/registrar/grading/sections', { params: { term: term } })
          .then(function (resp) {
            var data = _unwrap(resp);
            var subjects = (data && data.data && data.data.subjects) ? data.data.subjects : [];
            return { success: true, data: subjects };
          });
      },
      // Grading Viewer + Ops
      getViewer: function (id) {
        // Include admin/faculty context headers so backend can derive permissions when Gate user is missing
        return $http.get(BASE + '/classlists/' + encodeURIComponent(id) + '/viewer', _adminHeaders()).then(_unwrap);
      },
      saveGrades: function (id, payload) {
        return $http.post(BASE + '/classlists/' + encodeURIComponent(id) + '/grades', payload, _adminHeaders()).then(_unwrap);
      },
      finalize: function (id, payload) {
        return $http.post(BASE + '/classlists/' + encodeURIComponent(id) + '/finalize', payload || {}, _adminHeaders()).then(_unwrap);
      },
      unfinalize: function (id) {
        return $http.post(BASE + '/classlists/' + encodeURIComponent(id) + '/unfinalize', {}, _adminHeaders()).then(_unwrap);
      },

      // -----------------------------
      // Import Template + Import (parity with Subjects)
      // -----------------------------

      // Download import template (.xlsx)
      downloadImportTemplate: function () {
        var cfg = { responseType: 'arraybuffer', headers: {} };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            cfg.headers['X-Faculty-ID'] = state.faculty_id;
          }
          // propagate roles if present (parity with _adminHeaders)
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            cfg.headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        return $http.get(BASE + '/classlists/import/template', cfg).then(function (resp) {
          var headers = resp && resp.headers ? resp.headers : null;
          var filename = 'classlists-import-template.xlsx';
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

      // Import classlists file (.xlsx/.xls/.csv)
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
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        return $http.post(BASE + '/classlists/import', fd, {
          headers: headers,
          transformRequest: angular.identity
        }).then(_unwrap);
      },
      // Merge classlists: POST /classlists/merge
      merge: function (payload) {
        // payload: { target_id: number, source_ids: number[], options?: {} }
        return $http.post(BASE + '/classlists/merge', payload, _adminHeaders()).then(_unwrap);
      },

      // -----------------------------
      // Attendance APIs
      // -----------------------------
      getAttendanceDates: function (classlistId) {
        return $http.get(BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/dates', _adminHeaders()).then(_unwrap);
      },
      createAttendanceDate: function (classlistId, date, period) {
        function toYMD(val) {
          try {
            // Handle native Date object (AngularJS type="date" binds to Date)
            if (val && Object.prototype.toString.call(val) === '[object Date]') {
              if (isNaN(val.getTime())) return null;
              var y = val.getFullYear();
              var m = ('0' + (val.getMonth() + 1)).slice(-2);
              var d = ('0' + val.getDate()).slice(-2);
              return y + '-' + m + '-' + d;
            }
            // Handle strings: either already YYYY-MM-DD or parseable ISO/local
            if (typeof val === 'string') {
              if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return val;
              var dt = new Date(val);
              if (!isNaN(dt.getTime())) {
                var yy = dt.getFullYear();
                var mm = ('0' + (dt.getMonth() + 1)).slice(-2);
                var dd = ('0' + dt.getDate()).slice(-2);
                return yy + '-' + mm + '-' + dd;
              }
            }
          } catch (e) {}
          return null;
        }
        var payload = { date: toYMD(date) || date, period: (period || 'midterm') };
        return $http.post(BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/dates', payload, _adminHeaders()).then(_unwrap);
      },
      getAttendanceByDate: function (classlistId, dateId) {
        return $http.get(BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/dates/' + encodeURIComponent(dateId), _adminHeaders()).then(_unwrap);
      },
      saveAttendance: function (classlistId, dateId, items) {
        var payload = { items: items };
        return $http.put(BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/dates/' + encodeURIComponent(dateId), payload, _adminHeaders()).then(_unwrap);
      },

      // Download attendance template (.xlsx) for a specific date
      downloadAttendanceTemplate: function (classlistId, dateId) {
        var cfg = { responseType: 'arraybuffer', headers: {} };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            cfg.headers['X-Faculty-ID'] = state.faculty_id;
          }
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            cfg.headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        var url = BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/dates/' + encodeURIComponent(dateId) + '/template';
        return $http.get(url, cfg).then(function (resp) {
          var headers = resp && resp.headers ? resp.headers : null;
          var filename = 'classlist-' + classlistId + '-attendance-' + dateId + '-template.xlsx';
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

      // Import attendance (.xlsx) for a specific date
      importAttendance: function (classlistId, dateId, file) {
        if (!file) {
          return Promise.reject({ message: 'No file selected' });
        }
        var fd = new FormData();
        fd.append('file', file);
        var headers = { 'Content-Type': undefined };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            headers['X-Faculty-ID'] = state.faculty_id;
          }
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        var url = BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/dates/' + encodeURIComponent(dateId) + '/import';
        return $http.post(url, fd, {
          headers: headers,
          transformRequest: angular.identity
        }).then(_unwrap);
      },

      // Download attendance matrix template (.xlsx) for a classlist over a date range (per period)
      downloadAttendanceMatrixTemplate: function (classlistId, start, end, period) {
        // Local toYMD helper (mirrors logic in createAttendanceDate)
        function toYMD(val) {
          try {
            if (val && Object.prototype.toString.call(val) === '[object Date]') {
              if (isNaN(val.getTime())) return null;
              var y = val.getFullYear();
              var m = ('0' + (val.getMonth() + 1)).slice(-2);
              var d = ('0' + val.getDate()).slice(-2);
              return y + '-' + m + '-' + d;
            }
            if (typeof val === 'string') {
              if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return val;
              var dt = new Date(val);
              if (!isNaN(dt.getTime())) {
                var yy = dt.getFullYear();
                var mm = ('0' + (dt.getMonth() + 1)).slice(-2);
                var dd = ('0' + dt.getDate()).slice(-2);
                return yy + '-' + mm + '-' + dd;
              }
            }
          } catch (e) {}
          return null;
        }

        var cfg = { responseType: 'arraybuffer', headers: {}, params: {
          start: toYMD(start) || start,
          end: toYMD(end) || end,
          period: (period || 'midterm')
        } };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            cfg.headers['X-Faculty-ID'] = state.faculty_id;
          }
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            cfg.headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        var url = BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/matrix/template';
        return $http.get(url, cfg).then(function (resp) {
          var headers = resp && resp.headers ? resp.headers : null;
          var filename = 'classlist-' + classlistId + '-attendance-matrix-' + (period || 'midterm') + '.xlsx';
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

      // Import attendance matrix (.xlsx) for a classlist over a date range (per period)
      importAttendanceMatrix: function (classlistId, file, period) {
        if (!file) {
          return Promise.reject({ message: 'No file selected' });
        }
        var fd = new FormData();
        fd.append('file', file);
        fd.append('period', (period || 'midterm'));
        var headers = { 'Content-Type': undefined };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            headers['X-Faculty-ID'] = state.faculty_id;
          }
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        var url = BASE + '/classlists/' + encodeURIComponent(classlistId) + '/attendance/matrix/import';
        return $http.post(url, fd, {
          headers: headers,
          transformRequest: angular.identity
        }).then(_unwrap);
      },

      // -----------------------------
      // Grades Excel Template & Import
      // -----------------------------

      // Download per-classlist grades template (.xlsx) for a period
      downloadGradesTemplate: function (id, period) {
        var cfg = { responseType: 'arraybuffer', headers: {}, params: { period: period } };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            cfg.headers['X-Faculty-ID'] = state.faculty_id;
          }
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            cfg.headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        return $http.get(BASE + '/classlists/' + encodeURIComponent(id) + '/grades/template', cfg).then(function (resp) {
          var headers = resp && resp.headers ? resp.headers : null;
          var filename = 'classlist-' + id + '-' + (period || 'period') + '-template.xlsx';
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

      // Import grades (.xlsx only) for a period
      importGrades: function (id, file, period) {
        if (!file) {
          return Promise.reject({ message: 'No file selected' });
        }
        var fd = new FormData();
        fd.append('file', file);
        fd.append('period', period);
        var headers = { 'Content-Type': undefined };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            headers['X-Faculty-ID'] = state.faculty_id;
          }
          var roles = null;
          if (state && Array.isArray(state.roles)) roles = state.roles;
          else if (state && Array.isArray(state.role_codes)) roles = state.role_codes;
          else if (state && typeof state.roles === 'string') roles = [state.roles];
          if (roles && roles.length) {
            headers['X-User-Roles'] = roles.join(',');
          }
        } catch (e) {}
        return $http.post(BASE + '/classlists/' + encodeURIComponent(id) + '/grades/import', fd, {
          headers: headers,
          transformRequest: angular.identity
        }).then(_unwrap);
      }
    };
  }

})();
