(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('PaymentDetailsImportService', PaymentDetailsImportService);

  PaymentDetailsImportService.$inject = ['$http', 'APP_CONFIG', 'StorageService'];
  function PaymentDetailsImportService($http, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1

    function _getLoginState() {
      try {
        return StorageService.getJSON('loginState') || null;
      } catch (e) {
        return null;
      }
    }

    function _headers(extra) {
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

    function downloadTemplate() {
      var cfg = _headers();
      cfg.responseType = 'arraybuffer';
      return $http.get(BASE + '/finance/payment-details/import/template', cfg).then(function (resp) {
        var data = resp && resp.data ? resp.data : null;
        if (!data) return;

        var blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'payment-details-import-template.xlsx';
        document.body.appendChild(a);
        a.click();
        setTimeout(function () {
          try { document.body.removeChild(a); } catch (e) {}
          window.URL.revokeObjectURL(url);
        }, 0);
      });
    }

    function upload(file) {
      var fd = new FormData();
      fd.append('file', file);

      var cfg = _headers({ 'Content-Type': undefined }); // let browser set boundary
      return $http.post(BASE + '/finance/payment-details/import', fd, cfg).then(_unwrap);
    }

    return {
      downloadTemplate: downloadTemplate,
      upload: upload
    };
  }

})();
