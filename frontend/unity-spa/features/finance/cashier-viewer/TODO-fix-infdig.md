# Cashier Viewer — Fix [$rootScope:infdig] Infinite Digest

Goal:
- Eliminate infinite digest loops caused by inline array/object literals in ng-options within the Cashier Viewer template.

Root Cause:
- Template uses inline literals in ng-options which create a new array/object every digest:
  - Payment Type: `ng-options="t for t in ['full','partial']"`
  - Allow Enroll: `ng-options="v.value as v.label for v in [{value:0,label:'No'},{value:1,label:'Yes'}]"`
  - Has Downpayment: same as above
  - Status: inline array of objects
- These non-idempotent expressions can retrigger watchers repeatedly, especially when combined with ng-change that updates server-backed state, leading to [$rootScope:infdig].

Plan:
1) Controller: Define stable option arrays on `vm` so references are stable across digests.
   - `vm.paymentTypeOptions = ['full','partial']`
   - `vm.booleanOptions = [{ value: 0, label: 'No' }, { value: 1, label: 'Yes' }]`
   - `vm.enrollmentStatusOptions = [
        { value: 'enlisted', label: 'enlisted' },
        { value: 'enrolled', label: 'enrolled' },
        { value: 'loa', label: 'loa' },
        { value: 'withdrawn before', label: 'withdrawn before' },
        { value: 'withdrawn after', label: 'withdrawn after' },
        { value: 'withdrawn end', label: 'withdrawn end' }
     ]`

2) Template: Replace inline ng-options with references to these arrays:
   - Payment Type:
     - From: `ng-options="t for t in ['full','partial']"`
     - To:   `ng-options="t for t in vm.paymentTypeOptions"`
   - Allow Enroll and Has Downpayment:
     - From: `ng-options="v.value as v.label for v in [{value:0,label:'No'},{value:1,label:'Yes'}]"`
     - To:   `ng-options="v.value as v.label for v in vm.booleanOptions track by v.value"`
   - Status:
     - From: inline array of objects
     - To:   `ng-options="v.value as v.label for v in vm.enrollmentStatusOptions track by v.value"`

Verification:
- Change each dropdown and ensure no AngularJS [$rootScope:infdig] error in console.
- Ensure vm.onOptionChange() still fires and updates registration successfully.
- Reload page and confirm selected values load correctly from vm.edit snapshot.
- Smoke test other panels (Invoices, Payments) to ensure no regressions.

Status:
- [ ] Add `vm.paymentTypeOptions` to controller
- [ ] Add `vm.booleanOptions` to controller
- [ ] Add `vm.enrollmentStatusOptions` to controller
- [ ] Update Payment Type ng-options to use `vm.paymentTypeOptions`
- [ ] Update Allow Enroll ng-options to use `vm.booleanOptions`
- [ ] Update Has Downpayment ng-options to use `vm.booleanOptions`
- [ ] Update Status ng-options to use `vm.enrollmentStatusOptions`
- [ ] Manual verification (no infdig, options functional)
