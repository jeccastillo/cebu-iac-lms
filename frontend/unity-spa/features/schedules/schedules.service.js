(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('SchedulesService', SchedulesService);

  SchedulesService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function SchedulesService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE;

    function _getLoginState() {
      return StorageService.getJSON('loginState');
    }

    function _adminHeaders(extra) {
      var state = _getLoginState();
      var headers = Object.assign({}, extra || {});
      if (state && state.faculty_id) {
        headers["X-Faculty-ID"] = state.faculty_id;
      } else {
        // Fallback for super admin
        headers["X-Faculty-ID"] = "smssuperadmin";
      }
      
      // Add campus_id header if available
      if (state && state.campus_id) {
        headers["X-Campus-ID"] = state.campus_id;
      }
      
      return { headers: headers };
    }

    function _unwrap(resp) {
      console.log('SchedulesService API response:', resp); // Debug logging
      return resp && resp.data ? resp.data : resp;
    }

    return {
      list: function (params) {
        console.log('SchedulesService.list called with params:', params); // Debug logging
        var queryParams = {};
        if (params) {
          if (params.search && ("" + params.search).trim() !== "") {
            queryParams.search = params.search;
          }
          if (params.intSem) {
            queryParams.intSem = params.intSem;
          }
          if (params.room_id) {
            queryParams.room_id = params.room_id;
          }
          if (params.day) {
            queryParams.day = params.day;
          }
          if (params.class_type) {
            queryParams.class_type = params.class_type;
          }
          if (params.classlist_id) {
            queryParams.classlist_id = params.classlist_id;
          }
        }
        
        console.log('Making request to:', BASE + "/schedules", 'with params:', queryParams); // Debug logging
        return $http
          .get(BASE + "/schedules", {
            params: queryParams,
            headers: _adminHeaders().headers,
          })
          .then(_unwrap)
          .catch(function(error) {
            console.error('SchedulesService.list error:', error);
            throw error;
          });
      },
      
      get: function (id) {
        return $http
          .get(BASE + "/schedules/" + encodeURIComponent(id), _adminHeaders())
          .then(_unwrap);
      },
      
      create: function (payload) {
        return $http
          .post(BASE + "/schedules", payload, _adminHeaders())
          .then(_unwrap);
      },
      
      update: function (id, payload) {
        return $http
          .put(
            BASE + "/schedules/" + encodeURIComponent(id),
            payload,
            _adminHeaders()
          )
          .then(_unwrap);
      },
      
      delete: function (id) {
        return $http
          .delete(
            BASE + "/schedules/" + encodeURIComponent(id),
            _adminHeaders()
          )
          .then(_unwrap);
      },

      summary: function (intSem) {
        var params = {};
        if (intSem) {
          params.intSem = intSem;
        }
        
        return $http
          .get(BASE + "/schedules/summary", {
            params: params,
            headers: _adminHeaders().headers,
          })
          .then(_unwrap);
      },

      getAcademicYears: function() {
        return $http
          .get(BASE + "/schedules/academic-years", _adminHeaders())
          .then(_unwrap);
      },

      getAvailableClasslists: function(intSem, blockSectionID) {
        var params = {};
        if (intSem) {
          params.intSem = intSem;
        }
        if (blockSectionID) {
          params.blockSectionID = blockSectionID;
        }
        
        return $http
          .get(BASE + "/schedules/available-classlists", {
            params: params,
            headers: _adminHeaders().headers,
          })
          .then(_unwrap);
      },

      getBlockSections: function(intSem) {
        var params = {};
        if (intSem) {
          params.intSem = intSem;
        }
        
        return $http
          .get(BASE + "/schedules/block-sections", {
            params: params,
            headers: _adminHeaders().headers,
          })
          .then(_unwrap);
      },

      getAllClasslists: function() {
        return $http
          .get(BASE + "/classlists", _adminHeaders())
          .then(_unwrap);
      }
    };
  }
})();
