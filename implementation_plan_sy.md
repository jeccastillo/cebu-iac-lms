# Implementation Plan

[Overview]
Add comprehensive field support for tb_mas_sy (School Years/Terms) and refactor the SPA add/edit page into a tabbed UI. Enforce a composite uniqueness constraint to prevent duplicate terms per campus and surface additional scheduling, viewing, application/reconciliation, installment, and operational flag fields. Dates on the UI will use YYYY-MM-DD.

This extends the existing School Year CRUD by aligning the database schema with all supported fields, adding a unique composite index, and improving the user experience with a tabbed form layout. The Laravel requests already accept most extended fields; we will ensure schema alignment and update the AngularJS Unity SPA form to expose these fields in clearly grouped tabs.

[Types]  
Introduce optional fields and a composite uniqueness constraint without altering the primary key or timestamps.

TB_MAS_SY entity specification (superset of current usage):
- Core
  - intID: int, primary key, auto-increment
  - enumSem: string(16), required (e.g., '1st', '2nd', 'Summer')
  - strYearStart: char(4), required (YYYY)
  - strYearEnd: char(4), required (YYYY, gte strYearStart)
  - term_label: string(32) | nullable (default: 'Semester')
  - term_student_type: string(32) | nullable (e.g., 'college', 'shs', 'next')
  - campus_id: int | nullable, FK -> tb_mas_campuses(id), indexed
  - intProcessing: tinyint | nullable (0/1)
  - enumStatus: string(16) | nullable ('active'|'inactive')
  - enumFinalized: string(8) | nullable ('yes'|'no')
- Grading Windows (UI date-only; DB datetime or date)
  - midterm_start: datetime | nullable
  - midterm_end: datetime | nullable
  - final_start: datetime | nullable
  - final_end: datetime | nullable
  - end_of_submission: datetime | nullable (must not be '0000-00-00 00:00:00')
- Academic Timeline (UI date-only)
  - start_of_classes: date/datetime | nullable
  - final_exam_start: date/datetime | nullable
  - final_exam_end: date/datetime | nullable
- Viewing Windows (UI date-only)
  - viewing_midterm_start: date/datetime | nullable
  - viewing_midterm_end: date/datetime | nullable
  - viewing_final_start: date/datetime | nullable
  - viewing_final_end: date/datetime | nullable
- Application/Reconciliation (UI date-only)
  - endOfApplicationPeriod: date/datetime | nullable
  - reconf_start: date/datetime | nullable
  - reconf_end: date/datetime | nullable
  - ar_report_date_generation: date/datetime | nullable
- Installments (UI date-only)
  - installment1..installment5: date/datetime | nullable
- Operational Flags
  - classType: string(16) | nullable
  - pay_student_visa: tinyint | nullable (0/1)
  - is_locked: tinyint | nullable (0/1)
  - enumGradingPeriod: string | nullable ('active'|'inactive')
  - enumMGradingPeriod: string | nullable ('active'|'inactive')
  - enumFGradingPeriod: string | nullable ('active'|'inactive')
- Uniqueness
  - Composite unique index: (strYearStart, strYearEnd, enumSem, campus_id) to prevent duplicate terms per campus (campus_id nullable supported)

Validation (existing requests):
- Store: enumSem (required, string, max:16), strYearStart (required, digits:4), strYearEnd (required, digits:4, gte:strYearStart), optional fields as date/integer/string as defined
- Update: all fields optional; validated if present
- UI policy: All date-like inputs use YYYY-MM-DD (date-only); backend accepts date and will store consistently

[Files]
Create one new migration, optionally extend casts, and refactor SPA edit template into tabs; no deletions.

- New files to be created:
  - laravel-api/database/migrations/2025_08_28_000201_update_tb_mas_sy_extra_columns_and_unique_index.php
    - Purpose: Conditionally add the “extra” columns (academic timeline, viewing windows, application/reconciliation, installments, flags) if missing, and create the composite unique index ux_sy_year_sem_campus on (strYearStart, strYearEnd, enumSem, campus_id) if not present. Include defensive cleanup for zero datetime values if necessary.

- Existing files to be modified:
  - laravel-api/app/Models/SchoolYear.php
    - Extend $casts to include any additional date-like fields for consistent serialization (optional but recommended).
  - frontend/unity-spa/features/school-years/edit.html
    - Refactor into tabs with grouped sections: Core, Grading Windows, Academic Timeline, Viewing Windows, Application/Reconciliation, Installments, Operational Flags. Keep Campus read-only (sourced from selector).
  - frontend/unity-spa/features/school-years/school-years.controller.js
    - Add simple tab state; load and submit mappings for all groups. Ensure _fmtDate produces YYYY-MM-DD and all new fields are included in payload on save.
  - frontend/unity-spa/features/school-years/school-years.service.js
    - No change required (payload passthrough).

