# TODO: Assign Invoice/OR Numbers to Payments Without Numbers

## Problem Statement
Cashiers need the ability to assign invoice and OR numbers to payments that were created with mode='none' (payments without OR/Invoice numbers).

## Current Behavior
- Payments can be created with mode='none' which doesn't assign any number
- These payments appear in the payment details table without OR/Invoice numbers
- There's no way to assign numbers after creation

## Required Behavior
- Cashiers should be able to assign OR or Invoice numbers to payments that don't have them
- The assignment should use the cashier's next available number from their range
- The cashier's counter should be incremented after assignment
- All assignments should be logged for audit purposes

## Implementation Tasks

### Backend (Laravel)

#### 1. Add API Endpoint
- [X] Create `assignNumber()` method in `CashierController`
- [X] Add route: `POST /api/v1/cashiers/{cashier}/payments/{payment}/assign-number`
- [ ] Update `CashierPaymentAssignNumberRequest` validation

#### 2. Service Layer
- [ ] Add `assignNumberToPayment()` method in `CashierService`
- [ ] Add `getNextAvailableNumber()` helper method
- [ ] Add `isNumberAvailable()` validation method

#### 3. Database
- [ ] Verify payment_details columns exist: `or_no`, `invoice_number`
- [ ] Add indexes for performance if needed

#### 4. System Logging
- [ ] Log all number assignments in system_logs table
- [ ] Include old value (null) and new value in logs

### Frontend (AngularJS)

#### 1. UI Components
- [ ] Add "Assign Number" button for payments without numbers
- [ ] Create modal dialog for number assignment
- [ ] Add type selection (OR/Invoice)
- [ ] Show next available number
- [ ] Add validation messages

#### 2. Controller Logic
- [ ] Add `openAssignNumberModal()` method
- [ ] Add `assignNumber()` method
- [ ] Add `validateNumberAvailable()` method
- [ ] Handle success/error responses

#### 3. Service Integration
- [ ] Add `assignPaymentNumber()` method to CashiersService
- [ ] Connect to backend API endpoint

#### 4. UI/UX Enhancements
- [ ] Highlight payments without numbers
- [ ] Add loading states during assignment
- [ ] Show success/error toasts
- [ ] Auto-refresh payment list after assignment

### Testing

#### 1. Backend Tests
- [ ] Test number assignment logic
- [ ] Test validation rules (range, availability)
- [ ] Test counter increments
- [ ] Test permission checks
- [ ] Test concurrent assignments

#### 2. Frontend Tests
- [ ] Test modal functionality
- [ ] Test form validation
- [ ] Test API integration
- [ ] Test UI updates after assignment

#### 3. Integration Tests
- [ ] Test full flow: open modal → select type → assign → verify
- [ ] Test error scenarios
- [ ] Test edge cases (last number in range, etc.)

## Acceptance Criteria

1. **Functional Requirements**
   - [ ] Cashiers can assign OR numbers to payments without numbers
   - [ ] Cashiers can assign Invoice numbers to payments without numbers
   - [ ] Numbers are assigned from cashier's configured range
   - [ ] Cashier's counter is incremented after assignment
   - [ ] Cannot assign number if already exists on payment
   - [ ] Cannot assign number outside of cashier's range
   - [ ] Cannot assign already-used numbers

2. **Security Requirements**
   - [ ] Only users with 'finance' or 'admin' role can assign
   - [ ] Cashier must own the payment OR have admin rights
   - [ ] All assignments are logged with user info

3. **Performance Requirements**
   - [ ] Assignment completes in < 2 seconds
   - [ ] No impact on existing payment creation
   - [ ] Efficient queries with proper indexes

4. **User Experience**
   - [ ] Clear visual indication of payments without numbers
   - [ ] Intuitive assignment process
   - [ ] Clear error messages for validation failures
   - [ ] Immediate UI update after successful assignment

## Notes

- Consider adding batch assignment for multiple payments
- Future enhancement: auto-assign after certain period
- Consider adding report of all assignments for audit

## Priority
**HIGH** - This is blocking proper financial tracking and reporting

## Estimated Effort
- Backend: 2 days
- Frontend: 2 days
- Testing: 1 day
- **Total: 5 days**

## Dependencies
- Existing cashier management system
- Payment details table structure
- System logging service

## Risks
- Concurrent assignment conflicts
- Data integrity if cashier ranges change
- Performance impact on large payment lists

## Rollback Plan
1. Disable "Assign Number" button via feature flag
2. Revert API endpoint
3. Manual database updates if needed
