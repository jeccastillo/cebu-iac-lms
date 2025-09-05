(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('CampusesService', CampusesService);

  CampusesService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function CampusesService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    // Helpers to include admin header (X-Faculty-ID) for protected routes
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
      list: function (q) {
        var params = {};
        if (q && ('' + q).trim() !== '') params.q = q;
        return $http.get(BASE + '/campuses', { params: params })
          .then(_unwrap);
      },
      get: function (id) {
        return $http.get(BASE + '/campuses/' + encodeURIComponent(id))
          .then(_unwrap);
      },
      create: function (payload) {
        return $http.post(BASE + '/campuses', payload, _adminHeaders())
          .then(_unwrap);
      },
      update: function (id, payload) {
        return $http.put(BASE + '/campuses/' + encodeURIComponent(id), payload, _adminHeaders())
          .then(_unwrap);
      },
      remove: function (id) {
        return $http.delete(BASE + '/campuses/' + encodeURIComponent(id), _adminHeaders())
          .then(_unwrap);
      }
    };
  }

})();
