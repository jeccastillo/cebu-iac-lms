Title: Replace Select2 with PrimeUI-style Autocomplete in Cashier Viewer

Context:
- App is AngularJS 1.x. PrimeNG is Angular (2+) and not compatible.
- Using a custom AngularJS directive named pui-autocomplete to provide a PrimeUI-like autocomplete UX without migrating frameworks.
- Limit scope to the Cashier Viewer page to avoid side effects.

Plan (steps):
- [ ] Create shared directive: shared/directives/pui-autocomplete.directive.js
      - Attributes:
        - ng-model: selected value (id)
        - pui-source: array of options
        - pui-item-key: unique id field (default: id)
        - pui-label: Angular expression evaluated with {item} to render label
        - placeholder: input placeholder
        - pui-on-select: callback invoked after selection
      - Features:
        - Client-side filter (case-insensitive contains)
        - Max results 20 (configurable via pui-max-results)
        - Hides on blur/outside click
        - Syncs label when ngModel changes externally
- [ ] Include directive script in index.html after select2.directive.js
- [ ] Update cashier-viewer.html: replace Student Select2 with pui-autocomplete input
- [ ] Update cashier-viewer.html: replace Payment Mode Select2 with pui-autocomplete input
- [ ] Manual test:
      - Type to search students; select triggers vm.onStudentSelected() and navigates
      - Type to search payment modes; select updates vm.payment.mode_of_payment_id and vm.onPaymentModeChange() fills method
      - Form validation and submit still work

Notes:
- Do not remove Select2 from index.html; other pages may still rely on it.
- Styling follows existing Tailwind input appearance.
