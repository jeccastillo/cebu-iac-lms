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

    return {
      listAll: listAll,
      clearCache: clearCache
    };
  }
})();
