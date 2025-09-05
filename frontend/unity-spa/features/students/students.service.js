(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('StudentsService', StudentsService);

  StudentsService.$inject = ['$http', 'APP_CONFIG', '$q'];
  function StudentsService($http, APP_CONFIG, $q) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1
    var _cache = null;              // cached normalized rows
    var _loading = null;            // in-flight promise to avoid duplicate fetches

    function normalizeRows(rows) {
      try {
        return (rows || []).map(function (r) {
          return {
            id: r.id != null ? r.id : (r.intID != null ? r.intID : null),
            student_number: r.student_number || r.strStudentNumber || '',
            last_name: r.last_name || r.strLastname || r.lastName || '',
            first_name: r.first_name || r.strFirstname || r.firstName || '',
            middle_name: r.middle_name || r.strMiddlename || r.middleName || ''
          };
        }).filter(function (r) { return r.id !== null; });
      } catch (e) {
        return [];
      }
    }

    function fetchPage(page, acc) {
      return $http.get(BASE + '/students', { params: { per_page: 100, page: page } })
        .then(function (resp) {
          var data = (resp && resp.data) ? resp.data : {};
          var rows = data && data.data ? data.data : (Array.isArray(data) ? data : []);
          var meta = data && data.meta ? data.meta : {};
          var total = meta && meta.total ? meta.total : (acc.length + rows.length);

          acc = acc.concat(rows);

          if (acc.length < total && rows.length > 0) {
            return fetchPage(page + 1, acc);
          }
          return acc;
        });
    }

    function listAll(force) {
      // return cached list when available
      if (!force && _cache) {
        return $q.when(_cache);
      }
      // if a fetch is in-flight, reuse it
      if (_loading && !force) {
        return _loading;
      }

      var acc = [];
      _loading = fetchPage(1, acc)
        .then(function (allRows) {
          _cache = normalizeRows(allRows);
          return _cache;
        })
        .catch(function () {
          _cache = _cache || [];
          return _cache;
        })
        .finally(function () {
          _loading = null;
        });

      return _loading;
    }

    function clearCache() {
      _cache = null;
    }

    // Fetch a single page for lightweight dropdowns (avoids recursive full fetch)
    function listPage(opts) {
      opts = opts || {};
      var per = parseInt(opts.per_page, 10);
      if (!isFinite(per) || per <= 0) per = 100;
      if (per > 100) per = 100; // backend caps per_page at 100
      var page = parseInt(opts.page, 10);
      if (!isFinite(page) || page <= 0) page = 1;
      var params = { per_page: per, page: page };
      // Optional search or filters passthrough
      try {
        if (opts.q != null && ('' + opts.q).trim().length > 0) params.q = ('' + opts.q).trim();
        if (opts.program) params.program = opts.program;
        if (opts.year_level) params.year_level = opts.year_level;
        if (opts.gender) params.gender = opts.gender;
        if (opts.inactive != null) params.inactive = opts.inactive;
        if (opts.graduated != null) params.graduated = opts.graduated;
        if (opts.registered != null) params.registered = opts.registered;
        if (opts.sem != null) params.sem = opts.sem;
        if (opts.syid != null) params.syid = opts.syid;
        if (opts.campus_id != null) params.campus_id = opts.campus_id;
        // Include applicants flag (0/1) when requested by caller
        if (opts.include_applicants != null) params.include_applicants = opts.include_applicants;
      } catch (e) {}
      return $http.get(BASE + '/students', { params: params })
        .then(function (resp) {
          var data = (resp && resp.data) ? resp.data : {};
          var rows = data && data.data ? data.data : (Array.isArray(data) ? data : []);
          return normalizeRows(rows);
        })
        .catch(function () { return []; });
    }

    // Convenience: return first-page suggestions for a free-text query
    function listSuggestions(q) {
      return listPage({ q: q, per_page: 20, page: 1 });
    }

    return {
      listAll: listAll,
      listPage: listPage,
      listSuggestions: listSuggestions,
      clearCache: clearCache
    };
  }
})();
