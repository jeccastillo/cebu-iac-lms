(function () {
  'use strict';

  // AngularJS directive wrapper for intl-tel-input with lazy loading fallback
  // - Renders country dropdown with flags
  // - Keeps ngModel in E.164 format (e.g., +63917...)
  // - Adds "tel" validator via iti.isValidNumber()
  angular.module('unityApp').directive('itiTel', ['$timeout', function ($timeout) {
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function (scope, elem, attrs, ngModel) {
        var input = elem[0];
        var iti = null;
        var libSrcList = [
          'https://cdn.jsdelivr.net/npm/intl-tel-input@18.7.1/build/js/intlTelInput.min.js',
          'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js',
          'https://unpkg.com/intl-tel-input@18.7.1/build/js/intlTelInput.min.js'
        ];
        var utilsSrcList = [
          'https://cdn.jsdelivr.net/npm/intl-tel-input@18.7.1/build/js/utils.js',
          'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.min.js',
          'https://unpkg.com/intl-tel-input@18.7.1/build/js/utils.js'
        ];

        // Validator: permissive until plugin is ready (required handles emptiness)
        function setValidators() {
          ngModel.$validators.tel = function (modelValue, viewValue) {
            var val = modelValue || viewValue || '';
            if (!val) return true;
            if (!iti) return true;
            try { return iti.isValidNumber(); }
            catch (e) { return false; }
          };
        }
        setValidators();

        // Parser: return E.164 when available, else raw
        ngModel.$parsers.push(function (viewValue) {
          if (iti) {
            try {
              var e164 = iti.getNumber();
              return e164 || viewValue || '';
            } catch (e) {
              return viewValue || '';
            }
          }
          return viewValue || '';
        });

        // Formatter: reflect model into control; format if iti is ready
        ngModel.$formatters.push(function (modelValue) {
          if (!modelValue) {
            input.value = '';
            return modelValue;
          }
          $timeout(function () {
            try {
              if (iti) {
                iti.setNumber(modelValue);
              } else {
                input.value = modelValue;
              }
            } catch (e) {
              input.value = modelValue;
            }
          }, 0);
          return modelValue;
        });

        function updateModelFromInput() {
          var val = input.value || '';
          var e164 = '';
          if (iti) {
            try { e164 = iti.getNumber(); } catch (e) {}
          }

          if (!e164 && !val) {
            ngModel.$setViewValue('');
          } else {
            // Prefer E.164, fall back to raw input if plugin failed or not ready
            ngModel.$setViewValue(e164 || val);
          }
          ngModel.$validate();
          if (!scope.$$phase) {
            scope.$applyAsync();
          }
        }

        function bindEvents() {
          elem.on('blur', updateModelFromInput);
          elem.on('keyup', updateModelFromInput);
          elem.on('input', updateModelFromInput);
          elem.on('change', updateModelFromInput);
          elem.on('countrychange', updateModelFromInput);
        }

        function unbindEvents() {
          try {
            elem.off('blur', updateModelFromInput);
            elem.off('keyup', updateModelFromInput);
            elem.off('input', updateModelFromInput);
            elem.off('change', updateModelFromInput);
            elem.off('countrychange', updateModelFromInput);
          } catch (e) {}
        }

        function initIti() {
          if (iti) return;
          try {
            iti = window.intlTelInput(input, {
              initialCountry: 'ph',
              separateDialCode: true,
              nationalMode: false,
              autoPlaceholder: 'polite',
              formatOnDisplay: true,
              dropdownContainer: document.body, /* render dropdown in body to avoid clipping/z-index issues */
              preferredCountries: ['ph','us'],
              allowDropdown: true,
              utilsScript: ''
            });
            bindEvents();
            // Trigger a validation after init to update tel validator state
            $timeout(function () { ngModel.$validate(); }, 0);
          } catch (e) {
            // keep plain input if initialization fails
          }
        }

        function ensureScriptLoadedMulti(presenceCheckFn, srcList, cb) {
          var idx = 0;
          function tryNext() {
            try {
              if (presenceCheckFn && presenceCheckFn()) {
                return $timeout(function () { cb && cb(); }, 0);
              }
            } catch (e) {}
            if (idx >= srcList.length) {
              return; // give up; keep plain input
            }
            var src = srcList[idx++];
            var existing = document.querySelector('script[src="' + src + '"]');
            if (existing) {
              // already requested; wait a bit and re-check
              return $timeout(tryNext, 100);
            }
            var s = document.createElement('script');
            s.src = src;
            s.async = true;
            s.onload = function () {
              $timeout(function () { cb && cb(); }, 0);
            };
            s.onerror = function () {
              tryNext(); // try next source
            };
            document.head.appendChild(s);
          }
          tryNext();
        }

        // Ensure utils are loaded, then init
        function ensureUtilsThenInit() {
          if (window.intlTelInputUtils) {
            return initIti();
          }
          ensureScriptLoadedMulti(function () { return !!window.intlTelInputUtils; }, utilsSrcList, function () {
            $timeout(function () {
              // proceed to init whether or not utils loaded (utils improves formatting/validation)
              initIti();
            }, 0);
          });
        }

        // Initialize: load library (with fallbacks) then utils, then init
        if (window.intlTelInput) {
          ensureUtilsThenInit();
        } else {
          ensureScriptLoadedMulti(function () { return !!window.intlTelInput; }, libSrcList, function () {
            $timeout(function () {
              if (window.intlTelInput) {
                ensureUtilsThenInit();
              }
            }, 0);
          });
        }

        // Cleanup
        scope.$on('$destroy', function () {
          unbindEvents();
          try { if (iti) iti.destroy(); } catch (e) {}
        });
      }
    };
  }]);
})();
