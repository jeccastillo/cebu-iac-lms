(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('UnityService', UnityService);

  UnityService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function UnityService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

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
      // Advising placeholder
      advising: function (payload) {
        return $http.post(BASE + '/unity/advising', payload, _adminHeaders()).then(_unwrap);
      },
      // Enlist registrar operations (add/drop/change_section)
      enlist: function (payload) {
        // payload: { student_number, term, year_level, student_type?, operations: [...] }
        return $http.post(BASE + '/unity/enlist', payload, _adminHeaders()).then(_unwrap);
      },
      // Reset registration for a student/term
      resetRegistration: function (payload) {
        // payload: { student_number, term?, password? }
        return $http.post(BASE + '/unity/reset-registration', payload, _adminHeaders()).then(_unwrap);
      },
      // Registration: fetch existing row for student+term (no create)
      getRegistration: function (student_number, term) {
        var params = { student_number: student_number, term: term };
        return $http.get(BASE + '/unity/registration', Object.assign({ params: params }, _adminHeaders())).then(_unwrap);
      },
      // Registration: fetch by student_id (preferred when available)
      getRegistrationById: function (student_id, term) {
        var params = { student_id: student_id, term: term };
        return $http.get(BASE + '/unity/registration', Object.assign({ params: params }, _adminHeaders())).then(_unwrap);
      },
      // Registration: update editable fields for existing row
      updateRegistration: function (payload) {
        // payload: { student_number, term, fields: { ... } }
        return $http.put(BASE + '/unity/registration', payload, _adminHeaders()).then(_unwrap);
      },
      // Tag status placeholder
      tagStatus: function (payload) {
        return $http.post(BASE + '/unity/tag-status', payload, _adminHeaders()).then(_unwrap);
      },
      // Tuition preview wrapper
      tuitionPreview: function (payload) {
        return $http.post(BASE + '/unity/tuition-preview', payload, _adminHeaders()).then(_unwrap);
      },
      // Tuition save snapshot (server recomputes for integrity)
      tuitionSave: function (payload) {
        // payload: { student_number: string, term: int, discount_id?: int, scholarship_id?: int }
        return $http.post(BASE + '/unity/tuition-save', payload, _adminHeaders()).then(_unwrap);
      },
      // Fetch saved tuition snapshot existence/details
      tuitionSaved: function (params) {
        // params: { student_number: string, term: int }
        return $http.get(BASE + '/unity/tuition-saved', Object.assign({ params: params }, _adminHeaders())).then(_unwrap);
      },

      // Payment Details for selected term/registration
      paymentDetails: function (params) {
        // params: { student_number: string, term: int }
        return $http.get(BASE + '/finance/payment-details', Object.assign({ params: params }, _adminHeaders())).then(_unwrap);
      },

      // Invoices: list invoices (filter by registration_id, type, etc.)
      invoicesList: function (params) {
        return $http.get(BASE + '/finance/invoices', Object.assign({ params: params }, _adminHeaders())).then(_unwrap);
      },

      // Invoices: generate a new invoice
      invoicesGenerate: function (payload) {
        // payload: { type: 'tuition'|'billing'|'other', student_id: number, term: number, registration_id?: number, ... }
        return $http.post(BASE + '/finance/invoices/generate', payload, _adminHeaders()).then(_unwrap);
      }
    };
  }

})();
