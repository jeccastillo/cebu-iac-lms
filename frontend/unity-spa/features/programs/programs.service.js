(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ProgramsService', ProgramsService);

  ProgramsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ProgramsService($http, APP_CONFIG, StorageService) {
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
        if (opts && typeof opts.enabledOnly !== 'undefined') params.enabledOnly = !!opts.enabledOnly;
        if (opts && opts.type) params.type = opts.type;
        if (opts && opts.school) params.school = opts.school;
        if (opts && opts.search) params.search = opts.search;
        return $http.get(BASE + '/programs', { params: params }).then(_unwrap);
      },
      get: function (id) {
        return $http.get(BASE + '/programs/' + encodeURIComponent(id)).then(_unwrap);
      },
      create: function (payload) {
        return $http.post(BASE + '/programs', payload, _adminHeaders()).then(_unwrap);
      },
      update: function (id, payload) {
        return $http.put(BASE + '/programs/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      disable: function (id) {
        return $http.delete(BASE + '/programs/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
