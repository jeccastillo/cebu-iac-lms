(function () {
  'use strict';

  angular
    .module('unityApp')
    .constant('APP_CONFIG', {
      // Values read from window.* set in index.html bootstrap
      get API_BASE() {
        var v = (window.API_BASE || '/laravel-api/public/api/v1') + '';
        return v.replace(/\/+$/, '');
      },
      get IMG_BASE() {
        var v = (window.location.origin + '/iacademy/cebu-iac-lms/frontend/images/') + '';
        return v.replace(/\/+$/, '');
      },
      get AFTER_LOGIN_REDIRECTS() {
        return window.AFTER_LOGIN_REDIRECTS || { faculty: '/unity', student: '/portal' };
      },
      get LOGIN_APP_CONFIG() {
        return window.LOGIN_APP_CONFIG || { useRedirects: false };
      }
    });

})();
