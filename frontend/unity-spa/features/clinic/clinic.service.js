(function () {
  'use strict';

  angular
    .module('unityApp')
    .service('ClinicService', ClinicService);

  ClinicService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ClinicService($http, APP_CONFIG, StorageService) {
    var api = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1  

    // Helpers to include admin header (X-Faculty-ID) for protected routes
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

    // Health Records
    this.searchRecords = function (params) {
      var cfg = Object.assign({ params: params }, _adminHeaders());
      return $http.get(api + '/clinic/records', cfg).then(resp => resp.data);
    };
    this.createOrUpdateRecord = function (payload) {
      return $http.post(api + '/clinic/records', payload, _adminHeaders()).then(resp => resp.data);
    };
    this.getRecord = function (id) {
      return $http.get(api + '/clinic/records/' + id, _adminHeaders()).then(resp => resp.data);
    };
    this.updateRecord = function (id, payload) {
      return $http.put(api + '/clinic/records/' + id, payload, _adminHeaders()).then(resp => resp.data);
    };

    // Visits
    this.listVisits = function (recordId, params) {
      var p = angular.extend({}, params || {}, { record_id: recordId });
      var cfg = Object.assign({ params: p }, _adminHeaders());
      return $http.get(api + '/clinic/visits', cfg).then(resp => resp.data);
    };
    this.createVisit = function (payload) {
      return $http.post(api + '/clinic/visits', payload, _adminHeaders()).then(resp => resp.data);
    };
    this.getVisit = function (id) {
      return $http.get(api + '/clinic/visits/' + id, _adminHeaders()).then(resp => resp.data);
    };
    this.updateVisit = function (id, payload) {
      return $http.put(api + '/clinic/visits/' + id, payload, _adminHeaders()).then(resp => resp.data);
    };

    // Attachments
    this.listAttachments = function (params) {
      var cfg = Object.assign({ params: params }, _adminHeaders());
      return $http.get(api + '/clinic/attachments', cfg).then(resp => resp.data);
    };
    this.uploadAttachment = function (file, recordId, visitId, uploadedBy) {
      var fd = new FormData();
      fd.append('file', file);
      if (recordId) fd.append('record_id', recordId);
      if (visitId) fd.append('visit_id', visitId);
      if (uploadedBy) fd.append('uploaded_by', uploadedBy);
      var ah = _adminHeaders();
      var cfg = { headers: Object.assign({ 'Content-Type': undefined }, (ah && ah.headers) || {}) };
      return $http.post(api + '/clinic/attachments', fd, cfg).then(resp => resp.data);
    };
    this.downloadAttachmentUrl = function (id) {
      return api + '/clinic/attachments/' + id + '/download';
    };
    this.deleteAttachment = function (id) {
      return $http.delete(api + '/clinic/attachments/' + id, _adminHeaders()).then(resp => resp.data);
    };
  }
})();
