(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('DepartmentDeficienciesService', DepartmentDeficienciesService);

  DepartmentDeficienciesService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function DepartmentDeficienciesService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE + '/department-deficiencies';

    function _adminHeaders(extra) {
      var headers = Object.assign({}, extra || {});
      try {
        var state = StorageService.getJSON('loginState') || {};
        if (state && state.faculty_id != null) {
          headers['X-Faculty-ID'] = state.faculty_id;
        }
        if (Array.isArray(state.roles)) {
          headers['X-User-Roles'] = state.roles.join(',');
        }
        if (state && state.campus_id != null) {
          headers['X-Campus-ID'] = state.campus_id;
        }
      } catch (e) {}
      return { headers: headers };
    }

    function meta() {
      return $http.get(BASE + '/meta', _adminHeaders()).then(respOk, respErr);
    }

    function list(params) {
      var cfg = Object.assign({ params: params || {} }, _adminHeaders());
      return $http.get(BASE, cfg).then(respOk, respErr);
    }

    function create(body) {
      var cfg = _adminHeaders({ 'Content-Type': 'application/json' });
      return $http.post(BASE, body, cfg).then(respOk, respErr);
    }

    function update(id, body) {
      var cfg = _adminHeaders({ 'Content-Type': 'application/json' });
      return $http.put(BASE + '/' + encodeURIComponent(id), body, cfg).then(respOk, respErr);
    }

    function destroy(id) {
      return $http.delete(BASE + '/' + encodeURIComponent(id), _adminHeaders()).then(respOk, respErr);
    }

    function respOk(resp) {
      return (resp && resp.data) ? resp.data : { success: false, data: null };
    }

    function respErr(err) {
      var data = (err && err.data) ? err.data : null;
      var msg = (data && data.message) ? data.message : 'Request failed';
      return Promise.reject({ success: false, message: msg, error: err });
    }

    return {
      meta: meta,
      list: list,
      create: create,
      update: update,
      destroy: destroy
    };
  }
})();
