(function () {
  'use strict';

  // Requires jQuery and Select2 to be loaded globally
  angular
    .module('unityApp')
    .directive('select2', select2Directive);

  select2Directive.$inject = ['$timeout'];
  function select2Directive($timeout) {
    return {
      restrict: 'AC',
      require: 'ngModel',
      scope: {
        // Optional: pass a watched collection/expr to re-init select2 when options change
        select2Watch: '=?',

        // ng-select2-component-like bindings (optional)
        select2Data: '=?',          // [{id, text}] option source when not using ng-options
        select2Value: '=?',         // two-way value proxy
        onValueChanged: '&?',       // callback({ value })
        onOpened: '&?',             // callback()
        onClosed: '&?'              // callback()
      },
      link: function (scope, element, attrs, ngModel) {
        // Helpers to map between Angular ngOptions option value strings and model/view values
        function toDomValue(v) {
          try {
            if (v === undefined || v === null || v === '') return '';
            var vs = '' + v;
            var candidates = [
              'number:' + vs,
              'string:' + vs,
              'boolean:' + vs,
              'object:' + vs,
              vs
            ];
            for (var i = 0; i < candidates.length; i++) {
              var c = candidates[i];
              // Escape quotes in selector
              var sel = 'option[value="' + c.replace(/(["\\])/g, '\\$1') + '"]';
              if (jQuery(element).find(sel).length) return c;
            }
            return vs;
          } catch (e) {
            return v == null ? '' : ('' + v);
          }
        }
        function fromDomValue(val) {
          try {
            if (val === undefined || val === null || val === '') return null;
            if (typeof val !== 'string') val = '' + val;
            if (val.indexOf('number:') === 0) {
              var n = parseFloat(val.slice(7));
              return isFinite(n) ? n : null;
            }
            if (val.indexOf('boolean:') === 0) {
              var b = val.slice(8);
              return b === 'true';
            }
            if (val === 'null') return null;
            if (val.indexOf('string:') === 0) return val.slice(7);
            return val;
          } catch (e) {
            return val;
          }
        }
        var hasNgOptions = typeof attrs.ngOptions !== 'undefined';

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
              var allowClear = (typeof attrs.select2AllowClear !== 'undefined');

              // If external data array is provided and ng-options is NOT used, rebuild <option> list
              if (Array.isArray(scope.select2Data) && !hasNgOptions) {
                try {
                  var keepVal = ngModel.$viewValue;
                  jQuery(element).empty();
                  // add empty option to support placeholder/allowClear
                  if (placeholder || allowClear) {
                    jQuery(element).append(jQuery('<option>').val('').text(''));
                  }
                  scope.select2Data.forEach(function (item) {
                    if (item && (item.id !== undefined) && (item.text !== undefined)) {
                      var $opt = jQuery('<option>').val(item.id).text(item.text);
                      jQuery(element).append($opt);
                    }
                  });
                  if (keepVal !== undefined && keepVal !== null && keepVal !== '') {
                    jQuery(element).val(toDomValue(keepVal));
                  }
                } catch (e) {}
              }

              var select2Opts = {
                width: '100%',
                placeholder: placeholder || '',
                allowClear: allowClear
              };

              jQuery(element).select2(select2Opts);

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

              // Sync select2 -> ngModel (guard against recursive updates)
              jQuery(element).off('change.select2').on('change.select2', function () {
                scope.$applyAsync(function () {
                  var current = jQuery(element).val();
                  var parsed = fromDomValue(current);
                  if (parsed !== ngModel.$viewValue) {
                    ngModel.$setViewValue(parsed);
                  }
                  // Mirror to select2Value if bound (keep raw DOM value to interop with select2)
                  if (typeof scope.select2Value !== 'undefined') {
                    scope.select2Value = current;
                  }
                  // Fire callback if provided
                  if (attrs.onValueChanged && typeof scope.onValueChanged === 'function') {
                    try {
                      scope.onValueChanged({ value: current });
                    } catch (e) {}
                  }
                });
              });

              // Open/Close callbacks
              jQuery(element).off('select2:open.select2ext').on('select2:open.select2ext', function () {
                scope.$applyAsync(function () {
                  if (attrs.onOpened && typeof scope.onOpened === 'function') {
                    try { scope.onOpened(); } catch (e) {}
                  }
                });
              });
              jQuery(element).off('select2:close.select2ext').on('select2:close.select2ext', function () {
                scope.$applyAsync(function () {
                  if (attrs.onClosed && typeof scope.onClosed === 'function') {
                    try { scope.onClosed(); } catch (e) {}
                  }
                });
              });

              // Ensure the current model is reflected in the UI without re-triggering our handler
              jQuery(element).val(toDomValue(ngModel.$viewValue)).trigger('change.select2');
            } catch (e) {
              // swallow init errors to avoid breaking the page
            }
          });
        }

        // Reinitialize when the watched collection changes (e.g. options list updated)
        scope.$watch('select2Watch', function () {
          init();
        }, true);

        // Reinitialize when external data array changes
        scope.$watch('select2Data', function () {
          init();
        }, true);

        // Keep select2 in sync when proxy value changes
        scope.$watch('select2Value', function (nv, ov) {
          if (nv === ov) return;
          $timeout(function () {
            try {
              var current = jQuery(element).val();
              if (nv !== undefined) {
                var desiredDom = toDomValue(nv);
                if (current !== desiredDom) {
                  ngModel.$setViewValue(fromDomValue(nv));
                  jQuery(element).val(desiredDom).trigger('change.select2');
                }
              }
            } catch (e) {}
          });
        });

        // Watch model changes and update the UI (only when different to avoid loops)
        scope.$watch(function () { return ngModel.$viewValue; }, function () {
          $timeout(function () {
            try {
              var desired = ngModel.$viewValue;
              var current = jQuery(element).val();
              var desiredDom = toDomValue(desired);
              if (current !== desiredDom) {
                jQuery(element).val(desiredDom).trigger('change.select2');
              }
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
