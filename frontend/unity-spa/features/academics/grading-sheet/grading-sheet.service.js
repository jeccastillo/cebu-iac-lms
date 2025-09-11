(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('GradingSheetService', GradingSheetService);

  GradingSheetService.$inject = ['$http', '$window', 'APP_CONFIG', 'StorageService'];
  function GradingSheetService($http, $window, APP_CONFIG, StorageService) {
    var BASE = APP_CONFIG.API_BASE; // e.g., /laravel-api/public/api/v1
    var IMG_BASE = APP_CONFIG.IMG_BASE; // e.g., /laravel-api/public/api/v1
    function _getLoginState() {
      try { return StorageService.getJSON('loginState') || null; } catch (e) { return null; }
    }

    function _adminHeaders(extra) {
      var state = _getLoginState();
      var headers = Object.assign({}, extra || {});
      if (state && state.faculty_id) {
        headers['X-Faculty-ID'] = state.faculty_id; // required by role middleware patterns elsewhere
      }
      return { headers: headers };
    }

    function exportPdf(params) {
      // params: { student_id, syid, period: 'midterm'|'final' }
      var cfg = _adminHeaders();
      cfg.responseType = 'arraybuffer';
      cfg.headers = cfg.headers || {};
      cfg.headers['Accept'] = 'application/pdf';
      cfg.params = {
        student_id: params.student_id,
        syid: params.syid,
        period: params.period        
      };
      console.log(cfg.params);
      return $http.get(BASE + '/reports/grading-sheet/pdf', cfg)
        .then(function (resp) {
          var blob = new Blob([resp.data], { type: 'application/pdf' });
          var url = URL.createObjectURL(blob);
          try {
            $window.open(url, '_blank');
          } finally {
            // Revoke after delay
            setTimeout(function () { URL.revokeObjectURL(url); }, 30000);
          }
          return true;
        });
    }

    return {
      exportPdf: exportPdf
    };
  }
})();
