(function () {
  'use strict';

  angular
    .module('unityApp')
    .service('TermService', TermService);

  TermService.$inject = ['$http', '$rootScope', '$q', 'APP_CONFIG', 'StorageService', 'CampusService'];
  function TermService($http, $rootScope, $q, APP_CONFIG, StorageService, CampusService) {
    var service = this;
    var STORAGE_KEY = 'selectedTerm';
    var TERMS_CACHE_KEY = 'termsCache';
    var CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

    // Coalescing/in-flight guards to prevent duplicate API calls
    service._initPromise = null;
    service._termsLoadPromise = null;
    service._activePromise = null;

    // Service state
    service.selectedTerm = null;
    service.availableTerms = [];
    service.loading = false;
    service.error = null;

    // Public API
    service.init = init;
    service.loadTerms = loadTerms;
    service.getActiveTerm = getActiveTerm;
    service.setSelectedTerm = setSelectedTerm;
    service.getSelectedTerm = getSelectedTerm;
    service.clearCache = clearCache;

    /**
     * Initialize the service - load terms and set default selection
     */
    function init() {
      // Reuse ongoing init cycle to avoid duplicate terms/active-term loads
      if (service._initPromise) {
        return service._initPromise;
      }

      // Ensure campus context is initialized first if available
      var campusPromise = (CampusService && CampusService.init) ? CampusService.init() : $q.resolve();

      // Register listener to re-scope terms when campus changes
      // Only register once per service lifecycle
      if (!service._campusListenerRegistered) {
        service._campusListenerRegistered = true;
        $rootScope.$on('campusChanged', function () {
          clearCache();
          service.selectedTerm = null;
          // Reload terms for the new campus and select active term
          loadTerms().then(function () {
            var savedTerm = StorageService.getJSON(STORAGE_KEY);
            var currentCid = getCurrentCampusId();
            var savedCid = savedTerm && savedTerm._campusId ? ('' + savedTerm._campusId) : null;

            if (savedTerm && isValidTerm(savedTerm) && savedCid === currentCid) {
              service.selectedTerm = savedTerm;
              broadcastTermChange();
            } else {
              getActiveTerm().then(function (activeTerm) {
                if (activeTerm) {
                  setSelectedTerm(activeTerm);
                } else {
                  broadcastTermChange();
                }
              });
            }
          });
        });
      }

      service._initPromise = campusPromise.then(function () {
        return loadTerms()
          .then(function() {
            // Try to restore previously selected term for current campus
            var savedTerm = StorageService.getJSON(STORAGE_KEY);
            var currentCid = getCurrentCampusId();
            var savedCid = savedTerm && savedTerm._campusId ? ('' + savedTerm._campusId) : null;

            if (savedTerm && isValidTerm(savedTerm) && savedCid === currentCid) {
              service.selectedTerm = savedTerm;
              broadcastTermChange();
              return service.selectedTerm;
            }

            // If no saved term or invalid, get active term from API
            return getActiveTerm()
              .then(function(activeTerm) {
                if (activeTerm) {
                  setSelectedTerm(activeTerm);
                }
                return service.selectedTerm;
              });
          });
      })
      .catch(function(error) {
        service.error = 'Failed to initialize terms';
        console.error('TermService init error:', error);
        return null;
      });

      return service._initPromise;
    }

    /**
     * Load all available terms from API
     */
    function loadTerms() {
      // Check cache first
      var cached = getCachedTerms();
      if (cached) {
        service.availableTerms = cached;
        return $q.resolve(cached);
      }

      // Coalesce concurrent requests
      if (service._termsLoadPromise) {
        return service._termsLoadPromise;
      }

      service.loading = true;
      service.error = null;

      service._termsLoadPromise = $http.get(buildUrl(APP_CONFIG.API_BASE + '/generic/terms'))
        .then(function(response) {
          if (response && response.data && response.data.success) {
            service.availableTerms = response.data.data || [];
            cacheTerms(service.availableTerms);
          } else {
            service.availableTerms = [];
            service.error = 'Failed to load terms';
          }
          return service.availableTerms;
        })
        .catch(function(error) {
          service.availableTerms = [];
          service.error = 'Failed to load terms';
          console.error('TermService loadTerms error:', error);
          return [];
        })
        .finally(function() {
          service.loading = false;
          service._termsLoadPromise = null;
        });

      return service._termsLoadPromise;
    }

    /**
     * Get the active term from API
     */
    function getActiveTerm() {
      // Coalesce concurrent requests
      if (service._activePromise) {
        return service._activePromise;
      }
      service._activePromise = $http.get(buildUrl(APP_CONFIG.API_BASE + '/generic/active-term'))
        .then(function(response) {
          if (response && response.data && response.data.success) {
            return response.data.data;
          }
          return null;
        })
        .catch(function(error) {
          console.error('TermService getActiveTerm error:', error);
          return null;
        })
        .finally(function () {
          service._activePromise = null;
        });
      return service._activePromise;
    }

    /**
     * Set the selected term and persist to storage
     */
    function setSelectedTerm(term) {
      if (!term) return;

      service.selectedTerm = term;
      var toSave = angular.copy(term);
      toSave._campusId = getCurrentCampusId();
      StorageService.setJSON(STORAGE_KEY, toSave);
      broadcastTermChange();
    }

    /**
     * Get the currently selected term
     */
    function getSelectedTerm() {
      return service.selectedTerm;
    }

    /**
     * Clear cached terms (force reload on next request)
     */
    function clearCache() {
      StorageService.remove(TERMS_CACHE_KEY);
    }

    // Private helper functions

    function getCurrentCampusId() {
      try {
        var campus = CampusService && CampusService.getSelectedCampus ? CampusService.getSelectedCampus() : null;
        var id = campus && campus.id !== undefined && campus.id !== null ? ('' + campus.id) : null;
        return id;
      } catch (e) {
        return null;
      }
    }

    function buildUrl(base) {
      var cid = getCurrentCampusId();
      if (cid) {
        return base + (base.indexOf('?') === -1 ? '?' : '&') + 'campus_id=' + encodeURIComponent(cid);
      }
      return base;
    }

    /**
     * Check if a term object is valid
     */
    function isValidTerm(term) {
      return term && 
             term.intID && 
             term.label &&
             service.availableTerms.some(function(t) { 
               return t.intID === term.intID; 
             });
    }

    /**
     * Get cached terms if still valid
     */
    function getCachedTerms() {
      try {
        var cached = StorageService.getJSON(TERMS_CACHE_KEY);
        if (cached && cached.timestamp && cached.data) {
          var age = Date.now() - cached.timestamp;
          var cid = getCurrentCampusId();
          if (age < CACHE_DURATION && ('' + (cached.campusId || '')) === ('' + (cid || ''))) {
            return cached.data;
          }
        }
      } catch (e) {
        console.warn('Error reading terms cache:', e);
      }
      return null;
    }

    /**
     * Cache terms with timestamp
     */
    function cacheTerms(terms) {
      try {
        StorageService.setJSON(TERMS_CACHE_KEY, {
          timestamp: Date.now(),
          campusId: getCurrentCampusId(),
          data: terms
        });
      } catch (e) {
        console.warn('Error caching terms:', e);
      }
    }

    /**
     * Broadcast term change event to other components
     */
    function broadcastTermChange() {
      $rootScope.$broadcast('termChanged', {
        selectedTerm: service.selectedTerm,
        availableTerms: service.availableTerms
      });
    }
  }

})();
