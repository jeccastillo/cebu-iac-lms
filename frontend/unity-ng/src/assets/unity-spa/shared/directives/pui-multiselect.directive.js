(function () {
  'use strict';

  // pui-multiselect
  // Lightweight wrapper to provide a "PrimeUI-like" multi-select experience using Select2 under the hood.
  //
  // Dependencies:
  // - jQuery (window.jQuery)
  // - Select2 (jQuery.fn.select2)
  //
  // Usage:
  //   <select pui-multiselect
  //           ng-model="vm.filters.syidsA"
  //           ng-options="(t.intID || t.id) as vm.termLabel(t) for t in vm.terms track by (t.intID || t.id)"
  //           placeholder="Select terms..."
  //           select2-allow-clear>
  //   </select>
  //
  // Notes:
  // - ng-model should be an Array (multi-select).
  // - Works seamlessly with Angular's ngOptions. It respects Angular's option value encoding (e.g. "number:123").
  // - If you need to reinitialize when the options list changes, simply update the bound collection; this directive
  //   re-inits on any DOM change of options or on scope digest changes to ngOptions source (simple heuristic).
  angular
    .module('unityApp')
    .directive('puiMultiselect', puiMultiselect);

  puiMultiselect.$inject = ['$timeout', '$parse'];
  function puiMultiselect($timeout, $parse) {
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function (scope, element, attrs, ngModel) {
        // Ensure multiple attribute is set
        try { element.attr('multiple', 'multiple'); } catch (e) {}

        var hasNgOptions = typeof attrs.ngOptions !== 'undefined';

        // Map Angular/DOM option values to numeric IDs and back to DOM tokens
        function _toNumFromOptionValue(val) {
          try {
            if (val === null || val === undefined) return null;
            var s = String(val);
            if (s.indexOf('number:') === 0) {
              var n = parseInt(s.split(':')[1], 10);
              return isNaN(n) ? null : n;
            }
            if (/^\d+$/.test(s)) {
              var n2 = parseInt(s, 10);
              return isNaN(n2) ? null : n2;
            }
            // Not a recognizable numeric token
            return null;
          } catch (e) { return null; }
        }
        function _domTokensForModel(viewVal) {
          try {
            var arr = Array.isArray(viewVal) ? viewVal : (viewVal != null ? [viewVal] : []);
            var tokens = [];
            var $opts = (window.jQuery ? jQuery(element).find('option') : []);
            arr.forEach(function (v) {
              // Accept numeric model value or token-like string
              var vn = _toNumFromOptionValue(v);
              if (vn === null) {
                var t = parseInt(v, 10);
                vn = isNaN(t) ? null : t;
              }
              if (vn === null) return;
              $opts.each(function () {
                var ov = jQuery(this).attr('value');
                var on = _toNumFromOptionValue(ov);
                if (on !== null && on === vn) tokens.push(ov);
              });
            });
            return tokens;
          } catch (e) { return []; }
        }

        function init() {
          $timeout(function () {
            try {
              if (!(window.jQuery && jQuery.fn && jQuery.fn.select2)) {
                // Fallback: no select2 available, do nothing (native multi-select will work)
                return;
              }

              // Destroy previous
              if (jQuery(element).hasClass('select2-hidden-accessible')) {
                jQuery(element).select2('destroy');
              }

              var placeholder =
                attrs.placeholder ||
                attrs.dataPlaceholder ||
                (attrs.select2Placeholder || '');

              var allowClear = (typeof attrs.select2AllowClear !== 'undefined');

              var select2Opts = {
                width: '100%',
                placeholder: placeholder || '',
                allowClear: allowClear
              };

              // Ensure the element is truly multi-select before initializing Select2
              try {
                jQuery(element).attr('multiple', 'multiple').prop('multiple', true);
              } catch (e) {}

              jQuery(element).select2(select2Opts);

              // Apply some Tailwind layout classes from original select to visible container (width/margins/display)
              try {
                var $container = jQuery(element).next('.select2');
                var klass = (jQuery(element).attr('class') || '');
                var carry = [];
                klass.split(/\s+/).forEach(function (k) {
                  if (/^(mt-|mb-|ml-|mr-|mx-|my-|w-)/.test(k) || k === 'block' || k === 'inline-block') {
                    carry.push(k);
                  }
                });
                if ($container && carry.length) {
                  $container.addClass(carry.join(' '));
                }
              } catch (e) {}
             
              // Sync select2 -> ngModel
              jQuery(element).off('change.puims').on('change.puims', function () {
                scope.$applyAsync(function () {
                  var vals = jQuery(element).val() || [];
                  ngModel.$setViewValue(vals);
                });
              });

              // Reflect current model in UI using DOM tokens mapped from model values
              jQuery(element).val(_domTokensForModel(ngModel.$viewValue)).trigger('change.select2');
            } catch (e) {
              // swallow init errors
            }
          });
        }

        // Watch model and update UI
        scope.$watch(function () { return ngModel.$viewValue; }, function () {
          $timeout(function () {
            try {
              if (!(window.jQuery && jQuery.fn && jQuery.fn.select2)) return;
              var desiredTokens = _domTokensForModel(ngModel.$viewValue);
              var current = jQuery(element).val() || [];
              // Compare shallow arrays of DOM tokens
              var eq = Array.isArray(desiredTokens) && Array.isArray(current) &&
                       desiredTokens.length === current.length &&
                       desiredTokens.every(function (v, i) { return String(v) === String(current[i]); });
              if (!eq) {
                jQuery(element).val(desiredTokens).trigger('change.select2');
              }
            } catch (e) {}
          });
        });

        // Observe disabled
        attrs.$observe('disabled', function () {
          $timeout(function () {
            try {
              var disabled = attrs.disabled !== undefined && attrs.disabled !== false && attrs.disabled !== 'false';
              jQuery(element).prop('disabled', !!disabled);
            } catch (e) {}
          });
        });

        // Heuristic: re-init when options change (MutationObserver)
        var mo;
        try {
          mo = new MutationObserver(function () {
            init();
          });
          mo.observe(element[0], { childList: true, subtree: false });
        } catch (e) {}

        // Cleanup
        scope.$on('$destroy', function () {
          try { if (mo) mo.disconnect(); } catch (e) {}
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
