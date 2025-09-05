(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('RolesAdminService', RolesAdminService);

  RolesAdminService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function RolesAdminService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    var svc = {
      // Roles CRUD
      list: list,
      create: create,
      update: update,
      remove: remove,

      // Faculty role assignment
      facultyRoles: facultyRoles,
      assignFacultyRoles: assignFacultyRoles,
      removeFacultyRole: removeFacultyRole,

      // Utilities
      searchFaculty: searchFaculty
    };

    return svc;

    // --------------- Helpers ---------------

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
      // RequireRole middleware expects X-Faculty-ID on protected routes
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      // API returns { success, data, message? }
      return (resp && resp.data) ? resp.data : resp;
    }

    // --------------- Roles CRUD ---------------

    // list roles; includeInactive = true to include intActive = 0
    function list(includeInactive) {
      var params = {};
      if (includeInactive === true) {
        params.include_inactive = 1;
      }
      return $http.get(BASE + '/roles', Object.assign({ params: params }, _adminHeaders()))
        .then(_unwrap);
    }

    function create(payload) {
      // payload: { strCode, strName, strDescription?, intActive? }
      return $http.post(BASE + '/roles', payload, _adminHeaders())
        .then(_unwrap);
    }

    function update(id, payload) {
      return $http.put(BASE + '/roles/' + encodeURIComponent(id), payload, _adminHeaders())
        .then(_unwrap);
    }

    function remove(id) {
      return $http.delete(BASE + '/roles/' + encodeURIComponent(id), _adminHeaders())
        .then(_unwrap);
    }

    // --------------- Faculty Roles ---------------

    function facultyRoles(facultyId) {
      return $http.get(BASE + '/faculty/' + encodeURIComponent(facultyId) + '/roles', _adminHeaders())
        .then(_unwrap);
    }

    // payload: { role_ids?: number[], role_codes?: string[] }
    function assignFacultyRoles(facultyId, payload) {
      return $http.post(BASE + '/faculty/' + encodeURIComponent(facultyId) + '/roles', payload, _adminHeaders())
        .then(_unwrap);
    }

    function removeFacultyRole(facultyId, roleId) {
      return $http.delete(BASE + '/faculty/' + encodeURIComponent(facultyId) + '/roles/' + encodeURIComponent(roleId), _adminHeaders())
        .then(_unwrap);
    }

    // --------------- Utilities ---------------

    // query by name; uses open endpoint /generic/faculty
    function searchFaculty(q) {
      var params = {};
      if (q && ('' + q).trim() !== '') {
        params.q = q;
      }
      return $http.get(BASE + '/generic/faculty', { params: params })
        .then(_unwrap);
    }
  }

})();
