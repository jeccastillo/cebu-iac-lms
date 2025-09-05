(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('StorageService', StorageService);

  function StorageService() {
    function get(key) {
      try { return localStorage.getItem(key); } catch (e) { return null; }
    }
    function set(key, value) {
      try { localStorage.setItem(key, value); } catch (e) {}
    }
    function remove(key) {
      try { localStorage.removeItem(key); } catch (e) {}
    }
    function getJSON(key) {
      try {
        var v = localStorage.getItem(key);
        return v ? JSON.parse(v) : null;
      } catch (e) { return null; }
    }
    function setJSON(key, obj) {
      try { localStorage.setItem(key, JSON.stringify(obj)); } catch (e) {}
    }

    return {
      get: get,
      set: set,
      remove: remove,
      getJSON: getJSON,
      setJSON: setJSON
    };
  }

})();
