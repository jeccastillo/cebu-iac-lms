Title: pui-autocomplete keyboard navigation (ArrowUp/ArrowDown)

Status: Completed

Scope:
- Enhance pui-autocomplete (AngularJS 1.x directive) to support keyboard navigation:
  - ArrowDown: move highlight down (opens panel if closed and highlights first item).
  - ArrowUp: move highlight up.
  - Enter: select highlighted item if any; fallback to first visible.
  - Escape: closes the panel (existing).

Files changed:
- frontend/unity-spa/shared/directives/pui-autocomplete.directive.js

Implementation notes:
- State:
  - highlightedIndex: tracks active/highlighted suggestion (-1 = none).
  - currentList: last rendered suggestions.
- Accessibility:
  - Panel UL has role="listbox".
  - Items LI have role="option" and aria-selected toggled.
- Rendering:
  - renderList(list):
    - resets highlightedIndex to -1.
    - sets currentList = list.slice().
    - wires mouseenter to update highlight and mousedown to select.
- Keyboard:
  - ArrowDown:
    - If closed: render filtered list for current query, highlight first item.
    - If open: highlight next item (no wrap, stops at last).
  - ArrowUp:
    - If open: highlight previous item (no wrap, stops at first).
  - Enter:
    - If a highlight exists: selects highlighted item.
    - Else: selects first visible item (legacy behavior).
  - Escape:
    - Close panel (legacy behavior).
- Scrolling:
  - Highlighted item is kept in view via scrollIntoView({ block: 'nearest' }).
- No public API changes (pui-source, pui-label, pui-on-select unchanged).

Manual test checklist:
1) Basic typing
   - Type to filter: suggestions open and match.
   - Move with ArrowDown/ArrowUp: highlight changes and follows within visible area.
   - Press Enter: the highlighted item is selected; ng-model updates; input shows label.

2) Opening with ArrowDown
   - Focus input, press ArrowDown without typing.
   - Panel opens showing first N items; first item highlighted.

3) Bounds
   - At first item, ArrowUp keeps highlight at first.
   - At last item, ArrowDown keeps highlight at last.

4) Mouse interactions
   - Hover moves highlight to hovered item.
   - Click or mousedown selects that item.

5) Escape and blur
   - Escape closes panel.
   - Blurring input closes panel; model is set to null only when the input is empty (existing behavior preserved).

Pages to spot check:
- Admissions: applicants/list.html (campus filter)
- Finance: cashier-viewer/cashier-viewer.html
- Finance: student-billing/list.html
