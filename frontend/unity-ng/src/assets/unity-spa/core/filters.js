(function () {
  'use strict';

  // Global reusable filters for the Unity SPA
  angular
    .module('unityApp')
    .filter('ceil', function () {
      return function (input) {
        var n = Number(input || 0);
        return Math.ceil(n);
      };
    })
    .filter('capitalize', function () {
      return function (input) {
        if (!input) return '';
        input = input.toString();
        return input.charAt(0).toUpperCase() + input.slice(1).toLowerCase();
      };
    })
    .filter('trim', function () {
      return function (input) {
        if (input === undefined || input === null) return '';
        return ('' + input).trim();
      };
    })
    // Format SQL datetime strings like 'YYYY-MM-DD HH:mm:ss' to a desired display format
    // Usage: {{ value | formatSqlDateTime:'MMMM d, y h:mm a' }}
    .filter('formatSqlDateTime', ['$filter', function ($filter) {
      return function (input, fmt) {
        if (!input) return '';
        try {
          var s = String(input).trim();
          // Help Date parser by replacing space with 'T'
          var isoLike = s.replace(' ', 'T');
          var dt = new Date(isoLike);

          if (isNaN(dt.getTime())) {
            // Manual parse fallback for 'YYYY-MM-DD HH:mm[:ss]'
            var m = s.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
            if (m) {
              var Y = parseInt(m[1], 10);
              var M = parseInt(m[2], 10) - 1;
              var D = parseInt(m[3], 10);
              var h = parseInt(m[4], 10);
              var i = parseInt(m[5], 10);
              var sec = (m[6] !== undefined && m[6] !== null) ? parseInt(m[6], 10) : 0;
              dt = new Date(Y, M, D, h, i, sec);
            }
          }

          if (isNaN(dt.getTime())) return s; // leave unchanged if still invalid
          return $filter('date')(dt, fmt || 'MMMM d, y h:mm a');
        } catch (e) {
          return String(input);
        }
      };
    }]);

})();
