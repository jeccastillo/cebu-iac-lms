(function () {
  "use strict";

  angular.module("unityApp").factory("ClassroomsService", ClassroomsService);

  ClassroomsService.$inject = ["$http", "APP_CONFIG", "StorageService"];
  function ClassroomsService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1

    // Helpers to include admin header (X-Faculty-ID) for protected routes
    function _getLoginState() {
      try {
        return StorageService.getJSON("loginState") || null;
      } catch (e) {
        return null;
      }
    }

    function _adminHeaders(extra) {
      var state = _getLoginState();
      var headers = Object.assign({}, extra || {});
      if (state && state.faculty_id) {
        headers["X-Faculty-ID"] = state.faculty_id;
      }
      return { headers: headers };
    }

    function _unwrap(resp) {
      return resp && resp.data ? resp.data : resp;
    }

    return {
      list: function (qOrOpts) {
        var params = {};
        if (angular.isObject(qOrOpts)) {
          if (qOrOpts.search && ("" + qOrOpts.search).trim() !== "") {
            params.search = qOrOpts.search;
          }
          if (qOrOpts.campus_id !== null && qOrOpts.campus_id !== undefined && ("" + qOrOpts.campus_id).trim() !== "") {
            params.campus_id = parseInt(qOrOpts.campus_id, 10);
          }
        } else if (qOrOpts && ("" + qOrOpts).trim() !== "") {
          params.search = qOrOpts;
        }
        return $http
          .get(BASE + "/classroom", {
            params: params,
            headers: _adminHeaders().headers,
          })
          .then(_unwrap);
      },
      get: function (id) {
        return $http
          .get(BASE + "/classroom/" + encodeURIComponent(id), _adminHeaders())
          .then(_unwrap);
      },
      create: function (payload) {
        return $http
          .post(BASE + "/classroom", payload, _adminHeaders())
          .then(_unwrap);
      },
      update: function (id, payload) {
        return $http
          .put(
            BASE + "/classroom/" + encodeURIComponent(id),
            payload,
            _adminHeaders()
          )
          .then(_unwrap);
      },
      delete: function (id) {
        return $http
          .delete(
            BASE + "/classroom/" + encodeURIComponent(id),
            _adminHeaders()
          )
          .then(_unwrap);
      },

      // Download classrooms import template (.xlsx)
      downloadImportTemplate: function () {
        var opts = _adminHeaders();
        return $http.get(BASE + "/classrooms/import/template", {
          headers: opts.headers,
          responseType: "arraybuffer"
        }).then(function (resp) { return resp.data; });
      },

      // Upload classrooms import file (.xlsx/.xls/.csv)
      importFile: function (file, dryRun) {
        var fd = new FormData();
        fd.append("file", file);
        if (typeof dryRun !== "undefined") {
          fd.append("dry_run", dryRun ? "1" : "0");
        }
        var hdrs = Object.assign({}, _adminHeaders().headers, {
          "Content-Type": undefined // let browser set boundary
        });
        return $http.post(BASE + "/classrooms/import", fd, {
          headers: hdrs,
          transformRequest: angular.identity
        }).then(_unwrap);
      },
    };
  }
})();
