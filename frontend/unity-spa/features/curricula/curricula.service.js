(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('CurriculaService', CurriculaService);

  CurriculaService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function CurriculaService($http, APP_CONFIG, StorageService) {
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
      // CRUD for Curriculum
      list: function (opts) {
        var params = {};
        if (opts) {
          if (opts.search) params.search = opts.search;
          if (opts.campus_id !== undefined && opts.campus_id !== null && ('' + opts.campus_id) !== '') {
            params.campus_id = opts.campus_id;
          }
          if (opts.program_id) params.program_id = opts.program_id;
          if (opts.limit) params.limit = opts.limit;
          if (opts.page) params.page = opts.page;
        }
        return $http.get(BASE + '/curriculum', { params: params }).then(_unwrap);
      },
      get: function (id) {
        return $http.get(BASE + '/curriculum/' + encodeURIComponent(id)).then(_unwrap);
      },
      create: function (payload) {
        return $http.post(BASE + '/curriculum', payload, _adminHeaders()).then(_unwrap);
      },
      update: function (id, payload) {
        return $http.put(BASE + '/curriculum/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      remove: function (id) {
        return $http.delete(BASE + '/curriculum/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // Helpers for dropdowns
      getPrograms: function () {
        // include disabled as well to show full list in admin
        return $http.get(BASE + '/programs', { params: { enabledOnly: false } }).then(_unwrap);
      },
      getCampuses: function () {
        return $http.get(BASE + '/campuses').then(_unwrap);
      }
    };
  }

})();
