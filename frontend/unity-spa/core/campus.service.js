(function () {
  'use strict';

  angular
    .module('unityApp')
    .service('CampusService', CampusService);

  CampusService.$inject = ['$http', '$rootScope', '$q', 'APP_CONFIG', 'StorageService'];
  function CampusService($http, $rootScope, $q, APP_CONFIG, StorageService) {
    var service = this;

    var STORAGE_KEY = 'selectedCampus';
    var CACHE_KEY = 'campusesCache';
    var CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

    // Service state
    service.selectedCampus = null;
    service.availableCampuses = [];
    service.loading = false;
    service.error = null;

    // Public API
    service.init = init;
    service.loadCampuses = loadCampuses;
    service.setSelectedCampus = setSelectedCampus;
    service.getSelectedCampus = getSelectedCampus;
    service.clearCache = clearCache;

    /**
     * Initialize the service - load campuses and set default selection
     */
    function init() {
      return loadCampuses()
        .then(function () {
          // Try to restore previously selected campus
          var saved = StorageService.getJSON(STORAGE_KEY);
          if (saved && isValidCampus(saved)) {
            // Use instance from current list if available to keep references consistent
            service.selectedCampus = findById(saved.id) || saved;
            broadcastCampusChange();
            return service.selectedCampus;
          }

          // Fallback default: first active campus, else first in list
          var firstActive = (service.availableCampuses || []).find(function (c) {
            return (c.status || '').toLowerCase() === 'active';
          });
          if (firstActive) {
            setSelectedCampus(firstActive);
          } else if (service.availableCampuses && service.availableCampuses.length) {
            setSelectedCampus(service.availableCampuses[0]);
          }

          return service.selectedCampus;
        })
        .catch(function (error) {
          service.error = 'Failed to initialize campuses';
          console.error('CampusService init error:', error);
          return null;
        });
    }

    /**
     * Load all available campuses from API
     */
    function loadCampuses() {
      // Check cache first
      var cached = getCachedCampuses();
      if (cached) {
        service.availableCampuses = cached;
        return $q.resolve(cached);
      }

      service.loading = true;
      service.error = null;

      return $http.get(APP_CONFIG.API_BASE + '/campuses')
        .then(function (response) {
          var payload = response && response.data ? response.data : response;
          var rows = [];

          if (payload) {
            if (payload.success !== false && angular.isArray(payload.data)) {
              rows = payload.data;
            } else if (angular.isArray(payload)) {
              rows = payload;
            } else if (payload && angular.isArray(payload.rows)) {
              rows = payload.rows;
            }
          }

          service.availableCampuses = rows || [];
          cacheCampuses(service.availableCampuses);
          return service.availableCampuses;
        })
        .catch(function (error) {
          service.availableCampuses = [];
          service.error = 'Failed to load campuses';
          console.error('CampusService loadCampuses error:', error);
          return [];
        })
        .finally(function () {
          service.loading = false;
        });
    }

    /**
     * Set the selected campus and persist to storage
     */
    function setSelectedCampus(campus) {
      if (!campus) return;

      service.selectedCampus = campus;
      StorageService.setJSON(STORAGE_KEY, campus);
      broadcastCampusChange();
    }

    /**
     * Get the currently selected campus
     */
    function getSelectedCampus() {
      return service.selectedCampus;
    }

    /**
     * Clear cached campuses (force reload on next request)
     */
    function clearCache() {
      StorageService.remove(CACHE_KEY);
    }

    // Private helper functions

    /**
     * Check if a campus object is valid
     */
    function isValidCampus(campus) {
      return campus &&
             campus.id !== undefined &&
             campus.id !== null &&
             campus.campus_name &&
             service.availableCampuses.some(function (c) {
               return ('' + c.id) === ('' + campus.id);
             });
    }

    /**
     * Get cached campuses if still valid
     */
    function getCachedCampuses() {
      try {
        var cached = StorageService.getJSON(CACHE_KEY);
        if (cached && cached.timestamp && cached.data) {
          var age = Date.now() - cached.timestamp;
          if (age < CACHE_DURATION) {
            return cached.data;
          }
        }
      } catch (e) {
        console.warn('Error reading campuses cache:', e);
      }
      return null;
    }

    /**
     * Cache campuses with timestamp
     */
    function cacheCampuses(campuses) {
      try {
        StorageService.setJSON(CACHE_KEY, {
          timestamp: Date.now(),
          data: campuses
        });
      } catch (e) {
        console.warn('Error caching campuses:', e);
      }
    }

    /**
     * Broadcast campus change event to other components
     */
    function broadcastCampusChange() {
      $rootScope.$broadcast('campusChanged', {
        selectedCampus: service.selectedCampus,
        availableCampuses: service.availableCampuses
      });
    }

    /**
     * Find campus by id from available list
     */
    function findById(id) {
      var sid = '' + id;
      for (var i = 0; i < service.availableCampuses.length; i++) {
        if (('' + service.availableCampuses[i].id) === sid) {
          return service.availableCampuses[i];
        }
      }
      return null;
    }
  }

})();
