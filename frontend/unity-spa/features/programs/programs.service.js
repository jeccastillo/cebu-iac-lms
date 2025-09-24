(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ProgramsService', ProgramsService);

  ProgramsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ProgramsService($http, APP_CONFIG, StorageService) {
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
      list: function (opts) {
        var params = {};
        if (opts && typeof opts.enabledOnly !== 'undefined') params.enabledOnly = !!opts.enabledOnly;
        if (opts && opts.type) params.type = opts.type;
        if (opts && opts.school) params.school = opts.school;
        if (opts && opts.search) params.search = opts.search;
        return $http.get(BASE + '/programs', { params: params }).then(_unwrap);
      },
      get: function (id) {
        return $http.get(BASE + '/programs/' + encodeURIComponent(id)).then(_unwrap);
      },
      create: function (payload) {
        return $http.post(BASE + '/programs', payload, _adminHeaders()).then(_unwrap);
      },
      update: function (id, payload) {
        return $http.put(BASE + '/programs/' + encodeURIComponent(id), payload, _adminHeaders()).then(_unwrap);
      },
      disable: function (id) {
        return $http.delete(BASE + '/programs/' + encodeURIComponent(id), _adminHeaders()).then(_unwrap);
      },

      // Admin: Programs Import — download template (.xlsx)
      downloadImportTemplate: function () {
        var cfg = { responseType: 'arraybuffer', headers: {} };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            cfg.headers['X-Faculty-ID'] = state.faculty_id;
          }
        } catch (e) {}
        return $http.get(BASE + '/programs/import/template', cfg).then(function (resp) {
          var headers = resp && resp.headers ? resp.headers : null;
          var filename = 'programs-import-template.xlsx';
          try {
            if (headers && typeof headers === 'function') {
              var cd = headers('Content-Disposition') || headers('content-disposition');
              if (cd && /filename="?([^"]+)"?/i.test(cd)) {
                filename = cd.match(/filename="?([^"]+)"?/i)[1];
              }
            }
          } catch (e) {}
          return { data: resp.data, filename: filename };
        });
      },

      // Admin: Programs Import — upload file (.xlsx/.xls/.csv)
      importFile: function (file, opts) {
        if (!file) {
          return Promise.reject({ message: 'No file selected' });
        }
        var fd = new FormData();
        fd.append('file', file);
        if (opts && typeof opts.dry_run !== 'undefined') {
          fd.append('dry_run', opts.dry_run ? '1' : '0');
        }
        if (opts && opts.campus_id != null) {
          try {
            fd.append('campus_id', String(parseInt(opts.campus_id, 10)));
          } catch (e) {
            fd.append('campus_id', String(opts.campus_id));
          }
        }
        var headers = { 'Content-Type': undefined };
        try {
          var state = _getLoginState();
          if (state && state.faculty_id != null) {
            headers['X-Faculty-ID'] = state.faculty_id;
          }
        } catch (e) {}
        return $http.post(BASE + '/programs/import', fd, {
          headers: headers,
          transformRequest: angular.identity
        }).then(_unwrap);
      },

      getCurricula: function (opts) {
        var params = {};
        if (opts && opts.campus_id !== null && opts.campus_id !== undefined && opts.campus_id !== '') {
          params.campus_id = opts.campus_id;
        }
        if (opts && opts.program_id) params.program_id = opts.program_id;
        if (opts && opts.search) params.search = opts.search;
        if (opts && opts.limit) params.limit = opts.limit;
        return $http.get(BASE + '/curriculum', { params: params }).then(_unwrap);
      }
    };
  }

})();
