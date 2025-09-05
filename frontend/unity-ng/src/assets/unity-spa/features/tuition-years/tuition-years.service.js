(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('TuitionYearsService', TuitionYearsService);

  TuitionYearsService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function TuitionYearsService($http, APP_CONFIG, StorageService) {
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
      // -----------------------------
      // Tuition Years CRUD
      // -----------------------------

      // GET /tuition-years (optionally with ?default=1 or ?defaultShs=1)
      list: function (opts) {
        var params = {};
        if (opts && opts.default === true) params.default = 1;
        if (opts && opts.defaultShs === true) params.defaultShs = 1;
        return $http.get(BASE + '/tuition-years', { params: params }).then(_unwrap);
      },

      // GET /tuition-years/{id}
      show: function (id) {
        return $http.get(BASE + '/tuition-years/' + encodeURIComponent(id)).then(_unwrap);
      },

      // POST /tuition-years/add
      // payload minimally: { year }, other numeric fields are defaulted server-side
      create: function (payload) {
        var body = Object.assign({}, payload || {});
        return $http.post(BASE + '/tuition-years/add', body, _adminHeaders()).then(_unwrap);
      },

      // POST /tuition-years/finalize
      // Body must include intID and any updatable fields (e.g., year, pricePerUnit*, installment*, freeElectiveCount, isDefault/isDefaultShs, final)
      update: function (id, fields) {
        var body = Object.assign({}, fields || {});
        if (id !== undefined && id !== null && id !== '') {
          body.intID = parseInt(id, 10);
        }
        return $http.post(BASE + '/tuition-years/finalize', body, _adminHeaders()).then(_unwrap);
      },

      // POST /tuition-years/delete
      remove: function (id) {
        return $http.post(BASE + '/tuition-years/delete', { id: id }, _adminHeaders()).then(_unwrap);
      },

      // POST /tuition-years/duplicate
      duplicate: function (id) {
        return $http.post(BASE + '/tuition-years/duplicate', { id: id }, _adminHeaders()).then(_unwrap);
      },

      // POST /tuition-years/{id}/set-default?scope=college|shs
      setDefault: function (id, scope) {
        var url = BASE + '/tuition-years/' + encodeURIComponent(id) + '/set-default';
        var cfg = _adminHeaders();
        cfg.params = { scope: (scope || '').toLowerCase() };
        return $http.post(url, {}, cfg).then(_unwrap);
      },

      // -----------------------------
      // Related entities listing
      // -----------------------------
      // GET /tuition-years/{id}/misc
      listMisc: function (tuitionYearId) {
        return $http.get(
          BASE + '/tuition-years/' + encodeURIComponent(tuitionYearId) + '/misc'
        ).then(_unwrap);
      },

      // GET /tuition-years/{id}/lab-fees
      listLabFees: function (tuitionYearId) {
        return $http.get(
          BASE + '/tuition-years/' + encodeURIComponent(tuitionYearId) + '/lab-fees'
        ).then(_unwrap);
      },

      // GET /tuition-years/{id}/tracks
      listTracks: function (tuitionYearId) {
        return $http.get(
          BASE + '/tuition-years/' + encodeURIComponent(tuitionYearId) + '/tracks'
        ).then(_unwrap);
      },

      // GET /tuition-years/{id}/programs
      listPrograms: function (tuitionYearId) {
        return $http.get(
          BASE + '/tuition-years/' + encodeURIComponent(tuitionYearId) + '/programs'
        ).then(_unwrap);
      },

      // GET /tuition-years/{id}/electives
      listElectives: function (tuitionYearId) {
        return $http.get(
          BASE + '/tuition-years/' + encodeURIComponent(tuitionYearId) + '/electives'
        ).then(_unwrap);
      },

      // -----------------------------
      // Related entities mutations
      // -----------------------------

      // POST /tuition-years/submit-extra
      // type in { misc, lab_fee, track, program, elective }
      // Body must include FK:
      // - misc/lab_fee: tuitionYearID
      // - track/program/elective: tuitionyear_id
      addExtra: function (type, payload) {
        var body = Object.assign({ type: type }, payload || {});
        return $http.post(BASE + '/tuition-years/submit-extra', body, _adminHeaders()).then(_unwrap);
      },

      // POST /tuition-years/edit-type
      // Note: use 'xtype' to avoid clashing with misc row's 'type' column (category)
      updateExtra: function (type, id, payload) {
        var body = Object.assign({ xtype: type, id: id }, payload || {});
        return $http.post(BASE + '/tuition-years/edit-type', body, _adminHeaders()).then(_unwrap);
      },

      // POST /tuition-years/delete-type
      // Body: { type, id }
      deleteExtra: function (type, id) {
        var body = { type: type, id: id };
        return $http.post(BASE + '/tuition-years/delete-type', body, _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
