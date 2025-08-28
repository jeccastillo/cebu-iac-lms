(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('SchoolYearsService', SchoolYearsService);

  SchoolYearsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function SchoolYearsService($http, APP_CONFIG, StorageService) {
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
      list: function (opts) {
        var params = {};
        if (opts && opts.campus_id !== null && opts.campus_id !== undefined && opts.campus_id !== '') {
          params.campus_id = opts.campus_id;
        }
        if (opts && opts.term_student_type) params.term_student_type = opts.term_student_type;
        if (opts && opts.search) params.search = opts.search;
        if (opts && opts.limit) params.limit = opts.limit;
        return $http.get(BASE + '/school-years', { params: params }).then(_unwrap);
      },
      get: function (id) {
        return $http.get(BASE + '/school-years/' + encodeURIComponent(id)).then(_unwrap);
      },
      create: function (payload) {
        return $http.post(BASE + '/school-years', payload, _adminHeaders()).then(_unwrap);
      },
      update: function (id, payload) {
        return $http.put(BASE + '/school-years/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      remove: function (id) {
        return $http.delete(BASE + '/school-years/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      }
    };
  }
})();
