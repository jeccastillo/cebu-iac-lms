# Applicants Analytics: Conversion Rate for Reservation

Goal:
Add a "Conversion Rate for Reservation" metric to the Applicants Analytics page using the CodeIgniter formula:
((reserved) / (for_reservation + reserved + withdrawn_before + withdrawn_after + withdrawn_end)) * 100

Steps:
- [ ] Update analytics.controller.js
  - [ ] Add computeReservationConversion(summary) using the above formula with denominator guard and 2-dec rounding.
  - [ ] Extend vm.metrics to include reservationA, reservationB alongside conversionA, conversionB.
  - [ ] In load() success path, compute reservationA/reservationB and include them in vm.metrics.
  - [ ] In all reset/error/empty-state branches, ensure reservationA/reservationB are set to null.

- [ ] Update analytics.html
  - [ ] Add a second Quick Stats card titled "Conversion Rate for Reservation".
  - [ ] Display Term A and Term B values using vm.metrics.reservationA.percent and vm.metrics.reservationB.percent (number:2).

- [ ] Manual verification
  - [ ] Reload Applicants Analytics page.
  - [ ] Verify both metrics render for Term A and Term B.
  - [ ] Test with different filters (campus/status/type/sub_type/date range).
  - [ ] Confirm values match the legacy CodeIgniter computation.

Notes:
- Backend already returns by_status counts including for_reservation, reserved, withdrawn_before, withdrawn_after, withdrawn_end; no backend change required.
- Keep existing "Conversion Rate Reserved to Enrolled" card intact.
