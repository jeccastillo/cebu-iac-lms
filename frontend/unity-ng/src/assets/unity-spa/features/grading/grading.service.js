(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('GradingService', GradingService);

  GradingService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function GradingService($http, APP_CONFIG, StorageService) {
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
      // Grading Systems
      list: function () {
        return $http.get(BASE + '/grading-systems').then(_unwrap);
      },
      get: function (id) {
        return $http.get(BASE + '/grading-systems/' + encodeURIComponent(id)).then(_unwrap);
      },
      create: function (payload) {
        return $http.post(BASE + '/grading-systems', payload, _adminHeaders()).then(_unwrap);
      },
      update: function (id, payload) {
        return $http.put(BASE + '/grading-systems/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      remove: function (id) {
        return $http.delete(BASE + '/grading-systems/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // Grading Items
      addItems: function (id, items) {
        var payload = { items: items || [] };
        return $http.post(BASE + '/grading-systems/' + encodeURIComponent(id) + '/items/bulk', payload, _adminHeaders()).then(_unwrap);
      },
      addItem: function (id, item) {
        return $http.post(BASE + '/grading-systems/' + encodeURIComponent(id) + '/items', item, _adminHeaders()).then(_unwrap);
      },
      deleteItem: function (itemId) {
        return $http.delete(BASE + '/grading-systems/items/' + encodeURIComponent(itemId), _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
