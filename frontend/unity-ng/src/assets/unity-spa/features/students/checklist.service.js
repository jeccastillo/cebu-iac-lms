(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ChecklistService', ChecklistService);

  ChecklistService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ChecklistService($http, APP_CONFIG, StorageService) {
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
      get: function (studentId, opts) {
        var params = {};
        if (opts && (opts.year !== undefined && opts.year !== null && opts.year !== '')) params.year = opts.year;
        if (opts && (opts.sem !== undefined && opts.sem !== null && ('' + opts.sem).trim() !== '')) params.sem = opts.sem;
        var cfg = Object.assign({ params: params }, _adminHeaders());
        return $http.get(BASE + '/students/' + encodeURIComponent(studentId) + '/checklist', cfg).then(_unwrap);
      },
      generate: function (studentId, payload) {
        // payload: { intCurriculumID?, intYearLevel, strSem, remarks? }
        return $http.post(BASE + '/students/' + encodeURIComponent(studentId) + '/checklist/generate', payload, _adminHeaders()).then(_unwrap);
      },
      addItem: function (studentId, payload) {
        // payload: { intChecklistID, intSubjectID, strStatus?, dteCompleted?, isRequired? }
        return $http.post(BASE + '/students/' + encodeURIComponent(studentId) + '/checklist/items', payload, _adminHeaders()).then(_unwrap);
      },
      updateItem: function (studentId, itemId, payload) {
        return $http.put(BASE + '/students/' + encodeURIComponent(studentId) + '/checklist/items/' + encodeURIComponent(itemId), payload, _adminHeaders()).then(_unwrap);
      },
      deleteItem: function (studentId, itemId) {
        return $http.delete(BASE + '/students/' + encodeURIComponent(studentId) + '/checklist/items/' + encodeURIComponent(itemId), _adminHeaders()).then(_unwrap);
      },
      summary: function (studentId, opts) {
        var params = {};
        if (opts && (opts.year !== undefined && opts.year !== null && opts.year !== '')) params.year = opts.year;
        if (opts && (opts.sem !== undefined && opts.sem !== null && ('' + opts.sem).trim() !== '')) params.sem = opts.sem;
        var cfg = Object.assign({ params: params }, _adminHeaders());
        return $http.get(BASE + '/students/' + encodeURIComponent(studentId) + '/checklist/summary', cfg).then(_unwrap);
      }
    };
  }

})();
