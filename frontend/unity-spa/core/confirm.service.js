(function () {
  'use strict';

  angular
    .module('unityApp')
    .factory('ConfirmService', ConfirmService);

  ConfirmService.$inject = ['$q'];

  function ConfirmService($q) {
    /**
     * Show a confirmation dialog.
     * opts: {
     *   title?: string,
     *   text?: string,      // used when html not provided
     *   html?: string,      // rich content
     *   icon?: 'warning'|'info'|'error'|'success'|'question',
     *   confirmText?: string,
     *   cancelText?: string,
     *   showCancel?: boolean
     * }
     *
     * Returns: Promise<boolean> -> true if confirmed, false otherwise
     */
    function confirm(opts) {
      var o = opts || {};
      var title = o.title || 'Please Confirm';
      var text = o.text || '';
      var html = o.html || null;
      var confirmText = o.confirmText || 'Confirm';
      var cancelText = o.cancelText || 'Cancel';
      var icon = o.icon || 'warning';
      var showCancel = (o.showCancel !== false);

      // Prefer SweetAlert2 if available (already loaded via index.html)
      if (window.Swal && typeof window.Swal.fire === 'function') {
        return window.Swal.fire({
          title: title,
          text: html ? undefined : text,
          html: html || undefined,
          icon: icon,
          showCancelButton: showCancel,
          confirmButtonText: confirmText,
          cancelButtonText: cancelText,
          reverseButtons: true,
          focusCancel: true,
        }).then(function (res) {
          return !!(res && res.isConfirmed);
        });
      }

      // Fallback to native confirm (plain text)
      var msg = text || (html ? stripTags(html) : '');
      try {
        var res = window.confirm(title + '\n\n' + msg);
        return $q.resolve(!!res);
      } catch (e) {
        return $q.resolve(false);
      }
    }

    function stripTags(s) {
      try { return String(s).replace(/<[^>]*>/g, ''); } catch (e) { return String(s || ''); }
    }

    return {
      confirm: confirm
    };
  }
})();