- Files to be deleted or moved:
  - None

- Configuration updates:
  - None (Routes are in place; role middleware configured.)

[Functions]
Add UI tab state handlers and complete payload mapping; minor model casts update.

- New functions (SPA):
  - SchoolYearEditController (frontend/unity-spa/features/school-years/school-years.controller.js)
    - vm.activeTab: string; vm.setTab(tab: string): void; vm.isTab(tab: string): boolean
- Modified functions (SPA):
  - vm.load(): map new date fields using _toLocalInput; map enums/flags; preserve existing behaviors
  - vm.save(): include all new date fields via _fmtDate (YYYY-MM-DD) and flags; preserve validation of core fields
- Modified classes (Laravel):
  - App\Models\SchoolYear::$casts: optionally include date-like fields (start_of_classes, final_exam_start, final_exam_end, viewing_midterm_start, viewing_midterm_end, viewing_final_start, viewing_final_end, endOfApplicationPeriod, reconf_start, reconf_end, ar_report_date_generation, installment1..installment5)
- Removed functions/classes:
  - None

[Classes]
No new backend classes; adjust model casts only. SPA controllers remain but with additional responsibilities for tabs and field mapping.

- Modified classes:
  - App\Models\SchoolYear: extend $casts for date-like fields
- New classes:
  - None
- Removed classes:
  - None

[Dependencies]
No new Composer or npm dependencies. Tailwind already integrated via CDN in the SPA.

- Backend:
  - Continue using SystemLogService for auditing in controller actions.
  - Maintain role middleware for POST/PUT/DELETE.
- Frontend:
  - Use existing Tailwind and app styles; tabs implemented with simple AngularJS state and utility classes.

[Testing]
Validate the schema changes, uniqueness enforcement, and UI interactions across tabs.

- API/DB:
  - Migration applies cleanly on environments with zero datetime values (defensive updates to null if needed).
  - Composite unique index prevents duplicates for (strYearStart, strYearEnd, enumSem, campus_id).
  - Store/Update accept YYYY-MM-DD for date fields and persist correctly.
- UI:
  - Tabs render and switch without losing form state.
  - Save/Update passes all fields; values round-trip correctly (load after save shows the same).
  - Uniqueness violation is surfaced as a clear message in the UI on create/update.
  - Campus binding stays read-only from the global selector.

[Implementation Order]
Proceed backend-first to ensure the schema supports the full UI, then adjust model casts, then refactor UI.

1) Database/Migrations
   - Create 2025_08_28_000201_update_tb_mas_sy_extra_columns_and_unique_index.php to add missing “extra” columns and the composite unique index (strYearStart, strYearEnd, enumSem, campus_id). Include guards around existing columns and index creation; defensively set invalid zero datetimes to NULL before index/FK operations if necessary.
   - Ensure previous migration 2025_08_28_000200_update_tb_mas_sy_columns.php and 2025_08_25_000009_add_campus_id_to_tb_mas_sy.php have run (or this migration should be idempotent and safe).

2) Backend Model
   - Extend App\Models\SchoolYear::$casts with additional date-like fields for serialization consistency (date or datetime, consistent with other casts).

3) Frontend UI (Tabbed form)
   - Update edit.html to a tabbed layout:
     - Core (enumSem, strYearStart, strYearEnd, term_label, term_student_type, campus_id [read-only])
     - Grading Windows (midterm_start/end, final_start/end, end_of_submission)
     - Academic Timeline (start_of_classes, final_exam_start/end)
     - Viewing Windows (viewing_midterm_start/end, viewing_final_start/end)
     - Application/Reconciliation (endOfApplicationPeriod, reconf_start/end, ar_report_date_generation)
     - Installments (installment1..installment5)
     - Operational Flags (classType, pay_student_visa, is_locked, enumGradingPeriod, enumMGradingPeriod, enumFGradingPeriod; plus intProcessing, enumStatus, enumFinalized already present)
   - Provide simple tab switcher using AngularJS state and Tailwind classes.

4) Frontend Controller
   - Enhance vm.load() to map all new fields using existing helpers.
   - Enhance vm.save() to include all new fields and format dates as YYYY-MM-DD strings with _fmtDate; keep validation for year start/end.

5) Verification
   - Manual CRUD testing, index uniqueness check, and tab interactions.
   - Confirm downstream services (GradingWindowService, GenericApiController) continue working with extended model fields.
