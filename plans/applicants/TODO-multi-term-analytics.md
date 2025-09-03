# Applicants Analytics: Multi-term Combination Support

Goal:
Enable combining multiple terms (more than two) on the Applicants Analytics page to aggregate stats across selected terms.

Scope:
- Backend: Extend ApplicantAnalyticsController@summary to accept arrays of term IDs for primary and compare sets. Add combined aggregation logic.
- Frontend: Update analytics UI to allow multi-select for Term A and Term B and wire filters to new backend parameters.

Approved Plan:
1) Backend API changes (non-breaking):
   - Accept `syids` (array or CSV) for primary combined set and `compare_syids` for compare set.
   - Backward-compat continues for `syid` and `compare_syid`.
   - Implement `computeCombinedSummary(syids[], start, end, filters)` aggregating across terms using latest record per user per term and counting distinct (user_id, syid) pairs.
   - Response:
     - data.terms["__combined_A__"] and data.terms["__combined_B__"] when arrays are used.
     - data.meta includes `primary_syids` and `compare_syids` arrays when provided; retains `primary_syid` and `compare_syid` for singles.

2) Frontend:
   - Update filters model to support arrays: `syidsA` and `syidsB`.
   - analytics.html: Make Term A and Term B selects multi-select; add clear compare button for B (sets to []).
   - analytics.service.js: Serialize arrays as repeated params:
     - If syidsA.length > 1: send `syids[]=...`; if length === 1: send `syid`.
     - Same for B with `compare_syids[]` / `compare_syid`.
   - analytics.controller.js:
     - Initialize `syidsA` with default selected term `[tid]`.
     - Validate selected arrays against loaded terms and prune non-existent IDs.
     - load(): pick summaries:
       - If meta.primary_syids exists: use `terms["__combined_A__"]`; else if single: use first id mapping.
       - Same for B with `__combined_B__`.
     - labelFor(): show "Combined (n terms)" when multiple; otherwise single term label.
     - clearCompare(): sets `syidsB = []`.

Testing Checklist:
- Single Term A only: unchanged behavior.
- Multiple Term A: shows combined summary with label "Combined (n terms)".
- Compare with multiple Term B: shows side-by-side combined datasets.
- Filters: campus/status/type/sub_type/search work with combined sets.
- Date range: timeseries aggregated across selected terms.

Notes:
- If `tb_mas_applicant_data.syid` column is missing, combined summary is not supported. The controller will fallback to single-term behavior.

Tasks:
- [ ] Backend: update ApplicantAnalyticsController@summary
- [ ] Backend: add parseIdsList() helper
- [ ] Backend: add computeCombinedSummary()
- [ ] Frontend: update analytics.service.js
- [ ] Frontend: update analytics.controller.js
- [ ] Frontend: update analytics.html
- [ ] Manual test scenarios as listed above
