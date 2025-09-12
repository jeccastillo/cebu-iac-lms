(function () {
  'use strict';

  angular
    .module('unityApp')
    .service('ClinicService', ClinicService);

  ClinicService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function ClinicService($http, APP_CONFIG, StorageService) {
    var api = APP_CONFIG.API_BASE; // e.g. /laravel-api/public/api/v1  

    // Health Records
    this.searchRecords = function (params) {
      return $http.get(api + '/clinic/records', { params: params }).then(resp => resp.data);
    };
    this.createOrUpdateRecord = function (payload) {
      return $http.post(api + '/clinic/records', payload).then(resp => resp.data);
    };
    this.getRecord = function (id) {
      return $http.get(api + '/clinic/records/' + id).then(resp => resp.data);
    };
    this.updateRecord = function (id, payload) {
      return $http.put(api + '/clinic/records/' + id, payload).then(resp => resp.data);
    };

    // Visits
    this.listVisits = function (recordId, params) {
      var p = angular.extend({}, params || {}, { record_id: recordId });
      return $http.get(api + '/clinic/visits', { params: p }).then(resp => resp.data);
    };
    this.createVisit = function (payload) {
      return $http.post(api + '/clinic/visits', payload).then(resp => resp.data);
    };
    this.getVisit = function (id) {
      return $http.get(api + '/clinic/visits/' + id).then(resp => resp.data);
    };
    this.updateVisit = function (id, payload) {
      return $http.put(api + '/clinic/visits/' + id, payload).then(resp => resp.data);
    };

    // Attachments
    this.listAttachments = function (params) {
      return $http.get(api + '/clinic/attachments', { params: params }).then(resp => resp.data);
    };
    this.uploadAttachment = function (file, recordId, visitId, uploadedBy) {
      var fd = new FormData();
      fd.append('file', file);
      if (recordId) fd.append('record_id', recordId);
      if (visitId) fd.append('visit_id', visitId);
      if (uploadedBy) fd.append('uploaded_by', uploadedBy);
      return $http.post(api + '/clinic/attachments', fd, {
        headers: { 'Content-Type': undefined }
      }).then(resp => resp.data);
    };
    this.downloadAttachmentUrl = function (id) {
      return api + '/clinic/attachments/' + id + '/download';
    };
    this.deleteAttachment = function (id) {
      return $http.delete(api + '/clinic/attachments/' + id).then(resp => resp.data);
    };
  }
})();
