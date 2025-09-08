(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ScholarshipsService', ScholarshipsService);

  ScholarshipsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ScholarshipsService($http, APP_CONFIG, StorageService) {
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
      // GET /scholarships?status=&deduction_type=&deduction_from=&q=
      list: function (opts) {
        var params = {};
        if (opts) {
          if (opts.status) {
            params.status = ('' + opts.status).trim();
          }
          if (opts.deduction_type) {
            params.deduction_type = ('' + opts.deduction_type).trim();
          }
          if (opts.deduction_from) {
            params.deduction_from = ('' + opts.deduction_from).trim();
          }
          if (opts.q && ('' + opts.q).trim() !== '') {
            params.q = ('' + opts.q).trim();
          }
        }
        return $http.get(BASE + '/scholarships', { params: params }).then(_unwrap);
      },

      // GET /scholarships/{id}
      get: function (id) {
        return $http.get(BASE + '/scholarships/' + encodeURIComponent(id)).then(_unwrap);
      },

      // POST /scholarships
      create: function (payload) {
        return $http.post(BASE + '/scholarships', payload, _adminHeaders()).then(_unwrap);
      },

      // PUT /scholarships/{id}
      update: function (id, payload) {
        return $http.put(BASE + '/scholarships/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },

      // DELETE /scholarships/{id} (soft delete)
      destroy: function (id) {
        return $http.delete(BASE + '/scholarships/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // POST /scholarships/{id}/restore
      restore: function (id) {
        return $http.post(BASE + '/scholarships/' + encodeURIComponent(id) + '/restore', {}, _adminHeaders()).then(_unwrap);
      }
    };
  }
})();
