(function () {
  'use strict';

  // Requires jQuery and Select2 to be loaded globally
  angular
    .module('unityApp')
    .directive('select2', select2Directive);

  select2Directive.$inject = ['$timeout'];
  function select2Directive($timeout) {
    return {
      restrict: 'A',
      require: 'ngModel',
      scope: {
        // Optional: pass a watched collection/expr to re-init select2 when options change
        select2Watch: '=?'
      },
      link: function (scope, element, attrs, ngModel) {
        function init() {
          $timeout(function () {
            try {
              // Destroy previous instance if any
              if (window.jQuery && jQuery(element).hasClass('select2-hidden-accessible')) {
                jQuery(element).select2('destroy');
              }

              var placeholder =
                attrs.placeholder ||
                attrs.dataPlaceholder ||
                (attrs.select2Placeholder || '');

              // Initialize
              jQuery(element).select2({
                width: '100%',
                placeholder: placeholder || '',
                allowClear: !!attrs.select2AllowClear
              });

              // Apply relevant tailwind layout classes (margin/width/display) from the hidden select to the visible container
              try {
                var $container = jQuery(element).next('.select2');
                var klass = (jQuery(element).attr('class') || '');
                var carry = [];
                klass.split(/\s+/).forEach(function (k) {
                  if (/^(mt-|mb-|ml-|mr|mx-|my-|w-)/.test(k) || k === 'block' || k === 'inline-block') {
                    carry.push(k);
                  }
                });
                if ($container && carry.length) {
                  $container.addClass(carry.join(' '));
                }
              } catch (e) {}

              // Sync select2 -> ngModel
              jQuery(element).off('change.select2').on('change.select2', function () {
                scope.$applyAsync(function () {
                  // For single select bound to primitive (string)
                  ngModel.$setViewValue(element.val());
                });
              });

              // Ensure the current model is reflected in the UI
              jQuery(element).val(ngModel.$modelValue).trigger('change.select2');
            } catch (e) {
              // swallow init errors to avoid breaking the page
            }
          });
        }

        // Reinitialize when the watched collection changes (e.g. options list updated)
        scope.$watch('select2Watch', function () {
          init();
        }, true);

        // Watch model changes and update the UI
        scope.$watch(function () { return ngModel.$modelValue; }, function () {
          $timeout(function () {
            try {
              jQuery(element).val(ngModel.$modelValue).trigger('change.select2');
            } catch (e) {}
          });
        });

        // React to disabled changes if bound
        attrs.$observe('disabled', function () {
          $timeout(function () {
            try {
              var disabled = attrs.disabled !== undefined && attrs.disabled !== false && attrs.disabled !== 'false' ? true : false;
              jQuery(element).prop('disabled', disabled);
            } catch (e) {}
          });
        });

        // Cleanup
        scope.$on('$destroy', function () {
          try {
            if (window.jQuery && jQuery(element).hasClass('select2-hidden-accessible')) {
              jQuery(element).select2('destroy');
            }
          } catch (e) {}
        });

        // Initial init
        init();
      }
    };
  }
})();
