# Implementation Plan: Assign Invoice/OR Numbers to Payments Without Numbers

## Overview
Allow cashiers to assign invoice and OR numbers to payments that were created with mode='none' (payments without OR/Invoice numbers).

## Current State Analysis

### Backend (Laravel)
- `CashierController::createPayment()` already supports mode='none' which creates payments without numbers
- When mode='none', no number column is populated in payment_details
- The cashier's OR/Invoice counters are not incremented for mode='none' payments

### Frontend (AngularJS)
- Cashier viewer supports creating payments with mode='none' 
- Payment details table shows these payments but they lack OR/Invoice numbers
- No current UI to assign numbers after creation

## Implementation Plan

### Phase 1: Backend API Endpoint

#### 1.1 Create New Endpoint for Assigning Numbers
**File**: `laravel-api/app/Http/Controllers/Api/V1/CashierController.php`

Add new method:
```php
public function assignNumber($cashierId, $paymentId, CashierPaymentAssignNumberRequest $request)
```

This endpoint will:
- Validate the payment exists and has no number assigned
- Validate the cashier has available numbers in their range
- Assign the next available number based on type (OR/Invoice)
- Increment the cashier's counter
- Log the action in system logs

#### 1.2 Update Request Validation
**File**: `laravel-api/app/Http/Requests/Api/V1/CashierPaymentAssignNumberRequest.php`

Update to include:
```php
'type' => 'required|in:or,invoice',
'number' => 'nullable|integer', // Optional: specific number to assign
```

#### 1.3 Add Route
**File**: `laravel-api/routes/api.php`

Add:
```php
Route::post('cashiers/{cashier}/payments/{payment}/assign-number', [CashierController::class, 'assignNumber']);
```

### Phase 2: Frontend UI Components

#### 2.1 Add "Assign Number" Button in Payment Details Table
**File**: `frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html`

Add button for payments without numbers:
```html
<button ng-if="!p.or_no && !p.invoice_number && vm.canEdit" 
        ng-click="vm.openAssignNumberModal(p)"
        class="btn btn-sm btn-primary">
  Assign Number
</button>
```

#### 2.2 Create Assign Number Modal
Add modal dialog with:
- Radio buttons to select type (OR/Invoice)
- Display next available number for selected type
- Option to enter custom number (if within range)
- Validation messages
- Submit/Cancel buttons

#### 2.3 Update Controller Logic
**File**: `frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js`

Add methods:
```javascript
vm.openAssignNumberModal = function(payment) { ... }
vm.assignNumber = function() { ... }
vm.validateNumberAvailable = function(type, number) { ... }
```

### Phase 3: Service Layer Updates

#### 3.1 Update CashierService
**File**: `laravel-api/app/Services/CashierService.php`

Add methods:
```php
public function assignNumberToPayment($cashier, $paymentId, $type, $number = null)
public function getNextAvailableNumber($cashier, $type)
public function isNumberAvailable($type, $number, $excludePaymentId = null)
```

#### 3.2 Update Frontend Service
**File**: `frontend/unity-spa/features/cashiers/cashiers.service.js`

Add method:
```javascript
assignPaymentNumber: function(cashierId, paymentId, data) {
  return $http.post(API + '/cashiers/' + cashierId + '/payments/' + paymentId + '/assign-number', data);
}
```

### Phase 4: Database Considerations

#### 4.1 Ensure Columns Exist
Check and ensure these columns exist in payment_details:
- `or_no` or `or_number` for OR numbers
- `invoice_number` for invoice numbers
- `updated_at` for tracking modifications

#### 4.2 Add Index for Performance
Consider adding index on:
- `(or_no, status)` 
- `(invoice_number, status)`

### Phase 5: Business Rules & Validation

1. **Number Assignment Rules**:
   - Can only assign to payments with mode='none' or null numbers
   - Cannot reassign if number already exists
   - Number must be within cashier's assigned range
   - Number must not be already used by another payment
   - Must increment cashier's counter after assignment

2. **Permission Checks**:
   - Only users with 'finance' or 'admin' role can assign
   - Cashier must be the one who created the payment OR have admin rights

3. **Audit Trail**:
   - Log all number assignments in system_logs
   - Track: who assigned, when, old value (null), new value

### Phase 6: UI/UX Enhancements

1. **Visual Indicators**:
   - Highlight payments without numbers in red/yellow
   - Show badge count of unassigned payments
   - Add filter to show only unassigned payments

2. **Bulk Operations** (Future Enhancement):
   - Select multiple payments
   - Assign numbers in sequence
   - Export report of assignments

### Phase 7: Testing Requirements

1. **Unit Tests**:
   - Test number assignment logic
   - Test validation rules
   - Test counter increments

2. **Integration Tests**:
   - Test API endpoint
   - Test permission checks
   - Test concurrent assignments

3. **Frontend Tests**:
   - Test modal functionality
   - Test validation messages
   - Test UI updates after assignment

## Implementation Steps

### Step 1: Backend API (Day 1)
- [ ] Create assignNumber method in CashierController
- [ ] Update CashierService with assignment logic
- [ ] Add validation rules
- [ ] Add system logging
- [ ] Add API route

### Step 2: Frontend Modal (Day 2)
- [ ] Create assign number modal HTML
- [ ] Add modal controller logic
- [ ] Add validation in frontend
- [ ] Connect to backend API

### Step 3: Integration & Testing (Day 3)
- [ ] Test number assignment flow
- [ ] Test validation scenarios
- [ ] Test permission checks
- [ ] Fix any issues

### Step 4: UI Polish (Day 4)
- [ ] Add visual indicators
- [ ] Add loading states
- [ ] Add success/error messages
- [ ] Update documentation

## Rollback Plan

If issues arise:
1. Disable the "Assign Number" button via feature flag
2. Revert API endpoint changes
3. Payments created with mode='none' remain unchanged
4. Manual database updates can be done if needed

## Success Metrics

1. **Functional**:
   - 100% of mode='none' payments can be assigned numbers
   - No duplicate numbers assigned
   - Cashier counters accurately maintained

2. **Performance**:
   - Assignment completes in < 2 seconds
   - No impact on existing payment creation

3. **User Experience**:
   - Clear visual indication of unassigned payments
   - Intuitive assignment process
   - Clear error messages

## Notes

- Consider adding a configuration option to auto-assign numbers to mode='none' payments after a certain period
- Future enhancement: batch assignment tool for multiple payments
- Consider adding report of all number assignments for audit purposes
