(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ToastService', ToastService);

  // No DI needed; uses window.Swal if available, falls back to console.
  ToastService.$inject = [];
  function ToastService() {
    function fire(icon, title) {
      // Prefer SweetAlert2 toast if available
      if (window.Swal && Swal.mixin) {
        try {
          var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
          });
          Toast.fire({ icon: icon, title: title || '' });
          return;
        } catch (e) {
          // fallback to modal style if toast mixin fails
          try {
            Swal.fire({ icon: icon, title: title || '' });
            return;
          } catch (e2) {
            // fall through to console
          }
        }
      }

      // Fallback: log to console
      var msg = (title || '').toString();
      if (icon === 'error') {
        console && console.error && console.error(msg || 'Error');
      } else if (icon === 'warning') {
        console && console.warn && console.warn(msg || 'Warning');
      } else {
        console && console.log && console.log(msg || 'Info');
      }
    }

    return {
      success: function (msg) { fire('success', msg || 'Success'); },
      error: function (msg) { fire('error', msg || 'Error'); },
      warn: function (msg) { fire('warning', msg || 'Warning'); },
      info: function (msg) { fire('info', msg || 'Info'); }
    };
  }

})();
