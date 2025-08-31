(function () {
  'use strict';

  // AngularJS directive that provides a PrimeUI-like Autocomplete behavior using plain JS
  // Usage (attribute on an input inside a relatively positioned container):
  //
  // <div class="relative">
  //   <input type="text"
  //          class="block w-full rounded border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
  //          pui-autocomplete
  //          ng-model="vm.selectedId"
  //          pui-source="vm.items"
  //          pui-item-key="id"
  //          pui-label="(item.code + ' — ' + item.name)"
  //          placeholder="Search..."
  //          pui-max-results="20"
  //          pui-on-select="vm.onSelect()" />
  // </div>
  //
  // Notes:
  // - ng-model holds the item key (id by default).
  // - pui-label is an Angular expression evaluated with local 'item'.
  // - pui-source is an expression that evaluates to an Array of items.
  // - The parent container should have 'relative' class; the dropdown uses absolute positioning.

  angular.module('unityApp')
    .directive('puiAutocomplete', puiAutocomplete);

  puiAutocomplete.$inject = ['$parse', '$timeout', '$document'];
  function puiAutocomplete($parse, $timeout, $document) {
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function (scope, element, attrs, ngModelCtrl) {
        // Config
        var getSource = $parse(attrs.puiSource);
        var keyField = attrs.puiItemKey || 'id';
        var maxResults = parseInt(attrs.puiMaxResults, 10);
        if (!isFinite(maxResults) || maxResults <= 0) maxResults = 20;

        // Label function from expression; fallback to string(item)
        var labelExpr = attrs.puiLabel || null;
        var getLabel = labelExpr
          ? function (item) {
              try { return scope.$eval(labelExpr, { item: item }); }
              catch (e) { return String(item && (item[keyField] != null ? item[keyField] : '')); }
            }
          : function (item) {
              var raw = (item && (item.name || item.label || item[keyField])) || '';
              return String(raw);
            };

        // Optional on-select callback
        var onSelectExpr = attrs.puiOnSelect || null;

        // Build container for dropdown if parent is not relative; ensure positioning context
        try {
          var parent = element.parent();
          var pos = window.getComputedStyle(parent[0]).position;
          if (pos !== 'relative' && pos !== 'absolute' && pos !== 'fixed') {
            parent.addClass('relative');
          }
        } catch (e) {}

        // Create dropdown panel
        var panel = angular.element(
          '<ul class="pui-ac-panel absolute z-50 w-full bg-white border border-gray-200 rounded shadow max-h-60 overflow-auto hidden"></ul>'
        );
        element.after(panel);

        // Internal state
        var itemsIndex = Object.create(null); // key -> item
        var itemsList = []; // raw list
        var isOpen = false;
        var lastQuery = '';

        function openPanel() {
          if (!isOpen) {
            panel.removeClass('hidden');
            isOpen = true;
          }
        }
        function closePanel() {
          if (isOpen) {
            panel.addClass('hidden');
            isOpen = false;
          }
        }
        function clearPanel() {
          panel.empty();
        }

        function rebuildIndex(list) {
          itemsIndex = Object.create(null);
          itemsList = Array.isArray(list) ? list.slice() : [];
          for (var i = 0; i < itemsList.length; i++) {
            var it = itemsList[i];
            if (!it) continue;
            var k = (it[keyField] != null) ? String(it[keyField]) : null;
            if (k != null) {
              itemsIndex[k] = it;
            }
          }
          // After source changes, refresh displayed label based on ngModel
          syncInputWithModel();
        }

        // Filtering
        function filterItems(query) {
          var q = (query || '').toLowerCase();
          var out = [];
          if (!q) {
            // Show first N items (like dropdown)
            for (var i = 0; i < itemsList.length && out.length < maxResults; i++) {
              out.push(itemsList[i]);
            }
            return out;
          }
          for (var j = 0; j < itemsList.length; j++) {
            var it = itemsList[j];
            try {
              var lbl = (getLabel(it) || '').toString().toLowerCase();
              if (lbl.indexOf(q) !== -1) {
                out.push(it);
                if (out.length >= maxResults) break;
              }
            } catch (e) {}
          }
          return out;
        }

        // Render suggestions list
        function renderList(list, query) {
          clearPanel();
          if (!list || !list.length) {
            closePanel();
            return;
          }
          for (var i = 0; i < list.length; i++) {
            (function (item) {
              var li = angular.element('<li class="px-3 py-2 cursor-pointer hover:bg-blue-50"></li>');
              var labelText = '';
              try { labelText = getLabel(item) || ''; } catch (e) { labelText = ''; }
              li.text(labelText);
              li.on('mousedown', function (evt) {
                // mousedown instead of click to avoid blur before click
                evt.preventDefault();
                selectItem(item);
              });
              panel.append(li);
            })(list[i]);
          }
          openPanel();
        }

        // Selection
        function selectItem(item) {
          try {
            var k = item ? item[keyField] : null;
            // Update model (as key)
            ngModelCtrl.$setViewValue(k != null ? k : null);
            ngModelCtrl.$render();
            // Update input display
            var lbl = item ? (getLabel(item) || '') : '';
            element.val(lbl);
            lastQuery = lbl || '';
            closePanel();
            // Trigger optional onSelect callback
            if (onSelectExpr) {
              // Evaluate in parent scope to allow controller method access
              scope.$eval(onSelectExpr);
            }
          } catch (e) {
            closePanel();
          }
        }

        // Sync displayed label when model changes from outside
        function syncInputWithModel() {
          var mv = ngModelCtrl.$viewValue;
          var key = (mv != null) ? String(mv) : null;
          var item = (key != null && Object.prototype.hasOwnProperty.call(itemsIndex, key)) ? itemsIndex[key] : null;
          var lbl = item ? (getLabel(item) || '') : '';
          // Only update element if value differs to avoid moving cursor during typing
          if (element.val() !== lbl) {
            element.val(lbl);
            lastQuery = lbl || '';
          }
        }

        // Hook model -> view
        ngModelCtrl.$render = function () {
          syncInputWithModel();
        };

        // Watch source changes
        scope.$watchCollection(function () { return getSource(scope); }, function (nv) {
          rebuildIndex(nv || []);
        });

        // Input handlers
        element.on('input', function () {
          var q = element.val() || '';
          lastQuery = q;
          var list = filterItems(q);
          scope.$applyAsync(function () {
            renderList(list, q);
          });
        });

        element.on('focus', function () {
          var q = element.val() || '';
          lastQuery = q;
          var list = filterItems(q);
          scope.$applyAsync(function () {
            renderList(list, q);
          });
        });

        // Keyboard navigation (basic: Enter selects first item if open)
        element.on('keydown', function (evt) {
          if (evt.key === 'Escape') {
            scope.$applyAsync(closePanel);
          } else if (evt.key === 'Enter') {
            if (isOpen) {
              // Select first visible
              var first = panel.children()[0];
              if (first) {
                evt.preventDefault();
                angular.element(first).triggerHandler('mousedown');
              }
            }
          }
        });

        // Blur handling: close after a brief delay to allow click selection
        element.on('blur', function () {
          $timeout(function () {
            closePanel();
            // If text doesn't match any item exactly, keep the text but do not change model.
            // If cleared, set model to null.
            var txt = element.val() || '';
            if (!txt) {
              scope.$applyAsync(function () {
                ngModelCtrl.$setViewValue(null);
                ngModelCtrl.$render();
              });
            }
          }, 150);
        });

        // Outside click closes panel
        function onDocClick(e) {
          try {
            if (element[0] === e.target || panel[0] === e.target) return;
            if (panel[0].contains(e.target)) return;
            if (element[0].contains(e.target)) return;
            scope.$applyAsync(closePanel);
          } catch (err) {}
        }
        $document.on('click', onDocClick);

        // Cleanup
        scope.$on('$destroy', function () {
          try { $document.off('click', onDocClick); } catch (e) {}
          try { element.off(); } catch (e2) {}
          try { panel.remove(); } catch (e3) {}
        });
      }
    };
  }
})();
